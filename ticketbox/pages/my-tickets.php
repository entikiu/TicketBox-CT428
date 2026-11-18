<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';


if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$current_tab = $_GET['tab'] ?? 'tickets';
if ($current_tab === 'edit-profile') {
    $current_tab = 'profile-edit';
}
$user_id     = (int)($_SESSION['user']['id'] ?? 0);

$errors = [];
$success_msg = '';


$eventCreatorCol = null;
if (isset($pdo)) {
    try {
        $colsStmt   = $pdo->query("SHOW COLUMNS FROM sukien");
        $sukienCols = $colsStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach (['nguoi_dung_id', 'user_id', 'nguoi_tao_id', 'created_by'] as $candidate) {
            if (in_array($candidate, $sukienCols, true)) {
                $eventCreatorCol = $candidate;
                break;
            }
        }
    } catch (PDOException $e) {
        $eventCreatorCol = null;
    }
}

if ($user_id > 0 && isset($pdo)) {
    try {
        $stmtUser = $pdo->prepare("SELECT * FROM nguoidung WHERE id = :id LIMIT 1");
        $stmtUser->execute(['id' => $user_id]);
        $dbUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$dbUser) {
            $sessEmail = (string)($_SESSION['user']['email'] ?? '');
            if ($sessEmail !== '') {
                $stmtUserByEmail = $pdo->prepare("SELECT * FROM nguoidung WHERE email = :email LIMIT 1");
                $stmtUserByEmail->execute(['email' => $sessEmail]);
                $dbUser = $stmtUserByEmail->fetch(PDO::FETCH_ASSOC);
            }

            if (!$dbUser) {
                $_SESSION = [];
                session_destroy();
                header('Location: login.php');
                exit;
            }

            $user_id                     = (int)$dbUser['id'];
            $_SESSION['user_id']         = $user_id;
            $_SESSION['user_role']       = (string)($dbUser['vai_tro'] ?? $_SESSION['user_role'] ?? '');
            $_SESSION['user']['vai_tro'] = $_SESSION['user_role'];
            unset($_SESSION['my_tickets']);
        }

        if ($dbUser) {
            $_SESSION['user']['id']     = (int)$dbUser['id'];
            $_SESSION['user']['ho_ten'] = $dbUser['ho_ten'] ?? $_SESSION['user']['ho_ten'] ?? '';
            $_SESSION['user']['name']   = $dbUser['ho_ten'] ?? $_SESSION['user']['name'] ?? '';
            $_SESSION['user']['email']  = $dbUser['email'] ?? $_SESSION['user']['email'] ?? '';

            if (array_key_exists('phone', $dbUser) && !empty($dbUser['phone'])) {
                $_SESSION['user']['phone'] = $dbUser['phone'];
            }
            if (array_key_exists('dob', $dbUser) && !empty($dbUser['dob'])) {
                $_SESSION['user']['dob'] = $dbUser['dob'];
            }
            if (array_key_exists('gender', $dbUser) && !empty($dbUser['gender'])) {
                $_SESSION['user']['gender'] = $dbUser['gender'];
            }
            if (array_key_exists('avatar', $dbUser) && !empty($dbUser['avatar'])) {
                $_SESSION['user']['avatar'] = $dbUser['avatar'];
            }
        }
    } catch (PDOException $e) {
        
    }
}

$user_name       = $_SESSION['user']['ho_ten'] ?? $_SESSION['user']['name'] ?? 'Tài khoản';
$user_email      = $_SESSION['user']['email'] ?? '';
$user_phone      = $_SESSION['user']['phone'] ?? '';
$user_dob        = $_SESSION['user']['dob'] ?? '';
$user_gender     = $_SESSION['user']['gender'] ?? 'nam';
$user_avatar     = !empty($_SESSION['user']['avatar']) ? $_SESSION['user']['avatar'] : '../assets/images/default-avatar.png';
$user_first_char = mb_strtoupper(mb_substr($user_name, 0, 1, 'UTF-8'), 'UTF-8');


