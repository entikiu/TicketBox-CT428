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

// Chỉ chấp nhận GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    respond(405, [
        'success' => false,
        'message' => 'Phương thức không hợp lệ.'
    ]);
}

// Kiểm tra ID sự kiện
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    respond(400, [
        'success' => false,
        'message' => 'ID sự kiện không hợp lệ.'
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
        WHERE id = :id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    $event = $stmt->fetch();

    if (!$event) {
        respond(404, [
            'success' => false,
            'message' => 'Không tìm thấy sự kiện.'
        ]);
    }

    respond(200, [
        'success' => true,
        'message' => 'Lấy chi tiết sự kiện thành công.',
        'data' => $event
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Không thể lấy chi tiết sự kiện.'
    ]);
}
