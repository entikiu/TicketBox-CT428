-- ============================================================================
-- CƠ SỞ DỮ LIỆU HOÀN CHỈNH: HỆ THỐNG ĐẶT VÉ SỰ KIỆN TICKETBOX
-- Phiên bản: MariaDB / MySQL (InnoDB, utf8mb4_unicode_ci)
-- Hướng dẫn: Import trực tiếp 1 lần vào phpMyAdmin hoặc MySQL CLI
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ----------------------------------------------------------------------------
-- KHỞI TẠO CƠ SỞ DỮ LIỆU `ticketbox`
-- ----------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `ticketbox` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ticketbox`;

-- ----------------------------------------------------------------------------
-- XÓA CÁC BẢNG CŨ NẾU ĐÃ TỒN TẠI (ĐẢM BẢO IMPORT LẠI KHÔNG LỖI)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `ve`;
DROP TABLE IF EXISTS `the_thanh_vien_nguoidung`;
DROP TABLE IF EXISTS `the_thanh_vien`;
DROP TABLE IF EXISTS `loaive`;
DROP TABLE IF EXISTS `sukien`;
DROP TABLE IF EXISTS `danh_muc`;
DROP TABLE IF EXISTS `nguoidung`;

-- ============================================================================
-- PHẦN I: CẤU TRÚC BẢNG & DỮ LIỆU MẪU
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. BẢNG NGƯỜI DÙNG (Tài khoản Khách hàng, Ban tổ chức & Admin)
-- ----------------------------------------------------------------------------

CREATE TABLE `nguoidung` (
  `id` int(11) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `gender` varchar(10) NOT NULL DEFAULT 'nam',
  `avatar` varchar(255) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `vai_tro` enum('Attendee','Organizer','Admin') NOT NULL DEFAULT 'Attendee',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `nguoidung` (`id`, `ho_ten`, `email`, `phone`, `dob`, `gender`, `avatar`, `mat_khau`, `vai_tro`, `ngay_tao`) VALUES
(1, 'Nguyen Van A', 'a@example.com', NULL, NULL, 'nam', NULL, '$2y$10$IYnOfBN19s5Bi2G5kiLGN.ikXMNpdE5JPkYWlVqKe/Q2Mn6PerUaC', 'Attendee', '2026-10-23 06:34:03'),
(2, 'Nguyen Thanh Huy', 'huyb2407528@student.ctu.edu.vn', '0123456789', '21/11/2004', 'nam', '../assets/uploads/avatars/avatar_2_1794239731.jpg', '$2y$10$dAhv67SOY/RydNTGk/gJNeTFDl7KjRrb1Wn0lxco1XQqx6UYSIg0i', 'Attendee', '2026-11-01 13:17:10'),
(4, 'Kim JiWon', 'nguyenhuynth2910@gmail.com', NULL, NULL, 'nam', NULL, '$2y$10$IMWgWlBW.S4AHbgmfRxG1.It20GNdVrUoGpfEtbXE/eVhtRaIekni', 'Attendee', '2026-11-02 15:45:56');


-- ----------------------------------------------------------------------------
-- 2. BẢNG DANH MỤC SỰ KIỆN
-- ----------------------------------------------------------------------------

CREATE TABLE `danh_muc` (
  `id` int(11) NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `danh_muc` (`id`, `ten_danh_muc`, `slug`) VALUES
(1, 'Concert & Âm nhạc', 'concert-am-nhac'),
(2, 'Thể thao & Giải trí', 'the-thao-giai-tri'),
(3, 'Workshop & Sáng tạo', 'workshop-sang-tao'),
(4, 'Sân khấu & Nghệ thuật', 'san-khau-nghe-thuat');


-- ----------------------------------------------------------------------------
-- 3. BẢNG SỰ KIỆN (52 sự kiện liên kết với bảng danh mục)
-- ----------------------------------------------------------------------------

CREATE TABLE `sukien` (
  `id` int(11) NOT NULL,
  `ten_su_kien` varchar(200) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `dia_diem` varchar(255) DEFAULT NULL,
  `gia_thap_nhat` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `thoi_gian` datetime NOT NULL,
  `thoi_gian_hien_thi` varchar(150) DEFAULT NULL,
  `ten_dia_diem` varchar(200) DEFAULT NULL,
  `so_luong_toi_da` int(10) UNSIGNED NOT NULL,
  `nguoi_tao` int(11) NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `hinh_anh` varchar(255) DEFAULT NULL,
  `ten_ban_to_chuc` varchar(200) DEFAULT NULL,
  `mo_ta_ban_to_chuc` varchar(255) DEFAULT NULL,
  `danh_muc_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sukien` (`id`, `ten_su_kien`, `mo_ta`, `dia_diem`, `gia_thap_nhat`, `thoi_gian`, `thoi_gian_hien_thi`, `ten_dia_diem`, `so_luong_toi_da`, `nguoi_tao`, `ngay_tao`, `hinh_anh`, `ten_ban_to_chuc`, `mo_ta_ban_to_chuc`, `danh_muc_id`) VALUES
(1, 'Live Concert SKYNOTE - Quốc Thiên', NULL, 'Phạm Hùng, Mễ Trì, Nam Từ Liêm, Hà Nội', 880000, '2026-11-29 19:30:00', '19:30 - 23:00, 29 Tháng 11, 2026', 'Trung tâm Hội nghị Quốc gia', 1000, 2, '2026-11-11 13:47:35', '../assets/images/events/banner-skynote.jpg', 'SKYNOTE Entertainment', 'Đơn vị sản xuất & Tổ chức đêm nhạc', 1),
(2, 'Những Thành Phố Mộng Mơ Year End 2026', NULL, '799 Nguyễn Văn Linh, Tân Phú, Quận 7, TP. Hồ Chí Minh', 499000, '2026-12-12 15:00:00', '15:00 - 22:30, 12 Tháng 12, 2026', 'SECC - Trung tâm Hội chợ & Triển lãm Sài Gòn', 2000, 2, '2026-11-11 13:47:48', '../assets/images/events/banner-ntpmm.jpg', 'Mộng Mơ Studio', 'Ban tổ chức chuỗi sự kiện âm nhạc Indie', 1),
(3, 'Mắt Nhắm Mắt Mở Concert', NULL, 'Văn Cao, Liễu Giai, Ba Đình, Hà Nội', 1600000, '2026-11-01 18:00:00', '18:00 - 22:00, 01 Tháng 11, 2026', 'Cung Thể Thao Quần Ngựa', 1000, 2, '2026-11-11 13:54:52', '../assets/images/events/banner-mnmm.jpg', '1PRODUCTION', 'Đơn vị quản lý & sản xuất', 1),
(4, 'Tinh Hà \"Say Hi\" Concert - Đêm 4', NULL, 'Đường Cầu Tứ Liên, Xã Đông Anh, Thành phố Hà Nội', 800000, '2026-11-15 19:00:00', '19:00 - 23:00, 15 Tháng 11, 2026', 'Trung tâm triển lãm Việt Nam (VEC)', 3000, 2, '2026-11-12 13:15:08', '../assets/images/events/banner-tinh-ha.jpg', 'Vie Channel', 'VieChannel: Đơn vị sản xuất', 1),
(5, 'The Starry - S\'NOW', NULL, '116 Nguyễn Du, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh', 800000, '2026-12-19 18:30:00', '18:30 - 22:30, 19 Tháng 12, 2026', 'Nhà thi đấu Nguyễn Du', 600, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-snow.jpg', 'The Starry Entertainment', 'Đơn vị tổ chức đêm nhạc giáng sinh', 1),
(6, 'GAI MEET - NHÀ RỰC RỠ', NULL, 'Vũ Tông Phan, An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 1500000, '2026-10-18 18:30:00', '18:30 - 22:00, 18 Tháng 10, 2026', 'The Ants Studio', 400, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-nha-ruc-ro.jpg', 'YEAH1 x 1PRODUCTION', 'Đơn vị hợp tác sản xuất chuỗi show', 1),
(7, '(S)TRONG \'DỊ KIỀU\' SHOW 2026: ĐẾN GIÂY CUỐI CÙNG', NULL, '8 Nguyễn Công Hoan, Ngọc Khánh, Ba Đình, Hà Nội', 1450000, '2026-11-07 19:00:00', '19:00 - 22:30, 07 Tháng 11, 2026', 'SOL 8 Live Stage', 350, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-strong.jpg', 'Strong Entertainment', 'Ban tổ chức show diễn âm nhạc', 1),
(8, 'GAI MEET - DỰ ÁN NHÂN TỐ X', NULL, 'Vũ Tông Phan, An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 1500000, '2026-11-01 18:30:00', '18:30 - 22:00, 01 Tháng 11, 2026', 'The Ants Studio', 400, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-nhan-to-x.jpg', 'YEAH1 x 1PRODUCTION', 'Đơn vị hợp tác sản xuất', 1),
(9, '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 1 & DAY 2', NULL, 'Đỗ Xuân Hợp, Phường An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 1000000, '2026-10-18 17:00:00', '17:00 - 23:00, 18 Tháng 10, 2026', 'The Global City', 5000, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-anh-trai.jpg', 'YeaH1 Group', 'Đơn vị sản xuất độc quyền', 1),
(10, 'HIỆN DIỆN - UPRIZE CONCERT', NULL, 'Đường Trần Hữu Dực, Cầu Diễn, Nam Từ Liêm, Hà Nội', 800000, '2026-12-12 19:00:00', '19:00 - 22:30, 12 Tháng 12, 2026', 'Cung Điền kinh Mỹ Đình', 1500, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-uprize.jpg', 'UPRIZE Entertainment', 'Đơn vị sản xuất & Tổ chức sự kiện', 1),
(11, 'Trải nghiệm bay dù lượn tại Sapa', NULL, 'Đèo Ô Quy Hồ, Sapa, Lào Cai', 2190000, '2026-08-01 08:00:00', '08:00 - 17:00, 01 Tháng 08, 2026', 'Điểm bay dù lượn Sapa', 100, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-du-luon-sapa.jpg', 'Sapa Paragliding Club', 'Đơn vị tổ chức trải nghiệm thể thao mạo hiểm', 2),
(12, 'SAIGON PRO-AM BASKETBALL CUP 2026 OFFICIAL MERCHANDISE', NULL, '2 Hồ Xuân Hương, Phường 6, Quận 3, TP. Hồ Chí Minh', 25000, '2026-09-30 09:00:00', '09:00 - 21:00, 30 Tháng 09, 2026', 'Nhà thi đấu Hồ Xuân Hương', 500, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-saigon-basketball.jpg', 'New Sports', 'Đơn vị tổ chức giải bóng rổ Saigon Pro-Am', 2),
(13, 'TRẢI NGHIỆM ĐƯỜNG ĐUA GO - KART ĐA TẦNG TRONG NHÀ ĐẦU TIÊN TẠI VIỆT NAM', NULL, 'Tầng 5, 161 Xa lộ Hà Nội, Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh', 0, '2026-10-19 10:00:00', '10:00 - 22:00 hàng ngày, 19 Tháng 10, 2026', 'Vincom Mega Mall Thảo Điền', 500, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-go-kart.jpg', 'INFINITY RACING', 'Hệ thống đua xe Go-Kart đa tầng trong nhà', 2),
(14, 'Queens Of Glory - Giải Thể Thao Điện Tử Vinh Quang Nữ Hoàng', NULL, '479B Hoàng Quốc Việt, Phường 13, Quận 10, TP. Hồ Chí Minh', 199000, '2026-10-30 14:00:00', '14:00 - 21:00, 30 Tháng 10, 2026', 'VPS ARENA', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-queens-glory.jpg', 'VPS Esports', 'Đơn vị sản xuất giải đấu Esport nữ', 2),
(15, 'LCP 2026 Promotion Series', NULL, '479B Hoàng Quốc Việt, Phường 13, Quận 10, TP. Hồ Chí Minh', 199000, '2026-11-10 13:00:00', '13:00 - 21:00, 10 Tháng 11, 2026', 'VPS ARENA', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-lcp-2026.jpg', 'Riot Games & VPS Entertainment', 'Ban tổ chức giải đấu Liên Minh Huyền Thoại LCP', 2),
(16, 'Lion Championship 37 - 2026', NULL, '101 Võ Chí Công, Tây Hồ, Hà Nội', 350000, '2026-11-21 19:00:00', '19:00 - 23:00, 21 Tháng 11, 2026', 'Nhà thi đấu Tây Hồ', 1200, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-lion-championship.jpg', 'Liên đoàn Võ thuật tổng hợp Việt Nam (VMMAF)', 'Giải vô địch MMA chuyên nghiệp Việt Nam', 2),
(17, 'VIEWING PARTY CHUNG KẾT TỔNG GIẢI ĐẤU VALORANT CHAMPIONS 2026', NULL, '101 Võ Chí Công, Phường Xuân La, Quận Tây Hồ, Hà Nội', 259000, '2026-10-18 11:00:00', '11:00 - 20:00, 18 Tháng 10, 2026', 'Nhà thi đấu Tây Hồ', 1000, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-valorant-viewing-party.jpg', 'Riot Games & VNG Games', 'Đơn vị phát hành & Ban tổ chức giải đấu Valorant tại Việt Nam', 2),
(18, 'LOALOA SHOW: VIỆT NAM BỪNG SÁNG', NULL, 'Quận 1, TP. Hồ Chí Minh', 1600000, '2026-10-27 19:00:00', '19:00 - 21:00, 27 Tháng 10, 2026', 'Nhà hát Sân khấu Nghệ thuật', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-loaloa-bung-sang.jpg', 'LoaLoa Entertainment', 'Đơn vị tổ chức show nghệ thuật văn hóa dân gian', 4),
(19, 'A LITTLE AUTUMN BAKING WORKSHOP: \"PUMPKIN SPICE CINNAMON ROLL\"', NULL, 'Quận 3, TP. Hồ Chí Minh', 400000, '2026-10-27 09:30:00', '09:30 - 12:00, 27 Tháng 10, 2026', 'Bakery Studio & Academy', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-baking-pumpkin.jpg', 'Little Autumn Studio', 'Lớp học làm bánh nghệ thuật & trải nghiệm', 3),
(20, 'The \"Traditional Water Puppet Show\" - Múa rối nước', NULL, '55B Nguyễn Thị Minh Khai, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh', 350000, '2026-10-27 18:30:00', '18:30 - 19:30, 27 Tháng 10, 2026', 'Nhà hát Múa rối nước Rồng Vàng', 250, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-mua-roi-nuoc.jpg', 'Nhà hát Múa rối nước Rồng Vàng', 'Đơn vị biểu diễn nghệ thuật múa rối nước truyền thống', 4),
(21, 'LOALOA SHOW: KHI DI SẢN HÓA VŨ ĐIỆU - Live The Legacy', NULL, 'Quận 1, TP. Hồ Chí Minh', 1600000, '2026-10-27 19:30:00', '19:30 - 21:30, 27 Tháng 10, 2026', 'Sân khấu Nghệ thuật Truyền thống', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-loaloa-di-san.jpg', 'LoaLoa Entertainment', 'Đơn vị sản xuất chuỗi show vũ điệu di sản', 4),
(22, 'Chào Show - The Sound of Vietnam', NULL, '2 Nguyễn Bỉnh Khiêm, Bến Nghé, Quận 1, TP. Hồ Chí Minh', 1300000, '2026-10-27 19:15:00', '19:15 - 21:00, 27 Tháng 10, 2026', 'Bảo tàng Lịch sử TP. Hồ Chí Minh', 150, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-chao-show.jpg', 'Chào Show Production', 'Đêm nhạc trình diễn âm nhạc dân tộc Việt Nam', 1),
(23, '[GARDEN ART] - ART WORKSHOP \"FRUIT MOCHI CHEESECAKE\"', NULL, 'Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh', 370000, '2026-10-28 14:00:00', '14:00 - 17:00, 28 Tháng 10, 2026', 'Garden Art Space', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-mochi-cheesecake.jpg', 'Garden Art Studio', 'Xưởng sáng tạo nghệ thuật & làm bánh', 3),
(24, 'ART WORKSHOP \"UJI MATCHA CHEESECAKE TARTE\"', NULL, 'Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh', 420000, '2026-10-28 09:00:00', '09:00 - 12:00, 28 Tháng 10, 2026', 'Garden Art Space', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-matcha-tarte.jpg', 'Garden Art Studio', 'Xưởng sáng tạo nghệ thuật & làm bánh', 3),
(25, 'ART WORKSHOP OIL PASTEL PAINTING \"MELTED THOUGHTS\"', NULL, 'Quận 3, TP. Hồ Chí Minh', 420000, '2026-10-28 14:30:00', '14:30 - 17:30, 28 Tháng 10, 2026', 'Atelier Painting Studio', 25, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-oil-pastel.jpg', 'Atelier Studio', 'Lớp học vẽ tranh sáp dầu nghệ thuật', 3),
(26, '[DỄ GARDEN] Moss Frame Workshop', NULL, 'Quận 1, TP. Hồ Chí Minh', 450000, '2026-09-16 09:00:00', '09:00 - 12:00, 16 Tháng 09, 2026', 'Dễ Garden Studio', 25, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-moss-frame.jpg', 'Dễ Garden', 'Xưởng thiết kế & sáng tạo không gian xanh', 3),
(27, '[LÁ NHÀ GARDEN] Workshop Decor bí ngô Halloween', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 293000, '2026-10-27 14:00:00', '14:00 - 17:00, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-decor-bi-ngo.jpg', 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3),
(28, '[LÁ NHÀ GARDEN] Workshop Terrarium Mini', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 395000, '2026-10-27 09:30:00', '09:30 - 11:30, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-terrarium-mini.jpg', 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3),
(29, '[LÁ NHÀ GARDEN] Tiểu cảnh Sen đá', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 385000, '2026-10-27 14:00:00', '14:00 - 16:30, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-tieu-canh-sen-da.jpg', 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3),
(30, '[LÁ NHÀ GARDEN] Workshop Nương gỗ', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 465000, '2026-10-27 09:00:00', '09:00 - 12:00, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-nuong-go.jpg', 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3),
(31, '[LÁ NHÀ GARDEN] Workshop Tranh thực vật ép Botanical art', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 305000, '2026-10-27 14:00:00', '14:00 - 16:30, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-botanical-art.jpg', 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3),
(32, '[LÁ NHÀ GARDEN] Workshop Tranh rêu độc bản', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 370000, '2026-10-27 09:30:00', '09:30 - 12:00, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-tranh-reu.jpg', 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3),
(33, '[LÁ NHÀ GARDEN] Workshop Túi thơm thảo mộc healing', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 215000, '2026-10-27 15:00:00', '15:00 - 17:00, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-tui-thom-thao-moc.jpg', 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3),
(34, 'Trải Nghiệm Bay Dù Lượn Mù Cang Chải', NULL, 'Đèo Khau Phạ, Huyện Mù Cang Chải, Tỉnh Yên Bái', 2190000, '2026-02-01 07:30:00', '07:30 - 17:00, 01 Tháng 02, 2026', 'Đèo Khau Phạ', 100, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-du-luon-mu-cang-chai.jpg', 'VietWings Paragliding', 'Đơn vị tổ chức dù lượn hàng đầu Việt Nam', 2),
(35, 'KHU VUI CHƠI NHÀ CHEN - ƯỚC MƠ XANH', NULL, 'Quận 7, TP. Hồ Chí Minh', 60000, '2026-07-28 08:00:00', '08:00 - 21:00 hàng ngày, 28 Tháng 07, 2026', 'Khu Vui Chơi Nhà Chen', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-nha-chen.jpg', 'Nhà Chen Kids', 'Khu vui chơi trải nghiệm sáng tạo dành cho gia đình', 2),
(36, 'DU LỊCH VĂN HÓA SUỐI TIÊN', NULL, '120 Xa lộ Hà Nội, Phường Tân Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 60000, '2026-10-01 07:30:00', '07:30 - 17:30 hàng ngày, 01 Tháng 10, 2026', 'Khu Du Lịch Văn Hóa Suối Tiên', 2000, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-suoi-tien.jpg', 'Suối Tiên Theme Park', 'Tập đoàn Du lịch Văn hóa Suối Tiên', 2),
(37, 'Bảo Tàng Phở - Bảo Tàng Ẩm Thực Đầu Tiên tại Việt Nam', NULL, 'Thành phố Nam Định, Tỉnh Nam Định', 200000, '2026-10-01 08:30:00', '08:30 - 18:00 hàng ngày, 01 Tháng 10, 2026', 'Bảo Tàng Phở Việt Nam', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-bao-tang-pho.jpg', 'Hiệp hội Văn hóa Ẩm thực Việt Nam', 'Đơn vị bảo tồn & phát triển văn hóa ẩm thực', 4),
(38, 'PHẬT BÁO NGHIỆM TRẤN - TRIỂN LÃM DI SẢN VĂN HOÁ PHẬT GIÁO ĐỘC BẢN', NULL, 'Quận 1, TP. Hồ Chí Minh', 150000, '2026-10-27 09:00:00', '09:00 - 21:00, 27 Tháng 10, 2026', 'Trung tâm Triển lãm Nghệ thuật', 400, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-phat-bao-nghiem-tran.jpg', 'Phật Báo Nghiệm Trấn', 'Ban tổ chức triển lãm di sản văn hóa Phật giáo', 4),
(39, 'City Sightseeing SaiGon: Xe Buýt 2 Tầng', NULL, 'Công trường Lam Sơn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 105000, '2026-10-27 08:00:00', '08:00 - 22:30 hàng ngày, 27 Tháng 10, 2026', 'Trạm Nhà Hát Thành Phố', 500, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-bus-2-tang.jpg', 'City Sightseeing Vietnam', 'Thương hiệu xe buýt ngắm cảnh thành phố 2 tầng', 2),
(40, 'TOUR ĐÊM VĂN MIẾU - VAN MIEU NIGHT TOUR', NULL, '58 Quốc Tử Giám, Văn Miếu, Đống Đa, Hà Nội', 0, '2026-10-28 19:00:00', '19:00 - 22:00 (Thứ 4, 6, 7 & CN), 28 Tháng 10, 2026', 'Di tích Quốc gia đặc biệt Văn Miếu - Quốc Tử Giám', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-tour-dem-van-mieu.jpg', 'Trung tâm Hoạt động VHKH Văn Miếu - Quốc Tử Giám', 'Đơn vị quản lý & tổ chức trải nghiệm đêm Đạo Học', 4),
(41, 'Vé Trải Nghiệm GenZania 2 tiếng', NULL, 'Quận 2, TP. Thủ Đức, TP. Hồ Chí Minh', 190000, '2026-10-30 10:00:00', '10:00 - 21:00, 30 Tháng 10, 2026', 'Tổ hợp Trải nghiệm GenZania', 400, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-genzania.jpg', 'GenZania Entertainment', 'Thế giới trò chơi & giải trí tương tác cho giới trẻ', 2),
(42, 'KỊCH ĐOÀN THÀNH LỘC: CÔ GIÁO DUYÊN', NULL, '62 Trần Hưng Đạo, Phường Phạm Ngũ Lão, Quận 1, TP. Hồ Chí Minh', 300000, '2026-10-28 19:30:00', '19:30 - 22:00, 28 Tháng 10, 2026', 'Sân khấu kịch Thiên Đăng', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-co-giao-duyen.jpg', 'Sân khấu kịch Thiên Đăng', 'Kịch đoàn nghệ sĩ Ưu tú Thành Lộc', 4),
(43, 'Hà Nội - Eifman Ballet Of ST. Petersubrg World Tour 2026', NULL, '1 Tràng Tiền, Phường Tràng Tiền, Quận Hoàn Kiếm, Hà Nội', 500000, '2026-10-28 19:30:00', '19:30 - 21:30, 28 Tháng 10, 2026', 'Nhà hát Lớn Hà Nội', 600, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-eifman-ballet.jpg', 'SPO & Eifman Ballet', 'Đoàn múa Ballet Hàn lâm Saint Petersburg', 4),
(44, 'KỊCH ĐOÀN THÀNH LỘC: NGŨ QUÝ TƯƠNG PHÙNG', NULL, '62 Trần Hưng Đạo, Phường Phạm Ngũ Lão, Quận 1, TP. Hồ Chí Minh', 300000, '2026-10-29 19:30:00', '19:30 - 22:00, 29 Tháng 10, 2026', 'Sân khấu kịch Thiên Đăng', 300, 2, '2026-11-12 13:41:35', '../assets/images/events/banner-ngu-quy-tuong-phung.jpg', 'Sân khấu kịch Thiên Đăng', 'Kịch đoàn nghệ sĩ Ưu tú Thành Lộc', 4),
(45, 'Nhà Hát Kịch IDECAF: Hồn Ai Nấy Giữ', NULL, '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 300000, '2026-10-29 19:30:00', '19:30 - 22:00, 29 Tháng 10, 2026', 'Nhà hát kịch IDECAF', 350, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-hon-ai-nay-giu.jpg', 'Nhà hát kịch IDECAF', 'Sân khấu kịch nói chuyên nghiệp tại TP. Hồ Chí Minh', 4),
(46, 'Show Thực Cảnh Anh Hùng Cờ Lau - Đinh Bộ Lĩnh', NULL, 'Quận Hoàn Kiếm, Hà Nội', 175000, '2026-10-29 19:30:00', '19:30 - 21:00, 29 Tháng 10, 2026', 'Sân khấu Thực cảnh Di sản Hà Nội', 500, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-anh-hung-co-lau.jpg', 'Nhà hát Nghệ thuật Đương đại', 'Đơn vị sản xuất chương trình nghệ thuật thực cảnh lịch sử', 4),
(47, 'CADAO Collective - New Taste Of Heritage', NULL, 'Quận 1, TP. Hồ Chí Minh', 500000, '2026-10-30 19:30:00', '19:30 - 22:00, 30 Tháng 10, 2026', 'Không gian Nghệ thuật Đương đại Sài Gòn', 250, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-cadao-collective.jpg', 'CADAO Collective', 'Dự án nghệ thuật giao thoa di sản và âm nhạc đương đại', 4),
(48, 'Nhà Hát Kịch IDECAF: Mẹ Tôi Là Đàn Ông', NULL, '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 300000, '2026-10-30 19:30:00', '19:30 - 22:00, 30 Tháng 10, 2026', 'Nhà hát kịch IDECAF', 350, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-me-toi-la-dan-ong.jpg', 'Nhà hát kịch IDECAF', 'Sân khấu kịch nói chuyên nghiệp tại TP. Hồ Chí Minh', 4),
(49, 'BÓNG MA RẠP HÁT LỆ HOA - SÂN KHẤU MỚI', NULL, 'Quận 5, TP. Hồ Chí Minh', 250000, '2026-10-30 19:30:00', '19:30 - 22:00, 30 Tháng 10, 2026', 'Sân khấu kịch Lệ Hoa', 300, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-bong-ma-le-hoa.jpg', 'Sân khấu Mới', 'Đơn vị sản xuất kịch tâm lý & kinh dị sân khấu', 4),
(50, 'Sân Khấu Thế Giới Trẻ: Mật Mã Cầu Cơ', NULL, '125 Cống Quỳnh, Phường Nguyễn Cư Trinh, Quận 1, TP. Hồ Chí Minh', 330000, '2026-10-30 20:00:00', '20:00 - 22:30, 30 Tháng 10, 2026', 'Sân khấu Thế Giới Trẻ', 350, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-mat-ma-cau-co.jpg', 'Sân khấu Nghệ thuật Thế Giới Trẻ', 'Sân khấu kịch giải trí dành cho khán giả trẻ', 4),
(51, 'Mystic Night Show - Magic & Comedy in Saigon', NULL, 'Quận 1, TP. Hồ Chí Minh', 850000, '2026-10-30 20:00:00', '20:00 - 22:00, 30 Tháng 10, 2026', 'Saigon Comedy & Magic Lounge', 200, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-mystic-night-show.jpg', 'Mystic Entertainment', 'Chương trình biểu diễn ảo thuật & hài kịch tương tác', 4),
(52, 'Cuộc Phiêu Lưu Kỳ Thú', NULL, 'Quận 1, TP. Hồ Chí Minh', 120000, '2026-10-31 16:00:00', '16:00 - 18:00, 31 Tháng 10, 2026', 'Nhà hát Thiếu nhi TP. Hồ Chí Minh', 300, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-cuoc-phieu-luu-ky-thu.jpg', 'Sân khấu Kịch Thiếu Nhi', 'Chương trình kịch nghệ thuật dành cho gia đình & thiếu nhi', 4);


-- ----------------------------------------------------------------------------
-- 4. BẢNG LOẠI VÉ (100 hạng vé chi tiết tương ứng 52 sự kiện)
-- ----------------------------------------------------------------------------

CREATE TABLE `loaive` (
  `id` int(11) NOT NULL,
  `su_kien_id` int(11) NOT NULL,
  `ten_loai_ve` varchar(150) NOT NULL,
  `gia_ve` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `da_het_ve` tinyint(1) NOT NULL DEFAULT 0,
  `so_luong` int(10) UNSIGNED NOT NULL DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `loaive` (`id`, `su_kien_id`, `ten_loai_ve`, `gia_ve`, `da_het_ve`, `so_luong`) VALUES
(1, 1, 'SKY LOUNGE', 8800000, 0, 100),
(2, 1, 'SVIP A', 5000000, 0, 100),
(3, 1, 'VIP A', 3000000, 0, 100),
(4, 1, 'FANZONE', 880000, 1, 100),
(5, 2, 'VÉ BƯỚC THONG RONG (GA)', 499000, 0, 100),
(6, 2, 'VÉ MỘNG MƠ (SVIP)', 1200000, 0, 100),
(7, 3, 'GA 1', 1600000, 0, 100),
(8, 3, 'VIP DỌC', 3200000, 0, 100),
(9, 4, 'SKY LOUNGE', 10000000, 0, 100),
(10, 4, 'SVIP A', 5000000, 0, 100),
(11, 4, 'FANZONE A', 2500000, 1, 100),
(12, 5, 'STANDARD', 800000, 0, 100),
(13, 5, 'VIP S\'NOW', 1800000, 0, 100),
(14, 6, 'GA RỰC RỠ', 1500000, 0, 100),
(15, 6, 'VIP MEET & GREET', 3000000, 0, 100),
(16, 7, 'VÉ DỊ KIỀU', 1450000, 0, 100),
(17, 7, 'VÉ ĐẾN GIÂY CUỐI CÙNG', 2800000, 0, 100),
(18, 8, 'NHÂN TỐ X - GA', 1500000, 0, 100),
(19, 8, 'NHÂN TỐ X - VIP', 3200000, 0, 100),
(20, 9, 'GA CHÔNG GAI', 1000000, 0, 100),
(21, 9, 'VIP ANH TRAI', 3500000, 0, 100),
(22, 9, 'PRESIDENT LOUNGE', 8000000, 1, 100),
(23, 10, 'GA HIỆN DIỆN', 800000, 0, 100),
(24, 10, 'VIP UPRIZE', 2200000, 0, 100),
(25, 11, 'VÉ BAY ĐƠN TRẢI NGHIỆM', 2190000, 0, 100),
(26, 11, 'VÉ BAY ĐÔI + PHIM PHOTO', 2890000, 0, 100),
(27, 12, 'MÓC KHÓA OFFICIAL', 25000, 0, 100),
(28, 12, 'ÁO T-SHIRT PRO-AM', 350000, 0, 100),
(29, 12, 'ÁO JERSEY THI ĐẤU', 275000, 0, 100),
(30, 13, 'VÉ VÀO CỔNG THAM QUAN', 0, 0, 100),
(31, 13, 'GÓI ĐUA 10 PHÚT (SINGLE)', 220000, 0, 100),
(32, 14, 'VÉ XEM THỜI TRANG & THI ĐẤU (STANDARD)', 199000, 0, 100),
(33, 14, 'VIP PASS + GIFTSET', 499000, 0, 100),
(34, 15, 'VÉ XEM TRỰC TIẾP (DAY PASS)', 199000, 0, 100),
(35, 15, 'VIP FANZONE PASS', 500000, 1, 100),
(36, 16, 'VÉ KHU C (STAND C)', 350000, 0, 100),
(37, 16, 'VÉ KHU B (STAND B)', 600000, 0, 100),
(38, 16, 'VÉ RINGSSIDE (OCTAGON VIP)', 2000000, 0, 100),
(39, 17, 'VÉ TIÊU CHUẨN (STANDARD)', 259000, 1, 100),
(40, 17, 'VÉ VIP + GIFTSET ĐẶC BIỆT', 499000, 1, 100),
(41, 18, 'VÉ HẠNG BẠC (SILVER)', 1600000, 0, 100),
(42, 18, 'VÉ HẠNG VÀNG (GOLD VIP)', 2500000, 0, 100),
(43, 19, 'VÉ THAM GIA WORKSHOP', 400000, 0, 100),
(44, 20, 'VÉ TIÊU CHUẨN', 350000, 0, 100),
(45, 20, 'VÉ VIP HÀNG ĐẦU', 450000, 0, 100),
(46, 21, 'VÉ THƯỜNG', 1600000, 0, 100),
(47, 21, 'VÉ VIP', 2200000, 0, 100),
(48, 22, 'VÉ THAM QUAN & XEM SHOW', 1300000, 0, 100),
(49, 22, 'VÉ PREMIUM CHÀO SHOW', 1800000, 0, 100),
(50, 23, 'VÉ WORKSHOP MOCHI', 370000, 0, 100),
(51, 24, 'VÉ WORKSHOP MATCHA TARTE', 420000, 0, 100),
(52, 25, 'VÉ VẼ TRANH (BAO GỒM DỤNG CỤ)', 420000, 0, 100),
(53, 26, 'VÉ WORKSHOP KHUNG RÊU', 450000, 0, 100),
(54, 27, 'VÉ DECOR BÍ NGÔ HALLOWEEN', 293000, 0, 100),
(55, 28, 'VÉ WORKSHOP TERRARIUM MINI', 395000, 0, 100),
(56, 29, 'VÉ TRỒNG TIỂU CẢNH SEN ĐÁ', 385000, 0, 100),
(57, 30, 'VÉ WORKSHOP NƯƠNG GỖ', 465000, 0, 100),
(58, 31, 'VÉ LÀM TRANH ÉP THỰC VẬT', 305000, 0, 100),
(59, 32, 'VÉ LÀM TRANH RÊU ĐỘC BẢN', 370000, 0, 100),
(60, 33, 'VÉ TÚI THƠM THẢO MỘC', 215000, 0, 100),
(61, 34, 'VÉ BAY ĐÔI CÙNG HLV', 2190000, 0, 100),
(62, 34, 'VÉ BAY ĐÔI + GÓI CHỤP MEDIA GOPRO', 2690000, 0, 100),
(63, 35, 'VÉ TRẺ EM (NGÀY THƯỜNG)', 60000, 0, 100),
(64, 35, 'VÉ TRẺ EM + NGƯỜI LỚN (CUỐI TUẦN)', 120000, 0, 100),
(65, 36, 'VÉ VÀO CỔNG TRẺ EM', 60000, 0, 100),
(66, 36, 'VÉ VÀO CỔNG NGƯỜI LỚN', 150000, 0, 100),
(67, 36, 'VÉ TRỌN GÓI BẮN SÚNG & BIỂN TIÊN ĐỒNG', 350000, 0, 100),
(68, 37, 'VÉ THAM QUAN & THƯỞNG THỨC PHỞ', 200000, 0, 100),
(69, 37, 'VÉ TRẢI NGHIỆM TRÁNG BÁNH PHỞ THỦ CÔNG', 350000, 0, 100),
(70, 38, 'VÉ THAM QUAN TRIỂN LÃM', 150000, 0, 100),
(71, 39, 'VÉ XE BUÝT 1 VÒNG (CHUYẾN NGÀY)', 105000, 0, 100),
(72, 39, 'VÉ XE BUÝT 1 VÒNG (CHUYẾN ĐÊM)', 145000, 0, 100),
(73, 39, 'VÉ HOP-ON HOP-OFF 24 GIỜ', 475000, 0, 100),
(74, 40, 'VÉ ĐĂNG KÝ TRẢI NGHIỆM MẪU', 0, 0, 100),
(75, 40, 'VÉ TOUR ĐÊM CHÍNH THỨC', 199000, 0, 100),
(76, 41, 'VÉ TRẢI NGHIỆM 2 TIẾNG', 190000, 0, 100),
(77, 41, 'VÉ KHÔNG GIỚI HẠN THỜI GIAN', 290000, 0, 100),
(78, 42, 'VÉ HẠNG LẦU (STANDARD)', 300000, 0, 100),
(79, 42, 'VÉ HẠNG TRỆT (VIP)', 500000, 0, 100),
(80, 43, 'VÉ HẠNG C (STANDARD)', 500000, 0, 100),
(81, 43, 'VÉ HẠNG B (VIP)', 1000000, 0, 100),
(82, 43, 'VÉ HẠNG A (VVIP)', 1500000, 0, 100),
(83, 44, 'VÉ HẠNG LẦU (STANDARD)', 300000, 0, 100),
(84, 44, 'VÉ HẠNG TRỆT (VIP)', 500000, 0, 100),
(85, 45, 'VÉ TIÊU CHUẨN (STANDARD)', 300000, 0, 100),
(86, 45, 'VÉ VIP', 450000, 0, 100),
(87, 46, 'VÉ PHỔ THÔNG', 175000, 0, 100),
(88, 46, 'VÉ KHÁN ĐÀI VIP', 350000, 0, 100),
(89, 47, 'VÉ STANDARD', 500000, 0, 100),
(90, 47, 'VÉ VIP EXPERIENCE', 850000, 0, 100),
(91, 48, 'VÉ TIÊU CHUẨN (STANDARD)', 300000, 0, 100),
(92, 48, 'VÉ VIP', 450000, 0, 100),
(93, 49, 'VÉ THƯỜNG', 250000, 0, 100),
(94, 49, 'VÉ VIP', 400000, 0, 100),
(95, 50, 'VÉ TIÊU CHUẨN', 330000, 0, 100),
(96, 50, 'VÉ VIP', 450000, 0, 100),
(97, 51, 'STANDARD SEAT', 850000, 0, 100),
(98, 51, 'VIP TABLE (INCL. DRINK)', 1350000, 0, 100),
(99, 52, 'VÉ TRẺ EM / TIÊU CHUẨN', 120000, 0, 100),
(100, 52, 'VÉ GIA ĐÌNH VIP', 250000, 0, 100);


-- ----------------------------------------------------------------------------
-- 5. BẢNG THẺ THÀNH VIÊN (7 gói Membership của nghệ sĩ & nhãn hiệu)
-- ----------------------------------------------------------------------------

CREATE TABLE `the_thanh_vien` (
  `id` int(11) NOT NULL,
  `ten_the` varchar(150) NOT NULL,
  `nghe_si` varchar(150) NOT NULL,
  `gia` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `thoi_han_thang` int(10) UNSIGNED NOT NULL DEFAULT 6,
  `hinh_anh` varchar(255) NOT NULL,
  `trang_thai` enum('dang-ban','da-ban-het','da-dong-ban') NOT NULL DEFAULT 'dang-ban',
  `quyen_loi` text DEFAULT NULL,
  `dieu_khoan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `the_thanh_vien` (`id`, `ten_the`, `nghe_si`, `gia`, `thoi_han_thang`, `hinh_anh`, `trang_thai`, `quyen_loi`, `dieu_khoan`) VALUES
