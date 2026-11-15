<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';

require_method('POST');
require_roles(['organizer', 'admin']);

$data = read_json_input();

$ticketCode = isset($data['ma_ve']) ? trim((string)$data['ma_ve']) : '';
$registrationId = isset($data['registration_id'])
    ? filter_var($data['registration_id'], FILTER_VALIDATE_INT)
    : false;

if ($ticketCode === '' && ($registrationId === false || $registrationId < 1)) {
    api_response(422, [
        'success' => false,
        'message' => 'Vui lòng cung cấp mã vé (ma_ve) hoặc ID vé (registration_id).'
    ]);
}

$currentUserId = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
$currentRole   = strtolower((string)($_SESSION['role'] ?? $_SESSION['user_role'] ?? $_SESSION['user']['vai_tro'] ?? ''));

try {
    $pdo->beginTransaction();

    if ($ticketCode !== '') {
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.nguoi_dung_id,
                r.ma_ve,
                r.loai_ve,
                r.trang_thai,
                e.id AS event_id,
                e.ten_su_kien,
                e.nguoi_tao
            FROM ve r
            INNER JOIN sukien e ON r.su_kien_id = e.id
            WHERE r.ma_ve = :ma_ve
            FOR UPDATE
        ");
        $stmt->execute([':ma_ve' => $ticketCode]);
    } else {
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.nguoi_dung_id,
                r.ma_ve,
                r.loai_ve,
                r.trang_thai,
                e.id AS event_id,
                e.ten_su_kien,
                e.nguoi_tao
            FROM ve r
            INNER JOIN sukien e ON r.su_kien_id = e.id
            WHERE r.id = :id
            FOR UPDATE
        ");
        $stmt->execute([':id' => $registrationId]);
    }

    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registration) {
        $pdo->rollBack();
        api_response(404, [
            'success' => false,
            'message' => 'Không tìm thấy vé hợp lệ.'
        ]);
    }

    if ($currentRole !== 'admin' && (int)$registration['nguoi_tao'] !== $currentUserId) {
        $pdo->rollBack();
        api_response(403, [
            'success' => false,
            'message' => 'Bạn không có quyền check-in vé của sự kiện này.'
        ]);
    }

    if ($registration['trang_thai'] === 'da-huy') {
        $pdo->rollBack();
        api_response(422, [
            'success' => false,
            'message' => 'Vé này đã bị hủy, không thể check-in.'
        ]);
    }

    if ($registration['trang_thai'] === 'da-checkin') {
        $pdo->rollBack();
        api_response(409, [
            'success' => false,
            'message' => 'Vé này đã được check-in trước đó.',
            'data'    => [
                'id'    => (int)$registration['id'],
                'ma_ve' => $registration['ma_ve']
            ]
        ]);
    }

    $updateStmt = $pdo->prepare("
        UPDATE ve
        SET trang_thai = 'da-checkin'
        WHERE id = :id
    ");
    $updateStmt->execute([':id' => (int)$registration['id']]);

    $pdo->commit();

    api_response(200, [
        'success' => true,
        'message' => 'Check-in thành công.',
        'data'    => [
            'id'          => (int)$registration['id'],
            'ma_ve'       => $registration['ma_ve'],
            'loai_ve'     => $registration['loai_ve'],
            'event_id'    => (int)$registration['event_id'],
            'event_title' => $registration['ten_su_kien'],
            'trang_thai'  => 'da-checkin'
        ]
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[TicketBox Registration Checkin] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ.'
    ]);
}