<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// 1. Nếu đã đăng nhập -> Chuyển hướng về trang chủ
if (isset($_SESSION['user']) && (int)($_SESSION['user']['id'] ?? 0) > 0) {
    header('Location: home.php');
    exit;
}

$errors = ['email' => '', 'password' => '', 'general' => ''];
$success_msg = isset($_GET['registered']) ? 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.' : '';

// 2. XỬ LÝ ĐĂNG NHẬP FORM THƯỜNG (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'normal_login') {
    $email_phone = trim($_POST['email'] ?? '');
    $password    = trim($_POST['password'] ?? '');

    if (empty($email_phone)) {
        $errors['email'] = 'Vui lòng nhập email hoặc số điện thoại.';
    }
    if (empty($password)) {
        $errors['password'] = 'Vui lòng nhập mật khẩu.';
    }

    if (empty($errors['email']) && empty($errors['password'])) {
        if (isset($pdo)) {
            try {
                // Truy vấn tìm tài khoản theo Email hoặc SĐT
                $sql  = "SELECT * FROM nguoidung WHERE email = :val OR phone = :val LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['val' => $email_phone]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $pass_hash = $user['mat_khau'] ?? '';

                    // Xác thực mật khẩu
                    if (password_verify($password, $pass_hash) || $password === $pass_hash) {

                        // NẠP ĐẦY ĐỦ CÁC TRƯỜNG DỮ LIỆU ĐỂ GIỮ THÔNG TIN VÀ HIỂN THỊ AVATAR NGAY KHI ĐĂNG NHẬP
                        $_SESSION['user'] = [
                            'id'       => (int)$user['id'],
                            'ho_ten'   => $user['ho_ten'] ?? '',
                            'name'     => $user['ho_ten'] ?? '',
                            'email'    => $user['email'] ?? '',
                            'phone'    => $user['phone'] ?? '',
                            'dob'      => $user['dob'] ?? '',
                            'gender'   => $user['gender'] ?? 'nam',
                            'avatar'   => $user['avatar'] ?? '',
                            'vai_tro'  => $user['vai_tro'] ?? 'Attendee'
                        ];

                        header('Location: home.php');
                        exit;
                    } else {
                        $errors['password'] = 'Mật khẩu không chính xác.';
                    }
                } else {
                    $errors['email'] = 'Email hoặc số điện thoại không tồn tại trên hệ thống.';
                }
            } catch (PDOException $e) {
                $errors['general'] = 'Lỗi kết nối CSDL: ' . $e->getMessage();
            }
        }
    }
}

// 3. XỬ LÝ ĐĂNG NHẬP GOOGLE (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'google_login') {
    header('Content-Type: application/json');
    $gg_name  = trim($_POST['google_name'] ?? 'Người dùng Google');
    $gg_email = trim($_POST['google_email'] ?? '');

    if (!empty($gg_email) && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM nguoidung WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $gg_email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Nếu tài khoản Google chưa có trong DB -> Tự động tạo record mới
            if (!$user) {
                $stmtIns = $pdo->prepare("INSERT INTO nguoidung (ho_ten, email, mat_khau, vai_tro) VALUES (:name, :email, '', 'Attendee')");
                $stmtIns->execute(['name' => $gg_name, 'email' => $gg_email]);
                $new_id = $pdo->lastInsertId();

                $user = [
                    'id'      => $new_id,
                    'ho_ten'  => $gg_name,
                    'email'   => $gg_email,
                    'phone'   => '',
                    'dob'     => '',
                    'gender'  => 'nam',
                    'avatar'  => '',
                    'vai_tro' => 'Attendee'
                ];
            }

            $_SESSION['user'] = [
                'id'       => (int)$user['id'],
                'ho_ten'   => $user['ho_ten'] ?? '',
                'name'     => $user['ho_ten'] ?? '',
                'email'    => $user['email'] ?? '',
                'phone'    => $user['phone'] ?? '',
                'dob'      => $user['dob'] ?? '',
                'gender'   => $user['gender'] ?? 'nam',
                'avatar'   => $user['avatar'] ?? '',
                'vai_tro'  => $user['vai_tro'] ?? 'Attendee'
            ];

            echo json_encode(['status' => 'success']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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
                        <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success_msg); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors['general'])): ?>
                    <div class="auth-alert error-alert" style="background: rgba(255,77,79,0.15); color: #ff4d4f; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                        <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" id="loginForm" novalidate>
                    <input type="hidden" name="action" value="normal_login">

                    <!-- Ô Nhập Email / SĐT -->
                    <div class="form-group <?php echo !empty($errors['email']) ? 'has-error' : ''; ?>" id="group-email">
                        <label class="form-label">Email</label>
                        <input type="text" id="loginEmail" name="email" class="form-input" placeholder="Nhập email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <span class="field-error" id="error-loginEmail" style="color:#ff4d4f; font-size:12px;"><?php echo $errors['email'] ?? ''; ?></span>
                    </div>

                    <!-- Ô Nhập Mật khẩu -->
                    <div class="form-group <?php echo !empty($errors['password']) ? 'has-error' : ''; ?>" id="group-password">
                        <label class="form-label">Mật khẩu</label>
                        <div class="password-wrapper">
                            <input type="password" id="loginPassword" name="password" class="form-input" placeholder="Nhập mật khẩu">
                            <i class="fa-regular fa-eye toggle-password" onclick="toggleShowPassword('loginPassword', this)"></i>
                        </div>
                        <span class="field-error" id="error-loginPassword" style="color:#ff4d4f; font-size:12px;"><?php echo $errors['password'] ?? ''; ?></span>
                    </div>

                    <!-- Nút Tiếp Tục -->
                    <button type="submit" id="btnLoginSubmit" class="btn-primary-auth">Tiếp tục</button>
                </form>


                <div class="auth-links">
                    <p>Chưa có tài khoản? <a href="register.php">Tạo tài khoản ngay</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/auth.js"></script>

</body>

</html>