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

$itemType = trim((string)($_POST['item_type'] ?? 'event'));

try {
    // =========================================================================
    // NHÁNH 1: THANH TOÁN & KÍCH HOẠT THẺ THÀNH VIÊN
    // =========================================================================
    if ($itemType === 'membership') {
        $membershipId = filter_var($_POST['membership_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$membershipId || $membershipId < 1) {
            $_SESSION['flash_error'] = 'Gói thẻ thành viên không hợp lệ.';
            header('Location: ../../pages/home.php');
            exit;
        }

        $pdo->beginTransaction();

        $stmtMem = $pdo->prepare('SELECT * FROM the_thanh_vien WHERE id = :id LIMIT 1');
        $stmtMem->execute(['id' => $membershipId]);
        $memInfo = $stmtMem->fetch(PDO::FETCH_ASSOC);

        if (!$memInfo) {
            $pdo->rollBack();
            $_SESSION['flash_error'] = 'Không tìm thấy gói thẻ thành viên.';
            header('Location: ../../pages/home.php');
            exit;
        }

        $cardCode   = 'MB-' . date('Ymd') . '-' . random_int(1000, 9999);
        $months     = max(1, (int)($memInfo['thoi_han_thang'] ?? 1));
        $ngayHetHan = date('Y-m-d H:i:s', strtotime("+{$months} months"));

        $stmtInsMem = $pdo->prepare("
            INSERT INTO the_thanh_vien_nguoidung (ma_the, the_id, nguoi_dung_id, ngay_kich_hoat, ngay_het_han, trang_thai)
            VALUES (:ma_the, :the_id, :nguoi_dung_id, NOW(), :ngay_het_han, 'dang-hoat-dong')
        ");
        $stmtInsMem->execute([
            'ma_the'        => $cardCode,
            'the_id'        => $membershipId,
            'nguoi_dung_id' => $userId,
            'ngay_het_han'  => $ngayHetHan
        ]);

        $pdo->commit();

        unset($_SESSION['payment_data'], $_SESSION['checkout_data']);
        header('Location: ../../pages/my-tickets.php?tab=membership');
        exit;
    }

    // =========================================================================
    // NHÁNH 2: THANH TOÁN VÉ SỰ KIỆN (sukien + loaive + ve)
    // =========================================================================
    $suKienId = filter_var($_POST['su_kien_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$suKienId || $suKienId < 1) {
        $_SESSION['flash_error'] = 'Mã sự kiện không hợp lệ.';
        header('Location: ../../pages/home.php');
        exit;
    }

    $loaiVeId = filter_var($_POST['loai_ve_id'] ?? null, FILTER_VALIDATE_INT);
    $category = mb_substr(trim((string)($_POST['category'] ?? 'Vé Tiêu Chuẩn')), 0, 100, 'UTF-8');
    if ($category === '') {
        $category = 'Vé Tiêu Chuẩn';
    }

    $pdo->beginTransaction();

    // 1. Khóa bản ghi sự kiện trong bảng `sukien`
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

    // 2. Kiểm tra tổng số vé đã bán của toàn sự kiện so với `so_luong_toi_da`
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

    // 3. Tìm và khóa hạng vé tương ứng trong bảng `loaive`
    $ticketTier = null;
    if ($loaiVeId && $loaiVeId > 0) {
        $stmtTier = $pdo->prepare(
            'SELECT id, ten_loai_ve, gia_ve, da_het_ve, so_luong FROM loaive WHERE id = :id AND su_kien_id = :su_kien_id FOR UPDATE'
        );
        $stmtTier->execute([
            'id'         => $loaiVeId,
            'su_kien_id' => $suKienId
        ]);
        $ticketTier = $stmtTier->fetch(PDO::FETCH_ASSOC) ?: null;
    } else {
        $stmtTiers = $pdo->prepare(
            'SELECT id, ten_loai_ve, gia_ve, da_het_ve, so_luong FROM loaive WHERE su_kien_id = :su_kien_id FOR UPDATE'
        );
        $stmtTiers->execute(['su_kien_id' => $suKienId]);

        foreach ($stmtTiers->fetchAll(PDO::FETCH_ASSOC) as $tier) {
            if (mb_strtolower(trim((string)$tier['ten_loai_ve']), 'UTF-8') === mb_strtolower($category, 'UTF-8')) {
                $ticketTier = $tier;
                break;
            }
        }
    }

    $tierSold = 0;
    $tierMax  = 0;

    if ($ticketTier) {
        $category = mb_substr(trim((string)$ticketTier['ten_loai_ve']), 0, 100, 'UTF-8');
        $tierMax  = (int)$ticketTier['so_luong'];

        // Kiểm tra cờ `da_het_ve`
        if ((int)$ticketTier['da_het_ve'] === 1) {
            $pdo->rollBack();
            $_SESSION['flash_error'] = "Hạng vé \"{$category}\" đã hết.";
            header('Location: ../../pages/home.php');
            exit;
        }

        // Đếm số vé đã bán của riêng hạng vé này
        $stmtTierCount = $pdo->prepare(
            "SELECT COUNT(*) FROM ve WHERE su_kien_id = :su_kien_id AND loai_ve = :loai_ve AND trang_thai != 'da-huy'"
        );
        $stmtTierCount->execute([
            'su_kien_id' => $suKienId,
            'loai_ve'    => $category
        ]);
        $tierSold = (int)$stmtTierCount->fetchColumn();

        if ($tierMax > 0 && $tierSold >= $tierMax) {
            $pdo->rollBack();
            $_SESSION['flash_error'] = "Hạng vé \"{$category}\" đã hết.";
            header('Location: ../../pages/home.php');
            exit;
        }

        $numericPrice = (int)$ticketTier['gia_ve'];
    } else {
        $digitsOnly   = preg_replace('/[^0-9]/', '', (string)($_POST['price'] ?? '0'));
        $numericPrice = $digitsOnly !== '' ? (int)$digitsOnly : 0;
    }

    $formattedPrice = $numericPrice > 0
        ? number_format($numericPrice, 0, ',', '.') . ' đ'
        : '0 đ';

    $eventTitle = (string)$event['ten_su_kien'];
    $time       = (string)$event['thoi_gian'];
    $location   = (string)($event['dia_diem'] ?? 'Chưa cập nhật');
    $image      = !empty($event['hinh_anh'])
        ? (string)$event['hinh_anh']
        : '../assets/images/events/banner-tinh-ha.jpg';

    $ticketCode = 'TKB-' . date('Ymd') . '-' . random_int(1000, 9999);

    // 4. Lưu vé mới vào bảng `ve`
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

    // 5. Cập nhật `da_het_ve = 1` nếu hạng vé vừa bán hết suất cuối cùng
    if ($ticketTier && $tierMax > 0 && ($tierSold + 1) >= $tierMax) {
        $stmtMarkSoldOut = $pdo->prepare('UPDATE loaive SET da_het_ve = 1 WHERE id = :id');
        $stmtMarkSoldOut->execute(['id' => (int)$ticketTier['id']]);
    }

    $pdo->commit();

    // 6. Đồng bộ vé vào Session
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