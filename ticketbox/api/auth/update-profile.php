<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/database.php'; // adjust path as needed

header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['error' => 'Unauthenticated']);
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$ho_ten = trim($input['ho_ten'] ?? '');
$email  = trim($input['email'] ?? '');
$phone  = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';

$errors = [];
if ($ho_ten === '') $errors['ho_ten'] = 'Họ tên không được để trống';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ';
if ($phone !== '' && !preg_match('/^[0-9]{9,11}$/', $phone)) $errors['phone'] = 'Số điện thoại phải 9‑11 chữ số';

if (!empty($errors)) {
    echo json_encode(['errors' => $errors]);
    exit;
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE nguoidung SET ho_ten = :ho_ten, email = :email, phone = :phone WHERE id = :id');
    $stmt->execute([':ho_ten' => $ho_ten, ':email' => $email, ':phone' => $phone, ':id' => $user_id]);

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmtPwd = $pdo->prepare('UPDATE nguoidung SET mat_khau = :hash WHERE id = :id');
        $stmtPwd->execute([':hash' => $hash, ':id' => $user_id]);
    }
    $pdo->commit();
    // refresh session
    $_SESSION['user']['ho_ten'] = $ho_ten;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['phone'] = $phone;
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
