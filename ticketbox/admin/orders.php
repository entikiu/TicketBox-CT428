<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error   = '';

// -------------------- CẬP NHẬT TRẠNG THÁI VÉ (CHECK-IN / HỦY / KHÔI PHỤC) --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $ve_id          = (int)($_POST['ve_id'] ?? 0);
    $trang_thai_moi = $_POST['trang_thai'] ?? '';
    $hop_le         = ['thanh-cong', 'da-checkin', 'da-huy'];

    if ($ve_id > 0 && in_array($trang_thai_moi, $hop_le, true)) {
        try {
            $stmt = $pdo->prepare("UPDATE ve SET trang_thai = :trang_thai WHERE id = :id");
            $stmt->execute([
                'trang_thai' => $trang_thai_moi,
                'id'         => $ve_id,
            ]);
            $message = 'Đã cập nhật trạng thái vé thành công!';
        } catch (PDOException $e) {
            $error = 'Lỗi khi cập nhật trạng thái vé: ' . $e->getMessage();
        }
    }
}

// -------------------- THAM SỐ LỌC DỮ LIỆU --------------------
$filter_su_kien    = isset($_GET['su_kien_id']) ? (int)$_GET['su_kien_id'] : 0;
$filter_trang_thai = trim($_GET['trang_thai'] ?? '');
$keyword           = trim($_GET['q'] ?? '');

