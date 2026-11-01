<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}

$errors = [
    'email' => '',
    'password' => ''
];
$success_msg = isset($_GET['registered']) ? 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.' : '';

// XỬ LÝ ĐĂNG NHẬP SERVER-SIDE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'normal_login') {
    $email_phone = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Kiểm tra từng ô
    if (empty($email_phone)) {
        $errors['email'] = 'Vui lòng nhập email hoặc số điện thoại.';
    } elseif (!filter_var($email_phone, FILTER_VALIDATE_EMAIL) && !preg_match('/^(0[3|5|7|8|9])+([0-9]{8})$/', $email_phone)) {
        $errors['email'] = 'Email hoặc số điện thoại không hợp lệ.';
    }

    if (empty($password)) {
        $errors['password'] = 'Vui lòng nhập mật khẩu.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Mật khẩu phải chứa ít nhất 6 ký tự.';
    }

    // Nếu không có lỗi thì tiến hành đăng nhập
    if (empty($errors['email']) && empty($errors['password'])) {
        $users_db = $_SESSION['registered_users'] ?? [];
        $found_user = null;

        foreach ($users_db as $u) {
            if ($u['email'] === $email_phone && password_verify($password, $u['password_hash'])) {
                $found_user = $u;
                break;
            }
        }

        if ($found_user) {
            $_SESSION['user'] = [
                'name' => $found_user['name'],
                'email' => $found_user['email']
            ];
        } else {
            $_SESSION['user'] = [
                'name' => explode('@', $email_phone)[0],
                'email' => $email_phone
            ];
        }

        header('Location: home.php');
        exit;
    }
}

// XỬ LÝ ĐĂNG NHẬP GOOGLE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'google_login') {
    $gg_name = $_POST['google_name'] ?? 'Người dùng Google';
    $gg_email = $_POST['google_email'] ?? '';

    if (!empty($gg_email)) {
        $_SESSION['user'] = [
            'name' => $gg_name,
            'email' => $gg_email
        ];
        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Ticketbox</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>

<body>

    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Đăng nhập</h2>
                <a href="home.php" class="close-btn" title="Đóng">&times;</a>
            </div>

            <div class="auth-body">
                <?php if (!empty($success_msg)): ?>
                    <div class="auth-alert success-alert">
                        <i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <!-- pages/login.php -->
                <div id="apiAlertError" class="alert alert-danger" style="display: none; margin-bottom: 15px;"></div>
                <div id="apiAlertSuccess" class="alert alert-success" style="display: none; margin-bottom: 15px;"></div>

                <form action="login.php" method="POST" id="loginForm" novalidate>
                    <input type="hidden" name="action" value="normal_login">

                    <!-- Ô Nhập Email / SĐT -->
                    <div class="form-group <?php echo !empty($errors['email']) ? 'has-error' : ''; ?>" id="group-email">
                        <label class="form-label">Email hoặc Số điện thoại</label>
                        <input type="text" id="loginEmail" name="email" class="form-input" placeholder="Nhập email hoặc SĐT" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <span class="field-error" id="error-loginEmail"><?php echo $errors['email'] ?? ''; ?></span>
                    </div>

                    <!-- Ô Nhập Mật khẩu -->
                    <div class="form-group <?php echo !empty($errors['password']) ? 'has-error' : ''; ?>" id="group-password">
                        <label class="form-label">Mật khẩu</label>
                        <div class="password-wrapper">
                            <input type="password" id="loginPassword" name="password" class="form-input" placeholder="Nhập mật khẩu">
                            <i class="fa-regular fa-eye toggle-password" onclick="toggleShowPassword('loginPassword', this)"></i>
                        </div>
                        <span class="field-error" id="error-loginPassword"><?php echo $errors['password'] ?? ''; ?></span>
                    </div>

                    <!-- Nút Tiếp Tục (Ban đầu mờ xám, tự động sáng khi nhập đủ thông tin) -->
                    <button type="submit" id="btnLoginSubmit" class="btn-primary-auth" disabled>Tiếp tục</button>
                </form>


                <div class="auth-divider">
                    <span>Hoặc đăng nhập bằng</span>
                </div>

                <div class="google-login-box">
                    <div id="g_id_onload"
                        data-client_id="YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com"
                        data-callback="handleCredentialResponse"
                        data-auto_prompt="false">
                    </div>
                    <div class="g_id_signin" data-type="standard" data-shape="rectangular" data-theme="filled_blue" data-text="signin_with" data-size="large" data-logo_alignment="left" data-width="100%"></div>
                </div>

                <div class="auth-links">
                    <p>Chưa có tài khoản? <a href="register.php">Tạo tài khoản ngay</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/auth.js"></script>

</body>

</html>