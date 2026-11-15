<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$email   = mb_strtolower(trim((string)($data['email'] ?? '')), 'UTF-8');
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

try {
    $userModel = new User($pdo);
    $user      = $userModel->findByEmail($email);

    if (!$user || !password_verify($matKhau, $user['mat_khau'])) {
        respond(401, [
            'success' => false,
            'message' => 'Email hoặc mật khẩu không chính xác.'
        ]);
    }

    // Đổi ID Session sau khi xác thực thành công
    session_regenerate_id(true);

    // Khai báo rõ 2 biến trước khi gán vào $_SESSION['user']
    $userId   = (int)$user['id'];
    $userRole = (string)$user['vai_tro'];

    $_SESSION['user'] = [
        'id'      => $userId,
        'ho_ten'  => $user['ho_ten'],
        'email'   => $user['email'],
        'phone'   => $user['phone'] ?? null,
        'dob'     => $user['dob'] ?? null,
        'gender'  => $user['gender'] ?? 'nam',
        'avatar'  => $user['avatar'] ?? null,
        'vai_tro' => $userRole
    ];

    $_SESSION['user_id']   = $userId;
    $_SESSION['user_role'] = $userRole;

    respond(200, [
        'success' => true,
        'message' => 'Đăng nhập thành công.',
        'user'    => $_SESSION['user']
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Login] ' . $e->getMessage());
    respond(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ.'
    ]);
}