<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$current_tab = $_GET['tab'] ?? 'tickets';
$user_id     = (int)($_SESSION['user']['id'] ?? 0);

$errors = [];
$success_msg = '';

// 2. TỰ ĐỘNG NẠP DỮ LIỆU MỚI NHẤT TỪ CSDL VÀO SESSION KHI MỞ TRANG
if ($user_id > 0 && isset($pdo)) {
    try {
        $stmtUser = $pdo->prepare("SELECT * FROM nguoidung WHERE id = :id LIMIT 1");
        $stmtUser->execute(['id' => $user_id]);
        $dbUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

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
        // Lỗi đọc CSDL không ghi đè Session
    }
}

// Gán biến hiển thị cho giao diện
$user_name   = $_SESSION['user']['ho_ten'] ?? $_SESSION['user']['name'] ?? 'Tài khoản';
$user_email  = $_SESSION['user']['email'] ?? '';
$user_phone  = $_SESSION['user']['phone'] ?? '';
$user_dob    = $_SESSION['user']['dob'] ?? '';
$user_gender = $_SESSION['user']['gender'] ?? 'nam';
$user_avatar = !empty($_SESSION['user']['avatar']) ? $_SESSION['user']['avatar'] : '../assets/images/default-avatar.png';

// Hàm phân loại mốc thời gian sự kiện
function getEventTimelineStatus($date_str) {
    if (empty($date_str)) return 'sap-dien-ra';
    
    $day = $month = $year = null;

    if (preg_match('/(\d{1,2})[\.\/](\d{1,2})[\.\/](\d{4})/', $date_str, $matches)) {
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

// 3. XỬ LÝ SUBMIT FORM (CẬP NHẬT THÔNG TIN & HỦY VÉ)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // A. Cập nhật thông tin cá nhân & Upload Avatar
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

        // Xử lý tệp ảnh Avatar bằng đường dẫn tuyệt đối máy chủ
        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES['avatar_file']['tmp_name'];
            $fileName      = $_FILES['avatar_file']['name'];
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
                    // Xóa file avatar cũ trên máy chủ nếu đang nằm trong thư mục uploads/avatars/
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

        // CHỈ CẬP NHẬT SESSION KHI MYSQL UPDATE THÀNH CÔNG
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

                    // Đồng bộ dữ liệu vào Session
                    $_SESSION['user']['ho_ten'] = $ho_ten;
                    $_SESSION['user']['name']   = $ho_ten;
                    $_SESSION['user']['phone']  = $phone;
                    $_SESSION['user']['dob']    = $dob;
                    $_SESSION['user']['gender'] = $gender;
                    $_SESSION['user']['avatar'] = $avatar_path;

                    $user_name   = $ho_ten;
                    $user_phone  = $phone;
                    $user_dob    = $dob;
                    $user_gender = $gender;
                    $user_avatar = $avatar_path;
                    $success_msg = 'Cập nhật thành công và đã lưu vào CSDL!';
                } catch (PDOException $e) {
                    $errors['general'] = 'Lỗi lưu CSDL (Vui lòng chạy câu lệnh SQL thêm cột): ' . $e->getMessage();
                }
            } else {
                $errors['general'] = 'Không có kết nối CSDL hoặc ID tài khoản không hợp lệ.';
            }
        }
    }

    // B. Thao tác Hủy vé
    elseif ($action === 'cancel_ticket') {
        $ticket_db_id = $_POST['ticket_db_id'] ?? null;
        $ticket_index = $_POST['ticket_index'] ?? null;

        if ($ticket_db_id && isset($pdo) && $user_id > 0) {
            try {
                $stmtCancel = $pdo->prepare("UPDATE ve SET trang_thai = 'da-huy' WHERE id = :ticket_id AND nguoi_dung_id = :user_id");
                $stmtCancel->execute(['ticket_id' => $ticket_db_id, 'user_id' => $user_id]);
                $success_msg = 'Hủy vé thành công!';
            } catch (PDOException $e) {
                $errors['general'] = 'Không thể hủy vé: ' . $e->getMessage();
            }
        } elseif ($ticket_index !== null && isset($_SESSION['my_tickets'][$ticket_index])) {
            $_SESSION['my_tickets'][$ticket_index]['status'] = 'da-huy';
            $success_msg = 'Hủy vé thành công!';
        }
    }
