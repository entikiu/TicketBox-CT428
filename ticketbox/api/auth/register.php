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
    respond(405, ['error' => 'Phương thức không hợp lệ.']);
}

$data = json_decode(file_get_contents('php://input'), true);

if(!is_array($data)) {
    $data = $_POST;
}

$hoten = trim($data['ho_ten'] ?? '');
$email = trim($data['email'] ?? '');
$matKhau = trim($data['mat_khau'] ?? '');

if(
    $hoten === '' ||
    strlen($hoten) > 100 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 100 ||
    !is_string($matKhau) ||
    strlen($matKhau) < 8 ||
    strlen($matKhau) > 100
){
    respond(400, ['message' => 'Dữ liệu không hợp lệ. Mật khẩu phải có ít nhất 8 ký tự và không quá 100 ký tự.']);
}

$user = new User($db);

if ($user->findByEmail($email)) {
    respond(409, ['message' => 'Email đã được sử dụng.']);
}

try{
    $id = $user->create($hoten, $email, $matKhau);

    respond(201, ['message' => 'Đăng ký thành công.', 'user_id' => $id]);
}catch (PDOException $e){
    if ($e->getCode() === '23000') {
        respond(409, ['message' => 'Email đã được sử dụng.']);
    } else {
        respond(500, ['message' => 'Lỗi hệ thống.']);
    }
    error_log($e->getMessage());
    respond(500, ['message' => 'Lỗi hệ thống.']);
}