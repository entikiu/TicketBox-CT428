<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../app/models/Event.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    api_response(405, [
        'status'  => 'error',
        'success' => false, 
        'message' => 'Chỉ hỗ trợ GET.'
    ]);
}

// Nhận tham số tìm kiếm & bộ lọc từ Query String (URL)
$keyword = trim((string)($_GET['keyword'] ?? ''));
$status  = trim((string)($_GET['status'] ?? ''));

try {
    $eventModel = new Event($pdo);
    
    // Gọi phương thức searchEvents để hỗ trợ lọc từ khóa và tính số vé đã đăng ký
    $events = $eventModel->searchEvents($keyword, $status);

    api_response(200, [
        'status'  => 'success',
        'success' => true,
        'data'    => $events
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Events] ' . $e->getMessage());
    api_response(500, [
        'status'  => 'error',
        'success' => false, 
        'message' => 'Lỗi máy chủ.'
    ]);
}