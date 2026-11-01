<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function api_response(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Chỉ chấp nhận PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    api_response(405, [
        'success' => false,
        'message' => 'Phương thức không hợp lệ.'
    ]);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']['id'])) {
    api_response(401, [
        'success' => false,
        'message' => 'Vui lòng đăng nhập.'
    ]);
}

$userId = (int) $_SESSION['user']['id'];
$role = $_SESSION['user']['vai_tro'] ?? '';

// Kiểm tra quyền
if (!in_array($role, ['Organizer', 'Admin'], true)) {
    api_response(403, [
        'success' => false,
        'message' => 'Bạn không có quyền cập nhật sự kiện.'
    ]);
}

// Đọc dữ liệu JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    api_response(400, [
        'success' => false,
        'message' => 'Dữ liệu JSON không hợp lệ.'
    ]);
}

// Kiểm tra ID sự kiện
$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    api_response(422, [
        'success' => false,
        'message' => 'ID sự kiện không hợp lệ.'
    ]);
}

// Kiểm tra dữ liệu đầu vào
$ten = trim($data['ten_su_kien'] ?? '');
$moTa = trim($data['mo_ta'] ?? '');
$diaDiem = trim($data['dia_diem'] ?? '');
$thoiGian = trim($data['thoi_gian'] ?? '');
$soLuong = filter_var(
    $data['so_luong_toi_da'] ?? null,
    FILTER_VALIDATE_INT
);

if (
    $ten === '' ||
    $diaDiem === '' ||
    $thoiGian === '' ||
    $soLuong === false ||
    $soLuong === null ||
    $soLuong <= 0
) {
    api_response(422, [
        'success' => false,
        'message' => 'Vui lòng nhập đầy đủ và chính xác thông tin sự kiện.'
    ]);
}

// Kiểm tra định dạng thời gian
$date = DateTime::createFromFormat('!Y-m-d H:i:s', $thoiGian);

if (!$date || $date->format('Y-m-d H:i:s') !== $thoiGian) {
    api_response(422, [
        'success' => false,
        'message' => 'Thời gian không đúng định dạng Y-m-d H:i:s.'
    ]);
}

if ($date <= new DateTime()) {
    api_response(422, [
        'success' => false,
        'message' => 'Thời gian tổ chức phải ở tương lai.'
    ]);
}

try {
    // Kiểm tra sự kiện tồn tại và quyền sở hữu
    if ($role === 'Admin') {
        $check = $pdo->prepare(
            'SELECT id FROM sukien WHERE id = :id'
        );
        $check->execute(['id' => $id]);
    } else {
        $check = $pdo->prepare(
            'SELECT id FROM sukien
             WHERE id = :id AND nguoi_tao = :user_id'
        );
        $check->execute([
            'id' => $id,
            'user_id' => $userId
        ]);
    }

    if (!$check->fetch()) {
        api_response(404, [
            'success' => false,
            'message' => 'Không tìm thấy sự kiện hoặc bạn không có quyền chỉnh sửa.'
        ]);
    }

    // Không cho giảm sức chứa thấp hơn số người đã đăng ký
    $registered = $pdo->prepare(
        "SELECT COUNT(*) FROM dangkysukien
         WHERE su_kien_id = :id
         AND trang_thai <> 'Cancelled'"
    );
    $registered->execute(['id' => $id]);

    $registeredCount = (int) $registered->fetchColumn();

    if ($soLuong < $registeredCount) {
        api_response(422, [
            'success' => false,
            'message' => 'Số lượng tối đa không được nhỏ hơn số người đã đăng ký.'
        ]);
    }

    // Cập nhật sự kiện
    $sql = 'UPDATE sukien
            SET ten_su_kien = :ten,
                mo_ta = :mo_ta,
                dia_diem = :dia_diem,
                thoi_gian = :thoi_gian,
                so_luong_toi_da = :so_luong
            WHERE id = :id';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'ten' => $ten,
        'mo_ta' => $moTa,
        'dia_diem' => $diaDiem,
        'thoi_gian' => $thoiGian,
        'so_luong' => $soLuong,
        'id' => $id
    ]);

    api_response(200, [
        'success' => true,
        'message' => 'Cập nhật sự kiện thành công.'
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ khi cập nhật sự kiện.'
    ]);
}
