<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Nhận linh hoạt cả tham số ?slug= hoặc ?id= từ URL
$slug = trim($_GET['slug'] ?? $_GET['id'] ?? 'yeah1-family');

// 1. Cấu hình thông tin Ban tổ chức & từ khóa đối chiếu với bảng `sukien`
$organizersConfig = [
    'yeah1-family' => [
        'name'     => 'YeaH1 Family',
        'avatar'   => '../assets/images/stars/yeah1.jpg',
        'type'     => 'Ban tổ chức',
        'verified' => true,
        'keywords' => ['YeaH1', 'YEAH1']
    ],
    'vie-concert' => [
        'name'     => 'Vie Concert',
        'avatar'   => '../assets/images/stars/vieconcert.jpg',
        'type'     => 'Ban tổ chức',
        'verified' => true,
        'keywords' => ['Vie']
    ],
    'ss-label' => [
        'name'     => 'SS Label',
        'avatar'   => '../assets/images/stars/sslabel.jpg',
        'type'     => 'Ban tổ chức',
        'verified' => true,
        'keywords' => ['SS Label', '1PRODUCTION']
    ],
    'gai-meet' => [
        'name'     => 'GAI MEET',
        'avatar'   => '../assets/images/stars/gaimeet.jpg',
        'type'     => 'Ban tổ chức',
        'verified' => true,
        'keywords' => ['GAI MEET']
    ],
    'quoc-thien' => [
        'name'     => 'Quốc Thiên',
        'avatar'   => '../assets/images/stars/quocthien.jpg',
        'type'     => 'Nghệ sĩ',
        'verified' => true,
        'keywords' => ['Quốc Thiên', 'SKYNOTE']
    ],
    'kich-idecaf' => [
        'name'     => 'Kịch IDECAF',
        'avatar'   => '../assets/images/stars/idecaf.jpg',
        'type'     => 'Ban tổ chức',
        'verified' => true,
        'keywords' => ['IDECAF']
    ]
];

// Lấy thông tin Ban tổ chức hiện tại
$orgInfo = $organizersConfig[$slug] ?? [
    'name'     => ucwords(str_replace('-', ' ', $slug)),
    'avatar'   => '../assets/images/stars/' . $slug . '.jpg',
    'type'     => 'Ban tổ chức',
    'verified' => true,
    'keywords' => [str_replace('-', ' ', $slug)]
];

// 2. Truy vấn toàn bộ sự kiện của Ban tổ chức (Tách riêng từng tham số PDO)
$conditions = [];
$params = [];
foreach ($orgInfo['keywords'] as $idx => $kw) {
    $keyBtc   = 'kw_btc_' . $idx;
    $keyTitle = 'kw_title_' . $idx;

    $conditions[] = "(ten_ban_to_chuc LIKE :$keyBtc OR ten_su_kien LIKE :$keyTitle)";
    $params[$keyBtc]   = '%' . $kw . '%';
    $params[$keyTitle] = '%' . $kw . '%';
}

$whereSql = implode(' OR ', $conditions);
$stmtEvents = $pdo->prepare("SELECT * FROM sukien WHERE $whereSql ORDER BY thoi_gian DESC");
$stmtEvents->execute($params);
$dbRows = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

// Chuẩn bị truy vấn kiểm tra trạng thái hết vé từ bảng `loaive`
$stmtCheckSoldOut = $pdo->prepare("
    SELECT COUNT(*) AS total_cats, SUM(da_het_ve) AS soldout_cats 
    FROM loaive 
    WHERE su_kien_id = :id
");

// 3. Ánh xạ dữ liệu từ CSDL sang cấu trúc mảng giao diện
$mappedEvents = [];
foreach ($dbRows as $row) {
    $timeSec = strtotime($row['thoi_gian']);
    $isEnded = $timeSec < time();

    $status = 'available';
    if ($isEnded) {
        $status = 'ended';
    } else {
        $stmtCheckSoldOut->execute(['id' => $row['id']]);
        $ticketStat = $stmtCheckSoldOut->fetch(PDO::FETCH_ASSOC);
        if ($ticketStat && (int)$ticketStat['total_cats'] > 0 && (int)$ticketStat['total_cats'] === (int)$ticketStat['soldout_cats']) {
            $status = 'sold_out';
        }
    }

    $mappedEvents[] = [
        'id'       => (int)$row['id'],
        'title'    => $row['ten_su_kien'],
        'date_str' => date('d \T\h\á\n\g m, Y', $timeSec),
        'image'    => $row['hinh_anh'] ?: '../assets/images/default-event.jpg',
        'price'    => 'Từ ' . number_format((int)$row['gia_thap_nhat'], 0, ',', '.') . ' đ',
        'status'   => $status,
        'link'     => 'event-detail.php?id=' . (int)$row['id']
    ];
}

// 4. Khởi tạo biến `$current_org` cho HTML
$current_org = [
    'name'     => $orgInfo['name'],
    'avatar'   => $orgInfo['avatar'],
    'type'     => $orgInfo['type'],
    'verified' => $orgInfo['verified'],
    'events'   => $mappedEvents
];

include '../templates/header.php';
?>

<main class="container" style="margin-top: 35px; min-height: 80vh; max-width: 1200px;">
    
    <div class="organizer-profile-layout">
        
        <!-- CỘT TRÁI: THÔNG TIN BAN TỔ CHỨC -->
        <aside class="organizer-sidebar-card">
            <div class="organizer-avatar-wrap">
                <?php if (file_exists(__DIR__ . '/' . $current_org['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($current_org['avatar']); ?>" alt="<?php echo htmlspecialchars($current_org['name']); ?>">
                <?php else: ?>
                    <div class="organizer-avatar-fallback">
                        <span><?php echo htmlspecialchars(mb_substr($current_org['name'], 0, 5, 'UTF-8')); ?></span>
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