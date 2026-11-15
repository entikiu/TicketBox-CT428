<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    api_response(405, [
        'success' => false,
        'message' => 'Chỉ hỗ trợ phương thức GET.'
    ]);
}

try {
    $stmt = $pdo->query('SELECT id, ten_danh_muc, slug FROM danh_muc ORDER BY id ASC');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    api_response(200, [
        'success' => true,
        'data'    => $categories
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Categories] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ khi tải danh mục.'
    ]);
}