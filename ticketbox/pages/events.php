<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// 1. Lấy các tham số bộ lọc từ URL
$selected_cat      = trim($_GET['cat'] ?? '');
$selected_location = trim($_GET['location'] ?? '');
$selected_sort     = trim($_GET['sort'] ?? '');
$search_query      = trim($_GET['q'] ?? '');

// 2. Xây dựng câu truy vấn SQL động tối ưu hóa với bảng danh_muc
$sql = "SELECT s.*, dm.ten_danh_muc, dm.slug AS danh_muc_slug 
        FROM sukien s 
        LEFT JOIN danh_muc dm ON s.danh_muc_id = dm.id 
        WHERE 1=1";
$params = [];

// Lọc theo từ khóa tìm kiếm (nếu người dùng tìm từ thanh search)
if ($search_query !== '') {
    $sql .= " AND (s.ten_su_kien LIKE :q OR s.dia_diem LIKE :q OR s.ten_ban_to_chuc LIKE :q)";
    $params['q'] = '%' . $search_query . '%';
}

// Lọc theo địa điểm
if ($selected_location === 'hanoi') {
    $sql .= " AND s.dia_diem LIKE :loc";
    $params['loc'] = '%Hà Nội%';
} elseif ($selected_location === 'hcm') {
    $sql .= " AND s.dia_diem LIKE :loc";
    $params['loc'] = '%Hồ Chí Minh%';
} elseif ($selected_location === 'danang') {
    $sql .= " AND s.dia_diem LIKE :loc";
    $params['loc'] = '%Đà Nẵng%';
}

// Lọc theo danh mục chuẩn xác bằng ID hoặc Slug của bảng danh_muc
if ($selected_cat !== '') {
    if (is_numeric($selected_cat)) {
        $sql .= " AND s.danh_muc_id = :cat";
        $params['cat'] = (int)$selected_cat;
    } else {
        $sql .= " AND dm.slug = :cat_slug";
        $params['cat_slug'] = $selected_cat;
    }
}

// Sắp xếp theo giá hoặc mặc định
if ($selected_sort === 'price-asc') {
    $sql .= " ORDER BY s.gia_thap_nhat ASC, s.id ASC";
} elseif ($selected_sort === 'price-desc') {
    $sql .= " ORDER BY s.gia_thap_nhat DESC, s.id ASC";
} else {
    $sql .= " ORDER BY s.id ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Ánh xạ dữ liệu từ DB sang mảng $all_events chuẩn cho event-card.php
$all_events = [];
foreach ($rows as $row) {
    $city = strpos($row['dia_diem'], ',') !== false
        ? trim(substr(strrchr($row['dia_diem'], ','), 1))
        : $row['dia_diem'];

    $all_events[] = [
        'id'       => $row['id'],
        'title'    => $row['ten_su_kien'],
        'price'    => 'Từ ' . number_format((int)$row['gia_thap_nhat'], 0, ',', '.') . ' đ',
        'date'     => date('d.m.Y', strtotime($row['thoi_gian'])),
        'location' => $city,
        'image'    => $row['hinh_anh']
    ];
}

include '../templates/header.php';
?>

<main class="container" style="margin-top: 30px; min-height: 70vh;">
    <!-- Tiêu đề trang & Form Bộ lọc -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <h2 class="section-title" style="margin: 0;">
            <i class="fa-solid fa-compass" style="color: var(--primary-color);"></i> Tất cả sự kiện
        </h2>

        <!-- Form Bộ lọc tự động submit khi thay đổi lựa chọn -->
        <form method="GET" action="events.php" style="display: flex; gap: 10px;">
            <?php if (!empty($selected_cat)): ?>
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($selected_cat); ?>">
            <?php endif; ?>
            <?php if (!empty($search_query)): ?>
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
            <?php endif; ?>

            <select name="location" onchange="this.form.submit()" class="category-btn" style="background: var(--card-bg); color: #fff; border: 1px solid var(--border-color); padding: 8px 15px; outline: none; cursor: pointer;">
                <option value="">Tất cả địa điểm</option>
                <option value="hanoi" <?php echo $selected_location === 'hanoi' ? 'selected' : ''; ?>>Hà Nội</option>
                <option value="hcm" <?php echo $selected_location === 'hcm' ? 'selected' : ''; ?>>TP. Hồ Chí Minh</option>
                <option value="danang" <?php echo $selected_location === 'danang' ? 'selected' : ''; ?>>Đà Nẵng</option>
            </select>

            <select name="sort" onchange="this.form.submit()" class="category-btn" style="background: var(--card-bg); color: #fff; border: 1px solid var(--border-color); padding: 8px 15px; outline: none; cursor: pointer;">
                <option value="">Sắp xếp: Mới nhất</option>
                <option value="price-asc" <?php echo $selected_sort === 'price-asc' ? 'selected' : ''; ?>>Giá: Thấp đến Cao</option>
                <option value="price-desc" <?php echo $selected_sort === 'price-desc' ? 'selected' : ''; ?>>Giá: Cao đến Thấp</option>
            </select>
        </form>
    </div>

    <!-- Lưới hiển thị sự kiện -->
    <?php if (empty($all_events)): ?>
        <div style="text-align: center; padding: 60px 0; color: var(--text-muted);">
            <i class="fa-solid fa-calendar-xmark" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            <p>Không tìm thấy sự kiện phù hợp với bộ lọc.</p>
            <a href="events.php" style="color: var(--primary-color); font-size: 14px; text-decoration: underline; margin-top: 10px; display: inline-block;">Xem tất cả sự kiện</a>
        </div>
    <?php else: ?>
        <div class="event-grid">
            <?php
            $index = 0;
            foreach ($all_events as $event) {
                // Ẩn toàn bộ sự kiện từ chỉ số index >= 8 (từ sự kiện thứ 9 trở đi)
                $is_hidden = ($index >= 8);
                $extra_class = $is_hidden ? 'extra-event-item' : '';
                $extra_style = $is_hidden ? 'style="display: none;"' : '';

                echo '<div class="' . $extra_class . '" ' . $extra_style . '>';
                include '../templates/event-card.php';
                echo '</div>';

                $index++;
            }
            ?>
        </div>

        <!-- Nút Xem thêm chỉ xuất hiện khi số lượng sự kiện ban đầu > 8 -->
        <?php if (count($all_events) > 8): ?>
            <div class="load-more-wrapper" id="loadMoreWrapper">
                <button type="button" id="btnLoadMoreEvents" class="btn-load-more">
                    Xem thêm sự kiện <i class="fa-solid fa-chevron-down"></i>
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<script src="../assets/js/events.js"></script>
<?php include '../templates/footer.php'; ?>