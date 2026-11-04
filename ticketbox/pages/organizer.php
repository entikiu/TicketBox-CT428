<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
include '../templates/header.php';

// Lấy ID Ban tổ chức từ URL (Mặc định là 1 nếu không truyền)
$orgId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Dữ liệu mẫu khớp chuẩn hình ảnh Ticketbox
$mockOrganizers = [
    1 => ['id' => 1, 'ho_ten' => 'YeahH1 Family', 'avatar' => '../assets/images/stars/yeah1.jpg'],
    2 => ['id' => 2, 'ho_ten' => 'Vie Concert', 'avatar' => '../assets/images/stars/vieconcert.jpg'],
    3 => ['id' => 3, 'ho_ten' => 'SS Label', 'avatar' => '../assets/images/stars/sslabel.jpg'],
    4 => ['id' => 4, 'ho_ten' => 'GAI MEET', 'avatar' => '../assets/images/stars/gaimeet.jpg'],
    5 => ['id' => 5, 'ho_ten' => 'Quốc Thiên', 'avatar' => '../assets/images/stars/quocthien.jpg'],
];

// 1. Truy vấn thông tin Ban tổ chức từ CSDL
$organizer = null;
if (isset($pdo)) {
    $stmtOrg = $pdo->prepare("SELECT * FROM nguoidung WHERE id = :id");
    $stmtOrg->execute(['id' => $orgId]);
    $organizer = $stmtOrg->fetch(PDO::FETCH_ASSOC);
}

// Nếu CSDL chưa có dữ liệu ID này -> Sử dụng dữ liệu mẫu
if (!$organizer) {
    $organizer = $mockOrganizers[$orgId] ?? [
        'id' => $orgId,
        'ho_ten' => 'Ban tổ chức #' . $orgId,
        'avatar' => '../assets/images/default-avatar.png'
    ];
}

$avatarPath = $organizer['avatar'] ?? ($mockOrganizers[$orgId]['avatar'] ?? '../assets/images/default-avatar.png');

// 2. Truy vấn danh sách sự kiện do Ban tổ chức này đăng tải
$events = [];
if (isset($pdo)) {
    $stmtEvents = $pdo->prepare("SELECT * FROM sukien WHERE nguoi_tao = :id ORDER BY thoi_gian DESC");
    $stmtEvents->execute(['id' => $orgId]);
    $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);
}

// Danh sách sự kiện mẫu hiển thị đúng chuẩn timeline giao diện
if (empty($events)) {
    $events = [
        [
            'id' => 101,
            'ten_su_kien' => 'HIỆN DIỆN - UPRIZE CONCERT',
            'thoi_gian' => '2026-12-12 19:00:00',
            'gia' => 'Từ 800.000 đ',
            'trang_thai_btn' => 'buy_now', // Mua vé ngay
            'image' => '../assets/images/events/banner-uprize.jpg'
        ],
        [
            'id' => 102,
            'ten_su_kien' => '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 3 & DAY 4',
            'thoi_gian' => '2026-12-05 18:30:00',
            'gia' => 'Từ 1.000.000 đ',
            'trang_thai_btn' => 'upcoming', // Vé chưa mở bán
            'image' => '../assets/images/events/banner-anh-trai.jpg'
        ],
        [
            'id' => 103,
            'ten_su_kien' => '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 1 & DAY 2',
            'thoi_gian' => '2026-10-18 18:30:00',
            'gia' => 'Từ 1.000.000 đ',
            'trang_thai_btn' => 'ended', // Đã diễn ra
            'image' => '../assets/images/events/banner-anh-trai.jpg'
        ]
    ];
}
?>

