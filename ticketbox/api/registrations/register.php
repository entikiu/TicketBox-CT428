<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập trước khi đăng ký vé.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$event_id = isset($input['event_id']) ? (int)$input['event_id'] : 0;
$user_id = (int)$_SESSION['user']['id'];

if ($event_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID sự kiện không hợp lệ.']);
    exit;
}

try {
    // 2. Kiểm tra sự kiện có tồn tại và còn chỗ không
    $stmtEvent = $pdo->prepare("SELECT id, so_luong_toi_da FROM sukien WHERE id = :id LIMIT 1");
    $stmtEvent->execute(['id' => $event_id]);
    $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Sự kiện không tồn tại.']);
        exit;
    }

    // Kiểm tra số lượng vé đã đăng ký
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM ve WHERE su_kien_id = :eid AND trang_thai != 'da-huy'");
    $stmtCount->execute(['eid' => $event_id]);
    $registeredCount = (int)$stmtCount->fetchColumn();

    if ($registeredCount >= (int)$event['so_luong_toi_da']) {
        echo json_encode(['success' => false, 'message' => 'Sự kiện đã hết chỗ.']);
        exit;
    }

    // 3. Kiểm tra người dùng đã đăng ký vé hợp lệ chưa (tránh trùng lặp)
    $stmtCheck = $pdo->prepare("SELECT id FROM ve WHERE su_kien_id = :eid AND nguoi_dung_id = :uid AND trang_thai != 'da-huy' LIMIT 1");
    $stmtCheck->execute(['eid' => $event_id, 'uid' => $user_id]);
    if ($stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Bạn đã đăng ký vé hợp lệ cho sự kiện này rồi.']);
        exit;
    }

    // 4. Tạo mã vé mới và lưu vào CSDL
    $ticket_code = 'TKB-' . date('Y') . '-' . rand(10000, 99999);
    $stmtInsert = $pdo->prepare("INSERT INTO ve (ma_ve, su_kien_id, nguoi_dung_id, loai_ve, trang_thai) VALUES (:code, :eid, :uid, 'Vé Tiêu Chuẩn', 'thanh-cong')");
    $stmtInsert->execute(['code' => $ticket_code, 'eid' => $event_id, 'uid' => $user_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Đăng ký thành công',
        'data' => [
            'ticket_code' => $ticket_code
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}