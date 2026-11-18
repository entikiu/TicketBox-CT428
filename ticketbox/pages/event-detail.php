<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
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

// Kiểm tra sự kiện đã diễn ra hay chưa 
$isEventEnded = false;
$now = time();

if (!empty($row['thoi_gian_ket_thuc'])) {
    $endTs = strtotime($row['thoi_gian_ket_thuc']);
    if ($endTs !== false && $now > $endTs) {
        $isEventEnded = true;
    }
} elseif (!empty($row['thoi_gian'])) {
    $startTs = strtotime($row['thoi_gian']);
    if ($startTs !== false && $now > $startTs) {
        $isEventEnded = true;
    }
} elseif (!empty($row['thoi_gian_hien_thi'])) {
    if (preg_match('/(\d{1,2})[\.\/](\d{1,2})[\.\/](\d{4})/', $row['thoi_gian_hien_thi'], $m)) {
        $fallbackTs = strtotime(sprintf('%04d-%02d-%02d 23:59:59', $m[3], $m[2], $m[1]));
        if ($fallbackTs !== false && $now > $fallbackTs) {
            $isEventEnded = true;
        }
    }
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

// 3. Lấy thông tin Ban tổ chức liên kết với sự kiện
$eventStars = [];
try {
    $stmtStars = $pdo->prepare("
        SELECT ts.id, ts.slug, ts.ten_hien_thi, ts.loai, ts.avatar, ts.mo_ta, ts.da_xac_minh
        FROM ticketstars ts
        INNER JOIN ticketstar_sukien tss ON ts.id = tss.star_id
        WHERE tss.su_kien_id = :id
        ORDER BY ts.thu_tu ASC, ts.id ASC
    ");
    $stmtStars->execute(['id' => $event_id]);
    $eventStars = $stmtStars->fetchAll(PDO::FETCH_ASSOC);

    // Nếu chưa gắn trong ticketstar_sukien thì đối chiếu theo tên ban tổ chức
    if (empty($eventStars) && !empty($row['ten_ban_to_chuc'])) {
        $stmtFallbackStar = $pdo->prepare("
            SELECT id, slug, ten_hien_thi, loai, avatar, mo_ta, da_xac_minh
            FROM ticketstars
            WHERE LOWER(ten_hien_thi) = LOWER(:org_name)
            LIMIT 1
        ");
        $stmtFallbackStar->execute(['org_name' => trim($row['ten_ban_to_chuc'])]);
        $matchedStar = $stmtFallbackStar->fetch(PDO::FETCH_ASSOC);
        if ($matchedStar) {
            $eventStars[] = $matchedStar;
        }
    }
} catch (PDOException $e) {
    $eventStars = [];
}

// 4. Ánh xạ về biến $event_detail khớp với toàn bộ HTML bên dưới
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
    'categories'       => $categories,
    'stars'            => $eventStars
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

$starRoleLabels = [
    'NgheSi'    => 'Nghệ sĩ',
    'BanToChuc' => 'Ban tổ chức',
    'SanKhau'   => 'Sân khấu'
];

include '../templates/header.php';
?>

<main class="container" style="margin-top: 25px;">
    <!-- 1. KHUNG THẺ VÉ THÔNG TIN CHÍNH -->
    <div class="ticket-stub-card">
        <div class="stub-left">
            <?php if ($isEventEnded): ?>
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(250, 140, 22, 0.15); color: #fa8c16; border: 1px solid rgba(250, 140, 22, 0.4); padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; margin-bottom: 12px;">
                    <i class="fa-regular fa-clock"></i> Sự kiện đã diễn ra
                </div>
            <?php endif; ?>

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

                <?php if ($isEventEnded): ?>
                    <button type="button" class="btn-buy-primary" style="border:none; background:#3a3a45; color:#a0a3bd; cursor:not-allowed;" disabled>Đã kết thúc</button>
                <?php elseif ($allSoldOut): ?>
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
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
            <h3 style="margin: 0;">Lịch diễn & Hạng vé</h3>
            <?php if ($isEventEnded): ?>
                <span style="font-size: 13px; color: #fa8c16;">
                    <i class="fa-solid fa-circle-info"></i> Cổng bán vé trực tuyến đã đóng do sự kiện đã kết thúc.
                </span>
            <?php endif; ?>
        </div>

        <div class="schedule-accordion">
            <?php if (empty($event_detail['categories'])): ?>
                <p style="color: #a0a3bd;">Hiện chưa có thông tin hạng vé cho sự kiện này.</p>
            <?php else: ?>
                <?php foreach ($event_detail['categories'] as $cat): ?>
                    <div class="cat-item">
                        <span><i class="fa-solid fa-chevron-right"></i> <?php echo htmlspecialchars($cat['name']); ?></span>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <strong class="price-green"><?php echo htmlspecialchars($cat['price']); ?></strong>

                            <?php if ($isEventEnded): ?>
                                <span class="badge-soldout" style="background: rgba(250, 140, 22, 0.15); color: #fa8c16; border: 1px solid rgba(250, 140, 22, 0.35);">Ngừng bán</span>
                            <?php elseif ($cat['soldout']): ?>
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

    <!-- 3. BẢNG BAN TỔ CHỨC & NGHỆ SĨ ĐỘNG -->
    <div class="detail-block" style="margin-top: 30px;">
        <h3 style="color: var(--primary-color); margin-bottom: 15px;">
            <?php echo count($event_detail['stars']) > 1 ? 'Ban tổ chức & Nghệ sĩ' : 'Ban tổ chức'; ?>
        </h3>

        <?php if (!empty($event_detail['stars'])): ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($event_detail['stars'] as $star): 
                    $roleText = $starRoleLabels[$star['loai']] ?? 'Ban tổ chức';
                    $starAvatar = !empty($star['avatar']) ? $star['avatar'] : '../assets/images/default-avatar.png';
                    $starDesc = !empty($star['mo_ta']) ? $star['mo_ta'] : $event_detail['organizer_sub'];
                ?>
                    <a href="organizer.php?slug=<?php echo urlencode($star['slug']); ?>" class="organizer-card star-link-card" title="Xem trang chi tiết <?php echo htmlspecialchars($star['ten_hien_thi']); ?>">
                        <div class="star-card-left">
                            <div class="org-avatar star-avatar-wrap">
                                <img src="<?php echo htmlspecialchars($starAvatar); ?>"
                                     alt="<?php echo htmlspecialchars($star['ten_hien_thi']); ?>"
                                     onerror="this.onerror=null; this.src='../assets/images/default-avatar.png';">
                            </div>
                            <div style="min-width: 0;">
                                <h4 class="star-name-title" style="font-size: 18px; margin-bottom: 4px; transition: color 0.2s;">
                                    <?php echo htmlspecialchars($star['ten_hien_thi']); ?>
                                    <?php if (!empty($star['da_xac_minh'])): ?>
                                        <i class="fa-solid fa-circle-check" style="color: #26c973; font-size: 15px; margin-left: 4px;" title="Đã xác minh"></i>
                                    <?php endif; ?>
                                    <span class="star-role-pill"><?php echo htmlspecialchars($roleText); ?></span>
                                </h4>
                                <p class="star-desc-clamp"><?php echo htmlspecialchars($starDesc); ?></p>
                            </div>
                        </div>
                        <span style="color: #26c973; font-size: 13px; font-weight: 600; white-space: nowrap; flex-shrink: 0;">
                            Xem chi tiết <i class="fa-solid fa-chevron-right" style="font-size: 11px; margin-left: 4px;"></i>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Dự phòng cho các sự kiện chưa gắn vào bảng ticketstars-->
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
        <?php endif; ?>
    </div>
</main>

<?php include '../templates/footer.php'; ?>