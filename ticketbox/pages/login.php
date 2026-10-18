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
            <form action="#" method="POST">
                <div class="form-group">
                    <input type="text" class="form-input" placeholder="Nhập email hoặc số điện thoại">
                </div>
                <div class="form-group">
                    <input type="password" class="form-input" placeholder="Nhập mật khẩu">
                </div>
                
                <button type="submit" class="btn-primary-auth">Tiếp tục</button>
            </form>

            <div class="auth-links">
                <p><a href="#" style="color:#666;">Quên mật khẩu?</a></p>
                <p style="margin-top:10px;">Chưa có tài khoản? <a href="register.php">Tạo tài khoản ngay</a></p>
            </div>

            <div class="divider">Hoặc</div>

            <button class="btn-google">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" width="18" alt="Google">
                Đăng nhập bằng Google
            </button>
        </div>
    </div>
</div>

</body>
</html>