// -------------------- 1. THỐNG KÊ KPI TỔNG HỆ THỐNG --------------------
$kpi = [
    'tong_hop_le'    => 0,
    'da_checkin'     => 0,
    'chua_checkin'   => 0,
    'da_huy'         => 0,
];
try {
    $stmtKpi = $pdo->query("
        SELECT
            SUM(CASE WHEN trang_thai IN ('thanh-cong', 'da-checkin') THEN 1 ELSE 0 END) AS tong_hop_le,
            SUM(CASE WHEN trang_thai = 'da-checkin' THEN 1 ELSE 0 END) AS da_checkin,
            SUM(CASE WHEN trang_thai = 'thanh-cong' THEN 1 ELSE 0 END) AS chua_checkin,
            SUM(CASE WHEN trang_thai = 'da-huy' THEN 1 ELSE 0 END) AS da_huy
        FROM ve
    ");
    $rowKpi = $stmtKpi->fetch(PDO::FETCH_ASSOC);
    if ($rowKpi) {
        $kpi = $rowKpi;
    }
} catch (PDOException $e) {
    $error = 'Lỗi tải thống kê vé: ' . $e->getMessage();
}

// -------------------- 2. THỐNG KÊ NGƯỜI THAM DỰ THEO SỰ KIỆN --------------------
$eventStats = [];
try {
    $stmtEvStats = $pdo->query("
        SELECT 
            s.id,
            s.ten_su_kien,
            s.thoi_gian,
            CASE WHEN s.so_luong_toi_da > 0 THEN s.so_luong_toi_da ELSE COALESCE(lv.tong_ve_loai, 0) END AS suc_chua,
            COALESCE(v.ve_hop_le, 0) AS ve_hop_le,
            COALESCE(v.da_checkin, 0) AS da_checkin,
            COALESCE(v.ve_huy, 0) AS ve_huy
        FROM sukien s
        LEFT JOIN (
            SELECT su_kien_id, SUM(so_luong) AS tong_ve_loai
            FROM loaive
            GROUP BY su_kien_id
        ) lv ON lv.su_kien_id = s.id
        LEFT JOIN (
            SELECT 
                su_kien_id,
                SUM(CASE WHEN trang_thai IN ('thanh-cong', 'da-checkin') THEN 1 ELSE 0 END) AS ve_hop_le,
                SUM(CASE WHEN trang_thai = 'da-checkin' THEN 1 ELSE 0 END) AS da_checkin,
                SUM(CASE WHEN trang_thai = 'da-huy' THEN 1 ELSE 0 END) AS ve_huy
            FROM ve
            GROUP BY su_kien_id
        ) v ON v.su_kien_id = s.id
        ORDER BY v.ve_hop_le DESC, s.id DESC
    ");
    $eventStats = $stmtEvStats->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $eventStats = [];
}

// -------------------- 3. DANH SÁCH CHI TIẾT VÉ & NGƯỜI THAM DỰ --------------------
$where  = ["1=1"];
$params = [];

if ($filter_su_kien > 0) {
    $where[]              = "v.su_kien_id = :su_kien_id";
    $params['su_kien_id'] = $filter_su_kien;
}
if ($filter_trang_thai !== '') {
    $where[]              = "v.trang_thai = :trang_thai";
    $params['trang_thai'] = $filter_trang_thai;
}
if ($keyword !== '') {
    $where[]       = "(v.ma_ve LIKE :kw1 OR nd.ho_ten LIKE :kw2 OR nd.email LIKE :kw3 OR nd.phone LIKE :kw4)";
    $like          = '%' . $keyword . '%';
    $params['kw1'] = $like;
    $params['kw2'] = $like;
    $params['kw3'] = $like;
    $params['kw4'] = $like;
}

try {
    $sqlOrders = "
        SELECT 
            v.*,
            s.ten_su_kien,
            nd.ho_ten,
            nd.email,
            nd.phone
        FROM ve v
        LEFT JOIN sukien s ON v.su_kien_id = s.id
        LEFT JOIN nguoidung nd ON v.nguoi_dung_id = nd.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY v.id DESC
    ";
    $stmt = $pdo->prepare($sqlOrders);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
    $error  = 'Lỗi tải danh sách vé: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <title>Quản lý Đơn hàng & Người tham dự - TicketBox Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">TicketBox Admin</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li><a href="events.php"><i class="fa-solid fa-calendar-days"></i> Quản lý Sự kiện</a></li>
            <li><a href="users.php"><i class="fa-solid fa-users"></i> Quản lý Người dùng</a></li>
            <li class="active"><a href="orders.php"><i class="fa-solid fa-ticket"></i> Quản lý Đơn hàng/Vé</a></li>
            <li><a href="../pages/home.php" target="_blank"><i class="fa-solid fa-globe"></i> Xem Website chính</a></li>
        </ul>
    </div>

    <div class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <button type="button" class="mobile-menu-toggle" id="sidebarToggle" aria-label="Mở menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h3>Quản lý Đơn hàng, Vé & Người tham dự</h3>
            </div>
            <div class="admin-user-info">
                <span><i class="fa-solid fa-user-shield"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['admin']['ho_ten']); ?></span>
                <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </header>

        <div class="admin-content">
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Thẻ thống kê nhanh -->
            <div class="dashboard-cards">
                <div class="card-stat">
                    <div class="card-stat-icon icon-green"><i class="fa-solid fa-ticket"></i></div>
                    <div class="card-stat-info">
                        <h3><?php echo (int)($kpi['tong_hop_le'] ?? 0); ?></h3>
                        <p>Tổng vé hợp lệ đã đặt</p>
                    </div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-icon icon-blue"><i class="fa-solid fa-user-check"></i></div>
                    <div class="card-stat-info">
                        <h3><?php echo (int)($kpi['da_checkin'] ?? 0); ?></h3>
                        <p>Đã Check-in (Tham dự thực tế)</p>
                    </div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-icon icon-amber"><i class="fa-solid fa-clock"></i></div>
                    <div class="card-stat-info">
                        <h3><?php echo (int)($kpi['chua_checkin'] ?? 0); ?></h3>
                        <p>Chờ Check-in</p>
                    </div>
                </div>
            </div>

            <!-- Bảng 1: Theo dõi số lượng người tham dự theo từng sự kiện -->
            <div class="card-table">
                <h3><i class="fa-solid fa-chart-column"></i> Thống kê số lượng người tham dự theo sự kiện</h3>
                <div style="max-height: 320px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên sự kiện</th>
                                <th>Đã đặt / Sức chứa</th>
                                <th>Đã Check-in</th>
                                <th>Đã hủy</th>
                                <th>Tỷ lệ lấp đầy</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($eventStats) > 0): ?>
                                <?php foreach ($eventStats as $ev): ?>
                                    <?php
                                        $sucChua = (int)$ev['suc_chua'];
                                        $hopLe   = (int)$ev['ve_hop_le'];
                                        $tyLe    = $sucChua > 0 ? min(100, round(($hopLe / $sucChua) * 100, 1)) : 0;
                                    ?>
                                    <tr>
                                        <td>#<?php echo (int)$ev['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($ev['ten_su_kien']); ?></strong></td>
                                        <td><strong><?php echo $hopLe; ?></strong> / <?php echo $sucChua; ?> vé</td>
                                        <td><span class="badge badge-admin"><?php echo (int)$ev['da_checkin']; ?> người</span></td>
                                        <td><?php echo (int)$ev['ve_huy']; ?></td>
                                        <td><?php echo $tyLe; ?>% (còn <?php echo max(0, $sucChua - $hopLe); ?>)</td>
                                        <td>
                                            <a href="orders.php?su_kien_id=<?php echo (int)$ev['id']; ?>#danh-sach-ve" class="btn-action btn-edit">
                                                <i class="fa-solid fa-users-viewfinder"></i> Xem khách
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="empty-row">Chưa có dữ liệu sự kiện.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bảng 2: Bộ lọc & Danh sách chi tiết vé -->
            <div class="card-table" id="danh-sach-ve">
                <h3><i class="fa-solid fa-list-check"></i> Danh sách vé & trạng thái tham dự (<?php echo count($orders); ?>)</h3>

                <form method="GET" action="orders.php#danh-sach-ve" style="margin-bottom: 16px;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Lọc theo sự kiện</label>
                            <select name="su_kien_id">
                                <option value="0">-- Tất cả sự kiện --</option>
                                <?php foreach ($eventStats as $ev): ?>
                                    <option value="<?php echo (int)$ev['id']; ?>" <?php echo ($filter_su_kien === (int)$ev['id']) ? 'selected' : ''; ?>>
                                        #<?php echo (int)$ev['id']; ?> - <?php echo htmlspecialchars($ev['ten_su_kien']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái vé</label>
                            <select name="trang_thai">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="thanh-cong" <?php echo ($filter_trang_thai === 'thanh-cong') ? 'selected' : ''; ?>>Đã đặt (Chờ check-in)</option>
                                <option value="da-checkin" <?php echo ($filter_trang_thai === 'da-checkin') ? 'selected' : ''; ?>>Đã Check-in</option>
                                <option value="da-huy" <?php echo ($filter_trang_thai === 'da-huy') ? 'selected' : ''; ?>>Đã hủy</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tìm kiếm</label>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Mã vé, tên khách, email, SĐT...">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit"><i class="fa-solid fa-filter"></i> Lọc danh sách</button>
                        <a href="orders.php#danh-sach-ve" class="btn-cancel"><i class="fa-solid fa-rotate-left"></i> Đặt lại</a>
                    </div>
                </form>

                <table class="table-orders">
                    <thead>
                        <tr>
                            <th>Mã vé</th>
                            <th>Người tham dự</th>
                            <th>Sự kiện</th>
                            <th>Loại vé / Giá</th>
                            <th>Ngày đặt</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $ord): ?>
                                <?php $tt = $ord['trang_thai'] ?? 'thanh-cong'; ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($ord['ma_ve'] ?? ('#' . $ord['id'])); ?></strong><br>
                                        <small>#<?php echo (int)$ord['id']; ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($ord['ho_ten'] ?? 'N/A'); ?></strong><br>
                                        <small><?php echo htmlspecialchars($ord['email'] ?? ''); ?> <?php echo !empty($ord['phone']) ? '• ' . htmlspecialchars($ord['phone']) : ''; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($ord['ten_su_kien'] ?? ('Sự kiện #' . $ord['su_kien_id'])); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($ord['loai_ve'] ?? 'Vé tiêu chuẩn'); ?><br>
                                        <small><?php echo htmlspecialchars($ord['gia_ve'] ?? '0 đ'); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($ord['ngay_dat'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($tt === 'da-checkin'): ?>
                                            <span class="badge badge-admin"><i class="fa-solid fa-circle-check"></i> Đã Check-in</span>
                                        <?php elseif ($tt === 'da-huy'): ?>
                                            <span class="badge" style="background:#fee2e2;color:#b91c1c;"><i class="fa-solid fa-ban"></i> Đã hủy</span>
                                        <?php else: ?>
                                            <span class="badge badge-user"><i class="fa-solid fa-ticket"></i> Đã đặt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="orders.php?<?php echo htmlspecialchars(http_build_query($_GET)); ?>#danh-sach-ve" style="display:inline-flex; gap:6px; flex-wrap:wrap;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="ve_id" value="<?php echo (int)$ord['id']; ?>">
                                            <?php if ($tt === 'thanh-cong'): ?>
                                                <button type="submit" name="trang_thai" value="da-checkin" class="btn-action btn-edit" style="border:none;cursor:pointer;">
                                                    <i class="fa-solid fa-check"></i> Check-in
                                                </button>
                                                <button type="submit" name="trang_thai" value="da-huy" class="btn-action btn-delete" style="border:none;cursor:pointer;" onclick="return confirm('Xác nhận hủy vé này?')">
                                                    <i class="fa-solid fa-xmark"></i> Hủy
                                                </button>
                                            <?php elseif ($tt === 'da-checkin'): ?>
                                                <button type="submit" name="trang_thai" value="thanh-cong" class="btn-action btn-edit" style="border:none;cursor:pointer;">
                                                    <i class="fa-solid fa-rotate-left"></i> Hoàn tác
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="trang_thai" value="thanh-cong" class="btn-action btn-edit" style="border:none;cursor:pointer;">
                                                    <i class="fa-solid fa-arrow-rotate-right"></i> Khôi phục
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-row">Không tìm thấy vé nào phù hợp.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>