<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Truy vấn bảng vé hoặc đơn hàng (điều chỉnh tên bảng cho khớp CSDL của bạn nếu cần, ví dụ: 've', 'dat_ve', 'orders')
try {
    $stmt = $pdo->query("SELECT * FROM ve ORDER BY id DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <title>Quản lý Đơn hàng & Vé - TicketBox Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">TicketBox Admin</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="events.php"><i class="fa-solid fa-calendar-days"></i> Quản lý Sự kiện</a></li>
            <li><a href="users.php"><i class="fa-solid fa-users"></i> Quản lý Người dùng</a></li>
            <li class="active"><a href="orders.php"><i class="fa-solid fa-ticket"></i> Quản lý Đơn hàng/Vé</a></li>
            <li><a href="../pages/home.php" target="_blank"><i class="fa-solid fa-globe"></i> Xem Website chính</a></li>
        </ul>
    </div>

    <div class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <button type="button" class="mobile-menu-toggle" id="sidebarToggle" aria-label="Mở menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h3>Quản lý Đơn hàng & Vé đã đặt</h3>
            </div>
            <div class="admin-user-info">
                <span><i class="fa-solid fa-user-shield"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['admin']['ho_ten']); ?></span>
                <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </header>

        <div class="admin-content">
            <div class="card-table">
                <h3>Danh sách vé đã bán (<?php echo count($orders); ?>)</h3>
                <table class="table-orders">
                    <thead>
                        <tr>
                            <th>Mã vé / ID</th>
                            <th>Mã sự kiện</th>
                            <th>Mã người dùng</th>
                            <th>Ngày đặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td>#<?php echo $ord['id']; ?></td>
                                    <td><?php echo htmlspecialchars($ord['su_kien_id'] ?? $ord['event_id'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($ord['nguoi_dung_id'] ?? $ord['user_id'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($ord['ngay_dat'] ?? $ord['created_at'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="empty-row">Chưa có đơn đặt vé nào trong hệ thống.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>