<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// 1. Lấy ID/Slug nhà tổ chức từ URL (Mặc định: 'yeah1-family')
$organizer_id = $_GET['id'] ?? 'yeah1-family';

// 2. Cơ sở dữ liệu mẫu các Ban tổ chức
$organizers_db = [
    'yeah1-family' => [
        'name' => 'Yeah1 Family',
        'type' => 'Ban tổ chức',
        'verified' => true,
        'avatar' => '../assets/images/organizers/yeah1.jpg',
        'events' => [
            [
                'date_str' => '12 Tháng 12, 2026',
                'title' => 'HIỆN DIỆN - UPRIZE CONCERT',
                'price' => 'Từ 800.000 đ',
                'image' => '../assets/images/events/hien-dien.jpg',
                'status' => 'available',
                'status_label' => 'Mua vé ngay',
                'link' => 'event-detail.php?id=101'
            ],
            [
                'date_str' => '05 Tháng 12, 2026',
                'title' => '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 3 & DAY 4',
                'price' => 'Từ 1.000.000 đ',
                'image' => '../assets/images/events/atvncg-day34.jpg',
                'status' => 'sold_out',
                'status_label' => 'Hết vé',
                'link' => 'event-detail.php?id=102'
            ],
            [
                'date_str' => '18 Tháng 10, 2026',
                'title' => '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 1 & DAY 2',
                'price' => 'Từ 800.000 đ',
                'image' => '../assets/images/events/atvncg-day12.jpg',
                'status' => 'ended',
                'status_label' => 'Đã diễn ra',
                'link' => 'event-detail.php?id=103'
            ]
        ]
    ],
    'vie-concert' => [
        'name' => 'Vie Concert',
        'type' => 'Ban tổ chức',
        'verified' => true,
        'avatar' => '../assets/images/organizers/vie-concert.jpg',
        'events' => []
    ],
    'ss-label' => [
        'name' => 'SS Label',
        'type' => 'Ban tổ chức',
        'verified' => true,
        'avatar' => '../assets/images/organizers/ss-label.jpg',
        'events' => []
    ],
    'gai-meet' => [
        'name' => 'GAI MEET',
        'type' => 'Ban tổ chức',
        'verified' => true,
        'avatar' => '../assets/images/organizers/gai-meet.jpg',
        'events' => []
    ],
    'quoc-thien' => [
        'name' => 'Quốc Thiên',
        'type' => 'Nghệ sĩ',
        'verified' => true,
        'avatar' => '../assets/images/organizers/quoc-thien.jpg',
        'events' => []
    ]
];

$current_org = $organizers_db[$organizer_id] ?? $organizers_db['yeah1-family'];

include '../templates/header.php';
?>

<main class="container" style="margin-top: 35px; min-height: 80vh; max-width: 1200px;">
    
    <div class="organizer-profile-layout">
        
        <!-- CỘT TRÁI: THÔNG TIN BAN TỔ CHỨC -->
        <aside class="organizer-sidebar-card">
            <div class="organizer-avatar-wrap">
                <?php if (file_exists($current_org['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($current_org['avatar']); ?>" alt="<?php echo htmlspecialchars($current_org['name']); ?>">
                <?php else: ?>
                    <div class="organizer-avatar-fallback">
                        <span><?php echo htmlspecialchars(substr($current_org['name'], 0, 5)); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <h2 class="organizer-title">
                <?php echo htmlspecialchars($current_org['name']); ?>
                <?php if ($current_org['verified']): ?>
                    <i class="fa-solid fa-circle-check verified-badge" title="Đã xác minh"></i>
                <?php endif; ?>
            </h2>

            <span class="organizer-tag-pill"><?php echo htmlspecialchars($current_org['type']); ?></span>

            <button type="button" class="btn-follow-organizer">
                <i class="fa-solid fa-user-plus"></i> Theo dõi
            </button>
        </aside>

        <!-- CỘT PHẢI: LỊCH HOẠT ĐỘNG (TIMELINE) -->
        <section class="organizer-timeline-section">
            <div class="timeline-header">
                <h3>Lịch hoạt động</h3>
                <a href="events.php" class="view-all-link">Xem tất cả <i class="fa-solid fa-chevron-right"></i></a>
            </div>

            <?php if (empty($current_org['events'])): ?>
                <div class="empty-timeline">
                    <p style="color: #a0a3bd; text-align: center; padding: 40px 0;">Ban tổ chức chưa có lịch trình sự kiện mới.</p>
                </div>
            <?php else: ?>
                <div class="timeline-container">
                    <?php foreach ($current_org['events'] as $event): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            
                            <div class="timeline-date">
                                <i class="fa-regular fa-calendar-days"></i> <?php echo htmlspecialchars($event['date_str']); ?>
                            </div>

                            <div class="timeline-event-card">
                                <div class="event-thumb">
                                    <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" onerror="this.src='../assets/images/default-event.jpg'">
                                    
                                    <?php if ($event['status'] === 'ended'): ?>
                                        <span class="badge-event-status ended">Đã diễn ra</span>
                                    <?php endif; ?>
                                </div>

                                <div class="event-details">
                                    <h4 class="event-name"><?php echo htmlspecialchars($event['title']); ?></h4>
                                    
                                    <?php if (!empty($event['price'])): ?>
                                        <span class="event-price-text"><?php echo htmlspecialchars($event['price']); ?></span>
                                    <?php endif; ?>

                                    <div class="event-action-wrap">
                                        <?php if ($event['status'] === 'available'): ?>
                                            <a href="<?php echo $event['link']; ?>" class="btn-event-action btn-buy-now">
                                                Mua vé ngay
                                            </a>
                                        <?php elseif ($event['status'] === 'sold_out'): ?>
                                            <button class="btn-event-action btn-sold-out" disabled>
                                                Hết vé
                                            </button>
                                        <?php else: ?>
                                            <a href="<?php echo $event['link']; ?>" class="btn-event-action btn-view-detail">
                                                Xem chi tiết
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>

</main>

<?php include '../templates/footer.php'; ?>