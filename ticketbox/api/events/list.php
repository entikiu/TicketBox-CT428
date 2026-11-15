<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../app/models/Event.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    api_response(405, [
        'success' => false,
        'message' => 'Chỉ hỗ trợ GET.'
    ]);
}

// Chấp nhận linh hoạt cả ?keyword=, ?key= hoặc ?q= từ Frontend
$keyword = trim((string)($_GET['keyword'] ?? $_GET['key'] ?? $_GET['q'] ?? ''));

try {
    $eventModel = new Event($pdo);
    $events = $eventModel->searchEvents($keyword);

    api_response(200, [
        'success' => true,
        'data'    => $events
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Events] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ.'
    ]);
}