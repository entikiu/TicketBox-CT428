<?php
declare(strict_types=1);

class User
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): array|false
    {
        $sql = 'SELECT id, ho_ten, email, phone, dob, gender, avatar, mat_khau, vai_tro, ngay_tao
                FROM nguoidung
                WHERE id = :id
                LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail(string $email): array|false
    {
        $sql = 'SELECT id, ho_ten, email, phone, dob, gender, avatar, mat_khau, vai_tro, ngay_tao
                FROM nguoidung
                WHERE email = :email
                LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(
        string $hoTen,
        string $email,
        string $matKhauHash,
        string $vaiTro = 'Attendee'
    ): int {
        $sql = 'INSERT INTO nguoidung (ho_ten, email, mat_khau, vai_tro)
                VALUES (:ho_ten, :email, :mat_khau, :vai_tro)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'ho_ten'   => $hoTen,
            'email'    => $email,
            'mat_khau' => $matKhauHash,
            'vai_tro'  => $vaiTro
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}