<div class="org-page-wrapper">
    <div class="container org-main-container">
        
        <!-- CỘT BÊN TRÁI: KHUNG PROFILE BAN TỔ CHỨC -->
        <aside class="org-sidebar">
            <div class="org-card">
                <div class="org-avatar-box">
                    <img src="<?php echo $avatarPath; ?>" 
                         alt="<?php echo htmlspecialchars($organizer['ho_ten']); ?>" 
                         onerror="this.src='https://via.placeholder.com/120/000/26c973?text=BTC'">
                </div>
                <h2 class="org-title-name">
                    <?php echo htmlspecialchars($organizer['ho_ten']); ?>
                    <i class="fa-solid fa-circle-check verified-icon"></i>
                </h2>
                <span class="org-role-badge">Ban tổ chức</span>
                <button type="button" class="btn-follow-org">
                    <i class="fa-solid fa-user-plus"></i> Theo dõi
                </button>
            </div>
        </aside>

        <!-- CỘT BÊN PHẢI: LỊCH HOẠT ĐỘNG SỰ KIỆN -->
        <main class="org-timeline-content">
            <div class="timeline-card">
                <div class="timeline-header-row">
                    <h3 class="timeline-title">Lịch hoạt động</h3>
                    <a href="#" class="view-all-link">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
                </div>

                <div class="timeline-events-list">
                    <?php foreach ($events as $event): 
                        $timeSec = strtotime($event['thoi_gian']);
                        $dateFormatted = date('d \T\h\á\n\g m, Y', $timeSec);
                        $btnStatus = $event['trang_thai_btn'] ?? ($timeSec < time() ? 'ended' : 'buy_now');
                    ?>
                        <div class="timeline-event-item">
                            <!-- Cột mốc thời gian -->
                            <div class="timeline-date-marker">
                                <span class="dot-indicator"></span>
                                <i class="fa-regular fa-calendar"></i> <?php echo $dateFormatted; ?>
                            </div>

                            <!-- Thẻ sự kiện nằm ngang -->
                            <div class="event-horizontal-card">
                                <div class="event-thumb-wrapper">
                                    <img src="<?php echo $event['image'] ?? '../assets/images/events/banner-skynote.jpg'; ?>" 
                                         alt="Sự kiện"
                                         onerror="this.src='https://via.placeholder.com/200x110/222/fff?text=Ticketbox'">
                                    
                                    <?php if ($btnStatus === 'ended'): ?>
                                        <span class="badge-overlay-ended">Đã diễn ra</span>
                                    <?php endif; ?>
                                </div>

                                <div class="event-details-body">
                                    <h4 class="event-heading"><?php echo htmlspecialchars($event['ten_su_kien']); ?></h4>
                                    <div class="event-price-tag"><?php echo $event['gia'] ?? 'Từ 800.000 đ'; ?></div>
                                    
                                    <div class="event-action-box">
                                        <?php if ($btnStatus === 'buy_now'): ?>
                                            <a href="event-detail.php?id=<?php echo $event['id']; ?>" class="btn-ticket btn-buy-now">Mua vé ngay</a>
                                        <?php elseif ($btnStatus === 'upcoming'): ?>
                                            <button type="button" class="btn-ticket btn-disabled" disabled>Vé chưa mở bán</button>
                                        <?php else: ?>
                                            <button type="button" class="btn-ticket btn-disabled" disabled>Đã diễn ra</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>

    </div>
</div>

<style>
/* CSS ĐỊNH DẠNG TRANG NGHỆ SĨ / BAN TỔ CHỨC */
.org-page-wrapper {
    background-color: #121214;
    min-height: 85vh;
    padding: 40px 0;
    color: #ffffff;
}

.org-main-container {
    display: flex;
    gap: 28px;
    align-items: flex-start;
}

/* Sidebar Profile */
.org-sidebar {
    flex: 0 0 320px;
}

.org-card {
    background-color: #1e1e24;
    border: 1px solid #2d2d35;
    border-radius: 16px;
    padding: 32px 24px;
    text-align: center;
}

.org-avatar-box {
    width: 120px;
    height: 120px;
    margin: 0 auto 16px;
    border-radius: 50%;
    padding: 4px;
    background: linear-gradient(135deg, #26c973, #157342);
}

.org-avatar-box img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    background-color: #000;
}

.org-title-name {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.verified-icon {
    color: #26c973;
    font-size: 16px;
}

.org-role-badge {
    display: inline-block;
    background-color: #2b2b36;
    color: #b0b0ba;
    font-size: 12px;
    padding: 4px 14px;
    border-radius: 20px;
    margin-bottom: 24px;
}

.btn-follow-org {
    width: 100%;
    background-color: #26c973;
    color: #ffffff;
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.btn-follow-org:hover {
    background-color: #20ab61;
}

/* Timeline Content */
.org-timeline-content {
    flex: 1;
}

.timeline-card {
    background-color: #1e1e24;
    border: 1px solid #2d2d35;
    border-radius: 16px;
    padding: 28px;
}

.timeline-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #2d2d35;
}

.timeline-title {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
}

.view-all-link {
    color: #26c973;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}

/* Timeline Item List */
.timeline-events-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.timeline-event-item {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.timeline-date-marker {
    color: #26c973;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.dot-indicator {
    width: 8px;
    height: 8px;
    background-color: #26c973;
    border-radius: 50%;
    display: inline-block;
}

.event-horizontal-card {
    display: flex;
    gap: 16px;
    background-color: #272730;
    border-radius: 12px;
    padding: 14px;
    align-items: center;
}

.event-thumb-wrapper {
    position: relative;
    width: 180px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.event-thumb-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.badge-overlay-ended {
    position: absolute;
    top: 6px;
    right: 6px;
    background-color: #ff9800;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
}

.event-details-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.event-heading {
    font-size: 15px;
    font-weight: 600;
    margin: 0;
    color: #ffffff;
    line-height: 1.4;
}

.event-price-tag {
    color: #26c973;
    font-weight: 600;
    font-size: 14px;
}

.event-action-box {
    margin-top: 4px;
}

.btn-ticket {
    display: inline-block;
    padding: 8px 18px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    border: none;
}

.btn-buy-now {
    background-color: #26c973;
    color: #ffffff;
}

.btn-buy-now:hover {
    background-color: #20ab61;
}

.btn-disabled {
    background-color: #e5e5e5;
    color: #555555;
    cursor: not-allowed;
}
</style>

<?php include '../templates/footer.php'; ?>