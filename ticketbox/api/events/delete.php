<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';
require_once __DIR__ . '/../../app/helpers/response.php';

require_method('DELETE');
require_roles(['organizer', 'admin']);

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    api_response(422, [
        'success' => false,
        'message' => 'ID sự kiện không hợp lệ.'
    ]);
}

$userId   = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
$userRole = strtolower((string)($_SESSION['role'] ?? $_SESSION['user_role'] ?? $_SESSION['user']['vai_tro'] ?? ''));

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'SELECT id, nguoi_tao FROM sukien WHERE id = :id FOR UPDATE'
    );
    $stmt->execute(['id' => $id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $pdo->rollBack();
        api_response(404, [
            'success' => false,
            'message' => 'Không tìm thấy sự kiện.'
        ]);
    }

    if ($userRole !== 'admin' && (int)$event['nguoi_tao'] !== $userId) {
        $pdo->rollBack();
        api_response(403, [
            'success' => false,
            'message' => 'Bạn không có quyền xóa sự kiện này.'
        ]);
    }

    // Kiểm tra đã có vé nào phát sinh trong bảng `ve` chưa
    $check = $pdo->prepare(
        'SELECT COUNT(*) FROM ve WHERE su_kien_id = :id'
    );
    $check->execute(['id' => $id]);
    $registrationCount = (int)$check->fetchColumn();

    if ($registrationCount > 0) {
        $pdo->rollBack();
        api_response(409, [
            'success' => false,
            'message' => 'Không thể xóa sự kiện vì đã có người đăng ký.'
        ]);
    }

    // Xóa các hạng vé cấu hình của sự kiện trong bảng `loaive` trước khi xóa `sukien`
    $deleteTiers = $pdo->prepare('DELETE FROM loaive WHERE su_kien_id = :id');
    $deleteTiers->execute(['id' => $id]);

    $delete = $pdo->prepare('DELETE FROM sukien WHERE id = :id');
    $delete->execute(['id' => $id]);

    $pdo->commit();

    api_response(200, [
        'success' => true,
        'message' => 'Xóa sự kiện thành công.',
        'data'    => ['id' => (int)$id]
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[TicketBox Delete Event] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi cơ sở dữ liệu khi xóa sự kiện.'
    ]);
}