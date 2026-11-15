
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

$hoTen = trim((string)$data['ho_ten'] ?? '');
$email = mb_strtolower(trim((string)$data['email'] ?? ''), 'UTF-8');
$matKhau = $data['mat_khau'] ?? '';

if (
    $hoTen === '' ||
    strlen($hoTen) > 100 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 150 ||
    !is_string($matKhau) ||
    strlen($matKhau) < 8 ||
    strlen($matKhau) > 72
) {
    respond(422, [
        'success' => false,
        'message' => 'Dữ liệu không hợp lệ. Mật khẩu cần ít nhất 8 ký tự.'
    ]);
}
try{
    $user = new User($pdo);

    if ($user->findByEmail($email)) {
        respond(409, [
            'success' => false,
            'message' => 'Email đã được sử dụng.'
        ]);
    }

    $hash = password_hash($matKhau, PASSWORD_DEFAULT);

    $id = $user->create($hoTen, $email, $hash);

    respond(201, [
        'success' => true,
        'message' => 'Đăng ký thành công.',
        'user_id' => $id
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        respond(409, [
            'success' => false,
            'message' => 'Email đã được sử dụng.'
        ]);
    }

    error_log($e->getMessage());
    respond(500, [
        'success' => false,
        'message' => 'Lỗi hệ thống.'
    ]);
}
