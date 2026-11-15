<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// 1. Lấy các tham số bộ lọc từ URL (hỗ trợ cả tham số từ menu lẫn khung tìm kiếm AJAX)
$selected_cat      = trim((string)($_GET['cat'] ?? $_GET['danh_muc_id'] ?? ''));
$selected_location = trim((string)($_GET['location'] ?? $_GET['city'] ?? ''));
$selected_sort     = trim((string)($_GET['sort'] ?? ''));
$search_query      = trim((string)($_GET['q'] ??$_GET['search'] ?? $_GET['keyword'] ?? ''));

// 2. Xây dựng câu truy vấn SQL động
$sql = "SELECT s.*, dm.ten_danh_muc, dm.slug AS danh_muc_slug 
        FROM sukien s 
        LEFT JOIN danh_muc dm ON s.danh_muc_id = dm.id 
        WHERE 1=1";
$params = [];
$page_title = 'Tất cả sự kiện';

// Lọc theo từ khóa tìm kiếm (tách :q1, :q2, :q3 để tránh lỗi HY093 của PDO)
if ($search_query !== '') {
    $sql .= " AND (s.ten_su_kien LIKE :q1 OR s.dia_diem LIKE :q2 OR s.ten_ban_to_chuc LIKE :q3)";
    $kwLike       = '%' . $search_query . '%';
    $params['q1'] = $kwLike;
    $params['q2'] = $kwLike;
    $params['q3'] = $kwLike;
    $page_title   = 'Kết quả tìm kiếm: "' . $search_query . '"';
}

// Lọc theo địa điểm / địa chỉ
if ($selected_location !== '') {
    $locationMap = [
        'hanoi'  => 'Hà Nội',
        'hcm'    => 'Hồ Chí Minh',
        'danang' => 'Đà Nẵng',
        'dalat'  => 'Đà Lạt',
    ];
    $locKeyword = $locationMap[strtolower($selected_location)] ?? $selected_location;
    $sql .= " AND s.dia_diem LIKE :loc";
    $params['loc'] = '%' . $locKeyword . '%';
}

// Lọc theo danh mục (đồng bộ cả slug trên thanh menu lẫn ID/slug trong bảng danh_muc)
if ($selected_cat !== '' && $selected_cat !== 'all' && $selected_cat !== 'tat-ca') {
    switch ($selected_cat) {
        case 'nhac-song':
        case 'concert-am-nhac':
        case 'am-nhac':
        case '1':
            $sql .= " AND s.danh_muc_id = 1";
            $page_title = 'Nhạc sống';
            break;

        case 'san-khau-nghe-thuat':
        case 'san-khau':
        case '4':
            $sql .= " AND s.danh_muc_id = 4";
            $page_title = 'Sân khấu & Nghệ thuật';
            break;

        case 'the-thao':
        case 'the-thao-giai-tri':
        case '2':
            $sql .= " AND s.danh_muc_id = 2 AND s.id NOT IN (35, 36, 39, 41)";
            $page_title = 'Thể thao';
            break;

        case 'hoi-thao-workshop':
        case 'workshop-sang-tao':
        case 'workshop':
        case '3':
            $sql .= " AND s.danh_muc_id = 3";
            $page_title = 'Hội thảo & Workshop';
            break;

        case 'tham-quan-trai-nghiem':
        case 'tham-quan':
        case 'trai-nghiem':
        case '5':
            $sql .= " AND (
                        s.danh_muc_id = 5 
                        OR s.id IN (11, 13, 34, 35, 36, 37, 38, 39, 40, 41)
                        OR s.ten_su_kien LIKE '%Trải nghiệm%'
                        OR s.ten_su_kien LIKE '%Du lịch%'
                        OR s.ten_su_kien LIKE '%Bảo tàng%'
                        OR s.ten_su_kien LIKE '%Triển lãm%'
                        OR s.ten_su_kien LIKE '%Tour%'
                        OR s.ten_su_kien LIKE '%Khu vui chơi%'
                        OR s.ten_su_kien LIKE '%Xe Buýt%'
                      )";
            $page_title = 'Tham quan & Trải nghiệm';
            break;

        default:
            if (is_numeric($selected_cat)) {
                $sql .= " AND s.danh_muc_id = :cat";
                $params['cat'] = (int)$selected_cat;
            } else {
                $sql .= " AND dm.slug = :cat_slug";
                $params['cat_slug'] = $selected_cat;
            }
            break;
    }
}

// Sắp xếp theo giá hoặc mặc định
if ($selected_sort === 'price-asc') {
    $sql .= " ORDER BY s.gia_thap_nhat ASC, s.id ASC";
} elseif ($selected_sort === 'price-desc') {
    $sql .= " ORDER BY s.gia_thap_nhat DESC, s.id ASC";
} else {
    $sql .= " ORDER BY s.thoi_gian DESC, s.id DESC";
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
            <i class="fa-solid fa-compass" style="color: var(--primary-color);"></i> <?php echo htmlspecialchars($page_title); ?>
        </h2>

        <!-- Form Bộ lọc tự động submit khi thay đổi lựa chọn -->
        <form method="GET" action="events.php" style="display: flex; gap: 10px;">
            <?php if (!empty($selected_cat)): ?>
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($selected_cat); ?>">
            <?php endif; ?>
            <?php if (!empty($search_query)): ?>
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
            <?php endif; ?>


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