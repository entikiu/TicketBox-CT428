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
    respond(405, [
        'success' => false,
        'message' => 'Phương thức không hợp lệ.'
    ]);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    $data = $_POST;
}

$email = mb_strtolower(trim($data['email'] ?? ''), 'UTF-8');
$matKhau = $data['mat_khau'] ?? '';

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !is_string($matKhau) ||
    $matKhau === ''
) {
    respond(422, [
        'success' => false,
        'message' => 'Vui lòng nhập email và mật khẩu hợp lệ.'
    ]);
}
try{
    $userModel = new User($pdo);
    $user = $userModel->findByEmail($email);

    if (!$user || !password_verify($matKhau, $user['mat_khau'])) {
        respond(401, [
            'success' => false,
            'message' => 'Email hoặc mật khẩu không chính xác.'
        ]);
}

// Đổi ID Session sau khi xác thực thành công
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'ho_ten' => $user['ho_ten'],
        'email' => $user['email'],
        'vai_tro' => $user['vai_tro']
    ];

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_role'] = $user['vai_tro'];

    respond(200, [
        'success' => true,
        'message' => 'Đăng nhập thành công.',
        'user'    => [
            'id'      => $_SESSION['user_id'],
            'ho_ten'  => $_SESSION['user']['ho_ten'],
            'email'   => $_SESSION['user']['email'],
            'vai_tro' => $_SESSION['user_role']
        ]
    ]);
}catch (PDOException $e) {
    error_log('[TicketBox Login] ' . $e->getMessage());
    respond(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ.'
    ]);
}