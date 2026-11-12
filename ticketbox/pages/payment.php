<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_email = $_SESSION['user']['email'] ?? 'email@example.com';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['payment_data'] = [
        'item_type'      => $_POST['item_type'] ?? 'event',
        'membership_id'  => (int)($_POST['membership_id'] ?? 0),
        'customer_name'  => $_POST['customer_name'] ?? $_SESSION['user']['ho_ten'] ?? 'Khách hàng',
        'customer_phone' => $_POST['customer_phone'] ?? $_SESSION['user']['phone'] ?? '',
        'su_kien_id'     => $_POST['su_kien_id'] ?? 1,
        'event_title'    => $_POST['event_title'] ?? 'Sự kiện Ticketbox',
        'time'           => $_POST['time'] ?? '',
        'location'       => $_POST['location'] ?? '',
        'category'       => $_POST['category'] ?? 'Vé Tiêu Chuẩn',
        'price'          => $_POST['price'] ?? '0 đ',
        'image'          => $_POST['image'] ?? '../assets/images/events/default.jpg'
    ];
    header('Location: payment.php');
    exit;
}

if (!isset($_SESSION['payment_data'])) {
    header('Location: home.php');
    exit;
}

$pay_data       = $_SESSION['payment_data'];
$customer_name  = $pay_data['customer_name'];
$customer_phone = $pay_data['customer_phone'];
$event_id       = $pay_data['su_kien_id'];
$event_title    = $pay_data['event_title'];
$time           = $pay_data['time'];
$location       = $pay_data['location'];
$category       = $pay_data['category'];
$price          = $pay_data['price'];
$image          = $pay_data['image'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Ticketbox</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: #000;
            color: #fff;
        }

        .checkout-header {
            background-color: #26c973;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .checkout-header .logo {
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }

        .event-banner {
            padding: 40px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #222;
        }

        .event-info h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .event-info p {
            font-size: 14px;
            color: #ccc;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .timer-box {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 8px;
            text-align: center;
            backdrop-filter: blur(5px);
        }

        .timer-box p {
            font-size: 12px;
            color: #ccc;
            margin-bottom: 10px;
        }

        .time-blocks {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: 700;
        }

        .time-blocks span.box {
            background: #ff4d4f;
            padding: 8px 12px;
            border-radius: 6px;
            color: #fff;
        }

        .payment-container {
            padding: 40px 10%;
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }

        .section-title {
            color: #26c973;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .left-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .dark-card {
            background-color: #272730;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #3a3a45;
        }

        .dark-card h4 {
            font-size: 14px;
            font-weight: 600;
            color: #26c973;
            margin-bottom: 10px;
        }

        .info-text {
            font-size: 13px;
            color: #a0a3bd;
            line-height: 1.6;
        }

        .info-text strong {
            color: #fff;
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .voucher-header h4 {
            margin: 0;
        }

        .voucher-header a {
            color: #26c973;
            font-size: 13px;
            text-decoration: none;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #555;
            color: #a0a3bd;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
        }

        .payment-method {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #3a3a45;
            cursor: pointer;
        }

        .payment-method:last-child {
            border-bottom: none;
        }

        .payment-method input[type="radio"] {
            margin-right: 15px;
            accent-color: #26c973;
            width: 16px;
            height: 16px;
        }

        .payment-method img {
            width: 30px;
            height: 30px;
            object-fit: contain;
            margin-right: 15px;
            background: #fff;
            border-radius: 4px;
            padding: 2px;
        }

        .payment-method span {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }

        .method-hint {
            font-size: 12px;
            color: #a0a3bd;
            margin-left: 60px;
            margin-top: -5px;
        }

        .method-hint a {
            color: #26c973;
            text-decoration: none;
        }

        .right-col {
            width: 360px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            position: sticky;
            top: 20px;
        }

        .light-card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            color: #333;
        }

        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .light-card h4 {
            font-size: 14px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .light-card a {
            color: #007bff;
            font-size: 13px;
            text-decoration: none;
        }

        .ticket-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .ticket-row.bold {
            font-weight: 700;
            color: #111;
        }

        .ticket-category {
            background: #007bff;
            color: #fff;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 5px;
            font-weight: 600;
        }

        .divider {
            border-top: 1px dashed #ccc;
            margin: 15px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 16px;
            color: #26c973;
            margin-top: 15px;
        }

        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
            cursor: pointer;
        }

        .terms-checkbox input {
            margin-top: 3px;
            accent-color: #26c973;
        }

        .terms-checkbox p {
            font-size: 11px;
            color: #666;
            line-height: 1.5;
        }

        .terms-checkbox p a {
            color: #007bff;
            text-decoration: none;
        }

        .btn-pay {
            width: 100%;
            background-color: #26c973;
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 4px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            opacity: 0.5;
            pointer-events: none;
        }

        .btn-pay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .btn-pay.active:hover {
            background-color: #21b263;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .captcha-box {
            background: #fff;
            color: #333;
            width: 320px;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .captcha-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .captcha-header h3 {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
        }

        .captcha-header i {
            cursor: pointer;
            font-size: 14px;
            color: #888;
        }

        .captcha-desc {
            font-size: 12px;
            color: #666;
            text-align: left;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .captcha-img {
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            border-radius: 50%;
            border: 3px solid #f0f0f0;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .captcha-img img {
            width: 100%;
            object-fit: contain;
        }

        .slider-container {
            position: relative;
            width: 100%;
            height: 40px;
            background: #f0f0f0;
            border-radius: 20px;
            display: flex;
            align-items: center;
            padding: 0 5px;
        }

        .slider-input {
            -webkit-appearance: none;
            width: 100%;
            background: transparent;
            position: absolute;
            left: 0;
            z-index: 2;
            cursor: pointer;
            outline: none;
        }

        .slider-input::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 40px;
            height: 40px;
            background: #4a80ff;
            border-radius: 50%;
            cursor: grab;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" viewBox="0 0 16 16"><path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/></svg>');
            background-repeat: no-repeat;
            background-position: center;
            transition: background-color 0.2s;
        }

        .slider-input::-webkit-slider-thumb:active {
            cursor: grabbing;
            background-color: #26c973;
        }

        .slider-track-fill {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            background: #e0e8ff;
            border-radius: 20px;
            width: 0%;
            z-index: 1;
            transition: width 0.1s;
        }

        .captcha-logo {
            margin-top: 20px;
            font-weight: 800;
            font-size: 16px;
            color: #26c973;
        }
    </style>
</head>

<body>
    <header class="checkout-header">
        <a href="home.php" class="logo">ticketbox</a>
        <div class="user-menu">
            <i class="fa-solid fa-circle-user" style="font-size: 20px;"></i>
            Tài khoản <i class="fa-solid fa-caret-down"></i>
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
                <span class="box" id="min">11</span> : <span class="box" id="sec">40</span>
            </div>
        </div>
    </div>

    <div class="payment-container">
        <div class="left-col">
            <h2 class="section-title">THANH TOÁN</h2>

            <div class="dark-card">
                <h4>Thông tin nhận vé</h4>
                <p class="info-text">Vé điện tử sẽ được hiển thị trong mục "Vé của tôi" của tài khoản<br>
                    <strong><?php echo htmlspecialchars($user_email); ?></strong>
                </p>
            </div>

            <!-- Phần còn lại của Cột trái (Mã khuyến mãi, Phương thức thanh toán) giữ nguyên HTML -->
            <div class="dark-card">
                <div class="voucher-header">
                    <h4>Mã khuyến mãi</h4>
                    <a href="#">Chọn voucher</a>
                </div>
                <button class="btn-outline"><i class="fa-solid fa-plus"></i> Thêm khuyến mãi</button>
            </div>
            <div class="dark-card">
                <h4>Phương thức thanh toán</h4>
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="vnpay" checked>
                    <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/9/06ncktiwd6dc/logo-vnpay.png" alt="VNPAY">
                    <span>VNPAY/Ứng dụng ngân hàng</span>
                </label>
                <div class="method-hint">Hỗ trợ nguồn tiền thẻ tín dụng <a href="#">Chi tiết <i class="fa-regular fa-circle-question"></i></a></div>
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="vietqr">
                    <img src="https://vietqr.net/portal-template/img/logo-vietqr.png" alt="VietQR">
                    <span>VietQR</span>
                </label>
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="shopeepay">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTOB1mCXYJ9bVlsN8G6P3Q0zF9Z58y0d6Vn0Q&s" alt="ShopeePay">
                    <span>ShopeePay</span>
                </label>
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="zalopay">
                    <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png" alt="ZaloPay">
                    <span>ZaloPay</span>
                </label>
                <label class="payment-method" style="border: none;">
                    <input type="radio" name="payment_method" value="credit_card">
                    <img src="https://cdn-icons-png.flaticon.com/512/6963/6963703.png" alt="Thẻ tín dụng">
                    <span>Thẻ ghi nợ/Thẻ tín dụng</span>
                </label>
            </div>
        </div>

        <div class="right-col">
            <div class="light-card flex-between" style="margin-bottom: 0;">
                <h4>Hoá đơn điện tử</h4>
                <a href="#">Yêu cầu xuất</a>
            </div>
            <div class="light-card">
                <div class="flex-between">
                    <h4>Thông tin đặt vé</h4>
                    <a href="#" onclick="history.back();">Chọn lại vé</a>
                </div>
                <div class="ticket-row bold">
                    <span>Loại vé</span>
                    <span>Số lượng</span>
                </div>
                <div class="ticket-row" style="margin-top: 10px; align-items: flex-start;">
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: #666;"><?php echo htmlspecialchars($category); ?></div>
                        <div style="color: #888; font-size: 11px; margin-top: 3px;"><?php echo htmlspecialchars($price); ?></div>
                        <span class="ticket-category">F-38</span>
                    </div>
                    <div style="text-align: right;">
                        <div style="color: #333; font-weight: 600;">01</div>
                        <div style="color: #888; font-size: 11px; margin-top: 3px;"><?php echo htmlspecialchars($price); ?></div>
                    </div>
                </div>
            </div>
            <div class="light-card">
                <h4>Thông tin đơn hàng</h4>
                <div class="ticket-row" style="margin-top: 15px;">
                    <span style="color: #666;">Tạm tính</span>
                    <span style="font-weight: 600;"><?php echo htmlspecialchars($price); ?></span>
                </div>
                <div class="divider"></div>
                <div class="total-row">
                    <span>Tổng tiền</span>
                    <span><?php echo htmlspecialchars($price); ?></span>
                </div>
                <label class="terms-checkbox">
                    <input type="checkbox" id="chkTerms">
                    <p>Bằng việc tiến hành đặt mua, bạn đã đồng ý với <a href="#">Điều Kiện, Điều Khoản</a> của nền tảng Ticketbox</p>
                </label>
                <button type="button" class="btn-pay" id="btnPay">Thanh toán</button>
            </div>

            <!-- Truyền đầy đủ dữ liệu sang API lưu DB -->
            <form id="finalSubmitForm" action="../api/events/process_checkout.php" method="POST" style="display: none;">
                <input type="hidden" name="su_kien_id" value="<?php echo $event_id; ?>">
                <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($event_title); ?>">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">
                <input type="hidden" name="time" value="<?php echo htmlspecialchars($time); ?>">
                <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
                <input type="hidden" name="item_type" value="<?php echo htmlspecialchars($pay_data['item_type'] ?? 'event'); ?>">
                <input type="hidden" name="membership_id" value="<?php echo (int)($pay_data['membership_id'] ?? 0); ?>">
            </form>
        </div>
    </div>

    <!-- MODAL CAPTCHA (Bảo mật) -->
    <div class="modal-overlay" id="captchaModal">
        <div class="captcha-box">
            <div class="captcha-header">
                <h3>Xác Minh Người Dùng</h3>
                <div>
                    <i class="fa-solid fa-rotate-right" style="margin-right: 10px;" onclick="resetSlider()"></i>
                    <i class="fa-solid fa-circle-xmark" onclick="closeModal()"></i>
                </div>
            </div>
            <div class="captcha-desc">
                <strong>Chống bot tự động mua vé</strong><br><br>
                Kéo mũi tên qua phải để hoàn thiện bức hình, giúp Ticketbox xác minh bạn là người mua thực sự.
            </div>
            <div class="captcha-img">
                <img src="https://cdn-icons-png.flaticon.com/512/3504/3504780.png" alt="Xác minh" id="captchaIcon" style="opacity: 0.5; filter: grayscale(100%); transition: 0.3s;">
            </div>
            <div class="slider-container">
                <div class="slider-track-fill" id="sliderFill"></div>
                <input type="range" min="0" max="100" value="0" class="slider-input" id="verifySlider">
            </div>
            <div class="captcha-logo">ticket<span style="color:#333;">box</span></div>
        </div>
    </div>

    <script>
        let timeLeft = 11 * 60 + 40;
        const minEl = document.getElementById('min');
        const secEl = document.getElementById('sec');

        const timer = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(timer);
                alert("Đã hết thời gian thanh toán. Vui lòng đặt lại!");
                window.location.href = 'home.php';
            }
            timeLeft--;
            minEl.innerText = String(Math.floor(timeLeft / 60)).padStart(2, '0');
            secEl.innerText = String(timeLeft % 60).padStart(2, '0');
        }, 1000);

        const chkTerms = document.getElementById('chkTerms');
        const btnPay = document.getElementById('btnPay');

        chkTerms.addEventListener('change', function() {
            if (this.checked) {
                btnPay.classList.add('active');
            } else {
                btnPay.classList.remove('active');
            }
        });

        const modal = document.getElementById('captchaModal');
        const slider = document.getElementById('verifySlider');
        const sliderFill = document.getElementById('sliderFill');
        const captchaIcon = document.getElementById('captchaIcon');

        btnPay.addEventListener('click', () => {
            modal.style.display = 'flex';
            resetSlider();
        });

        function closeModal() {
            modal.style.display = 'none';
        }

        function resetSlider() {
            slider.value = 0;
            sliderFill.style.width = '0%';
            captchaIcon.style.opacity = '0.5';
            captchaIcon.style.filter = 'grayscale(100%)';
            slider.disabled = false;
        }

        slider.addEventListener('input', function() {
            const val = this.value;
            sliderFill.style.width = val + '%';
            captchaIcon.style.opacity = (0.5 + (val / 200));
            captchaIcon.style.filter = `grayscale(${100 - val}%)`;

            if (val == 100) {
                slider.disabled = true;
                captchaIcon.style.transform = 'scale(1.1)';

                setTimeout(() => {
                    const finalForm = document.getElementById('finalSubmitForm');
                    if (finalForm) {
                        HTMLFormElement.prototype.submit.call(finalForm);
                    }
                }, 1200);
            }
        });
    </script>
</body>

</html>