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
<main class="container">
    <h2 class="section-title"><i class="fa-solid fa-fire" style="color: #ff4d4f;"></i> Sự kiện nổi bật</h2>
    
    <!-- Khung chứa Slider và Nút điều hướng -->
    <div class="slider-wrapper">
        <!-- Nút cuộn sang trái -->
        <button class="slider-btn prev-btn" id="slideLeftBtn" aria-label="Cuộn trái">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <!-- Danh sách sự kiện cuộn ngang -->
        <div class="event-slider" id="eventSlider">
            <?php 
            $mock_events = [
                [
                    'title' => 'Live Concert SKYNOTE - Quốc Thiên',
                    'price' => 'Từ 880.000 đ',
                    'date' => '29.11.2026',
                    'location' => 'Hà Nội',
                    'image' => '../assets/images/events/banner-skynote.jpg'
                ],
                [
                    'title' => 'Những Thành Phố Mộng Mơ Year End',
                    'price' => 'Từ 499.000 đ',
                    'date' => '12.12.2026',
                    'location' => 'TP. Hồ Chí Minh',
                    'image' => '../assets/images/events/banner-ntpmm.jpg'
                ],
                [
                    'title' => 'Mắt Nhắm Mắt Mở',
                    'price' => 'Từ 1.600.000 đ',
                    'date' => '01.11.2026',
                    'location' => 'Hà Nội',
                    'image' => '../assets/images/events/banner-mnmm.jpg'
                ], 
                [
                    'title' => 'Tinh Hà "Say Hi" Concert - Đêm 2',
                    'price' => 'Từ 800.000 đ',
                    'date' => '18.10.2026',
                    'location' => 'Hà Nội',
                    'image' => '../assets/images/events/banner-tinh-ha.jpg'
                ], 
                [
                    'title' => 'The Starry - S\'NOW',
                    'price' => 'Từ 800.000 đ',
                    'date' => '19.12.2026',
                    'location' => 'Hồ Chí Minh',
                    'image' => '../assets/images/events/banner-snow.jpg'
                ], 
                [
                    'title' => 'GAI MEET - NHÀ RỰC RỠ',
                    'price' => 'Từ 1.500.000 đ',
                    'date' => '18.10.2026',
                    'location' => 'Hồ Chí Minh',
                    'image' => '../assets/images/events/banner-nha-ruc-ro.jpg'
                ], 
                [
                    'title' => '(S)TRONG \'DỊ KIỀU\' SHOW 2026: ĐẾN GIÂY CUỐI CÙNG',
                    'price' => 'Từ 1.450.000 đ',
                    'date' => '07.11.2026',
                    'location' => 'Hà Nội',
                    'image' => '../assets/images/events/banner-strong.jpg'
                ], 
                [
                    'title' => 'GAI MEET - DỰ ÁN NHÂN TỐ X',
                    'price' => 'Từ 1.500.000 đ',
                    'date' => '01.11.2026',
                    'location' => 'Hồ Chí Minh',
                    'image' => '../assets/images/events/banner-nhan-to-x.jpg'
                ],
                [
                    'title' => '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 1 & DAY 2',
                    'price' => 'Từ 1.000.000 đ',
                    'date' => '18.10.2026',
                    'location' => 'Hồ Chí Minh',
                    'image' => '../assets/images/events/banner-anh-trai.jpg'
                ]
            ];

            // Lặp và nhúng từng thẻ sự kiện
            foreach ($mock_events as $event) {
                include '../templates/event-card.php';
            }
            ?>
        </div>

        <!-- Nút cuộn sang phải -->
        <button class="slider-btn next-btn" id="slideRightBtn" aria-label="Cuộn phải">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</main>

<?php include '../templates/footer.php'; ?>