<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, [
        'success' => false,
        'message' => 'Phương thức không hợp lệ.'
    ]);
}

$userId = (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
    respond(401, [
        'success' => false,
        'message' => 'Vui lòng đăng nhập trước.'
    ]);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    $data = $_POST;
}

$eventId  = filter_var($data['event_id'] ?? $data['su_kien_id'] ?? null, FILTER_VALIDATE_INT);
$loaiVeId = filter_var($data['loai_ve_id'] ?? $data['tier_id'] ?? null, FILTER_VALIDATE_INT);
$rawTier  = trim((string)($data['loai_ve'] ?? $data['category'] ?? ''));

if (!$eventId || $eventId <= 0) {
    respond(422, [
        'success' => false,
        'message' => 'ID sự kiện không hợp lệ.'
    ]);
}

try {
    $pdo->beginTransaction();

    // 1. Khóa sự kiện để kiểm tra tồn tại, thời gian và tổng sức chứa
    $stmt = $pdo->prepare(
        'SELECT id, thoi_gian, so_luong_toi_da
         FROM sukien
         WHERE id = :id
         FOR UPDATE'
    );
    $stmt->execute([':id' => $eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $pdo->rollBack();
        respond(404, [
            'success' => false,
            'message' => 'Không tìm thấy sự kiện.'
        ]);
    }

    if (strtotime((string)$event['thoi_gian']) <= time()) {
        $pdo->rollBack();
        respond(409, [
            'success' => false,
            'message' => 'Sự kiện đã bắt đầu hoặc kết thúc.'
        ]);
    }

    // 2. Khóa dòng đăng ký hiện tại của user (nếu có) để chống trùng khi bấm đúp
    $stmtReg = $pdo->prepare(
        'SELECT id, trang_thai
         FROM ve
         WHERE nguoi_dung_id = :user_id
           AND su_kien_id = :event_id
         FOR UPDATE'
    );
    $stmtReg->execute([
        ':user_id'  => $userId,
        ':event_id' => $eventId
    ]);
    $registration = $stmtReg->fetch(PDO::FETCH_ASSOC);

    if ($registration && $registration['trang_thai'] !== 'da-huy') {
        $pdo->rollBack();
        respond(409, [
            'success' => false,
            'message' => 'Bạn đã đăng ký sự kiện này.'
        ]);
    }

    // 3. Kiểm tra tổng số vé đang chiếm chỗ của sự kiện
    $stmtCount = $pdo->prepare(
        "SELECT COUNT(*)
         FROM ve
         WHERE su_kien_id = :event_id
           AND trang_thai IN ('thanh-cong', 'da-checkin')"
    );
    $stmtCount->execute([':event_id' => $eventId]);
    $registeredCount = (int)$stmtCount->fetchColumn();

    if ((int)$event['so_luong_toi_da'] > 0 && $registeredCount >= (int)$event['so_luong_toi_da']) {
        $pdo->rollBack();
        respond(409, [
            'success' => false,
            'message' => 'Sự kiện đã hết vé.'
        ]);
    }

    // 4. Đối chiếu hạng vé trong bảng `loaive`
    $ticketTier = null;
    if ($loaiVeId && $loaiVeId > 0) {
        $stmtTier = $pdo->prepare(
            'SELECT id, ten_loai_ve, gia_ve, da_het_ve, so_luong
             FROM loaive
             WHERE id = :id AND su_kien_id = :event_id
             FOR UPDATE'
        );
        $stmtTier->execute([
            ':id'       => $loaiVeId,
            ':event_id' => $eventId
        ]);
        $ticketTier = $stmtTier->fetch(PDO::FETCH_ASSOC) ?: null;
    } else {
        $stmtTiers = $pdo->prepare(
            'SELECT id, ten_loai_ve, gia_ve, da_het_ve, so_luong
             FROM loaive
             WHERE su_kien_id = :event_id
             ORDER BY gia_ve ASC, id ASC
             FOR UPDATE'
        );
        $stmtTiers->execute([':event_id' => $eventId]);
        $tiers = $stmtTiers->fetchAll(PDO::FETCH_ASSOC);

        if ($rawTier !== '') {
            foreach ($tiers as $t) {
                if (mb_strtolower(trim((string)$t['ten_loai_ve']), 'UTF-8') === mb_strtolower($rawTier, 'UTF-8')) {
                    $ticketTier = $t;
                    break;
                }
            }
        } elseif (!empty($tiers)) {
            // Mặc định chọn hạng vé đầu tiên nếu sự kiện có cấu hình bảng `loaive`
            $ticketTier = $tiers[0];
        }
    }

    $loaiVe   = 'Vé Tiêu Chuẩn';
    $giaSo    = 0;
    $tierSold = 0;
    $tierMax  = 0;

    if ($ticketTier) {
        $loaiVe  = mb_substr(trim((string)$ticketTier['ten_loai_ve']), 0, 100, 'UTF-8');
        $giaSo   = (int)$ticketTier['gia_ve'];
        $tierMax = (int)$ticketTier['so_luong'];

        if ((int)$ticketTier['da_het_ve'] === 1) {
            $pdo->rollBack();
            respond(409, [
                'success' => false,
                'message' => "Hạng vé \"{$loaiVe}\" đã hết."
            ]);
        }

        $stmtTierCount = $pdo->prepare(
            "SELECT COUNT(*)
             FROM ve
             WHERE su_kien_id = :event_id
               AND loai_ve = :loai_ve
               AND trang_thai IN ('thanh-cong', 'da-checkin')"
        );
        $stmtTierCount->execute([
            ':event_id' => $eventId,
            ':loai_ve'  => $loaiVe
        ]);
        $tierSold = (int)$stmtTierCount->fetchColumn();

        if ($tierMax > 0 && $tierSold >= $tierMax) {
            $pdo->rollBack();
            respond(409, [
                'success' => false,
                'message' => "Hạng vé \"{$loaiVe}\" đã hết."
            ]);
        }
    } elseif ($rawTier !== '') {
        $loaiVe = mb_substr($rawTier, 0, 100, 'UTF-8');
    }

    $giaVe      = $giaSo > 0 ? number_format($giaSo, 0, ',', '.') . ' đ' : '0 đ';
    $ticketCode = 'TKB-' . date('Ymd') . '-' . random_int(1000, 9999);

    // 5. Tạo vé mới hoặc kích hoạt lại vé đã hủy
    if ($registration) {
        $stmtSave = $pdo->prepare(
            "UPDATE ve
             SET trang_thai = 'thanh-cong',
                 ma_ve = :ma_ve,
                 loai_ve = :loai_ve,
                 gia_ve = :gia_ve,
                 ngay_dat = CURRENT_TIMESTAMP
             WHERE id = :id"
        );
        $stmtSave->execute([
            ':ma_ve'   => $ticketCode,
            ':loai_ve' => $loaiVe,
            ':gia_ve'  => $giaVe,
            ':id'      => (int)$registration['id']
        ]);
        $ticketId = (int)$registration['id'];
    } else {
        $stmtSave = $pdo->prepare(
            "INSERT INTO ve
             (nguoi_dung_id, su_kien_id, ma_ve, loai_ve, gia_ve, trang_thai, ngay_dat)
             VALUES (:user_id, :event_id, :ma_ve, :loai_ve, :gia_ve, 'thanh-cong', CURRENT_TIMESTAMP)"
        );
        $stmtSave->execute([
            ':user_id'  => $userId,
            ':event_id' => $eventId,
            ':ma_ve'    => $ticketCode,
            ':loai_ve'  => $loaiVe,
            ':gia_ve'   => $giaVe
        ]);
        $ticketId = (int)$pdo->lastInsertId();
    }

    // 6. Cập nhật cờ `da_het_ve = 1` nếu hạng vé vừa hết suất cuối
    if ($ticketTier && $tierMax > 0 && ($tierSold + 1) >= $tierMax) {
        $stmtSoldOut = $pdo->prepare('UPDATE loaive SET da_het_ve = 1 WHERE id = :id');
        $stmtSoldOut->execute([':id' => (int)$ticketTier['id']]);
    }

    $pdo->commit();

    respond(201, [
        'success' => true,
        'message' => 'Đăng ký vé thành công.',
        'data'    => [
            'id'          => $ticketId,
            'event_id'    => $eventId,
            'ticket_code' => $ticketCode,
            'loai_ve'     => $loaiVe,
            'gia_ve'      => $giaVe,
            'status'      => 'thanh-cong'
        ]
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[TicketBox Registration Register] ' . $e->getMessage());
    respond(500, [
        'success' => false,
        'message' => 'Lỗi hệ thống, vui lòng thử lại.'
    ]);
}