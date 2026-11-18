<?php
// Admin Users Management - CRUD with PDO prepared statements
require_once __DIR__ . '/../app/middleware/admin.php'; // ensure admin access
require_once __DIR__ . '/../config/database.php'; // $pdo instance

// Process POST actions: add new user or update role (and optional password)
$editing = false;
$user = [
    'id' => '',
    'ho_ten' => '',
    'email' => '',
    'vai_tro' => 'user',
    'mat_khau' => ''
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $vai_tro = $_POST['vai_tro'] ?? 'user';
    $password = $_POST['password'] ?? '';

    if ($id) {
        // Update only role (and optional password change)
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE nguoidung SET vai_tro = :vai_tro, mat_khau = :mat_khau WHERE id = :id');
            $stmt->execute([
                'vai_tro' => $vai_tro,
                'mat_khau' => $hashed,
                'id' => $id
            ]);
        } else {
            $stmt = $pdo->prepare('UPDATE nguoidung SET vai_tro = :vai_tro WHERE id = :id');
            $stmt->execute([
                'vai_tro' => $vai_tro,
                'id' => $id
            ]);
        }
    } else {
        // Insert new user (password required)
        if (!$password) {
            $error = 'Mật khẩu mới là bắt buộc khi tạo người dùng.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO nguoidung (ho_ten, email, vai_tro, mat_khau) VALUES (:ho_ten, :email, :vai_tro, :mat_khau)');
            $stmt->execute([
                'ho_ten' => $ho_ten,
                'email' => $email,
                'vai_tro' => $vai_tro,
                'mat_khau' => $hashed
            ]);
        }
    }
    header('Location: users.php');
    exit;
}

// Handle edit request (populate form)
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM nguoidung WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $editing = true;
}

// Handle delete request
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    // Prevent deleting currently logged‑in admin
    if (isset($_SESSION['admin']['id']) && $_SESSION['admin']['id'] == $delId) {
        $error = 'Không thể xóa tài khoản admin đang đăng nhập.';
    } else {
        $stmt = $pdo->prepare('DELETE FROM nguoidung WHERE id = :id');
        $stmt->execute(['id' => $delId]);
    }
    header('Location: users.php');
    exit;
}

// Fetch user list for table display
$stmt = $pdo->query('SELECT id, ho_ten, email, vai_tro FROM nguoidung ORDER BY id DESC');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản trị Người dùng</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-section {max-width: 600px; margin: 20px auto;}
        .table-section {margin: 30px;}
    </style>
</head>
<body>
<div class="container">
    <h2 class="my-4">Quản trị Người dùng</h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <div class="form-section">
        <form method="post" action="users.php" class="needs-validation" novalidate>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id'] ?? ''); ?>">
            <div class="mb-3">
                <label class="form-label">Họ và tên</label>
                <input type="text" name="ho_ten" class="form-control" required value="<?php echo htmlspecialchars($user['ho_ten'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Vai trò</label>
                <select name="vai_tro" class="form-select">
                    <option value="user" <?php echo (isset($user['vai_tro']) && $user['vai_tro']=='user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo (isset($user['vai_tro']) && $user['vai_tro']=='admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu <?php echo $editing ? '(để trống nếu không đổi)' : ''; ?></label>
                <input type="password" name="password" class="form-control" <?php echo $editing ? '' : 'required'; ?>>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Cập nhật' : 'Thêm mới'; ?></button>
            <?php if ($editing): ?>
                <a href="users.php" class="btn btn-secondary ms-2">Hủy</a>
            <?php endif; ?>
        </form>
    </div>
    <hr>
    <div class="table-section">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['ho_ten']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['vai_tro']); ?></td>
                        <td>
                            <a href="users.php?edit=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                            <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
