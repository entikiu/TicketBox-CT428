<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Nếu chưa đăng nhập -> Ép buộc chuyển hướng sang trang đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 2. Xử lý lưu vé khi gửi Form Mua Vé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_title = $_POST['event_title'] ?? 'Sự kiện Ticketbox';
    $category = $_POST['category'] ?? 'Vé Tiêu Chuẩn';
    $price = $_POST['price'] ?? '500.000 đ';
    $location = $_POST['location'] ?? 'Hà Nội';
    $date = $_POST['date'] ?? '15.11.2026';
    $image = $_POST['image'] ?? '../assets/images/events/banner-tinh-ha.jpg';

    // Khởi tạo danh sách vé cá nhân trong Session nếu chưa có
    if (!isset($_SESSION['my_tickets'])) {
        $_SESSION['my_tickets'] = [];
    }

    // Tạo mã vé động ngẫu nhiên
    $ticket_id = 'TKB-' . date('Y') . '-' . rand(1000, 9999);

    // Thêm vé mới mua vào đầu danh sách Session
    array_unshift($_SESSION['my_tickets'], [
        'id' => $ticket_id,
        'event_title' => $event_title,
        'ticket_category' => $category,
        'price' => $price,
        'date' => $date,
        'location' => $location,
        'status' => 'success',   // Trạng thái mua thành công
        'time' => 'upcoming',     // Sắp diễn ra
        'image' => $image,
        'created_at' => date('H:i d/m/Y')
    ]);

    // Mua thành công -> Tự động chuyển thẳng tới trang "Vé của tôi"
    header('Location: my-tickets.php');
    exit;
}

header('Location: home.php');
exit;