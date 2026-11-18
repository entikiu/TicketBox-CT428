<?php
/**
 * TicketBox – Trang cá nhân (profile)
 * Dark theme: #18191a
 *
 * This page re‑uses the common header and navbar, shows the user’s avatar &
 * name, provides a shortcut to edit the profile, and lists:
 *   • Thẻ thành viên của tôi   (membership cards)
 *   • Đang theo dõi           (followed stars)
 *
 * The queries are placeholders – replace the table names / column names
 * with the actual schema in your project.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Check login
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}
$userId   = (int)$_SESSION['user']['id'];
$userName = $_SESSION['user']['ho_ten'] ?? $_SESSION['user']['name'] ?? 'Người dùng';
$userAvt  = $_SESSION['user']['avatar'] ?? '';
$firstChar = mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');

/* --------------------------------------------------------------
   1️⃣  Lấy dữ liệu thẻ thành viên (membership cards)
   -------------------------------------------------------------- */
$membershipCards = [];
if (isset($pdo)) {
    try {
        // TODO: replace `membership_cards` & column names with real ones
        $stmt = $pdo->prepare(
            "SELECT id, ten_the, loai_the, ngay_hieu_luc, ngay_het_han
               FROM membership_cards
              WHERE nguoi_dung_id = :uid"
        );
        $stmt->execute(['uid' => $userId]);
        $membershipCards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('[TicketBox profile] Membership query: ' . $e->getMessage());
    }
}

/* --------------------------------------------------------------
   2️⃣  Lấy danh sách các stars mà người dùng đang theo dõi
   -------------------------------------------------------------- */
$followedStars = [];
if (isset($pdo)) {
    try {
        // TODO: replace `ticketstar_theodoi` & column names with real ones
        $stmt = $pdo->prepare(
            "SELECT s.id, s.ten_su_kien, s.dia_diem, s.thoi_gian, s.avatar AS star_avatar
               FROM ticketstar_theodoi ts
               JOIN sukien s ON ts.star_id = s.id
              WHERE ts.nguoi_dung_id = :uid"
        );
        $stmt->execute(['uid' => $userId]);
        $followedStars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('[TicketBox profile] Followed stars query: ' . $e->getMessage());
    }
}
?>
<?php include '../templates/header.php'; ?>
<?php // include '../templates/navbar.php'; // Removed to avoid duplicate menu bar ?>

