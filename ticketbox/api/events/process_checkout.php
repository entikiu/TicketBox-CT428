<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

// Kiểm tra quyền (Bắt buộc đăng nhập)
if (!isset($_SESSION['user']) || (int)($_SESSION['user']['id'] ?? 0) <= 0) {
    header('Location: ../../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id   = (int)$_SESSION['user']['id'];
    $item_type = trim($_POST['item_type'] ?? 'event');

    if (isset($pdo) && $user_id > 0) {
        try {
            // =========================================================================
            // NHÁNH 1: XỬ LÝ THANH TOÁN & KÍCH HOẠT THẺ THÀNH VIÊN
            // =========================================================================
            if ($item_type === 'membership') {
                $membership_id = (int)($_POST['membership_id'] ?? 0);

                $stmtMem = $pdo->prepare("SELECT * FROM the_thanh_vien WHERE id = :id LIMIT 1");
                $stmtMem->execute(['id' => $membership_id]);
                $memInfo = $stmtMem->fetch(PDO::FETCH_ASSOC);

                if (!$memInfo) {
                    header('Location: ../../pages/home.php');
                    exit;
                }

                $cardCode    = 'MB-' . date('Ymd') . '-' . rand(1000, 9999);
                $months      = max(1, (int)$memInfo['thoi_han_thang']);
                $ngayHetHan  = date('Y-m-d H:i:s', strtotime("+{$months} months"));

                $stmtInsMem = $pdo->prepare("
                    INSERT INTO the_thanh_vien_nguoidung (ma_the, the_id, nguoi_dung_id, ngay_kich_hoat, ngay_het_han, trang_thai)
                    VALUES (:ma_the, :the_id, :nguoi_dung_id, NOW(), :ngay_het_han, 'dang-hoat-dong')
                ");
                $stmtInsMem->execute([
                    'ma_the'        => $cardCode,
                    'the_id'        => $membership_id,
                    'nguoi_dung_id' => $user_id,
                    'ngay_het_han'  => $ngayHetHan
                ]);

                // Dọn dẹp session thanh toán và chuyển sang trang Thẻ thành viên của tôi
                unset($_SESSION['payment_data'], $_SESSION['checkout_data']);
                header('Location: ../../pages/my-tickets.php?tab=membership');
                exit;
            }

            // =========================================================================
            // NHÁNH 2: XỬ LÝ THANH TOÁN VÉ SỰ KIỆN
            // =========================================================================
            $su_kien_id  = (int)($_POST['su_kien_id'] ?? 0);
            $category    = trim($_POST['category'] ?? 'Vé Tiêu Chuẩn');
            $price       = trim($_POST['price'] ?? '0 đ');

            $event_title = trim($_POST['event_title'] ?? 'Sự kiện Ticketbox');
            $time        = trim($_POST['time'] ?? date('Y-m-d 19:30:00'));
            $location    = trim($_POST['location'] ?? 'Hà Nội');
            $image       = '../assets/images/events/banner-tinh-ha.jpg';

            $ticket_code = 'TKB-' . date('Ymd') . '-' . rand(1000, 9999);

            // 1. AN TOÀN KHÓA NGOẠI: Kiểm tra xem su_kien_id có tồn tại trong bảng sukien hay không
            $stmtCheck = $pdo->prepare("SELECT id, hinh_anh FROM sukien WHERE id = :id LIMIT 1");
            $stmtCheck->execute(['id' => $su_kien_id]);
            $eventExists = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$eventExists) {
                $stmtName = $pdo->prepare("SELECT id, hinh_anh FROM sukien WHERE ten_su_kien = :title LIMIT 1");
                $stmtName->execute(['title' => $event_title]);
                $foundByName = $stmtName->fetch(PDO::FETCH_ASSOC);

                if ($foundByName) {
                    $su_kien_id = (int)$foundByName['id'];
                    $image = $foundByName['hinh_anh'] ?? $image;
                } else {
                    $stmtInsEvt = $pdo->prepare("
                        INSERT INTO sukien (ten_su_kien, dia_diem, thoi_gian, so_luong_toi_da, nguoi_tao, hinh_anh) 
                        VALUES (:title, :location, NOW(), 1000, :nguoi_tao, :image)
                    ");
                    $stmtInsEvt->execute([
                        'title'     => $event_title,
                        'location'  => $location,
                        'nguoi_tao' => $user_id,
                        'image'     => $image
                    ]);
                    $su_kien_id = (int)$pdo->lastInsertId();
                }
            } else {
                if (!empty($eventExists['hinh_anh'])) {
                    $image = $eventExists['hinh_anh'];
                }
            }

            // 2. Chèn vé vào bảng 've'
            $stmtVe = $pdo->prepare("
                INSERT INTO ve (ma_ve, su_kien_id, nguoi_dung_id, loai_ve, gia_ve, trang_thai, ngay_dat) 
                VALUES (:ma_ve, :su_kien_id, :nguoi_dung_id, :loai_ve, :gia_ve, 'thanh-cong', NOW())
            ");
            $stmtVe->execute([
                'ma_ve'         => $ticket_code,
                'su_kien_id'    => $su_kien_id,
                'nguoi_dung_id' => $user_id,
                'loai_ve'       => $category,
                'gia_ve'        => $price
            ]);
            $inserted_id = (int)$pdo->lastInsertId();

            // 3. Đồng bộ vé vào SESSION để trang "Vé của tôi" hiển thị ngay lập tức
            if (!isset($_SESSION['my_tickets'])) {
                $_SESSION['my_tickets'] = [];
            }
            array_unshift($_SESSION['my_tickets'], [
                'ticket_db_id'    => $inserted_id,
                'id'              => $ticket_code,
                'ticket_code'     => $ticket_code,
                'event_title'     => $event_title,
                'ticket_category' => $category,
                'price'           => $price,
                'date'            => $time,
                'location'        => $location,
                'status'          => 'thanh-cong',
                'timeline'        => 'sap-dien-ra',
                'image'           => $image
            ]);

            // Dọn dẹp session thanh toán
            unset($_SESSION['payment_data'], $_SESSION['checkout_data']);

            // Chuyển thẳng về trang quản lý vé
            header('Location: ../../pages/my-tickets.php?tab=tickets');
            exit;

        } catch (PDOException $e) {
            die("<h2 style='color:red;'>LỖI DATABASE KHI XỬ LÝ THANH TOÁN:</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>");
        }
    }
}

header('Location: ../../pages/home.php');
exit;