(1, 'TIỂU NƠ', 'Ngọc Thanh Tâm', 499000, 12, '../assets/images/memberships/tieu-no.jpg', 'dang-ban', 'Ưu tiên mua vé sớm qua Presale trước khi mở bán công khai (tùy chương trình)\nƯu tiên nhận vé các sự kiện của Ngọc Thanh Tâm tham gia (tùy chương trình)\nSở hữu thẻ Membership vật lý độc quyền\nGiảm giá 5% khi mua Merchandise của Ngọc Thanh Tâm\nQuà tặng cuối năm từ Ngọc Thanh Tâm\n100 bạn mua sớm nhất nhận quà đặc biệt từ Ngọc Thanh Tâm', 'Thẻ TIỂU NƠ Membership là thẻ thành viên, không phải vé tham dự sự kiện và không đảm bảo quyền tham dự bất kỳ chương trình nào của nghệ sĩ.\nThẻ có giá trị 12 tháng kể từ ngày kích hoạt. Các quyền lợi đi kèm chỉ có hiệu lực trong thời hạn Membership.\nMỗi tài khoản được sở hữu tối đa 01 Membership và không được chuyển nhượng/chia sẻ cho tài khoản khác.\nThành viên có trách nhiệm bảo mật tài khoản và thông tin Membership của mình.\nMột số quyền lợi có thể giới hạn số lượng, áp dụng theo từng sự kiện/chương trình và theo nguyên tắc được BTC công bố tại từng thời điểm.\nQuyền lợi Membership không có giá trị quy đổi thành tiền mặt và không được hoàn phí sau khi đã kích hoạt, trừ trường hợp BTC có thông báo khác.\nBTC có quyền cập nhật cách thức triển khai quyền lợi nhưng không làm ảnh hưởng đến các quyền lợi đã được thành viên xác nhận/nhận trước thời điểm cập nhật.\nÁp dụng cho khách hàng từ đủ 16 tuổi.\nTrong trường hợp phát sinh tranh chấp liên quan đến việc áp dụng quyền lợi, quyết định của BTC được đưa ra căn cứ trên thể lệ đã công bố và quy định pháp luật hiện hành.\nĐối với các Thẻ đặt trước ngày 30/11/2026. Ticketbox sẽ bắt đầu giao đến Khách hàng từ ngày 15/12/2026'),
(2, 'Official Membership', 'SpaceSpeakers', 334800, 6, '../assets/images/memberships/spacespeakers.jpg', 'dang-ban', 'Đặc quyền mua vé Presale các concert & show diễn thuộc SpaceSpeakers Label\nSở hữu thẻ thành viên điện tử & vật lý phiên bản giới hạn\nGiảm giá 10% toàn bộ sản phẩm Merchandise chính thức\nTham gia các buổi Fan Meeting và Listening Party độc quyền', 'Thẻ Official Membership có thời hạn sử dụng 06 tháng kể từ ngày thanh toán thành công.\nMỗi tài khoản chỉ được đăng ký tối đa 01 gói thành viên trong cùng thời điểm.\nQuyền lợi không có giá trị quy đổi thành tiền mặt hoặc chuyển nhượng cho người khác.'),
(3, 'Jun Phạm', 'Jun Phạm', 247000, 6, '../assets/images/memberships/jun-pham.jpg', 'dang-ban', 'Ưu tiên mua vé sớm qua cổng Presale cho các sự kiện của Jun Phạm\nTruy cập nội dung hậu trường & nhật ký nghệ sĩ độc quyền\nNhận bộ quà tặng chào mừng thành viên mới (Welcome Kit)\nƯu đãi giảm giá khi mua ấn phẩm & Merchandise chính thức', 'Thẻ Jun Phạm Official Fanclub Membership có giá trị 06 tháng kể từ ngày kích hoạt.\nThẻ thành viên không thay thế cho vé vào cửa tại các sự kiện.\nÁp dụng cho thành viên từ đủ 16 tuổi trở lên.'),
(4, 'PHIỀN PHIỀN', 'Phiền Phiền', 199000, 6, '../assets/images/memberships/phien-phien.jpg', 'da-ban-het', 'Ưu tiên đặt vé sớm các đêm nhạc & Fan Meeting\nNhận huy hiệu thành viên độc quyền trên hệ thống Ticketbox\nGiảm 5% khi mua vật phẩm lưu niệm tại sự kiện', 'Thẻ có hiệu lực 06 tháng kể từ ngày kích hoạt.\nKhông áp dụng hoàn hủy sau khi đã kích hoạt thành công.'),
(5, 'SWAN PASS', 'Black Swan', 324000, 6, '../assets/images/memberships/swan-pass.jpg', 'da-ban-het', 'Đặc quyền mua vé hạng VIP/SVIP trước giờ mở bán công khai\nTặng kèm bộ Photocard & thẻ cứng SWAN PASS phiên bản đặc biệt\nƯu tiên check-in cổng riêng tại các sự kiện của nghệ sĩ', 'Thẻ SWAN PASS có thời hạn 06 tháng.\nKhông chuyển nhượng dưới mọi hình thức.'),
(6, 'THẺ CƯ DÂN', 'Những Thành Phố Mộng Mơ', 0, 6, '../assets/images/memberships/the-cu-dan.jpg', 'da-ban-het', 'Nhận thông báo sớm nhất về lịch trình tour diễn Những Thành Phố Mộng Mơ\nCơ hội săn mã giảm giá vé Early Bird dành riêng cho Cư Dân\nTích điểm đổi quà lưu niệm tại gian hàng sự kiện', 'Thẻ Cư Dân phát hành miễn phí với số lượng giới hạn.\nHiệu lực sử dụng 06 tháng kể từ ngày đăng ký.'),
(7, 'SKYNOTE Membership', 'Quốc Thiên', 99000, 6, '../assets/images/memberships/skynote.jpg', 'da-dong-ban', 'Ưu tiên mua vé sớm Live Concert SKYNOTE trước khi mở bán đại trà\nGiảm giá 5% khi đặt mua vé nhóm từ 4 vé trở lên\nCơ hội tham gia buổi Soundcheck trước giờ diễn', 'Thẻ SKYNOTE Membership có giá trị 06 tháng kể từ ngày kích hoạt.\nQuyền lợi Presale áp dụng tùy theo quy mô từng đêm diễn.');