function getEventTimelineStatus($date_str) {
    if (empty($date_str)) return 'sap-dien-ra';

    $day = $month = $year = null;

    if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $date_str, $matches)) {
        $year  = $matches[1];
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $day   = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
    } elseif (preg_match('/(\d{1,2})[\.\/](\d{1,2})[\.\/](\d{4})/', $date_str, $matches)) {
        $day   = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year  = $matches[3];
    } elseif (preg_match('/(\d{1,2})\s+Tháng\s+(\d{1,2}),?\s+(\d{4})/ui', $date_str, $matches)) {
        $day   = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year  = $matches[3];
    }

    if ($day && $month && $year) {
        $event_timestamp = strtotime("$year-$month-$day 23:59:59");
        return (time() > $event_timestamp) ? 'da-ket-thuc' : 'sap-dien-ra';
    }

    return 'sap-dien-ra';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';


    if ($action === 'update_profile') {
        $ho_ten  = trim($_POST['ho_ten'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $dob     = trim($_POST['dob'] ?? '');
        $gender  = $_POST['gender'] ?? 'nam';
        $avatar_path = $user_avatar;

        if (empty($ho_ten)) $errors['ho_ten'] = 'Vui lòng nhập họ và tên.';
        if (empty($phone)) $errors['phone'] = 'Vui lòng nhập số điện thoại.';
        elseif (!preg_match('/^[0-9]{9,11}$/', $phone)) $errors['phone'] = 'Số điện thoại phải từ 9 - 11 chữ số.';
        if (empty($dob)) $errors['dob'] = 'Vui lòng nhập ngày sinh.';

        if ($user_id <= 0) {
            $errors['general'] = 'Không xác định được ID người dùng. Vui lòng đăng xuất và đăng nhập lại!';
        }

        $uploadField = isset($_FILES['avatar']) ? 'avatar' : 'avatar_file';
        if (isset($_FILES[$uploadField]) && $_FILES[$uploadField]['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES[$uploadField]['tmp_name'];
            $fileName      = $_FILES[$uploadField]['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts   = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($fileExtension, $allowedExts)) {
                $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName    = 'avatar_' . $user_id . '_' . time() . '.' . $fileExtension;
                $targetFilePath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
                    if (
                        !empty($user_avatar) &&
                        strpos($user_avatar, '../assets/uploads/avatars/') === 0 &&
                        is_file(__DIR__ . '/' . $user_avatar)
                    ) {
                        @unlink(__DIR__ . '/' . $user_avatar);
                    }

                    $avatar_path = '../assets/uploads/avatars/' . $newFileName;
                } else {
                    $errors['avatar'] = 'Không thể di chuyển file ảnh vào thư mục máy chủ.';
                }
            } else {
                $errors['avatar'] = 'Định dạng ảnh không hỗ trợ (chỉ nhận JPG, PNG, WEBP, GIF).';
            }
        }

        if (empty($errors)) {
            if (isset($pdo) && $user_id > 0) {
                try {
                    $sql = "UPDATE nguoidung 
                            SET ho_ten = :ho_ten, phone = :phone, dob = :dob, gender = :gender, avatar = :avatar 
                            WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'ho_ten' => $ho_ten,
                        'phone'  => $phone,
                        'dob'    => $dob,
                        'gender' => $gender,
                        'avatar' => $avatar_path,
                        'id'     => $user_id
                    ]);

                    $_SESSION['user']['ho_ten'] = $ho_ten;
                    $_SESSION['user']['name']   = $ho_ten;
                    $_SESSION['user']['phone']  = $phone;
                    $_SESSION['user']['dob']    = $dob;
                    $_SESSION['user']['gender'] = $gender;
                    $_SESSION['user']['avatar'] = $avatar_path;

                    $user_name       = $ho_ten;
                    $user_first_char = mb_strtoupper(mb_substr($user_name, 0, 1, 'UTF-8'), 'UTF-8');
                    $user_phone      = $phone;
                    $user_dob        = $dob;
                    $user_gender     = $gender;
                    $user_avatar     = $avatar_path;
                    $success_msg     = 'Cập nhật thành công!';
                } catch (PDOException $e) {
                    $errors['general'] = 'Lỗi lưu CSDL: ' . $e->getMessage();
                }
            } else {
                $errors['general'] = 'Không có kết nối CSDL hoặc ID tài khoản không hợp lệ.';
            }
        }
    }


    elseif ($action === 'unfollow_star') {
        $star_id = (int)($_POST['star_id'] ?? 0);
        if ($star_id > 0 && isset($pdo) && $user_id > 0) {
            try {
                $stmtUnfollow = $pdo->prepare("DELETE FROM ticketstar_theodoi WHERE nguoi_dung_id = :uid AND star_id = :sid");
                $stmtUnfollow->execute(['uid' => $user_id, 'sid' => $star_id]);
                if ($stmtUnfollow->rowCount() > 0) {
                    $pdo->prepare("UPDATE ticketstars SET luot_theo_doi = GREATEST(0, luot_theo_doi - 1) WHERE id = :sid")
                        ->execute(['sid' => $star_id]);
                }
                $success_msg = 'Đã bỏ theo dõi thành công!';
            } catch (PDOException $e) {
                $errors['general'] = 'Không thể cập nhật trạng thái theo dõi: ' . $e->getMessage();
            }
        }
    }


    elseif ($action === 'delete_my_event') {
        $event_id = (int)($_POST['event_id'] ?? 0);
        if ($event_id > 0 && isset($pdo) && $user_id > 0) {
            try {
                // Kiểm tra quyền sở hữu sự kiện
                $stmtOwner = $pdo->prepare("SELECT id FROM sukien WHERE id = :eid AND nguoi_tao_id = :uid LIMIT 1");
                $stmtOwner->execute(['eid' => $event_id, 'uid' => $user_id]);

                if (!$stmtOwner->fetchColumn()) {
                    $errors['general'] = 'Không tìm thấy sự kiện hoặc bạn không có quyền xóa sự kiện này.';
                } else {
                    $stmtCheckSold = $pdo->prepare("SELECT COUNT(*) FROM ve WHERE su_kien_id = :eid AND trang_thai != 'da-huy'");
                    $stmtCheckSold->execute(['eid' => $event_id]);
                    $soldCount = (int)$stmtCheckSold->fetchColumn();

                    if ($soldCount > 0) {
                        $errors['general'] = 'Không thể xóa sự kiện đã có khách đặt vé (' . $soldCount . ' vé).';
                    } else {
                        $pdo->prepare("DELETE FROM loaive WHERE su_kien_id = :eid")->execute(['eid' => $event_id]);
                        $pdo->prepare("DELETE FROM sukien WHERE id = :eid AND nguoi_tao_id = :uid")
                            ->execute(['eid' => $event_id, 'uid' => $user_id]);
                        $success_msg = 'Đã xóa sự kiện thành công!';
                    }
                }
            } catch (PDOException $e) {
                $errors['general'] = 'Không thể xóa sự kiện: ' . $e->getMessage();
            }
        }
    }
}

$has_custom_avatar = false;
if (!empty($user_avatar) && $user_avatar !== '../assets/images/default-avatar.png') {
    if (preg_match('#^https?://#i', $user_avatar) || file_exists(__DIR__ . '/' . $user_avatar)) {
        $has_custom_avatar = true;
    }
}

$my_tickets = [];
$tickets_db_ok = false;

if (isset($pdo) && $user_id > 0) {
    try {
        $sqlTickets = "SELECT 
                        v.id AS ticket_db_id,
                        v.ma_ve AS ticket_code,
                        v.trang_thai AS status,
                        v.loai_ve AS ticket_category,
                        v.gia_ve AS price,
                        s.ten_su_kien AS event_title,
                        s.thoi_gian AS date,
                        s.dia_diem AS location,
                        s.hinh_anh AS image
                    FROM ve v
                    JOIN sukien s ON v.su_kien_id = s.id
                    WHERE v.nguoi_dung_id = :user_id
                    ORDER BY v.ngay_dat DESC";
        $stmtT = $pdo->prepare($sqlTickets);
        $stmtT->execute(['user_id' => $user_id]);
        $my_tickets = $stmtT->fetchAll(PDO::FETCH_ASSOC);
        $tickets_db_ok = true;
    } catch (PDOException $e) {
        $my_tickets = [];
    }
}

if ($tickets_db_ok) {
    unset($_SESSION['my_tickets']);
} elseif (empty($my_tickets)) {
    $my_tickets = $_SESSION['my_tickets'] ?? [];
}


$filter_status   = $_GET['status'] ?? 'all';
$filter_timeline = $_GET['timeline'] ?? 'sap-dien-ra';

$count_all       = count($my_tickets);
$count_success   = 0;
$count_pending   = 0;
$count_cancelled = 0;

foreach ($my_tickets as $item) {
    $st = $item['status'] ?? 'thanh-cong';
    if ($st === 'thanh-cong') $count_success++;
    elseif ($st === 'dang-xu-ly') $count_pending++;
    elseif ($st === 'da-huy') $count_cancelled++;
}

$filtered_tickets = [];
foreach ($my_tickets as $index => $ticket) {
    $st = $ticket['status'] ?? 'thanh-cong';
    $event_date_str = $ticket['date'] ?? '';
    $computed_timeline = $ticket['timeline'] ?? getEventTimelineStatus($event_date_str);

    $match_status   = ($filter_status === 'all') || ($st === $filter_status);
    $match_timeline = ($computed_timeline === $filter_timeline);

    if ($match_status && $match_timeline) {
        $ticket['original_index']    = $index;
        $ticket['computed_timeline'] = $computed_timeline;
        $filtered_tickets[]          = $ticket;
    }
}

include '../templates/header.php';
?>

