<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';
require_once __DIR__ . '/../../app/models/Event.php';

require_method('PUT');
require_roles(['organizer', 'admin']);

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    api_response(422, ['success' => false, 'message' => 'ID không hợp lệ.']);
}

$data = read_json_input();
// Giả định Frontend gửi lên các trường này
require_fields($data, [
    'title', 'description', 'location',
    'time', 'capacity' // Thay start_at, end_at bằng time (thoi_gian)
]);

$eventModel = new Event($pdo);
$event = $eventModel->find($id);

if (!$event) {
    api_response(404, ['success' => false, 'message' => 'Không tìm thấy sự kiện.']);
}

// SỬA: Đổi 'user_role' thành 'role' và 'organizer_id' thành 'nguoi_tao'
$userRole = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
if (
    $userRole !== 'admin' &&
    (int)$event['nguoi_tao'] !== (int)$_SESSION['user_id']
) {
    api_response(403, ['success' => false, 'message' => 'Không có quyền sửa sự kiện này.']);
}

$title = trim((string)$data['title']);
$description = trim((string)$data['description']);
$location = trim((string)$data['location']);
$time = strtotime((string)$data['time']); // Lấy thoi_gian thay vì start_at/end_at
$capacity = filter_var($data['capacity'], FILTER_VALIDATE_INT);
// Giá vé có thể nằm ở bảng khác hoặc không có trong bảng sukien, tùy bạn cấu hình

if (
    $title === '' || strlen($title) > 200 ||
    $description === '' || strlen($description) > 10000 ||
    $location === '' || strlen($location) > 255 ||
    !$time ||
    $capacity === false || $capacity < 1
) {
    api_response(422, ['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
}

// SỬA: Map dữ liệu chuẩn bị Update sang đúng tên cột trong CSDL tiếng Việt
$updateData = [
    'ten_su_kien' => $title,
    'mo_ta' => $description,
    'dia_diem' => $location,
    'thoi_gian' => date('Y-m-d H:i:s', $time),
    'so_luong_toi_da' => $capacity
];

// Nếu có trạng thái thì map thêm
if (isset($data['status'])) {
    // Nhớ kiểm tra giá trị status sao cho khớp với ENUM trong DB của bạn
    $updateData['trang_thai'] = $data['status']; 
}

try {
    // Truyền mảng updateData (đã map tên cột tiếng Việt) vào model
    $eventModel->update($id, $updateData);

    api_response(200, [
        'success' => true,
        'message' => 'Cập nhật sự kiện thành công.'
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Event Update] ' . $e->getMessage());
    api_response(500, ['success' => false, 'message' => 'Lỗi máy chủ.']);
}