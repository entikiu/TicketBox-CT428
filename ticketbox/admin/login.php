<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mat_khau = $_POST['mat_khau'] ?? '';

    if (empty($email) || empty($mat_khau)) {
        $error = 'Vui lòng điền đầy đủ email và mật khẩu.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM nguoidung WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $db_pass = $user['mat_khau'] ?? $user['password'] ?? '';
                
                // Kiểm tra mật khẩu (hỗ trợ cả mã hóa password_hash lẫn chuỗi thường)
                $pass_match = false;
                if (!empty($db_pass) && (password_verify($mat_khau, $db_pass) || $mat_khau === $db_pass)) {
                    $pass_match = true;
                }

                if ($pass_match) {
                    // Kiểm tra quyền Admin (dùng strtolower để không phân biệt 'Admin' hay 'admin')
                    $vai_tro = strtolower(trim($user['vai_tro'] ?? ''));
                    if ($vai_tro === 'admin') {
                        $_SESSION['admin'] = [
                            'id' => $user['id'],
                            'ho_ten' => $user['ho_ten'] ?? $user['name'],
                            'email' => $user['email']
                        ];
                        header('Location: index.php');
                        exit;
                    } else {
                        $error = 'Tài khoản này không có quyền quản trị (Vai trò hiện tại: ' . htmlspecialchars($user['vai_tro']) . ').';
                    }
                } else {
                    $error = 'Mật khẩu không chính xác.';
                }
            } else {
                $error = 'Không tìm thấy tài khoản với email này trong CSDL.';
            }
        } catch (PDOException $e) {
            $error = 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <title>Đăng nhập Quản trị - TicketBox</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Montserrat', sans-serif; }
        .admin-login-card { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 400px; }
        .admin-login-card h2 { text-align: center; color: #26c973; margin-bottom: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        .btn-admin-login { width: 100%; background: #26c973; color: #fff; border: none; padding: 12px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .btn-admin-login:hover { background: #21b063; }
        .alert-error { background: #ffe6e6; color: #d9534f; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
    <div class="admin-login-card">
        <h2><i class="fa-solid fa-lock"></i> TicketBox Admin</h2>
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Email quản trị</label>
                <input type="email" name="email" required value="nguyenhuynth2910@gmail.com" placeholder="Nhập email của bạn">
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="mat_khau" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-admin-login">Đăng nhập Quản trị</button>
        </form>
    </div>
</body>
</html>