<?php
$id = $event['id'] ?? 1;
$title = $event['title'] ?? 'Tên sự kiện mẫu';
$price = $event['price'] ?? 'Từ 500.000 đ';
$date = $event['date'] ?? '12.12.2026';
$location = $event['location'] ?? 'TP. Hồ Chí Minh';
$image = $event['image'] ?? 'https://via.placeholder.com/300x160';
?>

<div class="event-card">
    <a href="event-detail.php?id=<?php echo $id; ?>" style="text-decoration: none; color: inherit; display: block;">
        <div class="card-img">
            <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($title); ?>" onerror="this.src='https://via.placeholder.com/300x160/222/fff?text=Ticketbox';">
        </div>
        <div class="card-body">
            <div class="card-price"><?php echo $price; ?></div>
            <h3 class="card-title"><?php echo htmlspecialchars($title); ?></h3>
            <div class="card-info">
                <i class="fa-regular fa-calendar"></i> <?php echo $date; ?>
            </div>
            <div class="card-info">
                <i class="fa-solid fa-location-dot"></i> <?php echo $location; ?>
            </div>
        </div>
    </a>
</div>