
<?php
// declare(strict_types=1);
class Event
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM events WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function listPublished(): array
    {
        $stmt = $this->pdo->query(
            "SELECT id, title, description, location, start_at,
                    end_at, price, capacity
             FROM events
             WHERE status = 'published'
             ORDER BY start_at ASC"
        );

        return $stmt->fetchAll();
    }

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
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'price' => $data['price'],
            'capacity' => $data['capacity'],
            'organizer_id' => $organizerId,
            'status' => $data['status'] ?? 'draft'
        ]);

        return (int)$this->pdo->lastInsertId();
    }

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
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'price' => $data['price'],
            'capacity' => $data['capacity'],
            'status' => $data['status'],
            'id' => $id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM events WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}
