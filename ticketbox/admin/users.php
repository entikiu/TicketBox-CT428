<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/middleware/admin.php'; // Đảm bảo quyền admin

// Kiểm tra bổ sung session admin giống index.php / orders.php
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error   = '';

// Kiểm tra danh sách cột hiện có trong bảng nguoidung để tương thích CSDL
$userTableCols = [];
try {
    $colStmt = $pdo->query("SHOW COLUMNS FROM nguoidung");
    $userTableCols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $userTableCols = ['id', 'ho_ten', 'email', 'mat_khau', 'vai_tro'];
}

// -------------------- XÓA NGƯỜI DÙNG --------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    if (isset($_SESSION['admin']['id']) && (int)$_SESSION['admin']['id'] === $delId) {
        $error = 'Không thể xóa tài khoản quản trị đang đăng nhập!';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM nguoidung WHERE id = :id");
            $stmt->execute(['id' => $delId]);
            $message = 'Đã xóa người dùng thành công!';
        } catch (PDOException $e) {
            $error = 'Lỗi khi xóa người dùng (tài khoản có thể đang liên kết dữ liệu vé/sự kiện): ' . $e->getMessage();
        }
    }
}

// -------------------- LẤY DỮ LIỆU CHO CHỈNH SỬA --------------------
$editUser = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM nguoidung WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $editId]);
        $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$editUser) {
            $error = 'Không tìm thấy người dùng cần chỉnh sửa.';
        }
    } catch (PDOException $e) {
        $error = 'Không thể tải thông tin người dùng: ' . $e->getMessage();
    }
}

// Lưu lại dữ liệu đã nhập khi thêm mới gặp lỗi ràng buộc
$formData = [
    'ho_ten'  => '',
    'email'   => '',
    'phone'   => '',
    'dob'     => '',
    'gender'  => 'nam',
    'vai_tro' => 'user',
];

