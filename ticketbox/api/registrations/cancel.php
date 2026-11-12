<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';

require_method('POST');
require_roles(['user', 'attendee', 'customer', 'member', 'organizer', 'admin']);

$data = read_json_input();
$rawId = $data['registration_id'] ?? $data['id'] ?? null;
$registrationId = filter_var($rawId, FILTER_VALIDATE_INT);

if ($registrationId === false || $registrationId < 1) {
    api_response(422, [
        'success' => false,
        'message' => 'ID đăng ký vé không hợp lệ.'
    ]);
}

$currentUserId = (int)$_SESSION['user_id'];
$currentRole = (string)($_SESSION['role'] ?? '');

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.nguoi_dung_id,
            r.trang_thai,
            e.thoi_gian
        FROM ve r
        INNER JOIN sukien e ON r.su_kien_id = e.id
        WHERE r.id = :id
        FOR UPDATE
    ");
    $stmt->execute([':id' => $registrationId]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registration) {
        $pdo->rollBack();
        api_response(404, [
            'success' => false,
            'message' => 'Không tìm thấy thông tin vé.'
        ]);
    }

    if ((int)$registration['nguoi_dung_id'] !== $currentUserId && $currentRole !== 'admin') {
        $pdo->rollBack();
        api_response(403, [
            'success' => false,
            'message' => 'Bạn không có quyền hủy vé này.'
        ]);
    }

    if ($registration['trang_thai'] === 'da-huy') {
        $pdo->rollBack();
        api_response(422, [
            'success' => false,
            'message' => 'Vé này đã được hủy trước đó.'
        ]);
    }

    if ($registration['trang_thai'] === 'da-checkin') {
        $pdo->rollBack();
        api_response(422, [
            'success' => false,
            'message' => 'Không thể hủy vé đã được check-in.'
        ]);
    }

    $eventStart = strtotime((string)$registration['thoi_gian']);
    if ($eventStart !== false && $eventStart <= time()) {
        $pdo->rollBack();
        api_response(422, [
            'success' => false,
            'message' => 'Không thể hủy vé khi sự kiện đã bắt đầu hoặc kết thúc.'
        ]);
    }

    $updateStmt = $pdo->prepare("
        UPDATE ve
        SET trang_thai = 'da-huy'
        WHERE id = :id
    ");
    $updateStmt->execute([':id' => $registrationId]);

    $pdo->commit();

    api_response(200, [
        'success' => true,
        'message' => 'Hủy vé thành công.',
        'data' => [
            'id' => $registrationId,
            'trang_thai' => 'da-huy'
        ]
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[TicketBox Registration Cancel] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ.'
    ]);
}