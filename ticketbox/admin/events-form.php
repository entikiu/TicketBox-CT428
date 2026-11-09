<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['vai_tro'] ?? '') !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

$is_edit = false;
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$ten_su_kien = '';
$dia_diem = '';
$thoi_gian = '';
$so_luong_toi_da = 100;
$mo_ta = '';
$errors = [];
$success_msg = '';

// Nếu là chế độ Edit -> Lấy thông tin sự kiện cũ từ CSDL
if ($event_id > 0) {
    $is_edit = true;
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT * FROM SuKien WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            $ten_su_kien     = $event['ten_su_kien'];
            $dia_diem        = $event['dia_diem'];
            $thoi_gian       = date('Y-m-d\TH:i', strtotime($event['thoi_gian']));
            $so_luong_toi_da = $event['so_luong_toi_da'];
            $mo_ta           = $event['mo_ta'];
        } else {
            $errors['general'] = 'Không tìm thấy sự kiện yêu cầu.';
        }
    }
}

// Xử lý Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_su_kien     = trim($_POST['ten_su_kien'] ?? '');
    $dia_diem        = trim($_POST['dia_diem'] ?? '');
    $thoi_gian       = $_POST['thoi_gian'] ?? '';
    $so_luong_toi_da = (int)($_POST['so_luong_toi_da'] ?? 0);
    $mo_ta           = trim($_POST['mo_ta'] ?? '');

    if (empty($ten_su_kien))     $errors['ten_su_kien'] = 'Vui lòng nhập tên sự kiện.';
    if (empty($dia_diem))        $errors['dia_diem'] = 'Vui lòng nhập địa điểm.';
    if (empty($thoi_gian))       $errors['thoi_gian'] = 'Vui lòng chọn thời gian.';
    if ($so_luong_toi_da <= 0)  $errors['so_luong_toi_da'] = 'Số lượng vé phải lớn hơn 0.';

    if (empty($errors)) {
        $nguoi_tao = $_SESSION['user']['id'] ?? 1;

        if (isset($pdo)) {
            try {
                if ($is_edit) {
                    $sql = "UPDATE SuKien 
                            SET ten_su_kien = :ten_su_kien, dia_diem = :dia_diem, thoi_gian = :thoi_gian, 
                                so_luong_toi_da = :so_luong_toi_da, mo_ta = :mo_ta 
                            WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'ten_su_kien'     => $ten_su_kien,
                        'dia_diem'        => $dia_diem,
                        'thoi_gian'       => $thoi_gian,
                        'so_luong_toi_da' => $so_luong_toi_da,
                        'mo_ta'           => $mo_ta,
                        'id'              => $event_id
                    ]);
                    $success_msg = 'Cập nhật sự kiện thành công!';
                } else {
                    $sql = "INSERT INTO SuKien (ten_su_kien, mo_ta, dia_diem, thoi_gian, so_luong_toi_da, nguoi_tao) 
                            VALUES (:ten_su_kien, :mo_ta, :dia_diem, :thoi_gian, :so_luong_toi_da, :nguoi_tao)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'ten_su_kien'     => $ten_su_kien,
                        'mo_ta'           => $mo_ta,
                        'dia_diem'        => $dia_diem,
                        'thoi_gian'       => $thoi_gian,
                        'so_luong_toi_da' => $so_luong_toi_da,
                        'nguoi_tao'       => $nguoi_tao
                    ]);
                    $success_msg = 'Thêm sự kiện mới thành công!';
                }
            } catch (PDOException $e) {
                $errors['general'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
            }
        } else {
            $success_msg = 'Đã lưu thay đổi (Chế độ Mock Demo)!';
        }
    }
}

include '../templates/header.php';
?>

