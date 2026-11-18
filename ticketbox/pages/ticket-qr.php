<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập (đồng bộ với my-tickets.php: dùng $_SESSION['user'])
if (empty($_SESSION['user']['id']) && empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ưu tiên $_SESSION['user']['id'] giống my-tickets.php để tránh lệch tài khoản
$userId   = (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
$ticketId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$ticket    = false;
$qr_error  = '';

if ($ticketId < 1) {
    $qr_error = 'Mã vé không hợp lệ.';
} else {
    try {
        // Lấy thông tin chi tiết vé của khách hàng (Khớp với cấu trúc bảng ve và sukien)
        $stmt = $pdo->prepare("SELECT v.*, s.ten_su_kien, s.thoi_gian, s.dia_diem, v.loai_ve AS ten_loai 
                               FROM ve v 
                               LEFT JOIN sukien s ON v.su_kien_id = s.id 
                               WHERE v.id = :tid AND v.nguoi_dung_id = :uid LIMIT 1");
        $stmt->execute(['tid' => $ticketId, 'uid' => $userId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('[TicketBox Ticket QR] ' . $e->getMessage());
        $ticket = false;
    }

    if (!$ticket) {
        $qr_error = 'Không tìm thấy vé hoặc bạn không có quyền xem vé này!';
    } elseif (($ticket['trang_thai'] ?? '') === 'da-huy') {
        $qr_error = 'Vé này đã bị hủy nên không thể hiển thị mã QR.';
    }
}

include '../templates/header.php';

// Hiển thị lỗi bên trong layout (trước đây echo chữ trắng trên nền trắng => trang trắng)
if ($qr_error !== ''):
?>
<div class="star-page-container">
    <div class="star-box-card" style="max-width: 500px; margin: 40px auto; text-align: center; background: #272730; border: 1px solid #3a3a45; border-radius: 12px; padding: 30px;">
        <i class="fa-solid fa-triangle-exclamation" style="color: #ff4d4f; font-size: 36px; margin-bottom: 15px;"></i>
        <p style="color: #fff; font-size: 15px; margin-bottom: 20px;"><?php echo htmlspecialchars($qr_error); ?></p>
        <a href="my-tickets.php?tab=tickets" style="display: inline-block; text-decoration: none; background: #26c973; color: #fff; padding: 10px 20px; border-radius: 6px; font-weight: 700;">
            <i class="fa-solid fa-arrow-left"></i> Quay lại Vé của tôi
        </a>
    </div>
</div>
<?php
    include '../templates/footer.php';
    exit;
endif;
?>

<div class="star-page-container">
    <div class="star-box-card" style="max-width: 500px; margin: 0 auto; text-align: center;">
        <h2 style="color: #fff; margin-bottom: 10px; font-size: 20px;">
            <i class="fa-solid fa-qrcode" style="color: #26c973;"></i> Mã QR Vé Sự Kiện (Động)
        </h2>
        <p style="color: #a0a3bd; font-size: 13.5px; margin-bottom: 15px;">
            Mã QR tự thay đổi và có hiệu lực trong vòng 60 giây để đảm bảo bảo mật.
        </p>

        <!-- Khung hiển thị thông tin sự kiện -->
        <div style="background: #18191d; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: left;">
            <div style="color: #fff; font-weight: 700; font-size: 16px; margin-bottom: 8px;">
                <?php echo htmlspecialchars($ticket['ten_su_kien']); ?>
            </div>
            <div style="color: #a0a3bd; font-size: 13px; margin-bottom: 4px;">
                <i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($ticket['thoi_gian']); ?>
            </div>
            <div style="color: #a0a3bd; font-size: 13px; margin-bottom: 4px;">
                <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($ticket['dia_diem']); ?>
            </div>
            <div style="color: #26c973; font-size: 13px; font-weight: 600; margin-top: 8px;">
                Hạng vé: <?php echo htmlspecialchars($ticket['ten_loai']); ?> | Mã vé: <?php echo htmlspecialchars($ticket['ma_ve']); ?>
            </div>
        </div>

        <!-- Đồng hồ đếm ngược -->
        <div style="margin-bottom: 15px;">
            <span style="color: #a0a3bd; font-size: 13px;">Mã QR hiệu lực trong:</span>
            <div id="countdown-timer" style="color: #26c973; font-size: 22px; font-weight: 700;">60s</div>
        </div>

        <!-- Thẻ chứa mã QR -->
        <div id="qrcode-container" style="background: #fff; padding: 20px; display: inline-block; border-radius: 10px; margin-bottom: 15px; transition: opacity 0.3s;">
            <div id="qrcode"></div>
        </div>

        <!-- Thông báo hết hiệu lực -->
        <div id="expired-box" style="display: none; color: #ff4d4f; font-size: 14px; font-weight: 600; margin-bottom: 15px;">
            <i class="fa-solid fa-triangle-exclamation"></i> Mã QR đã hết hạn! Vui lòng tạo mã mới.
        </div>

        <!-- Các nút điều hướng -->
        <div style="display: flex; gap: 10px; justify-content: center; align-items: center;">
            <button id="btn-refresh" onclick="refreshQRCode()" style="display: none; background: #26c973; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; cursor: pointer;">
                <i class="fa-solid fa-rotate-right"></i> Tạo mã QR mới
            </button>
            <a href="my-tickets.php" class="star-btn-buy" style="display: inline-block; text-decoration: none; background: #2b2f3e; color: #a0a3bd; border: 1px solid #3a3a45; padding: 10px 20px;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Nhúng thư viện qrcode.js từ CDN (có CDN dự phòng) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    if (typeof QRCode === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"><\/script>');
    }
</script>
<script>
    const baseTicketCode = <?php echo json_encode((string)($ticket['ma_ve'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    let timeLeft = 60;
    let timerInterval = null;

    function generateQRCode() {
        // Xóa nội dung QR cũ trước khi vẽ mới
        const qrContainer = document.getElementById("qrcode");
        if (!qrContainer) return;
        qrContainer.innerHTML = "";

        // Thư viện QR không tải được (mất mạng / bị chặn) => hiện mã vé dạng chữ thay vì khung trắng
        if (typeof QRCode === 'undefined') {
            qrContainer.innerHTML = '<div style="width:200px;color:#333;font-size:13px;line-height:1.5;">'
                + 'Không tải được thư viện mã QR.<br>Vui lòng kiểm tra kết nối mạng.<br><strong style="font-size:15px;"></strong></div>';
            qrContainer.querySelector('strong').textContent = baseTicketCode;
            return;
        }

        const randomStr = Math.random().toString(36).substring(2, 8);
        const uniqueDynamicToken = baseTicketCode + "_" + Date.now() + "_" + randomStr;

        try {
            new QRCode(qrContainer, {
                text: uniqueDynamicToken,
                width: 200,
                height: 200,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        } catch (e) {
            console.error(e);
            qrContainer.textContent = baseTicketCode;
        }
    }

    function startCountdown() {
        timeLeft = 60;
        const expiredBox = document.getElementById("expired-box");
        const btnRefresh = document.getElementById("btn-refresh");
        const qrBox = document.getElementById("qrcode-container");
        const timerText = document.getElementById("countdown-timer");

        if (expiredBox) expiredBox.style.display = "none";
        if (btnRefresh) btnRefresh.style.display = "none";
        if (qrBox) qrBox.style.opacity = "1";
        
        // Sinh mã QR ngay khi bắt đầu
        generateQRCode();

        if (timerInterval) clearInterval(timerInterval);
        
        timerInterval = setInterval(() => {
            timeLeft--;
            if (timerText) {
                timerText.innerText = timeLeft + "s";
            }

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                // Hết 60 giây: Làm mờ QR, hiện thông báo và nút tạo mới
                if (qrBox) qrBox.style.opacity = "0.2";
                if (expiredBox) expiredBox.style.display = "block";
                if (btnRefresh) btnRefresh.style.display = "inline-block";
                if (timerText) timerText.innerText = "0s";
            }
        }, 1000);
    }

    function refreshQRCode() {
        startCountdown();
    }

    // Tự động kích hoạt khi trang tải xong
    window.addEventListener('DOMContentLoaded', () => {
        if (baseTicketCode) {
            startCountdown();
        }
    });
</script>

<?php include '../templates/footer.php'; ?>