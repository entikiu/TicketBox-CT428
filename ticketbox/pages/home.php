<?php include '../templates/header.php'; ?>

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

<!-- Section: Sự kiện nổi bật (Cuộn bằng nút bấm, không xếp Rank) -->
<main class="container" style="margin-top: 35px;">
    <!-- Khung tiêu đề độc lập kết hợp nút Xem tất cả -->
    <div class="section-header-row">
        <h2 class="section-title" style="margin: 0;">
            <i class="fa-solid fa-fire" style="color: #ff4d4f;"></i> Sự kiện nổi bật
        </h2>
        <a href="events.php" class="view-all-link">
            Xem tất cả <i class="fa-solid fa-chevron-right"></i>
        </a>
    </div>

    <!-- Khung chứa Slider và Nút điều hướng -->
    <div class="slider-wrapper">
        <!-- Nút cuộn sang trái -->
        <button type="button" class="slider-btn prev-btn" id="slideLeftBtn" aria-label="Cuộn trái">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <!-- Danh sách sự kiện cuộn ngang -->
        <div class="event-slider" id="eventSlider">
            <?php
            $mock_events = [
                [
                    'id' => 1,
                    'title' => 'Live Concert SKYNOTE - Quốc Thiên',
                    'price' => 'Từ 880.000 đ',
                    'date' => '29.11.2026',
                    'location' => 'Hà Nội',
                    'image' => '../assets/images/events/banner-skynote.jpg'
                ],
                [
                    'id' => 2,
                    'title' => 'Những Thành Phố Mộng Mơ Year End',
                    'price' => 'Từ 499.000 đ',
                    'date' => '12.12.2026',
                    'location' => 'TP. Hồ Chí Minh',
                    'image' => '../assets/images/events/banner-ntpmm.jpg'
                ],
                [
                    'id' => 3,
                    'title' => 'Mắt Nhắm Mắt Mở',
                    'price' => 'Từ 1.600.000 đ',
                    'date' => '01.11.2026',
                    'location' => 'Hà Nội',
                    'image' => '../assets/images/events/banner-mnmm.jpg'
                ],
                [
                    'id' => 4,
                    'title' => 'Tinh Hà "Say Hi" Concert - Đêm 2',
                    'price' => 'Từ 800.000 đ',
                    'date' => '18.10.2026',
                    'location' => 'Hà Nội',
                    'image' => '../assets/images/events/banner-tinh-ha.jpg'
                ],
                [
                    'id' => 5,
                    'title' => 'The Starry - S\'NOW',
                    'price' => 'Từ 800.000 đ',
                    'date' => '19.12.2026',
                    'location' => 'Hồ Chí Minh',
                    'image' => '../assets/images/events/banner-snow.jpg'
                ],
                [
                    'id' => 6,
                    'title' => 'GAI MEET - NHÀ RỰC RỠ',
                    'price' => 'Từ 1.500.000 đ',
                    'date' => '18.10.2026',
                    'location' => 'Hồ Chí Minh',
                    'image' => '../assets/images/events/banner-nha-ruc-ro.jpg'
                ],
                [
                    'id' => 7,
                    'title' => '(S)TRONG \'DỊ KIỀU\' SHOW 2026: ĐẾN GIÂY CUỐI CÙNG',
                    'price' => 'Từ 1.450.000 đ',
                    'date' => '07.11.2026',
                    'location' => 'Hà Nội',
                    'image' => '../assets/images/events/banner-strong.jpg'
                ],
                [
                    'id' => 8,
                    'title' => 'GAI MEET - DỰ ÁN NHÂN TỐ X',
                    'price' => 'Từ 1.500.000 đ',
                    'date' => '01.11.2026',
                    'location' => 'Hồ Chí Minh',
                    'image' => '../assets/images/events/banner-nhan-to-x.jpg'
                ],
                [
                    'id' => 9,
                    'title' => '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 1 & DAY 2',
                    'price' => 'Từ 1.000.000 đ',
                    'date' => '18.10.2026',
                    'location' => 'Hồ Chí Minh',
                    'image' => '../assets/images/events/banner-anh-trai.jpg'
                ],
                [
                    'id' => 10,
                    'title' => 'HIỆN DIỆN - UPRIZE CONCERT',
                    'price' => 'Từ 800.000 đ',
                    'date' => '12.12.2026',
                    'location' => 'Hà Nội',
                    'image' => '../assets/images/events/banner-uprize.jpg'
                ]
            ];

            // Lặp và nhúng từng thẻ sự kiện
            foreach ($mock_events as $event) {
                include '../templates/event-card.php';
            }
            ?>
        </div>

        <!-- Nút cuộn sang phải -->
        <button type="button" class="slider-btn next-btn" id="slideRightBtn" aria-label="Cuộn phải">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</main>


<!--  FEATURED STARS / BAN TỔ CHỨC NỔI BẬT -->
<section class="container" style="margin-top: 35px;">
    <h2 class="section-title"><i class="fa-solid fa-star" style="color: #fadb14;"></i> Featured Stars</h2>
    <div class="stars-scroll">
        <div class="star-item">
            <div class="star-circle">YeaH1</div><span>YeaH1 Family</span>
        </div>
        <div class="star-item">
            <div class="star-circle" style="background: #2563eb;">Vie</div><span>Vie Concert</span>
        </div>
        <div class="star-item">
            <div class="star-circle" style="background: #059669;">SS</div><span>SS Label</span>
        </div>
        <div class="star-item">
            <div class="star-circle" style="background: #dc2626;">GAI</div><span>GAI MEET</span>
        </div>
        <div class="star-item">
            <div class="star-circle" style="background: #7c3aed;">QT</div><span>Quốc Thiên</span>
        </div>
        <div class="star-item">
            <div class="star-circle" style="background: #db2777;">IDECAF</div><span>Kịch IDECAF</span>
        </div>
    </div>
</section>


<!--  OFFICIAL MEMBERSHIP (THẺ THÀNH VIÊN) -->
<section class="container" style="margin-top: 40px;">
    <h2 class="section-title"><i class="fa-solid fa-id-card" style="color: var(--primary-color);"></i> Official Membership</h2>
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