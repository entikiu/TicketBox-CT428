<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// 1. Lấy thông tin chi tiết sự kiện từ bảng sukien
$stmt = $pdo->prepare("SELECT * FROM sukien WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $event_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    header('Location: home.php');
    exit;
}

// 2. Lấy danh sách hạng vé tương ứng từ bảng loaive
$stmtCat = $pdo->prepare("SELECT ten_loai_ve, gia_ve, da_het_ve FROM loaive WHERE su_kien_id = :id ORDER BY id ASC");
$stmtCat->execute(['id' => $event_id]);
$ticketRows = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
foreach ($ticketRows as $t) {
    $categories[] = [
        'name'    => $t['ten_loai_ve'],
        'price'   => number_format((int)$t['gia_ve'], 0, ',', '.') . ' đ',
        'soldout' => (bool)$t['da_het_ve']
    ];
}

// 3. Ánh xạ về biến $event_detail khớp với toàn bộ HTML bên dưới
$event_detail = [
    'id'               => (int)$row['id'],
    'title'            => $row['ten_su_kien'],
    'date_str'         => $row['thoi_gian_hien_thi'] ?: date('H:i, d/m/Y', strtotime($row['thoi_gian'])),
    'location_name'    => $row['ten_dia_diem'] ?: $row['dia_diem'],
    'location_address' => $row['dia_diem'] ?? '',
    'min_price'        => number_format((int)$row['gia_thap_nhat'], 0, ',', '.') . ' đ',
    'banner_img'       => $row['hinh_anh'] ?: '../assets/images/default-event.jpg',
    'organizer_name'   => $row['ten_ban_to_chuc'] ?: 'Ban tổ chức Ticketbox',
    'organizer_sub'    => $row['mo_ta_ban_to_chuc'] ?: 'Đơn vị tổ chức sự kiện',
    'categories'       => $categories
];

// Tìm hạng vé đầu tiên còn vé cho nút "Mua vé ngay" ở khung trên
$defaultCategory = ['name' => 'Vé Tiêu Chuẩn', 'price' => $event_detail['min_price']];
$allSoldOut = !empty($categories);
foreach ($categories as $cat) {
    if (!$cat['soldout']) {
        $defaultCategory = $cat;
        $allSoldOut = false;
        break;
    }
}

include '../templates/header.php';
?>

<main class="container" style="margin-top: 25px;">
    <!-- 1. KHUNG THẺ VÉ THÔNG TIN CHÍNH -->
    <div class="ticket-stub-card">
        <div class="stub-left">
            <h1 class="stub-title"><?php echo htmlspecialchars($event_detail['title']); ?></h1>

            <div class="stub-info">
                <i class="fa-regular fa-calendar-check"></i>
                <span><?php echo htmlspecialchars($event_detail['date_str']); ?></span>
            </div>

            <div class="stub-info">
                <i class="fa-solid fa-location-dot"></i>
                <div>
                    <strong><?php echo htmlspecialchars($event_detail['location_name']); ?></strong>
                    <p style="font-size: 12px; color: #a0a3bd; margin-top: 2px;"><?php echo htmlspecialchars($event_detail['location_address']); ?></p>
                </div>
            </div>

            <div class="stub-divider"></div>

            <div class="stub-price-row">
                <div class="stub-price-link">Giá từ <span><?php echo $event_detail['min_price']; ?></span></div>

                <?php if ($allSoldOut): ?>
                    <button type="button" class="btn-buy-primary" style="border:none; background:#555; cursor:not-allowed;" disabled>Đã hết vé</button>
                <?php else: ?>
                    <!-- Form 1: Ở khung thẻ vé chính phía trên -->
                    <form action="checkout.php" method="POST">
                        <input type="hidden" name="su_kien_id" value="<?php echo $event_detail['id']; ?>">
                        <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($event_detail['title']); ?>">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($defaultCategory['name']); ?>">
                        <input type="hidden" name="price" value="<?php echo htmlspecialchars($defaultCategory['price']); ?>">
                        <input type="hidden" name="location" value="<?php echo htmlspecialchars($event_detail['location_name']); ?>">
                        <input type="hidden" name="date" value="<?php echo htmlspecialchars($event_detail['date_str']); ?>">
                        <input type="hidden" name="image" value="<?php echo htmlspecialchars($event_detail['banner_img']); ?>">
                        <button type="submit" class="btn-buy-primary" style="border:none; cursor:pointer;">Mua vé ngay</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="notch notch-top"></div>
        <div class="notch notch-bottom"></div>

        <div class="stub-right">
            <img src="<?php echo htmlspecialchars($event_detail['banner_img']); ?>" alt="<?php echo htmlspecialchars($event_detail['title']); ?>" onerror="this.src='https://via.placeholder.com/600x340/1b1537/fff?text=Ticketbox';">
        </div>
    </div>

    <!-- 2. DANH SÁCH HẠNG VÉ ĐỘNG -->
    <div class="detail-block" style="margin-top: 35px;">
        <h3 style="margin-bottom: 20px;">Lịch diễn & Hạng vé</h3>
        <div class="schedule-accordion">
            <?php if (empty($event_detail['categories'])): ?>
                <p style="color: #a0a3bd;">Hiện chưa có thông tin hạng vé cho sự kiện này.</p>
            <?php else: ?>
                <?php foreach ($event_detail['categories'] as $cat): ?>
                    <div class="cat-item">
                        <span><i class="fa-solid fa-chevron-right"></i> <?php echo htmlspecialchars($cat['name']); ?></span>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <strong class="price-green"><?php echo htmlspecialchars($cat['price']); ?></strong>

                            <?php if ($cat['soldout']): ?>
                                <span class="badge-soldout">Hết vé</span>
                            <?php else: ?>
                                <!-- Form 2: Trong vòng lặp danh sách hạng vé bên dưới -->
                                <form action="checkout.php" method="POST">
                                    <input type="hidden" name="su_kien_id" value="<?php echo $event_detail['id']; ?>">
                                    <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($event_detail['title']); ?>">
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($cat['name']); ?>">
                                    <input type="hidden" name="price" value="<?php echo htmlspecialchars($cat['price']); ?>">
                                    <input type="hidden" name="location" value="<?php echo htmlspecialchars($event_detail['location_name']); ?>">
                                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($event_detail['date_str']); ?>">
                                    <input type="hidden" name="image" value="<?php echo htmlspecialchars($event_detail['banner_img']); ?>">
                                    <button type="submit" class="btn-buy-sm">Mua vé</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. BẢNG BAN TỔ CHỨC ĐỘNG -->
    <div class="detail-block" style="margin-top: 30px;">
        <h3 style="color: var(--primary-color); margin-bottom: 15px;">Ban tổ chức</h3>
        <div class="organizer-card">
            <div class="org-avatar">
                <span style="font-size: 16px; font-weight: 900; color: #ff3366; text-align:center;">
                    <?php echo mb_strtoupper(mb_substr($event_detail['organizer_name'], 0, 3, 'UTF-8'), 'UTF-8'); ?>
                </span>
            </div>
            <div>
                <h4 style="font-size: 18px; margin-bottom: 4px;"><?php echo htmlspecialchars($event_detail['organizer_name']); ?></h4>
                <p style="color: var(--text-muted); font-size: 14px;"><?php echo htmlspecialchars($event_detail['organizer_sub']); ?></p>
            </div>
        </div>
    </div>
</main>

<?php include '../templates/footer.php'; ?>