<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
include '../templates/header.php';

$todayStart = date('Y-m-d 00:00:00');
?>

<!-- Hero Banner Slider-->
<div class="container hero-slider-wrapper">
    <button class="slider-btn banner-btn prev-btn" id="bannerLeftBtn" aria-label="Banner trái">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <section class="hero-slider" id="heroSlider">
        <?php
        $stmtHero = $pdo->prepare("
            SELECT 
                s.*,
                COUNT(v.id) AS luot_ban,
                CASE 
                    WHEN EXISTS (SELECT 1 FROM ticketstar_sukien tss WHERE tss.su_kien_id = s.id)
                      OR EXISTS (SELECT 1 FROM ticketstars ts WHERE ts.su_kien_noi_bat_id = s.id)
                    THEN 1 
                    ELSE 0 
                END AS is_star
            FROM sukien s
            LEFT JOIN ve v 
                ON s.id = v.su_kien_id 
                AND v.trang_thai = 'thanh-cong'
            WHERE s.trang_thai = 'cong_khai'
              AND s.quyen_rieng_tu = 'public'
              AND (
                  s.thoi_gian >= :today_start_1 
                  OR (s.thoi_gian_ket_thuc IS NOT NULL AND s.thoi_gian_ket_thuc >= :today_start_2)
              )
            GROUP BY s.id
            ORDER BY 
                luot_ban DESC,
                is_star DESC,
                s.ngay_tao DESC,
                s.id DESC
            LIMIT 4
        ");
        $stmtHero->execute([
            'today_start_1' => $todayStart,
            'today_start_2' => $todayStart
        ]);
        $heroBanners = $stmtHero->fetchAll(PDO::FETCH_ASSOC);

        foreach ($heroBanners as $banner):
            $bannerImg = !empty($banner['banner']) ? $banner['banner'] : $banner['hinh_anh'];
            $eventTitle = htmlspecialchars($banner['ten_su_kien'], ENT_QUOTES, 'UTF-8');
            $fallbackText = urlencode(mb_substr($banner['ten_su_kien'], 0, 20, 'UTF-8'));
        ?>
            <div class="banner-item">
                <a href="event-detail.php?id=<?php echo (int)$banner['id']; ?>">
                    <img src="<?php echo htmlspecialchars($bannerImg, ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?php echo $eventTitle; ?>"
                         onerror="this.src='https://via.placeholder.com/600x260/00b14f/fff?text=<?php echo $fallbackText; ?>';">
                </a>
                <a href="event-detail.php?id=<?php echo (int)$banner['id']; ?>" class="btn-detail">Xem chi tiết</a>
            </div>
        <?php endforeach; ?>
    </section>
    <!-- Nút cuộn banner sang phải -->
    <button class="slider-btn banner-btn next-btn" id="bannerRightBtn" aria-label="Banner phải">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
</div>

<!--Sự kiện nổi bật-->
<main class="container" style="margin-top: 35px;">
    <div class="section-header-row">
        <h2 class="section-title" style="margin: 0;">
            <i class="fa-solid fa-fire" style="color: #ff4d4f;"></i> Sự kiện nổi bật
        </h2>
        <a href="events.php" class="view-all-link">
            Xem tất cả <i class="fa-solid fa-chevron-right"></i>
        </a>
    </div>

    <div class="slider-wrapper">
        <button type="button" class="slider-btn prev-btn" id="slideLeftBtn" aria-label="Cuộn trái">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="event-slider" id="eventSlider">
            <?php
            $stmtFeatured = $pdo->prepare("
                SELECT 
                    s.*,
                    COUNT(v.id) AS luot_ban,
                    CASE 
                        WHEN EXISTS (SELECT 1 FROM ticketstar_sukien tss WHERE tss.su_kien_id = s.id)
                          OR EXISTS (SELECT 1 FROM ticketstars ts WHERE ts.su_kien_noi_bat_id = s.id)
                        THEN 1 
                        ELSE 0 
                    END AS is_star
                FROM sukien s
                LEFT JOIN ve v 
                    ON s.id = v.su_kien_id 
                    AND v.trang_thai = 'thanh-cong'
                WHERE s.trang_thai = 'cong_khai'
                  AND s.quyen_rieng_tu = 'public'
                  AND (
                      s.thoi_gian >= :today_start_1 
                      OR (s.thoi_gian_ket_thuc IS NOT NULL AND s.thoi_gian_ket_thuc >= :today_start_2)
                  )
                GROUP BY s.id
                ORDER BY 
                    luot_ban DESC,
                    is_star DESC,
                    s.ngay_tao DESC,
                    s.id DESC
                LIMIT 10
            ");
            $stmtFeatured->execute([
                'today_start_1' => $todayStart,
                'today_start_2' => $todayStart
            ]);
            $dbEvents = $stmtFeatured->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbEvents as $row) {
                $city = $row['dia_diem'] ?? '';
                if ($city !== '' && strpos($city, ',') !== false) {
                    $city = trim(substr(strrchr($city, ','), 1));
                }

                $event = [
                    'id'       => $row['id'],
                    'title'    => $row['ten_su_kien'],
                    'price'    => 'Từ ' . number_format((int)$row['gia_thap_nhat'], 0, ',', '.') . ' đ',
                    'date'     => date('d.m.Y', strtotime($row['thoi_gian'])),
                    'location' => $city,
                    'image'    => $row['hinh_anh']
                ];

                include '../templates/event-card.php';
            }
            ?>
        </div>

        <button type="button" class="slider-btn next-btn" id="slideRightBtn" aria-label="Cuộn phải">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</main>

<!-- FEATURED STARS -->
<div class="featured-stars-wrapper-bg">
    <section class="container">
        <div class="stars-header-row">
            <a href="organizer.php" class="stars-title-link">
                <i class="fa-solid fa-star star-icon"></i>
                <span>Featured Stars</span>
                <i class="fa-solid fa-chevron-right title-arrow"></i>
            </a>
        </div>

        <div class="stars-slider-wrapper">
            <button type="button" class="star-nav-btn star-nav-prev" id="starPrevBtn" aria-label="Cuộn trái">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="stars-track" id="starsTrack">
                <?php
                $starsList = [];
                if (isset($pdo)) {
                    $stmtStars = $pdo->query("SELECT * FROM ticketstars ORDER BY thu_tu ASC, id ASC");
                    $starsList = $stmtStars->fetchAll(PDO::FETCH_ASSOC);
                }
                foreach ($starsList as $star):
                    $initials = mb_strtoupper(mb_substr($star['ten_hien_thi'], 0, 2, 'UTF-8'), 'UTF-8');
                ?>
                    <a href="organizer.php?slug=<?php echo urlencode($star['slug']); ?>" class="star-card" title="<?php echo htmlspecialchars($star['ten_hien_thi']); ?>">
                        <div class="avatar-ring">
                            <img src="<?php echo htmlspecialchars($star['avatar']); ?>"
                                alt="<?php echo htmlspecialchars($star['ten_hien_thi']); ?>"
                                class="star-avatar"
                                onerror="this.src='https://via.placeholder.com/150/16181d/26c973?text=<?php echo urlencode($initials); ?>'">
                        </div>
                        <div class="star-info">
                            <span class="star-name"><?php echo htmlspecialchars($star['ten_hien_thi']); ?></span>
                            <i class="fa-solid fa-circle-check verified-badge"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <button type="button" class="star-nav-btn star-nav-next" id="starNextBtn" aria-label="Cuộn phải">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </section>
</div>

<!--Thẻ thành viên -->
<?php
$stmtMem = $pdo->prepare("SELECT * FROM the_thanh_vien ORDER BY id ASC");
$stmtMem->execute();
$memberships = $stmtMem->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="container membership-section" style="margin-top: 35px; margin-bottom: 45px;">
    <div class="membership-header">
        <i class="fa-solid fa-credit-card"></i>
        <h2>Thẻ thành viên</h2>
    </div>

    <div class="membership-slider-wrap">
        <button type="button" class="mem-nav-btn mem-nav-prev" id="memPrevBtn" aria-label="Cuộn trái">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="membership-track" id="membershipTrack">
            <?php foreach ($memberships as $mem): ?>
                <div class="membership-card">
                    <a href="membership-detail.php?id=<?php echo (int)$mem['id']; ?>" class="membership-thumb">
                        <img src="<?php echo htmlspecialchars($mem['hinh_anh']); ?>"
                            alt="<?php echo htmlspecialchars($mem['ten_the']); ?>"
                            onerror="this.src='https://via.placeholder.com/480x260/18191d/26c973?text=<?php echo urlencode($mem['ten_the']); ?>'">
                    </a>
                    <div class="membership-body">
                        <a href="membership-detail.php?id=<?php echo (int)$mem['id']; ?>" class="membership-title">
                            <?php echo htmlspecialchars($mem['ten_the']); ?>
                        </a>
                        <div class="membership-meta">
                            <span class="membership-price"><?php echo number_format((int)$mem['gia'], 0, ',', '.'); ?> đ</span>
                            <span class="membership-duration"><?php echo (int)$mem['thoi_han_thang']; ?> tháng</span>
                        </div>

                        <?php if ($mem['trang_thai'] === 'dang-ban'): ?>
                            <a href="membership-detail.php?id=<?php echo (int)$mem['id']; ?>" class="btn-mem-join">Tham gia ngay</a>
                        <?php elseif ($mem['trang_thai'] === 'da-ban-het'): ?>
                            <a href="membership-detail.php?id=<?php echo (int)$mem['id']; ?>" class="btn-mem-disabled">Đã bán hết</a>
                        <?php else: ?>
                            <a href="membership-detail.php?id=<?php echo (int)$mem['id']; ?>" class="btn-mem-disabled">Đã đóng bán</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="mem-nav-btn mem-nav-next" id="memNextBtn" aria-label="Cuộn phải">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</section>

<!-- CÁC MỤC SỰ KIỆN THEO DANH MỤC -->
<?php
$homeCategorySections = [
    [
        'slug'  => 'nhac-song',
        'title' => 'Nhạc sống',
        'icon'  => 'fa-solid fa-microphone',
        'color' => '#26c973',
        'where' => 'danh_muc_id = 1'
    ],
    [
        'slug'  => 'san-khau-nghe-thuat',
        'title' => 'Sân khấu & Nghệ thuật',
        'icon'  => 'fa-solid fa-masks-theater',
        'color' => '#fadb14',
        'where' => 'danh_muc_id = 4'
    ],
    [
        'slug'  => 'the-thao',
        'title' => 'Thể thao',
        'icon'  => 'fa-solid fa-trophy',
        'color' => '#36cfc9',
        'where' => 'danh_muc_id = 2 AND id NOT IN (35, 36, 39, 41)'
    ],
    [
        'slug'  => 'hoi-thao-workshop',
        'title' => 'Hội thảo & Workshop',
        'icon'  => 'fa-solid fa-palette',
        'color' => '#ff85c0',
        'where' => 'danh_muc_id = 3'
    ],
    [
        'slug'  => 'tham-quan-trai-nghiem',
        'title' => 'Tham quan & Trải nghiệm',
        'icon'  => 'fa-solid fa-compass',
        'color' => '#69c0ff',
        'where' => "(
            danh_muc_id = 5
            OR id IN (11, 13, 34, 35, 36, 37, 38, 39, 40, 41)
            OR ten_su_kien LIKE '%Trải nghiệm%'
            OR ten_su_kien LIKE '%Du lịch%'
            OR ten_su_kien LIKE '%Bảo tàng%'
            OR ten_su_kien LIKE '%Triển lãm%'
            OR ten_su_kien LIKE '%Tour%'
            OR ten_su_kien LIKE '%Khu vui chơi%'
            OR ten_su_kien LIKE '%Xe Buýt%'
        )"
    ],
    [
        'slug'  => 'khac',
        'title' => 'Khác',
        'icon'  => 'fa-solid fa-layer-group',
        'color' => '#b37feb',
        'where' => "NOT (
            COALESCE(danh_muc_id, 0) IN (1, 3, 4, 5)
            OR (COALESCE(danh_muc_id, 0) = 2 AND id NOT IN (35, 36, 39, 41))
            OR id IN (11, 13, 34, 35, 36, 37, 38, 39, 40, 41)
            OR COALESCE(ten_su_kien, '') LIKE '%Trải nghiệm%'
            OR COALESCE(ten_su_kien, '') LIKE '%Du lịch%'
            OR COALESCE(ten_su_kien, '') LIKE '%Bảo tàng%'
            OR COALESCE(ten_su_kien, '') LIKE '%Triển lãm%'
            OR COALESCE(ten_su_kien, '') LIKE '%Tour%'
            OR COALESCE(ten_su_kien, '') LIKE '%Khu vui chơi%'
            OR COALESCE(ten_su_kien, '') LIKE '%Xe Buýt%'
        )"
    ]
];

