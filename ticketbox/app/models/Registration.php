<?php
declare(strict_types=1);

class Registration
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Tìm thông tin 1 vé theo ID
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.id,
                    r.ma_ve,
                    r.su_kien_id,
                    r.nguoi_dung_id,
                    r.loai_ve,
                    r.gia_ve,
                    r.trang_thai,
                    r.ngay_dat,
                    e.ten_su_kien,
                    e.dia_diem,
                    e.thoi_gian,
                    e.hinh_anh,
                    e.nguoi_tao
             FROM ve r
             INNER JOIN sukien e ON r.su_kien_id = e.id
             WHERE r.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm thông tin 1 vé theo mã vé (ma_ve)
     */
    public function findByCode(string $ticketCode): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.id,
                    r.ma_ve,
                    r.su_kien_id,
                    r.nguoi_dung_id,
                    r.loai_ve,
                    r.gia_ve,
                    r.trang_thai,
                    r.ngay_dat,
                    e.ten_su_kien,
                    e.dia_diem,
                    e.thoi_gian,
                    e.hinh_anh,
                    e.nguoi_tao
             FROM ve r
             INNER JOIN sukien e ON r.su_kien_id = e.id
             WHERE r.ma_ve = :ma_ve
             LIMIT 1'
        );
        $stmt->execute(['ma_ve' => $ticketCode]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách vé đã đặt của 1 người dùng
     */
    public function getByUser(int $userId, string $status = ''): array
    {
        $sql = 'SELECT r.id AS registration_id,
                       r.ma_ve,
                       r.trang_thai,
                       r.ngay_dat,
                       r.loai_ve,
                       r.gia_ve,
                       e.id AS event_id,
                       e.ten_su_kien,
                       e.dia_diem,
                       e.thoi_gian,
                       e.hinh_anh
                FROM ve r
                INNER JOIN sukien e ON r.su_kien_id = e.id
                WHERE r.nguoi_dung_id = :user_id';

        $params = ['user_id' => $userId];

        if ($status !== '') {
            $sql .= ' AND r.trang_thai = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY r.ngay_dat DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm số vé đang có hiệu lực của 1 sự kiện
     */
    public function countActiveByEvent(int $eventId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM ve
             WHERE su_kien_id = :event_id
               AND trang_thai IN ('thanh-cong', 'da-checkin')"
        );
        $stmt->execute(['event_id' => $eventId]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Kiểm tra người dùng đã có vé hợp lệ cho sự kiện này chưa
     */
    public function hasActiveRegistration(int $userId, int $eventId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1
             FROM ve
             WHERE nguoi_dung_id = :user_id
               AND su_kien_id = :event_id
               AND trang_thai IN ('thanh-cong', 'da-checkin')
             LIMIT 1"
        );
        $stmt->execute([
            'user_id'  => $userId,
            'event_id' => $eventId,
        ]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Tạo vé mới trong bảng `ve`
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO ve
             (ma_ve, su_kien_id, nguoi_dung_id, loai_ve, gia_ve, trang_thai, ngay_dat)
             VALUES
             (:ma_ve, :su_kien_id, :nguoi_dung_id, :loai_ve, :gia_ve, :trang_thai, NOW())"
        );

        $stmt->execute([
            'ma_ve'         => $data['ma_ve'],
            'su_kien_id'    => (int)$data['su_kien_id'],
            'nguoi_dung_id' => (int)$data['nguoi_dung_id'],
            'loai_ve'       => $data['loai_ve'] ?? 'Vé Tiêu Chuẩn',
            'gia_ve'        => $data['gia_ve'] ?? '0 đ',
            'trang_thai'    => $data['trang_thai'] ?? 'thanh-cong',
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Cập nhật trạng thái vé ('thanh-cong' | 'da-huy' | 'da-checkin')
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE ve SET trang_thai = :trang_thai WHERE id = :id'
        );
        $stmt->execute([
            'trang_thai' => $status,
            'id'         => $id,
        ]);

        return $stmt->rowCount() > 0;
    }
}