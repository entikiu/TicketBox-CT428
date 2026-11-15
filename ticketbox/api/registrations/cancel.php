<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
// Hỗ trợ cả registration_id (hoặc ticket_id)
$registration_id = isset($input['registration_id']) ? (int)$input['registration_id'] : (isset($input['ticket_id']) ? (int)$input['ticket_id'] : 0);
$user_id = (int)$_SESSION['user']['id'];

if ($registration_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mã vé/đăng ký không hợp lệ.']);
    exit;
}

try {
    // 2. Kiểm tra vé thuộc về người dùng hiện tại
    $stmtTicket = $pdo->prepare("SELECT id, trang_thai FROM ve WHERE id = :id AND nguoi_dung_id = :uid LIMIT 1");
    $stmtTicket->execute(['id' => $registration_id, 'uid' => $user_id]);
    $ticket = $stmtTicket->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy vé hoặc bạn không có quyền hủy vé này.']);
        exit;
    }

    if ($ticket['trang_thai'] === 'da-huy') {
        echo json_encode(['success' => false, 'message' => 'Vé này đã được hủy trước đó.']);
        exit;
    }

    // 3. Cập nhật trạng thái vé thành 'da-huy'
    $stmtCancel = $pdo->prepare("UPDATE ve SET trang_thai = 'da-huy' WHERE id = :id");
    $stmtCancel->execute(['id' => $registration_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Hủy đăng ký thành công'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}