<?php
header('Content-Type: application/json; charset=utf-8');

$dbPath = file_exists(__DIR__ . '/../../config/database.php')
    ? __DIR__ . '/../../config/database.php'
    : __DIR__ . '/../config/database.php';
require_once $dbPath;

$q          = trim($_GET['q'] ?? '');
$city       = trim($_GET['city'] ?? '');
$categoryId = (int)($_GET['category_id'] ?? 0);

try {
    $where  = ["1=1"];
    $params = [];

    // Tìm theo tên sự kiện, địa chỉ/địa điểm, hoặc tên ban tổ chức
    if ($q !== '') {
        $where[]       = "(s.ten_su_kien LIKE :q1 OR s.dia_diem LIKE :q2 OR s.ten_ban_to_chuc LIKE :q3)";
        $kw            = '%' . $q . '%';
        $params[':q1'] = $kw;
        $params[':q2'] = $kw;
        $params[':q3'] = $kw;
    }

    // Lọc theo thành phố / địa chỉ (cột dia_diem trong bảng sukien)
    if ($city !== '') {
        if (mb_strtolower($city, 'UTF-8') === 'vị trí khác' || mb_strtolower($city, 'UTF-8') === 'sapa') {
            $where[] = "(s.dia_diem NOT LIKE '%Hồ Chí Minh%' AND s.dia_diem NOT LIKE '%Hà Nội%' AND s.dia_diem NOT LIKE '%Đà Lạt%')";
        } else {
            $where[]         = "s.dia_diem LIKE :city";
            $params[':city'] = '%' . $city . '%';
        }
    }

    // Lọc theo danh mục (đồng bộ logic bảng danh_muc)
    if ($categoryId > 0) {
        if ($categoryId === 5) {
            $where[] = "(s.danh_muc_id = 5 OR s.id IN (11, 13, 34, 35, 36, 37, 38, 39, 40, 41))";
        } elseif ($categoryId === 2) {
            $where[] = "(s.danh_muc_id = 2 AND s.id NOT IN (35, 36, 39, 41))";
        } else {
            $where[]           = "s.danh_muc_id = :cat_id";
            $params[':cat_id'] = $categoryId;
        }
    }

    $sql = "SELECT s.id, s.ten_su_kien, s.hinh_anh, s.gia_thap_nhat, s.thoi_gian, s.dia_diem
            FROM sukien s
            WHERE " . implode(' AND ', $where) . "
            ORDER BY s.thoi_gian DESC, s.id DESC
            LIMIT 6";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $events], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}