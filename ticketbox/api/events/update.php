
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
require_fields($data, [
    'title', 'description', 'location',
    'start_at', 'end_at', 'price', 'capacity', 'status'
]);

$eventModel = new Event($pdo);
$event = $eventModel->find($id);

if (!$event) {
    api_response(404, ['success' => false, 'message' => 'Không tìm thấy sự kiện.']);
}

if (
    $_SESSION['user_role'] !== 'admin' &&
    (int)$event['organizer_id'] !== (int)$_SESSION['user_id']
) {
    api_response(403, ['success' => false, 'message' => 'Không có quyền sửa sự kiện này.']);
}

$title = trim((string)$data['title']);
$description = trim((string)$data['description']);
$location = trim((string)$data['location']);
$start = strtotime((string)$data['start_at']);
$end = strtotime((string)$data['end_at']);
$price = filter_var($data['price'], FILTER_VALIDATE_FLOAT);
$capacity = filter_var($data['capacity'], FILTER_VALIDATE_INT);
$status = $data['status'];

if (
    $title === '' || strlen($title) > 200 ||
    $description === '' || strlen($description) > 10000 ||
    $location === '' || strlen($location) > 255 ||
    !$start || !$end || $end <= $start ||
    $price === false || $price < 0 || $price > 99999999.99 ||
    $capacity === false || $capacity < 1 ||
    !in_array($status, ['draft', 'published', 'cancelled'], true)
) {
    api_response(422, ['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
}

$data['title'] = $title;
$data['description'] = $description;
$data['location'] = $location;
$data['start_at'] = date('Y-m-d H:i:s', $start);
$data['end_at'] = date('Y-m-d H:i:s', $end);
$data['price'] = $price;
$data['capacity'] = $capacity;

try {
    $eventModel->update($id, $data);

    api_response(200, [
        'success' => true,
        'message' => 'Cập nhật sự kiện thành công.'
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Event Update] ' . $e->getMessage());
    api_response(500, ['success' => false, 'message' => 'Lỗi máy chủ.']);
}