-- ----------------------------------------------------------------------------
-- 6. BẢNG THẺ THÀNH VIÊN NGƯỜI DÙNG (Lịch sử kích hoạt & thời hạn thẻ)
-- ----------------------------------------------------------------------------

CREATE TABLE `the_thanh_vien_nguoidung` (
  `id` int(11) NOT NULL,
  `ma_the` varchar(50) NOT NULL,
  `the_id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `ngay_kich_hoat` datetime NOT NULL DEFAULT current_timestamp(),
  `ngay_het_han` datetime NOT NULL,
  `trang_thai` enum('dang-hoat-dong','da-het-han','da-huy') NOT NULL DEFAULT 'dang-hoat-dong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `the_thanh_vien_nguoidung` (`id`, `ma_the`, `the_id`, `nguoi_dung_id`, `ngay_kich_hoat`, `ngay_het_han`, `trang_thai`) VALUES
(1, 'MB-20261112-6157', 3, 2, '2026-11-12 21:53:20', '2027-05-12 15:53:20', 'dang-hoat-dong');


-- ----------------------------------------------------------------------------
-- 7. BẢNG VÉ ĐÃ ĐẶT (Lịch sử giao dịch đặt vé & trạng thái vé)
-- ----------------------------------------------------------------------------

CREATE TABLE `ve` (
  `id` int(11) NOT NULL,
  `ma_ve` varchar(100) NOT NULL,
  `su_kien_id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `loai_ve` varchar(100) NOT NULL DEFAULT 'Vé Tiêu Chuẩn',
  `gia_ve` varchar(50) NOT NULL DEFAULT '0 đ',
  `trang_thai` varchar(50) NOT NULL DEFAULT 'thanh-cong',
  `ngay_dat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ve` (`id`, `ma_ve`, `su_kien_id`, `nguoi_dung_id`, `loai_ve`, `gia_ve`, `trang_thai`, `ngay_dat`) VALUES
(1, 'TKB-2026-52313', 2, 2, 'VÉ BƯỚC THONG RONG (GA)', '499.000 đ', 'thanh-cong', '2026-11-11 13:49:02'),
(2, 'TKB-2026-50387', 1, 2, 'SKY LOUNGE', '8.800.000 đ', 'thanh-cong', '2026-11-11 13:49:08'),
(3, 'TKB-2026-45864', 3, 2, 'GA 1', '1.600.000 đ', 'thanh-cong', '2026-11-11 13:54:52'),
(4, 'TKB-2026-32935', 1, 2, 'SKY LOUNGE', '8.800.000 đ', 'thanh-cong', '2026-11-12 12:14:57'),
(5, 'TKB-2026-96655', 1, 2, 'SKY LOUNGE', '8.800.000 đ', 'da-huy', '2026-11-12 12:15:09'),
(6, 'TKB-2026-91893', 1, 2, 'SKY LOUNGE', '8.800.000 đ', 'da-huy', '2026-11-12 12:15:45'),
(7, 'TKB-2026-23304', 1, 2, 'SKY LOUNGE', '8.800.000 đ', 'da-huy', '2026-11-12 12:43:29'),
(8, 'TKB-2026-87689', 1, 2, 'SKY LOUNGE', '8.800.000 đ', 'da-huy', '2026-11-12 12:43:35'),
(9, 'TKB-20261112-2703', 1, 2, 'GA HIỆN DIỆN', '800.000 đ', 'da-huy', '2026-11-12 12:46:57'),
(10, 'TKB-20261112-7333', 1, 2, 'GA HIỆN DIỆN', '800.000 đ', 'da-huy', '2026-11-12 12:47:17'),
(11, 'TKB-20261112-2380', 1, 2, 'NHÂN TỐ X - GA', '1.500.000 đ', 'thanh-cong', '2026-11-12 12:55:41'),
(12, 'TKB-20261112-2964', 1, 2, 'NHÂN TỐ X - GA', '1.500.000 đ', 'thanh-cong', '2026-11-12 12:55:50'),
(13, 'TKB-20261112-1918', 1, 2, 'GA RỰC RỠ', '1.500.000 đ', 'thanh-cong', '2026-11-12 12:56:14'),
(14, 'TKB-20261112-8010', 1, 2, 'GA RỰC RỠ', '1.500.000 đ', 'thanh-cong', '2026-11-12 12:57:45'),
(15, 'TKB-20261112-3988', 1, 2, 'GA RỰC RỠ', '1.500.000 đ', 'thanh-cong', '2026-11-12 13:01:39'),
(16, 'TKB-20261112-3618', 1, 2, 'GA RỰC RỠ', '1.500.000 đ', 'thanh-cong', '2026-11-12 13:02:30'),
(17, 'TKB-20261112-2866', 1, 2, 'GA RỰC RỠ', '1.500.000 đ', 'thanh-cong', '2026-11-12 13:05:21'),
(18, 'TKB-20261112-6421', 1, 2, 'STANDARD', '800.000 đ', 'thanh-cong', '2026-11-12 13:05:47'),
(20, 'TKB-20261112-1250', 4, 2, 'GA RỰC RỠ', '1.500.000 đ', 'thanh-cong', '2026-11-12 13:15:08'),
(21, 'TKB-20261112-2954', 4, 2, 'GA RỰC RỠ', '1.500.000 đ', 'thanh-cong', '2026-11-12 13:15:21'),
(22, 'TKB-20261112-8351', 44, 2, 'VÉ TIÊU CHUẨN', '350.000 đ', 'thanh-cong', '2026-11-12 13:41:35'),
(26, 'TKB-20261112-8027', 7, 2, 'VÉ DỊ KIỀU', '1.450.000 đ', 'thanh-cong', '2026-11-12 14:04:48');


-- ============================================================================
-- PHẦN II: CHỈ MỤC (INDEX) & KHÓA CHÍNH (PRIMARY KEY)
-- ============================================================================

ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sukien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sukien_nguoidung` (`nguoi_tao`),
  ADD KEY `fk_sukien_danhmuc` (`danh_muc_id`);

ALTER TABLE `loaive`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_loaive_sukien` (`su_kien_id`);

ALTER TABLE `the_thanh_vien`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `the_thanh_vien_nguoidung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_the` (`ma_the`),
  ADD KEY `fk_ttv_the` (`the_id`),
  ADD KEY `fk_ttv_nguoidung` (`nguoi_dung_id`);

ALTER TABLE `ve`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_ve` (`ma_ve`),
  ADD KEY `fk_ve_sukien` (`su_kien_id`),
  ADD KEY `fk_ve_nguoidung` (`nguoi_dung_id`);

-- ============================================================================
-- PHẦN III: GIÁ TRỊ TỰ TĂNG (AUTO_INCREMENT)
-- ============================================================================

ALTER TABLE `nguoidung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `danh_muc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `sukien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

ALTER TABLE `loaive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

ALTER TABLE `the_thanh_vien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `the_thanh_vien_nguoidung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `ve`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

-- ============================================================================
-- PHẦN IV: RÀNG BUỘC KHÓA NGOẠI (FOREIGN KEY CONSTRAINTS)
-- ============================================================================

ALTER TABLE `sukien`
  ADD CONSTRAINT `fk_sukien_nguoidung` FOREIGN KEY (`nguoi_tao`) REFERENCES `nguoidung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sukien_danhmuc` FOREIGN KEY (`danh_muc_id`) REFERENCES `danh_muc` (`id`) ON DELETE SET NULL;

ALTER TABLE `loaive`
  ADD CONSTRAINT `fk_loaive_sukien` FOREIGN KEY (`su_kien_id`) REFERENCES `sukien` (`id`) ON DELETE CASCADE;

ALTER TABLE `the_thanh_vien_nguoidung`
  ADD CONSTRAINT `fk_ttv_nguoidung` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoidung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ttv_the` FOREIGN KEY (`the_id`) REFERENCES `the_thanh_vien` (`id`) ON DELETE CASCADE;

ALTER TABLE `ve`
  ADD CONSTRAINT `fk_ve_nguoidung` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoidung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ve_sukien` FOREIGN KEY (`su_kien_id`) REFERENCES `sukien` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
-- ngay 15/11/2026

-- 1. Tạo bảng ticketstars
DROP TABLE IF EXISTS `ticketstars`;
CREATE TABLE `ticketstars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(120) NOT NULL,
  `ten_hien_thi` varchar(150) NOT NULL,
  `loai` enum('NgheSi','BanToChuc','SanKhau') NOT NULL DEFAULT 'BanToChuc',
  `avatar` varchar(255) NOT NULL,
  `anh_bia` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `luot_theo_doi` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tu_khoa_su_kien` varchar(255) NOT NULL,
  `nghe_si_membership` varchar(150) DEFAULT NULL,
  `da_xac_minh` tinyint(1) NOT NULL DEFAULT 1,
  `thu_tu` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Nạp 28 Featured Stars chuẩn thứ tự Ticketbox
INSERT INTO `ticketstars` (`slug`, `ten_hien_thi`, `loai`, `avatar`, `anh_bia`, `mo_ta`, `luot_theo_doi`, `tu_khoa_su_kien`, `nghe_si_membership`, `da_xac_minh`, `thu_tu`) VALUES
('yeah1-family', 'YeaH1 Family', 'BanToChuc', '../assets/images/stars/yeah1.jpg', '../assets/images/events/banner-anh-trai.jpg', 'Tập đoàn giải trí & nhà sản xuất chuỗi concert quy mô lớn Anh Trai Vượt Ngàn Chông Gai, GAI MEET, UPRIZE.', 58420, 'Anh Trai Vượt Ngàn Chông Gai|GAI MEET|UPRIZE|YeaH1', NULL, 1, 1),
('vie-concert', 'Vie Concert', 'BanToChuc', '../assets/images/stars/vieconcert.jpg', '../assets/images/events/banner-tinh-ha.jpg', 'Đơn vị tổ chức và sản xuất chuỗi concert Tinh Hà "Say Hi" cùng các lễ hội âm nhạc hàng đầu.', 64150, 'Tinh Hà|Vie Channel', NULL, 1, 2),
('ss-label', 'SS Label', 'BanToChuc', '../assets/images/stars/sslabel.jpg', '../assets/images/memberships/spacespeakers.jpg', 'Hãng thu âm & công ty quản lý nghệ sĩ trực thuộc SpaceSpeakers Group.', 41200, 'Mắt Nhắm Mắt Mở|SpaceSpeakers', 'SpaceSpeakers', 1, 3),
('gai-meet', 'GAI MEET', 'BanToChuc', '../assets/images/stars/gaimeet.jpg', '../assets/images/events/banner-nha-ruc-ro.jpg', 'Chuỗi sự kiện giao lưu âm nhạc & Fan Meeting độc quyền đồng sản xuất bởi YEAH1 x 1PRODUCTION.', 29800, 'GAI MEET', NULL, 1, 4),
('quoc-thien', 'Quốc Thiên', 'NgheSi', '../assets/images/stars/quocthien.jpg', '../assets/images/events/banner-skynote.jpg', 'Ca sĩ Quốc Thiên - Chủ nhân chuỗi Live Concert SKYNOTE và gói hội viên SKYNOTE Membership.', 36500, 'SKYNOTE|Quốc Thiên', 'Quốc Thiên', 1, 5),
('ngoc-thanh-tam', 'Ngọc Thanh Tâm', 'NgheSi', '../assets/images/stars/ngocthanhtam.jpg', '../assets/images/memberships/tieu-no.jpg', 'Diễn viên Ngọc Thanh Tâm - Cộng đồng thành viên TIỂU NƠ Membership độc quyền trên Ticketbox.', 21400, 'Ngọc Thanh Tâm|TIỂU NƠ', 'Ngọc Thanh Tâm', 1, 6),
('dinh-manh-ninh', 'Đinh Mạnh Ninh', 'NgheSi', '../assets/images/stars/dinhmanhninh.jpg', '../assets/images/events/banner-skynote.jpg', 'Ca sĩ - Nhạc sĩ Đinh Mạnh Ninh với các đêm nhạc Acoustic và bộ sưu tập Official Merchandise.', 18750, 'Đinh Mạnh Ninh', NULL, 1, 7),
('phung-khanh-linh', 'Phùng Khánh Linh', 'NgheSi', '../assets/images/stars/phungkhanhlinh.jpg', '../assets/images/events/banner-ntpmm.jpg', 'Ca sĩ - Nhạc sĩ dòng nhạc Indie Pop / City Pop với chuỗi tour diễn và album được yêu thích.', 24900, 'Phùng Khánh Linh|Những Thành Phố Mộng Mơ', NULL, 1, 8),
('nhung-thanh-pho-mong-mo', 'Những Thành Phố Mộng Mơ', 'BanToChuc', '../assets/images/stars/ntpmm.jpg', '../assets/images/events/banner-ntpmm.jpg', 'Lễ hội âm nhạc Indie thường niên quy tụ dàn nghệ sĩ trẻ đình đám tại Hà Nội & TP. Hồ Chí Minh.', 55300, 'Những Thành Phố Mộng Mơ', 'Những Thành Phố Mộng Mơ', 1, 9),
('jun-pham', 'Jun Phạm', 'NgheSi', '../assets/images/stars/junpham.jpg', '../assets/images/memberships/jun-pham.jpg', 'Ca sĩ, diễn viên, nhà văn Jun Phạm - Official Fanclub & Membership trên Ticketbox.', 33900, 'Jun Phạm|Anh Trai Vượt Ngàn Chông Gai', 'Jun Phạm', 1, 10),
('subicha', 'Subicha', 'NgheSi', '../assets/images/stars/subicha.jpg', '../assets/images/events/banner-snow.jpg', 'Nghệ sĩ sáng tạo & biểu diễn độc lập đồng hành cùng các sự kiện giải trí giới trẻ.', 14200, 'Subicha|The Starry', NULL, 1, 11),
('hoang-dung', 'Hoàng Dũng', 'NgheSi', '../assets/images/stars/hoangdung.jpg', '../assets/images/events/banner-ntpmm.jpg', 'Ca sĩ - Nhạc sĩ Hoàng Dũng với chuỗi concert Yên và các đêm nhạc tự sự sâu lắng.', 42800, 'Hoàng Dũng|Những Thành Phố Mộng Mơ', NULL, 1, 12),
('cheng', 'Cheng', 'NgheSi', '../assets/images/stars/cheng.jpg', '../assets/images/events/banner-snow.jpg', 'Nghệ sĩ trẻ Cheng với phong cách âm nhạc hiện đại, sôi động.', 16400, 'Cheng|The Starry', NULL, 1, 13),
('mr-siro', 'Mr. Siro', 'NgheSi', '../assets/images/stars/mrsiro.jpg', '../assets/images/events/banner-skynote.jpg', 'Nhạc sĩ - Ca sĩ Mr. Siro với những bản Ballad chạm đến trái tim hàng triệu khán giả.', 49100, 'Mr. Siro', NULL, 1, 14),
('nguyen-van-chung', 'Nguyễn Văn Chung', 'NgheSi', '../assets/images/stars/nguyenvanchung.jpg', '../assets/images/events/banner-chao-show.jpg', 'Nhạc sĩ Nguyễn Văn Chung - Tác giả của hàng trăm ca khúc nhạc trẻ và thiếu nhi kinh điển.', 27600, 'Nguyễn Văn Chung', NULL, 1, 15),
('tang-phuc', 'Tăng Phúc', 'NgheSi', '../assets/images/stars/tangphuc.jpg', '../assets/images/events/banner-strong.jpg', 'Ca sĩ Tăng Phúc - Chủ nhân chuỗi đêm nhạc Sun Song và các bản cover triệu view.', 31200, 'Tăng Phúc|Sun Song', NULL, 1, 16),
('dj-mie', 'DJ Mie', 'NgheSi', '../assets/images/stars/djmie.jpg', '../assets/images/events/banner-tinh-ha.jpg', 'Nữ DJ hàng đầu Việt Nam khuấy đảo các lễ hội âm nhạc điện tử lớn.', 38900, 'DJ Mie', NULL, 1, 17),
('kich-idecaf', 'Kịch IDECAF', 'SanKhau', '../assets/images/stars/idecaf.jpg', '../assets/images/events/banner-hon-ai-nay-giu.jpg', 'Nhà hát kịch IDECAF - Sân khấu kịch nói chuyên nghiệp hàng đầu tại TP. Hồ Chí Minh.', 44700, 'IDECAF', NULL, 1, 18),
('phong-tra-ben-thanh', 'Phòng trà Bến Thành', 'SanKhau', '../assets/images/stars/phongtrabenthanh.jpg', '../assets/images/events/banner-chao-show.jpg', 'Không gian âm nhạc ấm cúng tại trung tâm Quận 1, nơi hội tụ các giọng ca hàng đầu Việt Nam.', 26300, 'Phòng trà Bến Thành', NULL, 1, 19),
('kich-doan-thanh-loc', 'KỊCH ĐOÀN THÀNH LỘC', 'SanKhau', '../assets/images/stars/thiendang.jpg', '../assets/images/events/banner-co-giao-duyen.jpg', 'Sân khấu kịch Thiên Đăng - Kịch đoàn của NSƯT Thành Lộc với các vở diễn nghệ thuật đặc sắc.', 39500, 'THÀNH LỘC|Thiên Đăng', NULL, 1, 20),
('san-khau-moi', 'Sân Khấu Mới', 'SanKhau', '../assets/images/stars/sankhaumoi.jpg', '../assets/images/events/banner-bong-ma-le-hoa.jpg', 'Đơn vị sản xuất các vở kịch tâm lý, kinh dị và giải trí đặc sắc tại TP. Hồ Chí Minh.', 19800, 'Sân Khấu Mới|LỆ HOA', NULL, 1, 21),
('sao-concert', 'SAO Concert', 'BanToChuc', '../assets/images/stars/saoconcert.jpg', '../assets/images/events/banner-uprize.jpg', 'Đơn vị tổ chức đại nhạc hội SAO Concert quy tụ dàn sao V-Pop đình đám.', 22400, 'SAO Concert', NULL, 1, 22),
('sound-healing-concert', 'SOUND HEALING CONCERT', 'BanToChuc', '../assets/images/stars/soundhealing.jpg', '../assets/images/events/banner-chao-show.jpg', 'Chuỗi hòa nhạc chữa lành kết hợp âm thanh chuông xoay và nhạc cụ truyền thống.', 15900, 'Sound Healing', NULL, 1, 23),
('nha-hat-kich-thanh-nien', 'Nhà Hát Kịch Thanh Niên', 'SanKhau', '../assets/images/stars/kichthanhnien.jpg', '../assets/images/events/banner-cuoc-phieu-luu-ky-thu.jpg', 'Sân khấu kịch trẻ trung, sáng tạo dành cho khán giả và gia đình tại TP. Hồ Chí Minh.', 23100, 'Thanh Niên|Thiếu Nhi', NULL, 1, 24),
('secret-garden', 'SECRET GARDEN', 'BanToChuc', '../assets/images/stars/secretgarden.jpg', '../assets/images/events/banner-moss-frame.jpg', 'Không gian biểu diễn âm nhạc Acoustic và nghệ thuật ẩn mình giữa lòng thành phố.', 17500, 'Secret Garden', NULL, 1, 25),
('cat-and-mouse', 'Cat & Mouse Live Music', 'BanToChuc', '../assets/images/stars/catandmouse.jpg', '../assets/images/events/banner-mystic-night-show.jpg', 'Chuỗi đêm nhạc Live Band và trình diễn nghệ thuật tương tác về đêm.', 16200, 'Cat & Mouse|Mystic Night', NULL, 1, 26),
('san-khau-the-gioi-tre', 'SÂN KHẤU THẾ GIỚI TRẺ', 'SanKhau', '../assets/images/stars/thegioitre.jpg', '../assets/images/events/banner-mat-ma-cau-co.jpg', 'Điểm hẹn sân khấu kịch giải trí sôi động tại 125 Cống Quỳnh, Quận 1, TP. Hồ Chí Minh.', 31800, 'Thế Giới Trẻ', NULL, 1, 27),
('loaloa-show', 'LOALOA SHOW', 'BanToChuc', '../assets/images/stars/loaloa.jpg', '../assets/images/events/banner-loaloa-bung-sang.jpg', 'Chuỗi chương trình nghệ thuật kết hợp văn hóa dân gian và vũ điệu đương đại Việt Nam.', 20700, 'LOALOA SHOW', NULL, 1, 28);

-- 3. Bổ sung sự kiện mẫu cho các Star chưa có sự kiện riêng trong bảng sukien (ID 53 -> 64)
INSERT INTO `sukien` (`id`, `ten_su_kien`, `dia_diem`, `gia_thap_nhat`, `thoi_gian`, `thoi_gian_hien_thi`, `ten_dia_diem`, `so_luong_toi_da`, `nguoi_tao`, `hinh_anh`, `ten_ban_to_chuc`, `mo_ta_ban_to_chuc`) VALUES
(53, '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 3 & DAY 4', 'Đỗ Xuân Hợp, Phường An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 1000000, '2026-12-05 17:00:00', '17:00 - 23:00, 05 Tháng 12, 2026', 'The Global City', 5000, 2, '../assets/images/events/banner-anh-trai.jpg', 'YeaH1 Family', 'Đơn vị sản xuất độc quyền'),
(54, 'NGỌC THANH TÂM FAN MEETING: TIỂU NƠ DAY 2026', 'Quận 1, TP. Hồ Chí Minh', 350000, '2026-12-10 18:30:00', '18:30 - 21:30, 10 Tháng 12, 2026', 'Nhà hát Bến Thành', 400, 2, '../assets/images/memberships/tieu-no.jpg', 'Ngọc Thanh Tâm', 'Fan Meeting & Ra mắt dự án mới'),
(55, 'ĐINH MẠNH NINH LIVE ACOUSTIC & OFFICIAL MERCHANDISE', 'Quận Hoàn Kiếm, Hà Nội', 450000, '2026-12-08 20:00:00', '20:00 - 22:30, 08 Tháng 12, 2026', 'Polygon Musik', 250, 2, '../assets/images/events/banner-skynote.jpg', 'Đinh Mạnh Ninh', 'Đêm nhạc Acoustic & Giao lưu khán giả'),
(56, 'JUN PHẠM FAN MEETING: XỨ SỞ MIÊN MAN', 'Quận 3, TP. Hồ Chí Minh', 490000, '2026-12-15 18:30:00', '18:30 - 21:30, 15 Tháng 12, 2026', 'Nhà hát Hòa Bình', 600, 2, '../assets/images/memberships/jun-pham.jpg', 'Jun Phạm', 'Sự kiện dành cho cộng đồng Fanclub Jun Phạm'),
(57, 'MR. SIRO LIVE CONCERT: CHẠM ĐÁY NỖI ĐAU', 'Quận 1, TP. Hồ Chí Minh', 900000, '2026-12-20 19:30:00', '19:30 - 22:30, 20 Tháng 12, 2026', 'Nhà hát Thành Phố', 500, 2, '../assets/images/events/banner-skynote.jpg', 'Mr. Siro', 'Đêm nhạc Ballad đặc biệt của Mr. Siro'),
(58, 'ĐÊM NHẠC NGUYỄN VĂN CHUNG: NHẬT KÝ CỦA MẸ', 'Quận 1, TP. Hồ Chí Minh', 500000, '2026-12-18 19:30:00', '19:30 - 22:00, 18 Tháng 12, 2026', 'Nhà hát Bến Thành', 450, 2, '../assets/images/events/banner-chao-show.jpg', 'Nguyễn Văn Chung', 'Đêm nhạc kỷ niệm chặng đường sáng tác'),
(59, 'TĂNG PHÚC LIVE SHOW: SUN SONG 2026', 'Thành phố Đà Lạt, Tỉnh Lâm Đồng', 650000, '2026-12-14 17:00:00', '17:00 - 20:30, 14 Tháng 12, 2026', 'Mây Lang Thang Đà Lạt', 400, 2, '../assets/images/events/banner-strong.jpg', 'Tăng Phúc', 'Đêm nhạc ngoài trời giữa thông reo Đà Lạt'),
(60, 'DJ MIE - NEON RAVE NIGHT PARTY', 'Quận 1, TP. Hồ Chí Minh', 400000, '2026-12-22 21:00:00', '21:00 - 01:00, 22 Tháng 12, 2026', 'Capital Theatre', 500, 2, '../assets/images/events/banner-tinh-ha.jpg', 'DJ Mie', 'Đêm nhạc điện tử EDM bùng nổ'),
(61, 'Phòng trà Bến Thành: Đêm Nhạc Tình Khúc Mùa Đông', '6 Mạc Đĩnh Chi, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 450000, '2026-11-28 20:30:00', '20:30 - 23:00, 28 Tháng 11, 2026', 'Phòng trà Bến Thành', 220, 2, '../assets/images/events/banner-chao-show.jpg', 'Phòng trà Bến Thành', 'Không gian âm nhạc sang trọng tại Quận 1'),
(62, 'SAO CONCERT 2026 - ĐÊM HỘI ÂM NHẠC', 'Quận 7, TP. Hồ Chí Minh', 750000, '2026-12-26 19:00:00', '19:00 - 23:00, 26 Tháng 12, 2026', 'SECC Outdoor', 2000, 2, '../assets/images/events/banner-uprize.jpg', 'SAO Concert', 'Đại nhạc hội quy tụ dàn sao V-Pop'),
(63, 'SOUND HEALING CONCERT: TĨNH LẶNG GIỮA PHỐ', 'Quận 3, TP. Hồ Chí Minh', 380000, '2026-12-06 19:00:00', '19:00 - 21:00, 06 Tháng 12, 2026', 'Không gian Nghệ thuật Thiền Âm', 120, 2, '../assets/images/events/banner-chao-show.jpg', 'Sound Healing', 'Trải nghiệm hòa nhạc chữa lành tâm hồn'),
(64, 'SECRET GARDEN LIVE SESSION: ĐÊM MƯA PHỐ THỊ', 'Quận 1, TP. Hồ Chí Minh', 320000, '2026-12-09 20:00:00', '20:00 - 22:15, 09 Tháng 12, 2026', 'Secret Garden Acoustic Lounge', 100, 2, '../assets/images/events/banner-moss-frame.jpg', 'Secret Garden', 'Đêm nhạc Acoustic thân mật');

INSERT INTO `loaive` (`su_kien_id`, `ten_loai_ve`, `gia_ve`, `da_het_ve`, `so_luong`) VALUES
(53, 'GA CHÔNG GAI', 1000000, 1, 0),
(54, 'VÉ TIỂU NƠ STANDARD', 350000, 0, 100),
(55, 'VÉ ACOUSTIC STANDARD', 450000, 0, 100),
(56, 'VÉ MIÊN MAN', 490000, 0, 100),
(57, 'VÉ BALLAD VIP', 900000, 0, 100),
(58, 'VÉ TIÊU CHUẨN', 500000, 0, 100),
(59, 'VÉ SUN SONG', 650000, 0, 100),
(60, 'VÉ RAVE PASS', 400000, 0, 100),
(61, 'VÉ PHỤ THU ĐÊM NHẠC', 450000, 0, 100),
(62, 'VÉ SAO FANZONE', 750000, 0, 100),
(63, 'VÉ TRẢI NGHIỆM', 380000, 0, 100),
(64, 'VÉ ACOUSTIC', 320000, 0, 100);




--------------- BO SUNG CÁC CỘT RIÊNG CHO TỪNG STAR TIÊU BIỂU --------------


-- 1. Thêm 3 cột riêng biệt vào bảng ticketstars
ALTER TABLE `ticketstars`
  ADD COLUMN `banner_star` varchar(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh banner riêng của Star' AFTER `avatar`,
  ADD COLUMN `su_kien_noi_bat_id` int(11) DEFAULT NULL COMMENT 'ID sự kiện nổi bật từ bảng sukien (NULL nếu không có)' AFTER `tu_khoa_su_kien`,
  ADD COLUMN `thong_bao` text DEFAULT NULL COMMENT 'Nội dung thông báo hiển thị trên trang Star' AFTER `mo_ta`;

-- 2. Gán đường dẫn thư mục riêng cho banner_star (bạn chỉ cần bỏ ảnh vào assets/images/stars/banners/)
UPDATE `ticketstars`
SET `banner_star` = CONCAT('../assets/images/stars/banners/', `slug`, '.jpg');

-- 3. Gán Sự kiện nổi bật & Thông báo mẫu cho các Star tiêu biểu (các Star khác để NULL sẽ tự ẩn khối này)
UPDATE `ticketstars`
SET `su_kien_noi_bat_id` = 3,
    `thong_bao` = 'Chào mừng bạn đến với kênh chính thức của SS Label trên Ticketbox! Theo dõi ngay để nhận thông báo mở bán vé Presale sớm nhất.'
WHERE `slug` = 'ss-label';

UPDATE `ticketstars`
SET `su_kien_noi_bat_id` = 9,
    `thong_bao` = 'Lịch mở bán vé các đêm diễn tiếp theo của chuỗi concert sẽ được cập nhật trực tiếp tại mục Lịch hoạt động.'
WHERE `slug` = 'yeah1-family';

UPDATE `ticketstars`
SET `su_kien_noi_bat_id` = 4,
    `thong_bao` = 'Theo dõi Vie Concert để không bỏ lỡ các suất mở bán vé sớm và quyền lợi Fanzone.'
WHERE `slug` = 'vie-concert';

UPDATE `ticketstars`
SET `su_kien_noi_bat_id` = 6
WHERE `slug` = 'gai-meet';

UPDATE `ticketstars`
SET `su_kien_noi_bat_id` = 1,
    `thong_bao` = 'Hội viên SKYNOTE Membership được ưu tiên đặt vé sớm trước giờ mở bán công khai.'
WHERE `slug` = 'quoc-thien';

UPDATE `ticketstars`
SET `su_kien_noi_bat_id` = 54
WHERE `slug` = 'ngoc-thanh-tam';

UPDATE `ticketstars`
SET `su_kien_noi_bat_id` = 2
WHERE `slug` = 'nhung-thanh-pho-mong-mo';

UPDATE `ticketstars`
SET `su_kien_noi_bat_id` = 56
WHERE `slug` = 'jun-pham';

UPDATE `ticketstars`
SET `su_kien_noi_bat_id` = 45
WHERE `slug` = 'kich-idecaf';

UPDATE `ticketstars`
SET `su_kien_noi_bat_id` = 42
WHERE `slug` = 'kich-doan-thanh-loc';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;