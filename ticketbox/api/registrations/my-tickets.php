<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

try {
    // 2. Truy vấn danh sách vé kèm thông tin sự kiện
    $sql = "SELECT 
                v.id AS ticket_id,
                v.ma_ve AS ticket_code,
                v.loai_ve AS category,
                v.gia_ve AS price,
                v.trang_thai AS status,
                v.ngay_dat AS booked_at,
                s.id AS event_id,
                s.ten_su_kien AS event_title,
                s.thoi_gian AS event_time,
                s.dia_diem AS event_location,
                s.hinh_anh AS event_image
            FROM ve v
            JOIN sukien s ON v.su_kien_id = s.id
            WHERE v.nguoi_dung_id = :uid
            ORDER BY v.ngay_dat DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $user_id]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($tickets),
        'data' => $tickets
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}