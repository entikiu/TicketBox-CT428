CREATE DATABASE IF NOT EXISTS ticketbox
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ticketbox;

CREATE TABLE NguoiDung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ho_ten VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mat_khau VARCHAR(255) NOT NULL,
    vai_tro ENUM('Attendee', 'Organizer', 'Admin')
        NOT NULL DEFAULT 'Attendee',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE SuKien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_su_kien VARCHAR(200) NOT NULL,
    mo_ta TEXT,
    dia_diem VARCHAR(255),
    thoi_gian DATETIME NOT NULL,
    so_luong_toi_da INT UNSIGNED NOT NULL,
    nguoi_tao INT NOT NULL,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sukien_nguoidung
        FOREIGN KEY (nguoi_tao)
        REFERENCES NguoiDung(id),

    CONSTRAINT chk_soluong
        CHECK (so_luong_toi_da > 0)
);

CREATE TABLE DangKySuKien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nguoi_dung_id INT NOT NULL,
    su_kien_id INT NOT NULL,
    ngay_dang_ky TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trang_thai ENUM('Registered', 'Cancelled', 'CheckedIn')
        NOT NULL DEFAULT 'Registered',
    ma_ve VARCHAR(100) NOT NULL UNIQUE,

    CONSTRAINT fk_dangky_nguoidung
        FOREIGN KEY (nguoi_dung_id)
        REFERENCES NguoiDung(id),

    CONSTRAINT fk_dangky_sukien
        FOREIGN KEY (su_kien_id)
        REFERENCES SuKien(id),

    CONSTRAINT uq_dangky
        UNIQUE (nguoi_dung_id, su_kien_id)
);
