<?php
declare(strict_types=1);

function api_response(int $status, array $data): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE 
    );
    exit;
}