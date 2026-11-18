<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Bắt buộc đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_name  = $_SESSION['user']['ho_ten'] ?? $_SESSION['user']['name'] ?? '';
$user_phone = $_SESSION['user']['phone'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_type = $_POST['item_type'] ?? 'event';

    if ($item_type === 'membership') {
        $membership_id = (int)($_POST['membership_id'] ?? 0);
        $stmtMem = $pdo->prepare("SELECT * FROM the_thanh_vien WHERE id = :id LIMIT 1");
        $stmtMem->execute(['id' => $membership_id]);
        $memRow = $stmtMem->fetch(PDO::FETCH_ASSOC);

        if (!$memRow) {
            header('Location: home.php');
            exit;
        }

        $image = (strpos($memRow['hinh_anh'], '../') === 0) ? $memRow['hinh_anh'] : '../' . ltrim($memRow['hinh_anh'], './');

        $_SESSION['checkout_data'] = [
            'item_type'     => 'membership',
            'membership_id' => $membership_id,
            'su_kien_id'    => 0,
            'event_title'   => 'Thẻ thành viên: ' . $memRow['ten_the'],
            'time'          => 'Thời hạn hiệu lực: ' . $memRow['thoi_han_thang'] . ' tháng',
            'location'      => 'Đơn vị phát hành: ' . $memRow['nghe_si'],
            'image'         => $image,
            'category'      => 'Gói ' . $memRow['thoi_han_thang'] . ' tháng',
            'price'         => number_format((int)$memRow['gia'], 0, ',', '.') . ' đ'
        ];
    } else {
       $su_kien_id = (int)($_POST['su_kien_id'] ?? $_POST['event_id'] ?? $_POST['id'] ?? 0);

    $event_title = 'Sự kiện Ticketbox';
    $time        = '';
    $location    = '';
    $image       = '../assets/images/events/default.jpg';

    if (isset($pdo) && $su_kien_id > 0) {
        $stmt = $pdo->prepare("SELECT ten_su_kien, thoi_gian, thoi_gian_ket_thuc, dia_diem, hinh_anh FROM sukien WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $su_kien_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {

            $refTime = !empty($row['thoi_gian_ket_thuc']) ? $row['thoi_gian_ket_thuc'] : $row['thoi_gian'];
            if (!empty($refTime) && time() > strtotime($refTime)) {
                header('Location: event-detail.php?id=' . $su_kien_id);
                exit;
            }

            $event_title = $row['ten_su_kien'];
            $time        = date('H:i - d/m/Y', strtotime($row['thoi_gian']));
            $location    = $row['dia_diem'];
            if (!empty($row['hinh_anh'])) {
                $image = (strpos($row['hinh_anh'], '../') === 0) ? $row['hinh_anh'] : '../' . ltrim($row['hinh_anh'], './');
            }
        }
    }
        $_SESSION['checkout_data'] = [
            'item_type'     => 'event',
            'membership_id' => 0,
            'su_kien_id'    => $su_kien_id,
            'event_title'   => $event_title,
            'time'          => $time,
            'location'      => $location,
            'image'         => $image,
            'category'      => $_POST['category'] ?? $_POST['loai_ve'] ?? 'Vé Tiêu Chuẩn',
            'price'         => $_POST['price'] ?? $_POST['gia_ve'] ?? '500.000 đ'
        ];
    }

    header('Location: checkout.php');
    exit;
}

if (!isset($_SESSION['checkout_data'])) {
    header('Location: home.php');
    exit;
}

