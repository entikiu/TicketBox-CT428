<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (empty($_SESSION['user']) && empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$loggedUser   = $_SESSION['user'] ?? [];
$loggedUserId = (int)($loggedUser['id'] ?? $_SESSION['user_id'] ?? 0);

if ($loggedUserId <= 0 && !empty($loggedUser['email']) && isset($pdo)) {
    $stmtU = $pdo->prepare("SELECT * FROM nguoidung WHERE email = :email LIMIT 1");
    $stmtU->execute(['email' => $loggedUser['email']]);
    $dbU = $stmtU->fetch(PDO::FETCH_ASSOC);
    if ($dbU) {
        $loggedUserId = (int)$dbU['id'];
        $_SESSION['user'] = array_merge($loggedUser, $dbU);
        $loggedUser = $_SESSION['user'];
    }
}

$userName   = $loggedUser['ho_ten'] ?? $loggedUser['name'] ?? 'Ban tổ chức';
$userAvatar = (!empty($loggedUser['avatar']) && file_exists(__DIR__ . '/' . $loggedUser['avatar']))
    ? $loggedUser['avatar']
    : '../assets/images/default-avatar.png';

// Hàm xử lý upload ảnh an toàn
function handleEventImageUpload(string $fieldName, string $prefix, int $userId): ?string {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES[$fieldName];
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Dung lượng ảnh vượt quá 5MB.');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('Chỉ hỗ trợ định dạng JPG, PNG, WEBP.');
    }
    $uploadDir = __DIR__ . '/../assets/uploads/events/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileName = $prefix . '_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
        throw new RuntimeException('Không thể lưu tệp ảnh lên máy chủ.');
    }
    return '../assets/uploads/events/' . $fileName;
}

// Xác định đúng tên bảng danh mục trong CSDL (danh_muc hoặc danhmuc)
function getDanhMucTable(PDO $pdo): string {
    static $table = null;
    if ($table !== null) {
        return $table;
    }
    $stmt = $pdo->query("SHOW TABLES LIKE 'danh_muc'");
    if ($stmt && $stmt->fetchColumn() !== false) {
        $table = 'danh_muc';
        return $table;
    }
    $table = 'danhmuc';
    return $table;
}

// Lấy tên cột hiển thị của bảng danh_muc (ten_danh_muc / ten / name)
function getDanhMucNameCol(PDO $pdo): string {
    static $col = null;
    if ($col !== null) {
        return $col;
    }
    $table = getDanhMucTable($pdo);
    $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
    foreach (['ten_danh_muc', 'ten', 'name', 'tieu_de'] as $candidate) {
        if (in_array($candidate, $cols, true)) {
            $col = $candidate;
            return $col;
        }
    }
    $col = $cols[1] ?? 'ten_danh_muc';
    return $col;
}

