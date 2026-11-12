<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$mem_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$stmt = $pdo->prepare("SELECT * FROM the_thanh_vien WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $mem_id]);
$mem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mem) {
    header('Location: home.php');
    exit;
}

$benefits = array_filter(array_map('trim', explode("\n", $mem['quyen_loi'] ?? '')));
$terms    = array_filter(array_map('trim', explode("\n", $mem['dieu_khoan'] ?? '')));
$priceFormatted = number_format((int)$mem['gia'], 0, ',', '.') . ' đ';

include '../templates/header.php';
?>

<style>
    .mem-detail-page {
        max-width: 820px;
        margin: 25px auto 60px;
        padding: 0 16px;
        color: #fff;
    }
    .mem-breadcrumb {
        font-size: 13px;
        color: #a0a3bd;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
    }
    .mem-breadcrumb a { color: #a0a3bd; text-decoration: none; }
    .mem-breadcrumb a:hover { color: #26c973; }
    .mem-breadcrumb span.current { color: #fff; }

    .mem-page-heading {
        font-size: 20px;
        font-weight: 700;
        padding-bottom: 14px;
        border-bottom: 1px solid #2a2b32;
        margin-bottom: 28px;
    }

    .mem-artwork-wrap {
        display: flex;
        justify-content: center;
        margin-bottom: 28px;
    }
    .mem-artwork-wrap img {
        width: 100%;
        max-width: 420px;
        aspect-ratio: 16 / 10;
        object-fit: cover;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
    }

    .mem-summary-box {
        padding-bottom: 18px;
        border-bottom: 1px solid #2a2b32;
        margin-bottom: 22px;
    }
    .mem-summary-title {
        font-size: 18px;
        font-weight: 700;
        color: #26c973;
        margin-bottom: 8px;
    }
    .mem-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .mem-summary-price {
        font-size: 24px;
        font-weight: 800;
        color: #f5b800;
    }
    .mem-summary-duration {
        font-size: 15px;
        font-weight: 700;
        color: #fff;
    }

    .mem-section-block {
        padding-bottom: 22px;
        border-bottom: 1px solid #2a2b32;
        margin-bottom: 22px;
    }
    .mem-section-block h3 {
        font-size: 15px;
        font-weight: 700;
        color: #26c973;
        margin-bottom: 14px;
    }

    .benefit-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .benefit-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 14px;
        color: #e6e6eb;
        line-height: 1.5;
    }
    .benefit-list li i {
        color: #26c973;
        margin-top: 3px;
        font-size: 14px;
    }

    .terms-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 13.5px;
        color: #d0d1d8;
        line-height: 1.6;
    }

    .mem-agree-label {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        cursor: pointer;
        font-size: 13.5px;
        color: #e6e6eb;
        line-height: 1.5;
        margin-bottom: 20px;
    }
    .mem-agree-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-top: 2px;
        accent-color: #26c973;
        cursor: pointer;
    }
    .mem-agree-label a { color: #26c973; text-decoration: none; font-weight: 600; }

    .btn-mem-continue {
        width: 100%;
        padding: 14px;
        background: #26c973;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.25s;
        opacity: 0.45;
        pointer-events: none;
    }
    .btn-mem-continue.active {
        opacity: 1;
        pointer-events: auto;
    }
    .btn-mem-continue.active:hover { background: #21b263; }

    .btn-mem-unavailable {
        width: 100%;
        padding: 14px;
        background: #3a3b42;
        color: #9a9da8;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 700;
        cursor: not-allowed;
    }
</style>

<main class="mem-detail-page">
    <!-- Breadcrumb -->
    <div class="mem-breadcrumb">
        <a href="home.php"><?php echo htmlspecialchars($mem['nghe_si']); ?></a>
        <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
        <a href="home.php">Các Thẻ thành viên</a>
        <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
        <span class="current">Chi tiết Thẻ thành viên</span>
    </div>

    <h1 class="mem-page-heading">Chi tiết Thẻ thành viên</h1>

    <!-- Hình ảnh thẻ trung tâm -->
    <div class="mem-artwork-wrap">
        <img src="<?php echo htmlspecialchars($mem['hinh_anh']); ?>"
             alt="<?php echo htmlspecialchars($mem['ten_the']); ?>"
             onerror="this.src='https://via.placeholder.com/420x260/1c1d22/26c973?text=<?php echo urlencode($mem['ten_the']); ?>'">
    </div>

    <!-- Tên gói, giá và thời hạn -->
    <div class="mem-summary-box">
        <div class="mem-summary-title"><?php echo htmlspecialchars($mem['ten_the']); ?></div>
        <div class="mem-summary-row">
            <div class="mem-summary-price"><?php echo $priceFormatted; ?></div>
            <div class="mem-summary-duration"><?php echo (int)$mem['thoi_han_thang']; ?> tháng</div>
        </div>
    </div>

    <!-- Quyền lợi -->
    <div class="mem-section-block">
        <h3>Quyền lợi</h3>
        <ul class="benefit-list">
            <?php foreach ($benefits as $item): ?>
                <li>
                    <i class="fa-solid fa-check"></i>
                    <span><?php echo htmlspecialchars($item); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Điều khoản & Điều kiện sử dụng -->
    <div class="mem-section-block">
        <h3>Điều khoản &amp; Điều kiện sử dụng</h3>
        <div class="terms-list">
            <?php foreach ($terms as $term): ?>
                <div>- <?php echo htmlspecialchars($term); ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Form chuyển sang quy trình thanh toán giống mua vé -->
    <?php if ($mem['trang_thai'] === 'dang-ban'): ?>
        <form action="checkout.php" method="POST">
            <input type="hidden" name="item_type" value="membership">
            <input type="hidden" name="membership_id" value="<?php echo (int)$mem['id']; ?>">

            <label class="mem-agree-label">
                <input type="checkbox" id="chkAgreeMem">
                <span>Tôi đã đọc, hiểu, đồng ý và cam kết tuân thủ <a href="#">Quy định chung</a>, Điều khoản &amp; Điều kiện, chính sách khác áp dụng cho gói Thẻ thành viên này</span>
            </label>

            <button type="submit" class="btn-mem-continue" id="btnMemContinue">
                Tiếp tục - <?php echo $priceFormatted; ?> &raquo;
            </button>
        </form>

        <script>
            const chkAgreeMem = document.getElementById('chkAgreeMem');
            const btnMemContinue = document.getElementById('btnMemContinue');
            chkAgreeMem.addEventListener('change', function() {
                btnMemContinue.classList.toggle('active', this.checked);
            });
        </script>
    <?php else: ?>
        <button type="button" class="btn-mem-unavailable" disabled>
            <?php echo ($mem['trang_thai'] === 'da-ban-het') ? 'Đã bán hết' : 'Đã đóng bán'; ?>
        </button>
    <?php endif; ?>
</main>

<?php include '../templates/footer.php'; ?>