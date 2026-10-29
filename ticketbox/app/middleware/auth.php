<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../../config/config.php';

function require_auth(): void
{
    if (empty($_SERVER['user_id'])){
        api_response(401, [
            'success' => false,
            'message' => 'Vui lòng đăng nhập để thực hiện hành động này',
        ]);
    }
}

function require_roles(array $roles): void
{
    require_login();

    if (!in_array($_SESSION['user_role'] ?? '', $roles, true)) {
        api_response(403, [
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động này',
        ]);
    }
}