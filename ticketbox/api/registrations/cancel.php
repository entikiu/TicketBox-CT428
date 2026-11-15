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

$currentUserId = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
$currentRole   = strtolower((string)($_SESSION['role'] ?? $_SESSION['user_role'] ?? $_SESSION['user']['vai_tro'] ?? ''));

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.su_kien_id,
            r.nguoi_dung_id,
            r.loai_ve,
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

    // Mở bán lại hạng vé trong bảng `loaive` nếu đang đánh dấu `da_het_ve = 1`
    $stmtTier = $pdo->prepare('
        SELECT id, so_luong, da_het_ve
        FROM loaive
        WHERE su_kien_id = :su_kien_id
          AND ten_loai_ve = :ten_loai_ve
        FOR UPDATE
    ');
    $stmtTier->execute([
        ':su_kien_id'  => (int)$registration['su_kien_id'],
        ':ten_loai_ve' => (string)$registration['loai_ve']
    ]);
    $tier = $stmtTier->fetch(PDO::FETCH_ASSOC);

    if ($tier && (int)$tier['da_het_ve'] === 1) {
        $stmtActive = $pdo->prepare("
            SELECT COUNT(*)
            FROM ve
            WHERE su_kien_id = :su_kien_id
              AND loai_ve = :loai_ve
              AND trang_thai IN ('thanh-cong', 'da-checkin')
        ");
        $stmtActive->execute([
            ':su_kien_id' => (int)$registration['su_kien_id'],
            ':loai_ve'    => (string)$registration['loai_ve']
        ]);
        $activeCount = (int)$stmtActive->fetchColumn();

        if ((int)$tier['so_luong'] === 0 || $activeCount < (int)$tier['so_luong']) {
            $stmtReopen = $pdo->prepare('UPDATE loaive SET da_het_ve = 0 WHERE id = :id');
            $stmtReopen->execute([':id' => (int)$tier['id']]);
        }
    }

    $pdo->commit();

    api_response(200, [
        'success' => true,
        'message' => 'Hủy vé thành công.',
        'data'    => [
            'id'         => $registrationId,
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