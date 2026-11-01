<?php # create dính login
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, [
        'success' => false,
        'message' => 'Phương thức không hợp lệ.'
    ]);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']['id'])) {
    respond(401, [
        'success' => false,
        'message' => 'Vui lòng đăng nhập.'
    ]);
}

// Kiểm tra quyền
$role = $_SESSION['user']['vai_tro'] ?? '';

if (!in_array($role, ['Organizer', 'Admin'], true)) {
    respond(403, [
        'success' => false,
        'message' => 'Bạn không có quyền tạo sự kiện.'
    ]);
}

// Đọc JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    respond(400, [
        'success' => false,
        'message' => 'Dữ liệu JSON không hợp lệ.'
    ]);
}

$name = trim($data['ten_su_kien'] ?? '');
$description = trim($data['mo_ta'] ?? '');
$location = trim($data['dia_diem'] ?? '');
$time = trim($data['thoi_gian'] ?? '');
$capacity = filter_var(
    $data['so_luong_toi_da'] ?? null,
    FILTER_VALIDATE_INT
);

if (
    $name === '' ||
    $description === '' ||
    $location === '' ||
    $capacity === false ||
    $capacity === null ||
    $capacity < 1
) {
    respond(422, [
        'success' => false,
        'message' => 'Vui lòng nhập đầy đủ thông tin hợp lệ.'
    ]);
}

// Kiểm tra định dạng thời gian
$date = DateTime::createFromFormat('Y-m-d H:i:s', $time);

if (!$date || $date->format('Y-m-d H:i:s') !== $time) {
    respond(422, [
        'success' => false,
        'message' => 'Thời gian không hợp lệ. Định dạng: YYYY-MM-DD HH:MM:SS.'
    ]);
}

if ($date <= new DateTime()) {
    respond(422, [
        'success' => false,
        'message' => 'Thời gian sự kiện phải ở tương lai.'
    ]);
}

try {
    $sql = "
        INSERT INTO sukien (
            ten_su_kien,
            mo_ta,
            dia_diem,
            thoi_gian,
            so_luong_toi_da,
            nguoi_tao
        ) VALUES (
            :name,
            :description,
            :location,
            :time,
            :capacity,
            :creator
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'location' => $location,
        'time' => $time,
        'capacity' => $capacity,
        'creator' => $_SESSION['user']['id']
    ]);

    respond(201, [
        'success' => true,
        'message' => 'Tạo sự kiện thành công.',
        'data' => [
            'id' => (int) $pdo->lastInsertId()
        ]
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Không thể tạo sự kiện.'
    ]);
}
