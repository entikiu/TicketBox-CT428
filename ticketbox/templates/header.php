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
                            <span class="user-avatar-sm">
                                <?php
                                // Đã sửa 'name' thành 'ho_ten'
                                $userName = $_SESSION['user']['ho_ten'] ?? 'N';
                                echo mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');
                                ?>
                            </span>
                            <span><?php echo htmlspecialchars($_SESSION['user']['ho_ten'] ?? 'Tài khoản'); ?></span>
                            <i class="fa-solid fa-caret-down"></i>
                        </button>

                        <!-- Menu xổ xuống -->
                        <div class="dropdown-menu" id="accountDropdown">
                            <a href="#" class="btn-create-event"><i class="fa-solid fa-plus"></i> Tạo sự kiện</a>
                            <a href="my-tickets.php"><i class="fa-solid fa-receipt"></i> Vé đã mua</a>
                            <a href="my-tickets.php"><i class="fa-solid fa-gem"></i> Thẻ thành viên</a>
                            <a href="my-tickets.php"><i class="fa-regular fa-calendar-check"></i> Sự kiện của tôi</a>
                            <div class="dropdown-divider"></div>

                            <!-- NÚT ĐĂNG XUẤT (Tích hợp id="btnLogout" gọi API Logout) -->
                            <a href="#" id="btnLogout" class="logout-link">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
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