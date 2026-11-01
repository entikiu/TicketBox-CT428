<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../../config/config.php';

function require_login(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        api_response(401, [
            'success' => false,
            'message' => 'Vui lòng đăng nhập.'
        ]);
    }
}

function require_auth(): void
{
    require_login();
}

function require_roles(array $roles): void
{
    require_login();

    $role = strtolower($_SESSION['user_role'] ?? '');

    if (!in_array($role, $roles, true)) {
        api_response(403, [
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động này.'
        ]);
    }
}