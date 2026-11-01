<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Bắt buộc đăng nhập mới được xem vé
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user']['name'] ?? 'Tài khoản';
$user_avatar = strtoupper(substr($user_name, 0, 1));

/**
 * TỰ ĐỘNG PHÂN LOẠI SỰ KIỆN SẮP DIỄN RA HAY ĐÃ KẾT THÚC
 * So sánh chuỗi ngày (ví dụ: "29.11.2026" hoặc "19:30 - 29.11.2026") với thời gian hiện tại
 */
/**
 * TỰ ĐỘNG PHÂN LOẠI SỰ KIỆN SẮP DIỄN RA HAY ĐÃ KẾT THÚC
 * Hỗ trợ cả định dạng "29.11.2026" lẫn "29 Tháng 11, 2026"
 */
function getEventTimelineStatus($date_str) {
    if (empty($date_str)) return 'sap-dien-ra';
    
    $day = $month = $year = null;

    // Trường hợp 1: Dạng "29.11.2026" hoặc "29/11/2026"
    if (preg_match('/(\d{1,2})[\.\/](\d{1,2})[\.\/](\d{4})/', $date_str, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
    } 
    // Trường hợp 2: Dạng "29 Tháng 11, 2026" hoặc "19:30 - 23:00, 29 Tháng 11, 2026"
    elseif (preg_match('/(\d{1,2})\s+Tháng\s+(\d{1,2}),?\s+(\d{4})/ui', $date_str, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
    }

    // Nếu bóc tách thành công ngày/tháng/năm
    if ($day && $month && $year) {
        $event_timestamp = strtotime("$year-$month-$day 23:59:59");
        return (time() > $event_timestamp) ? 'da-ket-thuc' : 'sap-dien-ra';
    }

    return 'sap-dien-ra';
}
// 2. Xử lý CHỨC NĂNG HỦY VÉ
$alert_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_ticket') {
    $cancel_index = $_POST['ticket_index'] ?? null;
    if ($cancel_index !== null && isset($_SESSION['my_tickets'][$cancel_index])) {
        $_SESSION['my_tickets'][$cancel_index]['status'] = 'da-huy';
        $alert_message = 'Hủy vé thành công!';
    }
}

// 3. Lấy danh sách vé từ Session
$my_tickets = $_SESSION['my_tickets'] ?? [];

// 4. Nhận tham số bộ lọc từ URL (Mặc định: status = all, timeline = sap-dien-ra)
$filter_status = $_GET['status'] ?? 'all';
$filter_timeline = $_GET['timeline'] ?? 'sap-dien-ra';

// 5. Đếm số lượng vé theo trạng thái
$count_all = count($my_tickets);
$count_success = 0;
$count_pending = 0;
$count_cancelled = 0;

foreach ($my_tickets as $item) {
    $st = $item['status'] ?? 'thanh-cong';
    if ($st === 'thanh-cong') $count_success++;
    elseif ($st === 'dang-xu-ly') $count_pending++;
    elseif ($st === 'da-huy') $count_cancelled++;
}

// 6. Lọc danh sách vé kết hợp TỰ ĐỘNG ĐÁNH GIÁ THỜI GIAN (Sắp diễn ra / Đã kết thúc)
$filtered_tickets = [];
foreach ($my_tickets as $index => $ticket) {
    $st = $ticket['status'] ?? 'thanh-cong';
    
    // Tự động kiểm tra thời gian từ thuộc tính date của vé
    $event_date_str = $ticket['date'] ?? '';
    $computed_timeline = $ticket['timeline'] ?? getEventTimelineStatus($event_date_str);

    $match_status = ($filter_status === 'all') || ($st === $filter_status);
    $match_timeline = ($computed_timeline === $filter_timeline);

    if ($match_status && $match_timeline) {
        $ticket['original_index'] = $index;
        $ticket['computed_timeline'] = $computed_timeline; // Lưu lại trạng thái timeline đã tính
        $filtered_tickets[] = $ticket;
    }
}

include '../templates/header.php';
?>

<main class="container" style="margin-top: 25px; min-height: 75vh;">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="home.php">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i> 
        <span>Đơn hàng của tôi</span> <i class="fa-solid fa-chevron-right"></i> 
        <span style="color: #fff;">Vé của tôi</span>
    </div>

    <!-- Thông báo hủy vé thành công -->
    <?php if (!empty($alert_message)): ?>
        <div style="background: rgba(38, 201, 115, 0.15); border: 1px solid #26c973; color: #26c973; padding: 10px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
            <i class="fa-solid fa-circle-check"></i> <?php echo $alert_message; ?>
        </div>
    <?php endif; ?>

    <div class="account-layout">
        <!-- Sidebar -->
        <aside class="account-sidebar">
            <div class="user-profile-summary">
                <div class="avatar-circle"><?php echo $user_avatar; ?></div>
                <div class="user-info-text">
                    <span style="font-size: 12px; color: var(--text-muted);">Tài khoản của</span>
                    <strong style="display: block; font-size: 14px; margin-top: 2px;">
                        <?php echo htmlspecialchars($user_name); ?> <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                    </strong>
                </div>
            </div>

            <nav class="sidebar-menu">
                <a href="#" class="menu-item"><i class="fa-regular fa-user"></i> Cài đặt tài khoản</a>
                <div class="menu-group">
                    <a href="my-tickets.php" class="menu-item active-group">
                        <i class="fa-solid fa-ticket" style="color: var(--primary-color);"></i> 
                        <strong style="color: var(--primary-color);">Đơn hàng của tôi</strong>
                    </a>
                    <div class="sub-menu">
                        <a href="my-tickets.php" class="sub-item active">Vé của tôi</a>
                    </div>
                </div>
                <a href="#" class="menu-item"><i class="fa-regular fa-star"></i> Sự kiện của tôi</a>
            </nav>
        </aside>

        <!-- Nội dung hiển thị vé -->
        <section class="account-content">
            <h2 class="page-title-text">Vé của tôi</h2>

            <!-- Pills Filter (Lọc theo trạng thái vé) -->
            <div class="filter-pills">
                <a href="my-tickets.php?status=all&timeline=<?php echo $filter_timeline; ?>" 
                   class="pill-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>">
                   Tất cả (<?php echo $count_all; ?>)
                </a>
                
                <a href="my-tickets.php?status=thanh-cong&timeline=<?php echo $filter_timeline; ?>" 
                   class="pill-btn <?php echo $filter_status === 'thanh-cong' ? 'active' : ''; ?>">
                   Thành công <?php echo $count_success > 0 ? "($count_success)" : ''; ?>
                </a>

                <a href="my-tickets.php?status=dang-xu-ly&timeline=<?php echo $filter_timeline; ?>" 
                   class="pill-btn <?php echo $filter_status === 'dang-xu-ly' ? 'active' : ''; ?>">
                   Đang xử lý <?php echo $count_pending > 0 ? "($count_pending)" : ''; ?>
                </a>

                <a href="my-tickets.php?status=da-huy&timeline=<?php echo $filter_timeline; ?>" 
                   class="pill-btn <?php echo $filter_status === 'da-huy' ? 'active' : ''; ?>">
                   Đã hủy <?php echo $count_cancelled > 0 ? "($count_cancelled)" : ''; ?>
                </a>
            </div>

            <!-- Sub Filter Tabs (Lọc tự động: Sắp diễn ra / Đã kết thúc) -->
            <div class="sub-filter-tabs">
                <a href="my-tickets.php?status=<?php echo $filter_status; ?>&timeline=sap-dien-ra" 
                   class="tab-link <?php echo $filter_timeline === 'sap-dien-ra' ? 'active' : ''; ?>">
                   Sắp diễn ra
                </a>
                <a href="my-tickets.php?status=<?php echo $filter_status; ?>&timeline=da-ket-thuc" 
                   class="tab-link <?php echo $filter_timeline === 'da-ket-thuc' ? 'active' : ''; ?>">
                   Đã kết thúc
                </a>
            </div>

            <!-- KIỂM TRA: NẾU KHÔNG CÓ VÉ NÀO KHỚP BỘ LỌC THÌ HIỆN HÌNH MINH HỌA TRỐNG -->
            <?php if (empty($filtered_tickets)): ?>
                <div class="empty-state-box">
                    <div class="sunset-illustration">
                        <div class="sun"></div>
                    </div>
                    <p class="empty-text">Bạn chưa có vé nào</p>
                </div>
            <?php else: ?>
                <!-- NẾU CÓ VÉ THÌ HIỂN THỊ DANH SÁCH -->
                <div class="tickets-list">
                    <?php foreach ($filtered_tickets as $ticket): 
                        $ticket_id = $ticket['id'] ?? $ticket['ticket_code'] ?? 'TBX-'.rand(10000,99999);
                        $event_title = $ticket['event_title'] ?? $ticket['title'] ?? 'Tên sự kiện';
                        $ticket_cat = $ticket['ticket_category'] ?? $ticket['category'] ?? 'Vé tiêu chuẩn';
                        $ticket_status = $ticket['status'] ?? 'thanh-cong';
                        $is_ended = ($ticket['computed_timeline'] === 'da-ket-thuc');
                    ?>
                        <div class="ticket-item-card">
                            <div class="ticket-item-img">
                                <img src="<?php echo $ticket['image']; ?>" alt="Ticket Poster">
                            </div>
                            <div class="ticket-item-details">
                                <div class="ticket-header-row">
                                    <span class="ticket-code">Mã vé: <strong><?php echo $ticket_id; ?></strong></span>
                                    
                                    <!-- HIỂN THỊ TRẠNG THÁI VÉ -->
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <!-- Nhãn thời gian Sắp diễn ra / Đã kết thúc -->
                                        <?php if ($is_ended): ?>
                                            <span style="background: rgba(255,255,255,0.1); color: #888; padding: 2px 8px; border-radius: 4px; font-size: 11px;">Đã kết thúc</span>
                                        <?php else: ?>
                                            <span style="background: rgba(38, 201, 115, 0.1); color: #26c973; padding: 2px 8px; border-radius: 4px; font-size: 11px;">Sắp diễn ra</span>
                                        <?php endif; ?>

                                        <!-- Nhãn trạng thái đơn hàng -->
                                        <?php if ($ticket_status === 'thanh-cong'): ?>
                                            <span class="badge-status-tag status-success"><i class="fa-solid fa-circle-check"></i> Thành công</span>
                                        <?php elseif ($ticket_status === 'dang-xu-ly'): ?>
                                            <span class="badge-status-tag" style="background: rgba(255, 193, 7, 0.15); color: #ffc107; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;"><i class="fa-solid fa-clock"></i> Đang xử lý</span>
                                        <?php elseif ($ticket_status === 'da-huy'): ?>
                                            <span class="badge-status-tag" style="background: rgba(255, 77, 79, 0.15); color: #ff4d4f; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;"><i class="fa-solid fa-circle-xmark"></i> Đã hủy</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <h3 class="ticket-event-name"><?php echo htmlspecialchars($event_title); ?></h3>
                                <p class="ticket-meta"><i class="fa-solid fa-ticket"></i> Hạng vé: <strong><?php echo htmlspecialchars($ticket_cat); ?></strong> - <?php echo $ticket['price']; ?></p>
                                <p class="ticket-meta"><i class="fa-regular fa-calendar"></i> <?php echo $ticket['date']; ?></p>
                                <p class="ticket-meta"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($ticket['location']); ?></p>

                                <!-- NÚT HỦY VÉ (Chỉ cho phép hủy nếu chưa hủy và sự kiện chưa kết thúc) -->
                                <div class="ticket-action-row" style="margin-top: 10px; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 8px; text-align: right;">
                                    <?php if ($ticket_status !== 'da-huy' && !$is_ended): ?>
                                        <form method="POST" action="my-tickets.php?status=<?php echo $filter_status; ?>&timeline=<?php echo $filter_timeline; ?>" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn hủy vé này không?');">
                                            <input type="hidden" name="action" value="cancel_ticket">
                                            <input type="hidden" name="ticket_index" value="<?php echo $ticket['original_index']; ?>">
                                            <button type="submit" class="btn-cancel" style="background: transparent; color: #ff4d4f; border: 1px solid #ff4d4f; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s;">
                                                <i class="fa-solid fa-ban"></i> Hủy vé
                                            </button>
                                        </form>
                                    <?php elseif ($ticket_status === 'da-huy'): ?>
                                        <span style="font-size: 12px; color: #ff4d4f; font-style: italic;">Vé đã hủy</span>
                                    <?php elseif ($is_ended): ?>
                                        <span style="font-size: 12px; color: #888; font-style: italic;">Sự kiện đã diễn ra</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include '../templates/footer.php'; ?>