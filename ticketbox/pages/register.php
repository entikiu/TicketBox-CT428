<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}

$errors = [
    'fullname' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => '',
    'terms' => ''
];

$fullname = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);

    // 1. Kiểm tra Họ và tên
    if (empty($fullname)) {
        $errors['fullname'] = 'Vui lòng nhập họ và tên.';
    }

    // 2. Kiểm tra Email / SĐT
    if (empty($email)) {
        $errors['email'] = 'Vui lòng nhập email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ.';
    }

    // 3. KIỂM TRA MẬT KHẨU CHUẨN
    $p_len = strlen($password);
    $has_lower = preg_match('/[a-z]/', $password);
    $has_upper = preg_match('/[A-Z]/', $password);
    $has_num   = preg_match('/[0-9]/', $password);
    $has_spec  = preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\\\|,.<>\/?]/', $password);

    if (empty($password)) {
        $errors['password'] = 'Vui lòng tạo mật khẩu.';
    } elseif ($p_len < 8 || $p_len > 32 || !$has_lower || !$has_num || !$has_upper || !$has_spec) {
        $errors['password'] = 'Mật khẩu chưa đáp ứng đầy đủ các quy chuẩn an toàn.';
    }

    // 4. Kiểm tra Nhập lại Mật khẩu
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Xác nhận mật khẩu không trùng khớp.';
    }

    // 5. Điều khoản
    if (!$terms) {
        $errors['terms'] = 'Bạn cần đồng ý với điều khoản sử dụng.';
    }

    // Nếu tất cả các trường hợp lệ
    if (!array_filter($errors)) {
        if (!isset($_SESSION['registered_users'])) {
            $_SESSION['registered_users'] = [];
        }

        $exists = false;
        foreach ($_SESSION['registered_users'] as $u) {
            if ($u['email'] === $email) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $errors['email'] = 'Email này đã được đăng ký.';
        } else {
            $_SESSION['registered_users'][] = [
                'name' => $fullname,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT)
            ];

            header('Location: login.php?registered=success');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - Ticketbox</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Tạo tài khoản</h2>
            <a href="home.php" class="close-btn" title="Đóng">&times;</a>
        </div>

        <div class="auth-body">

<div id="apiAlertError" class="alert alert-danger" style="display: none; margin-bottom: 15px;"></div>
<div id="apiAlertSuccess" class="alert alert-success" style="display: none; margin-bottom: 15px;"></div>

<form action="register.php" method="POST" id="registerForm" novalidate>
    <!-- Họ và tên -->
    <div class="form-group <?php echo !empty($errors['fullname']) ? 'has-error' : ''; ?>">
        <label class="form-label">Họ và tên</label>
        <input type="text" id="regFullname" name="fullname" class="form-input" placeholder="Nhập họ và tên" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
        <span class="field-error" id="error-regFullname"><?php echo $errors['fullname'] ?? ''; ?></span>
    </div>

    <!-- Email / SĐT -->
    <div class="form-group <?php echo !empty($errors['email']) ? 'has-error' : ''; ?>">
        <label class="form-label">Email </label>
        <input type="text" id="regEmail" name="email" class="form-input" placeholder="Nhập email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        <span class="field-error" id="error-regEmail"><?php echo $errors['email'] ?? ''; ?></span>
    </div>

    <!-- Mật khẩu -->
    <div class="form-group <?php echo !empty($errors['password']) ? 'has-error' : ''; ?>">
        <label class="form-label">Mật khẩu</label>
        <div class="password-wrapper">
            <input type="password" id="regPassword" name="password" class="form-input" placeholder="Tạo mật khẩu">
            <i class="fa-regular fa-eye toggle-password" onclick="toggleShowPassword('regPassword', this)"></i>
        </div>

        <!-- BẢNG GỢI Ý TIÊU CHUẨN MẬT KHẨU -->
        <div class="pwd-requirements-box" id="pwdRequirementsBox">
            <div class="pwd-req-title" id="pwdReqTitle">
                <i class="fa-solid fa-circle-xmark"></i> Mật khẩu chưa hợp lệ
            </div>
            <ul class="pwd-req-list">
                <li id="req-length" class="invalid">
                    <i class="fa-solid fa-circle-xmark icon-status"></i> Từ 8 - 32 ký tự
                </li>
                <li id="req-lower-num" class="invalid">
                    <i class="fa-solid fa-circle-xmark icon-status"></i> Bao gồm chữ thường và số
                </li>
                <li id="req-special" class="invalid">
                    <i class="fa-solid fa-circle-xmark icon-status"></i> Bao gồm ký tự đặc biệt (!, $, @, %, ...)
                </li>
                <li id="req-upper" class="invalid">
                    <i class="fa-solid fa-circle-xmark icon-status"></i> Có ít nhất 1 ký tự in hoa
                </li>
            </ul>
        </div>

        <span class="field-error" id="error-regPassword"><?php echo $errors['password'] ?? ''; ?></span>
    </div>

    <!-- Xác nhận Mật khẩu -->
    <div class="form-group <?php echo !empty($errors['confirm_password']) ? 'has-error' : ''; ?>">
        <label class="form-label">Xác nhận mật khẩu</label>
        <div class="password-wrapper">
            <input type="password" id="regConfirmPassword" name="confirm_password" class="form-input" placeholder="Nhập lại mật khẩu">
            <i class="fa-regular fa-eye toggle-password" onclick="toggleShowPassword('regConfirmPassword', this)"></i>
        </div>
        <span class="field-error" id="error-regConfirmPassword"><?php echo $errors['confirm_password'] ?? ''; ?></span>
    </div>

    <!-- Điều khoản -->
    <div class="form-checkbox-group <?php echo !empty($errors['terms']) ? 'has-error' : ''; ?>">
        <label class="checkbox-container">
            <input type="checkbox" id="regTerms" name="terms">
            <span class="checkmark"></span>
            Tôi đồng ý với <a href="terms.php" style="color: var(--primary-color, #26c973);">Điều khoản & Quy định</a> của Ticketbox.
        </label>
        <span class="field-error" id="error-regTerms"><?php echo $errors['terms'] ?? ''; ?></span>
    </div>

    <!-- Nút Đăng Ký -->
    <button type="submit" id="btnRegisterSubmit" class="btn-primary-auth" disabled>Đăng ký</button>
</form>

<script src="../assets/js/auth.js"></script>

            <div class="auth-links">
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/auth.js"></script>

</body>
</html>