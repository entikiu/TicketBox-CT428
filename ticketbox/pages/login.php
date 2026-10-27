<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Xử lý khi bấm nút Đăng Nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (!empty($email)) {
        // Lưu thông tin user giả lập vào Session
        $_SESSION['user'] = [
            'name' => 'Nguyễn Thanh Huy',
            'email' => $email
        ];
        header('Location: home.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Ticketbox</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body style="background: #22242a;">

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Đăng nhập</h2>
            <a href="home.php" style="color:#fff; font-size:20px;">✕</a>
        </div>
        <div class="auth-body">
            <form action="login.php" method="POST">
                <div class="form-group">
                    <input type="text" name="email" class="form-input" placeholder="Nhập email hoặc số điện thoại" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-input" placeholder="Nhập mật khẩu" required>
                </div>
                
                <button type="submit" class="btn-primary-auth active">Tiếp tục</button>
            </form>

            <div class="auth-links" style="margin-top: 20px;">
                <p>Chưa có tài khoản? <a href="register.php">Tạo tài khoản ngay</a></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>