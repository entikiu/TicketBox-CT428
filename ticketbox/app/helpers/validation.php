
<?php
declare(strict_types=1);

function read_json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        api_response(400, [
            'success' => false,
            'message' => 'JSON không hợp lệ.'
        ]);
    }

    return $data;
}

function require_fields(array $data, array $fields): void
{
    foreach ($fields as $field) {
        if (
            !array_key_exists($field, $data) ||
            (!is_string($data[$field]) && !is_numeric($data[$field])) ||
            trim((string) $data[$field]) === ''
        ) {
            api_response(422, [
                'success' => false,
                'message' => "Thiếu hoặc sai trường: {$field}."
            ]);
        }
    }
}

function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);

        api_response(405, [
            'success' => false,
            'message' => 'Phương thức HTTP không được hỗ trợ.'
        ]);
    }
}
