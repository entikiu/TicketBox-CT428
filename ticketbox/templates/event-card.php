<?php
// Mockup biến nhận vào nếu có
$title = $event['title'] ?? 'Tên sự kiện mẫu';
$price = $event['price'] ?? 'Từ 500.000 đ';
$date = $event['date'] ?? '12.12.2026';
$location = $event['location'] ?? 'TP. Hồ Chí Minh';
$image = $event['image'] ?? 'https://via.placeholder.com/300x160';
?>

<div class="event-card">
    <div class="card-img">
        <img src="<?php echo $image; ?>" alt="<?php echo $title; ?>">
    </div>
    <div class="card-body">
        <div class="card-price"><?php echo $price; ?></div>
        <h3 class="card-title"><?php echo $title; ?></h3>
        <div class="card-info">
            <i class="fa-regular fa-calendar"></i> <?php echo $date; ?>
        </div>
        <div class="card-info">
            <i class="fa-solid fa-location-dot"></i> <?php echo $location; ?>
        </div>
    </div>
</div>