<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';

require_method('GET');
require_roles(['user', 'attendee', 'customer', 'member', 'organizer', 'admin']);

$userId = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
$status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';

$allowedStatuses = ['thanh-cong', 'da-huy', 'da-checkin'];
if ($status !== '' && !in_array($status, $allowedStatuses, true)) {
    api_response(422, [
        'success' => false,
        'message' => 'Trạng thái lọc vé không hợp lệ.'
    ]);
}

try {
    $sql = "
        SELECT 
            r.id AS registration_id,
            r.ma_ve,
            r.trang_thai,
            r.ngay_dat,
            r.loai_ve,
            r.gia_ve,
            e.id AS event_id,
            e.ten_su_kien,
            e.dia_diem,
            e.ten_dia_diem,
            e.thoi_gian,
            e.thoi_gian_hien_thi,
            e.ten_ban_to_chuc,
            e.hinh_anh
        FROM ve r
        INNER JOIN sukien e ON r.su_kien_id = e.id
        WHERE r.nguoi_dung_id = :user_id
    ";

    $params = [':user_id' => $userId];

    if ($status !== '') {
        $sql .= " AND r.trang_thai = :status";
        $params[':status'] = $status;
    }

    $sql .= " ORDER BY r.ngay_dat DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtMem = $pdo->prepare("
        SELECT 
            m.id,
            m.ma_the,
            m.the_id,
            m.ngay_kich_hoat,
            m.ngay_het_han,
            m.trang_thai,
            t.*
        FROM the_thanh_vien_nguoidung m
        INNER JOIN the_thanh_vien t ON m.the_id = t.id
        WHERE m.nguoi_dung_id = :user_id
        ORDER BY m.ngay_kich_hoat DESC
    ");
    $stmtMem->execute([':user_id' => $userId]);
    $memberships = $stmtMem->fetchAll(PDO::FETCH_ASSOC);

    api_response(200, [
        'success'     => true,
        'data'        => $tickets,
        'memberships' => $memberships
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox My Tickets] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ.'
    ]);
}