// Lấy toàn bộ danh mục từ CSDL
function getDanhMucList(PDO $pdo): array {
    try {
        $table   = getDanhMucTable($pdo);
        $nameCol = getDanhMucNameCol($pdo);
        $stmt = $pdo->query("SELECT id, `{$nameCol}` AS ten_danh_muc FROM `{$table}` ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

// Chuyển giá trị chọn từ form (ID hoặc tên danh mục) thành danh_muc_id hợp lệ trong bảng danh_muc
function resolveDanhMucId(PDO $pdo, string $categoryInput): ?int {
    $categoryInput = trim($categoryInput);
    if ($categoryInput === '') {
        return null;
    }
    $table   = getDanhMucTable($pdo);
    $nameCol = getDanhMucNameCol($pdo);

    if (ctype_digit($categoryInput)) {
        $stmt = $pdo->prepare("SELECT id FROM `{$table}` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$categoryInput]);
        $foundId = $stmt->fetchColumn();
        if ($foundId !== false) {
            return (int)$foundId;
        }
    }

    $stmt = $pdo->prepare("SELECT id FROM `{$table}` WHERE `{$nameCol}` = :ten LIMIT 1");
    $stmt->execute(['ten' => $categoryInput]);
    $foundId = $stmt->fetchColumn();
    if ($foundId !== false) {
        return (int)$foundId;
    }

    // Tự thêm vào bảng danh_muc nếu chưa tồn tại
    $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('slug', $cols, true)) {
        $ascii = function_exists('iconv') ? @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $categoryInput) : false;
        if ($ascii === false || $ascii === '') {
            $ascii = $categoryInput;
        }
        $slug = strtolower(trim((string)preg_replace('/[^A-Za-z0-9-]+/', '-', $ascii), '-'));
        $stmtIns = $pdo->prepare("INSERT INTO `{$table}` (`{$nameCol}`, slug) VALUES (:ten, :slug)");
        $stmtIns->execute(['ten' => $categoryInput, 'slug' => $slug ?: 'danh-muc-' . time()]);
    } else {
        $stmtIns = $pdo->prepare("INSERT INTO `{$table}` (`{$nameCol}`) VALUES (:ten)");
        $stmtIns->execute(['ten' => $categoryInput]);
    }
    return (int)$pdo->lastInsertId();
}

// =========================================================================
// 2. XỬ LÝ API AJAX LƯU TỪNG BƯỚC & XUẤT BẢN SỰ KIỆN
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($pdo) || $loggedUserId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Phiên đăng nhập không hợp lệ hoặc mất kết nối CSDL.']);
        exit;
    }

    $action  = $_POST['ajax_action'];
    $eventId = (int)($_POST['event_id'] ?? 0);

    try {
        // --- LƯU BƯỚC 1: THÔNG TIN CƠ BẢN & ĐỊA ĐIỂM ---
        if ($action === 'save_step_1') {
            $tenSuKien = trim($_POST['event_name'] ?? '');
            $moTa      = trim($_POST['event_description'] ?? '');
            $danhMucId = resolveDanhMucId($pdo, trim($_POST['category'] ?? ''));
            $tenBtc    = trim($_POST['organizer_name'] ?? $userName);
            $moTaBtc   = trim($_POST['organizer_info'] ?? '');
            $hinhThuc  = ($_POST['event_mode'] ?? 'offline') === 'online' ? 'online' : 'offline';

            if ($tenSuKien === '') {
                throw new RuntimeException('Vui lòng nhập tên sự kiện.');
            }
            if ($moTa === '' || strip_tags($moTa) === '') {
                throw new RuntimeException('Vui lòng nhập thông tin giới thiệu sự kiện.');
            }

            $diaDiem    = '';
            $linkOnline = null;

            if ($hinhThuc === 'online') {
                $linkOnline = trim($_POST['online_url'] ?? '');
                if ($linkOnline === '') {
                    throw new RuntimeException('Vui lòng nhập đường dẫn sự kiện trực tuyến.');
                }
                $diaDiem = 'Sự kiện Online (' . $linkOnline . ')';
            } else {
                $venue    = trim($_POST['venue_name'] ?? '');
                $street   = trim($_POST['street_address'] ?? '');
                $ward     = trim($_POST['ward'] ?? '');
                $district = trim($_POST['district'] ?? '');
                $city     = trim($_POST['city'] ?? '');

                if ($street === '' || $ward === '' || $district === '' || $city === '') {
                    throw new RuntimeException('Vui lòng điền đầy đủ địa chỉ tổ chức sự kiện.');
                }
                $parts   = array_filter([$venue, $street, $ward, $district, $city]);
                $diaDiem = implode(', ', $parts);
            }

            $posterPath = handleEventImageUpload('poster', 'poster', $loggedUserId);
            $bannerPath = handleEventImageUpload('banner', 'banner', $loggedUserId);

            if ($eventId > 0) {
                $stmtOld = $pdo->prepare("SELECT hinh_anh, banner FROM sukien WHERE id = :id AND nguoi_tao_id = :uid LIMIT 1");
                $stmtOld->execute(['id' => $eventId, 'uid' => $loggedUserId]);
                $oldRow = $stmtOld->fetch(PDO::FETCH_ASSOC);
                if (!$oldRow) {
                    throw new RuntimeException('Không tìm thấy sự kiện hoặc bạn không có quyền chỉnh sửa.');
                }

                $finalPoster = $posterPath ?? $oldRow['hinh_anh'];
                $finalBanner = $bannerPath ?? $oldRow['banner'];

                $sql = "UPDATE sukien SET 
                            ten_su_kien = :ten,
                            mo_ta = :mota,
                            danh_muc_id = :danh_muc_id,
                            ten_ban_to_chuc = :btc,
                            dia_diem = :diadiem,
                            hinh_thuc = :hinhthuc,
                            link_online = :link,
                            hinh_anh = :poster,
                            banner = :banner
                        WHERE id = :id AND nguoi_tao_id = :uid";
                $pdo->prepare($sql)->execute([
                    'ten'         => $tenSuKien,
                    'mota'        => $moTa,
                    'danh_muc_id' => $danhMucId,
                    'btc'         => $tenBtc,
                    'diadiem'     => $diaDiem,
                    'hinhthuc'    => $hinhThuc,
                    'link'        => $linkOnline,
                    'poster'      => $finalPoster,
                    'banner'      => $finalBanner,
                    'id'          => $eventId,
                    'uid'         => $loggedUserId
                ]);
            } else {
                $finalPoster = $posterPath ?? ($bannerPath ?? '../assets/images/default-event.jpg');
                $finalBanner = $bannerPath ?? $finalPoster;

                $sukienCols   = $pdo->query("SHOW COLUMNS FROM sukien")->fetchAll(PDO::FETCH_COLUMN);
                $insertCols   = ['nguoi_tao_id', 'ten_su_kien', 'mo_ta', 'danh_muc_id', 'ten_ban_to_chuc', 'dia_diem', 'hinh_thuc', 'link_online', 'thoi_gian', 'gia_thap_nhat', 'hinh_anh', 'banner', 'trang_thai'];
                $insertVals   = [':uid', ':ten', ':mota', ':danh_muc_id', ':btc', ':diadiem', ':hinhthuc', ':link', 'DATE_ADD(NOW(), INTERVAL 7 DAY)', '0', ':poster', ':banner', "'nhap'"];
                $insertParams = [
                    'uid'         => $loggedUserId,
                    'ten'         => $tenSuKien,
                    'mota'        => $moTa,
                    'danh_muc_id' => $danhMucId,
                    'btc'         => $tenBtc,
                    'diadiem'     => $diaDiem,
                    'hinhthuc'    => $hinhThuc,
                    'link'        => $linkOnline,
                    'poster'      => $finalPoster,
                    'banner'      => $finalBanner
                ];

                if (in_array('nguoi_tao', $sukienCols, true)) {
                    $insertCols[] = 'nguoi_tao';
                    $insertVals[] = ':nguoi_tao';
                    $insertParams['nguoi_tao'] = $loggedUserId;
                }
                if (in_array('so_luong_toi_da', $sukienCols, true)) {
                    $insertCols[] = 'so_luong_toi_da';
                    $insertVals[] = ':so_luong_toi_da';
                    $insertParams['so_luong_toi_da'] = 0;
                }

                $sql = "INSERT INTO sukien (" . implode(', ', $insertCols) . ") VALUES (" . implode(', ', $insertVals) . ")";
                $pdo->prepare($sql)->execute($insertParams);
                $eventId = (int)$pdo->lastInsertId();
            }

            echo json_encode([
                'status'   => 'success',
                'event_id' => $eventId,
                'message'  => 'Đã lưu thông tin Bước 1!'
            ]);
            exit;
        }

        // --- LƯU BƯỚC 2: THỜI GIAN & LOẠI VÉ ---
        if ($action === 'save_step_2') {
            if ($eventId <= 0) {
                throw new RuntimeException('Vui lòng lưu thông tin Bước 1 trước.');
            }

            $startTime = trim($_POST['start_time'] ?? '');
            $endTime   = trim($_POST['end_time'] ?? '');
            $tickets   = json_decode($_POST['tickets_json'] ?? '[]', true);

            if ($startTime === '' || $endTime === '') {
                throw new RuntimeException('Vui lòng chọn thời gian bắt đầu và kết thúc sự kiện.');
            }
            if (strtotime($endTime) <= strtotime($startTime)) {
                throw new RuntimeException('Thời gian kết thúc phải sau thời gian bắt đầu.');
            }
            if (!is_array($tickets) || count($tickets) === 0) {
                throw new RuntimeException('Vui lòng tạo ít nhất 1 loại vé cho sự kiện.');
            }

            $minPrice = null;
            foreach ($tickets as $t) {
                $tName = trim($t['name'] ?? '');
                $tQty  = (int)($t['qty'] ?? 0);
                $tPrice = max(0, (int)($t['price'] ?? 0));
                if ($tName === '' || $tQty <= 0) {
                    throw new RuntimeException('Tên loại vé không được trống và số lượng vé phải lớn hơn 0.');
                }
                if ($minPrice === null || $tPrice < $minPrice) {
                    $minPrice = $tPrice;
                }
            }

            $pdo->beginTransaction();

            $stmtUpdEvt = $pdo->prepare("
                UPDATE sukien 
                SET thoi_gian = :start_t, thoi_gian_ket_thuc = :end_t, gia_thap_nhat = :min_p 
                WHERE id = :id AND nguoi_tao_id = :uid
            ");
            $stmtUpdEvt->execute([
                'start_t' => date('Y-m-d H:i:s', strtotime($startTime)),
                'end_t'   => date('Y-m-d H:i:s', strtotime($endTime)),
                'min_p'   => (int)$minPrice,
                'id'      => $eventId,
                'uid'     => $loggedUserId
            ]);

            // Làm mới danh sách hạng vé của sự kiện
            $pdo->prepare("DELETE FROM loaive WHERE su_kien_id = :id")->execute(['id' => $eventId]);

            $stmtInsTicket = $pdo->prepare("
                INSERT INTO loaive (su_kien_id, ten_loai_ve, gia_ve, so_luong, mo_ta, bat_dau_ban, ket_thuc_ban, da_het_ve)
                VALUES (:sid, :ten, :gia, :sl, :mota, :bd, :kt, 0)
            ");

            foreach ($tickets as $t) {
                $stmtInsTicket->execute([
                    'sid'  => $eventId,
                    'ten'  => trim($t['name']),
                    'gia'  => max(0, (int)$t['price']),
                    'sl'   => max(1, (int)$t['qty']),
                    'mota' => trim($t['desc'] ?? ''),
                    'bd'   => !empty($t['sale_start']) ? date('Y-m-d H:i:s', strtotime($t['sale_start'])) : null,
                    'kt'   => !empty($t['sale_end']) ? date('Y-m-d H:i:s', strtotime($t['sale_end'])) : null,
                ]);
            }

            $pdo->commit();

            echo json_encode([
                'status'   => 'success',
                'event_id' => $eventId,
                'message'  => 'Đã lưu lịch diễn và ' . count($tickets) . ' hạng vé!'
            ]);
            exit;
        }

        // --- LƯU BƯỚC 3: CÀI ĐẶT HIỂN THỊ ---
        if ($action === 'save_step_3') {
            if ($eventId <= 0) {
                throw new RuntimeException('Không tìm thấy sự kiện để lưu cài đặt.');
            }

            $customSlug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['custom_url'] ?? '')));
            $privacy    = ($_POST['privacy'] ?? 'public') === 'link_only' ? 'link_only' : 'public';
            $thankMsg   = trim($_POST['thank_you_msg'] ?? '');

            $pdo->prepare("
                UPDATE sukien 
                SET url_tuy_chinh = :slug, quyen_rieng_tu = :priv, loi_nhan_cam_on = :msg
                WHERE id = :id AND nguoi_tao_id = :uid
            ")->execute([
                'slug' => $customSlug !== '' ? $customSlug : null,
                'priv' => $privacy,
                'msg'  => $thankMsg,
                'id'   => $eventId,
                'uid'  => $loggedUserId
            ]);

            echo json_encode([
                'status'   => 'success',
                'event_id' => $eventId,
                'message'  => 'Đã lưu cài đặt sự kiện!'
            ]);
            exit;
        }

        // --- LƯU BƯỚC 4 & XUẤT BẢN SỰ KIỆN ---
        if ($action === 'publish_event') {
            if ($eventId <= 0) {
                throw new RuntimeException('Vui lòng hoàn thành các bước trước khi xuất bản.');
            }

            $bankInfo = [
                'account_holder' => trim($_POST['account_holder'] ?? ''),
                'account_number' => trim($_POST['account_number'] ?? ''),
                'bank_name'      => trim($_POST['bank_name'] ?? ''),
                'bank_branch'    => trim($_POST['bank_branch'] ?? ''),
                'business_type'  => trim($_POST['business_type'] ?? 'Cá nhân'),
                'tax_code'       => trim($_POST['tax_code'] ?? '')
            ];

            if ($bankInfo['account_holder'] === '' || $bankInfo['account_number'] === '' || $bankInfo['bank_name'] === '') {
                throw new RuntimeException('Vui lòng nhập đầy đủ thông tin tài khoản ngân hàng nhận doanh thu.');
            }

            $mode = ($_POST['publish_mode'] ?? 'cong_khai') === 'nhap' ? 'nhap' : 'cong_khai';

            $pdo->prepare("
                UPDATE sukien 
                SET thong_tin_thanh_toan = :pay, trang_thai = :st
                WHERE id = :id AND nguoi_tao_id = :uid
            ")->execute([
                'pay' => json_encode($bankInfo, JSON_UNESCAPED_UNICODE),
                'st'  => $mode,
                'id'  => $eventId,
                'uid' => $loggedUserId
            ]);

            echo json_encode([
                'status'       => 'success',
                'event_id'     => $eventId,
                'redirect_url' => $mode === 'cong_khai' ? 'event-detail.php?id=' . $eventId : 'create-event.php?view=my-events',
                'message'      => $mode === 'cong_khai'
                    ? 'Xuất bản sự kiện thành công! Sự kiện đã hiển thị công khai.'
                    : 'Đã lưu bản nháp sự kiện!'
            ]);
            exit;
        }

        // --- XÓA SỰ KIỆN ---
        if ($action === 'delete_event') {
            if ($eventId > 0) {
                $pdo->prepare("DELETE FROM loaive WHERE su_kien_id = :id")->execute(['id' => $eventId]);
                $pdo->prepare("DELETE FROM sukien WHERE id = :id AND nguoi_tao_id = :uid")
                    ->execute(['id' => $eventId, 'uid' => $loggedUserId]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Đã xóa sự kiện.']);
            exit;
        }
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// =========================================================================
// 3. NẠP DỮ LIỆU CHỈNH SỬA (NẾU CÓ ?edit_id=...) HOẶC DANH SÁCH SỰ KIỆN ĐÃ TẠO
// =========================================================================
$viewMode       = $_GET['view'] ?? 'wizard'; // 'wizard' | 'my-events' | 'reports' | 'terms'
$editEventId    = (int)($_GET['edit_id'] ?? 0);
$editingEvent   = null;
$editingTickets = [];
$editingBank    = [];
$danhMucList    = isset($pdo) ? getDanhMucList($pdo) : [];

if ($editEventId > 0 && isset($pdo)) {
    $stmtEdit = $pdo->prepare("SELECT * FROM sukien WHERE id = :id AND nguoi_tao_id = :uid LIMIT 1");
    $stmtEdit->execute(['id' => $editEventId, 'uid' => $loggedUserId]);
    $editingEvent = $stmtEdit->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($editingEvent) {
        $stmtT = $pdo->prepare("SELECT * FROM loaive WHERE su_kien_id = :id ORDER BY id ASC");
        $stmtT->execute(['id' => $editEventId]);
        $editingTickets = $stmtT->fetchAll(PDO::FETCH_ASSOC);
        $editingBank    = json_decode((string)($editingEvent['thong_tin_thanh_toan'] ?? '{}'), true) ?: [];
    }
}

// Lấy danh sách sự kiện của Ban tổ chức hiện tại
$organizerEvents  = [];
$totalRevenue     = 0;
$totalTicketsSold = 0;

if (isset($pdo) && $loggedUserId > 0) {
    try {
        $dmTable   = getDanhMucTable($pdo);
        $dmNameCol = getDanhMucNameCol($pdo);
        $stmtMyEv = $pdo->prepare("
            SELECT s.*,
                   dm.`{$dmNameCol}` AS ten_danh_muc,
                   (SELECT COUNT(*) FROM ve v WHERE v.su_kien_id = s.id AND v.trang_thai = 'thanh-cong') AS ve_da_ban,
                   (SELECT COALESCE(SUM(CAST(REPLACE(REPLACE(v.gia_ve, '.', ''), ' đ', '') AS UNSIGNED)), 0)
                    FROM ve v WHERE v.su_kien_id = s.id AND v.trang_thai = 'thanh-cong') AS doanh_thu
            FROM sukien s
            LEFT JOIN `{$dmTable}` dm ON s.danh_muc_id = dm.id
            WHERE s.nguoi_tao_id = :uid
            ORDER BY s.id DESC
        ");
        $stmtMyEv->execute(['uid' => $loggedUserId]);
        $organizerEvents = $stmtMyEv->fetchAll(PDO::FETCH_ASSOC);

        foreach ($organizerEvents as $oe) {
            $totalTicketsSold += (int)$oe['ve_da_ban'];
            $totalRevenue     += (int)$oe['doanh_thu'];
        }
    } catch (PDOException $e) {
        $organizerEvents = [];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo sự kiện - Organizer Center</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: #111115;
            color: #ffffff;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* --- SIDEBAR TRÁI --- */
        .sidebar {
            width: 260px;
            background-color: #18181c;
            border-right: 1px solid #2a2a32;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #2a2a32;
            cursor: pointer;
            text-decoration: none;
        }

        .sidebar-header h3 {
            color: #26c973;
            font-size: 16px;
            font-weight: 700;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            flex-grow: 1;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #a0a3bd;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li.active a {
            background-color: rgba(38, 201, 115, 0.1);
            color: #26c973;
            border-left: 3px solid #26c973;
        }

        .sidebar-menu li a i {
            margin-right: 15px;
            font-size: 16px;
            width: 18px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #2a2a32;
        }

        /* --- NỘI DUNG CHÍNH --- */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background: radial-gradient(circle at top right, #1a1a24, #111115);
        }

        /* --- HEADER BÊN TRONG --- */
        .top-header {
            display: flex;
            justify-content: space-between;
            padding: 15px 30px;
            border-bottom: 1px solid #2a2a32;
            background-color: #111115;
            align-items: center;
            gap: 15px;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .back-home-link {
            color: #a0a3bd;
            text-decoration: none;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
        }

        .back-home-link:hover {
            color: #26c973;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-create {
            background-color: #26c973;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-create:hover {
            background-color: #20b264;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            text-decoration: none;
        }

        .user-profile img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #2a2a32;
            border: 1.5px solid #26c973;
        }

        /* --- THANH TIẾN TRÌNH BƯỚC --- */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            padding: 18px 40px;
            background-color: #15151a;
            border-bottom: 1px solid #2a2a32;
            align-items: center;
            position: sticky;
            top: 65px;
            z-index: 25;
            gap: 12px;
            flex-wrap: wrap;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #a0a3bd;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 6px;
            transition: 0.2s;
        }

        .step:hover {
            background: rgba(255, 255, 255, 0.04);
        }

        .step.active {
            color: #fff;
        }

        .step.completed {
            color: #26c973;
        }

        .step-number {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background-color: #2a2a32;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .step.active .step-number,
        .step.completed .step-number {
            background-color: #26c973;
            color: #fff;
        }

        .step-actions {
            display: flex;
            gap: 10px;
        }

        .btn-prev {
            background: #2a2a32;
            color: #fff;
            padding: 9px 18px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-save {
            background: #fff;
            color: #111;
            padding: 9px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-next {
            background: #26c973;
            color: #fff;
            padding: 9px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }

        /* --- KHUNG NHẬP FORM --- */
        .form-container {
            padding: 30px 40px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .step-panel {
            display: none;
        }

        .step-panel.active {
            display: block;
        }

        .form-section {
            background-color: #18181c;
            border: 1px solid #2a2a32;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .section-title {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-title span.req {
            color: #ff4d4f;
            margin-right: 5px;
        }

        /* Khung Upload Ảnh */
        .upload-wrapper {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .upload-box {
            border: 1.5px dashed #4a4a55;
            border-radius: 8px;
            background-color: #222228;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            color: #a0a3bd;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .upload-box:hover {
            border-color: #26c973;
            background-color: rgba(38, 201, 115, 0.05);
        }

        .upload-box i {
            font-size: 30px;
            color: #26c973;
            margin-bottom: 15px;
        }

        .upload-box.poster {
            width: 30%;
            height: 340px;
        }

        .upload-box.banner {
            width: 70%;
            height: 340px;
        }

        /* Input Fields */
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 8px;
        }

        .form-group label span {
            color: #ff4d4f;
        }

        .form-control {
            width: 100%;
            background-color: #fff;
            border: none;
            padding: 12px 15px;
            border-radius: 4px;
            font-size: 14px;
            color: #222;
            outline: none;
        }

        .form-control:focus {
            box-shadow: 0 0 0 2px rgba(38, 201, 115, 0.6);
        }

        /* Ticket Card trong Bước 2 */
        .ticket-item-card {
            background: #222228;
            border: 1px solid #32323c;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .ticket-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #32323c;
        }

        .btn-remove-ticket {
            background: rgba(255, 77, 79, 0.15);
            color: #ff4d4f;
            border: 1px solid #ff4d4f;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-add-ticket {
            background: rgba(38, 201, 115, 0.12);
            color: #26c973;
            border: 1px dashed #26c973;
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-add-ticket:hover {
            background: rgba(38, 201, 115, 0.22);
        }

        /* Toast thông báo */
        .toast-box {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: #1e293b;
            color: #fff;
            padding: 14px 20px;
            border-radius: 8px;
            border-left: 4px solid #26c973;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            z-index: 9999;
            font-size: 13.5px;
            font-weight: 600;
            display: none;
            align-items: center;
            gap: 10px;
        }

        .toast-box.error {
            border-left-color: #ff4d4f;
        }

        /* Bảng sự kiện của tôi */
        .org-event-card {
            background: #18181c;
            border: 1px solid #2a2a32;
            border-radius: 10px;
            padding: 18px;
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 16px;
        }

        .org-event-card img {
            width: 180px;
            height: 105px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-public {
            background: rgba(38, 201, 115, 0.15);
            color: #26c973;
        }

        .badge-draft {
            background: rgba(250, 173, 20, 0.15);
            color: #faad14;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR TRÁI -->
    <div class="sidebar">
        <div>
            <a href="home.php" class="sidebar-header" title="Về trang chủ Ticketbox">
                <i class="fa-solid fa-ticket" style="color: #26c973; font-size: 20px;"></i>
                <h3>Organizer Center</h3>
            </a>

            <ul class="sidebar-menu">
                <li class="<?php echo $viewMode === 'wizard' ? 'active' : ''; ?>">
                    <a href="create-event.php"><i class="fa-solid fa-circle-plus"></i> Tạo sự kiện mới</a>
                </li>
                <li class="<?php echo $viewMode === 'my-events' ? 'active' : ''; ?>">
                    <a href="create-event.php?view=my-events"><i class="fa-solid fa-calendar-check"></i> Sự kiện của tôi (<?php echo count($organizerEvents); ?>)</a>
                </li>
                <li class="<?php echo $viewMode === 'reports' ? 'active' : ''; ?>">
                    <a href="create-event.php?view=reports"><i class="fa-solid fa-chart-line"></i> Quản lý báo cáo</a>
                </li>
                <li class="<?php echo $viewMode === 'terms' ? 'active' : ''; ?>">
                    <a href="create-event.php?view=terms"><i class="fa-solid fa-file-contract"></i> Điều khoản Ban tổ chức</a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <span style="color: #a0a3bd; font-size: 13px;">Ngôn ngữ: </span>
            <span style="background: #2a2a32; padding: 4px 10px; border-radius: 12px; font-size: 12px; float:right;">Vie 🇻🇳</span>
        </div>
    </div>

    <!-- NỘI DUNG CHÍNH -->
    <div class="main-content">

        <!-- TOP HEADER -->
        <div class="top-header">
            <a href="home.php" class="back-home-link">
                <i class="fa-solid fa-arrow-left"></i> Quay lại trang mua vé Ticketbox
            </a>

            <div class="header-right">
                <a href="create-event.php" class="btn-create"><i class="fa-solid fa-plus"></i> Tạo sự kiện</a>
                <a href="my-tickets.php?tab=profile" class="user-profile">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Avatar">
                    <span><?php echo htmlspecialchars($userName); ?></span>
                    <i class="fa-solid fa-caret-down" style="font-size: 12px;"></i>
                </a>
            </div>
        </div>

        <?php if ($viewMode === 'my-events'): ?>
            <!-- ==================== GIAO DIỆN: SỰ KIỆN CỦA TÔI ==================== -->
            <div class="form-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h2 style="font-size: 22px;">Sự kiện của tôi (<?php echo count($organizerEvents); ?>)</h2>
                    <a href="create-event.php" class="btn-create"><i class="fa-solid fa-plus"></i> Tạo sự kiện mới</a>
                </div>

                <?php if (empty($organizerEvents)): ?>
                    <div class="form-section" style="text-align: center; padding: 60px 20px;">
                        <i class="fa-regular fa-calendar-plus" style="font-size: 46px; color: #26c973; margin-bottom: 14px;"></i>
                        <p style="color: #a0a3bd; margin-bottom: 18px;">Bạn chưa tạo sự kiện nào trên Organizer Center.</p>
                        <a href="create-event.php" class="btn-create" style="display: inline-flex;">Bắt đầu tạo sự kiện đầu tiên</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($organizerEvents as $ev): ?>
                        <div class="org-event-card" id="org-card-<?php echo (int)$ev['id']; ?>">
                            <img src="<?php echo htmlspecialchars($ev['banner'] ?: $ev['hinh_anh']); ?>"
                                 alt="<?php echo htmlspecialchars($ev['ten_su_kien']); ?>"
                                 onerror="this.src='../assets/images/default-event.jpg'">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                    <?php if (($ev['trang_thai'] ?? 'cong_khai') === 'cong_khai'): ?>
                                        <span class="badge-status badge-public"><i class="fa-solid fa-globe"></i> Đang công khai</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-draft"><i class="fa-solid fa-pen-ruler"></i> Bản nháp</span>
                                    <?php endif; ?>
                                    <span style="font-size: 12px; color: #a0a3bd;"><?php echo htmlspecialchars($ev['ten_danh_muc'] ?? 'Sự kiện'); ?></span>
                                </div>

                                <h3 style="font-size: 17px; margin-bottom: 6px;"><?php echo htmlspecialchars($ev['ten_su_kien']); ?></h3>
                                <p style="font-size: 13px; color: #a0a3bd; margin-bottom: 4px;">
                                    <i class="fa-regular fa-clock"></i> <?php echo date('H:i - d/m/Y', strtotime($ev['thoi_gian'])); ?>
                                    &nbsp;|&nbsp;
                                    <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($ev['dia_diem']); ?>
                                </p>
                                <p style="font-size: 13px; color: #26c973; font-weight: 600;">
                                    Đã bán: <?php echo (int)$ev['ve_da_ban']; ?> vé &bull; Doanh thu: <?php echo number_format((int)$ev['doanh_thu'], 0, ',', '.'); ?> đ
                                </p>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 8px; flex-shrink: 0;">
                                <a href="event-detail.php?id=<?php echo (int)$ev['id']; ?>" class="btn-next" style="text-decoration: none; text-align: center; font-size: 13px;">
                                    <i class="fa-solid fa-eye"></i> Xem trang vé
                                </a>
                                <a href="create-event.php?edit_id=<?php echo (int)$ev['id']; ?>" class="btn-save" style="text-decoration: none; text-align: center; font-size: 13px;">
                                    <i class="fa-solid fa-pen"></i> Chỉnh sửa
                                </a>
                                <button type="button" class="btn-remove-ticket" onclick="deleteOrganizerEvent(<?php echo (int)$ev['id']; ?>)">
                                    <i class="fa-solid fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php elseif ($viewMode === 'reports'): ?>
            <!-- ==================== GIAO DIỆN: QUẢN LÝ BÁO CÁO ==================== -->
            <div class="form-container">
                <h2 style="font-size: 22px; margin-bottom: 24px;">Báo cáo doanh thu & Vé bán</h2>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
                    <div class="form-section" style="margin-bottom: 0;">
                        <div style="color: #a0a3bd; font-size: 13px; margin-bottom: 8px;">Tổng sự kiện đã tạo</div>
                        <div style="font-size: 28px; font-weight: 700; color: #fff;"><?php echo count($organizerEvents); ?></div>
                    </div>
                    <div class="form-section" style="margin-bottom: 0;">
                        <div style="color: #a0a3bd; font-size: 13px; margin-bottom: 8px;">Tổng số vé đã bán</div>
                        <div style="font-size: 28px; font-weight: 700; color: #26c973;"><?php echo number_format($totalTicketsSold, 0, ',', '.'); ?> vé</div>
                    </div>
                    <div class="form-section" style="margin-bottom: 0;">
                        <div style="color: #a0a3bd; font-size: 13px; margin-bottom: 8px;">Tổng doanh thu ghi nhận</div>
                        <div style="font-size: 28px; font-weight: 700; color: #26c973;"><?php echo number_format($totalRevenue, 0, ',', '.'); ?> đ</div>
                    </div>
                </div>
            </div>

        <?php elseif ($viewMode === 'terms'): ?>
            <!-- ==================== GIAO DIỆN: ĐIỀU KHOẢN BAN TỔ CHỨC ==================== -->
            <div class="form-container">
                <div class="form-section">
                    <h2 style="font-size: 20px; margin-bottom: 16px; color: #26c973;">Điều khoản & Quy định dành cho Ban tổ chức</h2>
                    <p style="color: #d1d5db; line-height: 1.8; font-size: 14px; margin-bottom: 12px;">
                        1. Ban tổ chức chịu trách nhiệm hoàn toàn về tính hợp pháp, giấy phép biểu diễn và bản quyền nội dung của sự kiện đăng tải trên Ticketbox.
                    </p>
                    <p style="color: #d1d5db; line-height: 1.8; font-size: 14px; margin-bottom: 12px;">
                        2. Thông tin thời gian, địa điểm, sơ đồ hạng vé và chính sách hoàn/hủy vé phải được công bố minh bạch trước khi mở bán.
                    </p>
                    <p style="color: #d1d5db; line-height: 1.8; font-size: 14px;">
                        3. Doanh thu bán vé sẽ được đối soát và chuyển khoản về tài khoản ngân hàng đã đăng ký tại Bước 4 trong vòng 03 - 05 ngày làm việc sau khi sự kiện kết thúc.
                    </p>
                </div>
            </div>

        <?php else: ?>
            <!-- ==================== GIAO DIỆN: WIZARD 4 BƯỚC TẠO / SỬA SỰ KIỆN ==================== -->
            <div class="step-indicator">
                <div class="step active" data-step="1" onclick="goToStep(1)">
                    <div class="step-number">1</div>
                    Thông tin sự kiện
                </div>
                <div class="step" data-step="2" onclick="goToStep(2)">
                    <div class="step-number">2</div>
                    Thời gian & Loại vé
                </div>
                <div class="step" data-step="3" onclick="goToStep(3)">
                    <div class="step-number">3</div>
                    Cài đặt
                </div>
                <div class="step" data-step="4" onclick="goToStep(4)">
                    <div class="step-number">4</div>
                    Thông tin thanh toán
                </div>

                <div class="step-actions">
                    <button type="button" class="btn-prev" id="btnPrevStep" style="display: none;" onclick="prevStep()">Quay lại</button>
                    <button type="button" class="btn-save" id="btnSaveDraft">Lưu</button>
                    <button type="button" class="btn-next" id="btnNextStep">Tiếp tục</button>
                </div>
            </div>

            <div class="form-container">
                <input type="hidden" id="currentEventId" value="<?php echo $editingEvent ? (int)$editingEvent['id'] : 0; ?>">

                <!-- ==================== BƯỚC 1: THÔNG TIN SỰ KIỆN ==================== -->
                <div class="step-panel active" id="stepPanel1">
                    <div class="form-section">
                        <div class="section-title">
                            <div><span class="req">*</span> Upload hình ảnh</div>
                            <span style="font-size: 12px; color: #a0a3bd; font-weight: 400;">Hỗ trợ JPG, PNG, WEBP (tối đa 5MB/ảnh)</span>
                        </div>

                        <div class="upload-wrapper">
                            <?php
                            $posterBg = !empty($editingEvent['hinh_anh'])
                                ? "background-image: url('" . htmlspecialchars($editingEvent['hinh_anh']) . "'); background-size: cover; background-position: center; border: none;"
                                : "";
                            $bannerBg = !empty($editingEvent['banner'])
                                ? "background-image: url('" . htmlspecialchars($editingEvent['banner']) . "'); background-size: cover; background-position: center; border: none;"
                                : "";
                            ?>
                            <!-- Khung Poster -->
                            <div class="upload-box poster" id="boxPoster" style="<?php echo $posterBg; ?>" onclick="document.getElementById('inputPoster').click()">
                                <?php if (empty($editingEvent['hinh_anh'])): ?>
                                    <i class="fa-solid fa-box-archive"></i>
                                    <p style="font-size: 13px; font-weight: 500;">Thêm ảnh sự kiện<br>(720x958)</p>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="inputPoster" name="poster" accept="image/jpeg, image/png, image/webp" hidden>

                            <!-- Khung Banner -->
                            <div class="upload-box banner" id="boxBanner" style="<?php echo $bannerBg; ?>" onclick="document.getElementById('inputBanner').click()">
                                <?php if (empty($editingEvent['banner'])): ?>
                                    <i class="fa-solid fa-box-archive"></i>
                                    <p style="font-size: 13px; font-weight: 500;">Thêm ảnh nền sự kiện<br>(1280x720)</p>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="inputBanner" name="banner" accept="image/jpeg, image/png, image/webp" hidden>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label><span>*</span> Tên sự kiện</label>
                                <div style="position: relative;">
                                    <input type="text" id="eventName" class="form-control" placeholder="Nhập tên sự kiện" maxlength="100"
                                           value="<?php echo htmlspecialchars($editingEvent['ten_su_kien'] ?? ''); ?>">
                                    <span id="eventNameCount" style="position: absolute; right: 15px; top: 12px; font-size: 12px; color: #a0a3bd;">
                                        <?php echo mb_strlen($editingEvent['ten_su_kien'] ?? '', 'UTF-8'); ?> / 100
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><span>*</span> Thể loại sự kiện</label>
                                <?php
                                $curCatId = (int)($editingEvent['danh_muc_id'] ?? 0);
                                $defaultCats = [
                                    'Nhạc sống',
                                    'Sân khấu & Nghệ thuật',
                                    'Thể Thao',
                                    'Hội thảo & Workshop',
                                    'Tham quan & Trải nghiệm',
                                    'Khác'
                                ];
                                ?>
                                <select id="eventCategory" class="form-control">
                                    <?php if (!empty($danhMucList)): ?>
                                        <?php foreach ($danhMucList as $dm): ?>
                                            <option value="<?php echo (int)$dm['id']; ?>" <?php echo ($curCatId === (int)$dm['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dm['ten_danh_muc']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <?php foreach ($defaultCats as $catName): ?>
                                            <option value="<?php echo htmlspecialchars($catName); ?>"><?php echo htmlspecialchars($catName); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><span>*</span> Thông tin sự kiện</label>
                            <div id="editor-container" style="height: 280px; background: #fff; color: #333; border-radius: 0 0 4px 4px;"><?php echo $editingEvent['mo_ta'] ?? ''; ?></div>
                            <input type="hidden" id="eventDescription" value="<?php echo htmlspecialchars($editingEvent['mo_ta'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- ĐỊA ĐIỂM -->
                    <div class="form-section">
                        <div class="section-title"><div><span class="req">*</span> Hình thức & Địa chỉ sự kiện</div></div>
                        <?php $isOnlineMode = (($editingEvent['hinh_thuc'] ?? 'offline') === 'online'); ?>
                        <input type="hidden" id="eventMode" value="<?php echo $isOnlineMode ? 'online' : 'offline'; ?>">

                        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                            <button type="button" id="btnOffline" style="background:<?php echo !$isOnlineMode ? '#26c973' : '#3a3a45'; ?>; color:#fff; border:none; padding:9px 25px; border-radius:4px; font-weight:bold; cursor:pointer;">
                                <i class="fa-regular fa-map" style="margin-right:5px;"></i> Offline
                            </button>
                            <button type="button" id="btnOnline" style="background:<?php echo $isOnlineMode ? '#26c973' : '#3a3a45'; ?>; color:#fff; border:none; padding:9px 25px; border-radius:4px; font-weight:bold; cursor:pointer;">
                                <i class="fa-regular fa-circle-play" style="margin-right:5px;"></i> Online
                            </button>
                        </div>

                        <div id="locationFields">
                            <?php if ($isOnlineMode): ?>
                                <div class="form-group">
                                    <label><span>*</span> Đường dẫn sự kiện trực tuyến (Zoom, Google Meet, Microsoft Teams,...)</label>
                                    <input type="url" class="form-control" id="onlineUrl" placeholder="https://..." value="<?php echo htmlspecialchars($editingEvent['link_online'] ?? ''); ?>">
                                </div>
                            <?php else: ?>
                                <div class="form-group">
                                    <label>Tên tòa nhà / Địa điểm cụ thể</label>
                                    <input type="text" class="form-control" id="venueName" placeholder="Ví dụ: Hội trường A - Trường Đại học Cần Thơ" value="<?php echo htmlspecialchars($editingEvent['dia_diem'] ?? ''); ?>">
                                </div>
                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label><span>*</span> Số nhà, tên đường</label>
                                        <input type="text" class="form-control" id="streetAddress" placeholder="Ví dụ: Số 3 đường 3/2">
                                    </div>
                                    <div class="form-group">
                                        <label><span>*</span> Phường / Xã</label>
                                        <input type="text" class="form-control" id="wardInput" placeholder="Ví dụ: Phường Xuân Khánh">
                                    </div>
                                </div>
                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label><span>*</span> Quận / Huyện</label>
                                        <input type="text" class="form-control" id="districtInput" placeholder="Ví dụ: Quận Ninh Kiều">
                                    </div>
                                    <div class="form-group">
                                        <label><span>*</span> Tỉnh / Thành phố</label>
                                        <input type="text" class="form-control" id="cityInput" placeholder="Ví dụ: Thành phố Cần Thơ">
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- BAN TỔ CHỨC -->
                    <div class="form-section">
                        <div class="section-title"><div><span class="req">*</span> Thông tin Ban tổ chức</div></div>
                        <div class="form-group">
                            <label><span>*</span> Tên Ban tổ chức</label>
                            <input type="text" class="form-control" id="organizerName" value="<?php echo htmlspecialchars($editingEvent['ten_ban_to_chuc'] ?? $userName); ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Giới thiệu Ban tổ chức</label>
                            <textarea class="form-control" id="organizerInfo" rows="3" placeholder="Thông tin giới thiệu ngắn về đơn vị tổ chức..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- ==================== BƯỚC 2: THỜI GIAN & LOẠI VÉ ==================== -->
                <div class="step-panel" id="stepPanel2">
                    <div class="form-section">
                        <div class="section-title"><div><span class="req">*</span> Thời gian tổ chức sự kiện</div></div>
                        <?php
                        $defaultStart = !empty($editingEvent['thoi_gian']) ? date('Y-m-d\TH:i', strtotime($editingEvent['thoi_gian'])) : '';
                        $defaultEnd   = !empty($editingEvent['thoi_gian_ket_thuc']) ? date('Y-m-d\TH:i', strtotime($editingEvent['thoi_gian_ket_thuc'])) : '';
                        ?>
                        <div class="form-row-2">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label><span>*</span> Thời gian bắt đầu</label>
                                <input type="datetime-local" class="form-control" id="eventStartTime" value="<?php echo $defaultStart; ?>">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label><span>*</span> Thời gian kết thúc</label>
                                <input type="datetime-local" class="form-control" id="eventEndTime" value="<?php echo $defaultEnd; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <div><span class="req">*</span> Danh sách loại vé</div>
                            <span style="font-size: 12px; color: #a0a3bd; font-weight: 400;">Nhập giá 0 đ nếu là vé miễn phí</span>
                        </div>

                        <div id="ticketsContainer"></div>

                        <button type="button" class="btn-add-ticket" onclick="addTicketItem()">
                            <i class="fa-solid fa-plus"></i> Tạo thêm loại vé mới
                        </button>
                    </div>
                </div>

                <!-- ==================== BƯỚC 3: CÀI ĐẶT ==================== -->
                <div class="step-panel" id="stepPanel3">
                    <div class="form-section">
                        <div class="section-title"><div>Đường dẫn & Quyền riêng tư</div></div>

                        <div class="form-group">
                            <label>Đường dẫn sự kiện tùy chỉnh (Slug)</label>
                            <div style="display: flex; align-items: center; background: #222228; border: 1px solid #3a3a45; border-radius: 4px; overflow: hidden;">
                                <span style="padding: 12px 15px; color: #a0a3bd; font-size: 13px;">ticketbox.vn/events/</span>
                                <input type="text" class="form-control" id="customUrlInput" style="border-radius: 0;"
                                       placeholder="ten-su-kien-cua-ban"
                                       value="<?php echo htmlspecialchars($editingEvent['url_tuy_chinh'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Chế độ hiển thị sự kiện</label>
                            <?php $curPriv = $editingEvent['quyen_rieng_tu'] ?? 'public'; ?>
                            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
                                <label style="display: flex; align-items: center; gap: 10px; font-weight: 500; cursor: pointer;">
                                    <input type="radio" name="eventPrivacy" value="public" <?php echo $curPriv === 'public' ? 'checked' : ''; ?>>
                                    <span><strong>Công khai:</strong> Hiển thị trên trang chủ và kết quả tìm kiếm Ticketbox.</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px; font-weight: 500; cursor: pointer;">
                                    <input type="radio" name="eventPrivacy" value="link_only" <?php echo $curPriv === 'link_only' ? 'checked' : ''; ?>>
                                    <span><strong>Chỉ người có đường dẫn:</strong> Chỉ người nhận được liên kết trực tiếp mới có thể truy cập.</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title"><div>Tin nhắn xác nhận sau khi đặt vé</div></div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Nội dung lời cảm ơn gửi kèm vé điện tử</label>
                            <textarea class="form-control" id="thankYouMsg" rows="4" placeholder="Cảm ơn bạn đã đặt vé tham dự sự kiện! Vui lòng mang mã QR đến quầy check-in trước 30 phút."><?php echo htmlspecialchars($editingEvent['loi_nhan_cam_on'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- ==================== BƯỚC 4: THÔNG TIN THANH TOÁN ==================== -->
                <div class="step-panel" id="stepPanel4">
                    <div class="form-section">
                        <div class="section-title"><div><span class="req">*</span> Thông tin tài khoản ngân hàng nhận doanh thu</div></div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label><span>*</span> Chủ tài khoản (Viết hoa không dấu)</label>
                                <input type="text" class="form-control" id="bankHolder" placeholder="NGUYEN THANH HUY"
                                       value="<?php echo htmlspecialchars($editingBank['account_holder'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label><span>*</span> Số tài khoản</label>
                                <input type="text" class="form-control" id="bankNumber" placeholder="Nhập số tài khoản ngân hàng"
                                       value="<?php echo htmlspecialchars($editingBank['account_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label><span>*</span> Tên ngân hàng</label>
                                <input type="text" class="form-control" id="bankName" placeholder="Ví dụ: Vietcombank, MB Bank, Techcombank..."
                                       value="<?php echo htmlspecialchars($editingBank['bank_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Chi nhánh ngân hàng</label>
                                <input type="text" class="form-control" id="bankBranch" placeholder="Ví dụ: Chi nhánh Cần Thơ"
                                       value="<?php echo htmlspecialchars($editingBank['bank_branch'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title"><div>Thông tin xuất hóa đơn đỏ</div></div>
                        <div class="form-row-2">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Loại hình kinh doanh</label>
                                <?php $bType = $editingBank['business_type'] ?? 'Cá nhân'; ?>
                                <select class="form-control" id="businessType">
                                    <option value="Cá nhân" <?php echo $bType === 'Cá nhân' ? 'selected' : ''; ?>>Cá nhân</option>
                                    <option value="Doanh nghiệp / Tổ chức" <?php echo $bType === 'Doanh nghiệp / Tổ chức' ? 'selected' : ''; ?>>Doanh nghiệp / Tổ chức</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Mã số thuế (Nếu có)</label>
                                <input type="text" class="form-control" id="taxCode" placeholder="Nhập mã số thuế"
                                       value="<?php echo htmlspecialchars($editingBank['tax_code'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <div class="toast-box" id="toastBox">
        <i class="fa-solid fa-circle-check" id="toastIcon" style="color: #26c973;"></i>
        <span id="toastText">Đã lưu thành công!</span>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        const INITIAL_TICKETS = <?php echo json_encode($editingTickets, JSON_UNESCAPED_UNICODE); ?>;
        let currentStep = 1;
        let quill = null;

        function showToast(msg, isError = false) {
            const box = document.getElementById('toastBox');
            const icon = document.getElementById('toastIcon');
            const text = document.getElementById('toastText');
            text.textContent = msg;
            if (isError) {
                box.classList.add('error');
                icon.className = 'fa-solid fa-circle-exclamation';
                icon.style.color = '#ff4d4f';
            } else {
                box.classList.remove('error');
                icon.className = 'fa-solid fa-circle-check';
                icon.style.color = '#26c973';
            }
            box.style.display = 'flex';
            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(() => {
                box.style.display = 'none';
            }, 3500);
        }

        function updateStepUI(step) {
            currentStep = step;
            document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
            const activePanel = document.getElementById('stepPanel' + step);
            if (activePanel) activePanel.classList.add('active');

            document.querySelectorAll('.step-indicator .step').forEach(el => {
                const s = parseInt(el.getAttribute('data-step'), 10);
                el.classList.remove('active', 'completed');
                if (s === step) el.classList.add('active');
                else if (s < step) el.classList.add('completed');
            });

            const prevBtn = document.getElementById('btnPrevStep');
            const nextBtn = document.getElementById('btnNextStep');
            const saveBtn = document.getElementById('btnSaveDraft');

            if (prevBtn) prevBtn.style.display = step > 1 ? 'inline-block' : 'none';
            if (saveBtn) saveBtn.textContent = step === 4 ? 'Lưu nháp' : 'Lưu';
            if (nextBtn) nextBtn.textContent = step === 4 ? 'Hoàn tất & Xuất bản' : 'Tiếp tục';
        }

        function prevStep() {
            if (currentStep > 1) {
                updateStepUI(currentStep - 1);
            }
        }

        function goToStep(targetStep) {
            const eventId = parseInt(document.getElementById('currentEventId')?.value || '0', 10);
            if (targetStep > 1 && eventId <= 0) {
                showToast('Vui lòng nhập và lưu thông tin Bước 1 trước khi chuyển bước.', true);
                return;
            }
            updateStepUI(targetStep);
        }

        // Thêm thẻ hạng vé động tại Bước 2
        let ticketCounter = 0;
        function addTicketItem(data = {}) {
            const container = document.getElementById('ticketsContainer');
            if (!container) return;
            ticketCounter++;
            const idx = ticketCounter;

            const nameVal = data.ten_loai_ve || '';
            const priceVal = data.gia_ve !== undefined ? data.gia_ve : '';
            const qtyVal = data.so_luong || 100;
            const descVal = data.mo_ta || '';
            const saleStart = data.bat_dau_ban ? data.bat_dau_ban.replace(' ', 'T').slice(0, 16) : '';
            const saleEnd = data.ket_thuc_ban ? data.ket_thuc_ban.replace(' ', 'T').slice(0, 16) : '';

            const card = document.createElement('div');
            card.className = 'ticket-item-card';
            card.innerHTML = `
                <div class="ticket-item-header">
                    <strong style="color: #26c973; font-size: 14px;"><i class="fa-solid fa-ticket"></i> Hạng vé #${idx}</strong>
                    <button type="button" class="btn-remove-ticket" onclick="removeTicketItem(this)">
                        <i class="fa-solid fa-trash"></i> Xóa vé
                    </button>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label><span>*</span> Tên loại vé</label>
                        <input type="text" class="form-control t-name" placeholder="Ví dụ: Vé VIP, Vé Tiêu Chuẩn..." value="${nameVal.replace(/"/g, '&quot;')}">
                    </div>
                    <div class="form-row-2" style="gap: 12px;">
                        <div class="form-group">
                            <label><span>*</span> Giá vé (VNĐ)</label>
                            <input type="number" min="0" step="1000" class="form-control t-price" placeholder="500000" value="${priceVal}">
                        </div>
                        <div class="form-group">
                            <label><span>*</span> Tổng số lượng vé</label>
                            <input type="number" min="1" class="form-control t-qty" placeholder="100" value="${qtyVal}">
                        </div>
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Thời gian bắt đầu mở bán</label>
                        <input type="datetime-local" class="form-control t-sale-start" value="${saleStart}">
                    </div>
                    <div class="form-group">
                        <label>Thời gian kết thúc bán vé</label>
                        <input type="datetime-local" class="form-control t-sale-end" value="${saleEnd}">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Mô tả quyền lợi hạng vé</label>
                    <input type="text" class="form-control t-desc" placeholder="Ví dụ: Kèm quà tặng lightstick, check-in lối riêng..." value="${descVal.replace(/"/g, '&quot;')}">
                </div>
            `;
            container.appendChild(card);
        }

        function removeTicketItem(btn) {
            const container = document.getElementById('ticketsContainer');
            if (container.children.length <= 1) {
                showToast('Sự kiện phải có ít nhất 1 loại vé.', true);
                return;
            }
            btn.closest('.ticket-item-card').remove();
        }

        function collectTicketsData() {
            const cards = document.querySelectorAll('#ticketsContainer .ticket-item-card');
            const list = [];
            cards.forEach(card => {
                list.push({
                    name: card.querySelector('.t-name').value.trim(),
                    price: parseInt(card.querySelector('.t-price').value || '0', 10),
                    qty: parseInt(card.querySelector('.t-qty').value || '0', 10),
                    sale_start: card.querySelector('.t-sale-start').value,
                    sale_end: card.querySelector('.t-sale-end').value,
                    desc: card.querySelector('.t-desc').value.trim()
                });
            });
            return list;
        }

        async function saveCurrentStep(advanceNext = false, publishMode = 'cong_khai') {
            const eventIdInput = document.getElementById('currentEventId');
            const eventId = parseInt(eventIdInput.value || '0', 10);
            const formData = new FormData();
            formData.append('event_id', eventId);

            if (currentStep === 1) {
                formData.append('ajax_action', 'save_step_1');
                formData.append('event_name', document.getElementById('eventName').value);
                formData.append('category', document.getElementById('eventCategory').value);
                formData.append('event_description', quill ? quill.root.innerHTML : document.getElementById('eventDescription').value);
                formData.append('event_mode', document.getElementById('eventMode').value);
                formData.append('organizer_name', document.getElementById('organizerName').value);
                formData.append('organizer_info', document.getElementById('organizerInfo').value);

                if (document.getElementById('eventMode').value === 'online') {
                    formData.append('online_url', document.getElementById('onlineUrl')?.value || '');
                } else {
                    formData.append('venue_name', document.getElementById('venueName')?.value || '');
                    formData.append('street_address', document.getElementById('streetAddress')?.value || '');
                    formData.append('ward', document.getElementById('wardInput')?.value || '');
                    formData.append('district', document.getElementById('districtInput')?.value || '');
                    formData.append('city', document.getElementById('cityInput')?.value || '');
                }

                const posterFile = document.getElementById('inputPoster').files[0];
                const bannerFile = document.getElementById('inputBanner').files[0];
                if (posterFile) formData.append('poster', posterFile);
                if (bannerFile) formData.append('banner', bannerFile);

            } else if (currentStep === 2) {
                formData.append('ajax_action', 'save_step_2');
                formData.append('start_time', document.getElementById('eventStartTime').value);
                formData.append('end_time', document.getElementById('eventEndTime').value);
                formData.append('tickets_json', JSON.stringify(collectTicketsData()));

            } else if (currentStep === 3) {
                formData.append('ajax_action', 'save_step_3');
                formData.append('custom_url', document.getElementById('customUrlInput').value);
                const privEl = document.querySelector('input[name="eventPrivacy"]:checked');
                formData.append('privacy', privEl ? privEl.value : 'public');
                formData.append('thank_you_msg', document.getElementById('thankYouMsg').value);

            } else if (currentStep === 4) {
                formData.append('ajax_action', 'publish_event');
                formData.append('publish_mode', publishMode);
                formData.append('account_holder', document.getElementById('bankHolder').value);
                formData.append('account_number', document.getElementById('bankNumber').value);
                formData.append('bank_name', document.getElementById('bankName').value);
                formData.append('bank_branch', document.getElementById('bankBranch').value);
                formData.append('business_type', document.getElementById('businessType').value);
                formData.append('tax_code', document.getElementById('taxCode').value);
            }

            const btnNext = document.getElementById('btnNextStep');
            const btnSave = document.getElementById('btnSaveDraft');
            btnNext.disabled = true;
            btnSave.disabled = true;

            try {
                const res = await fetch('create-event.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') {
                    if (data.event_id) {
                        eventIdInput.value = data.event_id;
                    }
                    showToast(data.message);

                    if (currentStep === 4 && data.redirect_url) {
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 1000);
                    } else if (advanceNext && currentStep < 4) {
                        updateStepUI(currentStep + 1);
                    }
                } else {
                    showToast(data.message || 'Có lỗi xảy ra.', true);
                }
            } catch (err) {
                showToast('Lỗi kết nối máy chủ.', true);
            } finally {
                btnNext.disabled = false;
                btnSave.disabled = false;
            }
        }

        async function deleteOrganizerEvent(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa sự kiện này?')) return;
            const fd = new FormData();
            fd.append('ajax_action', 'delete_event');
            fd.append('event_id', id);
            const res = await fetch('create-event.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('org-card-' + id)?.remove();
                showToast(data.message);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const editorEl = document.getElementById('editor-container');
            if (editorEl) {
                quill = new Quill('#editor-container', {
                    theme: 'snow',
                    placeholder: 'Nhập giới thiệu sự kiện, chi tiết chương trình, khách mời...',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['link', 'image', 'video'],
                            ['clean']
                        ]
                    }
                });

                quill.on('text-change', function () {
                    document.getElementById('eventDescription').value = quill.root.innerHTML;
                });
            }

            // Preview ảnh Upload
            function setupImageUpload(inputId, boxId) {
                const input = document.getElementById(inputId);
                const box = document.getElementById(boxId);
                if (!input || !box) return;

                input.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    if (!file.type.startsWith('image/')) {
                        showToast('Vui lòng tải lên định dạng hình ảnh (JPG, PNG, WEBP).', true);
                        input.value = '';
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        showToast('Dung lượng ảnh tối đa là 5MB.', true);
                        input.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        box.style.backgroundImage = `url('${event.target.result}')`;
                        box.style.backgroundSize = 'cover';
                        box.style.backgroundPosition = 'center';
                        box.innerHTML = '';
                        box.style.border = 'none';
                    };
                    reader.readAsDataURL(file);
                });
            }
            setupImageUpload('inputPoster', 'boxPoster');
            setupImageUpload('inputBanner', 'boxBanner');

            // Đếm ký tự tên sự kiện
            const eventNameInput = document.getElementById('eventName');
            const eventNameCount = document.getElementById('eventNameCount');
            if (eventNameInput && eventNameCount) {
                eventNameInput.addEventListener('input', function () {
                    const len = this.value.length;
                    eventNameCount.textContent = `${len} / 100`;
                    eventNameCount.style.color = len >= 100 ? '#ff4d4f' : '#a0a3bd';
                });
            }

            // Chuyển đổi Offline / Online
            const btnOffline = document.getElementById('btnOffline');
            const btnOnline = document.getElementById('btnOnline');
            const locationFields = document.getElementById('locationFields');
            const eventModeInput = document.getElementById('eventMode');

            if (btnOnline && btnOffline && locationFields) {
                btnOnline.addEventListener('click', function () {
                    eventModeInput.value = 'online';
                    btnOnline.style.background = '#26c973';
                    btnOffline.style.background = '#3a3a45';
                    locationFields.innerHTML = `
                        <div class="form-group">
                            <label><span>*</span> Đường dẫn sự kiện trực tuyến (Zoom, Google Meet, Microsoft Teams,...)</label>
                            <input type="url" class="form-control" id="onlineUrl" placeholder="https://...">
                        </div>
                    `;
                });

                btnOffline.addEventListener('click', function () {
                    eventModeInput.value = 'offline';
                    btnOffline.style.background = '#26c973';
                    btnOnline.style.background = '#3a3a45';
                    locationFields.innerHTML = `
                        <div class="form-group">
                            <label>Tên tòa nhà / Địa điểm cụ thể</label>
                            <input type="text" class="form-control" id="venueName" placeholder="Ví dụ: Hội trường A - Trường Đại học Cần Thơ">
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label><span>*</span> Số nhà, tên đường</label>
                                <input type="text" class="form-control" id="streetAddress" placeholder="Ví dụ: Số 3 đường 3/2">
                            </div>
                            <div class="form-group">
                                <label><span>*</span> Phường / Xã</label>
                                <input type="text" class="form-control" id="wardInput" placeholder="Ví dụ: Phường Xuân Khánh">
                            </div>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label><span>*</span> Quận / Huyện</label>
                                <input type="text" class="form-control" id="districtInput" placeholder="Ví dụ: Quận Ninh Kiều">
                            </div>
                            <div class="form-group">
                                <label><span>*</span> Tỉnh / Thành phố</label>
                                <input type="text" class="form-control" id="cityInput" placeholder="Ví dụ: Thành phố Cần Thơ">
                            </div>
                        </div>
                    `;
                });
            }

            // Khởi tạo danh sách hạng vé ở Bước 2
            if (document.getElementById('ticketsContainer')) {
                if (Array.isArray(INITIAL_TICKETS) && INITIAL_TICKETS.length > 0) {
                    INITIAL_TICKETS.forEach(t => addTicketItem(t));
                } else {
                    addTicketItem();
                }
            }

            // Nút Lưu & Tiếp tục
            document.getElementById('btnSaveDraft')?.addEventListener('click', function () {
                saveCurrentStep(false, 'nhap');
            });

            document.getElementById('btnNextStep')?.addEventListener('click', function () {
                saveCurrentStep(true, 'cong_khai');
            });
        });
    </script>
</body>
</html>