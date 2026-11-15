<?php
declare(strict_types=1);

class Event
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Lấy thông tin chi tiết 1 sự kiện theo ID (kèm tên danh mục)
     */
    public function find(int $id): array|false
    {
        $stmt =$this->pdo->prepare(
            'SELECT s.id,
                    s.ten_su_kien, s.ten_su_kien AS title,
                    s.mo_ta, s.mo_ta AS description,
                    s.dia_diem, s.dia_diem AS location,
                    s.gia_thap_nhat,
                    s.thoi_gian, s.thoi_gian AS start_at,
                    s.thoi_gian_hien_thi,
                    s.ten_dia_diem,
                    s.so_luong_toi_da, s.so_luong_toi_da AS capacity,
                    s.nguoi_tao,
                    s.ngay_tao,
                    s.hinh_anh,
                    s.ten_ban_to_chuc,
                    s.mo_ta_ban_to_chuc,
                    s.danh_muc_id,
                    dm.ten_danh_muc,
                    dm.slug AS danh_muc_slug
             FROM sukien s
             LEFT JOIN danh_muc dm ON s.danh_muc_id = dm.id
             WHERE s.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' =>$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm kiếm & lọc sự kiện theo từ khóa và danh mục
     */
    public function searchEvents(string $keyword = '', ?int $categoryId = null, string$categorySlug = ''): array
    {
        $sql = "SELECT s.id,
                       s.ten_su_kien, s.ten_su_kien AS title,
                       s.mo_ta, s.mo_ta AS description,
                       s.dia_diem, s.dia_diem AS location,
                       s.gia_thap_nhat,
                       s.thoi_gian, s.thoi_gian AS start_at,
                       s.thoi_gian_hien_thi,
                       s.ten_dia_diem,
                       s.so_luong_toi_da, s.so_luong_toi_da AS capacity,
                       s.ngay_tao,
                       s.hinh_anh,
                       s.ten_ban_to_chuc,
                       s.mo_ta_ban_to_chuc,
                       s.danh_muc_id,
                       dm.ten_danh_muc,
                       dm.slug AS danh_muc_slug,
                       COUNT(v.id) AS so_ve_da_ban,
                       GREATEST(0, CAST(s.so_luong_toi_da AS SIGNED) - COUNT(v.id)) AS so_ve_con_lai
                FROM sukien s
                LEFT JOIN danh_muc dm ON s.danh_muc_id = dm.id
                LEFT JOIN ve v
                    ON s.id = v.su_kien_id
                    AND v.trang_thai IN ('thanh-cong', 'da-checkin')
                WHERE 1=1";

        $params = [];

        if ($keyword !== '') {$sql .= " AND (
                s.ten_su_kien LIKE :kw_title
                OR s.dia_diem LIKE :kw_location
                OR s.ten_dia_diem LIKE :kw_venue
                OR s.ten_ban_to_chuc LIKE :kw_org
            )";
            $kw = '\%' .$keyword . '%';
            $params['kw_title']    =$kw;
            $params['kw_location'] =$kw;
            $params['kw_venue']    =$kw;
            $params['kw_org']      =$kw;
        }

        if ($categoryId !== null && $categoryId > 0) {$sql .= " AND s.danh_muc_id = :danh_muc_id";
            $params['danh_muc_id'] =$categoryId;
        } elseif ($categorySlug !== '') {$sql .= " AND dm.slug = :danh_muc_slug";
            $params['danh_muc_slug'] =$categorySlug;
        }

        $sql .= " GROUP BY s.id
                  ORDER BY s.thoi_gian ASC, s.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm sự kiện mới
     */
    public function create(array $data, int$organizerId): int
    {
        $stmt =$this->pdo->prepare(
            'INSERT INTO sukien
             (ten_su_kien, mo_ta, dia_diem, gia_thap_nhat, thoi_gian, thoi_gian_hien_thi, ten_dia_diem,
              so_luong_toi_da, nguoi_tao, ngay_tao, hinh_anh, ten_ban_to_chuc, mo_ta_ban_to_chuc, danh_muc_id)
             VALUES
             (:ten_su_kien, :mo_ta, :dia_diem, :gia_thap_nhat, :thoi_gian, :thoi_gian_hien_thi, :ten_dia_diem,
              :so_luong_toi_da, :nguoi_tao, NOW(), :hinh_anh, :ten_ban_to_chuc, :mo_ta_ban_to_chuc, :danh_muc_id)'
        );

        $stmt->execute([
            'ten_su_kien'       => $data['ten_su_kien'] ?? $data['title'],
            'mo_ta'             => $data['mo_ta'] ?? $data['description'] ?? null,
            'dia_diem'          => $data['dia_diem'] ?? $data['location'] ?? null,
            'gia_thap_nhat'     => max(0, (int)($data['gia_thap_nhat'] ?? 0)),
            'thoi_gian'         => $data['thoi_gian'] ?? $data['start_at'],
            'thoi_gian_hien_thi'=> $data['thoi_gian_hien_thi'] ?? null,
            'ten_dia_diem'      => $data['ten_dia_diem'] ?? null,
            'so_luong_toi_da'   => (int)($data['so_luong_toi_da'] ?? $data['capacity']),
            'nguoi_tao'         => $organizerId,
            'hinh_anh'          => $data['hinh_anh'] ?? $data['image'] ?? null,
            'ten_ban_to_chuc'   => $data['ten_ban_to_chuc'] ?? null,
            'mo_ta_ban_to_chuc' => $data['mo_ta_ban_to_chuc'] ?? null,
            'danh_muc_id'       => isset($data['danh_muc_id']) ? (int)$data['danh_muc_id'] : 1,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Cập nhật thông tin sự kiện
     */
    public function update(int $id, array$data): bool
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
            'dia_diem'        => $data['dia_diem'] ?? $data['location'] ?? null,
            'thoi_gian'       => $data['thoi_gian'] ?? $data['start_at'],
            'so_luong_toi_da' => (int)($data['so_luong_toi_da'] ?? $data['capacity']),
            'id'              => $id,
        ];

        $optionalCols = [
            'gia_thap_nhat',
            'thoi_gian_hien_thi',
            'ten_dia_diem',
            'hinh_anh',
            'ten_ban_to_chuc',
            'mo_ta_ban_to_chuc',
            'danh_muc_id',
        ];

        foreach ($optionalCols as$col) {
            if (array_key_exists($col, $data)) {$fields[]     = "{$col} = :{$col}";
                $params[$col] = $data[$col];
            }
        }

        $sql  = 'UPDATE sukien SET ' . implode(', ', $fields) . ' WHERE id = :id';$stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Xóa sự kiện theo ID
     */
    public function delete(int $id): bool
    {
        $stmt =$this->pdo->prepare('DELETE FROM sukien WHERE id = :id');
        $stmt->execute(['id' =>$id]);

        return $stmt->rowCount() > 0;
    }
}