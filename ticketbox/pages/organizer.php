<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['followed_stars'])) {
    $_SESSION['followed_stars'] = [];
}

$slug = trim($_GET['slug'] ?? '');
$tab  = trim($_GET['tab'] ?? 'all');
$currentStar = null;

// Xử lý Theo dõi / Bỏ theo dõi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_follow_slug'])) {
    $targetSlug = trim($_POST['toggle_follow_slug']);
    if ($targetSlug !== '') {
        if (in_array($targetSlug, $_SESSION['followed_stars'], true)) {
            $_SESSION['followed_stars'] = array_values(array_diff($_SESSION['followed_stars'], [$targetSlug]));
            $pdo->prepare("UPDATE ticketstars SET luot_theo_doi = GREATEST(0, luot_theo_doi - 1) WHERE slug = :s")->execute(['s' => $targetSlug]);
        } else {
            $_SESSION['followed_stars'][] = $targetSlug;
            $pdo->prepare("UPDATE ticketstars SET luot_theo_doi = luot_theo_doi + 1 WHERE slug = :s")->execute(['s' => $targetSlug]);
        }
    }
    header("Location: organizer.php?slug=" . urlencode($targetSlug));
    exit;
}

if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT * FROM ticketstars WHERE slug = :slug LIMIT 1");
    $stmt->execute(['slug' => $slug]);
    $currentStar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Kiểm tra trạng thái đăng nhập hiện tại của người dùng
$loggedUser = null;
if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
    $loggedUser = $_SESSION['user'];
} elseif (!empty($_SESSION['user_id'])) {
    $stmtU = $pdo->prepare("SELECT ho_ten, avatar FROM nguoidung WHERE id = :uid LIMIT 1");
    $stmtU->execute(['uid' => (int)$_SESSION['user_id']]);
    $loggedUser = $stmtU->fetch(PDO::FETCH_ASSOC);
}

include '../templates/header.php';
?>

