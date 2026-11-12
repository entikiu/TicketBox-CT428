
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, [
        'success' => false,
        'message' => 'Phương thức không hợp lệ.'
    ]);
}

// Kiểm tra đăng nhập
$userId = (int) ($_SESSION['user']['id'] ?? 0);

if ($userId <= 0) {
    respond(401, [
        'success' => false,
        'message' => 'Vui lòng đăng nhập trước.'
    ]);
}

// Nhận dữ liệu AJAX
$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    $data = $_POST;
}

$eventId = filter_var(
    $data['event_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$eventId || $eventId <= 0) {
    respond(422, [
        'success' => false,
        'message' => 'ID sự kiện không hợp lệ.'
    ]);
}

try {
    $pdo->beginTransaction();

    // Khóa sự kiện để chống đăng ký vượt sức chứa
    $stmt = $pdo->prepare(
        "SELECT id, thoi_gian, so_luong_toi_da
         FROM SuKien
         WHERE id = ?
         FOR UPDATE"
    );
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        $pdo->rollBack();
        respond(404, [
            'success' => false,
            'message' => 'Không tìm thấy sự kiện.'
        ]);
    }

    if (strtotime($event['thoi_gian']) <= time()) {
        $pdo->rollBack();
        respond(409, [
            'success' => false,
            'message' => 'Sự kiện đã bắt đầu hoặc kết thúc.'
        ]);
    }

    // Kiểm tra đăng ký hiện tại
    $stmt = $pdo->prepare(
        "SELECT id, trang_thai
         FROM DangKySuKien
         WHERE nguoi_dung_id = ?
           AND su_kien_id = ?"
    );
    $stmt->execute([$userId, $eventId]);
    $registration = $stmt->fetch();

    if (
        $registration &&
        $registration['trang_thai'] !== 'Cancelled'
    ) {
        $pdo->rollBack();
        respond(409, [
            'success' => false,
            'message' => 'Bạn đã đăng ký sự kiện này.'
        ]);
    }

    // Đếm số vé đang chiếm chỗ
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM DangKySuKien
         WHERE su_kien_id = ?
           AND trang_thai IN ('Registered', 'CheckedIn')"
    );
    $stmt->execute([$eventId]);
    $registeredCount = (int) $stmt->fetchColumn();

    if ($registeredCount >= (int) $event['so_luong_toi_da']) {
        $pdo->rollBack();
        respond(409, [
            'success' => false,
            'message' => 'Sự kiện đã hết vé.'
        ]);
    }

    // Mã vé ngẫu nhiên
    $ticketCode = 'TB-' . strtoupper(bin2hex(random_bytes(12)));

    if ($registration) {
        // Đăng ký lại vé đã hủy
        $stmt = $pdo->prepare(
            "UPDATE DangKySuKien
             SET trang_thai = 'Registered',
                 ma_ve = ?,
                 ngay_dang_ky = CURRENT_TIMESTAMP
             WHERE id = ?"
        );
        $stmt->execute([
            $ticketCode,
            $registration['id']
        ]);
    } else {
        // Đăng ký mới
        $stmt = $pdo->prepare(
            "INSERT INTO DangKySuKien
             (nguoi_dung_id, su_kien_id, ma_ve, trang_thai)
             VALUES (?, ?, ?, 'Registered')"
        );
        $stmt->execute([
            $userId,
            $eventId,
            $ticketCode
        ]);
    }

    $pdo->commit();

    respond(201, [
        'success' => true,
        'message' => 'Đăng ký vé thành công.',
        'data' => [
            'event_id' => $eventId,
            'ticket_code' => $ticketCode,
            'status' => 'Registered'
        ]
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Lỗi hệ thống, vui lòng thử lại.'
    ]);
}

$stmtIns = $pdo->prepare("INSERT INTO nguoidung (ho_ten, email, mat_khau, vai_tro) VALUES (:name, :email, :pass, 'Attendee')");
$stmtIns->execute([
    'name'  => $fullname,
    'email' => $email,
    'pass'  => password_hash($password, PASSWORD_DEFAULT)
]);

$stmtCheck = $pdo->prepare("SELECT id FROM nguoidung WHERE email = :email LIMIT 1");
$stmtCheck->execute(['email' => $email]);
if ($stmtCheck->rowCount() > 0) {
    $errors['email'] = 'Email này đã được sử dụng trên hệ thống.';
}
