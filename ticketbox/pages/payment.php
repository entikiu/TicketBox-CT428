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
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/payment.css">
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
                    <img src="../assets/images/payment/vnpay.png" alt="VNPAY">
                    <span>VNPAY/Ứng dụng ngân hàng</span>
                </label>
               

                <label class="payment-method">
                    <input type="radio" name="payment_method" value="vietqr">
                    <img src="../assets/images/payment/vietqr.png" alt="VietQR">
                    <span>VietQR</span>
                </label>

                <label class="payment-method">
                    <input type="radio" name="payment_method" value="shopeepay">
                    <img src="../assets/images/payment/shopeepay.png" alt="ShopeePay">
                    <span>ShopeePay</span>
                </label>

                <label class="payment-method">
                    <input type="radio" name="payment_method" value="zalopay">
                    <img src="../assets/images/payment/zalopay.png" alt="ZaloPay">
                    <span>ZaloPay</span>
                </label>

                <label class="payment-method" style="border: none;">
                    <input type="radio" name="payment_method" value="credit_card">
                    <img src="../assets/images/payment/credit-card.png" alt="Thẻ tín dụng">
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