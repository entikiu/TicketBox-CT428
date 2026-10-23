
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode(['message' => 'Phương thức không hợp lệ.']);
    exit;
}

// Xóa dữ liệu Session
$_SESSION = [];

// Xóa cookie Session hiện tại
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax'
    ]);
}

session_destroy();

echo json_encode([
    'message' => 'Đăng xuất thành công.'
], JSON_UNESCAPED_UNICODE);
