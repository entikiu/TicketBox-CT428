<?php
/**
 * TicketBox – Trang chỉnh sửa hồ sơ người dùng
 * Dark theme (#18191a) – form nằm chính giữa, rộng vừa phải.
 *
 * UI (dựa trên @image_5.png) bao gồm:
 *   • Avatar tròn xanh đậm, chữ cái đầu và biểu tượng camera để tải ảnh mới.
 *   • Thông báo “Cung cấp thông tin chính xác …” (mờ dưới avatar).
 *   • Các trường nhập: Họ và tên, Số điện thoại (+84 dropdown), Email,
 *     Ngày sinh, Giới tính (radio).
 *   • Nút “Hoàn thành” màu xanh lá rực rỡ.
 *
 * Khi người dùng nhấn “Hoàn thành”, dữ liệu được gửi bằng AJAX tới
 *   ticketbox/api/auth/update-profile.php
 *   – cập nhật DB bằng Prepared Statements.
 *   – nếu có file ảnh tải lên, sẽ lưu vào assets/uploads/avatars/
 *   – thành công → hiển thị toast “Cập nhật thành công!” và
 *     tự động chuyển hướng về ticketbox/pages/profile.php.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}
$userId   = (int)$_SESSION['user']['id'];
$userName = $_SESSION['user']['ho_ten'] ?? $_SESSION['user']['name'] ?? 'Người dùng';
$userAvt  = $_SESSION['user']['avatar'] ?? '';
$userEmail= $_SESSION['user']['email'] ?? '';
$userPhone= $_SESSION['user']['phone'] ?? '';
$userDob  = $_SESSION['user']['dob'] ?? '';
$userGender = $_SESSION['user']['gender'] ?? 'nam';

$firstChar = mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');
?>
<?php include '../templates/header.php'; ?>
<?php include '../templates/navbar.php'; ?>

<main class="edit-profile-page" style="background:#18191a; min-height:80vh; padding:40px 0;">
    <div class="container" style="max-width:720px; margin:auto; color:#fff;">

        <!-- Avatar & Camera Icon -->
        <section class="avatar-section" style="text-align:center; margin-bottom:20px;">
            <div class="avatar-wrapper" style="position:relative; display:inline-block;">
                <?php if (!empty($userAvt)): ?>
                    <img id="avatarImg"
                         src="<?php echo htmlspecialchars($userAvt); ?>?v=<?php echo time(); ?>"
                         alt="Avatar"
                         style="width:130px;height:130px;border-radius:50%;
                                object-fit:cover; background:#26c973;">
                <?php else: ?>
                    <span id="avatarImg"
                          style="display:inline-flex; width:130px; height:130px;
                                 align-items:center; justify-content:center;
                                 background:#26c973; color:#fff; font-size:48px;
                                 border-radius:50;"><?php echo $firstChar; ?></span>
                <?php endif; ?>

                <!-- Camera upload button -->
                <label for="avatarUpload"
                       title="Thay đổi ảnh đại diện"
                       style="position:absolute; right:-5px; bottom:-5px;
                              background:#2b2f3e; color:#26c973;
                              width:32px; height:32px; border-radius:50%;
                              display:flex; align-items:center; justify-content:center;
                              cursor:pointer;">
                    <i class="fa-solid fa-camera"></i>
                </label>
                <input id="avatarUpload"
                       type="file"
                       accept="image/*"
                       style="display:none;">
            </div>

            <p style="margin-top:10px; color:#777; font-size:14px;">
                Cung cấp thông tin chính xác sẽ hỗ trợ bạn trong quá trình mua vé,
                hoặc khi cần xác thực vé
            </p>
        </section>

        <!-- Form -->
        <form id="editProfileForm" enctype="multipart/form-data"
              style="background:#272730; padding:30px; border-radius:8px;">

            <!-- Họ và tên -->
            <div class="form-group" style="margin-bottom:15px;">
                <label for="hoTen" style="display:block; margin-bottom:5px;">Họ và tên</label>
                <input type="text" id="hoTen" name="ho_ten"
                       value="<?php echo htmlspecialchars($userName); ?>"
                       class="form-control"
                       style="width:100%; padding:8px; background:#18191a; color:#fff;
                              border:1px solid #444; border-radius:4px;">
            </div>

            <!-- Số điện thoại -->
            <div class="form-group" style="margin-bottom:15px; display:flex; align-items:center;">
                <label style="margin-right:10px;">Số điện thoại</label>
                <select name="country_code" id="countryCode"
                        style="padding:8px; background:#18191a; color:#fff;
                               border:1px solid #444; border-radius:4px;">
                    <option value="+84" selected>+84</option>
                </select>
                <div style="position:relative; flex:1; margin-left:8px;">
                    <input type="text" id="phone" name="phone"
                           value="<?php echo htmlspecialchars($userPhone); ?>"
                           class="form-control"
                           style="width:100%; padding:8px 30px 8px 8px;
                                  background:#18191a; color:#fff;
                                  border:1px solid #444; border-radius:4px;">
                    <span id="clearPhone"
                          style="position:absolute; right:8px; top:50%; transform:translateY(-50%);
                                 cursor:pointer; color:#777;">✕</span>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group" style="margin-bottom:15px; position:relative;">
                <label for="email" style="display:block; margin-bottom:5px;">Email</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($userEmail); ?>"
                       class="form-control"
                       readonly
                       style="width:100%; padding:8px; background:#18191a; color:#777;
                              border:1px solid #444; border-radius:4px;">
                <i class="fa-solid fa-circle-check"
                   style="position:absolute; right:10px; top:38px; color:#26c973;"></i>
            </div>

            <!-- Ngày sinh -->
            <div class="form-group" style="margin-bottom:15px;">
                <label for="dob" style="display:block; margin-bottom:5px;">Ngày sinh</label>
                <input type="date" id="dob" name="dob"
                       value="<?php echo !empty($userDob) ? htmlspecialchars($userDob) : ''; ?>"
                       class="form-control"
                       style="width:100%; padding:8px; background:#18191a; color:#fff;
                              border:1px solid #444; border-radius:4px;">
            </div>

            <!-- Giới tính -->
            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:5px;">Giới tính</label>
                <?php
                $genders = ['nam' => 'Nam', 'nu' => 'Nữ', 'khac' => 'Khác'];
                foreach ($genders as $val => $label):
                    $checked = ($userGender === $val) ? 'checked' : '';
                ?>
                    <label style="margin-right:15px; color:#fff;">
                        <input type="radio" name="gender" value="<?php echo $val; ?>" <?php echo $checked; ?>>
                        <?php echo $label; ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Nút Hoàn thành -->
            <button type="submit"
                    style="width:100%; padding:12px; background:#26c973; color:#fff;
                           border:none; border-radius:6px; font-size:16px;
                           font-weight:600; cursor:pointer;">
                Hoàn thành
            </button>
        </form>
    </div>
</main>

<?php include '../templates/footer.php'; ?>

<script>
// Preview avatar khi người dùng chọn file
document.getElementById('avatarUpload').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (ev) {
        const img = document.getElementById('avatarImg');
        if (img.tagName.toLowerCase() === 'img') {
            img.src = ev.target.result;
        } else {
            // replace the <span> with an <img>
            const newImg = document.createElement('img');
            newImg.id = 'avatarImg';
            newImg.src = ev.target.result;
            newImg.style = 'width:130px;height:130px;border-radius:50%;object-fit:cover;background:#26c973;';
            img.parentNode.replaceChild(newImg, img);
        }
    };
    reader.readAsDataURL(file);
});

// Xóa nội dung số điện thoại nhanh
document.getElementById('clearPhone').addEventListener('click', function () {
    document.getElementById('phone').value = '';
});

// AJAX submit
document.getElementById('editProfileForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    // Append avatar file if selected
    const avatarFile = document.getElementById('avatarUpload').files[0];
    if (avatarFile) {
        formData.append('avatar', avatarFile);
    }

    fetch('../api/auth/update-profile.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert('Cập nhật thành công!');
            window.location.href = 'profile.php';
        } else if (res.errors) {
            alert('Có lỗi: ' + JSON.stringify(res.errors));
        } else {
            alert(res.error || 'Lỗi không xác định');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Không thể kết nối tới máy chủ.');
    });
});
</script>