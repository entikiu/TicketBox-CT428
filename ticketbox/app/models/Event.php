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
            'SELECT * FROM events WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách sự kiện đã xuất bản (published)
     */
    public function listPublished(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id, title, description, location, start_at,
                    end_at, price, capacity
             FROM events
             WHERE status = 'published'
             ORDER BY start_at ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm kiếm sự kiện + tính số lượt vé đã đăng ký (Tích hợp với API list & events.js)
     */
    public function searchEvents(string $keyword = '', string $status = ''): array
    {
        $sql = "SELECT 
                    e.id,
                    e.id AS ID,
                    e.title,
                    e.title AS TenSuKien,
                    e.description,
                    e.description AS MoTa,
                    e.location,
                    e.location AS DiaDiem,
                    e.start_at,
                    e.start_at AS ThoiGian,
                    e.end_at,
                    e.price,
                    e.price AS GiaVe,
                    e.capacity,
                    e.capacity AS SoLuongToiDa,
                    e.status,
                    e.status AS TrangThai,
                    COUNT(CASE WHEN r.status IN ('registered', 'checked_in', 'Registered', 'CheckedIn') THEN 1 END) AS SoNguoiDaDangKy
                FROM events e
                LEFT JOIN registrations r ON e.id = r.event_id
                WHERE 1=1";

        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (e.title LIKE :keyword OR e.location LIKE :keyword)";
            $params['keyword'] = '%' . $keyword . '%';
        }

        if ($status !== '') {
            $sql .= " AND e.status = :status";
            $params['status'] = $status;
        } else {
            $sql .= " AND e.status = 'published'";
        }

        $sql .= " GROUP BY e.id ORDER BY e.start_at ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm sự kiện mới
     */
    public function create(array $data, int $organizerId): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO events
             (title, description, location, start_at, end_at,
              price, capacity, organizer_id, status)
             VALUES
             (:title, :description, :location, :start_at, :end_at,
              :price, :capacity, :organizer_id, :status)'
        );

        $stmt->execute([
            'title'        => $data['title'],
            'description'  => $data['description'],
            'location'     => $data['location'],
            'start_at'     => $data['start_at'],
            'end_at'       => $data['end_at'],
            'price'        => $data['price'],
            'capacity'     => $data['capacity'],
            'organizer_id' => $organizerId,
            'status'       => $data['status'] ?? 'draft'
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Cập nhật thông tin sự kiện
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE events SET
                title = :title,
                description = :description,
                location = :location,
                start_at = :start_at,
                end_at = :end_at,
                price = :price,
                capacity = :capacity,
                status = :status
             WHERE id = :id'
        );

        $stmt->execute([
            'title'       => $data['title'],
            'description' => $data['description'],
            'location'    => $data['location'],
            'start_at'    => $data['start_at'],
            'end_at'      => $data['end_at'],
            'price'       => $data['price'],
            'capacity'    => $data['capacity'],
            'status'      => $data['status'],
            'id'          => $id
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Xóa sự kiện theo ID
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM events WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}