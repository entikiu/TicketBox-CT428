<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../app/models/Event.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    api_response(405, [
        'success' => false,
        'message' => 'Chỉ hỗ trợ phương thức GET.'
    ]);
}

$keyword      = trim((string)($_GET['keyword'] ?? $_GET['key'] ?? $_GET['q'] ?? ''));
$categoryId   = filter_var($_GET['danh_muc_id'] ?? $_GET['category_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$categorySlug = trim((string)($_GET['category'] ?? $_GET['slug'] ?? ''));

try {
    $eventModel = new Event($pdo);
    $events     = $eventModel->searchEvents($keyword, $categoryId, $categorySlug);

    api_response(200, [
        'success' => true,
        'total'   => count($events),
        'data'    => $events
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Event List] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ khi tải danh sách sự kiện.'
    ]);
}