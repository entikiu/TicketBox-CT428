<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';
require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../app/models/Event.php';

require_method('POST');
require_roles(['organizer', 'admin']);

$data = read_json_input();

require_fields($data, [
    'title', 'description', 'location', 'capacity'
]);

$rawTime = $data['time'] ?? $data['start_at'] ?? null;
if ($rawTime === null || trim((string)$rawTime) === '') {
    api_response(422, [
        'success' => false,
        'message' => 'Thời gian diễn ra sự kiện không được để trống.'
    ]);
}

$title       = trim((string)$data['title']);
$description = trim((string)$data['description']);
$location    = trim((string)$data['location']);
$time        = strtotime((string)$rawTime);
$capacity    = filter_var($data['capacity'], FILTER_VALIDATE_INT);
$image       = trim((string)($data['hinh_anh'] ?? $data['image'] ?? ''));

if (
    $title === '' || mb_strlen($title, 'UTF-8') > 200 ||
    mb_strlen($description, 'UTF-8') > 10000 ||
    mb_strlen($location, 'UTF-8') > 255 ||
    mb_strlen($image, 'UTF-8') > 255 ||
    $time === false ||
    $capacity === false || $capacity < 1
) {
    api_response(422, [
        'success' => false,
        'message' => 'Dữ liệu sự kiện không hợp lệ.'
    ]);
}

$userId = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
if ($userId < 1) {
    api_response(401, [
        'success' => false,
        'message' => 'Phiên đăng nhập không hợp lệ.'
    ]);
}

$payload = [
    'ten_su_kien'     => $title,
    'mo_ta'           => $description !== '' ? $description : null,
    'dia_diem'        => $location !== '' ? $location : null,
    'thoi_gian'       => date('Y-m-d H:i:s', $time),
    'so_luong_toi_da' => $capacity,
    'hinh_anh'        => $image !== '' ? $image : null
];

try {
    $id = (new Event($pdo))->create($payload, $userId);

    api_response(201, [
        'success' => true,
        'message' => 'Tạo sự kiện thành công.',
        'data'    => ['id' => $id]
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Event Create] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ khi tạo sự kiện.'
    ]);
}