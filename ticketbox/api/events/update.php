<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';
require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../app/models/Event.php';

require_method('PUT');
require_roles(['organizer', 'admin']);

$data = read_json_input();

$id = filter_var($_GET['id'] ?? $data['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    api_response(422, [
        'success' => false,
        'message' => 'ID sự kiện không hợp lệ.'
    ]);
}

require_fields($data, [
    'title', 'location', 'time', 'capacity'
]);

$eventModel = new Event($pdo);
$event      = $eventModel->find($id);

if (!$event) {
    api_response(404, [
        'success' => false,
        'message' => 'Không tìm thấy sự kiện.'
    ]);
}

$userId   = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
$userRole = strtolower((string)($_SESSION['role'] ?? $_SESSION['user_role'] ?? $_SESSION['user']['vai_tro'] ?? ''));

if ($userRole !== 'admin' && (int)($event['nguoi_tao'] ?? 0) !== $userId) {
    api_response(403, [
        'success' => false,
        'message' => 'Bạn không có quyền sửa sự kiện này.'
    ]);
}

$title       = trim((string)$data['title']);
$description = trim((string)($data['description'] ?? ''));
$location    = trim((string)$data['location']);
$time        = strtotime((string)$data['time']);
$capacity    = filter_var($data['capacity'], FILTER_VALIDATE_INT);

if (
    $title === '' || mb_strlen($title, 'UTF-8') > 200 ||
    mb_strlen($description, 'UTF-8') > 10000 ||
    mb_strlen($location, 'UTF-8') > 255 ||
    $time === false ||
    $capacity === false || $capacity < 1
) {
    api_response(422, [
        'success' => false,
        'message' => 'Dữ liệu cập nhật không hợp lệ.'
    ]);
}

$updateData = [
    'ten_su_kien'     => $title,
    'mo_ta'           => $description !== '' ? $description : null,
    'dia_diem'        => $location !== '' ? $location : null,
    'thoi_gian'       => date('Y-m-d H:i:s', $time),
    'so_luong_toi_da' => $capacity
];

$stringFieldMap = [
    'hinh_anh'          => ['keys' => ['hinh_anh', 'image'], 'max' => 255],
    'thoi_gian_hien_thi'=> ['keys' => ['thoi_gian_hien_thi', 'display_time'], 'max' => 150],
    'ten_dia_diem'      => ['keys' => ['ten_dia_diem', 'venue_name'], 'max' => 200],
    'ten_ban_to_chuc'   => ['keys' => ['ten_ban_to_chuc', 'organizer_name'], 'max' => 200],
    'mo_ta_ban_to_chuc' => ['keys' => ['mo_ta_ban_to_chuc', 'organizer_desc'], 'max' => 255],
];

foreach ($stringFieldMap as $col => $cfg) {
    foreach ($cfg['keys'] as $k) {
        if (array_key_exists($k, $data)) {
            $val = trim((string)($data[$k] ?? ''));
            if (mb_strlen($val, 'UTF-8') > $cfg['max']) {
                api_response(422, [
                    'success' => false,
                    'message' => "Trường {$col} vượt quá {$cfg['max']} ký tự."
                ]);
            }
            $updateData[$col] = $val !== '' ? $val : null;
            break;
        }
    }
}

if (array_key_exists('gia_thap_nhat', $data)) {
    $updateData['gia_thap_nhat'] = max(0, (int)$data['gia_thap_nhat']);
}

if (array_key_exists('danh_muc_id', $data)) {
    $catId = filter_var($data['danh_muc_id'], FILTER_VALIDATE_INT);
    $updateData['danh_muc_id'] = ($catId && $catId > 0) ? $catId : null;
}

try {
    $stmtSold = $pdo->prepare(
        "SELECT COUNT(*) FROM ve WHERE su_kien_id = :id AND trang_thai != 'da-huy'"
    );
    $stmtSold->execute(['id' => $id]);
    $soldCount = (int)$stmtSold->fetchColumn();

    if ($capacity < $soldCount) {
        api_response(409, [
            'success' => false,
            'message' => "Sức chứa mới ({$capacity}) không thể nhỏ hơn số vé đã bán ({$soldCount})."
        ]);
    }

    $eventModel->update($id, $updateData);

    api_response(200, [
        'success' => true,
        'message' => 'Cập nhật sự kiện thành công.'
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Event Update] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ khi cập nhật sự kiện.'
    ]);
}