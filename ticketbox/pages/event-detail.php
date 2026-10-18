<?php include '../templates/header.php'; ?>

<main class="container">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top:20px;">
        <!-- Cột trái: Ảnh & Chi tiết -->
        <div>
            <img src="../assets/images/events/banner-skynote.jpg" style="width:100%; border-radius:12px;" alt="Banner">
            <h1 style="margin: 20px 0 10px;">LIVE CONCERT SKYNOTE - QUỐC THIÊN</h1>
            <p style="color: var(--text-muted);"><i class="fa-solid fa-location-dot"></i> Trung tâm Triển lãm Việt Nam (VEC), Hà Nội</p>
            <p style="color: var(--text-muted);"><i class="fa-regular fa-clock"></i> 16:30 - 29/11/2026</p>
            
            <hr style="border-color: var(--border-color); margin: 20px 0;">
            <h3>Giới thiệu sự kiện</h3>
            <p style="margin-top:10px; color: #ccc;">Chi tiết về đêm nhạc Live Concert Skynote của Quốc Thiên...</p>
        </div>

        <!-- Cột phải: Box Đặt Vé -->
        <div style="background: var(--card-bg); padding: 20px; border-radius: 12px; height: fit-content;">
            <h3 style="color: var(--primary-color);">Giá từ: 800.000 đ</h3>
            <button style="width:100%; background: var(--primary-color); color:#fff; border:none; padding:12px; border-radius:6px; font-weight:bold; margin-top:15px; cursor:pointer;">
                MUA VÉ NGAY
            </button>
        </div>
    </div>
</main>

<?php include '../templates/footer.php'; ?>