// -------------------- THÊM MỚI / CẬP NHẬT VAI TRÒ NGƯỜI DÙNG --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $vai_tro = strtolower(trim($_POST['vai_tro'] ?? 'user'));
    if (!in_array($vai_tro, ['user', 'admin'], true)) {
        $vai_tro = 'user';
    }

    if ($id > 0) {
        // CHỈ ĐƯỢC SỬA VAI TRÒ CỦA NGƯỜI DÙNG
        try {
            if (isset($_SESSION['admin']['id']) && (int)$_SESSION['admin']['id'] === $id && $vai_tro !== 'admin') {
                $error = 'Không thể tự hạ quyền của tài khoản quản trị đang đăng nhập!';
            } else {
                $stmt = $pdo->prepare("UPDATE nguoidung SET vai_tro = :vai_tro WHERE id = :id");
                $stmt->execute([
                    'vai_tro' => $vai_tro,
                    'id'      => $id,
                ]);
                $message  = 'Cập nhật vai trò người dùng thành công!';
                $editUser = null; // Quay về trạng thái bình thường sau khi lưu
            }
        } catch (PDOException $e) {
            $error = 'Lỗi khi cập nhật vai trò: ' . $e->getMessage();
        }
    } else {
        // THÊM MỚI NGƯỜI DÙNG - ĐẦY ĐỦ THÔNG TIN & RÀNG BUỘC NHƯ ĐĂNG KÝ TÀI KHOẢN
        $ho_ten           = trim($_POST['ho_ten'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $phone            = trim($_POST['phone'] ?? '');
        $dob              = trim($_POST['dob'] ?? '');
        $gender           = $_POST['gender'] ?? 'nam';
        $mat_khau         = $_POST['mat_khau'] ?? '';
        $confirm_mat_khau = $_POST['confirm_mat_khau'] ?? '';

        $formData = [
            'ho_ten'  => $ho_ten,
            'email'   => $email,
            'phone'   => $phone,
            'dob'     => $dob,
            'gender'  => $gender,
            'vai_tro' => $vai_tro,
        ];

        $validationErrors = [];

        if ($ho_ten === '' || mb_strlen($ho_ten, 'UTF-8') < 2) {
            $validationErrors[] = 'Họ và tên phải có ít nhất 2 ký tự.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationErrors[] = 'Địa chỉ email không hợp lệ.';
        }
        if ($phone === '' || !preg_match('/^[0-9]{9,11}$/', $phone)) {
            $validationErrors[] = 'Số điện thoại bắt buộc và phải gồm 9 - 11 chữ số.';
        }
        if ($dob === '') {
            $validationErrors[] = 'Vui lòng chọn ngày sinh.';
        } elseif (strtotime($dob) >= strtotime('today')) {
            $validationErrors[] = 'Ngày sinh phải nhỏ hơn ngày hiện tại.';
        }
        if (!in_array($gender, ['nam', 'nu', 'khac'], true)) {
            $validationErrors[] = 'Giới tính không hợp lệ.';
        }
        if (strlen($mat_khau) < 6) {
            $validationErrors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }
        if ($mat_khau !== $confirm_mat_khau) {
            $validationErrors[] = 'Mật khẩu xác nhận không trùng khớp.';
        }

        // Kiểm tra trùng Email trong CSDL
        if (empty($validationErrors)) {
            try {
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM nguoidung WHERE email = :email");
                $checkStmt->execute(['email' => $email]);
                if ((int)$checkStmt->fetchColumn() > 0) {
                    $validationErrors[] = 'Email này đã được đăng ký trong hệ thống.';
                }
            } catch (PDOException $e) {
                $validationErrors[] = 'Lỗi kiểm tra email: ' . $e->getMessage();
            }
        }

        if (!empty($validationErrors)) {
            $error = implode('<br>', $validationErrors);
        } else {
            try {
                $hashedPassword = password_hash($mat_khau, PASSWORD_DEFAULT);

                // Xây dựng câu lệnh INSERT động theo các cột có trong bảng nguoidung
                $insertCols   = ['ho_ten', 'email', 'mat_khau', 'vai_tro'];
                $insertParams = [
                    'ho_ten'   => $ho_ten,
                    'email'    => $email,
                    'mat_khau' => $hashedPassword,
                    'vai_tro'  => $vai_tro,
                ];

                if (in_array('phone', $userTableCols, true)) {
                    $insertCols[]          = 'phone';
                    $insertParams['phone'] = $phone;
                }
                if (in_array('dob', $userTableCols, true)) {
                    $insertCols[]        = 'dob';
                    $insertParams['dob'] = $dob;
                }
                if (in_array('gender', $userTableCols, true)) {
                    $insertCols[]           = 'gender';
                    $insertParams['gender'] = $gender;
                }

                $colList         = implode(', ', $insertCols);
                $placeholderList = ':' . implode(', :', $insertCols);

                $stmt = $pdo->prepare("INSERT INTO nguoidung ($colList) VALUES ($placeholderList)");
                $stmt->execute($insertParams);

                $message  = 'Thêm người dùng mới thành công!';
                $formData = [
                    'ho_ten'  => '',
                    'email'   => '',
                    'phone'   => '',
                    'dob'     => '',
                    'gender'  => 'nam',
                    'vai_tro' => 'user',
                ];
            } catch (PDOException $e) {
                $error = 'Lỗi khi lưu người dùng vào CSDL: ' . $e->getMessage();
            }
        }
    }
}

// -------------------- LẤY DANH SÁCH NGƯỜI DÙNG --------------------
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

        .admin-content { padding: 30px; display: flex; flex-direction: column; gap: 24px; }
        .card-table { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .card-table h3 { color: #1e293b; margin-bottom: 16px; font-size: 18px; display: flex; align-items: center; gap: 8px; }

        /* Form Grid */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
        .form-group { margin-bottom: 4px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13.5px; color: #334155; }
        .form-group label .req { color: #ef4444; }
        .form-group input, .form-group select { width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; color: #1e293b; background: #fff; transition: border-color 0.2s; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary-color); }
        .form-group input[readonly], .form-group input:disabled { background: #f1f5f9; color: #64748b; cursor: not-allowed; }

        .form-actions { margin-top: 18px; display: flex; align-items: center; gap: 10px; }
        .btn-submit { background: var(--primary-color); color: #fff; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .btn-submit:hover { background: #21b063; }
        .btn-cancel { background: #64748b; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; }
        .btn-cancel:hover { background: #475569; }

        /* Table & Badges */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #334155; }
        th { background: #f8fafc; font-weight: 600; color: #475569; }
        tr:hover td { background: #f8fafc; }

        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-admin { background: #fef3c7; color: #b45309; }
        .badge-user { background: #dcfce7; color: #15803d; }

        .btn-action { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 6px; color: #fff; text-decoration: none; font-size: 13px; font-weight: 500; transition: background 0.2s; }
        .btn-edit { background: #2563eb; }
        .btn-edit:hover { background: #1d4ed8; }
        .btn-delete { background: #ef4444; }
        .btn-delete:hover { background: #dc2626; }

        .alert { padding: 12px 16px; border-radius: 6px; font-size: 14px; line-height: 1.5; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .edit-note { font-size: 13px; color: #2563eb; background: #eff6ff; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; border-left: 4px solid #2563eb; }
    </style>
</head>
<body>
    <!-- Sidebar -->
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

    <!-- Main Content Area -->
    <div class="admin-main">
        <header class="admin-header">
            <h3>Quản lý Người dùng</h3>
            <div class="admin-user-info">
                <span><i class="fa-solid fa-user-shield"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['admin']['ho_ten']); ?></span>
                <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </header>

        <div class="admin-content">
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Form Thêm mới / Chỉnh sửa vai trò người dùng -->
            <div class="card-table">
                <h3>
                    <?php if ($editUser): ?>
                        <i class="fa-solid fa-user-pen" style="color: #2563eb;"></i> Phân quyền người dùng (#<?php echo (int)$editUser['id']; ?>)
                    <?php else: ?>
                        <i class="fa-solid fa-user-plus" style="color: #26c973;"></i> Thêm người dùng mới
                    <?php endif; ?>
                </h3>

                <?php if ($editUser): ?>
                    <div class="edit-note">
                        <i class="fa-solid fa-circle-info"></i> Chế độ chỉnh sửa chỉ cho phép thay đổi <strong>Vai trò</strong> của người dùng. Thông tin cá nhân và mật khẩu được bảo mật.
                    </div>
                    <form method="post" action="users.php?action=edit&id=<?php echo (int)$editUser['id']; ?>">
                        <input type="hidden" name="id" value="<?php echo (int)$editUser['id']; ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Họ và tên</label>
                                <input type="text" value="<?php echo htmlspecialchars($editUser['ho_ten'] ?? ''); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="text" value="<?php echo htmlspecialchars($editUser['phone'] ?? 'Chưa cập nhật'); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Vai trò <span class="req">*</span></label>
                                <?php $curRole = strtolower(trim($editUser['vai_tro'] ?? 'user')); ?>
                                <select name="vai_tro" required>
                                    <option value="user" <?php echo ($curRole === 'user') ? 'selected' : ''; ?>>User (Người dùng)</option>
                                    <option value="admin" <?php echo ($curRole === 'admin') ? 'selected' : ''; ?>>Admin (Quản trị viên)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> Lưu vai trò</button>
                            <a href="users.php" class="btn-cancel"><i class="fa-solid fa-xmark"></i> Hủy</a>
                        </div>
                    </form>
                <?php else: ?>
                    <form method="post" action="users.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Họ và tên <span class="req">*</span></label>
                                <input type="text" name="ho_ten" required minlength="2" placeholder="Nhập họ và tên đầy đủ" value="<?php echo htmlspecialchars($formData['ho_ten']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Email <span class="req">*</span></label>
                                <input type="email" name="email" required placeholder="example@gmail.com" value="<?php echo htmlspecialchars($formData['email']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại <span class="req">*</span></label>
                                <input type="tel" name="phone" required pattern="[0-9]{9,11}" title="Số điện thoại gồm 9 đến 11 chữ số" placeholder="09xxxxxxxx" value="<?php echo htmlspecialchars($formData['phone']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Ngày sinh <span class="req">*</span></label>
                                <input type="date" name="dob" required max="<?php echo date('Y-m-d', strtotime('-1 day')); ?>" value="<?php echo htmlspecialchars($formData['dob']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Giới tính <span class="req">*</span></label>
                                <select name="gender" required>
                                    <option value="nam" <?php echo ($formData['gender'] === 'nam') ? 'selected' : ''; ?>>Nam</option>
                                    <option value="nu" <?php echo ($formData['gender'] === 'nu') ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="khac" <?php echo ($formData['gender'] === 'khac') ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Vai trò <span class="req">*</span></label>
                                <select name="vai_tro" required>
                                    <option value="user" <?php echo ($formData['vai_tro'] === 'user') ? 'selected' : ''; ?>>User (Người dùng)</option>
                                    <option value="admin" <?php echo ($formData['vai_tro'] === 'admin') ? 'selected' : ''; ?>>Admin (Quản trị viên)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Mật khẩu <span class="req">*</span></label>
                                <input type="password" name="mat_khau" required minlength="6" placeholder="Tối thiểu 6 ký tự">
                            </div>
                            <div class="form-group">
                                <label>Xác nhận mật khẩu <span class="req">*</span></label>
                                <input type="password" name="confirm_mat_khau" required minlength="6" placeholder="Nhập lại mật khẩu">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-submit"><i class="fa-solid fa-plus"></i> Thêm người dùng</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Danh sách người dùng -->
            <div class="card-table">
                <h3><i class="fa-solid fa-users" style="color: #3b82f6;"></i> Danh sách người dùng trên hệ thống (<?php echo count($users); ?>)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Vai trò</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $u): ?>
                                <?php $roleLower = strtolower(trim($u['vai_tro'] ?? 'user')); ?>
                                <tr>
                                    <td>#<?php echo (int)$u['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['ho_ten'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(!empty($u['phone']) ? $u['phone'] : '—'); ?></td>
                                    <td>
                                        <?php if ($roleLower === 'admin'): ?>
                                            <span class="badge badge-admin"><i class="fa-solid fa-shield-halved"></i> Admin</span>
                                        <?php else: ?>
                                            <span class="badge badge-user"><i class="fa-solid fa-user"></i> User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="users.php?action=edit&id=<?php echo (int)$u['id']; ?>" class="btn-action btn-edit">
                                            <i class="fa-solid fa-pen"></i> Sửa vai trò
                                        </a>
                                        <?php if (!isset($_SESSION['admin']['id']) || (int)$_SESSION['admin']['id'] !== (int)$u['id']): ?>
                                            <a href="users.php?action=delete&id=<?php echo (int)$u['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
                                                <i class="fa-solid fa-trash-can"></i> Xóa
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #64748b;">Chưa có người dùng nào trong hệ thống.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>