
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/Event.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header('Allow: DELETE');
    api_response(405, ['success' => false, 'message' => 'Chỉ hỗ trợ DELETE.']);
}

require_roles(['organizer', 'admin']);

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    api_response(422, ['success' => false, 'message' => 'ID không hợp lệ.']);
}

try {
    $model = new Event($pdo);
    $event = $model->find($id);

    if (!$event) {
        api_response(404, ['success' => false, 'message' => 'Không tìm thấy sự kiện.']);
    }

    if (
        $_SESSION['user_role'] !== 'admin' &&
        (int)$event['organizer_id'] !== (int)$_SESSION['user_id']
    ) {
        api_response(403, ['success' => false, 'message' => 'Không có quyền xóa sự kiện này.']);
    }

    $model->delete($id);

    api_response(200, ['success' => true, 'message' => 'Đã xóa sự kiện.']);
} catch (PDOException $e) {
    error_log('[TicketBox Event Delete] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Không thể xóa sự kiện. Kiểm tra các dữ liệu liên quan.'
    ]);
}