<main class="profile-page" style="background:#18191a; min-height:80vh; padding:40px 0;">
    <div class="container" style="max-width:900px; margin:auto; color:#fff;">

        <!-- Avatar & Tên -->
        <section class="profile-header" style="text-align:center; margin-bottom:40px;">
            <div class="avatar-wrapper" style="position:relative; display:inline-block;">
                <?php if (!empty($userAvt)): ?>
                    <img src="<?php echo htmlspecialchars($userAvt); ?>?v=<?php echo time(); ?>"
                         alt="Avatar"
                         style="width:120px;height:120px;border-radius:50%;object-fit:cover;
                                background:#26c973;">
                <?php else: ?>
                    <span style="display:inline-flex; width:120px; height:120px;
                               align-items:center; justify-content:center;
                               background:#26c973; color:#fff; font-size:48px;
                               border-radius:50%;"><?php echo $firstChar; ?></span>
                <?php endif; ?>
                <!-- nút chỉnh sửa -->
                <a href="edit-profile.php"
                   title="Chỉnh sửa hồ sơ"
                   style="position:absolute; right:-10px; top:-10px;
                          background:#444; color:#ccc; border-radius:50%;
                          width:32px; height:32px; display:flex;
                          align-items:center; justify-content:center;
                          text-decoration:none; font-size:18px;">
                    <i class="fa-solid fa-pen"></i>
                </a>
            </div>
            <h2 style="margin-top:15px; font-weight:700;">
                <?php echo htmlspecialchars($userName); ?>
            </h2>
        </section>

        <!-- Thẻ thành viên của tôi -->
        <section class="membership-section" style="margin-bottom:40px;">
            <h3 style="font-size:20px; margin-bottom:15px;">
                <i class="fa-solid fa-gem" style="color:#ffd700; margin-right:8px;"></i>
                Thẻ thành viên của tôi
            </h3>

            <?php if (empty($membershipCards)): ?>
                <p style="color:#777; font-style:italic;">
                    Bạn chưa có thẻ thành viên
                </p>
                <a href="my-tickets.php?tab=membership"
                   style="color:#26c973; text-decoration:none;">
                    Khám phá thêm &gt;
                </a>
            <?php else: ?>
                <div class="cards-grid" style="display:flex; flex-wrap:wrap; gap:20px;">
                    <?php foreach ($membershipCards as $card): ?>
                        <div class="card"
                             style="background:#272730; padding:15px; border-radius:8px;
                                    width:calc(50% - 10px);">
                            <h4 style="margin:0 0 5px 0;">
                                <?php echo htmlspecialchars($card['ten_the']); ?>
                            </h4>
                            <p style="margin:0; color:#a0a3bd;">
                                Loại: <?php echo htmlspecialchars($card['loai_the'] ?? ''); ?><br>
                                Hiệu lực: <?php echo htmlspecialchars($card['ngay_hieu_luc'] ?? '-'); ?>
                                – <?php echo htmlspecialchars($card['ngay_het_han'] ?? '-'); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <!-- Clear separator -->
        <div style="clear:both; margin-top:30px;"></div>

        <!-- Đang theo dõi -->
        <section class="followed-section">
            <h3 style="font-size:20px; margin-bottom:15px;">
                <i class="fa-solid fa-star" style="color:#ffd700; margin-right:8px;"></i>
                Đang theo dõi
            </h3>

            <?php if (empty($followedStars)): ?>
                <p style="color:#777; font-style:italic;">
                    Bạn chưa có danh sách theo dõi
                </p>
                <a href="my-tickets.php?tab=my-events"
                   style="color:#26c973; text-decoration:none;">
                    Khám phá thêm &gt;
                </a>
            <?php else: ?>
                <div class="stars-grid" style="display:flex; flex-wrap:wrap; gap:20px;">
                    <?php foreach ($followedStars as $star): ?>
                        <div class="star-card"
                             style="background:#272730; padding:15px; border-radius:8px;
                                    width:calc(33% - 13px); display:flex; align-items:center;">
                            <?php if (!empty($star['star_avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($star['star_avatar']); ?>"
                                     alt="Avatar" style="width:48px; height:48px;
                                     border-radius:50%; object-fit:cover; margin-right:10px;">
                            <?php else: ?>
                                <span style="width:48px; height:48px; background:#555;
                                            border-radius:50%; display:inline-block;
                                            margin-right:10px;"></span>
                            <?php endif; ?>
                            <div>
                                <strong style="display:block;"><?php echo htmlspecialchars($star['ten_su_kien']); ?></strong>
                                <small style="color:#a0a3bd;">
                                    <?php echo htmlspecialchars($star['dia_diem'] ?? ''); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php include '../templates/footer.php'; ?><?php
/**
 * TicketBox – Trang cá nhân (profile)
 * Dark theme: #18191a
 *
 * This page re‑uses the common header and navbar, shows the user’s avatar &
 * name, provides a shortcut to edit the profile, and lists:
 *   • Thẻ thành viên của tôi   (membership cards)
 *   • Đang theo dõi           (followed stars)
 *
 * The queries are placeholders – replace the table names / column names
 * with the actual schema in your project.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Check login
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}
$userId   = (int)$_SESSION['user']['id'];
$userName = $_SESSION['user']['ho_ten'] ?? $_SESSION['user']['name'] ?? 'Người dùng';
$userAvt  = $_SESSION['user']['avatar'] ?? '';
$firstChar = mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');

/* --------------------------------------------------------------
   1️⃣  Lấy dữ liệu thẻ thành viên (membership cards)
   -------------------------------------------------------------- */
$membershipCards = [];
if (isset($pdo)) {
    try {
        // TODO: replace `membership_cards` & column names with real ones
        $stmt = $pdo->prepare(
            "SELECT id, ten_the, loai_the, ngay_hieu_luc, ngay_het_han
               FROM membership_cards
              WHERE nguoi_dung_id = :uid"
        );
        $stmt->execute(['uid' => $userId]);
        $membershipCards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('[TicketBox profile] Membership query: ' . $e->getMessage());
    }
}

/* --------------------------------------------------------------
   2️⃣  Lấy danh sách các stars mà người dùng đang theo dõi
   -------------------------------------------------------------- */
$followedStars = [];
if (isset($pdo)) {
    try {
        // TODO: replace `ticketstar_theodoi` & column names with real ones
        $stmt = $pdo->prepare(
            "SELECT s.id, s.ten_su_kien, s.dia_diem, s.thoi_gian, s.avatar AS star_avatar
               FROM ticketstar_theodoi ts
               JOIN sukien s ON ts.star_id = s.id
              WHERE ts.nguoi_dung_id = :uid"
        );
        $stmt->execute(['uid' => $userId]);
        $followedStars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('[TicketBox profile] Followed stars query: ' . $e->getMessage());
    }
}
?>
<?php include '../templates/header.php'; ?>
<?php // include '../templates/navbar.php'; // Removed to avoid duplicate menu bar ?>

<main class="profile-page" style="background:#18191a; min-height:80vh; padding:40px 0;">
    <div class="container" style="max-width:900px; margin:auto; color:#fff;">

        <!-- Avatar & Tên -->
        <section class="profile-header" style="text-align:center; margin-bottom:40px;">
            <div class="avatar-wrapper" style="position:relative; display:inline-block;">
                <?php if (!empty($userAvt)): ?>
                    <img src="<?php echo htmlspecialchars($userAvt); ?>?v=<?php echo time(); ?>"
                         alt="Avatar"
                         style="width:120px;height:120px;border-radius:50%;object-fit:cover;
                                background:#26c973;">
                <?php else: ?>
                    <span style="display:inline-flex; width:120px; height:120px;
                               align-items:center; justify-content:center;
                               background:#26c973; color:#fff; font-size:48px;
                               border-radius:50%;"><?php echo $firstChar; ?></span>
                <?php endif; ?>
                <!-- nút chỉnh sửa -->
                <a href="edit-profile.php"
                   title="Chỉnh sửa hồ sơ"
                   style="position:absolute; right:-10px; top:-10px;
                          background:#444; color:#ccc; border-radius:50%;
                          width:32px; height:32px; display:flex;
                          align-items:center; justify-content:center;
                          text-decoration:none; font-size:18px;">
                    <i class="fa-solid fa-pen"></i>
                </a>
            </div>
            <h2 style="margin-top:15px; font-weight:700;">
                <?php echo htmlspecialchars($userName); ?>
            </h2>
        </section>

        <!-- Thẻ thành viên của tôi -->
        <section class="membership-section" style="margin-bottom:40px;">
            <h3 style="font-size:20px; margin-bottom:15px;">
                <i class="fa-solid fa-gem" style="color:#ffd700; margin-right:8px;"></i>
                Thẻ thành viên của tôi
            </h3>

            <?php if (empty($membershipCards)): ?>
                <p style="color:#777; font-style:italic;">
                    Bạn chưa có thẻ thành viên
                </p>
                <a href="my-tickets.php?tab=membership"
                   style="color:#26c973; text-decoration:none;">
                    Khám phá thêm &gt;
                </a>
            <?php else: ?>
                <div class="cards-grid" style="display:flex; flex-wrap:wrap; gap:20px;">
                    <?php foreach ($membershipCards as $card): ?>
                        <div class="card"
                             style="background:#272730; padding:15px; border-radius:8px;
                                    width:calc(50% - 10px);">
                            <h4 style="margin:0 0 5px 0;">
                                <?php echo htmlspecialchars($card['ten_the']); ?>
                            </h4>
                            <p style="margin:0; color:#a0a3bd;">
                                Loại: <?php echo htmlspecialchars($card['loai_the'] ?? ''); ?><br>
                                Hiệu lực: <?php echo htmlspecialchars($card['ngay_hieu_luc'] ?? '-'); ?>
                                – <?php echo htmlspecialchars($card['ngay_het_han'] ?? '-'); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <!-- Clear separator -->
        <div style="clear:both; margin-top:30px;"></div>

        <!-- Đang theo dõi -->
        <section class="followed-section">
            <h3 style="font-size:20px; margin-bottom:15px;">
                <i class="fa-solid fa-star" style="color:#ffd700; margin-right:8px;"></i>
                Đang theo dõi
            </h3>

            <?php if (empty($followedStars)): ?>
                <p style="color:#777; font-style:italic;">
                    Bạn chưa có danh sách theo dõi
                </p>
                <a href="my-tickets.php?tab=my-events"
                   style="color:#26c973; text-decoration:none;">
                    Khám phá thêm &gt;
                </a>
            <?php else: ?>
                <div class="stars-grid" style="display:flex; flex-wrap:wrap; gap:20px;">
                    <?php foreach ($followedStars as $star): ?>
                        <div class="star-card"
                             style="background:#272730; padding:15px; border-radius:8px;
                                    width:calc(33% - 13px); display:flex; align-items:center;">
                            <?php if (!empty($star['star_avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($star['star_avatar']); ?>"
                                     alt="Avatar" style="width:48px; height:48px;
                                     border-radius:50%; object-fit:cover; margin-right:10px;">
                            <?php else: ?>
                                <span style="width:48px; height:48px; background:#555;
                                            border-radius:50%; display:inline-block;
                                            margin-right:10px;"></span>
                            <?php endif; ?>
                            <div>
                                <strong style="display:block;"><?php echo htmlspecialchars($star['ten_su_kien']); ?></strong>
                                <small style="color:#a0a3bd;">
                                    <?php echo htmlspecialchars($star['dia_diem'] ?? ''); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php include '../templates/footer.php'; ?>