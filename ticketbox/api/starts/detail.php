<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    api_response(405, [
        'success' => false,
        'message' => 'Chỉ hỗ trợ phương thức GET.'
    ]);
}

$slug = trim((string)($_GET['slug'] ?? ''));
$id   = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($slug === '' && (!$id || $id < 1)) {
    api_response(422, [
        'success' => false,
        'message' => 'Vui lòng cung cấp slug hoặc ID của Star.'
    ]);
}

try {
    if ($slug !== '') {
        $stmt = $pdo->prepare('SELECT * FROM ticketstars WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
    } else {
        $stmt = $pdo->prepare('SELECT * FROM ticketstars WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
    }

    $star = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$star) {
        api_response(404, [
            'success' => false,
            'message' => 'Không tìm thấy trang nghệ sĩ / ban tổ chức.'
        ]);
    }

    // 1. Lấy sự kiện nổi bật (nếu có)
    $featuredEvent = null;
    if (!empty($star['su_kien_noi_bat_id'])) {
        $stmtFeat = $pdo->prepare('SELECT * FROM sukien WHERE id = :id LIMIT 1');
        $stmtFeat->execute(['id' => (int)$star['su_kien_noi_bat_id']]);
        $featuredEvent = $stmtFeat->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // 2. Lấy danh sách sự kiện theo từ khóa phân tách bởi dấu '|'
    $keywords = array_values(array_filter(array_map('trim', explode('|', (string)$star['tu_khoa_su_kien']))));
    $events   = [];

    if (!empty($keywords)) {
        $conditions = [];
        $params     = [];
        foreach ($keywords as $idx => $kw) {
            $kTitle = ":kw_t_{$idx}";
            $kOrg   = ":kw_o_{$idx}";
            $conditions[]   = "(ten_su_kien LIKE {$kTitle} OR ten_ban_to_chuc LIKE {$kOrg})";
            $params[$kTitle] = '%' . $kw . '%';
            $params[$kOrg]   = '%' . $kw . '%';
        }

        $sqlEvents = 'SELECT * FROM sukien WHERE ' . implode(' OR ', $conditions) . ' ORDER BY thoi_gian ASC';
        $stmtEv    = $pdo->prepare($sqlEvents);
        $stmtEv->execute($params);
        $events = $stmtEv->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Lấy gói thẻ thành viên của nghệ sĩ (nếu có)
    $membership = null;
    if (!empty($star['nghe_si_membership'])) {
        $stmtMem = $pdo->prepare('SELECT * FROM the_thanh_vien WHERE nghe_si = :nghe_si LIMIT 1');
        $stmtMem->execute(['nghe_si' => $star['nghe_si_membership']]);
        $membership = $stmtMem->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    api_response(200, [
        'success'        => true,
        'data'           => $star,
        'featured_event' => $featuredEvent,
        'events'         => $events,
        'membership'     => $membership
    ]);
} catch (PDOException $e) {
    error_log('[TicketBox Star Detail] ' . $e->getMessage());
    api_response(500, [
        'success' => false,
        'message' => 'Lỗi máy chủ khi tải thông tin Star.'
    ]);
}