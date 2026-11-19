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

if (isset($pdo)) {
    if ($loggedUserId > 0) {
        $stmtU = $pdo->prepare("SELECT * FROM nguoidung WHERE id = :id LIMIT 1");
        $stmtU->execute(['id' => $loggedUserId]);
        $dbU = $stmtU->fetch(PDO::FETCH_ASSOC);
        if ($dbU) {
            $_SESSION['user'] = array_merge($loggedUser, $dbU);
            $loggedUser = $_SESSION['user'];
        }
    } elseif (!empty($loggedUser['email'])) {
        $stmtU = $pdo->prepare("SELECT * FROM nguoidung WHERE email = :email LIMIT 1");
        $stmtU->execute(['email' => $loggedUser['email']]);
        $dbU = $stmtU->fetch(PDO::FETCH_ASSOC);
        if ($dbU) {
            $loggedUserId = (int)$dbU['id'];
            $_SESSION['user'] = array_merge($loggedUser, $dbU);
            $loggedUser = $_SESSION['user'];
        }
    }
}

$userName   = $loggedUser['ho_ten'] ?? $loggedUser['name'] ?? 'Ban tổ chức';
$userAvatar = (!empty($loggedUser['avatar']) && (preg_match('#^https?://#i', $loggedUser['avatar']) || file_exists(__DIR__ . '/' . $loggedUser['avatar'])))
    ? $loggedUser['avatar']
    : '../assets/images/default-avatar.png';