$chk_data    = $_SESSION['checkout_data'];
$item_type     = $chk_data['item_type'] ?? 'event';
$membership_id = $chk_data['membership_id'] ?? 0;
$event_id    = $chk_data['su_kien_id'];
$event_title = $chk_data['event_title'];
$time        = $chk_data['time'];
$location    = $chk_data['location'];
$category    = $chk_data['category'];
$price       = $chk_data['price'];
$image       = $chk_data['image'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Ticketbox</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/checkout.css">
    <style>

    </style>
</head>

<body>
    <header class="checkout-header">
        <a href="home.php" class="logo">ticketbox</a>
        <?php
        $headerUserName   = $_SESSION['user']['ho_ten'] ?? $_SESSION['user']['name'] ?? 'Tài khoản';
        $headerUserAvatar = $_SESSION['user']['avatar'] ?? '';
        ?>

        <div class="header-account" style="display: flex; align-items: center; gap: 8px; color: #fff; font-weight: 600; cursor: pointer;">
            <?php if (!empty($headerUserAvatar) && file_exists(__DIR__ . '/' . $headerUserAvatar)): ?>
                <img src="<?php echo htmlspecialchars($headerUserAvatar); ?>"
                    alt="<?php echo htmlspecialchars($headerUserName); ?>"
                    style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 1.5px solid #fff;">
            <?php else: ?>
                <i class="fa-solid fa-circle-user" style="font-size: 22px;"></i>
            <?php endif; ?>
            <span><?php echo htmlspecialchars($headerUserName); ?></span>
            <i class="fa-solid fa-caret-down" style="font-size: 12px;"></i>
        </div>
    </header>

    <div class="event-banner" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.9)), url('<?php echo htmlspecialchars($image); ?>') center/cover;">
        <div class="event-info">
            <h1><?php echo htmlspecialchars($event_title); ?></h1>
            <p><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($time); ?></p>
            <p><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($location); ?></p>
        </div>
        <div class="timer-box">
            <p>Hoàn tất đặt vé trong</p>
            <div class="time-blocks">
                <span class="box" id="min">15</span> : <span class="box" id="sec">00</span>
            </div>
        </div>
    </div>

    <div class="checkout-container">
        <div class="question-section">
            <h2 class="section-title">BẢNG CÂU HỎI</h2>

            <form id="checkoutForm" action="payment.php" method="POST">
                <input type="hidden" name="su_kien_id" value="<?php echo $event_id; ?>">
                <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($event_title); ?>">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">
                <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
                <input type="hidden" name="time" value="<?php echo htmlspecialchars($time); ?>">
                <input type="hidden" name="image" value="<?php echo htmlspecialchars($image); ?>">
                <input type="hidden" name="item_type" value="<?php echo htmlspecialchars($item_type); ?>">
                <input type="hidden" name="membership_id" value="<?php echo (int)$membership_id; ?>">

                <div class="form-card">
                    <div class="form-group">
                        <label>Họ và Tên của bạn</label>
                        <input type="text" name="customer_name" id="inpName" class="form-control" placeholder="Điền câu trả lời của bạn" value="<?php echo htmlspecialchars($user_name); ?>" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Số Điện Thoại của bạn</label>
                        <input type="text" name="customer_phone" id="inpPhone" class="form-control" placeholder="Điền câu trả lời của bạn" value="<?php echo htmlspecialchars($user_phone); ?>" required>
                    </div>
                </div>
            </form>
        </div>

        <div class="summary-card">
            <div class="summary-header">
                <span>Thông tin đặt vé</span>
                <a href="#" onclick="history.back();">Chọn lại vé</a>
            </div>

            <div class="ticket-row bold">
                <span>Loại vé</span>
                <span>Số lượng</span>
            </div>

            <div class="ticket-row" style="margin-top: 15px; align-items: flex-start;">
                <div>
                    <div style="font-size: 13px; text-transform: uppercase;"><?php echo htmlspecialchars($category); ?></div>
                    <div style="color: #666; font-size: 12px; margin-top: 5px;"><?php echo htmlspecialchars($price); ?></div>
                    <span class="ticket-category">F-38</span>
                </div>
                <div style="text-align: right;">
                    <div>01</div>
                    <div style="color: #666; font-size: 12px; margin-top: 5px;"><?php echo htmlspecialchars($price); ?></div>
                </div>
            </div>

            <div class="divider"></div>
            <div class="total-row">
                <span>Tạm tính 1 ghế</span>
                <span><?php echo htmlspecialchars($price); ?></span>
            </div>
            <button type="button" class="btn-continue" id="btnContinue">Tiếp tục <i class="fa-solid fa-angles-right"></i></button>
            <p class="btn-note">Vui lòng trả lời tất cả câu hỏi để tiếp tục</p>
        </div>
    </div>

    <script>
        let timeLeft = 15 * 60;
        const minEl = document.getElementById('min');
        const secEl = document.getElementById('sec');

        const timer = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(timer);
                alert("Đã hết thời gian giữ vé. Vui lòng đặt lại!");
                window.location.href = 'home.php';
            }
            timeLeft--;
            minEl.innerText = String(Math.floor(timeLeft / 60)).padStart(2, '0');
            secEl.innerText = String(timeLeft % 60).padStart(2, '0');
        }, 1000);

        const inpName = document.getElementById('inpName');
        const inpPhone = document.getElementById('inpPhone');
        const btnContinue = document.getElementById('btnContinue');
        const checkoutForm = document.getElementById('checkoutForm');

        function checkForm() {
            if (inpName.value.trim() !== '' && inpPhone.value.trim() !== '') {
                btnContinue.classList.add('active');
            } else {
                btnContinue.classList.remove('active');
            }
        }

        inpName.addEventListener('input', checkForm);
        inpPhone.addEventListener('input', checkForm);
        checkForm();

        btnContinue.addEventListener('click', () => {
            if (btnContinue.classList.contains('active')) {
                checkoutForm.submit();
            }
        });
    </script>
</body>

</html>