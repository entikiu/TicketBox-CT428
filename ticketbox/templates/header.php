<?php
// Khởi động Session ở đầu file để quản lý trạng thái đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
            <!-- Logo Text (Click chuyển về trang chủ) -->
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

                <?php if (isset($_SESSION['user'])): ?>

                    <!-- MENU TÀI KHOẢN KHI ĐÃ ĐĂNG NHẬP -->
                    <div class="user-account-dropdown">
                        <button type="button" class="account-btn" onclick="toggleAccountMenu(event)">
                            <!-- ĐÃ SỬA: Đồng bộ Avatar hiển thị trực tiếp ngay khi đăng nhập -->
                            <span class="user-avatar-sm" style="display: flex; align-items: center; justify-content: center; overflow: hidden; background: #26c973; color: #fff; width: 32px; height: 32px; border-radius: 50%;">
                                <?php
                                $userName      = $_SESSION['user']['ho_ten'] ?? $_SESSION['user']['name'] ?? 'Tài khoản';
                                $header_avatar = $_SESSION['user']['avatar'] ?? '';

                                // Xử lý đường dẫn tệp ảnh tuyệt đối / tương đối chính xác trên mọi trang
                                $resolved_path = '';
                                if (!empty($header_avatar)) {
                                    if (file_exists($header_avatar)) {
                                        $resolved_path = $header_avatar;
                                    } elseif (file_exists(__DIR__ . '/' . $header_avatar)) {
                                        $resolved_path = __DIR__ . '/' . $header_avatar;
                                    } elseif (file_exists(__DIR__ . '/../' . ltrim($header_avatar, '../'))) {
                                        $resolved_path = __DIR__ . '/../' . ltrim($header_avatar, '../');
                                    }
                                }

                                if (!empty($resolved_path) && !is_dir($resolved_path)) {
                                    // Thêm tham số ?v=time() để chống cache trình duyệt
                                    $display_src = (strpos($header_avatar, '../') === 0) ? $header_avatar : '../' . ltrim($header_avatar, './');
                                    echo '<img src="' . htmlspecialchars($display_src) . '?v=' . time() . '" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">';
                                } else {
                                    echo mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');
                                }
                                ?>
                            </span>
                            <span><?php echo htmlspecialchars($userName); ?></span>
                            <i class="fa-solid fa-caret-down"></i>
                        </button>

                        <!-- Menu xổ xuống -->
                        <div class="dropdown-menu" id="accountDropdown">
                            <a href="events-form.php" class="btn-create-event">
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

                        // Xử lý sự kiện nút đăng xuất
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