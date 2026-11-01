<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$documents = [
    'khach-hang'            => '../assets/docs/dieu-khoan-khach-hang.pdf',
    'ban-to-chuc'            => '../assets/docs/dieu-khoan-ban-to-chuc.pdf',
    'quy-che-hoat-dong'     => '../assets/docs/quy-che-hoat-dong.pdf',
    'chinh-sach-bao-mat'    => '../assets/docs/chinh-sach-bao-mat.pdf',
    'giai-quyet-tranh-chap' => '../assets/docs/co-che-giai-quyet-tranh-chap.pdf',
    'bao-mat-thanh-toan'   => '../assets/docs/chinh-sach-bao-mat-thanh-toan.pdf',
    'doi-tra-kiem-hang'     => '../assets/docs/chinh-sach-doi-tra-kiem-hang.pdf',
    'van-chuyen-giao-nhan'  => '../assets/docs/dieu-kien-van-chuyen-giao-nhan.pdf',
    'phuong-thuc-thanh-toan' => '../assets/docs/phuong-thuc-thanh-toan.pdf'
];

$type = $_GET['type'] ?? 'khach-hang';
$pdf_file = $documents[$type] ?? $documents['khach-hang'];

include '../templates/header.php';
?>

<!-- TRANG XEM PDF CHUẨN GIAO DIỆN TICKETBOX -->
<main class="terms-pdf-page">
    <div class="terms-pdf-container">
        <?php if (file_exists($pdf_file)): ?>
            <iframe
                src="<?php echo $pdf_file; ?>#zoom=100&toolbar=1"
                class="terms-pdf-iframe"
                type="application/pdf">
            </iframe>
        <?php else: ?>
            <div class="pdf-fallback-box">
                <i class="fa-solid fa-file-pdf"></i>
                <p>Văn bản chưa sẵn sàng. Vui lòng đặt tệp PDF vào thư mục: <code>assets/docs/<?php echo htmlspecialchars(basename($pdf_file)); ?></code></p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../templates/footer.php'; ?>