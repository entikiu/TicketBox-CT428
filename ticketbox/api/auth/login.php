
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/User.php';

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['message' => 'Phương thức không hợp lệ.']);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    $data = $_POST;
}

$email = trim($data['email'] ?? '');
$matKhau = $data['mat_khau'] ?? '';

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !is_string($matKhau) ||
    $matKhau === ''
) {
    respond(422, ['message' => 'Vui lòng nhập email và mật khẩu hợp lệ.']);
}

$userModel = new User($pdo);
$user = $userModel->findByEmail($email);

if (!$user || !password_verify($matKhau, $user['mat_khau'])) {
    respond(401, ['message' => 'Email hoặc mật khẩu không chính xác.']);
}

// Đổi ID Session sau khi xác thực thành công
session_regenerate_id(true);

$_SESSION['user'] = [
    'id' => (int) $user['id'],
    'ho_ten' => $user['ho_ten'],
    'email' => $user['email'],
    'vai_tro' => $user['vai_tro']
];

respond(200, [
    'message' => 'Đăng nhập thành công.',
    'user' => $_SESSION['user']
]);
