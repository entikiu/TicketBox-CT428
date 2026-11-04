<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
include '../templates/header.php';
?>

<!-- Hero Banner Slider (Dùng Nút Bấm Điều Hướng) -->
<div class="container hero-slider-wrapper">
    <!-- Nút cuộn banner sang trái -->
    <button class="slider-btn banner-btn prev-btn" id="bannerLeftBtn" aria-label="Banner trái">
        <i class="fa-solid fa-chevron-left"></i>
    </button>

    <section class="hero-slider" id="heroSlider">
        <div class="banner-item">
            <a href="event-detail.php?id=1">
                <img src="../assets/images/events/banner-skynote.jpg" alt="SKYNOTE Live Concert" onerror="this.src='https://via.placeholder.com/600x260/00b14f/fff?text=SKYNOTE';">
            </a>
            <a href="event-detail.php?id=1" class="btn-detail">Xem chi tiết</a>
        </div>

        <div class="banner-item">
            <a href="event-detail.php?id=2">
                <img src="../assets/images/events/banner-ntpmm.jpg" alt="Những Thành Phố Mộng Mơ" onerror="this.src='https://via.placeholder.com/600x260/222/fff?text=NTPMM';">
            </a>
            <a href="event-detail.php?id=2" class="btn-detail">Xem chi tiết</a>
        </div>

        <div class="banner-item">
            <a href="event-detail.php?id=3">
                <img src="../assets/images/events/banner-mnmm.jpg" alt="Mắt Nhắm Mắt Mở" onerror="this.src='https://via.placeholder.com/600x260/333/fff?text=Mắt+Nhắm+Mắt+Mở';">
            </a>
            <a href="event-detail.php?id=3" class="btn-detail">Xem chi tiết</a>
        </div>
    </section>

    <!-- Nút cuộn banner sang phải -->
    <button class="slider-btn banner-btn next-btn" id="bannerRightBtn" aria-label="Banner phải">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
</div>

<!-- Section: Sự kiện nổi bật -->
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
            $mock_events = [
                ['id' => 1, 'title' => 'Live Concert SKYNOTE - Quốc Thiên', 'price' => 'Từ 880.000 đ', 'date' => '29.11.2026', 'location' => 'Hà Nội', 'image' => '../assets/images/events/banner-skynote.jpg'],
                ['id' => 2, 'title' => 'Những Thành Phố Mộng Mơ Year End', 'price' => 'Từ 499.000 đ', 'date' => '12.12.2026', 'location' => 'TP. Hồ Chí Minh', 'image' => '../assets/images/events/banner-ntpmm.jpg'],
                ['id' => 3, 'title' => 'Mắt Nhắm Mắt Mở', 'price' => 'Từ 1.600.000 đ', 'date' => '01.11.2026', 'location' => 'Hà Nội', 'image' => '../assets/images/events/banner-mnmm.jpg'],
                ['id' => 4, 'title' => 'Tinh Hà "Say Hi" Concert - Đêm 2', 'price' => 'Từ 800.000 đ', 'date' => '18.10.2026', 'location' => 'Hà Nội', 'image' => '../assets/images/events/banner-tinh-ha.jpg'],
                ['id' => 5, 'title' => 'The Starry - S\'NOW', 'price' => 'Từ 800.000 đ', 'date' => '19.12.2026', 'location' => 'Hồ Chí Minh', 'image' => '../assets/images/events/banner-snow.jpg'],
                ['id' => 6, 'title' => 'GAI MEET - NHÀ RỰC RỠ', 'price' => 'Từ 1.500.000 đ', 'date' => '18.10.2026', 'location' => 'Hồ Chí Minh', 'image' => '../assets/images/events/banner-nha-ruc-ro.jpg'],
                ['id' => 7, 'title' => '(S)TRONG \'DỊ KIỀU\' SHOW 2026: ĐẾN GIÂY CUỐI CÙNG', 'price' => 'Từ 1.450.000 đ', 'date' => '07.11.2026', 'location' => 'Hà Nội', 'image' => '../assets/images/events/banner-strong.jpg'],
                ['id' => 8, 'title' => 'GAI MEET - DỰ ÁN NHÂN TỐ X', 'price' => 'Từ 1.500.000 đ', 'date' => '01.11.2026', 'location' => 'Hồ Chí Minh', 'image' => '../assets/images/events/banner-nhan-to-x.jpg'],
                ['id' => 9, 'title' => '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 1 & DAY 2', 'price' => 'Từ 1.000.000 đ', 'date' => '18.10.2026', 'location' => 'Hồ Chí Minh', 'image' => '../assets/images/events/banner-anh-trai.jpg'],
                ['id' => 10, 'title' => 'HIỆN DIỆN - UPRIZE CONCERT', 'price' => 'Từ 800.000 đ', 'date' => '12.12.2026', 'location' => 'Hà Nội', 'image' => '../assets/images/events/banner-uprize.jpg']
            ];

            foreach ($mock_events as $event) {
                include '../templates/event-card.php';
            }
            ?>
        </div>

        <button type="button" class="slider-btn next-btn" id="slideRightBtn" aria-label="Cuộn phải">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</main>


