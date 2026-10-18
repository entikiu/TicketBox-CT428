<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản - Ticketbox</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body style="background: #22242a;">

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Đăng ký tài khoản</h2>
            <a href="home.php" style="color:#fff; font-size:20px;">✕</a>
        </div>
        <div class="auth-body">
            <button class="btn-google">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" width="18" alt="Google">
                Đăng ký bằng Google
            </button>

            <div class="divider">Hoặc</div>

            <form action="#" method="POST">
                <div class="form-group">
                    <input type="email" class="form-input" placeholder="Nhập email của bạn">
                </div>
                <div class="form-group">
                    <input type="password" id="reg-password" class="form-input" placeholder="Nhập mật khẩu">
                </div>
                <div class="form-group">
                    <input type="password" class="form-input" placeholder="Nhập lại mật khẩu">
                </div>

                <!-- Khung báo điều kiện mật khẩu -->
                <div class="password-rules">
                    <h4>✕ Mật khẩu chưa hợp lệ</h4>
                    <div class="rule-item">✕ Từ 8 - 32 ký tự</div>
                    <div class="rule-item">✕ Bao gồm chữ thường và số</div>
                    <div class="rule-item">✕ Bao gồm ký tự đặc biệt (!,$,@,%,...)</div>
                    <div class="rule-item">✕ Có ít nhất 1 ký tự in hoa</div>
                </div>

                <div class="auth-links" style="margin-bottom: 15px;">
                    Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
                </div>

                <button type="submit" class="btn-primary-auth">Tiếp tục</button>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/auth.js"></script>
</body>
</html>