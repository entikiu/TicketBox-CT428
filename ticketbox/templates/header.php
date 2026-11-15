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

$is_logged_in      = isset($_SESSION['user']);
$user_data         = $_SESSION['user'] ?? [];
$header_name       = $user_data['ho_ten'] ?? $user_data['name'] ?? 'Tài khoản';
$header_avt        = $user_data['avatar'] ?? '';
$first_char        = mb_strtoupper(mb_substr($header_name, 0, 1, 'UTF-8'), 'UTF-8');
$current_search_q  = trim((string)($_GET['q'] ?? $_GET['search'] ?? $_GET['keyword'] ?? ''));
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticketbox - Mua Vé Sự Kiện</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/search-ajax.css?v=<?php echo @filemtime(__DIR__ . '/../assets/css/search-ajax.css') ?: time(); ?>">
</head>

<body>

    <header class="main-header">
        <div class="header-container">
            <!-- Logo Text -->
            <a href="home.php" class="logo-text" title="Về trang chủ Ticketbox">ticketbox</a>

            <!-- Khung tìm kiếm AJAX Real-time -->
            <div class="tb-search-wrapper" id="tbSearchWrapper">
                <form class="tb-search-box" id="tbSearchForm" action="events.php" method="GET" autocomplete="off">
                    <i class="fa-solid fa-magnifying-glass tb-search-icon"></i>
                    <input 
                        type="text" 
                        name="q"
                        id="tbSearchInput" 
                        class="tb-search-input" 
                        placeholder="Bạn tìm gì hôm nay?" 
                        aria-label="Tìm kiếm sự kiện"
                        value="<?php echo htmlspecialchars($current_search_q); ?>"
                    >
                    <button type="button" id="tbSearchClearBtn" class="tb-search-clear" title="Xóa từ khóa" style="display: <?php echo $current_search_q !== '' ? 'flex' : 'none'; ?>;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <button type="submit" id="tbSearchSubmitBtn" class="tb-search-submit">Tìm kiếm</button>
                </form>

                <!-- Dropdown gợi ý & kết quả tìm kiếm -->
                <div class="tb-search-dropdown" id="tbSearchDropdown">
                    <!-- 1. Từ khóa xu hướng -->
                    <div class="tb-trending-list" id="tbTrendingSection">
                        <button type="button" class="tb-trending-item" data-keyword="tinh hà say hi">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                            <span>tinh hà say hi</span>
                        </button>
                        <button type="button" class="tb-trending-item" data-keyword="những thành phố mộng mơ">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                            <span>những thành phố mộng mơ</span>
                        </button>
                        <button type="button" class="tb-trending-item" data-keyword="skynote">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                            <span>skynote</span>
                        </button>
                    </div>

                    <!-- 2. Tab Khám phá theo Thể loại -->
                    <div class="tb-explore-section" id="tbExploreSection">
                        <div class="tb-explore-tabs">
                            <button type="button" class="tb-explore-tab active" data-tab="category">
                                Khám phá theo Thể loại
                            </button>
                        </div>

                        <!-- Tab 1: Thể loại -->
                        <div class="tb-explore-grid" id="tab-category">
                            <div class="tb-explore-card" data-category-id="1" data-label="Concert & Âm nhạc">
                                <img src="/TicketBox-CT428/ticketbox/assets/images/events/banner-tinh-ha.jpg" alt="Nhạc sống">
                                <div class="tb-explore-overlay"><span>Nhạc sống</span></div>
                            </div>
                            <div class="tb-explore-card" data-category-id="4" data-label="Sân khấu & Nghệ thuật">
                                <img src="/TicketBox-CT428/ticketbox/assets/images/events/banner-mua-roi-nuoc.jpg" alt="Sân khấu & Nghệ thuật">
                                <div class="tb-explore-overlay"><span>Sân khấu &amp; Nghệ thuật</span></div>
                            </div>
                            <div class="tb-explore-card" data-category-id="2" data-label="Thể thao & Giải trí">
                                <img src="/TicketBox-CT428/ticketbox/assets/images/events/banner-lion-championship.jpg" alt="Thể thao">
                                <div class="tb-explore-overlay"><span>Thể thao</span></div>
                            </div>
                            <div class="tb-explore-card" data-category-id="3" data-label="Workshop & Sáng tạo">
                                <img src="/TicketBox-CT428/ticketbox/assets/images/events/banner-mochi-cheesecake.jpg" alt="Hội thảo & Workshop">
                                <div class="tb-explore-overlay"><span>Hội thảo &amp; Workshop</span></div>
                            </div>
                        </div>

                        <!-- Tab 2: Thành phố -->
                        <div class="tb-explore-grid" id="tab-city" style="display: none;">
                            <div class="tb-explore-card" data-city="Hồ Chí Minh" data-label="TP. Hồ Chí Minh">
                                <img src="/TicketBox-CT428/ticketbox/assets/images/events/banner-bus-2-tang.jpg" alt="TP. Hồ Chí Minh">
                                <div class="tb-explore-overlay"><span>TP. Hồ Chí Minh</span></div>
                            </div>
                            <div class="tb-explore-card" data-city="Hà Nội" data-label="Hà Nội">
                                <img src="/TicketBox-CT428/ticketbox/assets/images/events/banner-tour-dem-van-mieu.jpg" alt="Hà Nội">
                                <div class="tb-explore-overlay"><span>Hà Nội</span></div>
                            </div>
                            <div class="tb-explore-card" data-city="Đà Lạt" data-label="Đà Lạt">
                                <img src="/TicketBox-CT428/ticketbox/assets/images/events/banner-strong.jpg" alt="Đà Lạt">
                                <div class="tb-explore-overlay"><span>Đà Lạt</span></div>
                            </div>
                            <div class="tb-explore-card" data-city="Sapa" data-label="Vị trí khác">
                                <img src="/TicketBox-CT428/ticketbox/assets/images/events/banner-du-luon-sapa.jpg" alt="Vị trí khác">
                                <div class="tb-explore-overlay"><span>Vị trí khác</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Lưới 3 cột Gợi ý / Kết quả AJAX -->
                    <div class="tb-results-section">
                        <div class="tb-results-header">
                            <h4 id="tbResultsHeading">Gợi ý dành cho bạn</h4>
                            <button type="button" id="tbResetFilterBtn" class="tb-reset-filter" style="display: none;">
                                Đặt lại bộ lọc
                            </button>
                        </div>
                        <div class="tb-results-grid" id="tbAjaxResultsGrid"></div>
                    </div>
                </div>
            </div>

            <!-- Links Điều Hướng -->
            <nav class="nav-links">
                <a href="events.php">Sự kiện</a>
                <a href="organizer.php">TicketStars</a>
                <a href="events.php">Bán lại vé</a>
                <a href="#">Blog</a>

                <?php if ($is_logged_in): ?>

                    <!-- MENU TÀI KHOẢN KHI ĐÃ ĐĂNG NHẬP -->
                    <div class="user-account-dropdown">
                        <button type="button" class="account-btn" onclick="toggleAccountMenu(event)">
                            <!-- KHUNG AVATAR ĐÃ ĐƯỢC ÉP LẤY ẢNH MỚI NHẤT -->
                            <span class="user-avatar-sm" style="display: flex; align-items: center; justify-content: center; overflow: hidden; background: #26c973; color: #fff; width: 32px; height: 32px; border-radius: 50%;">
                                <?php if (!empty($header_avt)): ?>
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
                            <?php 
                            $current_role = strtolower($user_data['vai_tro'] ?? '');
                            if ($current_role === 'admin'): 
                            ?>
                                <a href="../admin/index.php" style="color: #26c973; font-weight: bold; background: #f0fdf4;">
                                    <i class="fa-solid fa-shield-halved"></i> Trang quản trị
                                </a>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>

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