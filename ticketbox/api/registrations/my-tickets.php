<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/validation.php';

require_method('GET');
require_roles(['user', 'attendee', 'customer', 'member', 'organizer', 'admin']);

$userId = (int)$_SESSION['user_id'];
$status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';

// Giả định trạng thái trong bảng `ve` (ví dụ từ ảnh là "thanh-cong")
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
            e.thoi_gian
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

    api_response(200, [
        'success' => true,
        'data' => $tickets
    ]);

} catch (PDOException $e) {
    error_log('[TicketBox My Tickets] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ.'
    ]);
}