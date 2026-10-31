<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';
require_once __DIR__ . '/../../app/models/Event.php';

require_method('POST');
require_roles(['organizer', 'admin']);

$data = read_json_input();

require_fields($data, [
    'title', 'description', 'location',
    'start_at', 'end_at', 'price', 'capacity'
]);

$title = trim((string)$data['title']);
$description = trim((string)$data['description']);
$location = trim((string)$data['location']);

$start = strtotime((string)$data['start_at']);
$end = strtotime((string)$data['end_at']);

$price = filter_var($data['price'], FILTER_VALIDATE_FLOAT);
$capacity = filter_var($data['capacity'], FILTER_VALIDATE_INT);
$status = $data['status'] ?? 'draft';

if (
    $title === '' || strlen($title) > 200 ||
    $description === '' || strlen($description) > 10000 ||
    $location === '' || strlen($location) > 255 ||
    !$start || !$end || $end <= $start ||
    $price === false || $price < 0 ||
    $capacity === false || $capacity < 1 ||
    !in_array($status, ['draft', 'published'], true)
) {
    api_response(422, [
        'success' => false,
        'message' => 'Dữ liệu sự kiện không hợp lệ.'
    ]);
}

$data['title'] = $title;
$data['description'] = $description;
$data['location'] = $location;
$data['start_at'] = date('Y-m-d H:i:s', $start);
$data['end_at'] = date('Y-m-d H:i:s', $end);
$data['price'] = $price;
$data['capacity'] = $capacity;
$data['status'] = $status;

try {
    $id = (new Event($pdo))->create(
        $data,
        (int)$_SESSION['user_id']
    );

    api_response(201, [
        'success' => true,
        'message' => 'Tạo sự kiện thành công.',
        'data' => ['id' => $id]
    ]);

} catch (PDOException $e) {
    error_log('[TicketBox Event Create] ' . $e->getMessage());

    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ.'
    ]);
}
?>