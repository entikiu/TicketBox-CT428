
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../app/models/Event.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    api_response(405, ['success' => false, 'message' => 'Chỉ hỗ trợ GET.']);
}

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    api_response(422, ['success' => false, 'message' => 'ID không hợp lệ.']);
}

try {
    $event = (new Event($pdo))->find($id);

    if (!$event || $event['status'] !== 'published') {
        api_response(404, ['success' => false, 'message' => 'Không tìm thấy sự kiện.']);
    }

    unset($event['organizer_id']);
    api_response(200, ['success' => true, 'data' => $event]);
} catch (PDOException $e) {
    error_log('[TicketBox Event Detail] ' . $e->getMessage());
    api_response(500, ['success' => false, 'message' => 'Lỗi máy chủ.']);
}
