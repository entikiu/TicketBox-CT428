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

        <!-- Danh sách sự kiện nổi bật lấy từ Database -->
        <div class="event-slider" id="eventSlider">
            <?php
            // Truy vấn 10 sự kiện nổi bật từ bảng sukien
            $stmtFeatured = $pdo->prepare("SELECT * FROM sukien ORDER BY id ASC LIMIT 10");
            $stmtFeatured->execute();
            $dbEvents = $stmtFeatured->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dbEvents as $row) {
                // Tách tên Tỉnh/Thành phố ở cuối chuỗi địa chỉ (sau dấu phẩy cuối cùng)
                $city = $row['dia_diem'];
                if (strpos($row['dia_diem'], ',') !== false) {
                    $city = trim(substr(strrchr($row['dia_diem'], ','), 1));
                }

                // Ánh xạ cột CSDL sang đúng định dạng biến của templates/event-card.php
                $event = [
                    'id'       => $row['id'],
                    'title'    => $row['ten_su_kien'],
                    'price'    => 'Từ ' . number_format($row['gia_thap_nhat'], 0, ',', '.') . ' đ',
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


<!-- FEATURED STARS / BAN TỔ CHỨC NỔI BẬT -->
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
            <button type="button" class="star-nav-btn star-nav-prev" id="starPrevBtn" onclick="scrollStars(-1)">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="stars-track" id="starsTrack" onscroll="updateStarArrows()">
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

            <button type="button" class="star-nav-btn star-nav-next" id="starNextBtn" onclick="scrollStars(1)">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </section>
</div>

<script>
function updateStarArrows() {
    const track = document.getElementById('starsTrack');
    const prevBtn = document.getElementById('starPrevBtn');
    const nextBtn = document.getElementById('starNextBtn');
    if (!track || !prevBtn || !nextBtn) return;

    prevBtn.style.display = track.scrollLeft > 10 ? 'flex' : 'none';
    const isAtEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 10;
    nextBtn.style.display = isAtEnd ? 'none' : 'flex';
}

function scrollStars(direction) {
    const track = document.getElementById('starsTrack');
    if (!track) return;
    const card = track.querySelector('.star-card');
    const step = card ? (card.offsetWidth + 12) * 5 : 680;
    track.scrollBy({ left: direction * step, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', updateStarArrows);
window.addEventListener('resize', updateStarArrows);
</script>
<!-- Section: Thẻ thành viên -->
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
        <button type="button" class="mem-nav-btn mem-nav-prev" id="memPrevBtn" onclick="scrollMembership(-1)">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="membership-track" id="membershipTrack" onscroll="updateMembershipArrows()">
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

        <button type="button" class="mem-nav-btn mem-nav-next" id="memNextBtn" onclick="scrollMembership(1)">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</section>

<script>
    function updateMembershipArrows() {
        const track = document.getElementById('membershipTrack');
        const prevBtn = document.getElementById('memPrevBtn');
        const nextBtn = document.getElementById('memNextBtn');
        if (!track || !prevBtn || !nextBtn) return;

        // Căn giữa mũi tên theo đúng chiều cao thực tế của khung ảnh
        const thumb = track.querySelector('.membership-thumb');
        if (thumb && thumb.offsetHeight > 0) {
            const centerTop = (thumb.offsetHeight / 2) + 'px';
            prevBtn.style.top = centerTop;
            nextBtn.style.top = centerTop;
        }

        prevBtn.style.display = track.scrollLeft > 10 ? 'flex' : 'none';
        const isAtEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 10;
        nextBtn.style.display = isAtEnd ? 'none' : 'flex';
    }

    function scrollMembership(direction) {
        const track = document.getElementById('membershipTrack');
        if (!track) return;
        const card = track.querySelector('.membership-card');
        const step = card ? (card.offsetWidth + 20) * 2 : track.clientWidth * 0.7;
        track.scrollBy({ left: direction * step, behavior: 'smooth' });
    }

    document.addEventListener('DOMContentLoaded', updateMembershipArrows);
    window.addEventListener('resize', updateMembershipArrows);
</script>



<?php include '../templates/footer.php'; ?>