foreach ($homeCategorySections as $catSec):
    $stmtCatEvents = $pdo->prepare("SELECT * FROM sukien WHERE ({$catSec['where']}) AND thoi_gian >= :today_start ORDER BY thoi_gian ASC, id ASC LIMIT 8");
    $stmtCatEvents->execute(['today_start' => $todayStart]);
    $catEvents = $stmtCatEvents->fetchAll(PDO::FETCH_ASSOC);

    if (empty($catEvents)) continue;
    $sliderDomId = 'catSlider_' . str_replace('-', '_', $catSec['slug']);
?>
    <section class="container" style="margin-top: 38px; margin-bottom: 15px;">
        <div class="section-header-row">
            <h2 class="section-title" style="margin: 0;">
                <a href="events.php?cat=<?php echo urlencode($catSec['slug']); ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 10px;">
                    <i class="<?php echo htmlspecialchars($catSec['icon']); ?>" style="color: <?php echo htmlspecialchars($catSec['color']); ?>;"></i>
                    <span><?php echo htmlspecialchars($catSec['title']); ?></span>
                </a>
            </h2>
            <a href="events.php?cat=<?php echo urlencode($catSec['slug']); ?>" class="view-all-link">
                Xem tất cả <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>

        <div class="slider-wrapper">
            <button type="button"
                class="slider-btn prev-btn"
                id="<?php echo $sliderDomId; ?>_prev"
                aria-label="Cuộn trái">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="event-slider cat-event-slider" id="<?php echo $sliderDomId; ?>">
                <?php
                foreach ($catEvents as $row) {
                    $city = $row['dia_diem'];
                    if (strpos($row['dia_diem'], ',') !== false) {
                        $city = trim(substr(strrchr($row['dia_diem'], ','), 1));
                    }

                    $event = [
                        'id'       => $row['id'],
                        'title'    => $row['ten_su_kien'],
                        'price'    => 'Từ ' . number_format((int)$row['gia_thap_nhat'], 0, ',', '.') . ' đ',
                        'date'     => date('d.m.Y', strtotime($row['thoi_gian'])),
                        'location' => $city,
                        'image'    => $row['hinh_anh']
                    ];

                    include '../templates/event-card.php';
                }
                ?>
            </div>

            <button type="button"
                class="slider-btn next-btn"
                id="<?php echo $sliderDomId; ?>_next"
                aria-label="Cuộn phải">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </section>
<?php endforeach; ?>

<?php include '../templates/footer.php'; ?>