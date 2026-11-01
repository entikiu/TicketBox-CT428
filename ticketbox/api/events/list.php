<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    respond(405, [
        'success' => false,
        'message' => 'Phương thức không hợp lệ.'
    ]);
}

try {
    $sql = "
        SELECT
            id,
            ten_su_kien,
            mo_ta,
            dia_diem,
            thoi_gian,
            so_luong_toi_da,
            nguoi_tao
        FROM sukien
        ORDER BY thoi_gian ASC
    ";

    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll();

    respond(200, [
        'success' => true,
        'message' => 'Lấy danh sách sự kiện thành công.',
        'data' => $events
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Không thể lấy danh sách sự kiện.'
    ]);
}
