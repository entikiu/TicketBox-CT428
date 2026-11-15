<?php

class User
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): array|false
    {
        $sql = 'SELECT id, ho_ten, email, vai_tro
                FROM nguoidung
                WHERE id = :id
                LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail(string $email): array|false
    {
        $sql = "SELECT id, ho_ten, email, mat_khau, vai_tro
                FROM nguoidung
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(
        string $hoTen,
        string $email,
        string $matKhauHash
    ): int {
        $sql = "INSERT INTO nguoidung
                    (ho_ten, email, mat_khau)
                VALUES (:ho_ten, :email, :mat_khau)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'ho_ten' => $hoTen,
            'email' => $email,
            'mat_khau' => $matKhauHash
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