<main class="container" style="margin-top: 25px; min-height: 80vh;">

    <div class="breadcrumb" style="font-size: 13px; color: #a0a3bd; margin-bottom: 25px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <a href="home.php" style="color: #a0a3bd; text-decoration: none;">Trang chủ</a>
        <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>

        <?php if ($current_tab === 'profile'): ?>
            <span style="color: #fff;">Thông tin tài khoản</span>
        <?php elseif ($current_tab === 'profile-edit'): ?>
            <a href="my-tickets.php?tab=profile-edit" style="color: #a0a3bd; text-decoration: none;">Cài đặt tài khoản</a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
            <span style="color: #fff;">Thông tin tài khoản</span>
        <?php elseif ($current_tab === 'membership'): ?>
            <span>Đơn hàng của tôi</span> <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i> <span style="color: #fff;">Thẻ thành viên</span>
        <?php elseif ($current_tab === 'my-events'): ?>
            <span style="color: #fff;">Sự kiện của tôi</span>
        <?php else: ?>
            <span>Đơn hàng của tôi</span> <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i> <span style="color: #fff;">Vé của tôi</span>
        <?php endif; ?>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div style="background: rgba(38, 201, 115, 0.15); border: 1px solid #26c973; color: #26c973; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
            <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <div style="background: rgba(255, 77, 79, 0.15); border: 1px solid #ff4d4f; color: #ff4d4f; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
            <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <div class="account-layout" style="display: flex; gap: 40px;">

        <aside class="account-sidebar" style="width: 260px; flex-shrink: 0;">
            <a href="my-tickets.php?tab=profile" style="text-decoration: none; display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
                <div style="width: 42px; height: 42px; border-radius: 50%; overflow: hidden; background: #26c973; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700;">
                    <?php if ($has_custom_avatar): ?>
                        <img src="<?php echo htmlspecialchars($user_avatar); ?>?v=<?php echo time(); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="font-size: 18px;"><?php echo htmlspecialchars($user_first_char); ?></span>
                    <?php endif; ?>
                </div>
                <div style="display: flex; flex-direction: column;">
                    <span style="font-size: 12px; color: #a0a3bd;">Tài khoản của</span>
                    <strong style="font-size: 14px; color: <?php echo ($current_tab === 'profile') ? '#26c973' : '#fff'; ?>; display: flex; align-items: center; gap: 8px;">
                        <?php echo htmlspecialchars($user_name); ?>
                        <i class="fa-solid <?php echo ($current_tab === 'profile') ? 'fa-chevron-down' : 'fa-chevron-right'; ?>" style="font-size: 10px;"></i>
                    </strong>
                </div>
            </a>

            <nav class="sidebar-menu" style="display: flex; flex-direction: column; gap: 15px;">
                <div>
                    <a href="my-tickets.php?tab=profile-edit" style="display: flex; justify-content: space-between; align-items: center; color: <?php echo ($current_tab === 'profile-edit') ? '#26c973' : '#fff'; ?>; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 0;">
                        <span><i class="fa-regular fa-user" style="width: 22px;"></i> Cài đặt tài khoản</span>
                        <i class="fa-solid <?php echo ($current_tab === 'profile-edit') ? 'fa-chevron-down' : 'fa-chevron-right'; ?>" style="font-size: 11px;"></i>
                    </a>
                    <?php if ($current_tab === 'profile-edit'): ?>
                        <div style="padding-left: 28px; margin-top: 8px;">
                            <a href="my-tickets.php?tab=profile-edit" style="color: #26c973; text-decoration: none; font-size: 13px; font-weight: 600;">Thông tin tài khoản</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <a href="my-tickets.php?tab=tickets" style="display: flex; justify-content: space-between; align-items: center; color: <?php echo in_array($current_tab, ['tickets', 'membership']) ? '#26c973' : '#fff'; ?>; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 0;">
                        <span><i class="fa-solid fa-ticket" style="width: 22px;"></i> Đơn hàng của tôi</span>
                        <i class="fa-solid <?php echo in_array($current_tab, ['tickets', 'membership']) ? 'fa-chevron-down' : 'fa-chevron-right'; ?>" style="font-size: 11px;"></i>
                    </a>
                    <?php if (in_array($current_tab, ['tickets', 'membership'])): ?>
                        <div style="padding-left: 28px; margin-top: 8px; display: flex; flex-direction: column; gap: 8px;">
                            <a href="my-tickets.php?tab=tickets" style="color: <?php echo ($current_tab === 'tickets') ? '#26c973' : '#a0a3bd'; ?>; text-decoration: none; font-size: 13px;">Vé của tôi</a>
                            <a href="my-tickets.php?tab=membership" style="color: <?php echo ($current_tab === 'membership') ? '#26c973' : '#a0a3bd'; ?>; text-decoration: none; font-size: 13px;">Thẻ thành viên của tôi</a>
                        </div>
                    <?php endif; ?>
                </div>

                <a href="my-tickets.php?tab=my-events" style="color: <?php echo ($current_tab === 'my-events') ? '#26c973' : '#fff'; ?>; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-regular fa-star" style="width: 22px;"></i> Sự kiện của tôi
                </a>
            </nav>
        </aside>

        <section class="account-content" style="flex: 1; min-width: 0;">
            <?php switch ($current_tab):

                case 'profile':
                    $profile_memberships = [];
                    $followed_stars = [];

                    if (isset($pdo) && $user_id > 0) {
                        try {
                            $stmtProfMem = $pdo->prepare("
                                SELECT u.ma_the, u.ngay_kich_hoat, u.ngay_het_han, t.ten_the, t.nghe_si, t.thoi_han_thang, t.hinh_anh
                                FROM the_thanh_vien_nguoidung u
                                JOIN the_thanh_vien t ON u.the_id = t.id
                                WHERE u.nguoi_dung_id = :uid AND u.trang_thai = 'dang-hoat-dong'
                                ORDER BY u.ngay_kich_hoat DESC
                            ");
                            $stmtProfMem->execute(['uid' => $user_id]);
                            $profile_memberships = $stmtProfMem->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            $profile_memberships = [];
                        }

                        try {
                            $stmtFollow = $pdo->prepare("
                                SELECT ts.id, ts.slug, ts.ten_hien_thi, ts.loai, ts.avatar, ts.da_xac_minh
                                FROM ticketstar_theodoi td
                                JOIN ticketstars ts ON td.star_id = ts.id
                                WHERE td.nguoi_dung_id = :uid
                                ORDER BY td.ngay_theo_doi DESC
                            ");
                            $stmtFollow->execute(['uid' => $user_id]);
                            $followed_stars = $stmtFollow->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            $followed_stars = [];
                        }
                    }

                    $roleLabels = [
                        'NgheSi'    => 'Nghệ sĩ',
                        'BanToChuc' => 'Ban tổ chức',
                        'SanKhau'   => 'Sân khấu'
                    ];
                ?>
                    <!-- Avatar & Tên (tích hợp từ profile.php) -->
                    <div class="profile-header" style="text-align: center; margin-bottom: 45px;">
                        <div style="position: relative; display: inline-block; margin-bottom: 15px;">
                            <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; background: #26c973; display: flex; align-items: center; justify-content: center; color: #fff; border: 3px solid #2d2d35;">
                                <?php if ($has_custom_avatar): ?>
                                    <img src="<?php echo htmlspecialchars($user_avatar); ?>?v=<?php echo time(); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <span style="font-size: 48px; font-weight: 700;"><?php echo htmlspecialchars($user_first_char); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="my-tickets.php?tab=profile-edit"
                               title="Chỉnh sửa hồ sơ"
                               style="position: absolute; right: -4px; top: -4px; background: #444; color: #ccc; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 14px; border: 2px solid #18191a;">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </div>
                        <h2 style="font-size: 22px; font-weight: 700; color: #fff; display: flex; align-items: center; justify-content: center; gap: 8px; margin: 0;">
                            <?php echo htmlspecialchars($user_name); ?>
                            <a href="my-tickets.php?tab=profile-edit" title="Chỉnh sửa thông tin" style="color: #a0a3bd; font-size: 15px; text-decoration: none;"><i class="fa-solid fa-pen"></i></a>
                        </h2>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 35px; max-width: 850px;">

                        <!-- Thẻ thành viên của tôi -->
                        <section class="membership-section">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
                                    <i class="fa-solid fa-gem" style="color: #ffd700;"></i> Thẻ thành viên của tôi
                                </h3>
                                <a href="my-tickets.php?tab=membership" style="color: #26c973; text-decoration: none; font-size: 13px; font-weight: 600;">
                                    Khám phá thêm &gt;
                                </a>
                            </div>

                            <?php if (empty($profile_memberships)): ?>
                                <p style="color: #777; font-style: italic; font-size: 13.5px; margin: 0;">Bạn chưa có thẻ thành viên</p>
                            <?php else: ?>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px;">
                                    <?php foreach ($profile_memberships as $pm): ?>
                                        <a href="my-tickets.php?tab=membership" style="background: #272730; border: 1px solid #3a3a45; border-radius: 10px; padding: 14px; display: flex; align-items: center; gap: 12px; text-decoration: none;">
                                            <img src="<?php echo htmlspecialchars($pm['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($pm['ten_the']); ?>" style="width: 56px; height: 56px; border-radius: 8px; object-fit: cover; flex-shrink: 0;">
                                            <div style="min-width: 0;">
                                                <div style="font-size: 14px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($pm['ten_the']); ?></div>
                                                <?php if (!empty($pm['nghe_si'])): ?>
                                                    <div style="font-size: 12px; color: #a0a3bd; margin-top: 2px;"><?php echo htmlspecialchars($pm['nghe_si']); ?></div>
                                                <?php endif; ?>
                                                <div style="font-size: 12px; color: #26c973; margin-top: 2px;">
                                                    Hiệu lực: <?php echo !empty($pm['ngay_kich_hoat']) ? date('d/m/Y', strtotime($pm['ngay_kich_hoat'])) . ' – ' : ''; ?><?php echo date('d/m/Y', strtotime($pm['ngay_het_han'])); ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>

                        <div style="border-top: 1px solid #2d2d35;"></div>

                        <!-- Đang theo dõi -->
                        <section class="followed-section">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
                                    <i class="fa-solid fa-star" style="color: #ffd700;"></i> Đang theo dõi
                                    <?php if (!empty($followed_stars)): ?>
                                        <span style="font-size: 13px; font-weight: 500; color: #a0a3bd;">(<?php echo count($followed_stars); ?>)</span>
                                    <?php endif; ?>
                                </h3>
                                <a href="organizer.php" style="color: #26c973; text-decoration: none; font-size: 13px; font-weight: 600;">
                                    Khám phá thêm &gt;
                                </a>
                            </div>

                            <?php if (empty($followed_stars)): ?>
                                <p style="color: #777; font-style: italic; font-size: 13.5px; margin: 0;">Bạn chưa có danh sách theo dõi</p>
                            <?php else: ?>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px;">
                                    <?php foreach ($followed_stars as $fStar):
                                        $fRole = $roleLabels[$fStar['loai']] ?? 'Ban tổ chức';
                                    ?>
                                        <div style="background: #272730; border: 1px solid #3a3a45; border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                            <a href="organizer.php?slug=<?php echo urlencode($fStar['slug']); ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none; min-width: 0; flex: 1;">
                                                <img src="<?php echo htmlspecialchars($fStar['avatar']); ?>"
                                                     alt="<?php echo htmlspecialchars($fStar['ten_hien_thi']); ?>"
                                                     style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 2px solid rgba(38,201,115,0.4);"
                                                     onerror="this.src='../assets/images/default-avatar.png'">
                                                <div style="min-width: 0;">
                                                    <div style="font-size: 15px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                        <?php echo htmlspecialchars($fStar['ten_hien_thi']); ?>
                                                        <?php if (!empty($fStar['da_xac_minh'])): ?>
                                                            <i class="fa-solid fa-circle-check" style="color: #26c973; font-size: 13px; margin-left: 3px;"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div style="font-size: 12px; color: #a0a3bd; margin-top: 2px;"><?php echo htmlspecialchars($fRole); ?></div>
                                                </div>
                                            </a>

                                            <form method="POST" action="my-tickets.php?tab=profile" style="margin: 0; flex-shrink: 0;" onsubmit="return confirm('Bỏ theo dõi <?php echo htmlspecialchars($fStar['ten_hien_thi'], ENT_QUOTES); ?>?');">
                                                <input type="hidden" name="action" value="unfollow_star">
                                                <input type="hidden" name="star_id" value="<?php echo (int)$fStar['id']; ?>">
                                                <button type="submit" style="background: rgba(38, 201, 115, 0.15); color: #26c973; border: 1px solid #26c973; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                    <i class="fa-solid fa-check"></i> Đang theo dõi
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
                    <?php break; ?>

                <?php case 'profile-edit':
                    $cur_gender = $_POST['gender'] ?? $user_gender;
                ?>
                    <h2 style="font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 24px;">Thông tin tài khoản</h2>

                    <div style="max-width: 720px;">
                        <!-- Form cập nhật hồ sơ -->
                        <form id="editProfileForm" method="POST" action="my-tickets.php?tab=profile-edit" enctype="multipart/form-data"
                              style="background: #272730; border: 1px solid #3a3a45; padding: 30px; border-radius: 12px;">
                            <input type="hidden" name="action" value="update_profile">

                            <!-- Avatar & Camera Icon (tích hợp từ edit-profile.php) -->
                            <section class="avatar-section" style="text-align: center; margin-bottom: 24px;">
                                <div class="avatar-wrapper" style="position: relative; display: inline-block;">
                                    <?php if ($has_custom_avatar): ?>
                                        <img id="avatarImg"
                                             src="<?php echo htmlspecialchars($user_avatar); ?>?v=<?php echo time(); ?>"
                                             alt="Avatar"
                                             style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; background: #26c973; border: 3px solid #3a3a45;">
                                    <?php else: ?>
                                        <span id="avatarImg"
                                              style="display: inline-flex; width: 130px; height: 130px; align-items: center; justify-content: center; background: #26c973; color: #fff; font-size: 48px; font-weight: 700; border-radius: 50%; border: 3px solid #3a3a45;"><?php echo htmlspecialchars($user_first_char); ?></span>
                                    <?php endif; ?>

                                    <!-- Camera upload button -->
                                    <label for="avatarUpload"
                                           title="Thay đổi ảnh đại diện"
                                           style="position: absolute; right: 0; bottom: 0; background: #2b2f3e; color: #26c973; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid #18191a;">
                                        <i class="fa-solid fa-camera"></i>
                                    </label>
                                    <input id="avatarUpload"
                                           name="avatar"
                                           type="file"
                                           accept="image/*"
                                           style="display: none;">
                                </div>

                                <p style="margin-top: 10px; color: #a0a3bd; font-size: 13.5px;">
                                    Cung cấp thông tin chính xác sẽ hỗ trợ bạn trong quá trình mua vé, hoặc khi cần xác thực vé
                                </p>
                                <?php if (!empty($errors['avatar'])): ?>
                                    <span style="color: #ff4d4f; font-size: 12px; display: block; margin-top: 4px;"><?php echo htmlspecialchars($errors['avatar']); ?></span>
                                <?php endif; ?>
                            </section>

                            <!-- Họ và tên -->
                            <div class="form-group" style="margin-bottom: 18px;">
                                <label for="hoTen" style="display: block; font-size: 13.5px; font-weight: 600; color: #fff; margin-bottom: 8px;">Họ và tên</label>
                                <input type="text" id="hoTen" name="ho_ten"
                                       value="<?php echo htmlspecialchars($_POST['ho_ten'] ?? $user_name); ?>"
                                       placeholder="Nhập họ và tên"
                                       class="form-control"
                                       style="width: 100%; padding: 10px 12px; background: #18191a; color: #fff; border: 1px solid #444; border-radius: 6px; font-size: 14px; outline: none;">
                                <?php if (!empty($errors['ho_ten'])): ?>
                                    <span style="color: #ff4d4f; font-size: 12px; margin-top: 4px; display: block;"><?php echo htmlspecialchars($errors['ho_ten']); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Số điện thoại -->
                            <div class="form-group" style="margin-bottom: 18px;">
                                <label for="phone" style="display: block; font-size: 13.5px; font-weight: 600; color: #fff; margin-bottom: 8px;">Số điện thoại</label>
                                <div style="display: flex; align-items: center;">
                                    <select name="country_code" id="countryCode"
                                            style="padding: 10px 12px; background: #18191a; color: #fff; border: 1px solid #444; border-radius: 6px; font-size: 14px; outline: none;">
                                        <option value="+84" selected>+84</option>
                                    </select>
                                    <div style="position: relative; flex: 1; margin-left: 8px;">
                                        <input type="text" id="phone" name="phone"
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? $user_phone); ?>"
                                               placeholder="Nhập số điện thoại"
                                               class="form-control"
                                               style="width: 100%; padding: 10px 32px 10px 12px; background: #18191a; color: #fff; border: 1px solid #444; border-radius: 6px; font-size: 14px; outline: none;">
                                        <span id="clearPhone"
                                              title="Xóa số điện thoại"
                                              style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #8c8fa6; font-size: 13px;">✕</span>
                                    </div>
                                </div>
                                <?php if (!empty($errors['phone'])): ?>
                                    <span style="color: #ff4d4f; font-size: 12px; margin-top: 4px; display: block;"><?php echo htmlspecialchars($errors['phone']); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Email -->
                            <div class="form-group" style="margin-bottom: 18px; position: relative;">
                                <label for="email" style="display: block; font-size: 13.5px; font-weight: 600; color: #fff; margin-bottom: 8px;">Email</label>
                                <div style="position: relative;">
                                    <input type="email" id="email" name="email"
                                           value="<?php echo htmlspecialchars($user_email); ?>"
                                           class="form-control"
                                           readonly
                                           style="width: 100%; padding: 10px 36px 10px 12px; background: #18191a; color: #8c8fa6; border: 1px solid #444; border-radius: 6px; font-size: 14px; cursor: not-allowed;">
                                    <i class="fa-solid fa-circle-check"
                                       style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #26c973;"></i>
                                </div>
                            </div>

                            <!-- Ngày sinh -->
                            <div class="form-group" style="margin-bottom: 18px;">
                                <label for="dob" style="display: block; font-size: 13.5px; font-weight: 600; color: #fff; margin-bottom: 8px;">Ngày sinh</label>
                                <input type="date" id="dob" name="dob"
                                       value="<?php echo !empty($_POST['dob'] ?? $user_dob) ? htmlspecialchars($_POST['dob'] ?? $user_dob) : ''; ?>"
                                       class="form-control"
                                       style="width: 100%; padding: 10px 12px; background: #18191a; color: #fff; border: 1px solid #444; border-radius: 6px; font-size: 14px; outline: none;">
                                <?php if (!empty($errors['dob'])): ?>
                                    <span style="color: #ff4d4f; font-size: 12px; margin-top: 4px; display: block;"><?php echo htmlspecialchars($errors['dob']); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Giới tính -->
                            <div class="form-group" style="margin-bottom: 24px;">
                                <label style="display: block; font-size: 13.5px; font-weight: 600; color: #fff; margin-bottom: 10px;">Giới tính</label>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <?php
                                    $genders = ['nam' => 'Nam', 'nu' => 'Nữ', 'khac' => 'Khác'];
                                    foreach ($genders as $val => $label):
                                        $checked = ($cur_gender === $val) ? 'checked' : '';
                                    ?>
                                        <label style="display: inline-flex; align-items: center; gap: 6px; color: #fff; font-size: 14px; cursor: pointer;">
                                            <input type="radio" name="gender" value="<?php echo $val; ?>" <?php echo $checked; ?> style="accent-color: #26c973;">
                                            <span><?php echo $label; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Nút Hoàn thành -->
                            <button type="submit"
                                    style="width: 100%; padding: 12px; background: #26c973; color: #fff; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer;">
                                Hoàn thành
                            </button>
                        </form>
                    </div>
                    <?php break; ?>

                <?php case 'my-events':
                    $created_events = [];
                    if (isset($pdo) && $user_id > 0) {
                        try {

                            $dmTable = ($pdo->query("SHOW TABLES LIKE 'danh_muc'")->fetchColumn() !== false) ? 'danh_muc' : 'danhmuc';
                            $dmCols  = $pdo->query("SHOW COLUMNS FROM `{$dmTable}`")->fetchAll(PDO::FETCH_COLUMN);
                            $dmCol   = 'ten_danh_muc';
                            foreach (['ten_danh_muc', 'ten', 'name', 'tieu_de'] as $cand) {
                                if (in_array($cand, $dmCols, true)) {
                                    $dmCol = $cand;
                                    break;
                                }
                            }

                            $sqlCreated = "SELECT s.*,
                                                  dm.`{$dmCol}` AS ten_danh_muc,
                                                  (SELECT COUNT(*) FROM ve v WHERE v.su_kien_id = s.id AND v.trang_thai != 'da-huy') AS so_ve_da_ban,
                                                  (SELECT COALESCE(SUM(CAST(REPLACE(REPLACE(v.gia_ve, '.', ''), ' đ', '') AS UNSIGNED)), 0)
                                                   FROM ve v WHERE v.su_kien_id = s.id AND v.trang_thai = 'thanh-cong') AS doanh_thu
                                           FROM sukien s
                                           LEFT JOIN `{$dmTable}` dm ON s.danh_muc_id = dm.id
                                           WHERE s.nguoi_tao_id = :uid
                                           ORDER BY s.id DESC";
                            $stmtEv = $pdo->prepare($sqlCreated);
                            $stmtEv->execute(['uid' => $user_id]);
                            $created_events = $stmtEv->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            $created_events = [];
                        }
                    }

                    $ev_filter         = $_GET['ev_filter'] ?? 'all';
                    $ev_count_all      = count($created_events);
                    $ev_count_public   = 0;
                    $ev_count_draft    = 0;
                    $ev_count_ended    = 0;
                    $filtered_created  = [];

                    foreach ($created_events as $ev) {
                        $tl      = getEventTimelineStatus($ev['thoi_gian'] ?? '');
                        $pubStat = $ev['trang_thai'] ?? 'cong_khai';

                        if ($pubStat === 'nhap') $ev_count_draft++;
                        else $ev_count_public++;

                        if ($tl === 'da-ket-thuc') $ev_count_ended++;

                        $match = ($ev_filter === 'all')
                              || ($ev_filter === 'cong_khai' && $pubStat !== 'nhap')
                              || ($ev_filter === 'nhap' && $pubStat === 'nhap')
                              || ($ev_filter === 'da-ket-thuc' && $tl === 'da-ket-thuc');

                        if ($match) {
                            $ev['computed_timeline'] = $tl;
                            $filtered_created[]      = $ev;
                        }
                    }
                ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
                        <h2 style="font-size: 24px; font-weight: 700; color: #fff; margin: 0;">Sự kiện của tôi</h2>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <a href="create-event.php?view=reports" style="background: #272730; color: #fff; border: 1px solid #3a3a45; padding: 9px 16px; border-radius: 8px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-chart-line" style="color: #26c973;"></i> Báo cáo doanh thu
                            </a>
                            <a href="create-event.php" style="background: #26c973; color: #fff; padding: 9px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-plus"></i> Tạo sự kiện mới
                            </a>
                        </div>
                    </div>

                    <div class="account-pill-row" style="display: flex; gap: 10px; margin-bottom: 25px;">
                        <a href="my-tickets.php?tab=my-events&ev_filter=all" class="pill-btn <?php echo $ev_filter === 'all' ? 'active' : ''; ?>">
                            Tất cả (<?php echo $ev_count_all; ?>)
                        </a>
                        <a href="my-tickets.php?tab=my-events&ev_filter=cong_khai" class="pill-btn <?php echo $ev_filter === 'cong_khai' ? 'active' : ''; ?>">
                            Đang công khai (<?php echo $ev_count_public; ?>)
                        </a>
                        <a href="my-tickets.php?tab=my-events&ev_filter=nhap" class="pill-btn <?php echo $ev_filter === 'nhap' ? 'active' : ''; ?>">
                            Bản nháp (<?php echo $ev_count_draft; ?>)
                        </a>
                        <a href="my-tickets.php?tab=my-events&ev_filter=da-ket-thuc" class="pill-btn <?php echo $ev_filter === 'da-ket-thuc' ? 'active' : ''; ?>">
                            Đã kết thúc (<?php echo $ev_count_ended; ?>)
                        </a>
                    </div>

                    <?php if (empty($filtered_created)): ?>
                        <div style="background: #272730; border: 1px dashed #3a3a45; border-radius: 12px; text-align: center; padding: 55px 20px;">
                            <i class="fa-regular fa-calendar-plus" style="font-size: 42px; color: #26c973; margin-bottom: 14px; display: block;"></i>
                            <p style="color: #fff; font-size: 16px; font-weight: 600; margin-bottom: 6px;">Bạn chưa có sự kiện nào trong mục này</p>
                            <p style="color: #a0a3bd; font-size: 13.5px; margin-bottom: 20px;">Bắt đầu đăng tải sự kiện của riêng bạn trên Organizer Center.</p>
                            <a href="create-event.php" style="background: #26c973; color: #fff; padding: 10px 22px; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-plus"></i> Tạo sự kiện ngay
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="tickets-list" style="display: flex; flex-direction: column; gap: 20px;">
                            <?php foreach ($filtered_created as $ev):
                                $ev_id       = (int)$ev['id'];
                                $ev_title    = $ev['ten_su_kien'] ?? 'Sự kiện chưa đặt tên';
                                $ev_img      = !empty($ev['banner']) ? $ev['banner'] : (!empty($ev['hinh_anh']) ? $ev['hinh_anh'] : '../assets/images/default-event.jpg');
                                $ev_time     = !empty($ev['thoi_gian']) ? date('H:i - d/m/Y', strtotime($ev['thoi_gian'])) : 'Đang cập nhật';
                                $ev_loc      = $ev['dia_diem'] ?? 'Đang cập nhật';
                                $ev_cat      = $ev['ten_danh_muc'] ?? 'Sự kiện';
                                $ev_sold     = (int)($ev['so_ve_da_ban'] ?? 0);
                                $ev_rev      = (int)($ev['doanh_thu'] ?? 0);
                                $ev_is_draft = (($ev['trang_thai'] ?? 'cong_khai') === 'nhap');
                                $ev_is_ended = ($ev['computed_timeline'] === 'da-ket-thuc');
                            ?>
                                <div class="my-created-event-card" style="background: #272730; border: 1px solid #3a3a45; border-radius: 12px; padding: 18px; display: flex; gap: 20px;">
                                    <div class="ticket-card-thumb" style="width: 190px; height: 115px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: #181920;">
                                        <img src="<?php echo htmlspecialchars($ev_img); ?>"
                                             alt="<?php echo htmlspecialchars($ev_title); ?>"
                                             style="width: 100%; height: 100%; object-fit: cover;"
                                             onerror="this.src='../assets/images/default-event.jpg'">
                                    </div>

                                    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between;">
                                        <div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 6px;">
                                                <span style="font-size: 12px; color: #26c973; font-weight: 600; background: rgba(38,201,115,0.12); padding: 2px 10px; border-radius: 999px;">
                                                    <?php echo htmlspecialchars($ev_cat); ?>
                                                </span>

                                                <div style="display: flex; gap: 6px; align-items: center;">
                                                    <?php if ($ev_is_draft): ?>
                                                        <span style="background: rgba(250,173,20,0.15); color: #faad14; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                                            <i class="fa-solid fa-pen-ruler"></i> Bản nháp
                                                        </span>
                                                    <?php elseif ($ev_is_ended): ?>
                                                        <span style="background: rgba(160,163,189,0.15); color: #a0a3bd; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                                            <i class="fa-regular fa-clock"></i> Đã kết thúc
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="background: rgba(38,201,115,0.15); color: #26c973; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                                            <i class="fa-solid fa-globe"></i> Đang công khai
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0 0 8px 0;">
                                                <a href="event-detail.php?id=<?php echo $ev_id; ?>" style="color: #fff; text-decoration: none;">
                                                    <?php echo htmlspecialchars($ev_title); ?>
                                                </a>
                                            </h3>

                                            <p style="font-size: 13px; color: #a0a3bd; margin: 2px 0;">
                                                <i class="fa-regular fa-calendar" style="width: 16px; color: #26c973;"></i> <?php echo htmlspecialchars($ev_time); ?>
                                            </p>
                                            <p style="font-size: 13px; color: #a0a3bd; margin: 2px 0;">
                                                <i class="fa-solid fa-location-dot" style="width: 16px; color: #26c973;"></i> <?php echo htmlspecialchars($ev_loc); ?>
                                            </p>
                                            <p style="font-size: 13px; color: #26c973; font-weight: 600; margin: 6px 0 0 0;">
                                                Đã bán: <?php echo $ev_sold; ?> vé &bull; Doanh thu: <?php echo number_format($ev_rev, 0, ',', '.'); ?> đ
                                            </p>
                                        </div>

                                        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 12px;">
                                            <a href="event-detail.php?id=<?php echo $ev_id; ?>"
                                               style="background: #2b2f3e; color: #26c973; border: 1px solid #26c973; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                                <i class="fa-regular fa-eye"></i> Xem trang vé
                                            </a>

                                            <a href="create-event.php?edit_id=<?php echo $ev_id; ?>"
                                               style="background: #333544; color: #fff; border: 1px solid #4a4d60; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                                <i class="fa-solid fa-pen"></i> Chỉnh sửa
                                            </a>

                                            <form method="POST" action="my-tickets.php?tab=my-events" style="display: inline-block; margin: 0;"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa sự kiện này không?');">
                                                <input type="hidden" name="action" value="delete_my_event">
                                                <input type="hidden" name="event_id" value="<?php echo $ev_id; ?>">
                                                <button type="submit"
                                                        style="background: transparent; color: #ff4d4f; border: 1px solid #ff4d4f; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                                    <i class="fa-regular fa-trash-can"></i> Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php break; ?>

                <?php case 'membership':
                    $mem_status = $_GET['mem_status'] ?? 'dang-hoat-dong';
                    $stmtUserMem = $pdo->prepare("
                        SELECT u.*, t.ten_the, t.nghe_si, t.gia, t.thoi_han_thang, t.hinh_anh
                        FROM the_thanh_vien_nguoidung u
                        JOIN the_thanh_vien t ON u.the_id = t.id
                        WHERE u.nguoi_dung_id = :uid AND u.trang_thai = :st
                        ORDER BY u.ngay_kich_hoat DESC
                    ");
                    $stmtUserMem->execute(['uid' => $user_id, 'st' => $mem_status]);
                    $my_memberships = $stmtUserMem->fetchAll(PDO::FETCH_ASSOC);
                ?>
                    <h2 style="font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 20px;">Thẻ thành viên</h2>
                    <div style="display: flex; gap: 30px; border-bottom: 1px solid #2d2d35; margin-bottom: 30px; padding-bottom: 10px;">
                        <a href="my-tickets.php?tab=membership&mem_status=dang-hoat-dong" style="color: <?php echo $mem_status === 'dang-hoat-dong' ? '#26c973' : '#a0a3bd'; ?>; font-weight: 600; text-decoration: none; <?php echo $mem_status === 'dang-hoat-dong' ? 'border-bottom: 2px solid #26c973; padding-bottom: 10px;' : ''; ?>">Đang hoạt động</a>
                        <a href="my-tickets.php?tab=membership&mem_status=da-het-han" style="color: <?php echo $mem_status === 'da-het-han' ? '#26c973' : '#a0a3bd'; ?>; font-weight: 600; text-decoration: none; <?php echo $mem_status === 'da-het-han' ? 'border-bottom: 2px solid #26c973; padding-bottom: 10px;' : ''; ?>">Đã hết hạn</a>
                        <a href="my-tickets.php?tab=membership&mem_status=da-huy" style="color: <?php echo $mem_status === 'da-huy' ? '#26c973' : '#a0a3bd'; ?>; font-weight: 600; text-decoration: none; <?php echo $mem_status === 'da-huy' ? 'border-bottom: 2px solid #26c973; padding-bottom: 10px;' : ''; ?>">Đã huỷ</a>
                    </div>

                    <?php if (empty($my_memberships)): ?>
                        <div style="text-align: center; padding: 60px 0;">
                            <p style="color: #a0a3bd; font-size: 14px;">Bạn chưa có thẻ nào trong mục này</p>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 18px;">
                            <?php foreach ($my_memberships as $m): ?>
                                <div class="ticket-card" style="background: #272730; border: 1px solid #3a3a45; border-radius: 12px; padding: 18px; display: flex; gap: 20px; align-items: center;">
                                    <img class="ticket-card-thumb" src="<?php echo htmlspecialchars($m['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($m['ten_the']); ?>" style="width: 170px; height: 105px; object-fit: cover; border-radius: 8px;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                            <span style="font-size: 12px; color: #a0a3bd;">Mã thẻ: <strong style="color: #fff;"><?php echo htmlspecialchars($m['ma_the']); ?></strong></span>
                                            <span style="background: rgba(38,201,115,0.15); color: #26c973; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                                <i class="fa-solid fa-crown"></i> Đang hoạt động
                                            </span>
                                        </div>
                                        <h3 style="font-size: 17px; font-weight: 700; color: #26c973; margin: 0 0 6px 0;"><?php echo htmlspecialchars($m['ten_the']); ?></h3>
                                        <p style="font-size: 13px; color: #d0d1d8; margin: 2px 0;">Nghệ sĩ / Đơn vị: <strong><?php echo htmlspecialchars($m['nghe_si']); ?></strong> (Gói <?php echo (int)$m['thoi_han_thang']; ?> tháng)</p>
                                        <p style="font-size: 12px; color: #a0a3bd; margin: 4px 0 0 0;">
                                            Hiệu lực: <?php echo date('d/m/Y', strtotime($m['ngay_kich_hoat'])); ?> - <?php echo date('d/m/Y', strtotime($m['ngay_het_han'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php break; ?>

                <?php default: ?>
                    <h2 style="font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 20px;">Vé của tôi</h2>

                    <div class="account-pill-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <a href="my-tickets.php?tab=tickets&status=all&timeline=<?php echo urlencode($filter_timeline); ?>" class="pill-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>">Tất cả (<?php echo $count_all; ?>)</a>
                        <a href="my-tickets.php?tab=tickets&status=thanh-cong&timeline=<?php echo urlencode($filter_timeline); ?>" class="pill-btn <?php echo $filter_status === 'thanh-cong' ? 'active' : ''; ?>">Thành công <?php echo $count_success > 0 ? "($count_success)" : ''; ?></a>
                        <a href="my-tickets.php?tab=tickets&status=dang-xu-ly&timeline=<?php echo urlencode($filter_timeline); ?>" class="pill-btn <?php echo $filter_status === 'dang-xu-ly' ? 'active' : ''; ?>">Đang xử lý <?php echo $count_pending > 0 ? "($count_pending)" : ''; ?></a>
                        <a href="my-tickets.php?tab=tickets&status=da-huy&timeline=<?php echo urlencode($filter_timeline); ?>" class="pill-btn <?php echo $filter_status === 'da-huy' ? 'active' : ''; ?>">Đã hủy <?php echo $count_cancelled > 0 ? "($count_cancelled)" : ''; ?></a>
                    </div>

                    <div style="display: flex; gap: 30px; border-bottom: 1px solid #2d2d35; margin-bottom: 30px;">
                        <a href="my-tickets.php?tab=tickets&status=<?php echo urlencode($filter_status); ?>&timeline=sap-dien-ra" class="tab-link <?php echo $filter_timeline === 'sap-dien-ra' ? 'active' : ''; ?>">Sắp diễn ra</a>
                        <a href="my-tickets.php?tab=tickets&status=<?php echo urlencode($filter_status); ?>&timeline=da-ket-thuc" class="tab-link <?php echo $filter_timeline === 'da-ket-thuc' ? 'active' : ''; ?>">Đã kết thúc</a>
                    </div>

                    <?php if (empty($filtered_tickets)): ?>
                        <div style="text-align: center; padding: 50px 0;">
                            <p style="color: #a0a3bd; font-size: 15px;">Bạn chưa có vé nào trong mục này</p>
                        </div>
                    <?php else: ?>
                        <div class="tickets-list" style="display: flex; flex-direction: column; gap: 20px;">
                            <?php foreach ($filtered_tickets as $ticket):
                                $t_code       = $ticket['ticket_code'] ?? $ticket['id'] ?? 'TKB-' . rand(1000, 9999);
                                $t_title      = $ticket['event_title'] ?? 'Sự kiện';
                                $t_cat        = $ticket['ticket_category'] ?? 'Vé Tiêu Chuẩn';
                                $t_price      = $ticket['price'] ?? '';
                                $t_status     = $ticket['status'] ?? 'thanh-cong';
                                $t_is_ended   = ($ticket['computed_timeline'] === 'da-ket-thuc');
                                $t_img        = $ticket['image'] ?? '../assets/images/default-event.jpg';
                                $t_db_id      = $ticket['ticket_db_id'] ?? null;
                                $t_orig_index = $ticket['original_index'] ?? null;
                            ?>
                                <div class="ticket-card" style="background: #272730; border: 1px solid #3a3a45; border-radius: 12px; padding: 18px; display: flex; gap: 20px;">
                                    <div class="ticket-card-thumb" style="width: 160px; height: 100px; border-radius: 8px; overflow: hidden; flex-shrink: 0;">
                                        <img src="<?php echo htmlspecialchars($t_img); ?>" alt="<?php echo htmlspecialchars($t_title); ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='../assets/images/default-event.jpg'">
                                    </div>
                                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                        <div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                                <span style="font-size: 12px; color: #a0a3bd;">Mã vé: <strong style="color: #fff;"><?php echo htmlspecialchars((string)$t_code); ?></strong></span>
                                                <div>
                                                    <?php if ($t_status === 'thanh-cong'): ?>
                                                        <span style="background: rgba(38,201,115,0.15); color: #26c973; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> Thành công</span>
                                                    <?php elseif ($t_status === 'da-huy'): ?>
                                                        <span style="background: rgba(255,77,79,0.15); color: #ff4d4f; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;"><i class="fa-solid fa-circle-xmark"></i> Đã hủy</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0 0 8px 0;"><?php echo htmlspecialchars($t_title); ?></h3>
                                            <p style="font-size: 13px; color: #a0a3bd; margin: 2px 0;"><i class="fa-solid fa-ticket"></i> Hạng vé: <strong style="color: #fff;"><?php echo htmlspecialchars($t_cat); ?></strong> <?php echo !empty($t_price) ? ' - ' . htmlspecialchars($t_price) : ''; ?></p>
                                            <p style="font-size: 13px; color: #a0a3bd; margin: 2px 0;"><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($ticket['date'] ?? 'Đang cập nhật'); ?></p>
                                        </div>

                                        <?php if ($t_status !== 'da-huy' && (int)$t_db_id > 0): ?>
                                            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-top: 10px;">
                                                <a href="ticket-qr.php?id=<?php echo (int) $t_db_id; ?>"
                                                   style="background: #2b2f3e; color: #26c973; border: 1px solid #26c973; padding: 5px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
                                                    <i class="fa-solid fa-qrcode"></i> Xem mã QR
                                                </a>

                                                <!-- Nút Hủy vé (AJAX) -->
                                                <?php if ($t_status !== 'da-checkin' && !$t_is_ended): ?>
                                                <button type="button" onclick="cancelTicket(<?php echo (int)$t_db_id; ?>, this)" style="background: transparent; color: #ff4d4f; border: 1px solid #ff4d4f; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                                    <i class="fa-solid fa-ban"></i> Hủy vé
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php break; ?>
            <?php endswitch; ?>
        </section>

    </div>
</main>

<!-- Script xử lý Hủy vé qua AJAX -->
<script src="../assets/js/registrations.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/registrations.js'); ?>"></script>

<script>
// Preview avatar và xóa nhanh SĐT
(function () {
    const avatarUpload = document.getElementById('avatarUpload');
    if (avatarUpload) {
        avatarUpload.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (ev) {
                const img = document.getElementById('avatarImg');
                if (img && img.tagName.toLowerCase() === 'img') {
                    img.src = ev.target.result;
                } else if (img) {
                    const newImg = document.createElement('img');
                    newImg.id = 'avatarImg';
                    newImg.src = ev.target.result;
                    newImg.alt = 'Avatar';
                    newImg.style = 'width:130px;height:130px;border-radius:50%;object-fit:cover;background:#26c973;border:3px solid #3a3a45;';
                    img.parentNode.replaceChild(newImg, img);
                }
            };
            reader.readAsDataURL(file);
        });
    }

    const clearPhoneBtn = document.getElementById('clearPhone');
    if (clearPhoneBtn) {
        clearPhoneBtn.addEventListener('click', function () {
            const phoneInput = document.getElementById('phone');
            if (phoneInput) phoneInput.value = '';
        });
    }
})();
</script>

<?php include '../templates/footer.php'; ?>