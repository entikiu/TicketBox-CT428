<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';
// Xử lý đổi quyền hoặc xóa người dùng nếu có yêu cầu POST/GET
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete' && $id != $_SESSION['admin']['id']) {
        try {
            $stmt = $pdo->prepare("DELETE FROM nguoidung WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Đã xóa người dùng thành công!';
        } catch (PDOException $e) {
            $message = 'Lỗi khi xóa: ' . $e->getMessage();
        }
    }
}

// Lấy danh sách người dùng
try {
    $stmt = $pdo->query("SELECT * FROM nguoidung ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <title>Quản lý Người dùng - TicketBox Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --primary-color: #26c973; --bg-color: #f8f9fa; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; background: var(--bg-color); min-height: 100vh; }
        .admin-sidebar { width: var(--sidebar-width); background: #1e293b; color: #fff; display: flex; flex-direction: column; position: fixed; height: 100%; }
        .sidebar-brand { padding: 20px; font-size: 20px; font-weight: bold; background: #0f172a; color: var(--primary-color); text-align: center; }
        .sidebar-menu { list-style: none; padding: 20px 0; flex: 1; }
        .sidebar-menu li a { display: flex; align-items: center; gap: 12px; padding: 14px 24px; color: #94a3b8; text-decoration: none; font-size: 15px; transition: 0.2s; }
        .sidebar-menu li.active a, .sidebar-menu li a:hover { background: #334155; color: #fff; border-left: 4px solid var(--primary-color); }
        .admin-main { margin-left: var(--sidebar-width); flex: 1; display: flex; flex-direction: column; }
        .admin-header { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .admin-user-info { display: flex; align-items: center; gap: 10px; font-weight: 600; color: #333; }
        .btn-logout { background: #ef4444; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 13px; }
        .admin-content { padding: 30px; }
        .card-table { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #334155; }
        th { background: #f8fafc; font-weight: 600; color: #475569; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-admin { background: #dbeafe; color: #1d4ed8; }
        .badge-user { background: #f1f5f9; color: #475569; }
        .btn-action { padding: 6px 10px; border-radius: 4px; color: #fff; text-decoration: none; font-size: 12px; margin-right: 4px; }
        .btn-delete { background: #ef4444; }
        .btn-delete:hover { background: #dc2626; }
        .alert { padding: 12px; background: #d1fae5; color: #065f46; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="admin-sidebar">
        <div class="sidebar-brand">TicketBox Admin</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="events.php"><i class="fa-solid fa-calendar-days"></i> Quản lý Sự kiện</a></li>
            <li class="active"><a href="users.php"><i class="fa-solid fa-users"></i> Quản lý Người dùng</a></li>
            <li><a href="orders.php"><i class="fa-solid fa-ticket"></i> Quản lý Đơn hàng/Vé</a></li>
            <li><a href="../pages/home.php" target="_blank"><i class="fa-solid fa-globe"></i> Xem Website chính</a></li>
        </ul>
    </div>

    <div class="admin-main">
        <header class="admin-header">
            <h3>Quản lý Người dùng hệ thống</h3>
            <div class="admin-user-info">
                <span><i class="fa-solid fa-user-shield"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['admin']['ho_ten']); ?></span>
                <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </header>

        <div class="admin-content">
            <?php if (!empty($message)): ?>
                <div class="alert"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="card-table">
                <h3>Danh sách thành viên (<?php echo count($users); ?>)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>#<?php echo $u['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['ho_ten'] ?? $u['name'] ?? 'Chưa cập nhật'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <?php $role = $u['vai_tro'] ?? 'user'; ?>
                                        <span class="badge <?php echo ($role === 'admin') ? 'badge-admin' : 'badge-user'; ?>">
                                            <?php echo ucfirst($role); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['id'] != $_SESSION['admin']['id']): ?>
                                            <a href="users.php?action=delete&id=<?php echo $u['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?');">
                                                <i class="fa-solid fa-trash"></i> Xóa
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 12px;">Tài khoản hiện tại</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b;">Chưa có người dùng nào trong hệ thống.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>