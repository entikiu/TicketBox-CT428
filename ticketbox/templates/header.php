<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticketbox - Mua Vé Sự Kiện</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
      <!-- Logo Text - Nút quay về trang chủ -->
        <a href="home.php" class="logo-text" title="Về trang chủ Ticketbox">
            ticketbox
        </a>
        
        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Bạn tìm gì hôm nay?">
        </div>

        <nav class="nav-links">
            <a href="#">TicketStars</a>
            <a href="#">Bán lại vé</a>
            <a href="#">Blog</a>
            <div class="auth-btn">
                <a href="login.php">Đăng nhập</a> | <a href="register.php">Đăng ký</a>
            </div>
        </nav>
    </div>
</header>

<!-- Nhúng navbar vào đây -->
<?php include __DIR__ . '/navbar.php'; ?>