<?php if ($currentStar): ?>
    <script>
        document.body.classList.add('is-star-detail-page');
        document.title = <?php echo json_encode('Trang chủ nghệ sĩ | ' . $currentStar['ten_hien_thi']); ?>;
    </script>

    <?php
    $isFollowing = in_array($currentStar['slug'], $_SESSION['followed_stars'], true);
    $roleMap = [
        'BanToChuc' => 'Ban tổ chức',
        'NgheSi'    => 'Nghệ sĩ',
        'SanKhau'   => 'Sân khấu'
    ];
    $roleLabel = $roleMap[$currentStar['loai']] ?? 'Ban tổ chức';
    $initials  = mb_strtoupper(mb_substr($currentStar['ten_hien_thi'], 0, 2, 'UTF-8'), 'UTF-8');

    // 1. Lấy Sự kiện nổi bật (chỉ hiện nếu su_kien_noi_bat_id có dữ liệu)
    $featuredEvent = null;
    if (!empty($currentStar['su_kien_noi_bat_id'])) {
        $stmtFeat = $pdo->prepare("SELECT id, ten_su_kien, hinh_anh FROM sukien WHERE id = :eid LIMIT 1");
        $stmtFeat->execute(['eid' => (int)$currentStar['su_kien_noi_bat_id']]);
        $featuredEvent = $stmtFeat->fetch(PDO::FETCH_ASSOC);
    }

    // 2. Lấy Thẻ thành viên của Star (chỉ hiện nếu Star có gói Membership)
    $starMemberships = [];
    if (!empty($currentStar['nghe_si_membership'])) {
        $stmtMem = $pdo->prepare("SELECT * FROM the_thanh_vien WHERE nghe_si LIKE :ns ORDER BY id ASC");
        $stmtMem->execute(['ns' => '%' . $currentStar['nghe_si_membership'] . '%']);
        $starMemberships = $stmtMem->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Lấy toàn bộ Lịch hoạt động (sự kiện của Star từ bảng sukien)
    $starEvents = [];
    $keywords = array_filter(array_map('trim', explode('|', (string)$currentStar['tu_khoa_su_kien'])));
    if (!empty($keywords)) {
        $conds = [];
        $params = [];
        foreach ($keywords as $i => $kw) {
            $conds[] = "(s.ten_su_kien LIKE :k1_{$i} OR s.ten_ban_to_chuc LIKE :k2_{$i})";
            $params[":k1_{$i}"] = '%' . $kw . '%';
            $params[":k2_{$i}"] = '%' . $kw . '%';
        }
        $sql = "SELECT s.*,
                       (SELECT COUNT(*) FROM loaive lv WHERE lv.su_kien_id = s.id AND lv.da_het_ve = 0) AS ve_con_ban
                FROM sukien s
                WHERE " . implode(' OR ', $conds) . "
                ORDER BY s.thoi_gian DESC";
        $stmtEvt = $pdo->prepare($sql);
        $stmtEvt->execute($params);
        $starEvents = $stmtEvt->fetchAll(PDO::FETCH_ASSOC);
    }
    ?>

    <!-- THANH HEADER RIÊNG CỦA STAR (LUÔN TREO CỐ ĐỊNH KHI CUỘN XUỐNG) -->
    <div class="star-sticky-header">
        <div class="container star-sticky-inner">
            <!-- Bên trái: Logo Ticketbox vuông xanh + Tên Star -->
            <div style="display: flex; align-items: center; gap: 12px;">
                <a href="home.php" class="tkb-square-logo" title="Về trang chủ Ticketbox">
                    <span>ticket</span>
                    <span>box</span>
                </a>
                <a href="organizer.php?slug=<?php echo urlencode($currentStar['slug']); ?>" class="star-brand-title" style="text-decoration: none;">
                    <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?>
                </a>
            </div>

            <!-- Ở giữa: Các tab điều hướng -->
            <nav class="star-nav-links">
                <a href="organizer.php?slug=<?php echo urlencode($currentStar['slug']); ?>&tab=all" class="<?php echo $tab === 'all' ? 'active' : ''; ?>">Tất cả</a>
                <a href="organizer.php?slug=<?php echo urlencode($currentStar['slug']); ?>&tab=events" class="<?php echo $tab === 'events' ? 'active' : ''; ?>">Sự kiện</a>
                <a href="organizer.php?slug=<?php echo urlencode($currentStar['slug']); ?>&tab=news" class="<?php echo $tab === 'news' ? 'active' : ''; ?>">Tin tức</a>
                <a href="organizer.php?slug=<?php echo urlencode($currentStar['slug']); ?>&tab=community" class="<?php echo $tab === 'community' ? 'active' : ''; ?>">Cộng đồng</a>
                <a href="organizer.php?slug=<?php echo urlencode($currentStar['slug']); ?>&tab=about" class="<?php echo $tab === 'about' ? 'active' : ''; ?>">Về <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?></a>
            </nav>

            <!-- Bên phải: Tài khoản / Đăng nhập | Đăng ký -->
            <div class="star-auth-group">
                <?php if ($loggedUser): ?>
                    <a href="my-tickets.php" class="star-user-chip">
                        <img src="<?php echo htmlspecialchars($loggedUser['avatar'] ?: 'https://via.placeholder.com/60/26c973/ffffff?text=U'); ?>"
                             alt="Avatar"
                             onerror="this.src='https://via.placeholder.com/60/26c973/ffffff?text=U'">
                        <span><?php echo htmlspecialchars($loggedUser['ho_ten'] ?? 'Tài khoản'); ?></span>
                    </a>
                <?php else: ?>
                    <a href="login.php">Đăng nhập</a>
                    <span style="color: #6b7280;">|</span>
                    <a href="register.php">Đăng ký</a>
                <?php endif; ?>
                <span class="star-badge-circle"><i class="fa-solid fa-star"></i></span>
            </div>
        </div>
    </div>

    <!-- BANNER STAR TRÀN MÀN HÌNH -->
    <div class="star-banner-section">
        <?php if (!empty($currentStar['banner_star'])): ?>
            <img src="<?php echo htmlspecialchars($currentStar['banner_star']); ?>"
                 alt="Banner <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?>"
                 onerror="this.style.display='none'">
        <?php endif; ?>
    </div>

    <!-- NỘI DUNG CHÍNH 2 CỘT -->
    <main class="container" style="padding-bottom: 60px;">
        <div class="star-profile-grid">

            <!-- ================= CỘT TRÁI (TREO CỐ ĐỊNH KHI LƯỚT XUỐNG) ================= -->
            <aside class="star-left-col">
                <!-- 1. Thẻ thông tin Star -->
                <div class="star-box-card">
                    <div class="star-sidebar-top">
                        <img src="<?php echo htmlspecialchars($currentStar['avatar']); ?>"
                             alt="<?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?>"
                             class="star-sidebar-avatar"
                             onerror="this.src='https://via.placeholder.com/150/16181d/26c973?text=<?php echo urlencode($initials); ?>'">
                        <div>
                            <h1 class="star-sidebar-name">
                                <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?>
                                <i class="fa-solid fa-circle-check" style="color: #26c973; font-size: 16px;"></i>
                            </h1>
                            <span class="star-role-pill"><?php echo $roleLabel; ?></span>
                            <div style="color: #9ca3af; font-size: 12.5px; margin-top: 8px;">
                                <strong style="color: #fff;"><?php echo number_format((int)$currentStar['luot_theo_doi'], 0, ',', '.'); ?></strong> người theo dõi
                            </div>
                        </div>
                    </div>

                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="toggle_follow_slug" value="<?php echo htmlspecialchars($currentStar['slug']); ?>">
                        <button type="submit" class="btn-star-follow-full <?php echo $isFollowing ? 'is-following' : ''; ?>">
                            <?php if ($isFollowing): ?>
                                <i class="fa-solid fa-user-check"></i> Đang theo dõi
                            <?php else: ?>
                                <i class="fa-solid fa-user-plus"></i> Theo dõi
                            <?php endif; ?>
                        </button>
                    </form>
                </div>

                <!-- 2. Khối Sự kiện nổi bật (CHỈ HIỆN NẾU CÓ) -->
                <?php if ($featuredEvent): ?>
                    <div class="star-box-card">
                        <h3 class="star-featured-title">
                            <i class="fa-solid fa-star"></i>
                            <span>Sự kiện nổi bật</span>
                        </h3>
                        <a href="event-detail.php?id=<?php echo (int)$featuredEvent['id']; ?>"
                           class="star-featured-poster"
                           title="<?php echo htmlspecialchars($featuredEvent['ten_su_kien']); ?>">
                            <img src="<?php echo htmlspecialchars($featuredEvent['hinh_anh']); ?>"
                                 alt="<?php echo htmlspecialchars($featuredEvent['ten_su_kien']); ?>"
                                 onerror="this.src='https://via.placeholder.com/600x340/18191d/26c973?text=Ticketbox'">
                        </a>
                    </div>
                <?php endif; ?>
            </aside>

            <!-- ================= CỘT PHẢI ================= -->
            <div class="star-right-col">

                <?php if ($tab === 'about'): ?>
                    <div class="star-box-card">
                        <div class="star-activity-header">
                            <h2>Về <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?></h2>
                        </div>
                        <p style="color: #d1d5db; line-height: 1.7; margin: 0;">
                            <?php echo nl2br(htmlspecialchars($currentStar['mo_ta'] ?: 'Đơn vị hoạt động nghệ thuật chính thức trên hệ thống Ticketbox.')); ?>
                        </p>
                    </div>

                <?php elseif ($tab === 'news'): ?>
                    <div class="star-box-card">
                        <h2 class="star-notice-heading">
                            <i class="fa-solid fa-bullhorn"></i>
                            <span>Tin tức & Thông báo</span>
                        </h2>
                        <p style="color: #d1d5db; line-height: 1.7; margin: 0;">
                            <?php echo nl2br(htmlspecialchars($currentStar['thong_bao'] ?: 'Hiện chưa có thông báo mới từ ' . $currentStar['ten_hien_thi'] . '.')); ?>
                        </p>
                    </div>

                <?php elseif ($tab === 'community'): ?>
                    <div class="star-box-card">
                        <h2 class="star-notice-heading">
                            <i class="fa-solid fa-users"></i>
                            <span>Cộng đồng người hâm mộ <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?></span>
                        </h2>
                        <p style="color: #9ca3af; font-size: 14px; line-height: 1.6; margin: 0;">
                            Hiện có <strong style="color: #26c973;"><?php echo number_format((int)$currentStar['luot_theo_doi'], 0, ',', '.'); ?></strong> thành viên đang theo dõi kênh chính thức của <strong><?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?></strong>.
                        </p>
                    </div>

                <?php else: ?>
                    <!-- 1. Khối Thẻ thành viên (CHỈ HIỆN NẾU STAR CÓ GÓI MEMBERSHIP) -->
                    <?php if ($tab === 'all' && !empty($starMemberships)): ?>
                        <div class="star-box-card">
                            <h2 class="star-mem-heading">Thẻ thành viên</h2>
                            <p class="star-mem-greet">Xin chào, bạn iu!</p>
                            <p class="star-mem-sub">
                                Hãy nâng cấp tài khoản Membership để sở hữu đặc quyền từ <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?>
                            </p>

                            <div class="star-mem-grid">
                                <?php foreach ($starMemberships as $mem): ?>
                                    <div class="star-mem-item">
                                        <a href="membership-detail.php?id=<?php echo (int)$mem['id']; ?>" class="star-mem-item-thumb">
                                            <img src="<?php echo htmlspecialchars($mem['hinh_anh']); ?>"
                                                 alt="<?php echo htmlspecialchars($mem['ten_the']); ?>"
                                                 onerror="this.src='https://via.placeholder.com/480x260/18191d/26c973?text=<?php echo urlencode($mem['ten_the']); ?>'">
                                        </a>
                                        <div class="star-mem-item-body">
                                            <a href="membership-detail.php?id=<?php echo (int)$mem['id']; ?>" class="star-mem-item-name">
                                                <?php echo htmlspecialchars($mem['ten_the']); ?>
                                            </a>
                                            <div class="star-mem-item-meta">
                                                <span><?php echo number_format((int)$mem['gia'], 0, ',', '.'); ?> đ</span>
                                                <span><?php echo (int)$mem['thoi_han_thang']; ?> tháng</span>
                                            </div>
                                            <?php if ($mem['trang_thai'] === 'dang-ban'): ?>
                                                <a href="membership-detail.php?id=<?php echo (int)$mem['id']; ?>" class="btn-star-mem-yellow">Tham gia ngay</a>
                                            <?php else: ?>
                                                <a href="membership-detail.php?id=<?php echo (int)$mem['id']; ?>" class="btn-star-soldout">
                                                    <?php echo $mem['trang_thai'] === 'da-ban-het' ? 'Đã bán hết' : 'Đã đóng bán'; ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 2. Khối Thông báo (CHỈ HIỆN NẾU CÓ THÔNG BÁO) -->
                    <?php if ($tab === 'all' && !empty($currentStar['thong_bao'])): ?>
                        <div class="star-box-card">
                            <h2 class="star-notice-heading">
                                <i class="fa-solid fa-bullhorn"></i>
                                <span>Thông báo</span>
                            </h2>
                            <p style="color: #d1d5db; font-size: 14px; line-height: 1.6; margin: 0;">
                                <?php echo nl2br(htmlspecialchars($currentStar['thong_bao'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- 3. Khối Lịch hoạt động (Truy xuất từ bảng sukien) -->
                    <div class="star-box-card">
                        <div class="star-activity-header">
                            <h2>Lịch hoạt động</h2>
                            <a href="organizer.php?slug=<?php echo urlencode($currentStar['slug']); ?>&tab=events" style="color: #26c973; font-size: 13.5px; font-weight: 600; text-decoration: none;">
                                Xem tất cả <i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i>
                            </a>
                        </div>

                        <?php if (!empty($starEvents)): ?>
                            <div class="star-timeline">
                                <?php foreach ($starEvents as $ev):
                                    $eventTs   = strtotime($ev['thoi_gian']);
                                    $isEnded   = $eventTs < time();
                                    $isSoldOut = ((int)$ev['ve_con_ban'] === 0);
                                    $dateLabel = date('d', $eventTs) . ' Tháng ' . date('m, Y', $eventTs);
                                ?>
                                    <div class="star-timeline-item">
                                        <span class="star-timeline-dot"></span>
                                        <div class="star-timeline-date">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span><?php echo $dateLabel; ?></span>
                                        </div>

                                        <div class="star-event-row-card">
                                            <a href="event-detail.php?id=<?php echo (int)$ev['id']; ?>" class="star-event-thumb">
                                                <img src="<?php echo htmlspecialchars($ev['hinh_anh']); ?>"
                                                     alt="<?php echo htmlspecialchars($ev['ten_su_kien']); ?>"
                                                     onerror="this.src='https://via.placeholder.com/380x216/18191d/26c973?text=Ticketbox'">
                                                <?php if ($isEnded): ?>
                                                    <span class="badge-event-ended">Đã diễn ra</span>
                                                <?php endif; ?>
                                            </a>

                                            <div class="star-event-info">
                                                <a href="event-detail.php?id=<?php echo (int)$ev['id']; ?>" class="star-event-title">
                                                    <?php echo htmlspecialchars($ev['ten_su_kien']); ?>
                                                </a>
                                                <div class="star-event-price">
                                                    Từ <?php echo number_format((int)$ev['gia_thap_nhat'], 0, ',', '.'); ?> đ
                                                </div>

                                                <?php if (!$isEnded): ?>
                                                    <?php if ($isSoldOut): ?>
                                                        <a href="event-detail.php?id=<?php echo (int)$ev['id']; ?>" class="btn-star-soldout">Hết vé</a>
                                                    <?php else: ?>
                                                        <a href="event-detail.php?id=<?php echo (int)$ev['id']; ?>" class="btn-star-buy">Mua vé ngay</a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #9ca3af; text-align: center; padding: 24px 0; margin: 0;">
                                Chưa có lịch hoạt động mới từ <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?>.
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

<?php else: ?>
    <!-- Danh bạ toàn bộ 28 TicketStars -->
    <?php
    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
        $stmtAll = $pdo->prepare("SELECT * FROM ticketstars WHERE ten_hien_thi LIKE :q ORDER BY thu_tu ASC");
        $stmtAll->execute(['q' => '%' . $q . '%']);
    } else {
        $stmtAll = $pdo->query("SELECT * FROM ticketstars ORDER BY thu_tu ASC");
    }
    $allStars = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <main class="container" style="padding: 32px 15px 60px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 24px;">
            <h1 class="section-title" style="margin: 0;">
                <i class="fa-solid fa-star" style="color: #f7b500;"></i> Tất cả Featured Stars (<?php echo count($allStars); ?>)
            </h1>
            <form method="GET" style="display: flex; gap: 8px;">
                <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Tìm nghệ sĩ, ban tổ chức..."
                       style="background: #1c1e24; border: 1px solid #353844; color: #fff; padding: 9px 14px; border-radius: 8px; outline: none;">
                <button type="submit" class="btn-star-buy" style="border: none; cursor: pointer;">Tìm kiếm</button>
            </form>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 14px;">
            <?php foreach ($allStars as $st):
                $ini = mb_strtoupper(mb_substr($st['ten_hien_thi'], 0, 2, 'UTF-8'), 'UTF-8');
            ?>
                <a href="organizer.php?slug=<?php echo urlencode($st['slug']); ?>" class="star-card" title="<?php echo htmlspecialchars($st['ten_hien_thi']); ?>">
                    <div class="avatar-ring">
                        <img src="<?php echo htmlspecialchars($st['avatar']); ?>" alt="<?php echo htmlspecialchars($st['ten_hien_thi']); ?>" class="star-avatar"
                             onerror="this.src='https://via.placeholder.com/150/16181d/26c973?text=<?php echo urlencode($ini); ?>'">
                    </div>
                    <div class="star-info">
                        <span class="star-name"><?php echo htmlspecialchars($st['ten_hien_thi']); ?></span>
                        <i class="fa-solid fa-circle-check verified-badge"></i>
                    </div>
                    <span style="font-size: 11.5px; color: #9ca3af;"><?php echo number_format((int)$st['luot_theo_doi'], 0, ',', '.'); ?> theo dõi</span>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
<?php endif; ?>

<?php include '../templates/footer.php'; ?>