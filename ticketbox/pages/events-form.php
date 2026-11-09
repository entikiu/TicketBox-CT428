<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// 1. Bắt buộc đăng nhập mới được truy cập trang tạo sự kiện
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success_msg = '';

// 2. Xử lý lưu sự kiện khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_su_kien     = trim($_POST['ten_su_kien'] ?? '');
    $dia_diem        = trim($_POST['dia_diem'] ?? '');
    $thoi_gian       = $_POST['thoi_gian'] ?? '';
    $so_luong_toi_da = (int)($_POST['so_luong_toi_da'] ?? 0);
    $mo_ta           = trim($_POST['mo_ta'] ?? '');

    // Validation cơ bản
    if (empty($ten_su_kien)) {
        $errors['ten_su_kien'] = 'Vui lòng nhập tên sự kiện.';
    }
    if (empty($dia_diem)) {
        $errors['dia_diem'] = 'Vui lòng nhập địa điểm tổ chức.';
    }
    if (empty($thoi_gian)) {
        $errors['thoi_gian'] = 'Vui lòng chọn thời gian diễn ra.';
    }
    if ($so_luong_toi_da <= 0) {
        $errors['so_luong_toi_da'] = 'Số lượng vé tối đa phải lớn hơn 0.';
    }

    // Nếu không có lỗi -> Lưu vào CSDL
    if (empty($errors)) {
        $nguoi_tao = $_SESSION['user']['id'] ?? 1;

        if (isset($pdo)) {
            try {
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
                $success_msg = 'Tạo sự kiện mới thành công!';
            } catch (PDOException $e) {
                $errors['general'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
            }
        } else {
            $success_msg = 'Tạo sự kiện mới thành công (chế độ Demo Session)!';
        }
    }
}

include '../templates/header.php';
?>

<main class="container" style="margin-top: 35px; min-height: 75vh; max-width: 800px;">
    <div style="background: var(--card-bg, #1e1e24); border: 1px solid var(--border-color, #2d2d35); border-radius: 16px; padding: 32px; color: #ffffff;">
        
        <h2 style="font-size: 22px; font-weight: 700; margin-bottom: 25px; color: var(--primary-color, #26c973); display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-calendar-plus"></i> Tạo sự kiện mới
        </h2>

        <?php if (!empty($success_msg)): ?>
            <div style="background: rgba(38, 201, 115, 0.15); border: 1px solid #26c973; color: #26c973; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
                <i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?>
                <a href="events.php" style="color: #fff; text-decoration: underline; margin-left: 10px;">Xem danh sách sự kiện</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div style="background: rgba(255, 77, 79, 0.15); border: 1px solid #ff4d4f; color: #ff4d4f; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>

        <form action="events-form.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Tên sự kiện -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Tên sự kiện <span style="color: #ff4d4f;">*</span></label>
                <input type="text" name="ten_su_kien" value="<?php echo htmlspecialchars($_POST['ten_su_kien'] ?? ''); ?>" placeholder="Nhập tên sự kiện" style="width: 100%; padding: 12px 15px; background: #272730; border: 1px solid #3a3a45; border-radius: 8px; color: #fff; outline: none;">
                <?php if (!empty($errors['ten_su_kien'])): ?>
                    <span style="color: #ff4d4f; font-size: 12px; margin-top: 5px; display: block;"><?php echo $errors['ten_su_kien']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Địa điểm & Thời gian -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Địa điểm tổ chức <span style="color: #ff4d4f;">*</span></label>
                    <input type="text" name="dia_diem" value="<?php echo htmlspecialchars($_POST['dia_diem'] ?? ''); ?>" placeholder="VD: SECC Quận 7, TP.HCM" style="width: 100%; padding: 12px 15px; background: #272730; border: 1px solid #3a3a45; border-radius: 8px; color: #fff; outline: none;">
                    <?php if (!empty($errors['dia_diem'])): ?>
                        <span style="color: #ff4d4f; font-size: 12px; margin-top: 5px; display: block;"><?php echo $errors['dia_diem']; ?></span>
                    <?php endif; ?>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Thời gian diễn ra <span style="color: #ff4d4f;">*</span></label>
                    <input type="datetime-local" name="thoi_gian" value="<?php echo htmlspecialchars($_POST['thoi_gian'] ?? ''); ?>" style="width: 100%; padding: 12px 15px; background: #272730; border: 1px solid #3a3a45; border-radius: 8px; color: #fff; outline: none;">
                    <?php if (!empty($errors['thoi_gian'])): ?>
                        <span style="color: #ff4d4f; font-size: 12px; margin-top: 5px; display: block;"><?php echo $errors['thoi_gian']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Số lượng vé tối đa -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Số lượng vé tối đa <span style="color: #ff4d4f;">*</span></label>
                <input type="number" name="so_luong_toi_da" min="1" value="<?php echo htmlspecialchars($_POST['so_luong_toi_da'] ?? '100'); ?>" placeholder="Nhập số lượng vé" style="width: 100%; padding: 12px 15px; background: #272730; border: 1px solid #3a3a45; border-radius: 8px; color: #fff; outline: none;">
                <?php if (!empty($errors['so_luong_toi_da'])): ?>
                    <span style="color: #ff4d4f; font-size: 12px; margin-top: 5px; display: block;"><?php echo $errors['so_luong_toi_da']; ?></span>
                <?php endif; ?>
            </div>

            <!-- Mô tả chi tiết -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Mô tả chi tiết sự kiện</label>
                <textarea name="mo_ta" rows="5" placeholder="Nhập thông tin mô tả chi tiết sự kiện..." style="width: 100%; padding: 12px 15px; background: #272730; border: 1px solid #3a3a45; border-radius: 8px; color: #fff; outline: none; resize: vertical;"><?php echo htmlspecialchars($_POST['mo_ta'] ?? ''); ?></textarea>
            </div>

            <!-- Nút bấm tác vụ -->
            <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 10px;">
                <a href="events.php" style="padding: 12px 24px; background: #272730; color: #a0a3bd; text-decoration: none; border-radius: 8px; font-weight: 600;">Hủy</a>
                <button type="submit" style="padding: 12px 28px; background: var(--primary-color, #26c973); color: #ffffff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">Tạo sự kiện</button>
            </div>
        </form>
    </div>
</main>

<?php include '../templates/footer.php'; ?>