<main class="container" style="margin-top: 35px; min-height: 80vh; max-width: 900px;">
    <div style="background: var(--card-bg, #1e1e24); border: 1px solid var(--border-color, #2d2d35); border-radius: 16px; padding: 32px; color: #fff;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #2d2d35; padding-bottom: 15px;">
            <h2 style="font-size: 22px; font-weight: 700; color: var(--primary-color, #26c973); display: flex; align-items: center; gap: 10px; margin: 0;">
                <i class="fa-solid <?php echo $is_edit ? 'fa-pen-to-square' : 'fa-plus-circle'; ?>"></i>
                <?php echo $is_edit ? 'Chỉnh sửa sự kiện #' . $event_id : 'Thêm sự kiện mới (Admin)'; ?>
            </h2>
            <a href="events-list.php" style="color: #a0a3bd; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại quản lý
            </a>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div style="background: rgba(38, 201, 115, 0.15); border: 1px solid #26c973; color: #26c973; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
                <i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div style="background: rgba(255, 77, 79, 0.15); border: 1px solid #ff4d4f; color: #ff4d4f; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>

        <form action="event-form.php<?php echo $is_edit ? '?id=' . $event_id : ''; ?>" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Tên sự kiện <span style="color: #ff4d4f;">*</span></label>
                <input type="text" name="ten_su_kien" value="<?php echo htmlspecialchars($ten_su_kien); ?>" placeholder="Nhập tên sự kiện..." style="width: 100%; padding: 12px 15px; background: #272730; border: 1px solid #3a3a45; border-radius: 8px; color: #fff; outline: none;">
                <?php if (!empty($errors['ten_su_kien'])): ?>
                    <span style="color: #ff4d4f; font-size: 12px; margin-top: 5px; display: block;"><?php echo $errors['ten_su_kien']; ?></span>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Địa điểm <span style="color: #ff4d4f;">*</span></label>
                    <input type="text" name="dia_diem" value="<?php echo htmlspecialchars($dia_diem); ?>" placeholder="Địa điểm tổ chức" style="width: 100%; padding: 12px 15px; background: #272730; border: 1px solid #3a3a45; border-radius: 8px; color: #fff; outline: none;">
                    <?php if (!empty($errors['dia_diem'])): ?>
                        <span style="color: #ff4d4f; font-size: 12px; margin-top: 5px; display: block;"><?php echo $errors['dia_diem']; ?></span>
                    <?php endif; ?>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Thời gian tổ chức <span style="color: #ff4d4f;">*</span></label>
                    <input type="datetime-local" name="thoi_gian" value="<?php echo htmlspecialchars($thoi_gian); ?>" style="width: 100%; padding: 12px 15px; background: #272730; border: 1px solid #3a3a45; border-radius: 8px; color: #fff; outline: none;">
                    <?php if (!empty($errors['thoi_gian'])): ?>
                        <span style="color: #ff4d4f; font-size: 12px; margin-top: 5px; display: block;"><?php echo $errors['thoi_gian']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Số lượng vé tối đa <span style="color: #ff4d4f;">*</span></label>
                <input type="number" name="so_luong_toi_da" min="1" value="<?php echo htmlspecialchars((string)$so_luong_toi_da); ?>" style="width: 100%; padding: 12px 15px; background: #272730; border: 1px solid #3a3a45; border-radius: 8px; color: #fff; outline: none;">
                <?php if (!empty($errors['so_luong_toi_da'])): ?>
                    <span style="color: #ff4d4f; font-size: 12px; margin-top: 5px; display: block;"><?php echo $errors['so_luong_toi_da']; ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Mô tả sự kiện</label>
                <textarea name="mo_ta" rows="6" placeholder="Mô tả chi tiết nội dung sự kiện..." style="width: 100%; padding: 12px 15px; background: #272730; border: 1px solid #3a3a45; border-radius: 8px; color: #fff; outline: none; resize: vertical;"><?php echo htmlspecialchars($mo_ta); ?></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 10px;">
                <a href="events-list.php" style="padding: 12px 24px; background: #272730; color: #a0a3bd; text-decoration: none; border-radius: 8px; font-weight: 600;">Hủy</a>
                <button type="submit" style="padding: 12px 28px; background: var(--primary-color, #26c973); color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
                    <?php echo $is_edit ? 'Cập nhật sự kiện' : 'Lưu sự kiện'; ?>
                </button>
            </div>
        </form>
    </div>
</main>

<?php include '../templates/footer.php'; ?>