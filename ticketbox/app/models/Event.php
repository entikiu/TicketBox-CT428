<?php
declare(strict_types=1);

class Event
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Lấy thông tin chi tiết 1 sự kiện theo ID
     */
    public function find(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id,
                    ten_su_kien, ten_su_kien AS title,
                    mo_ta, mo_ta AS description,
                    dia_diem, dia_diem AS location,
                    thoi_gian, thoi_gian AS start_at,
                    so_luong_toi_da, so_luong_toi_da AS capacity,
                    nguoi_tao,
                    ngay_tao,
                    hinh_anh
             FROM sukien
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm kiếm sự kiện theo từ khóa (Tên sự kiện hoặc Địa điểm)
     */
    public function searchEvents(string $keyword = ''): array
    {
        $sql = "SELECT s.id,
                       s.ten_su_kien, s.ten_su_kien AS title,
                       s.mo_ta, s.mo_ta AS description,
                       s.dia_diem, s.dia_diem AS location,
                       s.thoi_gian, s.thoi_gian AS start_at,
                       s.so_luong_toi_da, s.so_luong_toi_da AS capacity,
                       s.ngay_tao,
                       s.hinh_anh
                FROM sukien s
                LEFT JOIN ve v
                    ON s.id = v.su_kien_id
                    AND v.trang_thai IN ('thanh_cong', 'da_checkin')
                WHERE 1=1";

        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (s.ten_su_kien LIKE :kw_title OR s.dia_diem LIKE :kw_location)";
            $params['kw_title']    = '%' . $keyword . '%';
            $params['kw_location'] = '%' . $keyword . '%';
        }

        $sql .= " GROUP BY s.id
                  ORDER BY s.ngay_tao DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm sự kiện mới (CRUD Admin)
     */
    public function create(array $data, int $organizerId): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sukien
             (ten_su_kien, mo_ta, dia_diem, thoi_gian, so_luong_toi_da, nguoi_tao, ngay_tao, hinh_anh)
             VALUES
             (:ten_su_kien, :mo_ta, :dia_diem, :thoi_gian, :so_luong_toi_da, :nguoi_tao, NOW(), :hinh_anh)'
        );

        $stmt->execute([
            'ten_su_kien'     => $data['ten_su_kien'] ?? $data['title'],
            'mo_ta'           => $data['mo_ta'] ?? $data['description'] ?? null,
            'dia_diem'        => $data['dia_diem'] ?? $data['location'],
            'thoi_gian'       => $data['thoi_gian'] ?? $data['start_at'],
            'so_luong_toi_da' => $data['so_luong_toi_da'] ?? $data['capacity'],
            'nguoi_tao'       => $organizerId,
            'hinh_anh'        => $data['hinh_anh'] ?? null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Cập nhật thông tin sự kiện (CRUD Admin)
     */
    public function update(int $id, array $data): bool
    {
        $fields = [
            'ten_su_kien = :ten_su_kien',
            'mo_ta = :mo_ta',
            'dia_diem = :dia_diem',
            'thoi_gian = :thoi_gian',
            'so_luong_toi_da = :so_luong_toi_da',
        ];

        $params = [
            'ten_su_kien'     => $data['ten_su_kien'] ?? $data['title'],
            'mo_ta'           => $data['mo_ta'] ?? $data['description'] ?? null,
            'dia_diem'        => $data['dia_diem'] ?? $data['location'],
            'thoi_gian'       => $data['thoi_gian'] ?? $data['start_at'],
            'so_luong_toi_da' => $data['so_luong_toi_da'] ?? $data['capacity'],
            'id'              => $id,
        ];

        if (array_key_exists('hinh_anh', $data) || array_key_exists('image', $data)) {
            $fields[]           = 'hinh_anh = :hinh_anh';
            $params['hinh_anh'] = $data['hinh_anh'] ?? $data['image'] ?? null;
        }

        $sql  = 'UPDATE sukien SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Xóa sự kiện theo ID (CRUD Admin)
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM sukien WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}