<!-- FEATURED STARS / BAN TỔ CHỨC NỔI BẬT (CHUẨN GIAO DIỆN TICKETBOX) -->
<section class="container featured-stars-section" style="margin-top: 35px;">
    <div class="section-title-wrapper">
        <h2 class="section-title">
            <i class="fa-solid fa-star star-icon"></i>
            Featured Stars
            <i class="fa-solid fa-chevron-right title-arrow"></i>
        </h2>
    </div>

    <div class="stars-slider-wrapper">
        <button type="button" class="slider-btn prev-btn" id="starPrevBtn" aria-label="Previous">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="stars-track" id="starsTrack">
            <?php
            // 1. Lấy danh sách Ban tổ chức từ bảng `nguoidung`
            $stmtOrg = $pdo->prepare("SELECT id, ho_ten FROM nguoidung WHERE vai_tro IN ('Organizer', 'Admin') ORDER BY id DESC");
            $stmtOrg->execute();
            $organizers = $stmtOrg->fetchAll(PDO::FETCH_ASSOC);

            // 2. Danh sách ảnh demo fallback cho các Ban tổ chức/Nghệ sĩ
            $sampleAvatars = [
                'YeahH1 Family' => '../assets/images/stars/yeah1.jpg',
                'Vie Concert'   => '../assets/images/stars/vieconcert.jpg',
                'SS Label'      => '../assets/images/stars/sslabel.jpg',
                'GAI MEET'      => '../assets/images/stars/gaimeet.jpg',
                'Quốc Thiên'    => '../assets/images/stars/quocthien.jpg',
                'Kịch IDECAF'   => '../assets/images/stars/idecaf.jpg',
            ];

            // Nếu trong CSDL chưa có dữ liệu Organizer, hiển thị danh sách mặc định để test giao diện
            if (empty($organizers)) {
                $organizers = [
                    ['id' => 1, 'ho_ten' => 'YeahH1 Family'],
                    ['id' => 2, 'ho_ten' => 'Vie Concert'],
                    ['id' => 3, 'ho_ten' => 'SS Label'],
                    ['id' => 4, 'ho_ten' => 'GAI MEET'],
                    ['id' => 5, 'ho_ten' => 'Quốc Thiên'],
                    ['id' => 6, 'ho_ten' => 'Đình Mạnh...'],
                    ['id' => 7, 'ho_ten' => 'Phùng Khá...'],
                    ['id' => 8, 'ho_ten' => 'Những Thà...'],
                    ['id' => 9, 'ho_ten' => 'Jun Phạm'],
                    ['id' => 10, 'ho_ten' => 'Subicha'],
                    ['id' => 11, 'ho_ten' => 'Hoàng Dũng'],
                    ['id' => 12, 'ho_ten' => 'Cheng'],
                    ['id' => 13, 'ho_ten' => 'Mr. Siro']
                ];
            }

            foreach ($organizers as $org):
                $avatarSrc = $sampleAvatars[$org['ho_ten']] ?? '../assets/images/default-avatar.png';
            ?>
                <a href="organizer.php?id=<?php echo $org['id']; ?>" class="star-card">
                    <div class="avatar-ring">
                        <img src="<?php echo $avatarSrc; ?>" alt="<?php echo htmlspecialchars($org['ho_ten']); ?>" class="star-avatar" onerror="this.src='https://via.placeholder.com/90/000/26c973?text=<?php echo urlencode(substr($org['ho_ten'], 0, 2)); ?>'">
                    </div>
                    <div class="star-info">
                        <span class="star-name"><?php echo htmlspecialchars($org['ho_ten']); ?></span>
                        <i class="fa-solid fa-circle-check verified-badge"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <button type="button" class="slider-btn next-btn" id="starNextBtn" aria-label="Next">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</section>


<!-- OFFICIAL MEMBERSHIP (THẺ THÀNH VIÊN) -->
<section class="container" style="margin-top: 40px;">
    <h2 class="section-title"><i class="fa-solid fa-id-card" style="color: var(--primary-color, #26c973);"></i> Official Membership</h2>
    <div class="membership-grid">
        <div class="membership-card">
            <h3>THẺ CƯ DÂN</h3>
            <p class="membership-price">0 đ / 6 tháng</p>
            <span class="badge-status status-sold">Đã bán hết</span>
        </div>
        <div class="membership-card highlight">
            <h3>SWAN PASS</h3>
            <p class="membership-price">324.000 đ / 6 tháng</p>
            <button class="btn-join">Tham gia ngay</button>
        </div>
        <div class="membership-card">
            <h3>SKYNOTE Membership</h3>
            <p class="membership-price">99.000 đ / 6 tháng</p>
            <span class="badge-status">Đã đóng bán</span>
        </div>
    </div>
</section>



<?php include '../templates/footer.php'; ?>