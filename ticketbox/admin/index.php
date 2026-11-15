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
$total_users = 0;
$total_events = 0;
$total_tickets = 0;

try {
    // Đếm số lượng người dùng
    $stmt = $pdo->query("SELECT COUNT(*) FROM nguoidung");
    $total_users = $stmt->fetchColumn();

    // Đếm số lượng sự kiện (giả định bảng sự kiện là 'sukien' hoặc 'events')
    $stmt = $pdo->query("SELECT COUNT(*) FROM sukien");
    $total_events = $stmt->fetchColumn();
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
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #26c973;
            --bg-color: #f8f9fa;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; background: var(--bg-color); min-height: 100vh; }
        
        /* Sidebar */
        .admin-sidebar { width: var(--sidebar-width); background: #1e293b; color: #fff; display: flex; flex-direction: column; position: fixed; height: 100%; }
        .sidebar-brand { padding: 20px; font-size: 20px; font-weight: bold; background: #0f172a; color: var(--primary-color); text-align: center; letter-spacing: 1px; }
        .sidebar-menu { list-style: none; padding: 20px 0; flex: 1; }
        .sidebar-menu li a { display: flex; align-items: center; gap: 12px; padding: 14px 24px; color: #94a3b8; text-decoration: none; font-size: 15px; transition: 0.2s; }
        .sidebar-menu li.active a, .sidebar-menu li a:hover { background: #334155; color: #fff; border-left: 4px solid var(--primary-color); }
        
        /* Main Content */
        .admin-main { margin-left: var(--sidebar-width); flex: 1; display: flex; flex-direction: column; }
        .admin-header { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .admin-user-info { display: flex; align-items: center; gap: 10px; font-weight: 600; color: #333; }
        .btn-logout { background: #ef4444; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 13px; }
        .btn-logout:hover { background: #dc2626; }
        
        .admin-content { padding: 30px; }
        .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card-stat { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; }
        .card-stat-icon { width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; background: var(--primary-color); }
        .card-stat-info h3 { font-size: 24px; color: #1e293b; margin-bottom: 4px; }
        .card-stat-info p { color: #64748b; font-size: 14px; }

        .welcome-box { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .welcome-box h2 { color: #1e293b; margin-bottom: 10px; }
        .welcome-box p { color: #64748b; line-height: 1.6; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="admin-sidebar">
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
            <h3>Bảng điều khiển quản trị</h3>
            <div class="admin-user-info">
                <span><i class="fa-solid fa-user-shield"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['admin']['ho_ten']); ?></span>
                <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </header>

        <div class="admin-content">
            <!-- Thẻ thống kê nhanh -->
            <div class="dashboard-cards">
                <div class="card-stat">
                    <div class="card-stat-icon" style="background: #3b82f6;"><i class="fa-solid fa-users"></i></div>
                    <div class="card-stat-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Thành viên hệ thống</p>
                    </div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-icon" style="background: #10b981;"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="card-stat-info">
                        <h3><?php echo $total_events; ?></h3>
                        <p>Sự kiện đang mở</p>
                    </div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-icon" style="background: #f59e0b;"><i class="fa-solid fa-ticket-simple"></i></div>
                    <div class="card-stat-info">
                        <h3>0</h3>
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

</body>
</html>