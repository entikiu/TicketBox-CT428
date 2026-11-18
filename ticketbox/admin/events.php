<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/middleware/admin.php'; // ensure admin role

$message = '';

// -------------------- XÓA SỰ KIỆN --------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $event_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM sukien WHERE id = :id");
        $stmt->execute(['id' => $event_id]);
        $message = 'Đã xóa sự kiện thành công!';
    } catch (PDOException $e) {
        $message = 'Lỗi khi xóa sự kiện: ' . $e->getMessage();
    }
}

// -------------------- LẤY DỮ LIỆU CHO CHỈNH SỬA --------------------
$editEvent = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $event_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM sukien WHERE id = :id");
        $stmt->execute(['id' => $event_id]);
        $editEvent = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = 'Không thể tải thông tin sự kiện: ' . $e->getMessage();
    }
}

// -------------------- THÊM / CẬP NHẬT SỰ KIỆN --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $ten_su_kien = trim($_POST['ten_su_kien'] ?? '');
    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $dia_diem = trim($_POST['dia_diem'] ?? '');
    $hinh_thuc = $_POST['hinh_thuc'] ?? 'offline';
    $link_online = $_POST['link_online'] ?? null;
    $gia_thap_nhat = (int)($_POST['gia_thap_nhat'] ?? 0);
    $thoi_gian = $_POST['thoi_gian'] ?? null;
    $thoi_gian_ket_thuc = $_POST['thoi_gian_ket_thuc'] ?? null;
    $thoi_gian_hien_thi = $_POST['thoi_gian_hien_thi'] ?? null;
    $so_luong_toi_da = (int)($_POST['so_luong_toi_da'] ?? 0);
    $ten_ban_to_chuc = trim($_POST['ten_ban_to_chuc'] ?? '');
    $mo_ta_ban_to_chuc = trim($_POST['mo_ta_ban_to_chuc'] ?? '');
    $danh_muc_id = (int)($_POST['danh_muc_id'] ?? 1);
    $trang_thai = $_POST['trang_thai'] ?? 'cong_khai';

    try {
        if ($id > 0) {
            // Cập nhật
            $stmt = $pdo->prepare("UPDATE sukien SET ten_su_kien = :ten_su_kien, mo_ta = :mo_ta, dia_diem = :dia_diem, hinh_thuc = :hinh_thuc, link_online = :link_online, gia_thap_nhat = :gia_thap_nhat, thoi_gian = :thoi_gian, thoi_gian_ket_thuc = :thoi_gian_ket_thuc, thoi_gian_hien_thi = :thoi_gian_hien_thi, so_luong_toi_da = :so_luong_toi_da, ten_ban_to_chuc = :ten_ban_to_chuc, mo_ta_ban_to_chuc = :mo_ta_ban_to_chuc, danh_muc_id = :danh_muc_id, trang_thai = :trang_thai WHERE id = :id");
            $stmt->execute([
                'id' => $id,
                'ten_su_kien' => $ten_su_kien,
                'mo_ta' => $mo_ta,
                'dia_diem' => $dia_diem,
                'hinh_thuc' => $hinh_thuc,
                'link_online' => $link_online,
                'gia_thap_nhat' => $gia_thap_nhat,
                'thoi_gian' => $thoi_gian,
                'thoi_gian_ket_thuc' => $thoi_gian_ket_thuc,
                'thoi_gian_hien_thi' => $thoi_gian_hien_thi,
                'so_luong_toi_da' => $so_luong_toi_da,
                'ten_ban_to_chuc' => $ten_ban_to_chuc,
                'mo_ta_ban_to_chuc' => $mo_ta_ban_to_chuc,
                'danh_muc_id' => $danh_muc_id,
                'trang_thai' => $trang_thai,
            ]);
            $message = 'Cập nhật sự kiện thành công!';
        } else {
            // Thêm mới
            $stmt = $pdo->prepare("INSERT INTO sukien (nguoi_tao_id, ten_su_kien, mo_ta, dia_diem, hinh_thuc, link_online, gia_thap_nhat, thoi_gian, thoi_gian_ket_thuc, thoi_gian_hien_thi, so_luong_toi_da, nguoi_tao, ten_ban_to_chuc, mo_ta_ban_to_chuc, danh_muc_id, trang_thai) VALUES (:nguoi_tao_id, :ten_su_kien, :mo_ta, :dia_diem, :hinh_thuc, :link_online, :gia_thap_nhat, :thoi_gian, :thoi_gian_ket_thuc, :thoi_gian_hien_thi, :so_luong_toi_da, :nguoi_tao, :ten_ban_to_chuc, :mo_ta_ban_to_chuc, :danh_muc_id, :trang_thai)");
            $stmt->execute([
                'nguoi_tao_id' => $_SESSION['admin']['id'] ?? null,
                'ten_su_kien' => $ten_su_kien,
                'mo_ta' => $mo_ta,
                'dia_diem' => $dia_diem,
                'hinh_thuc' => $hinh_thuc,
                'link_online' => $link_online,
                'gia_thap_nhat' => $gia_thap_nhat,
                'thoi_gian' => $thoi_gian,
                'thoi_gian_ket_thuc' => $thoi_gian_ket_thuc,
                'thoi_gian_hien_thi' => $thoi_gian_hien_thi,
                'so_luong_toi_da' => $so_luong_toi_da,
                'nguoi_tao' => $_SESSION['admin']['id'] ?? null,
                'ten_ban_to_chuc' => $ten_ban_to_chuc,
                'mo_ta_ban_to_chuc' => $mo_ta_ban_to_chuc,
                'danh_muc_id' => $danh_muc_id,
                'trang_thai' => $trang_thai,
            ]);
            $message = 'Thêm sự kiện mới thành công!';
        }
    } catch (PDOException $e) {
        $message = 'Lỗi khi lưu sự kiện: ' . $e->getMessage();
    }
}

