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

// Chuẩn bị dữ liệu cho trang chi tiết Star hoặc trang danh sách tất cả Star
$featuredEvent = null;
$starEvents    = [];
$allStars      = [];
$isFollowing   = false;
$roleLabels    = [
    'NgheSi'    => 'Nghệ sĩ',
    'BanToChuc' => 'Ban tổ chức',
    'SanKhau'   => 'Sân khấu'
];

if ($currentStar) {
    $isFollowing = in_array($currentStar['slug'], $_SESSION['followed_stars'], true);

    // 1. Lấy Sự kiện nổi bật (chỉ hiện nếu su_kien_noi_bat_id có dữ liệu)
    if (!empty($currentStar['su_kien_noi_bat_id'])) {
        $stmtFeat = $pdo->prepare("SELECT id, ten_su_kien, hinh_anh, gia_thap_nhat FROM sukien WHERE id = :eid LIMIT 1");
        $stmtFeat->execute(['eid' => (int)$currentStar['su_kien_noi_bat_id']]);
        $featuredEvent = $stmtFeat->fetch(PDO::FETCH_ASSOC);
    }

    // 2. Kiểm tra bảng liên kết nhiều-nhiều ticketstar_sukien
    $hasLinkTable = false;
    try {
        $pdo->query("SELECT 1 FROM ticketstar_sukien LIMIT 1");
        $hasLinkTable = true;
    } catch (Exception $e) {
        $hasLinkTable = false;
    }

    // 3. Lấy toàn bộ Lịch hoạt động (ưu tiên bảng ticketstar_sukien + từ khóa)
    $conds  = [];
    $params = [];

    if ($hasLinkTable) {
        $conds[]        = "s.id IN (SELECT su_kien_id FROM ticketstar_sukien WHERE star_id = :sid)";
        $params[':sid'] = (int)$currentStar['id'];
    }

    $keywords = array_filter(array_map('trim', explode('|', (string)$currentStar['tu_khoa_su_kien'])));
    foreach ($keywords as $i => $kw) {
        $conds[]            = "(s.ten_su_kien LIKE :k1_{$i} OR s.ten_ban_to_chuc LIKE :k2_{$i})";
        $params[":k1_{$i}"] = '%' . $kw . '%';
        $params[":k2_{$i}"] = '%' . $kw . '%';
    }

    if (!empty($conds)) {
        $sql = "SELECT DISTINCT s.*,
                       (SELECT COUNT(*) FROM loaive lv WHERE lv.su_kien_id = s.id AND lv.da_het_ve = 0) AS ve_con_ban
                FROM sukien s
                WHERE " . implode(' OR ', $conds) . "
                ORDER BY s.thoi_gian DESC, s.id DESC";
        $stmtEvt = $pdo->prepare($sql);
        $stmtEvt->execute($params);
        $starEvents = $stmtEvt->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif ($slug === '') {
    $stmtAll = $pdo->query("SELECT * FROM ticketstars ORDER BY thu_tu ASC, id ASC");
    $allStars = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
}

include '../templates/header.php';
?>

<?php if ($currentStar): ?>
    <?php
    $roleText = $roleLabels[$currentStar['loai']] ?? 'Ban tổ chức';
    $baseUrl  = 'organizer.php?slug=' . urlencode($currentStar['slug']);
    ?>

    <!-- Thanh Sub-navigation của Star -->
    <div class="star-subnav">
        <div class="star-subnav-inner">
            <a href="<?php echo $baseUrl; ?>" class="star-subnav-brand">
                <span><?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?></span>
            </a>

            <nav class="star-subnav-tabs">
                <a href="<?php echo $baseUrl; ?>&tab=all" class="star-subnav-tab <?php echo $tab === 'all' ? 'active' : ''; ?>">Tất cả</a>
                <a href="<?php echo $baseUrl; ?>&tab=events" class="star-subnav-tab <?php echo $tab === 'events' ? 'active' : ''; ?>">Sự kiện</a>
                <a href="<?php echo $baseUrl; ?>&tab=news" class="star-subnav-tab <?php echo $tab === 'news' ? 'active' : ''; ?>">Tin tức</a>
                <a href="<?php echo $baseUrl; ?>&tab=community" class="star-subnav-tab <?php echo $tab === 'community' ? 'active' : ''; ?>">Cộng đồng</a>
                <a href="<?php echo $baseUrl; ?>&tab=about" class="star-subnav-tab <?php echo $tab === 'about' ? 'active' : ''; ?>">
                    Về <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?>
                </a>
            </nav>
        </div>
    </div>

    <main class="star-page-container">
        <div class="star-layout-grid">
            <!-- CỘT TRÁI: Thông tin Star + Sự kiện nổi bật -->
            <aside class="star-sidebar">
                <div class="star-box-card">
                    <div class="star-profile-header">
                        <img src="<?php echo htmlspecialchars($currentStar['avatar']); ?>"
                             alt="<?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?>"
                             class="star-profile-avatar"
                             onerror="this.src='https://via.placeholder.com/150/18191d/26c973?text=Star'">
                        <div class="star-profile-info">
                            <h1>
                                <span><?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?></span>
                                <?php if (!empty($currentStar['da_xac_minh'])): ?>
                                    <i class="fa-solid fa-circle-check star-verified-icon" title="Đã xác minh"></i>
                                <?php endif; ?>
                            </h1>
                            <span class="star-role-badge"><?php echo htmlspecialchars($roleText); ?></span>
                        </div>
                    </div>

                    <form method="POST" action="<?php echo $baseUrl; ?>">
                        <input type="hidden" name="toggle_follow_slug" value="<?php echo htmlspecialchars($currentStar['slug']); ?>">
                        <button type="submit" class="star-follow-btn <?php echo $isFollowing ? 'following' : ''; ?>">
                            <?php if ($isFollowing): ?>
                                <i class="fa-solid fa-check"></i> Đang theo dõi
                            <?php else: ?>
                                <i class="fa-solid fa-user-plus"></i> Theo dõi
                            <?php endif; ?>
                        </button>
                    </form>

                    <div class="star-follow-meta">
                        <strong><?php echo number_format((int)$currentStar['luot_theo_doi'], 0, ',', '.'); ?></strong> người theo dõi
                    </div>
                </div>

                <!-- Khối Sự kiện nổi bật (chỉ hiển thị nếu có) -->
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
                        <a href="event-detail.php?id=<?php echo (int)$featuredEvent['id']; ?>" class="star-featured-name">
                            <?php echo htmlspecialchars($featuredEvent['ten_su_kien']); ?>
                        </a>
                        <div class="star-featured-price">
                            Từ <?php echo number_format((int)$featuredEvent['gia_thap_nhat'], 0, ',', '.'); ?> đ
                        </div>
                    </div>
                <?php endif; ?>
            </aside>

            <!-- CỘT PHẢI: Nội dung theo Tab -->
            <section>
                <?php if ($tab === 'about'): ?>
                    <div class="star-box-card">
                        <h3 style="margin: 0 0 12px; color: #fff;">Giới thiệu về <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?></h3>
                        <p style="color: #d1d5db; line-height: 1.7; margin-bottom: 16px;">
                            <?php echo nl2br(htmlspecialchars($currentStar['mo_ta'] ?: 'Đang cập nhật thông tin giới thiệu.')); ?>
                        </p>
                        <div style="color: #9ca0ab; font-size: 13.5px;">
                            <div>• Phân loại: <strong style="color: #fff;"><?php echo htmlspecialchars($roleText); ?></strong></div>
                            <div style="margin-top: 6px;">• Lượt theo dõi: <strong style="color: #26c973;"><?php echo number_format((int)$currentStar['luot_theo_doi'], 0, ',', '.'); ?></strong></div>
                        </div>
                    </div>

                <?php elseif ($tab === 'news'): ?>
                    <div class="star-box-card">
                        <h3 style="margin-top: 0; color: #fff;">Tin tức & Thông báo</h3>
                        <?php if (!empty($currentStar['thong_bao'])): ?>
                            <div class="star-notice-banner" style="margin-bottom: 0;">
                                <i class="fa-solid fa-bullhorn"></i>
                                <div><?php echo nl2br(htmlspecialchars($currentStar['thong_bao'])); ?></div>
                            </div>
                        <?php else: ?>
                            <p style="color: #9ca0ab; margin: 0;">Chưa có tin tức mới từ <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?>.</p>
                        <?php endif; ?>
                    </div>

                <?php elseif ($tab === 'community'): ?>
                    <div class="star-box-card">
                        <h3 style="margin-top: 0; color: #fff;">Cộng đồng người hâm mộ</h3>
                        <p style="color: #d1d5db; line-height: 1.6;">
                            Hiện có <strong style="color: #26c973;"><?php echo number_format((int)$currentStar['luot_theo_doi'], 0, ',', '.'); ?></strong> thành viên đang theo dõi <strong><?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?></strong> trên Ticketbox.
                        </p>
                        <?php if (!empty($currentStar['nghe_si_membership'])): ?>
                            <div class="star-notice-banner" style="margin-bottom: 0;">
                                <i class="fa-solid fa-crown"></i>
                                <div>Gói hội viên độc quyền <strong><?php echo htmlspecialchars($currentStar['nghe_si_membership']); ?> Membership</strong> đang hoạt động với đặc quyền đặt vé sớm Presale.</div>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- Tab 'all' hoặc 'events' -->
                    <?php if ($tab === 'all' && !empty($currentStar['thong_bao'])): ?>
                        <div class="star-notice-banner">
                            <i class="fa-solid fa-bullhorn"></i>
                            <div><?php echo nl2br(htmlspecialchars($currentStar['thong_bao'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($starEvents)): ?>
                        <div class="star-box-card" style="text-align: center; padding: 50px 20px; color: #9ca0ab;">
                            <i class="fa-regular fa-calendar-xmark" style="font-size: 36px; margin-bottom: 12px; display: block;"></i>
                            Chưa có sự kiện nào được cập nhật cho <?php echo htmlspecialchars($currentStar['ten_hien_thi']); ?>.
                        </div>
                    <?php else: ?>
                        <div class="star-timeline">
                            <?php foreach ($starEvents as $ev): ?>
                                <?php
                                $eventTs       = strtotime($ev['thoi_gian']);
                                $isMerchandise = (stripos($ev['ten_su_kien'], 'MERCHANDISE') !== false);
                                $isEnded       = ($eventTs < time()) && !$isMerchandise;
                                $veConBan      = (int)($ev['ve_con_ban'] ?? 0);
                                $formattedDate = date('d', $eventTs) . ' Tháng ' . date('m, Y', $eventTs);
                                ?>
                                <div class="star-timeline-item">
                                    <span class="star-timeline-dot"></span>
                                    <div class="star-timeline-date">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span><?php echo htmlspecialchars($formattedDate); ?></span>
                                    </div>

                                    <div class="star-event-card">
                                        <a href="event-detail.php?id=<?php echo (int)$ev['id']; ?>" class="star-event-thumb">
                                            <img src="<?php echo htmlspecialchars($ev['hinh_anh']); ?>"
                                                 alt="<?php echo htmlspecialchars($ev['ten_su_kien']); ?>"
                                                 onerror="this.src='https://via.placeholder.com/400x225/18191d/26c973?text=Ticketbox'">
                                            <?php if ($isEnded): ?>
                                                <span class="star-badge-ended">Đã diễn ra</span>
                                            <?php endif; ?>
                                        </a>

                                        <div class="star-event-body">
                                            <a href="event-detail.php?id=<?php echo (int)$ev['id']; ?>" class="star-event-title">
                                                <?php echo htmlspecialchars($ev['ten_su_kien']); ?>
                                            </a>
                                            <div class="star-event-price">
                                                Từ <?php echo number_format((int)$ev['gia_thap_nhat'], 0, ',', '.'); ?> đ
                                            </div>

                                            <?php if (!$isEnded): ?>
                                                <?php if ($veConBan > 0): ?>
                                                    <a href="event-detail.php?id=<?php echo (int)$ev['id']; ?>" class="star-btn-buy">
                                                        Mua vé ngay
                                                    </a>
                                                <?php else: ?>
                                                    <span class="star-btn-soldout">Hết vé</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>

<?php elseif ($slug === ''): ?>
    <!-- Trang danh sách tất cả TicketStars nếu không truyền ?slug= -->
    <main class="star-page-container">
        <h2 style="color: #fff; margin: 0 0 22px; font-size: 22px;">
            <i class="fa-solid fa-star" style="color: #26c973;"></i> Khám phá TicketStars
        </h2>
        <div class="stars-directory-grid">
            <?php foreach ($allStars as $st): ?>
                <a href="organizer.php?slug=<?php echo urlencode($st['slug']); ?>" class="star-dir-card">
                    <img src="<?php echo htmlspecialchars($st['avatar']); ?>"
                         alt="<?php echo htmlspecialchars($st['ten_hien_thi']); ?>"
                         onerror="this.src='https://via.placeholder.com/150/18191d/26c973?text=Star'">
                    <div style="font-weight: 700; font-size: 16px; margin-bottom: 6px;">
                        <?php echo htmlspecialchars($st['ten_hien_thi']); ?>
                        <?php if (!empty($st['da_xac_minh'])): ?>
                            <i class="fa-solid fa-circle-check" style="color: #26c973; font-size: 14px;"></i>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 12.5px; color: #9ca0ab; margin-bottom: 8px;">
                        <?php echo htmlspecialchars($roleLabels[$st['loai']] ?? 'Ban tổ chức'); ?>
                    </div>
                    <div style="font-size: 13px; color: #26c973; font-weight: 600;">
                        <?php echo number_format((int)$st['luot_theo_doi'], 0, ',', '.'); ?> người theo dõi
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

<?php else: ?>
    <!-- Trường hợp slug không tồn tại -->
    <main class="star-page-container" style="text-align: center; padding: 70px 20px; color: #9ca0ab;">
        <i class="fa-solid fa-user-slash" style="font-size: 46px; margin-bottom: 14px; display: block;"></i>
        <p style="font-size: 16px; margin-bottom: 16px;">Không tìm thấy nghệ sĩ hoặc ban tổ chức này.</p>
        <a href="organizer.php" class="star-btn-buy">Xem tất cả TicketStars</a>
    </main>
<?php endif; ?>

<?php include '../templates/footer.php'; ?>