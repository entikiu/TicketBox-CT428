<?php
// Khởi động Session ở đầu file để quản lý trạng thái đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tự động kết nối DB để đồng bộ dữ liệu toàn cục trên mọi trang
require_once __DIR__ . '/../config/database.php';

// 0. XỬ LÝ TÌM KIẾM AJAX REAL-TIME (SỰ KIỆN + TICKETSTARS + THỂ LOẠI)
if (isset($_GET['tb_ajax_search']) && $_GET['tb_ajax_search'] === '1') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');

    $q          = trim((string)($_GET['q'] ?? ''));
    $categoryId = (int)($_GET['category_id'] ?? 0);
    $todayStart = date('Y-m-d 00:00:00');

    $response = [
        'stars'  => [],
        'events' => []
    ];

    if (isset($pdo)) {
        try {
            // Tìm TicketStars khi có từ khóa
            if ($q !== '') {
                $likeKw   = '%' . $q . '%';
                $prefixKw = $q . '%';

                $stmtStars = $pdo->prepare("
                    SELECT id, slug, ten_hien_thi, loai, avatar, luot_theo_doi, da_xac_minh
                    FROM ticketstars
                    WHERE ten_hien_thi LIKE :kw_1
                       OR tu_khoa_su_kien LIKE :kw_2
                       OR mo_ta LIKE :kw_3
                    ORDER BY 
                        CASE WHEN ten_hien_thi LIKE :prefix_kw THEN 0 ELSE 1 END ASC,
                        luot_theo_doi DESC,
                        thu_tu ASC
                    LIMIT 6
                ");
                $stmtStars->execute([
                    'kw_1'      => $likeKw,
                    'kw_2'      => $likeKw,
                    'kw_3'      => $likeKw,
                    'prefix_kw' => $prefixKw
                ]);

                $loaiMap = [
                    'NgheSi'    => 'Nghệ sĩ',
                    'BanToChuc' => 'Ban tổ chức',
                    'SanKhau'   => 'Sân khấu'
                ];

                foreach ($stmtStars->fetchAll(PDO::FETCH_ASSOC) as $st) {
                    $followers = (int)$st['luot_theo_doi'];
                    $followersText = $followers >= 1000
                        ? number_format($followers / 1000, 1, ',', '.') . 'K'
                        : (string)$followers;

                    $response['stars'][] = [
                        'id'             => (int)$st['id'],
                        'slug'           => $st['slug'],
                        'ten_hien_thi'   => $st['ten_hien_thi'],
                        'loai_label'     => $loaiMap[$st['loai']] ?? 'TicketStar',
                        'avatar'         => $st['avatar'],
                        'da_xac_minh'    => (int)$st['da_xac_minh'],
                        'followers_text' => $followersText
                    ];
                }
            }

            // Tìm Sự kiện
            $whereClauses = [
                "s.trang_thai = 'cong_khai'",
                "s.quyen_rieng_tu = 'public'"
            ];
            $params = [
                'now_1' => $todayStart,
                'now_2' => $todayStart
            ];

            if ($q !== '') {
                $likeEv = '%' . $q . '%';
                $whereClauses[] = "(
                    s.ten_su_kien LIKE :ev_kw_1
                    OR s.ten_ban_to_chuc LIKE :ev_kw_2
                    OR s.dia_diem LIKE :ev_kw_3
                    OR s.ten_dia_diem LIKE :ev_kw_4
                )";
                $params['ev_kw_1'] = $likeEv;
                $params['ev_kw_2'] = $likeEv;
                $params['ev_kw_3'] = $likeEv;
                $params['ev_kw_4'] = $likeEv;
            } elseif ($categoryId > 0) {
                if ($categoryId === 2) {
                    $whereClauses[] = "(s.danh_muc_id = 2 AND s.id NOT IN (35, 36, 39, 41))";
                } else {
                    $whereClauses[] = "s.danh_muc_id = :cat_id";
                    $params['cat_id'] = $categoryId;
                }
                $whereClauses[] = "(s.thoi_gian >= :cat_now_1 OR (s.thoi_gian_ket_thuc IS NOT NULL AND s.thoi_gian_ket_thuc >= :cat_now_2))";
                $params['cat_now_1'] = $todayStart;
                $params['cat_now_2'] = $todayStart;
            } else {
                $whereClauses[] = "(s.thoi_gian >= :def_now_1 OR (s.thoi_gian_ket_thuc IS NOT NULL AND s.thoi_gian_ket_thuc >= :def_now_2))";
                $params['def_now_1'] = $todayStart;
                $params['def_now_2'] = $todayStart;
            }

            $sqlEvents = "
                SELECT 
                    s.id,
                    s.ten_su_kien,
                    s.dia_diem,
                    s.gia_thap_nhat,
                    s.thoi_gian,
                    s.thoi_gian_ket_thuc,
                    s.hinh_anh,
                    (SELECT COUNT(*) FROM ve v WHERE v.su_kien_id = s.id AND v.trang_thai = 'thanh-cong') AS luot_ban,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM ticketstar_sukien tss WHERE tss.su_kien_id = s.id)
                          OR EXISTS (SELECT 1 FROM ticketstars ts WHERE ts.su_kien_noi_bat_id = s.id)
                        THEN 1 ELSE 0 
                    END AS is_star,
                    CASE 
                        WHEN s.thoi_gian >= :now_1 OR (s.thoi_gian_ket_thuc IS NOT NULL AND s.thoi_gian_ket_thuc >= :now_2)
                        THEN 1 ELSE 0 
                    END AS is_upcoming
                FROM sukien s
                WHERE " . implode(' AND ', $whereClauses) . "
                ORDER BY 
                    is_upcoming DESC,
                    luot_ban DESC,
                    is_star DESC,
                    s.ngay_tao DESC,
                    s.id DESC
                LIMIT 6
            ";

            $stmtEvents = $pdo->prepare($sqlEvents);
            $stmtEvents->execute($params);

            foreach ($stmtEvents->fetchAll(PDO::FETCH_ASSOC) as $ev) {
                $cityName = $ev['dia_diem'] ?? '';
                if ($cityName !== '' && strpos($cityName, ',') !== false) {
                    $cityName = trim(substr(strrchr($cityName, ','), 1));
                }

                $gia = (int)$ev['gia_thap_nhat'];
                $priceText = $gia === 0 ? 'Miễn phí' : 'Từ ' . number_format($gia, 0, ',', '.') . ' đ';

                $response['events'][] = [
                    'id'    => (int)$ev['id'],
                    'title' => $ev['ten_su_kien'],
                    'price' => $priceText,
                    'date'  => date('d.m.Y', strtotime($ev['thoi_gian'])),
                    'city'  => $cityName ?: 'Việt Nam',
                    'image' => $ev['hinh_anh']
                ];
            }
        } catch (PDOException $e) {
            // Trả về rỗng nếu lỗi truy vấn
        }
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

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
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
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
                        value="<?php echo htmlspecialchars($current_search_q); ?>">
                    <button type="button" id="tbSearchClearBtn" class="tb-search-clear" title="Xóa từ khóa" style="display: <?php echo $current_search_q !== '' ? 'flex' : 'none'; ?>;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <button type="submit" id="tbSearchSubmitBtn" class="tb-search-submit">Tìm kiếm</button>
                </form>

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

                    <div class="tb-explore-section" id="tbExploreSection">
                        <div class="tb-explore-tabs">
                            <button type="button" class="tb-explore-tab active" data-tab="category">
                                Khám phá theo Thể loại
                            </button>
                        </div>

                        <div class="tb-explore-grid" id="tab-category">
                            <div class="tb-explore-card" data-category-id="1" data-label="Nhạc sống">
                                <img src="../assets/images/events/banner-tinh-ha.jpg" alt="Nhạc sống">
                                <div class="tb-explore-overlay"><span>Nhạc sống</span></div>
                            </div>
                            <div class="tb-explore-card" data-category-id="4" data-label="Sân khấu & Nghệ thuật">
                                <img src="../assets/images/events/banner-mua-roi-nuoc.jpg" alt="Sân khấu & Nghệ thuật">
                                <div class="tb-explore-overlay"><span>Sân khấu &amp; Nghệ thuật</span></div>
                            </div>
                            <div class="tb-explore-card" data-category-id="2" data-label="Thể thao">
                                <img src="../assets/images/events/banner-lion-championship.jpg" alt="Thể thao">
                                <div class="tb-explore-overlay"><span>Thể thao</span></div>
                            </div>
                            <div class="tb-explore-card" data-category-id="3" data-label="Hội thảo & Workshop">
                                <img src="../assets/images/events/banner-mochi-cheesecake.jpg" alt="Hội thảo & Workshop">
                                <div class="tb-explore-overlay"><span>Hội thảo &amp; Workshop</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="tb-stars-results-section" id="tbStarsResultsSection" style="display: none;">
                        <div class="tb-stars-results-header">
                            <h4><i class="fa-solid fa-star" style="color: #fadb14;"></i> TicketStars</h4>
                            <a href="organizer.php" style="font-size: 12px; color: #26c973; text-decoration: none;">Xem tất cả</a>
                        </div>
                        <div class="tb-stars-results-grid" id="tbStarsResultsGrid"></div>
                    </div>
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

            <nav class="nav-links">
                <a href="events.php">Sự kiện</a>
                <a href="organizer.php">TicketStars</a>

                <?php if ($is_logged_in): ?>

                    <div class="user-account-dropdown">
                        <button type="button" class="account-btn" id="accountMenuBtn">
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

                            <a href="my-tickets.php?tab=edit-profile">
                                <i class="fa-solid fa-gear"></i> Cài đặt tài khoản
                            </a>

                            <div class="dropdown-divider"></div>

                            <!-- Đăng xuất -->
                            <a href="logout.php" id="btnLogout" class="logout-link">
                                <i class="fa-solid fa-arrow-up-from-bracket" style="transform: rotate(90deg);"></i> Đăng xuất
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="auth-btn">
                        <a href="login.php">Đăng nhập</a> | <a href="register.php">Đăng ký</a>
                    </div>
                <?php endif; ?>
                <button class="mobile-menu-btn d-lg-none"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#mobileGroupMenu"
                    aria-controls="mobileGroupMenu"
                    aria-label="Mở menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

    <div class="offcanvas offcanvas-end mobile-offcanvas"
        tabindex="-1"
        id="mobileGroupMenu"
        aria-labelledby="mobileGroupMenuLabel">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileGroupMenuLabel">
                ticketbox
            </h5>

            <button type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="offcanvas"
                aria-label="Đóng">
            </button>
        </div>

        <div class="offcanvas-body">
            <a href="create-event.php" class="btn w-100 fw-bold text-white rounded-pill mb-3" style="background-color: #00b14f;">
                <i class="fa-solid fa-plus me-1"></i> Tạo sự kiện
            </a>
            <a href="events.php" class="mobile-menu-link">
                <i class="fa-solid fa-calendar-days text-success"></i>
                <span>Sự kiện</span>
            </a>

            <a href="organizer.php" class="mobile-menu-link">
                <i class="fa-solid fa-star text-warning"></i>
                <span>TicketStars</span>
            </a>

        </div>
    </div>

    <?php require_once __DIR__ . '/navbar.php'; ?>