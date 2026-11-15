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
$price       = isset($data['price']) ? filter_var($data['price'], FILTER_VALIDATE_FLOAT) : 0.0;

if (
    $title === '' || mb_strlen($title, 'UTF-8') > 200 ||
    mb_strlen($description, 'UTF-8') > 10000 ||
    mb_strlen($location, 'UTF-8') > 255 ||
    mb_strlen($image, 'UTF-8') > 255 ||
    $time === false ||
    $capacity === false || $capacity < 1 ||
    $price === false || $price < 0
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
    $pdo->beginTransaction();

    // 1. Tạo sự kiện trong bảng `sukien`
    $eventId = (new Event($pdo))->create($payload, $userId);

    // 2. Tạo hạng vé tương ứng trong bảng `loaive`
    $stmtTier = $pdo->prepare('
        INSERT INTO loaive (su_kien_id, ten_loai_ve, gia_ve, da_het_ve, so_luong)
        VALUES (:su_kien_id, :ten_loai_ve, :gia_ve, 0, :so_luong)
    ');

    if (!empty($data['loaive']) && is_array($data['loaive'])) {
        foreach ($data['loaive'] as $tier) {
            if (!is_array($tier)) {
                continue;
            }
            $tierName = mb_substr(trim((string)($tier['ten_loai_ve'] ?? '')), 0, 150, 'UTF-8');
            $tierPrice = max(0, (int)($tier['gia_ve'] ?? 0));
            $tierQty   = max(1, (int)($tier['so_luong'] ?? $capacity));

            if ($tierName !== '') {
                $stmtTier->execute([
                    ':su_kien_id'  => $eventId,
                    ':ten_loai_ve' => $tierName,
                    ':gia_ve'      => $tierPrice,
                    ':so_luong'    => $tierQty
                ]);
            }
        }
    } else {
        $stmtTier->execute([
            ':su_kien_id'  => $eventId,
            ':ten_loai_ve' => 'Vé Tiêu Chuẩn',
            ':gia_ve'      => (int)round((float)$price),
            ':so_luong'    => $capacity
        ]);
    }

    $pdo->commit();

    api_response(201, [
        'success' => true,
        'message' => 'Tạo sự kiện thành công.',
        'data'    => ['id' => $eventId]
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[TicketBox Event Create] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ khi tạo sự kiện.'
    ]);
}