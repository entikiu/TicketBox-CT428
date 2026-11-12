<?php
// Khởi động Session ở đầu file để quản lý trạng thái đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tự động kết nối DB để đồng bộ dữ liệu toàn cục trên mọi trang
require_once __DIR__ . '/../config/database.php';

// 1. ĐỒNG BỘ DỮ LIỆU TỪ CSDL NGAY TRÊN HEADER ĐỂ TRÁNH LỖI SESSION
if (isset($_SESSION['user']) && isset($_SESSION['user']['id']) && isset($pdo)) {
    try {
        $sync_id = (int)$_SESSION['user']['id'];
        $stmtSync = $pdo->prepare("SELECT ho_ten, avatar FROM nguoidung WHERE id = :id LIMIT 1");
        $stmtSync->execute(['id' => $sync_id]);
        $dbUserSync = $stmtSync->fetch(PDO::FETCH_ASSOC);
        
        if ($dbUserSync) {
            $_SESSION['user']['ho_ten'] = $dbUserSync['ho_ten'];
            $_SESSION['user']['name']   = $dbUserSync['ho_ten'];
            $_SESSION['user']['avatar'] = $dbUserSync['avatar'] ?? '';
        }
    } catch (PDOException $e) {
        // Bỏ qua lỗi nếu CSDL gián đoạn
    }
}

$is_logged_in = isset($_SESSION['user']);
$user_data    = $_SESSION['user'] ?? [];
$header_name  = $user_data['ho_ten'] ?? $user_data['name'] ?? 'Tài khoản';
$header_avt   = $user_data['avatar'] ?? '';
$first_char   = mb_strtoupper(mb_substr($header_name, 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticketbox - Mua Vé Sự Kiện</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <header class="main-header">
        <div class="header-container">
            <!-- Logo Text -->
            <a href="home.php" class="logo-text" title="Về trang chủ Ticketbox">ticketbox</a>

            <!-- Thanh tìm kiếm -->
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Bạn tìm gì hôm nay?">
            </div>

            <!-- Links Điều Hướng -->
            <nav class="nav-links">
                <a href="events.php">Sự kiện</a>
                <a href="events.php">TicketStars</a>
                <a href="events.php">Bán lại vé</a>
                <a href="#">Blog</a>

                <?php if ($is_logged_in): ?>

                    <!-- MENU TÀI KHOẢN KHI ĐÃ ĐĂNG NHẬP -->
                    <div class="user-account-dropdown">
                        <button type="button" class="account-btn" onclick="toggleAccountMenu(event)">
                            <!-- KHUNG AVATAR ĐÃ ĐƯỢC ÉP LẤY ẢNH MỚI NHẤT -->
                            <span class="user-avatar-sm" style="display: flex; align-items: center; justify-content: center; overflow: hidden; background: #26c973; color: #fff; width: 32px; height: 32px; border-radius: 50%;">
                                <?php if (!empty($header_avt)): ?>
                                    <!-- Thêm ?v=time() để chống trình duyệt lưu Cache ảnh cũ -->
                                    <img src="<?php echo htmlspecialchars($header_avt); ?>?v=<?php echo time(); ?>" 
                                         alt="Avatar" 
                                         style="width: 100%; height: 100%; object-fit: cover;" 
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <span style="display:none; font-weight: bold;"><?php echo $first_char; ?></span>
                                <?php else: ?>
                                    <span style="font-weight: bold;"><?php echo $first_char; ?></span>
                                <?php endif; ?>
                            </span>
                            
                            <span><?php echo htmlspecialchars($header_name); ?></span>
                            <i class="fa-solid fa-caret-down"></i>
                        </button>

                        <!-- Menu xổ xuống -->
                        <div class="dropdown-menu" id="accountDropdown">
                            <a href="create-event.php" class="btn-create-event">
                                <i class="fa-solid fa-plus"></i> Tạo sự kiện
                            </a>

                            <a href="my-tickets.php?tab=tickets">
                                <i class="fa-solid fa-ticket-simple"></i> Vé đã mua
                            </a>

                            <a href="my-tickets.php?tab=membership">
                                <i class="fa-solid fa-gem"></i> Thẻ thành viên
                            </a>

                            <a href="my-tickets.php?tab=my-events">
                                <i class="fa-regular fa-calendar-check"></i> Sự kiện của tôi
                            </a>

                            <a href="my-tickets.php?tab=profile">
                                <i class="fa-regular fa-circle-user"></i> Trang cá nhân
                            </a>

                            <a href="my-tickets.php?tab=profile-edit">
                                <i class="fa-solid fa-gear"></i> Cài đặt tài khoản
                            </a>

                            <div class="dropdown-divider"></div>

                            <!-- Đăng xuất -->
                            <a href="logout.php" id="btnLogout" class="logout-link">
                                <i class="fa-solid fa-arrow-up-from-bracket" style="transform: rotate(90deg);"></i> Đăng xuất
                            </a>
                        </div>
                    </div>

                    <!-- Script toggle menu xổ xuống & Đăng xuất -->
                    <script>
                        function toggleAccountMenu(event) {
                            event.stopPropagation();
                            const dropdown = document.getElementById('accountDropdown');
                            if (dropdown) {
                                dropdown.classList.toggle('show');
                            }
                        }

                        document.addEventListener('click', function() {
                            const dropdown = document.getElementById('accountDropdown');
                            if (dropdown && dropdown.classList.contains('show')) {
                                dropdown.classList.remove('show');
                            }
                        });

                        document.addEventListener('DOMContentLoaded', function() {
                            const btnLogout = document.getElementById('btnLogout');
                            if (btnLogout) {
                               btnLogout.addEventListener('click', function(e) {
                                    if (typeof handleLogout === 'function') {
                                        e.preventDefault();
                                        handleLogout();
                                    }
                                });
                            }
                        });
                    </script>

                <?php else: ?>
                    <!-- HIỂN THỊ KHI CHƯA ĐĂNG NHẬP -->
                    <div class="auth-btn">
                        <a href="login.php">Đăng nhập</a> | <a href="register.php">Đăng ký</a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Nhúng thanh danh mục -->
    <?php require_once __DIR__ . '/navbar.php'; ?>