// -------------------- LẤY DỮ LIỆU SỰ KIỆN --------------------
try {
    $stmt = $pdo->query("SELECT * FROM sukien ORDER BY id DESC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $events = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <title>Quản lý Sự kiện - TicketBox Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">TicketBox Admin</div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
            <li class="active"><a href="events.php"><i class="fa-solid fa-calendar-days"></i> Quản lý Sự kiện</a></li>
            <li><a href="users.php"><i class="fa-solid fa-users"></i> Quản lý Người dùng</a></li>
            <li><a href="orders.php"><i class="fa-solid fa-ticket"></i> Quản lý Đơn hàng/Vé</a></li>
            <li><a href="../pages/home.php" target="_blank"><i class="fa-solid fa-globe"></i> Xem Website chính</a></li>
        </ul>
    </div>
    <div class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <button type="button" class="mobile-menu-toggle" id="sidebarToggle" aria-label="Mở menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h3>Quản lý Sự kiện</h3>
            </div>
            <div class="admin-user-info">
                <span><i class="fa-solid fa-user-shield"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['admin']['ho_ten']); ?></span>
                <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </header>
        <div class="admin-content">
            <?php if (!empty($message)): ?>
                <div class="alert"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <!-- Form Thêm / Sửa -->
            <div class="card-table">
                <h3><?php echo $editEvent ? 'Chỉnh sửa sự kiện' : 'Thêm mới sự kiện'; ?></h3>
                <form method="post" action="events.php<?php echo $editEvent ? '?action=edit&id=' . $editEvent['id'] : ''; ?>">
                    <?php if ($editEvent): ?>
                        <input type="hidden" name="id" value="<?php echo $editEvent['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group"><label>Tên sự kiện</label><input type="text" name="ten_su_kien" required value="<?php echo htmlspecialchars($editEvent['ten_su_kien'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Mô tả</label><textarea name="mo_ta"><?php echo htmlspecialchars($editEvent['mo_ta'] ?? ''); ?></textarea></div>
                    <div class="form-group"><label>Địa điểm</label><input type="text" name="dia_diem" value="<?php echo htmlspecialchars($editEvent['dia_diem'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Hình thức</label><select name="hinh_thuc"><option value="offline" <?php echo (isset($editEvent['hinh_thuc']) && $editEvent['hinh_thuc'] === 'offline') ? 'selected' : ''; ?>>Offline</option><option value="online" <?php echo (isset($editEvent['hinh_thuc']) && $editEvent['hinh_thuc'] === 'online') ? 'selected' : ''; ?>>Online</option></select></div>
                    <div class="form-group"><label>Link online (nếu có)</label><input type="url" name="link_online" value="<?php echo htmlspecialchars($editEvent['link_online'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Giá tối thiểu</label><input type="number" name="gia_thap_nhat" min="0" value="<?php echo $editEvent['gia_thap_nhat'] ?? 0; ?>"></div>
                    <div class="form-group"><label>Thời gian (datetime-local)</label><input type="datetime-local" name="thoi_gian" value="<?php echo $editEvent['thoi_gian'] ? date('Y-m-d\TH:i', strtotime($editEvent['thoi_gian'])) : ''; ?>"></div>
                    <div class="form-group"><label>Thời gian kết thúc</label><input type="datetime-local" name="thoi_gian_ket_thuc" value="<?php echo $editEvent['thoi_gian_ket_thuc'] ? date('Y-m-d\TH:i', strtotime($editEvent['thoi_gian_ket_thuc'])) : ''; ?>"></div>
                    <div class="form-group"><label>Thời gian hiển thị (chuỗi)</label><input type="text" name="thoi_gian_hien_thi" value="<?php echo htmlspecialchars($editEvent['thoi_gian_hien_thi'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Số lượng tối đa</label><input type="number" name="so_luong_toi_da" min="0" value="<?php echo $editEvent['so_luong_toi_da'] ?? 0; ?>"></div>
                    <div class="form-group"><label>Tên ban tổ chức</label><input type="text" name="ten_ban_to_chuc" value="<?php echo htmlspecialchars($editEvent['ten_ban_to_chuc'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Mô tả ban tổ chức</label><textarea name="mo_ta_ban_to_chuc"><?php echo htmlspecialchars($editEvent['mo_ta_ban_to_chuc'] ?? ''); ?></textarea></div>
                    <div class="form-group"><label>Danh mục ID</label><input type="number" name="danh_muc_id" min="1" value="<?php echo $editEvent['danh_muc_id'] ?? 1; ?>"></div>
                    <div class="form-group"><label>Trạng thái</label><select name="trang_thai"><option value="cong_khai" <?php echo (isset($editEvent['trang_thai']) && $editEvent['trang_thai'] === 'cong_khai') ? 'selected' : ''; ?>>Công khai</option><option value="nhap" <?php echo (isset($editEvent['trang_thai']) && $editEvent['trang_thai'] === 'nhap') ? 'selected' : ''; ?>>Nháp</option><option value="da_huy" <?php echo (isset($editEvent['trang_thai']) && $editEvent['trang_thai'] === 'da_huy') ? 'selected' : ''; ?>>Đã hủy</option></select></div>
                    <button type="submit" class="btn-submit"><?php echo $editEvent ? 'Cập nhật' : 'Thêm mới'; ?></button>
                </form>
            </div>
            <!-- Danh sách sự kiện -->
            <div class="card-table">
                <h3>Danh sách sự kiện trên hệ thống (<?php echo count($events); ?>)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên sự kiện</th>
                            <th>Địa điểm</th>
                            <th>Thời gian</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($events) > 0): ?>
                            <?php foreach ($events as $ev): ?>
                                <tr>
                                    <td>#<?php echo $ev['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($ev['ten_su_kien'] ?? $ev['title'] ?? 'Sự kiện'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($ev['dia_diem'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($ev['thoi_gian'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="events.php?action=edit&id=<?php echo $ev['id']; ?>" class="btn-action btn-edit"><i class="fa-solid fa-pen"></i> Sửa</a>
                                        <a href="events.php?action=delete&id=<?php echo $ev['id']; ?>" class="btn-action btn-delete" data-confirm="Bạn có chắc chắn muốn xóa sự kiện này?"><i class="fa-solid fa-trash-can"></i> Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="empty-row">Chưa có sự kiện nào được tạo.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>