function handleEventImageUpload(string $fieldName, string $prefix, int $userId): ?string
{
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

function getDanhMucTable(PDO $pdo): string
{
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
function getDanhMucNameCol(PDO $pdo): string
{
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


function getDanhMucList(PDO $pdo): array
{
    try {
        $table   = getDanhMucTable($pdo);
        $nameCol = getDanhMucNameCol($pdo);
        $stmt = $pdo->query("SELECT id, `{$nameCol}` AS ten_danh_muc FROM `{$table}` ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

function resolveDanhMucId(PDO $pdo, string $categoryInput): ?int
{
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
// 2. XỬ LÝ API AJAX LƯU TỪNG BƯỚC & XUẤT BẢN SỰ KIỆN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($pdo) || $loggedUserId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Phiên đăng nhập không hợp lệ hoặc mất kết nối CSDL.']);
        exit;
    }

    $action  = $_POST['ajax_action'];
    $eventId = (int)($_POST['event_id'] ?? 0);

    try {
        // LƯU BƯỚC 1: THÔNG TIN CƠ BẢN & ĐỊA ĐIỂM 
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
            $tenDiaDiem = null; // [1] Khai báo thêm biến lưu tên địa điểm cụ thể
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
                $tenDiaDiem = ($venue !== '') ? $venue : null; // [1] Lấy tên tòa nhà / địa điểm cụ thể
                $parts      = array_filter([$venue, $street, $ward, $district, $city]);
                $diaDiem    = implode(', ', $parts);
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

                // [2] Cập nhật thêm cột ten_dia_diem và mo_ta_ban_to_chuc khi sửa sự kiện
                $sql = "UPDATE sukien SET 
                            ten_su_kien = :ten,
                            mo_ta = :mota,
                            danh_muc_id = :danh_muc_id,
                            ten_ban_to_chuc = :btc,
                            mo_ta_ban_to_chuc = :mota_btc,
                            dia_diem = :diadiem,
                            ten_dia_diem = :tendd,
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
                    'mota_btc'    => $moTaBtc !== '' ? $moTaBtc : null,
                    'diadiem'     => $diaDiem,
                    'tendd'       => $tenDiaDiem,
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

                // [3] Thêm cột ten_dia_diem và mo_ta_ban_to_chuc khi tạo mới sự kiện
                if (in_array('mo_ta_ban_to_chuc', $sukienCols, true)) {
                    $insertCols[]             = 'mo_ta_ban_to_chuc';
                    $insertVals[]             = ':mota_btc';
                    $insertParams['mota_btc'] = $moTaBtc !== '' ? $moTaBtc : null;
                }
                if (in_array('ten_dia_diem', $sukienCols, true)) {
                    $insertCols[]          = 'ten_dia_diem';
                    $insertVals[]          = ':tendd';
                    $insertParams['tendd'] = $tenDiaDiem;
                }
                if (in_array('nguoi_tao', $sukienCols, true)) {
                    $insertCols[]              = 'nguoi_tao';
                    $insertVals[]              = ':nguoi_tao';
                    $insertParams['nguoi_tao'] = $loggedUserId;
                }
                if (in_array('so_luong_toi_da', $sukienCols, true)) {
                    $insertCols[]                    = 'so_luong_toi_da';
                    $insertVals[]                    = ':so_luong_toi_da';
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
        // LƯU BƯỚC 2: THỜI GIAN & LOẠI VÉ 
        if ($action === 'save_step_2') {
            if ($eventId <= 0) {
                throw new RuntimeException('Vui lòng lưu thông tin Bước 1 trước.');
            }

            $stmtOwn = $pdo->prepare("SELECT id FROM sukien WHERE id = :id AND nguoi_tao_id = :uid LIMIT 1");
            $stmtOwn->execute(['id' => $eventId, 'uid' => $loggedUserId]);
            if (!$stmtOwn->fetchColumn()) {
                throw new RuntimeException('Không tìm thấy sự kiện hoặc bạn không có quyền chỉnh sửa.');
            }

            $startTime = trim($_POST['start_time'] ?? '');
            $endTime   = trim($_POST['end_time'] ?? '');
            $tickets   = json_decode($_POST['tickets_json'] ?? '[]', true);

            if ($startTime === '' || $endTime === '') {
                throw new RuntimeException('Vui lòng chọn thời gian bắt đầu và kết thúc sự kiện.');
            }

            $startTs = strtotime($startTime);
            $endTs   = strtotime($endTime);

            if ($endTs <= $startTs) {
                throw new RuntimeException('Thời gian kết thúc phải sau thời gian bắt đầu.');
            }
            if (!is_array($tickets) || count($tickets) === 0) {
                throw new RuntimeException('Vui lòng tạo ít nhất 1 loại vé cho sự kiện.');
            }

            $minPrice = null;
            $totalQty = 0;
            foreach ($tickets as $t) {
                $tName  = trim($t['name'] ?? '');
                $tQty   = (int)($t['qty'] ?? 0);
                $tPrice = max(0, (int)($t['price'] ?? 0));
                if ($tName === '' || $tQty <= 0) {
                    throw new RuntimeException('Tên loại vé không được trống và số lượng vé phải lớn hơn 0.');
                }
                if ($minPrice === null || $tPrice < $minPrice) {
                    $minPrice = $tPrice;
                }
                $totalQty += $tQty;
            }

            // Tạo chuỗi thoi_gian_hien_thi (hỗ trợ cả sự kiện trong ngày và nhiều ngày)
            if (date('Y-m-d', $startTs) === date('Y-m-d', $endTs)) {
                $thoiGianHienThi = date('H:i', $startTs) . ' - ' . date('H:i, d', $endTs)
                    . ' Tháng ' . date('m, Y', $endTs);
            } else {
                $thoiGianHienThi = date('H:i, d', $startTs) . ' Tháng ' . date('m', $startTs)
                    . ' - ' . date('H:i, d', $endTs) . ' Tháng ' . date('m, Y', $endTs);
            }

            $pdo->beginTransaction();

            $stmtUpdEvt = $pdo->prepare("
                UPDATE sukien 
                SET thoi_gian = :start_t,
                    thoi_gian_ket_thuc = :end_t,
                    thoi_gian_hien_thi = :hien_thi,
                    gia_thap_nhat = :min_p,
                    so_luong_toi_da = :total_qty
                WHERE id = :id AND nguoi_tao_id = :uid
            ");
            $stmtUpdEvt->execute([
                'start_t'   => date('Y-m-d H:i:s', $startTs),
                'end_t'     => date('Y-m-d H:i:s', $endTs),
                'hien_thi'  => $thoiGianHienThi,
                'min_p'     => (int)$minPrice,
                'total_qty' => $totalQty,
                'id'        => $eventId,
                'uid'       => $loggedUserId
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

        // LƯU BƯỚC 3: CÀI ĐẶT HIỂN THỊ 
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

        // LƯU BƯỚC 4 & XUẤT BẢN SỰ KIỆN 
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

        // XÓA SỰ KIỆN 
        if ($action === 'delete_event') {
            if ($eventId <= 0) {
                throw new RuntimeException('ID sự kiện không hợp lệ.');
            }

            $stmtOwner = $pdo->prepare("SELECT id FROM sukien WHERE id = :id AND nguoi_tao_id = :uid LIMIT 1");
            $stmtOwner->execute(['id' => $eventId, 'uid' => $loggedUserId]);
            if (!$stmtOwner->fetchColumn()) {
                throw new RuntimeException('Không tìm thấy sự kiện hoặc bạn không có quyền xóa sự kiện này.');
            }

            $stmtCheckSold = $pdo->prepare("SELECT COUNT(*) FROM ve WHERE su_kien_id = :id AND trang_thai != 'da-huy'");
            $stmtCheckSold->execute(['id' => $eventId]);
            $soldCount = (int)$stmtCheckSold->fetchColumn();
            if ($soldCount > 0) {
                throw new RuntimeException('Không thể xóa sự kiện đã có khách đặt vé (' . $soldCount . ' vé).');
            }

            $pdo->prepare("DELETE FROM loaive WHERE su_kien_id = :id")->execute(['id' => $eventId]);
            $pdo->prepare("DELETE FROM sukien WHERE id = :id AND nguoi_tao_id = :uid")
                ->execute(['id' => $eventId, 'uid' => $loggedUserId]);

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

// 3. NẠP DỮ LIỆU CHỈNH SỬA HOẶC DANH SÁCH SỰ KIỆN ĐÃ TẠO
$viewMode       = $_GET['view'] ?? 'wizard';
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
    <link rel="stylesheet" href="../assets/css/create-event.css">
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

    <div class="main-content">
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
            <!--SỰ KIỆN CỦA TÔI-->
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
            <!--QUẢN LÝ BÁO CÁO-->
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
            <!--ĐIỀU KHOẢN BAN TỔ CHỨC-->
            <?php
            $btc_pdf_rel  = '../assets/docs/dieu-khoan-ban-to-chuc.pdf';
            $btc_pdf_path = __DIR__ . '/' . $btc_pdf_rel;
            ?>
            <div class="form-container">
                <div class="form-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
                        <h2 style="font-size: 20px; margin: 0; color: #26c973;">Điều khoản &amp; Quy định dành cho Ban tổ chức</h2>
                        <a href="terms.php?type=ban-to-chuc" target="_blank" style="color: #26c973; font-size: 13.5px; font-weight: 600; text-decoration: none;">
                            <i class="fa-solid fa-up-right-from-square"></i> Mở trang điều khoản
                        </a>
                    </div>

                    <?php if (file_exists($btc_pdf_path)): ?>
                        <div style="border: 1px solid #34374c; border-radius: 10px; overflow: hidden; background: #181a20;">
                            <iframe
                                src="<?php echo htmlspecialchars($btc_pdf_rel); ?>#zoom=100&toolbar=1"
                                style="width: 100%; height: 520px; border: none; display: block;"
                                type="application/pdf">
                            </iframe>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- 4 BƯỚC TẠO / SỬA SỰ KIỆN-->
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

                <!--BƯỚC 1: THÔNG TIN SỰ KIỆN-->
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
                        <div class="section-title">
                            <div><span class="req">*</span> Hình thức & Địa chỉ sự kiện</div>
                        </div>
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
                            <?php else:
                                $editVenue    = trim((string)($editingEvent['ten_dia_diem'] ?? ''));
                                $editStreet   = '';
                                $editWard     = '';
                                $editDistrict = '';
                                $editCity     = '';
                                if (!empty($editingEvent['dia_diem'])) {
                                    $rawAddr = trim((string)$editingEvent['dia_diem']);
                                    if ($editVenue !== '' && mb_stripos($rawAddr, $editVenue . ',', 0, 'UTF-8') === 0) {
                                        $rawAddr = trim(mb_substr($rawAddr, mb_strlen($editVenue, 'UTF-8') + 1, null, 'UTF-8'));
                                    }
                                    $addrParts = array_values(array_filter(array_map('trim', explode(',', $rawAddr)), 'strlen'));
                                    if (count($addrParts) >= 4) {
                                        $editCity     = array_pop($addrParts);
                                        $editDistrict = array_pop($addrParts);
                                        $editWard     = array_pop($addrParts);
                                        $editStreet   = implode(', ', $addrParts);
                                    } elseif (count($addrParts) === 3) {
                                        $editCity     = $addrParts[2];
                                        $editDistrict = $addrParts[1];
                                        $editStreet   = $addrParts[0];
                                    } elseif (count($addrParts) === 2) {
                                        $editCity     = $addrParts[1];
                                        $editDistrict = $addrParts[0];
                                    } elseif (count($addrParts) === 1) {
                                        $editStreet   = $addrParts[0];
                                    }
                                }
                            ?>
                                <div class="form-group">
                                    <label>Tên tòa nhà / Địa điểm cụ thể</label>
                                    <input type="text" class="form-control" id="venueName" placeholder="Ví dụ: Hội trường A - Trường Đại học Cần Thơ" value="<?php echo htmlspecialchars($editVenue); ?>">
                                </div>
                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label><span>*</span> Số nhà, tên đường</label>
                                        <input type="text" class="form-control" id="streetAddress" placeholder="Ví dụ: Số 3 đường 3/2" value="<?php echo htmlspecialchars($editStreet); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label><span>*</span> Phường / Xã</label>
                                        <input type="text" class="form-control" id="wardInput" placeholder="Ví dụ: Phường Xuân Khánh" value="<?php echo htmlspecialchars($editWard); ?>">
                                    </div>
                                </div>
                                <div class="form-row-2">
                                    <div class="form-group">
                                        <label><span>*</span> Quận / Huyện</label>
                                        <input type="text" class="form-control" id="districtInput" placeholder="Ví dụ: Quận Ninh Kiều" value="<?php echo htmlspecialchars($editDistrict); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label><span>*</span> Tỉnh / Thành phố</label>
                                        <input type="text" class="form-control" id="cityInput" placeholder="Ví dụ: Thành phố Cần Thơ" value="<?php echo htmlspecialchars($editCity); ?>">
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- BAN TỔ CHỨC -->
                    <div class="form-section">
                        <div class="section-title">
                            <div><span class="req">*</span> Thông tin Ban tổ chức</div>
                        </div>
                        <div class="form-group">
                            <label><span>*</span> Tên Ban tổ chức</label>
                            <input type="text" class="form-control" id="organizerName" value="<?php echo htmlspecialchars($editingEvent['ten_ban_to_chuc'] ?? $userName); ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Giới thiệu Ban tổ chức</label>
                            <textarea class="form-control" id="organizerInfo" rows="3" placeholder="Thông tin giới thiệu ngắn về đơn vị tổ chức..."><?php echo htmlspecialchars($editingEvent['mo_ta_ban_to_chuc'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!--BƯỚC 2: THỜI GIAN & LOẠI VÉ-->
                <div class="step-panel" id="stepPanel2">
                    <div class="form-section">
                        <div class="section-title">
                            <div><span class="req">*</span> Thời gian tổ chức sự kiện</div>
                        </div>
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

                        <div id="ticketsContainer" data-initial-tickets='<?php echo htmlspecialchars(json_encode($editingTickets, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>'></div>

                        <button type="button" class="btn-add-ticket" onclick="addTicketItem()">
                            <i class="fa-solid fa-plus"></i> Tạo thêm loại vé mới
                        </button>
                    </div>
                </div>

                <!--BƯỚC 3: CÀI ĐẶT-->
                <div class="step-panel" id="stepPanel3">
                    <div class="form-section">
                        <div class="section-title">
                            <div>Đường dẫn & Quyền riêng tư</div>
                        </div>

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
                        <div class="section-title">
                            <div>Tin nhắn xác nhận sau khi đặt vé</div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Nội dung lời cảm ơn gửi kèm vé điện tử</label>
                            <textarea class="form-control" id="thankYouMsg" rows="4" placeholder="Cảm ơn bạn đã đặt vé tham dự sự kiện! Vui lòng mang mã QR đến quầy check-in trước 30 phút."><?php echo htmlspecialchars($editingEvent['loi_nhan_cam_on'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!--BƯỚC 4: THÔNG TIN THANH TOÁN-->
                <div class="step-panel" id="stepPanel4">
                    <div class="form-section">
                        <div class="section-title">
                            <div><span class="req">*</span> Thông tin tài khoản ngân hàng nhận doanh thu</div>
                        </div>

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
                        <div class="section-title">
                            <div>Thông tin xuất hóa đơn đỏ</div>
                        </div>
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
    <script src="../assets/js/create-event.js"></script>
</body>

</html>