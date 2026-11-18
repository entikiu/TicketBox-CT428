<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

// Kiểm tra quyền đăng nhập admin
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Lấy thống kê nhanh từ CSDL
$total_users    = 0;
$total_events   = 0;
$total_tickets  = 0;
$total_checkins = 0;

try {
    $total_users = (int)$pdo->query("SELECT COUNT(*) FROM nguoidung")->fetchColumn();
    $total_events = (int)$pdo->query("SELECT COUNT(*) FROM sukien")->fetchColumn();

    $ticketStats = $pdo->query("
        SELECT 
            SUM(CASE WHEN trang_thai IN ('thanh-cong', 'da-checkin') THEN 1 ELSE 0 END) AS sold,
            SUM(CASE WHEN trang_thai = 'da-checkin' THEN 1 ELSE 0 END) AS checked_in
        FROM ve
    ")->fetch(PDO::FETCH_ASSOC);

    $total_tickets  = (int)($ticketStats['sold'] ?? 0);
    $total_checkins = (int)($ticketStats['checked_in'] ?? 0);
} catch (PDOException $e) {
    // Bỏ qua nếu chưa có bảng tương ứng
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <title>Trang quản trị - TicketBox Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">TicketBox Admin</div>
        <ul class="sidebar-menu">
            <li class="active"><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="events.php"><i class="fa-solid fa-calendar-days"></i> Quản lý Sự kiện</a></li>
            <li><a href="users.php"><i class="fa-solid fa-users"></i> Quản lý Người dùng</a></li>
            <li><a href="orders.php"><i class="fa-solid fa-ticket"></i> Quản lý Đơn hàng/Vé</a></li>
            <li><a href="../pages/home.php" target="_blank"><i class="fa-solid fa-globe"></i> Xem Website chính</a></li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <button type="button" class="mobile-menu-toggle" id="sidebarToggle" aria-label="Mở menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h3>Bảng điều khiển quản trị</h3>
            </div>
            <div class="admin-user-info">
                <span><i class="fa-solid fa-user-shield"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['admin']['ho_ten']); ?></span>
                <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </header>

        <div class="admin-content">
            <!-- Thẻ thống kê nhanh -->
            <div class="dashboard-cards">
                <div class="card-stat">
                    <div class="card-stat-icon icon-blue"><i class="fa-solid fa-users"></i></div>
                    <div class="card-stat-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Thành viên hệ thống</p>
                    </div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-icon icon-green"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="card-stat-info">
                        <h3><?php echo $total_events; ?></h3>
                        <p>Sự kiện đang mở</p>
                    </div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-icon icon-amber"><i class="fa-solid fa-ticket-simple"></i></div>
                    <div class="card-stat-info">
                        <h3><?php echo $total_tickets; ?> <small style="font-size:13px;font-weight:normal;">(<?php echo $total_checkins; ?> check-in)</small></h3>
                        <p>Vé đã bán ra</p>
                    </div>
                </div>
            </div>

            <!-- Khung chào mừng -->
            <div class="welcome-box">
                <h2>Chào mừng bạn quay trở lại hệ thống quản trị TicketBox!</h2>
                <p>Từ bảng điều khiển này, bạn có thể dễ dàng theo dõi thông tin người dùng, kiểm duyệt các sự kiện mới được tạo, cũng như quản lý toàn bộ cơ sở dữ liệu của nền tảng bán vé trực tuyến.</p>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>

</html>