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

$loai    = trim((string)($_GET['loai'] ?? ''));
$keyword = trim((string)($_GET['keyword'] ?? $_GET['q'] ?? ''));

$allowedTypes = ['NgheSi', 'BanToChuc', 'SanKhau'];
if ($loai !== '' && !in_array($loai, $allowedTypes, true)) {
    api_response(422, [
        'success' => false,
        'message' => 'Loại Star không hợp lệ.'
    ]);
}

try {
    $sql    = 'SELECT * FROM ticketstars WHERE 1=1';
    $params = [];

    if ($loai !== '') {
        $sql .= ' AND loai = :loai';
        $params['loai'] = $loai;
    }

    if ($keyword !== '') {
        $sql .= ' AND (ten_hien_thi LIKE :kw_name OR mo_ta LIKE :kw_desc)';
        $params['kw_name'] = '%' . $keyword . '%';
        $params['kw_desc'] = '%' . $keyword . '%';
    }

    $sql .= ' ORDER BY thu_tu ASC, id ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    api_response(200, [
        'success' => true,
        'data'    => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Stars List] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ khi tải danh sách TicketStars.'
    ]);
}