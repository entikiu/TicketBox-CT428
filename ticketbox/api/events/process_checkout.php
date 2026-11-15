<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

$userId = (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
    header('Location: ../../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($pdo)) {
    header('Location: ../../pages/home.php');
    exit;
}

$suKienId = filter_var($_POST['su_kien_id'] ?? null, FILTER_VALIDATE_INT);
if (!$suKienId || $suKienId < 1) {
    $_SESSION['flash_error'] = 'Mã sự kiện không hợp lệ.';
    header('Location: ../../pages/home.php');
    exit;
}

$category = mb_substr(trim((string)($_POST['category'] ?? 'Vé Tiêu Chuẩn')), 0, 100, 'UTF-8');
if ($category === '') {
    $category = 'Vé Tiêu Chuẩn';
}

// Chuẩn hóa giá vé theo định dạng varchar(50) trong bảng `ve` (ví dụ: "500.000 đ" hoặc "0 đ")
$digitsOnly   = preg_replace('/[^0-9]/', '', (string)($_POST['price'] ?? '0'));
$numericPrice = $digitsOnly !== '' ? (int)$digitsOnly : 0;
$formattedPrice = $numericPrice > 0
    ? number_format($numericPrice, 0, ',', '.') . ' đ'
    : '0 đ';

try {
    $pdo->beginTransaction();

    // 1. Khóa dòng sự kiện trong bảng `sukien`
    $stmtEvent = $pdo->prepare(
        'SELECT id, ten_su_kien, dia_diem, thoi_gian, so_luong_toi_da, hinh_anh FROM sukien WHERE id = :id FOR UPDATE'
    );
    $stmtEvent->execute(['id' => $suKienId]);
    $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = 'Sự kiện không tồn tại hoặc đã bị gỡ bỏ.';
        header('Location: ../../pages/home.php');
        exit;
    }

    // 2. Kiểm tra số lượng vé đã bán so với `so_luong_toi_da`
    $maxCapacity = (int)$event['so_luong_toi_da'];
    $stmtCount = $pdo->prepare(
        "SELECT COUNT(*) FROM ve WHERE su_kien_id = :id AND trang_thai != 'da-huy'"
    );
    $stmtCount->execute(['id' => $suKienId]);
    $soldTickets = (int)$stmtCount->fetchColumn();

    if ($maxCapacity > 0 && $soldTickets >= $maxCapacity) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = 'Sự kiện đã hết vé.';
        header('Location: ../../pages/home.php');
        exit;
    }

    $eventTitle = (string)$event['ten_su_kien'];
    $time       = (string)$event['thoi_gian'];
    $location   = (string)($event['dia_diem'] ?? 'Chưa cập nhật');
    $image      = !empty($event['hinh_anh'])
        ? (string)$event['hinh_anh']
        : '../assets/images/events/banner-tinh-ha.jpg';

    $ticketCode = 'TKB-' . date('Ymd') . '-' . random_int(1000, 9999);

    // 3. Lưu vé mới vào bảng `ve`
    $stmtVe = $pdo->prepare("
        INSERT INTO ve (ma_ve, su_kien_id, nguoi_dung_id, loai_ve, gia_ve, trang_thai, ngay_dat)
        VALUES (:ma_ve, :su_kien_id, :nguoi_dung_id, :loai_ve, :gia_ve, 'thanh-cong', NOW())
    ");
    $stmtVe->execute([
        'ma_ve'         => $ticketCode,
        'su_kien_id'    => $suKienId,
        'nguoi_dung_id' => $userId,
        'loai_ve'       => $category,
        'gia_ve'        => $formattedPrice
    ]);
    $insertedId = (int)$pdo->lastInsertId();

    $pdo->commit();

    // 4. Đồng bộ vé vào Session
    if (!isset($_SESSION['my_tickets']) || !is_array($_SESSION['my_tickets'])) {
        $_SESSION['my_tickets'] = [];
    }
    array_unshift($_SESSION['my_tickets'], [
        'ticket_db_id'    => $insertedId,
        'id'              => $ticketCode,
        'ticket_code'     => $ticketCode,
        'event_title'     => $eventTitle,
        'ticket_category' => $category,
        'price'           => $formattedPrice,
        'date'            => $time,
        'location'        => $location,
        'status'          => 'thanh-cong',
        'timeline'        => 'sap-dien-ra',
        'image'           => $image
    ]);

    unset($_SESSION['payment_data'], $_SESSION['checkout_data']);
    header('Location: ../../pages/my-tickets.php?tab=tickets');
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[TicketBox Checkout Error] ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại.';
    header('Location: ../../pages/home.php');
    exit;
}