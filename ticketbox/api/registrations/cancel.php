<?php
/**
 * API: Hủy vé sự kiện (AJAX - JSON).
 *
 * POST JSON: { "registration_id": <ve.id> }
 * - Xác thực người dùng đăng nhập (hỗ trợ cả $_SESSION['user']['id'] và $_SESSION['user_id']).
 * - Chỉ chủ vé (hoặc Admin) mới được hủy.
 * - Toàn bộ truy vấn dùng Prepared Statements, chạy trong transaction có khóa dòng.
 * - Khôi phục số lượng vé khả dụng: số vé còn lại được tính theo COUNT(ve != 'da-huy'),
 *   nên khi chuyển vé sang 'da-huy' suất vé được trả lại; đồng thời mở bán lại hạng vé
 *   (loaive.da_het_ve = 0) nếu hạng vé đang bị đánh dấu hết vé.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_method('POST');

// --- 1. Xác thực đăng nhập (đồng bộ với các trang khác) ---
$currentUserId = (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
$currentRole   = strtolower((string)($_SESSION['user']['vai_tro'] ?? $_SESSION['user_role'] ?? ''));

if ($currentUserId <= 0) {
    api_response(401, [
        'success' => false,
        'message' => 'Vui lòng đăng nhập để hủy vé.'
    ]);
}

// --- 2. Kiểm tra dữ liệu đầu vào ---
$data           = read_json_input();
$rawId          = $data['registration_id'] ?? $data['ticket_id'] ?? $data['id'] ?? null;
$registrationId = filter_var($rawId, FILTER_VALIDATE_INT);

if ($registrationId === false || $registrationId < 1) {
    api_response(422, [
        'success' => false,
        'message' => 'ID vé không hợp lệ.'
    ]);
}

try {
    $pdo->beginTransaction();

    // --- 3. Khóa vé cần hủy ---
    $stmtTicket = $pdo->prepare('
        SELECT id, su_kien_id, nguoi_dung_id, loai_ve, trang_thai
        FROM ve
        WHERE id = :id
        FOR UPDATE
    ');
    $stmtTicket->execute([':id' => $registrationId]);
    $ticket = $stmtTicket->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        $pdo->rollBack();
        api_response(404, ['success' => false, 'message' => 'Không tìm thấy thông tin vé.']);
    }

    if ((int)$ticket['nguoi_dung_id'] !== $currentUserId && $currentRole !== 'admin') {
        $pdo->rollBack();
        api_response(403, ['success' => false, 'message' => 'Bạn không có quyền hủy vé này.']);
    }

    if ($ticket['trang_thai'] === 'da-huy') {
        $pdo->rollBack();
        api_response(422, ['success' => false, 'message' => 'Vé này đã được hủy trước đó.']);
    }

    if ($ticket['trang_thai'] === 'da-checkin') {
        $pdo->rollBack();
        api_response(422, ['success' => false, 'message' => 'Không thể hủy vé đã được check-in.']);
    }

    $eventId  = (int)$ticket['su_kien_id'];
    $tierName = (string)$ticket['loai_ve'];

    // --- 4. Khóa sự kiện (cùng thứ tự khóa với process_checkout.php để tránh deadlock) ---
    $stmtEvent = $pdo->prepare('
        SELECT id, thoi_gian, so_luong_toi_da
        FROM sukien
        WHERE id = :id
        FOR UPDATE
    ');
    $stmtEvent->execute([':id' => $eventId]);
    $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        $eventStart = strtotime((string)$event['thoi_gian']);
        if ($eventStart !== false && $eventStart <= time()) {
            $pdo->rollBack();
            api_response(422, [
                'success' => false,
                'message' => 'Không thể hủy vé khi sự kiện đã bắt đầu hoặc kết thúc.'
            ]);
        }
    }

    // --- 5. Hủy vé ---
    $stmtCancel = $pdo->prepare("
        UPDATE ve
        SET trang_thai = 'da-huy'
        WHERE id = :id AND trang_thai <> 'da-huy'
    ");
    $stmtCancel->execute([':id' => $registrationId]);

    // --- 6. Khôi phục số lượng vé khả dụng của hạng vé ---
    $tierRemaining = null;
    $stmtTier = $pdo->prepare('
        SELECT id, so_luong, da_het_ve
        FROM loaive
        WHERE su_kien_id = :su_kien_id AND ten_loai_ve = :ten_loai_ve
        LIMIT 1
        FOR UPDATE
    ');
    $stmtTier->execute([':su_kien_id' => $eventId, ':ten_loai_ve' => $tierName]);
    $tier = $stmtTier->fetch(PDO::FETCH_ASSOC);

    if ($tier) {
        $stmtTierSold = $pdo->prepare("
            SELECT COUNT(*)
            FROM ve
            WHERE su_kien_id = :su_kien_id AND loai_ve = :loai_ve AND trang_thai <> 'da-huy'
        ");
        $stmtTierSold->execute([':su_kien_id' => $eventId, ':loai_ve' => $tierName]);
        $tierSold = (int)$stmtTierSold->fetchColumn();
        $tierMax  = (int)$tier['so_luong'];

        if ($tierMax > 0) {
            $tierRemaining = max(0, $tierMax - $tierSold);
        }

        // Mở bán lại hạng vé nếu đang bị đánh dấu hết vé và nay đã có chỗ trống
        if ((int)$tier['da_het_ve'] === 1 && ($tierMax === 0 || $tierSold < $tierMax)) {
            $stmtReopen = $pdo->prepare('UPDATE loaive SET da_het_ve = 0 WHERE id = :id');
            $stmtReopen->execute([':id' => (int)$tier['id']]);
        }
    }

    // --- 7. Tính số vé còn lại của sự kiện sau khi hủy ---
    $eventRemaining = null;
    if ($event && (int)$event['so_luong_toi_da'] > 0) {
        $stmtEventSold = $pdo->prepare("
            SELECT COUNT(*) FROM ve WHERE su_kien_id = :su_kien_id AND trang_thai <> 'da-huy'
        ");
        $stmtEventSold->execute([':su_kien_id' => $eventId]);
        $eventRemaining = max(0, (int)$event['so_luong_toi_da'] - (int)$stmtEventSold->fetchColumn());
    }

    $pdo->commit();

    // Đồng bộ dữ liệu vé lưu tạm trong session (nếu có)
    if (!empty($_SESSION['my_tickets']) && is_array($_SESSION['my_tickets'])) {
        foreach ($_SESSION['my_tickets'] as $i => $sessTicket) {
            if ((int)($sessTicket['ticket_db_id'] ?? 0) === $registrationId) {
                $_SESSION['my_tickets'][$i]['status'] = 'da-huy';
            }
        }
    }

    api_response(200, [
        'success' => true,
        'message' => 'Hủy vé thành công.',
        'data'    => [
            'id'              => $registrationId,
            'trang_thai'      => 'da-huy',
            'event_remaining' => $eventRemaining,
            'tier_remaining'  => $tierRemaining
        ]
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[TicketBox Registration Cancel] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ, vui lòng thử lại sau.'
    ]);
}
