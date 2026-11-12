<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function delete_event_response(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Kiểm tra phương thức HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header('Allow: DELETE');

    delete_event_response(405, [
        'success' => false,
        'message' => 'Chỉ hỗ trợ phương thức DELETE.'
    ]);
}

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user']['id'])) {
    delete_event_response(401, [
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thực hiện chức năng này.'
    ]);
}

$userId = (int) $_SESSION['user']['id'];
$role = $_SESSION['user']['vai_tro'] ?? '';

// 3. Kiểm tra quyền Organizer hoặc Admin
if (!in_array($role, ['Organizer', 'Admin'], true)) {
    delete_event_response(403, [
        'success' => false,
        'message' => 'Bạn không có quyền xóa sự kiện.'
    ]);
}

// 4. Kiểm tra ID sự kiện
$id = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id < 1) {
    delete_event_response(422, [
        'success' => false,
        'message' => 'ID sự kiện không hợp lệ.'
    ]);
}

try {
    $pdo->beginTransaction();

    // 5. Tìm sự kiện và khóa bản ghi
    $sql = 'SELECT id, nguoi_tao
            FROM sukien
            WHERE id = :id
            FOR UPDATE';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $pdo->rollBack();

        delete_event_response(404, [
            'success' => false,
            'message' => 'Không tìm thấy sự kiện.'
        ]);
    }

    // 6. Kiểm tra quyền sở hữu
    if (
        $role !== 'Admin' &&
        (int) $event['nguoi_tao'] !== $userId
    ) {
        $pdo->rollBack();

        delete_event_response(403, [
            'success' => false,
            'message' => 'Bạn không có quyền xóa sự kiện này.'
        ]);
    }

    // 7. Kiểm tra đăng ký liên quan
    $check = $pdo->prepare(
        'SELECT COUNT(*)
         FROM ve
         WHERE su_kien_id = :id'
    );

    $check->execute(['id' => $id]);

    $registrationCount = (int) $check->fetchColumn();

    if ($registrationCount > 0) {
        $pdo->rollBack();

        delete_event_response(409, [
            'success' => false,
            'message' => 'Không thể xóa sự kiện vì đã có người đăng ký.'
        ]);
    }

    // 8. Xóa sự kiện
    $delete = $pdo->prepare(
        'DELETE FROM sukien WHERE id = :id'
    );

    $delete->execute(['id' => $id]);

    $pdo->commit();

    // 9. Trả kết quả thành công
    delete_event_response(200, [
        'success' => true,
        'message' => 'Xóa sự kiện thành công.',
        'data' => [
            'id' => (int) $id
        ]
    ]);

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('[TicketBox Delete Event] ' . $e->getMessage());

    delete_event_response(500, [
        'success' => false,
        'message' => 'Lỗi cơ sở dữ liệu khi xóa sự kiện.'
    ]);
}
