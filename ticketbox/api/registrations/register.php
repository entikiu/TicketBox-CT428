<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, [
        'success' => false,
        'message' => 'Phương thức không hợp lệ.'
    ]);
}

// Kiểm tra đăng nhập (Giả định session của bạn lưu 'id' trong mảng 'user' hoặc 'user_id')
// Nếu dùng biến giống các file trước: $_SESSION['user_id']
$userId = (int) ($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);

if ($userId <= 0) {
    respond(401, [
        'success' => false,
        'message' => 'Vui lòng đăng nhập trước.'
    ]);
}

// Nhận dữ liệu AJAX
$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    $data = $_POST;
}

$eventId = filter_var(
    $data['event_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$eventId || $eventId <= 0) {
    respond(422, [
        'success' => false,
        'message' => 'ID sự kiện không hợp lệ.'
    ]);
}

try {
    $pdo->beginTransaction();

    // Khóa sự kiện để chống đăng ký vượt sức chứa (Sửa bảng thành `sukien`)
    $stmt = $pdo->prepare(
        "SELECT id, thoi_gian, so_luong_toi_da
         FROM sukien
         WHERE id = ?
         FOR UPDATE"
    );
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        $pdo->rollBack();
        respond(404, [
            'success' => false,
            'message' => 'Không tìm thấy sự kiện.'
        ]);
    }

    if (strtotime($event['thoi_gian']) <= time()) {
        $pdo->rollBack();
        respond(409, [
            'success' => false,
            'message' => 'Sự kiện đã bắt đầu hoặc kết thúc.'
        ]);
    }

    // Kiểm tra đăng ký hiện tại (Sửa bảng thành `ve`)
    $stmt = $pdo->prepare(
        "SELECT id, trang_thai
         FROM ve
         WHERE nguoi_dung_id = ?
           AND su_kien_id = ?"
    );
    $stmt->execute([$userId, $eventId]);
    $registration = $stmt->fetch();

    // Sửa trạng thái thành 'da-huy'
    if (
        $registration &&
        $registration['trang_thai'] !== 'da-huy'
    ) {
        $pdo->rollBack();
        respond(409, [
            'success' => false,
            'message' => 'Bạn đã đăng ký sự kiện này.'
        ]);
    }

    // Đếm số vé đang chiếm chỗ (Sửa trạng thái)
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM ve
         WHERE su_kien_id = ?
           AND trang_thai IN ('thanh-cong', 'da-checkin')"
    );
    $stmt->execute([$eventId]);
    $registeredCount = (int) $stmt->fetchColumn();

    if ($registeredCount >= (int) $event['so_luong_toi_da']) {
        $pdo->rollBack();
        respond(409, [
            'success' => false,
            'message' => 'Sự kiện đã hết vé.'
        ]);
    }

    // Mã vé ngẫu nhiên
    $ticketCode = 'TKB-' . date('Y') . '-' . rand(10000, 99999); // VD: TKB-2026-12345 giống ảnh CSDL của bạn

    // Cần có tham số mặc định cho loại vé và giá vé (bạn có thể lấy từ bảng sukien nếu có)
    $loaiVe = 'VÉ TIÊU CHUẨN';
    $giaVe = '0 đ'; 

    if ($registration) {
        // Đăng ký lại vé đã hủy (Sửa cột ngay_dang_ky thành ngay_dat, trạng thái 'thanh-cong')
        $stmt = $pdo->prepare(
            "UPDATE ve
             SET trang_thai = 'thanh-cong',
                 ma_ve = ?,
                 ngay_dat = CURRENT_TIMESTAMP
             WHERE id = ?"
        );
        $stmt->execute([
            $ticketCode,
            $registration['id']
        ]);
    } else {
        // Đăng ký mới (Thêm cột loai_ve, gia_ve, sửa ngay_dang_ky thành ngay_dat, trạng thái 'thanh-cong')
        $stmt = $pdo->prepare(
            "INSERT INTO ve
             (nguoi_dung_id, su_kien_id, ma_ve, loai_ve, gia_ve, trang_thai, ngay_dat)
             VALUES (?, ?, ?, ?, ?, 'thanh-cong', CURRENT_TIMESTAMP)"
        );
        $stmt->execute([
            $userId,
            $eventId,
            $ticketCode,
            $loaiVe,
            $giaVe
        ]);
    }

    $pdo->commit();

    respond(201, [
        'success' => true,
        'message' => 'Đăng ký vé thành công.',
        'data' => [
            'event_id' => $eventId,
            'ticket_code' => $ticketCode,
            'status' => 'thanh-cong'
        ]
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Lỗi hệ thống, vui lòng thử lại.'
    ]);
}