// C. Thao tác Bỏ theo dõi Star / Ban tổ chức
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
}

// 4. LẤY DANH SÁCH VÉ TỪ CSDL MYSQL
$my_tickets = [];

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
    } catch (PDOException $e) {
        $my_tickets = [];
    }
}

if (empty($my_tickets)) {
    $my_tickets = $_SESSION['my_tickets'] ?? [];
}

// 5. LỌC VÉ & ĐẾM SỐ LƯỢNG
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
    
    <!-- BREADCRUMB -->
    <div class="breadcrumb" style="font-size: 13px; color: #a0a3bd; margin-bottom: 25px; display: flex; align-items: center; gap: 8px;">
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
        <?php else: ?>
            <span>Đơn hàng của tôi</span> <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i> <span style="color: #fff;">Vé của tôi</span>
        <?php endif; ?>
    </div>

    <!-- THÔNG BÁO LỖI HOẶC THÀNH CÔNG -->
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
        
        <!-- SIDEBAR BÊN TRÁI -->
        <aside class="account-sidebar" style="width: 260px; flex-shrink: 0;">
            <a href="my-tickets.php?tab=profile" style="text-decoration: none; display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
                <div style="width: 42px; height: 42px; border-radius: 50%; overflow: hidden; background: #26c973; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700;">
                    <?php if (!empty($user_avatar) && file_exists(__DIR__ . '/' . $user_avatar)): ?>
                        <img src="<?php echo htmlspecialchars($user_avatar); ?>?v=<?php echo time(); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fa-solid fa-user" style="font-size: 18px;"></i>
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

                <a href="my-tickets.php?tab=my-events" style="color: #fff; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-regular fa-star" style="width: 22px;"></i> Sự kiện của tôi
                </a>
            </nav>
        </aside>

        <!-- NỘI DUNG TỪNG TAB -->
        <section class="account-content" style="flex: 1;">
            <?php switch ($current_tab): 

               // ==================== 1. TRANG CÁ NHÂN TỔNG QUAN ====================
                case 'profile': 
                    // Lấy danh sách thẻ thành viên đang hoạt động
                    $profile_memberships = [];
                    // Lấy danh sách nghệ sĩ / ban tổ chức đang theo dõi
                    $followed_stars = [];

                    if (isset($pdo) && $user_id > 0) {
                        try {
                            $stmtProfMem = $pdo->prepare("
                                SELECT u.ma_the, u.ngay_het_han, t.ten_the, t.nghe_si, t.hinh_anh
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
                    <div style="text-align: center; margin-bottom: 50px;">
                        <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; margin: 0 auto 15px auto; background: #26c973; display: flex; align-items: center; justify-content: center; color: #fff; border: 3px solid #2d2d35;">
                            <?php if (!empty($user_avatar) && file_exists(__DIR__ . '/' . $user_avatar)): ?>
                                <img src="<?php echo htmlspecialchars($user_avatar); ?>?v=<?php echo time(); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fa-solid fa-user" style="font-size: 55px;"></i>
                            <?php endif; ?>
                        </div>
                        <h2 style="font-size: 22px; font-weight: 700; color: #fff; display: flex; align-items: center; justify-content: center; gap: 8px; margin: 0;">
                            <?php echo htmlspecialchars($user_name); ?>
                            <a href="my-tickets.php?tab=profile-edit" title="Chỉnh sửa thông tin" style="color: #a0a3bd; font-size: 15px; text-decoration: none;"><i class="fa-solid fa-pen"></i></a>
                        </h2>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 35px; max-width: 800px;">
                        <!-- MỤC 1: THẺ THÀNH VIÊN CỦA TÔI -->
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
                                    <i class="fa-solid fa-gem" style="color: #26c973;"></i> Thẻ thành viên của tôi
                                </h4>
                                <a href="my-tickets.php?tab=membership" style="color: #26c973; text-decoration: none; font-size: 13px; font-weight: 600;">Khám phá thêm <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i></a>
                            </div>

                            <?php if (empty($profile_memberships)): ?>
                                <p style="color: #6c6d7c; font-size: 13px; margin: 0;">Bạn chưa có thẻ thành viên</p>
                            <?php else: ?>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px;">
                                    <?php foreach ($profile_memberships as $pm): ?>
                                        <a href="my-tickets.php?tab=membership" style="background: #272730; border: 1px solid #3a3a45; border-radius: 10px; padding: 12px; display: flex; align-items: center; gap: 12px; text-decoration: none;">
                                            <img src="<?php echo htmlspecialchars($pm['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($pm['ten_the']); ?>" style="width: 56px; height: 56px; border-radius: 8px; object-fit: cover; flex-shrink: 0;">
                                            <div style="min-width: 0;">
                                                <div style="font-size: 14px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($pm['ten_the']); ?></div>
                                                <div style="font-size: 12px; color: #26c973; margin-top: 2px;">HSD: <?php echo date('d/m/Y', strtotime($pm['ngay_het_han'])); ?></div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- MỤC 2: ĐANG THEO DÕI -->
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
                                    <i class="fa-solid fa-star" style="color: #26c973;"></i> Đang theo dõi
                                    <?php if (!empty($followed_stars)): ?>
                                        <span style="font-size: 13px; font-weight: 500; color: #a0a3bd;">(<?php echo count($followed_stars); ?>)</span>
                                    <?php endif; ?>
                                </h4>
                                <a href="organizer.php" style="color: #26c973; text-decoration: none; font-size: 13px; font-weight: 600;">Khám phá thêm <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i></a>
                            </div>

                            <?php if (empty($followed_stars)): ?>
                                <p style="color: #6c6d7c; font-size: 13px; margin: 0;">Bạn chưa có danh sách theo dõi</p>
                            <?php else: ?>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 14px;">
                                    <?php foreach ($followed_stars as $fStar): 
                                        $fRole = $roleLabels[$fStar['loai']] ?? 'Ban tổ chức';
                                    ?>
                                        <div style="background: #272730; border: 1px solid #3a3a45; border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                            <a href="organizer.php?slug=<?php echo urlencode($fStar['slug']); ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none; min-width: 0; flex: 1;">
                                                <img src="<?php echo htmlspecialchars($fStar['avatar']); ?>"
                                                     alt="<?php echo htmlspecialchars($fStar['ten_hien_thi']); ?>"
                                                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 2px solid rgba(38,201,115,0.4);"
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
                        </div>
                    </div>
                    <?php break; ?>

                // ==================== 3. THẺ THÀNH VIÊN ====================
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
                                <div style="background: #272730; border: 1px solid #3a3a45; border-radius: 12px; padding: 18px; display: flex; gap: 20px; align-items: center;">
                                    <img src="<?php echo htmlspecialchars($m['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($m['ten_the']); ?>" style="width: 170px; height: 105px; object-fit: cover; border-radius: 8px;">
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

                // ==================== 4. VÉ CỦA TÔI ====================
                <?php default: ?>
                    <h2 style="font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 20px;">Vé của tôi</h2>

                    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
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
                                    <div style="width: 160px; height: 100px; border-radius: 8px; overflow: hidden; flex-shrink: 0;">
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
                                        
                                        <?php if ($t_status !== 'da-huy' && !$t_is_ended): ?>
                                            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-top: 10px;">
                                                <!-- Nút Xem mã QR -->
                                                <a href="ticket-qr.php?id=<?php echo (int) $t_db_id; ?>" 
                                                style="background: #2b2f3e; color: #26c973; border: 1px solid #26c973; padding: 5px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
                                                    <i class="fa-solid fa-qrcode"></i> Xem mã QR
                                                </a>

                                                <!-- Form Hủy vé riêng biệt -->
                                                <form method="POST" action="my-tickets.php?tab=tickets" style="display: inline-block; margin: 0;"
                                                    onsubmit="return confirm('Bạn có chắc chắn muốn hủy vé này?');">
                                                    <input type="hidden" name="action" value="cancel_ticket">
                                                    <input type="hidden" name="ticket_db_id" value="<?php echo htmlspecialchars((string) $t_db_id); ?>">
                                                    <input type="hidden" name="ticket_index" value="<?php echo (int) $t_orig_index; ?>">
                                                    <button type="submit"
                                                        style="background: transparent; color: #ff4d4f; border: 1px solid #ff4d4f; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                                        <i class="fa-solid fa-ban"></i> Hủy vé
                                                    </button>
                                                </form>
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

<?php include '../templates/footer.php'; ?>