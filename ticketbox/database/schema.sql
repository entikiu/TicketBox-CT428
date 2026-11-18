SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- 1. Bảng `danh_muc`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `danh_muc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten_danh_muc` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `danh_muc` (`id`, `ten_danh_muc`, `slug`) VALUES
(1, 'Concert & Âm nhạc', 'concert-am-nhac'),
(2, 'Thể thao & Giải trí', 'the-thao-giai-tri'),
(3, 'Workshop & Sáng tạo', 'workshop-sang-tao'),
(4, 'Sân khấu & Nghệ thuật', 'san-khau-nghe-thuat')
ON DUPLICATE KEY UPDATE
  `ten_danh_muc` = VALUES(`ten_danh_muc`),
  `slug` = VALUES(`slug`);

ALTER TABLE `danh_muc` AUTO_INCREMENT = 5;

-- --------------------------------------------------------
-- 2. Bảng `nguoidung`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `nguoidung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `gender` varchar(10) NOT NULL DEFAULT 'nam',
  `avatar` varchar(255) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `vai_tro` enum('Attendee','Organizer','Admin') NOT NULL DEFAULT 'Attendee',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `nguoidung` (`id`, `ho_ten`, `email`, `phone`, `dob`, `gender`, `avatar`, `mat_khau`, `vai_tro`, `ngay_tao`) VALUES
(1, 'Nguyen Van A', 'a@example.com', NULL, NULL, 'nam', NULL, '$2y$10$IYnOfBN19s5Bi2G5kiLGN.ikXMNpdE5JPkYWlVqKe/Q2Mn6PerUaC', 'Attendee', '2026-10-23 06:34:03'),
(2, 'Nguyen Thanh Huy', 'huyb2407528@student.ctu.edu.vn', '0123456789', '21/11/2004', 'nam', '../assets/uploads/avatars/avatar_2_1794239731.jpg', '$2y$10$dAhv67SOY/RydNTGk/gJNeTFDl7KjRrb1Wn0lxco1XQqx6UYSIg0i', 'Attendee', '2026-11-01 13:17:10'),
(4, 'Kim JiWon', 'nguyenhuynth2910@gmail.com', '0987654321', '21/11/2004', 'nam', '../assets/uploads/avatars/avatar_4_1794728506.jpg', '$2y$10$IMWgWlBW.S4AHbgmfRxG1.It20GNdVrUoGpfEtbXE/eVhtRaIekni', 'Attendee', '2026-11-02 15:45:56')
ON DUPLICATE KEY UPDATE
  `ho_ten` = VALUES(`ho_ten`),
  `email` = VALUES(`email`),
  `phone` = VALUES(`phone`),
  `dob` = VALUES(`dob`),
  `gender` = VALUES(`gender`),
  `avatar` = VALUES(`avatar`),
  `mat_khau` = VALUES(`mat_khau`),
  `vai_tro` = VALUES(`vai_tro`),
  `ngay_tao` = VALUES(`ngay_tao`);

ALTER TABLE `nguoidung` AUTO_INCREMENT = 5;

-- --------------------------------------------------------
-- 3. Bảng `the_thanh_vien`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `the_thanh_vien` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten_the` varchar(150) NOT NULL,
  `nghe_si` varchar(150) NOT NULL,
  `gia` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `thoi_han_thang` int(10) UNSIGNED NOT NULL DEFAULT 6,
  `hinh_anh` varchar(255) NOT NULL,
  `trang_thai` enum('dang-ban','da-ban-het','da-dong-ban') NOT NULL DEFAULT 'dang-ban',
  `quyen_loi` text DEFAULT NULL,
  `dieu_khoan` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `the_thanh_vien` (`id`, `ten_the`, `nghe_si`, `gia`, `thoi_han_thang`, `hinh_anh`, `trang_thai`, `quyen_loi`, `dieu_khoan`) VALUES
(1, 'TIỂU NƠ', 'Ngọc Thanh Tâm', 499000, 12, '../assets/images/memberships/tieu-no.jpg', 'dang-ban', 'Ưu tiên mua vé sớm qua Presale trước khi mở bán công khai (tùy chương trình)\nƯu tiên nhận vé các sự kiện của Ngọc Thanh Tâm tham gia (tùy chương trình)\nSở hữu thẻ Membership vật lý độc quyền\nGiảm giá 5% khi mua Merchandise của Ngọc Thanh Tâm\nQuà tặng cuối năm từ Ngọc Thanh Tâm\n100 bạn mua sớm nhất nhận quà đặc biệt từ Ngọc Thanh Tâm', 'Thẻ TIỂU NƠ Membership là thẻ thành viên, không phải vé tham dự sự kiện và không đảm bảo quyền tham dự bất kỳ chương trình nào của nghệ sĩ.\nThẻ có giá trị 12 tháng kể từ ngày kích hoạt. Các quyền lợi đi kèm chỉ có hiệu lực trong thời hạn Membership.\nMỗi tài khoản được sở hữu tối đa 01 Membership và không được chuyển nhượng/chia sẻ cho tài khoản khác.\nThành viên có trách nhiệm bảo mật tài khoản và thông tin Membership của mình.\nMột số quyền lợi có thể giới hạn số lượng, áp dụng theo từng sự kiện/chương trình và theo nguyên tắc được BTC công bố tại từng thời điểm.\nQuyền lợi Membership không có giá trị quy đổi thành tiền mặt và không được hoàn phí sau khi đã kích hoạt, trừ trường hợp BTC có thông báo khác.\nBTC có quyền cập nhật cách thức triển khai quyền lợi nhưng không làm ảnh hưởng đến các quyền lợi đã được thành viên xác nhận/nhận trước thời điểm cập nhật.\nÁp dụng cho khách hàng từ đủ 16 tuổi.\nTrong trường hợp phát sinh tranh chấp liên quan đến việc áp dụng quyền lợi, quyết định của BTC được đưa ra căn cứ trên thể lệ đã công bố và quy định pháp luật hiện hành.\nĐối với các Thẻ đặt trước ngày 30/11/2026. Ticketbox sẽ bắt đầu giao đến Khách hàng từ ngày 15/12/2026'),
(2, 'Official Membership', 'SpaceSpeakers', 334800, 6, '../assets/images/memberships/spacespeakers.jpg', 'dang-ban', 'Đặc quyền mua vé Presale các concert & show diễn thuộc SpaceSpeakers Label\nSở hữu thẻ thành viên điện tử & vật lý phiên bản giới hạn\nGiảm giá 10% toàn bộ sản phẩm Merchandise chính thức\nTham gia các buổi Fan Meeting và Listening Party độc quyền', 'Thẻ Official Membership có thời hạn sử dụng 06 tháng kể từ ngày thanh toán thành công.\nMỗi tài khoản chỉ được đăng ký tối đa 01 gói thành viên trong cùng thời điểm.\nQuyền lợi không có giá trị quy đổi thành tiền mặt hoặc chuyển nhượng cho người khác.'),
(3, 'Jun Phạm', 'Jun Phạm', 247000, 6, '../assets/images/memberships/jun-pham.jpg', 'dang-ban', 'Ưu tiên mua vé sớm qua cổng Presale cho các sự kiện của Jun Phạm\nTruy cập nội dung hậu trường & nhật ký nghệ sĩ độc quyền\nNhận bộ quà tặng chào mừng thành viên mới (Welcome Kit)\nƯu đãi giảm giá khi mua ấn phẩm & Merchandise chính thức', 'Thẻ Jun Phạm Official Fanclub Membership có giá trị 06 tháng kể từ ngày kích hoạt.\nThẻ thành viên không thay thế cho vé vào cửa tại các sự kiện.\nÁp dụng cho thành viên từ đủ 16 tuổi trở lên.'),
(4, 'PHIỀN PHIỀN', 'Phiền Phiền', 199000, 6, '../assets/images/memberships/phien-phien.jpg', 'da-ban-het', 'Ưu tiên đặt vé sớm các đêm nhạc & Fan Meeting\nNhận huy hiệu thành viên độc quyền trên hệ thống Ticketbox\nGiảm 5% khi mua vật phẩm lưu niệm tại sự kiện', 'Thẻ có hiệu lực 06 tháng kể từ ngày kích hoạt.\nKhông áp dụng hoàn hủy sau khi đã kích hoạt thành công.'),
(5, 'SWAN PASS', 'Black Swan', 324000, 6, '../assets/images/memberships/swan-pass.jpg', 'da-ban-het', 'Đặc quyền mua vé hạng VIP/SVIP trước giờ mở bán công khai\nTặng kèm bộ Photocard & thẻ cứng SWAN PASS phiên bản đặc biệt\nƯu tiên check-in cổng riêng tại các sự kiện của nghệ sĩ', 'Thẻ SWAN PASS có thời hạn 06 tháng.\nKhông chuyển nhượng dưới mọi hình thức.'),
(6, 'THẺ CƯ DÂN', 'Những Thành Phố Mộng Mơ', 0, 6, '../assets/images/memberships/the-cu-dan.jpg', 'da-ban-het', 'Nhận thông báo sớm nhất về lịch trình tour diễn Những Thành Phố Mộng Mơ\nCơ hội săn mã giảm giá vé Early Bird dành riêng cho Cư Dân\nTích điểm đổi quà lưu niệm tại gian hàng sự kiện', 'Thẻ Cư Dân phát hành miễn phí với số lượng giới hạn.\nHiệu lực sử dụng 06 tháng kể từ ngày đăng ký.'),
(7, 'SKYNOTE Membership', 'Quốc Thiên', 99000, 6, '../assets/images/memberships/skynote.jpg', 'da-dong-ban', 'Ưu tiên mua vé sớm Live Concert SKYNOTE trước khi mở bán đại trà\nGiảm giá 5% khi đặt mua vé nhóm từ 4 vé trở lên\nCơ hội tham gia buổi Soundcheck trước giờ diễn', 'Thẻ SKYNOTE Membership có giá trị 06 tháng kể từ ngày kích hoạt.\nQuyền lợi Presale áp dụng tùy theo quy mô từng đêm diễn.')
ON DUPLICATE KEY UPDATE
  `ten_the` = VALUES(`ten_the`),
  `nghe_si` = VALUES(`nghe_si`),
  `gia` = VALUES(`gia`),
  `thoi_han_thang` = VALUES(`thoi_han_thang`),
  `hinh_anh` = VALUES(`hinh_anh`),
  `trang_thai` = VALUES(`trang_thai`),
  `quyen_loi` = VALUES(`quyen_loi`),
  `dieu_khoan` = VALUES(`dieu_khoan`);

ALTER TABLE `the_thanh_vien` AUTO_INCREMENT = 8;

-- --------------------------------------------------------
-- 4. Bảng `sukien`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sukien` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_tao_id` int(11) DEFAULT NULL,
  `ten_su_kien` varchar(200) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `dia_diem` varchar(255) DEFAULT NULL,
  `hinh_thuc` enum('offline','online') NOT NULL DEFAULT 'offline',
  `link_online` varchar(500) DEFAULT NULL,
  `gia_thap_nhat` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `thoi_gian` datetime NOT NULL,
  `thoi_gian_ket_thuc` datetime DEFAULT NULL,
  `thoi_gian_hien_thi` varchar(150) DEFAULT NULL,
  `ten_dia_diem` varchar(200) DEFAULT NULL,
  `so_luong_toi_da` int(10) UNSIGNED NOT NULL,
  `nguoi_tao` int(11) NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `hinh_anh` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `ten_ban_to_chuc` varchar(200) DEFAULT NULL,
  `mo_ta_ban_to_chuc` varchar(255) DEFAULT NULL,
  `danh_muc_id` int(11) DEFAULT 1,
  `trang_thai` enum('nhap','cong_khai','da_huy') NOT NULL DEFAULT 'cong_khai',
  `url_tuy_chinh` varchar(150) DEFAULT NULL,
  `quyen_rieng_tu` enum('public','link_only') NOT NULL DEFAULT 'public',
  `loi_nhan_cam_on` text DEFAULT NULL,
  `thong_tin_thanh_toan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sukien_nguoidung` (`nguoi_tao`),
  KEY `fk_sukien_danhmuc` (`danh_muc_id`),
  CONSTRAINT `fk_sukien_danhmuc` FOREIGN KEY (`danh_muc_id`) REFERENCES `danh_muc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sukien_nguoidung` FOREIGN KEY (`nguoi_tao`) REFERENCES `nguoidung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `sukien` WHERE `id` IN (55, 57, 58, 59, 60, 61, 62, 63, 64);

INSERT INTO `sukien` (`id`, `nguoi_tao_id`, `ten_su_kien`, `mo_ta`, `dia_diem`, `hinh_thuc`, `link_online`, `gia_thap_nhat`, `thoi_gian`, `thoi_gian_ket_thuc`, `thoi_gian_hien_thi`, `ten_dia_diem`, `so_luong_toi_da`, `nguoi_tao`, `ngay_tao`, `hinh_anh`, `banner`, `ten_ban_to_chuc`, `mo_ta_ban_to_chuc`, `danh_muc_id`, `trang_thai`, `url_tuy_chinh`, `quyen_rieng_tu`, `loi_nhan_cam_on`, `thong_tin_thanh_toan`) VALUES
(1, NULL, 'LIVE CONCERT - SKYNOTE THE REFLECTION DAY 2 - QUỐC THIÊN', NULL, 'Phạm Hùng, Mễ Trì, Nam Từ Liêm, Hà Nội', 'offline', NULL, 880000, '2026-11-29 19:30:00', NULL, '19:30 - 23:00, 29 Tháng 11, 2026', 'Trung tâm Hội nghị Quốc gia', 1000, 2, '2026-11-11 13:47:35', '../assets/images/events/banner-skynote.jpg', NULL, 'SKYNOTE Entertainment', 'Đơn vị sản xuất & Tổ chức đêm nhạc', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(2, NULL, '[TP.HCM] Những Thành Phố Mơ Màng Year End 2026', NULL, '799 Nguyễn Văn Linh, Tân Phú, Quận 7, TP. Hồ Chí Minh', 'offline', NULL, 499000, '2026-12-12 15:00:00', NULL, '15:00 - 22:30, 12 Tháng 12, 2026', 'SECC - Trung tâm Hội chợ & Triển lãm Sài Gòn', 2000, 2, '2026-11-11 13:47:48', '../assets/images/events/banner-ntpmm.jpg', NULL, 'Mộng Mơ Studio', 'Ban tổ chức chuỗi sự kiện âm nhạc Indie', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(3, NULL, 'MẮT NHẮM MẮT MỞ CONCERT - DAY 3 (HÀ NỘI)', NULL, 'Văn Cao, Liễu Giai, Ba Đình, Hà Nội', 'offline', NULL, 1600000, '2026-11-01 18:00:00', NULL, '18:00 - 22:00, 01 Tháng 11, 2026', 'Cung Thể Thao Quần Ngựa', 1000, 2, '2026-11-11 13:54:52', '../assets/images/events/banner-mnmm.jpg', NULL, '1PRODUCTION', 'Đơn vị quản lý & sản xuất', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(4, NULL, 'Tinh Hà \"Say Hi\" Concert - Đêm 4', NULL, 'Đường Cầu Tứ Liên, Xã Đông Anh, Thành phố Hà Nội', 'offline', NULL, 2500000, '2026-11-15 19:00:00', NULL, '19:00 - 23:00, 15 Tháng 11, 2026', 'Trung tâm triển lãm Việt Nam (VEC)', 3000, 2, '2026-11-12 13:15:08', '../assets/images/events/banner-tinh-ha-dem-4.jpg', NULL, 'Vie Channel', 'VieChannel: Đơn vị sản xuất', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(5, NULL, 'The Starry - S\'NOW', NULL, '116 Nguyễn Du, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 800000, '2026-12-19 18:30:00', NULL, '18:30 - 22:30, 19 Tháng 12, 2026', 'Nhà thi đấu Nguyễn Du', 600, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-snow.jpg', NULL, 'The Starry Entertainment', 'Đơn vị tổ chức đêm nhạc giáng sinh', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(6, NULL, 'GAI MEET - NHÀ RỰC RỠ', NULL, 'Vũ Tông Phan, An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 1500000, '2026-10-31 18:30:00', NULL, '18:30 - 22:00, 31 Tháng 10, 2026', 'The Ants Studio', 400, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-nha-ruc-ro.jpg', NULL, 'YEAH1 x 1PRODUCTION', 'Đơn vị hợp tác sản xuất chuỗi show', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(7, NULL, '(S)TRONG \'DỊ KIỀU\' SHOW 2026: ĐẾN GIÂY CUỐI CÙNG', NULL, '8 Nguyễn Công Hoan, Ngọc Khánh, Ba Đình, Hà Nội', 'offline', NULL, 1450000, '2026-11-07 19:00:00', NULL, '19:00 - 22:30, 07 Tháng 11, 2026', 'SOL 8 Live Stage', 350, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-strong.jpg', NULL, 'Strong Entertainment', 'Ban tổ chức show diễn âm nhạc', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(8, NULL, 'GAI MEET - DỰ ÁN NHÂN TỐ X', NULL, 'Vũ Tông Phan, An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 1500000, '2026-11-01 18:30:00', NULL, '18:30 - 22:00, 01 Tháng 11, 2026', 'The Ants Studio', 400, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-nhan-to-x.jpg', NULL, 'YEAH1 x 1PRODUCTION', 'Đơn vị hợp tác sản xuất', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(9, NULL, '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 1 & DAY 2', NULL, 'Đường Trần Hữu Dực, Cầu Diễn, Nam Từ Liêm, Hà Nội', 'offline', NULL, 1000000, '2026-10-18 17:00:00', NULL, '17:00 - 23:00, 18 Tháng 10, 2026', 'Cung Điền kinh Mỹ Đình', 5000, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-anh-trai.jpg', NULL, 'YeaH1 Group', 'Đơn vị sản xuất độc quyền', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(10, NULL, 'HIỆN DIỆN - UPRIZE CONCERT', NULL, 'Đỗ Xuân Hợp, Phường An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 750000, '2026-12-19 19:00:00', NULL, '19:00 - 23:00, 19 Tháng 12, 2026', 'The Global City', 1500, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-uprize.jpg', NULL, 'UPRIZE Entertainment', 'Đơn vị sản xuất & Tổ chức sự kiện', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(11, NULL, 'Trải nghiệm bay dù lượn tại Sapa', NULL, 'Đèo Ô Quy Hồ, Sapa, Lào Cai', 'offline', NULL, 2190000, '2026-12-18 08:00:00', NULL, '08:00 - 17:00, 01 Tháng 08, 2026', 'Điểm bay dù lượn Sapa', 100, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-du-luon-sapa.jpg', NULL, 'Sapa Paragliding Club', 'Đơn vị tổ chức trải nghiệm thể thao mạo hiểm', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(12, NULL, 'SAIGON PRO-AM BASKETBALL CUP 2026 OFFICIAL MERCHANDISE', NULL, '2 Hồ Xuân Hương, Phường 6, Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 25000, '2026-09-30 09:00:00', NULL, '09:00 - 21:00, 30 Tháng 09, 2026', 'Nhà thi đấu Hồ Xuân Hương', 500, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-saigon-basketball.jpg', NULL, 'New Sports', 'Đơn vị tổ chức giải bóng rổ Saigon Pro-Am', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(13, NULL, 'TRẢI NGHIỆM ĐƯỜNG ĐUA GO - KART ĐA TẦNG TRONG NHÀ ĐẦU TIÊN TẠI VIỆT NAM', NULL, 'Tầng 5, 161 Xa lộ Hà Nội, Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 0, '2026-12-18 10:00:00', NULL, '10:00 - 22:00 hàng ngày, 19 Tháng 10, 2026', 'Vincom Mega Mall Thảo Điền', 500, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-go-kart.jpg', NULL, 'INFINITY RACING', 'Hệ thống đua xe Go-Kart đa tầng trong nhà', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(14, NULL, 'Queens Of Glory - Giải Thể Thao Điện Tử Vinh Quang Nữ Hoàng', NULL, '479B Hoàng Quốc Việt, Phường 13, Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 199000, '2026-10-30 14:00:00', NULL, '14:00 - 21:00, 30 Tháng 10, 2026', 'VPS ARENA', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-queens-glory.jpg', NULL, 'VPS Esports', 'Đơn vị sản xuất giải đấu Esport nữ', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(15, NULL, 'LCP 2026 Promotion Series', NULL, '479B Hoàng Quốc Việt, Phường 13, Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 199000, '2026-11-10 13:00:00', NULL, '13:00 - 21:00, 10 Tháng 11, 2026', 'VPS ARENA', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-lcp-2026.jpg', NULL, 'Riot Games & VPS Entertainment', 'Ban tổ chức giải đấu Liên Minh Huyền Thoại LCP', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(16, NULL, 'Lion Championship 37 - 2026', NULL, '101 Võ Chí Công, Tây Hồ, Hà Nội', 'offline', NULL, 350000, '2026-11-21 19:00:00', NULL, '19:00 - 23:00, 21 Tháng 11, 2026', 'Nhà thi đấu Tây Hồ', 1200, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-lion-championship.jpg', NULL, 'Liên đoàn Võ thuật tổng hợp Việt Nam (VMMAF)', 'Giải vô địch MMA chuyên nghiệp Việt Nam', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(17, NULL, 'VIEWING PARTY CHUNG KẾT TỔNG GIẢI ĐẤU VALORANT CHAMPIONS 2026', NULL, '101 Võ Chí Công, Phường Xuân La, Quận Tây Hồ, Hà Nội', 'offline', NULL, 259000, '2026-10-18 11:00:00', NULL, '11:00 - 20:00, 18 Tháng 10, 2026', 'Nhà thi đấu Tây Hồ', 1000, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-valorant-viewing-party.jpg', NULL, 'Riot Games & VNG Games', 'Đơn vị phát hành & Ban tổ chức giải đấu Valorant tại Việt Nam', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(19, NULL, 'A LITTLE AUTUMN BAKING WORKSHOP: \"PUMPKIN SPICE CINNAMON ROLL\"', NULL, 'Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 400000, '2026-10-27 09:30:00', NULL, '09:30 - 12:00, 27 Tháng 10, 2026', 'Bakery Studio & Academy', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-baking-pumpkin.jpg', NULL, 'Little Autumn Studio', 'Lớp học làm bánh nghệ thuật & trải nghiệm', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(20, NULL, 'The \"Traditional Water Puppet Show\" - Múa rối nước', NULL, '55B Nguyễn Thị Minh Khai, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 350000, '2026-10-27 18:30:00', NULL, '18:30 - 19:30, 27 Tháng 10, 2026', 'Nhà hát Múa rối nước Rồng Vàng', 250, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-mua-roi-nuoc.jpg', NULL, 'Nhà hát Múa rối nước Rồng Vàng', 'Đơn vị biểu diễn nghệ thuật múa rối nước truyền thống', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(22, NULL, 'Chào Show - The Sound of Vietnam', NULL, '2 Nguyễn Bỉnh Khiêm, Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 1300000, '2026-10-27 19:15:00', NULL, '19:15 - 21:00, 27 Tháng 10, 2026', 'Bảo tàng Lịch sử TP. Hồ Chí Minh', 150, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-chao-show.jpg', NULL, 'Chào Show Production', 'Đêm nhạc trình diễn âm nhạc dân tộc Việt Nam', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(23, NULL, '[GARDEN ART] - ART WORKSHOP \"FRUIT MOCHI CHEESECAKE\"', NULL, 'Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 370000, '2026-11-28 14:00:00', NULL, '14:00 - 17:00, 28 Tháng 10, 2026', 'Garden Art Space', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-mochi-cheesecake.jpg', NULL, 'Garden Art Studio', 'Xưởng sáng tạo nghệ thuật & làm bánh', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(24, NULL, 'ART WORKSHOP \"UJI MATCHA CHEESECAKE TARTE\"', NULL, 'Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 420000, '2026-11-28 09:00:00', NULL, '09:00 - 12:00, 28 Tháng 10, 2026', 'Garden Art Space', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-matcha-tarte.jpg', NULL, 'Garden Art Studio', 'Xưởng sáng tạo nghệ thuật & làm bánh', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(25, NULL, 'ART WORKSHOP OIL PASTEL PAINTING \"MELTED THOUGHTS\"', NULL, 'Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 420000, '2026-11-28 14:30:00', NULL, '14:30 - 17:30, 28 Tháng 10, 2026', 'Atelier Painting Studio', 25, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-oil-pastel.jpg', NULL, 'Atelier Studio', 'Lớp học vẽ tranh sáp dầu nghệ thuật', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(26, NULL, '[DỄ GARDEN] Moss Frame Workshop', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 450000, '2026-09-16 09:00:00', NULL, '09:00 - 12:00, 16 Tháng 09, 2026', 'Dễ Garden Studio', 25, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-moss-frame.jpg', NULL, 'Dễ Garden', 'Xưởng thiết kế & sáng tạo không gian xanh', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(27, NULL, '[LÁ NHÀ GARDEN] Workshop Decor bí ngô Halloween', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 'offline', NULL, 293000, '2026-11-27 14:00:00', NULL, '14:00 - 17:00, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-decor-bi-ngo.jpg', NULL, 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(28, NULL, '[LÁ NHÀ GARDEN] Workshop Terrarium Mini', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 'offline', NULL, 395000, '2026-10-27 09:30:00', NULL, '09:30 - 11:30, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-terrarium-mini.jpg', NULL, 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(29, NULL, '[LÁ NHÀ GARDEN] Tiểu cảnh Sen đá', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 'offline', NULL, 385000, '2026-11-27 14:00:00', NULL, '14:00 - 16:30, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-tieu-canh-sen-da.jpg', NULL, 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(30, NULL, '[LÁ NHÀ GARDEN] Workshop Nương gỗ', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 'offline', NULL, 465000, '2026-10-27 09:00:00', NULL, '09:00 - 12:00, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-nuong-go.jpg', NULL, 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(31, NULL, '[LÁ NHÀ GARDEN] Workshop Tranh thực vật ép Botanical art', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 'offline', NULL, 305000, '2026-11-27 14:00:00', NULL, '14:00 - 16:30, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-botanical-art.jpg', NULL, 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(32, NULL, '[LÁ NHÀ GARDEN] Workshop Tranh rêu độc bản', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 'offline', NULL, 370000, '2026-11-27 09:30:00', NULL, '09:30 - 12:00, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-tranh-reu.jpg', NULL, 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(33, NULL, '[LÁ NHÀ GARDEN] Workshop Túi thơm thảo mộc healing', NULL, '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh', 'offline', NULL, 215000, '2026-11-27 15:00:00', NULL, '15:00 - 17:00, 27 Tháng 10, 2026', 'Lá Nhà Garden Studio', 30, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-tui-thom-thao-moc.jpg', NULL, 'Lá Nhà Garden', 'Nhận tổ chức Workshop thủ công & cây cảnh', 3, 'cong_khai', NULL, 'public', NULL, NULL),
(34, NULL, 'Trải Nghiệm Bay Dù Lượn Mù Cang Chải', NULL, 'Đèo Khau Phạ, Huyện Mù Cang Chải, Tỉnh Yên Bái', 'offline', NULL, 2190000, '2026-12-18 07:30:00', NULL, '07:30 - 17:00, 01 Tháng 02, 2026', 'Đèo Khau Phạ', 100, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-du-luon-mu-cang-chai.jpg', NULL, 'VietWings Paragliding', 'Đơn vị tổ chức dù lượn hàng đầu Việt Nam', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(35, NULL, 'KHU VUI CHƠI NHÀ CHEN - ƯỚC MƠ XANH', NULL, 'Quận 7, TP. Hồ Chí Minh', 'offline', NULL, 60000, '2026-12-18 08:00:00', NULL, '08:00 - 21:00 hàng ngày, 28 Tháng 07, 2026', 'Khu Vui Chơi Nhà Chen', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-nha-chen.jpg', NULL, 'Nhà Chen Kids', 'Khu vui chơi trải nghiệm sáng tạo dành cho gia đình', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(36, NULL, 'DU LỊCH VĂN HÓA SUỐI TIÊN', NULL, '120 Xa lộ Hà Nội, Phường Tân Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 60000, '2026-12-18 07:30:00', NULL, '07:30 - 17:30 hàng ngày, 01 Tháng 10, 2026', 'Khu Du Lịch Văn Hóa Suối Tiên', 2000, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-suoi-tien.jpg', NULL, 'Suối Tiên Theme Park', 'Tập đoàn Du lịch Văn hóa Suối Tiên', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(37, NULL, 'Bảo Tàng Phở - Bảo Tàng Ẩm Thực Đầu Tiên tại Việt Nam', NULL, 'Thành phố Nam Định, Tỉnh Nam Định', 'offline', NULL, 200000, '2026-12-18 08:30:00', NULL, '08:30 - 18:00 hàng ngày, 01 Tháng 10, 2026', 'Bảo Tàng Phở Việt Nam', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-bao-tang-pho.jpg', NULL, 'Hiệp hội Văn hóa Ẩm thực Việt Nam', 'Đơn vị bảo tồn & phát triển văn hóa ẩm thực', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(38, NULL, 'PHẬT BÁO NGHIỆM TRẤN - TRIỂN LÃM DI SẢN VĂN HOÁ PHẬT GIÁO ĐỘC BẢN', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 150000, '2026-12-18 09:00:00', NULL, '09:00 - 21:00, 27 Tháng 10, 2026', 'Trung tâm Triển lãm Nghệ thuật', 400, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-phat-bao-nghiem-tran.jpg', NULL, 'Phật Báo Nghiệm Trấn', 'Ban tổ chức triển lãm di sản văn hóa Phật giáo', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(39, NULL, 'City Sightseeing SaiGon: Xe Buýt 2 Tầng', NULL, 'Công trường Lam Sơn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 105000, '2026-12-18 08:00:00', NULL, '08:00 - 22:30 hàng ngày, 27 Tháng 10, 2026', 'Trạm Nhà Hát Thành Phố', 500, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-bus-2-tang.jpg', NULL, 'City Sightseeing Vietnam', 'Thương hiệu xe buýt ngắm cảnh thành phố 2 tầng', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(40, NULL, 'TOUR ĐÊM VĂN MIẾU - VAN MIEU NIGHT TOUR', NULL, '58 Quốc Tử Giám, Văn Miếu, Đống Đa, Hà Nội', 'offline', NULL, 0, '2026-12-18 19:00:00', NULL, '19:00 - 22:00 (Thứ 4, 6, 7 & CN), 28 Tháng 10, 2026', 'Di tích Quốc gia đặc biệt Văn Miếu - Quốc Tử Giám', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-tour-dem-van-mieu.jpg', NULL, 'Trung tâm Hoạt động VHKH Văn Miếu - Quốc Tử Giám', 'Đơn vị quản lý & tổ chức trải nghiệm đêm Đạo Học', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(41, NULL, 'Vé Trải Nghiệm GenZania 2 tiếng', NULL, 'Quận 2, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 190000, '2026-12-18 10:00:00', NULL, '10:00 - 21:00, 30 Tháng 10, 2026', 'Tổ hợp Trải nghiệm GenZania', 400, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-genzania.jpg', NULL, 'GenZania Entertainment', 'Thế giới trò chơi & giải trí tương tác cho giới trẻ', 2, 'cong_khai', NULL, 'public', NULL, NULL),
(42, NULL, 'KỊCH ĐOÀN THÀNH LỘC: CÔ GIÁO DUYÊN', NULL, '62 Trần Hưng Đạo, Phường Phạm Ngũ Lão, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-19 19:30:00', NULL, '19:30 - 22:30, 19 Tháng 11, 2026', 'Sân khấu kịch Thiên Đăng', 300, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-co-giao-duyen.jpg', NULL, 'Sân khấu kịch Thiên Đăng', 'Kịch đoàn nghệ sĩ Ưu tú Thành Lộc', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(43, NULL, 'Eifman Ballet: Eugene Onegin - World Tour 2026', NULL, '1 Tràng Tiền, Phường Tràng Tiền, Quận Hoàn Kiếm, Hà Nội', 'offline', NULL, 500000, '2026-10-24 20:00:00', NULL, '20:00 - 22:30, 24 Tháng 10, 2026', 'Nhà hát Lớn Hà Nội', 600, 2, '2026-11-12 13:30:26', '../assets/images/events/banner-eifman-ballet.jpg', NULL, 'Eifman Ballet', 'Đoàn múa Ballet Hàn lâm Saint Petersburg', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(45, NULL, 'Nhà Hát Kịch IDECAF: Hồn Ai Nấy Giữ', NULL, '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-10-29 19:30:00', NULL, '19:30 - 22:00, 29 Tháng 10, 2026', 'Nhà hát kịch IDECAF', 350, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-hon-ai-nay-giu.jpg', NULL, 'Kịch IDECAF', 'Sân khấu kịch nói chuyên nghiệp tại TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(46, NULL, '[CAT&MOUSE] THÀNH VÁ', NULL, '37B Phạm Ngọc Thạch, Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 175000, '2026-11-07 20:00:00', NULL, '20:00 - 23:00, 07 Tháng 11, 2026', 'Cat & Mouse Live Music', 500, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-cm-thanh-va.jpg', NULL, 'Cat & Mouse Live Music', 'Không gian âm nhạc Live Band tại TP.HCM', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(47, NULL, 'SOUND HEALING CONCERT 2025', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 500000, '2025-09-07 18:00:00', NULL, '18:00 - 22:00, 07 Tháng 09, 2025', 'Không gian Nghệ thuật Đương đại Sài Gòn', 250, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-sound-healing.jpg', NULL, 'SOUND HEALING CONCERT', 'Trải nghiệm hòa nhạc chữa lành tâm hồn', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(48, NULL, 'Nhà Hát Kịch IDECAF: Mẹ Tôi Là Đàn Ông', NULL, '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-10-30 19:30:00', NULL, '19:30 - 22:00, 30 Tháng 10, 2026', 'Nhà hát kịch IDECAF', 350, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-me-toi-la-dan-ong.jpg', NULL, 'Kịch IDECAF', 'Sân khấu kịch nói chuyên nghiệp tại TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(49, NULL, 'BÓNG MA RẠP HÁT LỆ HOA - SÂN KHẤU MỚI', NULL, 'Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 250000, '2026-10-30 19:30:00', NULL, '19:30 - 22:00, 30 Tháng 10, 2026', 'Sân Khấu Mới', 300, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-bong-ma-le-hoa.jpg', NULL, 'Sân Khấu Mới', 'Sân khấu hài kịch & chính kịch TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(50, NULL, 'Sân Khấu Thế Giới Trẻ: Mật Mã Cầu Cơ', NULL, '22 Vĩnh Viễn, Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 330000, '2026-10-30 20:00:00', NULL, '20:00 - 22:30, 30 Tháng 10, 2026', 'Sân khấu Nghệ thuật Trương Hùng Minh', 350, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-mat-ma-cau-co.jpg', NULL, 'SÂN KHẤU NGHỆ THUẬT TRƯƠNG HÙNG MINH', 'Sân khấu kịch nghệ thuật Trương Hùng Minh', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(51, NULL, 'Mystic Night Show - Magic & Comedy in Saigon', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 850000, '2026-10-30 20:00:00', NULL, '20:00 - 22:00, 30 Tháng 10, 2026', 'Saigon Comedy & Magic Lounge', 200, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-mystic-night-show.jpg', NULL, 'Mystic Entertainment', 'Chương trình biểu diễn ảo thuật & hài kịch tương tác', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(52, NULL, 'Cuộc Phiêu Lưu Kỳ Thú', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 120000, '2026-10-31 16:00:00', NULL, '16:00 - 18:00, 31 Tháng 10, 2026', 'Nhà hát Thiếu nhi TP. Hồ Chí Minh', 300, 2, '2026-11-12 13:43:44', '../assets/images/events/banner-cuoc-phieu-luu-ky-thu.jpg', NULL, 'Sân khấu Kịch Thiếu Nhi', 'Chương trình kịch nghệ thuật dành cho gia đình & thiếu nhi', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(53, NULL, '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 3 & DAY 4', NULL, 'Đỗ Xuân Hợp, Phường An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 1000000, '2026-12-05 17:00:00', NULL, '17:00 - 23:00, 05 Tháng 12, 2026', 'The Global City', 5000, 2, '2026-11-15 07:29:39', '../assets/images/events/banner-anh-trai.jpg', NULL, 'YeaH1 Family', 'Đơn vị sản xuất độc quyền', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(54, NULL, 'NGỌC THANH TÂM FAN MEETING: TIỂU NƠ DAY 2026', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 350000, '2026-12-10 18:30:00', NULL, '18:30 - 21:30, 10 Tháng 12, 2026', 'Nhà hát Bến Thành', 400, 2, '2026-11-15 07:29:39', '../assets/images/memberships/tieu-no.jpg', NULL, 'Ngọc Thanh Tâm', 'Fan Meeting & Ra mắt dự án mới', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(56, NULL, 'JUN PHẠM FAN MEETING: XỨ SỞ MIÊN MAN', NULL, 'Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 490000, '2026-12-15 18:30:00', NULL, '18:30 - 21:30, 15 Tháng 12, 2026', 'Nhà hát Hòa Bình', 600, 2, '2026-11-15 07:29:39', '../assets/images/memberships/jun-pham.jpg', NULL, 'Jun Phạm', 'Sự kiện dành cho cộng đồng Fanclub Jun Phạm', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(65, NULL, '[Hà Nội] Những Thành Phố Mơ Màng Year End 2026', NULL, 'Công viên Yên Sở, Hoàng Mai, Hà Nội', 'offline', NULL, 499000, '2027-01-17 15:00:00', NULL, '15:00 - 22:30, 17 Tháng 01, 2027', 'Công viên Yên Sở', 2500, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-ntpmm-hn.jpg', NULL, 'Những Thành Phố Mơ Màng', 'Lễ hội âm nhạc Indie thường niên', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(66, NULL, '[Hà Nội] Những Thành Phố Mơ Màng Summer 2026', NULL, 'Công viên Yên Sở, Hoàng Mai, Hà Nội', 'offline', NULL, 855000, '2026-07-12 15:00:00', NULL, '15:00 - 22:30, 12 Tháng 07, 2026', 'Công viên Yên Sở', 2500, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-ntpmm-summer.jpg', NULL, 'Những Thành Phố Mơ Màng', 'Lễ hội âm nhạc Indie mùa hè', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(67, NULL, '[Hà Nội] Những Thành Phố Mơ Màng Year End 2025', NULL, 'Công viên Yên Sở, Hoàng Mai, Hà Nội', 'offline', NULL, 620000, '2025-12-21 15:00:00', NULL, '15:00 - 22:30, 21 Tháng 12, 2025', 'Công viên Yên Sở', 2500, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-ntpmm-2025.jpg', NULL, 'Những Thành Phố Mơ Màng', 'Lễ hội âm nhạc Indie cuối năm 2025', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(68, NULL, '\'GIỮA MỘT VẠN TOUR (MỞ RỘNG)\' - PHÙNG KHÁNH LINH | CHAPTER 5: LIVE EXPERIENCE IN HCMC', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 700000, '2026-10-17 19:30:00', NULL, '19:30 - 22:00, 17 Tháng 10, 2026', 'Nhà hát Bến Thành', 500, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-pkl-ch5.jpg', NULL, 'Phùng Khánh Linh', 'Live Experience Tour tại TP.HCM', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(69, NULL, '\'GIỮA MỘT VẠN TOUR\' - PHÙNG KHÁNH LINH | CHAPTER 4: LIVE EXPERIENCE IN HÀ NỘI', NULL, 'Quận Hoàn Kiếm, Hà Nội', 'offline', NULL, 860000, '2026-08-09 19:30:00', NULL, '19:30 - 22:00, 09 Tháng 08, 2026', 'Nhà hát Lớn Hà Nội', 500, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-pkl-ch4.jpg', NULL, 'Phùng Khánh Linh', 'Live Experience Tour tại Hà Nội', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(70, NULL, '\'GIỮA MỘT VẠN TOUR\' - PHÙNG KHÁNH LINH | CHAPTER 3: LIVE EXPERIENCE IN HÀ NỘI', NULL, 'Quận Hoàn Kiếm, Hà Nội', 'offline', NULL, 860000, '2026-08-08 19:30:00', NULL, '19:30 - 22:00, 08 Tháng 08, 2026', 'Nhà hát Lớn Hà Nội', 500, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-pkl-ch3.jpg', NULL, 'Phùng Khánh Linh', 'Live Experience Tour tại Hà Nội', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(71, NULL, 'CONCERT OSUNFEST', NULL, 'Quận 7, TP. Hồ Chí Minh', 'offline', NULL, 500000, '2026-06-06 18:00:00', NULL, '18:00 - 22:30, 06 Tháng 06, 2026', 'SECC Outdoor', 1500, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-osunfest.jpg', NULL, 'OSUNFEST', 'Lễ hội âm nhạc mùa hè', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(72, NULL, '[PRE-ORDER] OFFICIAL ĐINH MẠNH NINH MERCHANDISE', NULL, 'Giao hàng toàn quốc', 'offline', NULL, 299000, '2026-12-30 12:00:00', NULL, 'Mở bán đặt trước Official Merchandise', 'Ticketbox Official Store', 1000, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-dmn-merch.jpg', NULL, 'Đinh Mạnh Ninh', 'Bộ sưu tập Merchandise chính thức của Đinh Mạnh Ninh', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(73, NULL, 'NOEL CHO TÌNH NHÂN ANNIVERSARY LIVESHOW', NULL, 'Quận Hoàn Kiếm, Hà Nội', 'offline', NULL, 800000, '2026-12-20 20:00:00', NULL, '20:00 - 23:00, 20 Tháng 12, 2026', 'Cung Văn hóa Hữu nghị Việt Xô', 800, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-noel-tinh-nhan.jpg', NULL, 'Đinh Mạnh Ninh', 'Đêm nhạc Giáng sinh đặc biệt', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(74, NULL, 'GAI MEET - NHÀ HÀO MÔN', NULL, 'Vũ Tông Phan, An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 1500000, '2026-11-14 18:30:00', NULL, '18:30 - 22:00, 14 Tháng 11, 2026', 'The Ants Studio', 400, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-nha-hao-mon.jpg', NULL, 'YEAH1 x 1PRODUCTION', 'Chuỗi sự kiện giao lưu âm nhạc GAI MEET', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(75, NULL, 'The Starry - NHM SHOW', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 800000, '2026-11-12 19:00:00', NULL, '19:00 - 22:30, 12 Tháng 11, 2026', 'Nhà thi đấu Nguyễn Du', 600, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-snow.jpg', NULL, 'The Starry Entertainment', 'Đêm nhạc The Starry', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(76, NULL, 'NGỌC THANH TÂM\'S BIRTHDAY FAN MEETING', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 0, '2026-07-19 18:30:00', NULL, '18:30 - 21:30, 19 Tháng 07, 2026', 'Nhà hát Bến Thành', 400, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-ntt-birthday.jpg', NULL, 'Ngọc Thanh Tâm', 'Sự kiện mừng sinh nhật & giao lưu fanclub', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(77, NULL, 'Y-CONCERT BY YEAH1', NULL, 'TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 800000, '2025-12-20 18:00:00', NULL, '18:00 - 23:00, 20 Tháng 12, 2025', 'The Global City', 3000, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-y-concert.jpg', NULL, 'YeaH1 Family', 'Đại nhạc hội Y-Concert', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(78, NULL, 'NGỌC THANH TÂM\'S FAN MEETING IN HA NOI', NULL, 'Quận Đống Đa, Hà Nội', 'offline', NULL, 0, '2025-10-12 18:30:00', NULL, '18:30 - 21:30, 12 Tháng 10, 2025', 'Trung tâm Nghệ thuật Âu Cơ', 350, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-ntt-hn.jpg', NULL, 'Ngọc Thanh Tâm', 'Fan Meeting tại Hà Nội', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(79, NULL, 'NGỌC THANH TÂM\'S FAN MEETING IN HO CHI MINH CITY', NULL, 'Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 0, '2025-07-05 18:30:00', NULL, '18:30 - 21:30, 05 Tháng 07, 2025', 'Nhà hát Hòa Bình', 400, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-ntt-hcm.jpg', NULL, 'Ngọc Thanh Tâm', 'Fan Meeting tại TP. Hồ Chí Minh', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(80, NULL, 'MEMORY SHOW - VALENTINE INLOVE| QUỐC THIÊN x NGUYỄN ĐÌNH TUẤN DŨNG', NULL, 'Quận Hoàn Kiếm, Hà Nội', 'offline', NULL, 700000, '2027-02-14 20:00:00', NULL, '20:00 - 22:30, 14 Tháng 02, 2027', 'Nhà hát Lớn Hà Nội', 500, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-memory-valentine.jpg', NULL, 'Quốc Thiên', 'Đêm nhạc tình nhân Valentine Inlove', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(81, NULL, 'STARLIGHT II - QUỐC THIÊN X HÀ AN HUY', NULL, 'Thành phố Đà Lạt, Tỉnh Lâm Đồng', 'offline', NULL, 900000, '2027-01-02 20:00:00', NULL, '20:00 - 22:30, 02 Tháng 01, 2027', 'Đà Lạt Opera House', 450, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-starlight-2.jpg', NULL, 'Quốc Thiên', 'Đêm nhạc Starlight II', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(82, NULL, '[BẾN THÀNH] Đêm nhạc Quốc Thiên', NULL, '6 Mạc Đĩnh Chi, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 500000, '2026-12-16 20:30:00', NULL, '20:30 - 23:00, 16 Tháng 12, 2026', 'Phòng trà Bến Thành', 220, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-qt-benthanh.jpg', NULL, 'Phòng trà Bến Thành', 'Đêm nhạc riêng của Quốc Thiên tại Phòng trà Bến Thành', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(83, NULL, 'GAI MEET - NHÀ SOẠN NHẠC', NULL, 'Vũ Tông Phan, An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 1500000, '2026-12-06 18:30:00', NULL, '18:30 - 22:00, 06 Tháng 12, 2026', 'The Ants Studio', 400, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-nha-soan-nhac.jpg', NULL, 'YEAH1 x 1PRODUCTION', 'Chuỗi sự kiện giao lưu âm nhạc GAI MEET', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(84, NULL, 'GAI MEET - NHƯ NGÀY ĐẦU', NULL, 'Vũ Tông Phan, An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 1500000, '2026-11-29 18:30:00', NULL, '18:30 - 22:00, 29 Tháng 11, 2026', 'The Ants Studio', 400, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-nhu-ngay-dau.jpg', NULL, 'YEAH1 x 1PRODUCTION', 'Chuỗi sự kiện giao lưu âm nhạc GAI MEET', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(85, NULL, 'GAI MEET - NHÓM THÁM THÍNH', NULL, 'Vũ Tông Phan, An Phú, TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 1500000, '2026-11-28 18:30:00', NULL, '18:30 - 22:00, 28 Tháng 11, 2026', 'The Ants Studio', 400, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-nhom-tham-thinh.jpg', NULL, 'YEAH1 x 1PRODUCTION', 'Chuỗi sự kiện giao lưu âm nhạc GAI MEET', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(86, NULL, 'KOSMIK LIVE CONCERT 2026 IN HANOI: HOMECOMING', NULL, 'Nam Từ Liêm, Hà Nội', 'offline', NULL, 800000, '2026-12-12 19:00:00', NULL, '19:00 - 23:00, 12 Tháng 12, 2026', 'Cung Điền kinh Mỹ Đình', 3000, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-kosmik-concert.jpg', NULL, 'SS Label', 'Live Concert kỷ niệm của SpaceSpeakers Label', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(87, NULL, '35SIGN - LEZII\'s 1st Fansign in HCMC', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 0, '2026-11-14 14:00:00', NULL, '14:00 - 17:30, 14 Tháng 11, 2026', 'SS Label Studio', 200, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-35sign-lezii.jpg', NULL, 'SS Label', 'Sự kiện ký tặng & giao lưu cùng nghệ sĩ LEZII', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(88, NULL, '[FOR KINGDOM] ĐĂNG KÝ NHẬN VÉ FANZONE TIKTOKSHOP FUNFEST 2026', NULL, 'TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 0, '2026-11-12 16:00:00', NULL, '16:00 - 22:00, 12 Tháng 11, 2026', 'The Global City', 500, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-for-kingdom.jpg', NULL, 'SS Label', 'Đặc quyền Fanzone dành cho cộng đồng Kingdom', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(89, NULL, 'KOSMIK 2026 OFFICIAL MERCHANDISE COLLECTION', NULL, 'Giao hàng toàn quốc', 'offline', NULL, 100000, '2026-10-26 12:00:00', NULL, 'Mở bán Official Merchandise Collection', 'SpaceSpeakers Store', 1000, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-kosmik-merch.jpg', NULL, 'SS Label', 'Bộ sưu tập vật phẩm chính thức KOSMIK 2026', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(90, NULL, 'GAI HOME CONCERT', NULL, 'Vinhomes Ocean Park 3, Hưng Yên', 'offline', NULL, 1000000, '2026-04-26 17:00:00', NULL, '17:00 - 23:00, 26 Tháng 04, 2026', 'Vinhomes Ocean Park 3', 4000, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-gai-home.jpg', NULL, 'YeaH1 Family', 'Đêm nhạc hội tụ các Anh Tài', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(91, NULL, 'Tinh Hà \"Say Hi\" Concert - Đêm 3', NULL, 'Đường Cầu Tứ Liên, Xã Đông Anh, Thành phố Hà Nội', 'offline', NULL, 800000, '2026-11-14 19:00:00', NULL, '19:00 - 23:00, 14 Tháng 11, 2026', 'Trung tâm triển lãm Việt Nam (VEC)', 3000, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-tinh-ha-dem-3.jpg', NULL, 'Vie Concert', 'Chuỗi concert Tinh Hà Say Hi', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(92, NULL, 'Tinh Hà \"Say Hi\" Concert - Đêm 2', NULL, 'Đường Cầu Tứ Liên, Xã Đông Anh, Thành phố Hà Nội', 'offline', NULL, 800000, '2026-10-18 19:00:00', NULL, '19:00 - 23:00, 18 Tháng 10, 2026', 'Trung tâm triển lãm Việt Nam (VEC)', 3000, 2, '2026-11-15 08:06:07', '../assets/images/events/banner-tinh-ha.jpg', NULL, 'Vie Concert', 'Chuỗi concert Tinh Hà Say Hi', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(93, NULL, 'Nhà Hát Kịch IDECAF: TẤM CÁM ĐẠI CHIẾN!', NULL, '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-12-05 19:30:00', NULL, '19:30 - 22:30, 05 Tháng 12, 2026', 'Nhà hát Kịch IDECAF', 350, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-idecaf-tam-cam.jpg', NULL, 'Kịch IDECAF', 'Sân khấu kịch nói chuyên nghiệp tại TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(94, NULL, 'Nhà Hát Kịch IDECAF: LƯƠNG SƠN BÁ CHÚC ANH ĐÀI ngoại truyện', NULL, '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-29 19:30:00', NULL, '19:30 - 22:30, 29 Tháng 11, 2026', 'Nhà hát Kịch IDECAF', 350, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-idecaf-luong-son-ba.jpg', NULL, 'Kịch IDECAF', 'Sân khấu kịch nói chuyên nghiệp tại TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(95, NULL, 'Nhà Hát Kịch IDECAF: 12 Bà Mụ', NULL, '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-28 19:30:00', NULL, '19:30 - 22:30, 28 Tháng 11, 2026', 'Nhà hát Kịch IDECAF', 350, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-idecaf-12-ba-mu.jpg', NULL, 'Kịch IDECAF', 'Sân khấu kịch nói chuyên nghiệp tại TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(96, NULL, 'Nhà Hát Kịch IDECAF: THUỐC ĐẮNG GIÃ TẬT', NULL, '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-27 19:30:00', NULL, '19:30 - 22:30, 27 Tháng 11, 2026', 'Nhà hát Kịch IDECAF', 350, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-idecaf-thuoc-dang.jpg', NULL, 'Kịch IDECAF', 'Sân khấu kịch nói chuyên nghiệp tại TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(97, NULL, 'Nhà Hát Kịch IDECAF: Dưới Bóng Giai Nhân', NULL, '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-21 19:30:00', NULL, '19:30 - 22:30, 21 Tháng 11, 2026', 'Nhà hát Kịch IDECAF', 350, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-idecaf-duoi-bong-giai-nhan.jpg', NULL, 'Kịch IDECAF', 'Sân khấu kịch nói chuyên nghiệp tại TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(98, NULL, 'PHIỀN MEETING - DJ MIE', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 450000, '2026-09-27 18:30:00', NULL, '18:30 - 22:00, 27 Tháng 09, 2026', 'Capital Theatre', 400, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-phien-meeting-mie.jpg', NULL, 'DJ Mie', 'Fan Meeting & Live DJ Set của DJ Mie', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(99, NULL, 'GAMA Music Racing Festival', NULL, 'TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 82000, '2025-06-28 17:00:00', NULL, '17:00 - 23:00, 28 Tháng 06, 2025', 'Saigon Riverside Park', 3000, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-gama-festival.jpg', NULL, 'GAMA Entertainment', 'Lễ hội âm nhạc & đua xe thể thao', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(100, NULL, 'WEEKEND FIESTA: MIE', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 250000, '2025-06-27 21:00:00', NULL, '21:00 - 01:00, 27 Tháng 06, 2025', 'Zion Sky Lounge', 300, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-weekend-fiesta-mie.jpg', NULL, 'DJ Mie', 'Đêm nhạc EDM cuối tuần cùng DJ Mie', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(101, NULL, 'TỪ ĐÂY TỪ NAY: PHUCJUOI', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 800000, '2026-11-01 19:30:00', NULL, '19:30 - 22:30, 01 Tháng 11, 2026', 'Nhà hát Bến Thành', 500, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-tu-day-tu-nay.jpg', NULL, 'Tăng Phúc', 'Live Show kỷ niệm hành trình âm nhạc Tăng Phúc', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(102, NULL, 'THE BROTHERS | Đỗ Hoàng Hiệp x Tăng Phúc x Hà Lê x Cheng x Bình Văn Band', NULL, 'Quận Hoàn Kiếm, Hà Nội', 'offline', NULL, 1000000, '2026-10-31 19:30:00', NULL, '19:30 - 22:30, 31 Tháng 10, 2026', 'Cung Văn hóa Hữu nghị Việt Xô', 800, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-the-brothers.jpg', NULL, 'The Brothers Concert', 'Đêm nhạc hội tụ các Anh Tài', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(103, NULL, '[CAT&MOUSE] TĂNG PHÚC + PHẠM QUỲNH ANH', NULL, '37B Phạm Ngọc Thạch, Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 900000, '2026-10-24 20:00:00', NULL, '20:00 - 23:00, 24 Tháng 10, 2026', 'Cat & Mouse Live Music', 250, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-catmouse-tangphuc.jpg', NULL, 'Cat & Mouse Live Music', 'Đêm nhạc Live Band tại Cat & Mouse', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(104, NULL, '[BẾN THÀNH] Đêm nhạc Tăng Phúc', NULL, '6 Mạc Đĩnh Chi, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 500000, '2026-09-20 20:30:00', NULL, '20:30 - 23:00, 20 Tháng 09, 2026', 'Phòng trà Bến Thành', 220, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-benthanh-tangphuc.jpg', NULL, 'Phòng trà Bến Thành', 'Đêm nhạc tại Phòng trà Bến Thành', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(105, NULL, 'Chillpark: Dạ Quang', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 480000, '2026-11-28 18:30:00', NULL, '18:30 - 22:00, 28 Tháng 11, 2026', 'Chillpark Saigon', 400, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-chillpark-da-quang.jpg', NULL, 'Chillpark Live', 'Đêm nhạc chủ đề Dạ Quang cùng nhạc sĩ Nguyễn Văn Chung', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(106, NULL, '[BẾN THÀNH] Đêm nhạc Nguyễn Văn Chung - Phương Thanh - Tăng Phúc - Nguyên Hà - Ôn Vĩnh Khang', NULL, '6 Mạc Đĩnh Chi, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 500000, '2025-07-27 20:30:00', NULL, '20:30 - 23:00, 27 Tháng 07, 2025', 'Phòng trà Bến Thành', 220, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-benthanh-nvc.jpg', NULL, 'Phòng trà Bến Thành', 'Đêm nhạc tác giả Nguyễn Văn Chung', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(107, NULL, 'Mr Siro - Encore Extended - Ai Cũng Giấu Trong Lòng Tảng Băng - Hà Nội', NULL, 'Quận Hoàn Kiếm, Hà Nội', 'offline', NULL, 800000, '2026-11-01 19:30:00', NULL, '19:30 - 22:30, 01 Tháng 11, 2026', 'Cung Văn hóa Hữu nghị Việt Xô', 900, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-mrsiro-hn-encore.jpg', NULL, 'Mr. Siro', 'Fan Concert Encore Extended tại Hà Nội', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(108, NULL, 'Mr Siro - Fan Concert - Encore Ai Cũng Giấu Trong Lòng Tảng Băng - HCM', NULL, 'Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 800000, '2026-06-20 19:30:00', NULL, '19:30 - 22:30, 20 Tháng 06, 2026', 'Nhà hát Hòa Bình', 1200, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-mrsiro-hcm-encore.jpg', NULL, 'Mr. Siro', 'Fan Concert Encore tại TP.HCM', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(109, NULL, 'Mr Siro - Fan Concert - Ai Cũng Giấu Trong Lòng Tảng Băng - Hà Nội', NULL, 'Quận Hoàn Kiếm, Hà Nội', 'offline', NULL, 800000, '2026-03-29 19:30:00', NULL, '19:30 - 22:30, 29 Tháng 03, 2026', 'Cung Văn hóa Hữu nghị Việt Xô', 900, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-mrsiro-hn.jpg', NULL, 'Mr. Siro', 'Fan Concert tại Hà Nội', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(110, NULL, 'Mr Siro - Fan Concert - Ai Cũng Giấu Trong Lòng Tảng Băng', NULL, 'Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 600000, '2026-01-10 19:30:00', NULL, '19:30 - 22:30, 10 Tháng 01, 2026', 'Nhà hát Hòa Bình', 1200, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-mrsiro-hcm.jpg', NULL, 'Mr. Siro', 'Fan Concert mở màn chuỗi Ai Cũng Giấu Trong Lòng Tảng Băng', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(112, NULL, 'Set quà bí mật từ Cheng\'s team', NULL, 'Giao hàng toàn quốc', 'offline', NULL, 0, '2026-07-17 12:00:00', NULL, '12:00, 17 Tháng 07, 2026', 'Ticketbox Official Store', 500, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-cheng-giftset.jpg', NULL, 'Cheng', 'Quà tặng dành riêng cho người hâm mộ Cheng', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(113, NULL, 'HOÀNG DŨNG FANMEETING: HOMECOMING', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 799000, '2026-11-22 18:30:00', NULL, '18:30 - 21:30, 22 Tháng 11, 2026', 'Nhà hát Bến Thành', 500, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-hoangdung-homecoming.jpg', NULL, 'Hoàng Dũng', 'Fan Meeting đặc biệt của Hoàng Dũng', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(114, NULL, 'J-UNZIP | Jun Phạm Showcase 2026', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 724000, '2026-03-28 19:00:00', NULL, '19:00 - 22:00, 28 Tháng 03, 2026', 'Capital Theatre', 500, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-j-unzip.jpg', NULL, 'Jun Phạm', 'Showcase âm nhạc J-UNZIP', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(115, NULL, 'SOOBIN LIVE CONCERT: ALL-ROUNDER THE FINAL', NULL, 'TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 800000, '2025-11-29 19:30:00', NULL, '19:30 - 23:00, 29 Tháng 11, 2025', 'The Global City', 4000, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-soobin-allrounder.jpg', NULL, 'SS Label', 'Live Concert của nghệ sĩ SOOBIN', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(116, NULL, 'EM XINH \"SAY HI\" CONCERT - ĐÊM 2', NULL, 'TP. Thủ Đức, TP. Hồ Chí Minh', 'offline', NULL, 800000, '2025-10-11 19:00:00', NULL, '19:00 - 23:00, 11 Tháng 10, 2025', 'The Global City', 4000, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-em-xinh-say-hi.jpg', NULL, 'Vie Concert', 'Chuỗi concert Em Xinh Say Hi', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(117, NULL, 'SUBICHA MERCHANDISE COLLECTION', NULL, 'Giao hàng toàn quốc', 'offline', NULL, 60000, '2025-10-01 12:00:00', NULL, 'Mở bán Official Merchandise Collection', 'Ticketbox Official Store', 1000, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-subicha-merch.jpg', NULL, 'Subicha', 'Bộ sưu tập vật phẩm chính thức Subicha', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(118, NULL, 'The Aura - Không Thể Thay Thế: Isaac - Jun Phạm - Will - S.T Sơn Thạch', NULL, 'Quận 7, TP. Hồ Chí Minh', 'offline', NULL, 1250000, '2026-11-28 19:30:00', NULL, '19:30 - 23:00, 28 Tháng 11, 2026', 'SECC Outdoor', 2000, 2, '2026-11-15 08:40:12', '../assets/images/events/banner-the-aura.jpg', NULL, 'The Aura Live', 'Đêm nhạc hội tụ 4 thành viên 365daband', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(119, NULL, 'KỊCH ĐOÀN THÀNH LỘC: NGÔI NHÀ KHÔNG CÓ ĐÀN ÔNG', NULL, '112 Nguyễn Đình Chiểu, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-28 19:30:00', NULL, '19:30 - 22:30, 28 Tháng 11, 2026', 'Nhà hát Bến Thành', 400, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-kdtl-ngoi-nha.jpg', NULL, 'KỊCH ĐOÀN THÀNH LỘC', 'Sân khấu kịch nghệ thuật Thiên Đăng / Thành Lộc', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(120, NULL, 'KỊCH ĐOÀN THÀNH LỘC: NGŨ QUÝ TƯƠNG PHÙNG', NULL, '112 Nguyễn Đình Chiểu, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-22 19:30:00', NULL, '19:30 - 22:30, 22 Tháng 11, 2026', 'Nhà hát Bến Thành', 400, 2, '2026-11-15 11:06:35', '../assets/images/events/banner-ngu-quy-tuong-phung.jpg', NULL, 'KỊCH ĐOÀN THÀNH LỘC', 'Sân khấu kịch nghệ thuật Thiên Đăng / Thành Lộc', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(121, NULL, 'KỊCH ĐOÀN THÀNH LỘC: GIÁNG HƯƠNG', NULL, '112 Nguyễn Đình Chiểu, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-25 19:30:00', NULL, '19:30 - 22:30, 25 Tháng 11, 2026', 'Nhà hát Bến Thành', 400, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-kdtl-giang-huong.jpg', NULL, 'KỊCH ĐOÀN THÀNH LỘC', 'Sân khấu kịch nghệ thuật Thiên Đăng / Thành Lộc', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(122, NULL, 'VỞ DIỄN RA GIÊNG CHO CƯỚI HÔN? (MỘT CÁI NHÀ BA KIỂU TẾT) - SÂN KHẤU MỚI', NULL, 'Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 250000, '2026-11-29 19:30:00', NULL, '19:30 - 22:30, 29 Tháng 11, 2026', 'Sân Khấu Mới', 350, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-skm-ra-gieng.jpg', NULL, 'Sân Khấu Mới', 'Sân khấu hài kịch & chính kịch TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(123, NULL, 'SÂN KHẤU MỚI - VỞ DIỄN NỘI GIÁN NẠN DỐI', NULL, 'Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 250000, '2026-11-15 20:00:00', NULL, '20:00 - 22:30, 15 Tháng 11, 2026', 'Sân Khấu Mới', 350, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-skm-noi-gian.jpg', NULL, 'Sân Khấu Mới', 'Sân khấu hài kịch & chính kịch TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(124, NULL, 'VỞ DIỄN AI CHỒNG AI VỢ (SÂN KHẤU MỚI)', NULL, 'Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 250000, '2026-11-08 19:30:00', NULL, '19:30 - 22:30, 08 Tháng 11, 2026', 'Sân Khấu Mới', 350, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-skm-ai-chong-ai-vo.jpg', NULL, 'Sân Khấu Mới', 'Sân khấu hài kịch & chính kịch TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(125, NULL, 'LOALOA SHOW: KHI DI SẢN HÓA VŨ ĐIỆU - Live The Legacy', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 1600000, '2026-11-15 20:00:00', NULL, '20:00 - 22:00, 15 Tháng 11, 2026', 'Nhà hát Thành phố', 400, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-loaloa-di-san.jpg', NULL, 'LOALOA SHOW', 'Chương trình nghệ thuật thực cảnh & ẩm thực di sản', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(126, NULL, 'Sân khấu Trương Hùng Minh: Song Trùng', NULL, '22 Vĩnh Viễn, Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-22 19:30:00', NULL, '19:30 - 22:30, 22 Tháng 11, 2026', 'Sân khấu Nghệ thuật Trương Hùng Minh', 350, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-thm-song-trung.jpg', NULL, 'SÂN KHẤU NGHỆ THUẬT TRƯƠNG HÙNG MINH', 'Sân khấu kịch nghệ thuật Trương Hùng Minh', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(127, NULL, 'SKNT TRƯƠNG HÙNG MINH : LỤA MÁU', NULL, '22 Vĩnh Viễn, Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-01 19:30:00', NULL, '19:30 - 22:30, 01 Tháng 11, 2026', 'Sân khấu Nghệ thuật Trương Hùng Minh', 350, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-thm-lua-mau.jpg', NULL, 'SÂN KHẤU NGHỆ THUẬT TRƯƠNG HÙNG MINH', 'Sân khấu kịch nghệ thuật Trương Hùng Minh', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(128, NULL, 'SKNT TRƯƠNG HÙNG MINH : NGÀY MAI NGƯỜI TA LẤY CHỒNG', NULL, '22 Vĩnh Viễn, Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-10-04 19:30:00', NULL, '19:30 - 22:30, 04 Tháng 10, 2026', 'Sân khấu Nghệ thuật Trương Hùng Minh', 350, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-thm-ngay-mai.jpg', NULL, 'SÂN KHẤU NGHỆ THUẬT TRƯƠNG HÙNG MINH', 'Sân khấu kịch nghệ thuật Trương Hùng Minh', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(129, NULL, '[CAT&MOUSE] FACE TO FACE 2', NULL, '37B Phạm Ngọc Thạch, Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 600000, '2026-11-13 20:00:00', NULL, '20:00 - 23:00, 13 Tháng 11, 2026', 'Cat & Mouse Live Music', 250, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-cm-face-to-face-2.jpg', NULL, 'Cat & Mouse Live Music', 'Không gian âm nhạc Live Band tại TP.HCM', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(130, NULL, '[CAT&MOUSE] NEKO LÊ + MEW AMAZING', NULL, '37B Phạm Ngọc Thạch, Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 900000, '2026-11-08 20:00:00', NULL, '20:00 - 23:00, 08 Tháng 11, 2026', 'Cat & Mouse Live Music', 250, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-cm-neko-mew.jpg', NULL, 'Cat & Mouse Live Music', 'Không gian âm nhạc Live Band tại TP.HCM', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(131, NULL, '[CAT&MOUSE] THANH DUY + PHẠM TRẦN PHƯƠNG', NULL, '37B Phạm Ngọc Thạch, Quận 3, TP. Hồ Chí Minh', 'offline', NULL, 900000, '2026-10-30 20:00:00', NULL, '20:00 - 23:00, 30 Tháng 10, 2026', 'Cat & Mouse Live Music', 250, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-cm-thanh-duy.jpg', NULL, 'Cat & Mouse Live Music', 'Không gian âm nhạc Live Band tại TP.HCM', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(132, NULL, 'LIVESHOW SECRET GARDEN - HÀ NHI', NULL, 'Thành phố Đà Lạt, Tỉnh Lâm Đồng', 'offline', NULL, 1000000, '2026-07-11 20:00:00', NULL, '20:00 - 23:00, 11 Tháng 07, 2026', 'Secret Garden Đà Lạt', 500, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-sg-ha-nhi.jpg', NULL, 'SECRET GARDEN - Khu Vườn Âm Nhạc', 'Chuỗi đêm nhạc ngoài trời giữa thiên nhiên', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(133, NULL, '[LIVE CONCERT] THANH ÂM MÙA HẠ', NULL, 'Thành phố Đà Lạt, Tỉnh Lâm Đồng', 'offline', NULL, 0, '2026-05-30 19:30:00', NULL, '19:30 - 22:30, 30 Tháng 05, 2026', 'Secret Garden Đà Lạt', 600, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-sg-thanh-am.jpg', NULL, 'SECRET GARDEN - Khu Vườn Âm Nhạc', 'Chuỗi đêm nhạc ngoài trời giữa thiên nhiên', 1, 'cong_khai', NULL, 'public', NULL, NULL),
(134, NULL, '[Nhà Hát Bến Thành] Hài kịch: Đào Hoa Hậu', NULL, '6 Mạc Đĩnh Chi, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 350000, '2026-06-25 19:30:00', NULL, '19:30 - 22:30, 25 Tháng 06, 2026', 'Nhà hát Bến Thành', 400, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-ktn-dao-hoa-hau.jpg', NULL, 'Nhà Hát Kịch Thanh Niên', 'Sân khấu hài kịch giải trí trẻ trung', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(135, NULL, '[Nhà hát kịch Thanh Niên] Hài kịch: Tung Hoành Pattaya', NULL, '4 Phạm Ngọc Thạch, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 350000, '2026-06-14 19:30:00', NULL, '19:30 - 22:30, 14 Tháng 06, 2026', 'Nhà Hát Kịch Thanh Niên', 350, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-ktn-pattaya.jpg', NULL, 'Nhà Hát Kịch Thanh Niên', 'Sân khấu hài kịch giải trí trẻ trung', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(136, NULL, '[Nhà hát kịch Thanh Niên] Hài kịch: Lạc Lối Ở BangKok', NULL, '4 Phạm Ngọc Thạch, Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 350000, '2026-04-18 19:30:00', NULL, '19:30 - 22:30, 18 Tháng 04, 2026', 'Nhà Hát Kịch Thanh Niên', 350, 2, '2026-11-15 10:41:23', '../assets/images/events/banner-ktn-bangkok.jpg', NULL, 'Nhà Hát Kịch Thanh Niên', 'Sân khấu hài kịch giải trí trẻ trung', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(137, NULL, 'LOALOA SHOW: VIỆT NAM BỪNG SÁNG', NULL, 'Quận 1, TP. Hồ Chí Minh', 'offline', NULL, 1600000, '2026-11-15 19:30:00', NULL, '19:30 - 21:30, 15 Tháng 11, 2026', 'Nhà hát Thành phố', 400, 2, '2026-11-15 10:50:42', '../assets/images/events/banner-loaloa-viet-nam-bung-sang.jpg', NULL, 'LOALOA SHOW', 'Chương trình nghệ thuật thực cảnh & ẩm thực di sản', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(138, NULL, '[Hài kịch trinh thám] BÓNG MA RẠP HÁT LỆ HOA - SÂN KHẤU MỚI', NULL, 'Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 250000, '2026-11-22 19:30:00', NULL, '19:30 - 22:30, 22 Tháng 11, 2026', 'Sân Khấu Mới', 350, 2, '2026-11-15 11:13:19', '../assets/images/events/banner-skm-bong-ma.jpg', NULL, 'Sân Khấu Mới', 'Sân khấu hài kịch & chính kịch TP.HCM', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(139, NULL, 'SKNT TRƯƠNG HÙNG MINH : NGĂN LẠNH SỐ 44', NULL, '22 Vĩnh Viễn, Quận 10, TP. Hồ Chí Minh', 'offline', NULL, 300000, '2026-11-15 20:00:00', NULL, '20:00 - 23:00, 15 Tháng 11, 2026', 'Sân khấu Nghệ thuật Trương Hùng Minh', 350, 2, '2026-11-15 11:36:15', '../assets/images/events/banner-thm-ngan-lanh.jpg', NULL, 'SÂN KHẤU NGHỆ THUẬT TRƯƠNG HÙNG MINH', 'Sân khấu kịch nghệ thuật Trương Hùng Minh', 4, 'cong_khai', NULL, 'public', NULL, NULL),
(140, 4, 'QUYET CHIEN SHIBUYA', '<p>CUOC CHIEN CUA 2 KE MANH NHAT</p>', 'CTU, SO 3 DUONG 3/2, XUAN KHANH, NINH KIEU, CAN THO', 'offline', NULL, 999999, '2026-11-30 00:10:00', '2026-12-17 23:52:00', NULL, NULL, 0, 4, '2026-11-17 16:52:25', '../assets/uploads/events/poster_4_1794934345_b3f604.jpg', '../assets/uploads/events/banner_4_1794934345_0cb5d9.jpg', 'Kim JiWon', NULL, 2, 'cong_khai', 'shibuya', 'public', 'Thank you so much!!!', '{\"account_holder\":\"Kim Ji Wonm\",\"account_number\":\"012345678987654321\",\"bank_name\":\"MB Bank\",\"bank_branch\":\"Can Tho\",\"business_type\":\"Cá nhân\",\"tax_code\":\"0987654321\"}')
ON DUPLICATE KEY UPDATE
  `nguoi_tao_id` = VALUES(`nguoi_tao_id`),
  `ten_su_kien` = VALUES(`ten_su_kien`),
  `mo_ta` = VALUES(`mo_ta`),
  `dia_diem` = VALUES(`dia_diem`),
  `hinh_thuc` = VALUES(`hinh_thuc`),
  `link_online` = VALUES(`link_online`),
  `gia_thap_nhat` = VALUES(`gia_thap_nhat`),
  `thoi_gian` = VALUES(`thoi_gian`),
  `thoi_gian_ket_thuc` = VALUES(`thoi_gian_ket_thuc`),
  `thoi_gian_hien_thi` = VALUES(`thoi_gian_hien_thi`),
  `ten_dia_diem` = VALUES(`ten_dia_diem`),
  `so_luong_toi_da` = VALUES(`so_luong_toi_da`),
  `nguoi_tao` = VALUES(`nguoi_tao`),
  `ngay_tao` = VALUES(`ngay_tao`),
  `hinh_anh` = VALUES(`hinh_anh`),
  `banner` = VALUES(`banner`),
  `ten_ban_to_chuc` = VALUES(`ten_ban_to_chuc`),
  `mo_ta_ban_to_chuc` = VALUES(`mo_ta_ban_to_chuc`),
  `danh_muc_id` = VALUES(`danh_muc_id`),
  `trang_thai` = VALUES(`trang_thai`),
  `url_tuy_chinh` = VALUES(`url_tuy_chinh`),
  `quyen_rieng_tu` = VALUES(`quyen_rieng_tu`),
  `loi_nhan_cam_on` = VALUES(`loi_nhan_cam_on`),
  `thong_tin_thanh_toan` = VALUES(`thong_tin_thanh_toan`);

ALTER TABLE `sukien` AUTO_INCREMENT = 142;

-- --------------------------------------------------------
-- 5. Bảng `loaive`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `loaive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `su_kien_id` int(11) NOT NULL,
  `ten_loai_ve` varchar(150) NOT NULL,
  `gia_ve` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `da_het_ve` tinyint(1) NOT NULL DEFAULT 0,
  `so_luong` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `mo_ta` text DEFAULT NULL,
  `bat_dau_ban` datetime DEFAULT NULL,
  `ket_thuc_ban` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_loaive_sukien` (`su_kien_id`),
  CONSTRAINT `fk_loaive_sukien` FOREIGN KEY (`su_kien_id`) REFERENCES `sukien` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `loaive` WHERE `id` IN (103, 105, 106, 107, 108, 109, 111, 112, 188);

INSERT INTO `loaive` (`id`, `su_kien_id`, `ten_loai_ve`, `gia_ve`, `da_het_ve`, `so_luong`, `mo_ta`, `bat_dau_ban`, `ket_thuc_ban`) VALUES
(1, 1, 'SKY LOUNGE', 8800000, 0, 100, NULL, NULL, NULL),
(2, 1, 'SVIP A', 5000000, 0, 100, NULL, NULL, NULL),
(3, 1, 'VIP A', 3000000, 0, 100, NULL, NULL, NULL),
(4, 1, 'FANZONE', 880000, 0, 100, NULL, NULL, NULL),
(5, 2, 'VÉ BƯỚC THONG RONG (GA)', 499000, 0, 100, NULL, NULL, NULL),
(6, 2, 'VÉ MỘNG MƠ (SVIP)', 1200000, 0, 100, NULL, NULL, NULL),
(7, 3, 'GA 1', 1600000, 0, 100, NULL, NULL, NULL),
(8, 3, 'VIP DỌC', 3200000, 0, 100, NULL, NULL, NULL),
(9, 4, 'SKY LOUNGE', 10000000, 0, 100, NULL, NULL, NULL),
(10, 4, 'SVIP A', 5000000, 0, 100, NULL, NULL, NULL),
(11, 4, 'FANZONE A', 2500000, 0, 100, NULL, NULL, NULL),
(12, 5, 'STANDARD', 800000, 0, 100, NULL, NULL, NULL),
(13, 5, 'VIP S\'NOW', 1800000, 0, 100, NULL, NULL, NULL),
(14, 6, 'GA RỰC RỠ', 1500000, 0, 100, NULL, NULL, NULL),
(15, 6, 'VIP MEET & GREET', 3000000, 0, 100, NULL, NULL, NULL),
(16, 7, 'VÉ DỊ KIỀU', 1450000, 0, 100, NULL, NULL, NULL),
(17, 7, 'VÉ ĐẾN GIÂY CUỐI CÙNG', 2800000, 0, 100, NULL, NULL, NULL),
(18, 8, 'NHÂN TỐ X - GA', 1500000, 0, 100, NULL, NULL, NULL),
(19, 8, 'NHÂN TỐ X - VIP', 3200000, 0, 100, NULL, NULL, NULL),
(20, 9, 'GA CHÔNG GAI', 1000000, 0, 100, NULL, NULL, NULL),
(21, 9, 'VIP ANH TRAI', 3500000, 0, 100, NULL, NULL, NULL),
(22, 9, 'PRESIDENT LOUNGE', 8000000, 0, 100, NULL, NULL, NULL),
(23, 10, 'GA HIỆN DIỆN', 800000, 0, 100, NULL, NULL, NULL),
(24, 10, 'VIP UPRIZE', 2200000, 0, 100, NULL, NULL, NULL),
(25, 11, 'VÉ BAY ĐƠN TRẢI NGHIỆM', 2190000, 0, 100, NULL, NULL, NULL),
(26, 11, 'VÉ BAY ĐÔI + PHIM PHOTO', 2890000, 0, 100, NULL, NULL, NULL),
(27, 12, 'MÓC KHÓA OFFICIAL', 25000, 0, 100, NULL, NULL, NULL),
(28, 12, 'ÁO T-SHIRT PRO-AM', 350000, 0, 100, NULL, NULL, NULL),
(29, 12, 'ÁO JERSEY THI ĐẤU', 275000, 0, 100, NULL, NULL, NULL),
(30, 13, 'VÉ VÀO CỔNG THAM QUAN', 0, 0, 100, NULL, NULL, NULL),
(31, 13, 'GÓI ĐUA 10 PHÚT (SINGLE)', 220000, 0, 100, NULL, NULL, NULL),
(32, 14, 'VÉ XEM THỜI TRANG & THI ĐẤU (STANDARD)', 199000, 0, 100, NULL, NULL, NULL),
(33, 14, 'VIP PASS + GIFTSET', 499000, 0, 100, NULL, NULL, NULL),
(34, 15, 'VÉ XEM TRỰC TIẾP (DAY PASS)', 199000, 0, 100, NULL, NULL, NULL),
(35, 15, 'VIP FANZONE PASS', 500000, 1, 100, NULL, NULL, NULL),
(36, 16, 'VÉ KHU C (STAND C)', 350000, 0, 100, NULL, NULL, NULL),
(37, 16, 'VÉ KHU B (STAND B)', 600000, 0, 100, NULL, NULL, NULL),
(38, 16, 'VÉ RINGSSIDE (OCTAGON VIP)', 2000000, 0, 100, NULL, NULL, NULL),
(39, 17, 'VÉ TIÊU CHUẨN (STANDARD)', 259000, 1, 100, NULL, NULL, NULL),
(40, 17, 'VÉ VIP + GIFTSET ĐẶC BIỆT', 499000, 1, 100, NULL, NULL, NULL),
(43, 19, 'VÉ THAM GIA WORKSHOP', 400000, 0, 100, NULL, NULL, NULL),
(44, 20, 'VÉ TIÊU CHUẨN', 350000, 0, 100, NULL, NULL, NULL),
(45, 20, 'VÉ VIP HÀNG ĐẦU', 450000, 0, 100, NULL, NULL, NULL),
(48, 22, 'VÉ THAM QUAN & XEM SHOW', 1300000, 0, 100, NULL, NULL, NULL),
(49, 22, 'VÉ PREMIUM CHÀO SHOW', 1800000, 0, 100, NULL, NULL, NULL),
(50, 23, 'VÉ WORKSHOP MOCHI', 370000, 0, 100, NULL, NULL, NULL),
(51, 24, 'VÉ WORKSHOP MATCHA TARTE', 420000, 0, 100, NULL, NULL, NULL),
(52, 25, 'VÉ VẼ TRANH (BAO GỒM DỤNG CỤ)', 420000, 0, 100, NULL, NULL, NULL),
(53, 26, 'VÉ WORKSHOP KHUNG RÊU', 450000, 0, 100, NULL, NULL, NULL),
(54, 27, 'VÉ DECOR BÍ NGÔ HALLOWEEN', 293000, 0, 100, NULL, NULL, NULL),
(55, 28, 'VÉ WORKSHOP TERRARIUM MINI', 395000, 0, 100, NULL, NULL, NULL),
(56, 29, 'VÉ TRỒNG TIỂU CẢNH SEN ĐÁ', 385000, 0, 100, NULL, NULL, NULL),
(57, 30, 'VÉ WORKSHOP NƯƠNG GỖ', 465000, 0, 100, NULL, NULL, NULL),
(58, 31, 'VÉ LÀM TRANH ÉP THỰC VẬT', 305000, 0, 100, NULL, NULL, NULL),
(59, 32, 'VÉ LÀM TRANH RÊU ĐỘC BẢN', 370000, 0, 100, NULL, NULL, NULL),
(60, 33, 'VÉ TÚI THƠM THẢO MỘC', 215000, 0, 100, NULL, NULL, NULL),
(61, 34, 'VÉ BAY ĐÔI CÙNG HLV', 2190000, 0, 100, NULL, NULL, NULL),
(62, 34, 'VÉ BAY ĐÔI + GÓI CHỤP MEDIA GOPRO', 2690000, 0, 100, NULL, NULL, NULL),
(63, 35, 'VÉ TRẺ EM (NGÀY THƯỜNG)', 60000, 0, 100, NULL, NULL, NULL),
(64, 35, 'VÉ TRẺ EM + NGƯỜI LỚN (CUỐI TUẦN)', 120000, 0, 100, NULL, NULL, NULL),
(65, 36, 'VÉ VÀO CỔNG TRẺ EM', 60000, 0, 100, NULL, NULL, NULL),
(66, 36, 'VÉ VÀO CỔNG NGƯỜI LỚN', 150000, 0, 100, NULL, NULL, NULL),
(67, 36, 'VÉ TRỌN GÓI BẮN SÚNG & BIỂN TIÊN ĐỒNG', 350000, 0, 100, NULL, NULL, NULL),
(68, 37, 'VÉ THAM QUAN & THƯỞNG THỨC PHỞ', 200000, 0, 100, NULL, NULL, NULL),
(69, 37, 'VÉ TRẢI NGHIỆM TRÁNG BÁNH PHỞ THỦ CÔNG', 350000, 0, 100, NULL, NULL, NULL),
(70, 38, 'VÉ THAM QUAN TRIỂN LÃM', 150000, 0, 100, NULL, NULL, NULL),
(71, 39, 'VÉ XE BUÝT 1 VÒNG (CHUYẾN NGÀY)', 105000, 0, 100, NULL, NULL, NULL),
(72, 39, 'VÉ XE BUÝT 1 VÒNG (CHUYẾN ĐÊM)', 145000, 0, 100, NULL, NULL, NULL),
(73, 39, 'VÉ HOP-ON HOP-OFF 24 GIỜ', 475000, 0, 100, NULL, NULL, NULL),
(74, 40, 'VÉ ĐĂNG KÝ TRẢI NGHIỆM MẪU', 0, 0, 100, NULL, NULL, NULL),
(75, 40, 'VÉ TOUR ĐÊM CHÍNH THỨC', 199000, 0, 100, NULL, NULL, NULL),
(76, 41, 'VÉ TRẢI NGHIỆM 2 TIẾNG', 190000, 0, 100, NULL, NULL, NULL),
(77, 41, 'VÉ KHÔNG GIỚI HẠN THỜI GIAN', 290000, 0, 100, NULL, NULL, NULL),
(78, 42, 'VÉ HẠNG LẦU (STANDARD)', 300000, 0, 100, NULL, NULL, NULL),
(79, 42, 'VÉ HẠNG TRỆT (VIP)', 500000, 0, 100, NULL, NULL, NULL),
(80, 43, 'VÉ HẠNG C (STANDARD)', 500000, 0, 100, NULL, NULL, NULL),
(81, 43, 'VÉ HẠNG B (VIP)', 1000000, 0, 100, NULL, NULL, NULL),
(82, 43, 'VÉ HẠNG A (VVIP)', 1500000, 0, 100, NULL, NULL, NULL),
(85, 45, 'VÉ TIÊU CHUẨN (STANDARD)', 300000, 0, 100, NULL, NULL, NULL),
(86, 45, 'VÉ VIP', 450000, 0, 100, NULL, NULL, NULL),
(87, 46, 'VÉ PHỔ THÔNG', 175000, 0, 100, NULL, NULL, NULL),
(88, 46, 'VÉ KHÁN ĐÀI VIP', 350000, 0, 100, NULL, NULL, NULL),
(89, 47, 'VÉ STANDARD', 500000, 0, 100, NULL, NULL, NULL),
(90, 47, 'VÉ VIP EXPERIENCE', 850000, 0, 100, NULL, NULL, NULL),
(91, 48, 'VÉ TIÊU CHUẨN (STANDARD)', 300000, 0, 100, NULL, NULL, NULL),
(92, 48, 'VÉ VIP', 450000, 0, 100, NULL, NULL, NULL),
(93, 49, 'VÉ THƯỜNG', 250000, 0, 100, NULL, NULL, NULL),
(94, 49, 'VÉ VIP', 400000, 0, 100, NULL, NULL, NULL),
(95, 50, 'VÉ TIÊU CHUẨN', 330000, 0, 100, NULL, NULL, NULL),
(96, 50, 'VÉ VIP', 450000, 0, 100, NULL, NULL, NULL),
(97, 51, 'STANDARD SEAT', 850000, 0, 100, NULL, NULL, NULL),
(98, 51, 'VIP TABLE (INCL. DRINK)', 1350000, 0, 100, NULL, NULL, NULL),
(99, 52, 'VÉ TRẺ EM / TIÊU CHUẨN', 120000, 0, 100, NULL, NULL, NULL),
(100, 52, 'VÉ GIA ĐÌNH VIP', 250000, 0, 100, NULL, NULL, NULL),
(101, 53, 'GA CHÔNG GAI', 1000000, 1, 0, NULL, NULL, NULL),
(102, 54, 'VÉ TIỂU NƠ STANDARD', 350000, 0, 100, NULL, NULL, NULL),
(104, 56, 'VÉ MIÊN MAN', 490000, 0, 100, NULL, NULL, NULL),
(110, 10, 'VÉ SAO FANZONE', 750000, 0, 100, NULL, NULL, NULL),
(113, 65, 'VÉ BƯỚC THONG RONG (GA)', 499000, 0, 100, NULL, NULL, NULL),
(114, 66, 'VÉ SUMMER PASS', 855000, 1, 0, NULL, NULL, NULL),
(115, 67, 'VÉ YEAR END 2025', 620000, 1, 0, NULL, NULL, NULL),
(116, 68, 'VÉ CHAPTER 5', 700000, 1, 0, NULL, NULL, NULL),
(117, 69, 'VÉ CHAPTER 4', 860000, 1, 0, NULL, NULL, NULL),
(118, 70, 'VÉ CHAPTER 3', 860000, 1, 0, NULL, NULL, NULL),
(119, 71, 'VÉ OSUNFEST', 500000, 1, 0, NULL, NULL, NULL),
(120, 72, 'SET MERCHANDISE STANDARD', 299000, 0, 100, NULL, NULL, NULL),
(121, 73, 'VÉ NOEL STANDARD', 800000, 0, 100, NULL, NULL, NULL),
(122, 74, 'GA NHÀ HÀO MÔN', 1500000, 1, 0, NULL, NULL, NULL),
(123, 75, 'VÉ NHM SHOW', 800000, 1, 0, NULL, NULL, NULL),
(124, 76, 'VÉ FAN MEETING', 0, 1, 0, NULL, NULL, NULL),
(125, 77, 'VÉ Y-CONCERT', 800000, 1, 0, NULL, NULL, NULL),
(126, 78, 'VÉ FAN MEETING HN', 0, 1, 0, NULL, NULL, NULL),
(127, 79, 'VÉ FAN MEETING HCM', 0, 1, 0, NULL, NULL, NULL),
(128, 80, 'VÉ MEMORY SHOW', 700000, 0, 100, NULL, NULL, NULL),
(129, 81, 'VÉ STARLIGHT II', 900000, 0, 100, NULL, NULL, NULL),
(130, 82, 'VÉ PHỤ THU ĐÊM NHẠC', 500000, 1, 0, NULL, NULL, NULL),
(131, 83, 'GA NHÀ SOẠN NHẠC', 1500000, 1, 0, NULL, NULL, NULL),
(132, 84, 'GA NHƯ NGÀY ĐẦU', 1500000, 0, 100, NULL, NULL, NULL),
(133, 85, 'GA NHÓM THÁM THÍNH', 1500000, 0, 100, NULL, NULL, NULL),
(134, 86, 'VÉ KOSMIK GA', 800000, 1, 0, NULL, NULL, NULL),
(135, 87, 'VÉ FANSIGN', 0, 1, 0, NULL, NULL, NULL),
(136, 88, 'VÉ FANZONE KINGDOM', 0, 1, 0, NULL, NULL, NULL),
(137, 89, 'KOSMIK MERCHANDISE', 100000, 0, 100, NULL, NULL, NULL),
(138, 90, 'GA GAI HOME', 1000000, 1, 0, NULL, NULL, NULL),
(139, 91, 'FANZONE ĐÊM 3', 800000, 1, 0, NULL, NULL, NULL),
(140, 92, 'FANZONE ĐÊM 2', 800000, 1, 0, NULL, NULL, NULL),
(141, 93, 'VÉ SÂN KHẤU STANDARD', 300000, 0, 100, NULL, NULL, NULL),
(142, 94, 'VÉ SÂN KHẤU STANDARD', 300000, 0, 100, NULL, NULL, NULL),
(143, 95, 'VÉ SÂN KHẤU STANDARD', 300000, 0, 100, NULL, NULL, NULL),
(144, 96, 'VÉ SÂN KHẤU STANDARD', 300000, 0, 100, NULL, NULL, NULL),
(145, 97, 'VÉ SÂN KHẤU STANDARD', 300000, 0, 100, NULL, NULL, NULL),
(146, 98, 'VÉ PHIỀN MEETING', 450000, 1, 0, NULL, NULL, NULL),
(147, 99, 'VÉ GAMA GA', 82000, 1, 0, NULL, NULL, NULL),
(148, 100, 'VÉ FIESTA PASS', 250000, 1, 0, NULL, NULL, NULL),
(149, 101, 'VÉ PHUCJUOI STANDARD', 800000, 1, 0, NULL, NULL, NULL),
(150, 102, 'VÉ THE BROTHERS', 1000000, 1, 0, NULL, NULL, NULL),
(151, 103, 'VÉ PHỤ THU LIVE BAND', 900000, 1, 0, NULL, NULL, NULL),
(152, 104, 'VÉ PHỤ THU ĐÊM NHẠC', 500000, 1, 0, NULL, NULL, NULL),
(153, 105, 'VÉ DẠ QUANG STANDARD', 480000, 0, 100, NULL, NULL, NULL),
(154, 106, 'VÉ PHỤ THU ĐÊM NHẠC', 500000, 1, 0, NULL, NULL, NULL),
(155, 107, 'VÉ ENCORE EXTENDED', 800000, 1, 0, NULL, NULL, NULL),
(156, 108, 'VÉ FAN CONCERT HCM', 800000, 1, 0, NULL, NULL, NULL),
(157, 109, 'VÉ FAN CONCERT HN', 800000, 1, 0, NULL, NULL, NULL),
(158, 110, 'VÉ FAN CONCERT', 600000, 1, 0, NULL, NULL, NULL),
(160, 112, 'SET QUÀ BÍ MẬT', 0, 1, 0, NULL, NULL, NULL),
(161, 113, 'VÉ HOMECOMING PASS', 799000, 0, 100, NULL, NULL, NULL),
(162, 114, 'VÉ J-UNZIP SHOWCASE', 724000, 1, 0, NULL, NULL, NULL),
(163, 115, 'VÉ ALL-ROUNDER GA', 800000, 1, 0, NULL, NULL, NULL),
(164, 116, 'VÉ EM XINH GA', 800000, 1, 0, NULL, NULL, NULL),
(165, 117, 'SUBICHA MERCH ITEM', 60000, 0, 200, NULL, NULL, NULL),
(166, 118, 'VÉ THE AURA STANDARD', 1250000, 0, 100, NULL, NULL, NULL),
(167, 119, 'VÉ SÂN KHẤU STANDARD', 300000, 0, 100, NULL, NULL, NULL),
(168, 120, 'VÉ SÂN KHẤU STANDARD', 300000, 0, 100, NULL, NULL, NULL),
(169, 121, 'VÉ SÂN KHẤU STANDARD', 300000, 0, 100, NULL, NULL, NULL),
(170, 122, 'VÉ SÂN KHẤU MỚI', 250000, 0, 100, NULL, NULL, NULL),
(171, 123, 'VÉ SÂN KHẤU MỚI', 250000, 0, 100, NULL, NULL, NULL),
(172, 124, 'VÉ SÂN KHẤU MỚI', 250000, 1, 0, NULL, NULL, NULL),
(173, 125, 'VÉ DI SẢN VIP', 1600000, 0, 100, NULL, NULL, NULL),
(174, 126, 'VÉ SÂN KHẤU THM', 300000, 0, 100, NULL, NULL, NULL),
(175, 127, 'VÉ SÂN KHẤU THM', 300000, 1, 0, NULL, NULL, NULL),
(176, 128, 'VÉ SÂN KHẤU THM', 300000, 1, 0, NULL, NULL, NULL),
(177, 129, 'VÉ PHỤ THU LIVE BAND', 600000, 1, 0, NULL, NULL, NULL),
(178, 130, 'VÉ PHỤ THU LIVE BAND', 900000, 1, 0, NULL, NULL, NULL),
(179, 131, 'VÉ PHỤ THU LIVE BAND', 900000, 1, 0, NULL, NULL, NULL),
(180, 132, 'VÉ SECRET GARDEN', 1000000, 1, 0, NULL, NULL, NULL),
(181, 133, 'VÉ THANH ÂM MÙA HẠ', 0, 1, 0, NULL, NULL, NULL),
(182, 134, 'VÉ KỊCH THANH NIÊN', 350000, 1, 0, NULL, NULL, NULL),
(183, 135, 'VÉ KỊCH THANH NIÊN', 350000, 1, 0, NULL, NULL, NULL),
(184, 136, 'VÉ KỊCH THANH NIÊN', 350000, 1, 0, NULL, NULL, NULL),
(185, 137, 'VÉ VIỆT NAM BỪNG SÁNG', 1600000, 0, 100, NULL, NULL, NULL),
(186, 138, 'VÉ SÂN KHẤU MỚI', 250000, 0, 100, NULL, NULL, NULL),
(187, 139, 'VÉ SÂN KHẤU THM', 300000, 0, 100, NULL, NULL, NULL),
(190, 140, 'BANH TRUONG LANH DIA', 999999, 0, 100, 'BUOC VAO CUOC CHIEN', '2026-11-17 23:53:00', '2026-12-12 23:53:00')
ON DUPLICATE KEY UPDATE
  `su_kien_id` = VALUES(`su_kien_id`),
  `ten_loai_ve` = VALUES(`ten_loai_ve`),
  `gia_ve` = VALUES(`gia_ve`),
  `da_het_ve` = VALUES(`da_het_ve`),
  `so_luong` = VALUES(`so_luong`),
  `mo_ta` = VALUES(`mo_ta`),
  `bat_dau_ban` = VALUES(`bat_dau_ban`),
  `ket_thuc_ban` = VALUES(`ket_thuc_ban`);

ALTER TABLE `loaive` AUTO_INCREMENT = 192;

-- --------------------------------------------------------
-- 6. Bảng `the_thanh_vien_nguoidung`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `the_thanh_vien_nguoidung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_the` varchar(50) NOT NULL,
  `the_id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `ngay_kich_hoat` datetime NOT NULL DEFAULT current_timestamp(),
  `ngay_het_han` datetime NOT NULL,
  `trang_thai` enum('dang-hoat-dong','da-het-han','da-huy') NOT NULL DEFAULT 'dang-hoat-dong',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_the` (`ma_the`),
  KEY `fk_ttv_the` (`the_id`),
  KEY `fk_ttv_nguoidung` (`nguoi_dung_id`),
  CONSTRAINT `fk_ttv_nguoidung` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoidung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ttv_the` FOREIGN KEY (`the_id`) REFERENCES `the_thanh_vien` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `the_thanh_vien_nguoidung` (`id`, `ma_the`, `the_id`, `nguoi_dung_id`, `ngay_kich_hoat`, `ngay_het_han`, `trang_thai`) VALUES
(1, 'MB-20261112-6157', 3, 2, '2026-11-12 21:53:20', '2027-05-12 15:53:20', 'dang-hoat-dong'),
(2, 'MB-20261117-1729', 1, 4, '2026-11-17 21:49:43', '2027-11-17 15:49:43', 'dang-hoat-dong')
ON DUPLICATE KEY UPDATE
  `ma_the` = VALUES(`ma_the`),
  `the_id` = VALUES(`the_id`),
  `nguoi_dung_id` = VALUES(`nguoi_dung_id`),
  `ngay_kich_hoat` = VALUES(`ngay_kich_hoat`),
  `ngay_het_han` = VALUES(`ngay_het_han`),
  `trang_thai` = VALUES(`trang_thai`);

ALTER TABLE `the_thanh_vien_nguoidung` AUTO_INCREMENT = 3;

-- --------------------------------------------------------
-- 7. Bảng `ticketstars`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ticketstars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(120) NOT NULL,
  `ten_hien_thi` varchar(150) NOT NULL,
  `loai` enum('NgheSi','BanToChuc','SanKhau') NOT NULL DEFAULT 'BanToChuc',
  `avatar` varchar(255) NOT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `banner_star` varchar(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh banner riêng của Star',
  `anh_bia` varchar(255) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `thong_bao` text DEFAULT NULL COMMENT 'Nội dung thông báo hiển thị trên trang Star',
  `luot_theo_doi` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tu_khoa_su_kien` varchar(255) NOT NULL,
  `su_kien_noi_bat_id` int(11) DEFAULT NULL COMMENT 'ID sự kiện nổi bật từ bảng sukien (NULL nếu không có)',
  `nghe_si_membership` varchar(150) DEFAULT NULL,
  `da_xac_minh` tinyint(1) NOT NULL DEFAULT 1,
  `thu_tu` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_ticketstars_sukien_noibat` (`su_kien_noi_bat_id`),
  CONSTRAINT `fk_ticketstars_sukien_noibat` FOREIGN KEY (`su_kien_noi_bat_id`) REFERENCES `sukien` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ticketstars` (`id`, `slug`, `ten_hien_thi`, `loai`, `avatar`, `banner`, `banner_star`, `anh_bia`, `mo_ta`, `thong_bao`, `luot_theo_doi`, `tu_khoa_su_kien`, `su_kien_noi_bat_id`, `nghe_si_membership`, `da_xac_minh`, `thu_tu`) VALUES
(1, 'yeah1-family', 'YeaH1 Family', 'BanToChuc', '../assets/images/stars/yeah1.jpg', '../assets/images/stars/banners/yeah1-family.jpg', '../assets/images/stars/banners/yeah1-family.jpg', '../assets/images/events/banner-anh-trai.jpg', 'Tập đoàn giải trí & nhà sản xuất chuỗi concert quy mô lớn Anh Trai Vượt Ngàn Chông Gai, GAI MEET, UPRIZE.', 'Lịch mở bán vé các đêm diễn tiếp theo của chuỗi concert sẽ được cập nhật trực tiếp tại mục Lịch hoạt động.', 58420, 'Anh Trai Vượt Ngàn Chông Gai|GAI MEET|UPRIZE|YeaH1', NULL, NULL, 1, 1),
(2, 'vie-concert', 'Vie Concert', 'BanToChuc', '../assets/images/stars/vieconcert.jpg', '../assets/images/stars/banners/vie-concert.jpg', '../assets/images/stars/banners/vie-concert.jpg', '../assets/images/events/banner-tinh-ha.jpg', 'Đơn vị tổ chức và sản xuất chuỗi concert Tinh Hà \"Say Hi\" cùng các lễ hội âm nhạc hàng đầu.', 'Theo dõi Vie Concert để không bỏ lỡ các suất mở bán vé sớm và quyền lợi Fanzone.', 64150, 'Tinh Hà|Vie Channel', NULL, NULL, 1, 2),
(3, 'ss-label', 'SS Label', 'BanToChuc', '../assets/images/stars/sslabel.jpg', '../assets/images/stars/banners/ss-label.jpg', '../assets/images/stars/banners/ss-label.jpg', '../assets/images/memberships/spacespeakers.jpg', 'Hãng thu âm & công ty quản lý nghệ sĩ trực thuộc SpaceSpeakers Group.', 'Chào mừng bạn đến với kênh chính thức của SS Label trên Ticketbox! Theo dõi ngay để nhận thông báo mở bán vé Presale sớm nhất.', 41200, 'Mắt Nhắm Mắt Mở|SpaceSpeakers', 89, 'SpaceSpeakers', 1, 3),
(4, 'gai-meet', 'GAI MEET', 'BanToChuc', '../assets/images/stars/gaimeet.jpg', '../assets/images/stars/banners/gai-meet.jpg', '../assets/images/stars/banners/gai-meet.jpg', '../assets/images/events/banner-nha-ruc-ro.jpg', 'Chuỗi sự kiện giao lưu âm nhạc & Fan Meeting độc quyền đồng sản xuất bởi YEAH1 x 1PRODUCTION.', NULL, 29800, 'GAI MEET', NULL, NULL, 1, 4),
(5, 'quoc-thien', 'Quốc Thiên', 'NgheSi', '../assets/images/stars/quocthien.jpg', '../assets/images/stars/banners/quoc-thien.jpg', '../assets/images/stars/banners/quoc-thien.jpg', '../assets/images/events/banner-skynote.jpg', 'Ca sĩ Quốc Thiên - Chủ nhân chuỗi Live Concert SKYNOTE và gói hội viên SKYNOTE Membership.', 'Hội viên SKYNOTE Membership được ưu tiên đặt vé sớm trước giờ mở bán công khai.', 36500, 'SKYNOTE|Quốc Thiên', NULL, 'Quốc Thiên', 1, 5),
(6, 'ngoc-thanh-tam', 'Ngọc Thanh Tâm', 'NgheSi', '../assets/images/stars/ngocthanhtam.jpg', '../assets/images/stars/banners/ngoc-thanh-tam.jpg', '../assets/images/stars/banners/ngoc-thanh-tam.jpg', '../assets/images/memberships/tieu-no.jpg', 'Diễn viên Ngọc Thanh Tâm - Cộng đồng thành viên TIỂU NƠ Membership độc quyền trên Ticketbox.', NULL, 21400, 'Ngọc Thanh Tâm|TIỂU NƠ', NULL, 'Ngọc Thanh Tâm', 1, 6),
(7, 'dinh-manh-ninh', 'Đinh Mạnh Ninh', 'NgheSi', '../assets/images/stars/dinhmanhninh.jpg', '../assets/images/stars/banners/dinh-manh-ninh.jpg', '../assets/images/stars/banners/dinh-manh-ninh.jpg', '../assets/images/events/banner-skynote.jpg', 'Ca sĩ - Nhạc sĩ Đinh Mạnh Ninh với các đêm nhạc Acoustic và bộ sưu tập Official Merchandise.', NULL, 18750, 'Đinh Mạnh Ninh', 72, NULL, 1, 7),
(8, 'phung-khanh-linh', 'Phùng Khánh Linh', 'NgheSi', '../assets/images/stars/phungkhanhlinh.jpg', '../assets/images/stars/banners/phung-khanh-linh.jpg', '../assets/images/stars/banners/phung-khanh-linh.jpg', '../assets/images/events/banner-ntpmm.jpg', 'Ca sĩ - Nhạc sĩ dòng nhạc Indie Pop / City Pop với chuỗi tour diễn và album được yêu thích.', NULL, 24902, 'Phùng Khánh Linh|Những Thành Phố Mộng Mơ', NULL, NULL, 1, 8),
(9, 'nhung-thanh-pho-mong-mo', 'Những Thành Phố Mơ Màng', 'BanToChuc', '../assets/images/stars/ntpmm.jpg', '../assets/images/stars/banners/nhung-thanh-pho-mong-mo.jpg', '../assets/images/stars/banners/nhung-thanh-pho-mong-mo.jpg', '../assets/images/events/banner-ntpmm.jpg', 'Lễ hội âm nhạc Indie thường niên quy tụ dàn nghệ sĩ trẻ đình đám tại Hà Nội & TP. Hồ Chí Minh.', NULL, 55301, 'Những Thành Phố Mơ Màng|Những Thành Phố Mộng Mơ', NULL, 'Những Thành Phố Mộng Mơ', 1, 9),
(10, 'jun-pham', 'Jun Phạm', 'NgheSi', '../assets/images/stars/junpham.jpg', '../assets/images/stars/banners/jun-pham.jpg', '../assets/images/stars/banners/jun-pham.jpg', '../assets/images/memberships/jun-pham.jpg', 'Ca sĩ, diễn viên, nhà văn Jun Phạm - Official Fanclub & Membership trên Ticketbox.', NULL, 33900, 'Jun Phạm', NULL, 'Jun Phạm', 1, 10),
(11, 'subicha', 'Subicha', 'NgheSi', '../assets/images/stars/subicha.jpg', '../assets/images/stars/banners/subicha.jpg', '../assets/images/stars/banners/subicha.jpg', '../assets/images/events/banner-snow.jpg', 'Nghệ sĩ sáng tạo & biểu diễn độc lập đồng hành cùng các sự kiện giải trí giới trẻ.', NULL, 14200, 'Subicha', 117, NULL, 1, 11),
(12, 'hoang-dung', 'Hoàng Dũng', 'NgheSi', '../assets/images/stars/hoangdung.jpg', '../assets/images/stars/banners/hoang-dung.jpg', '../assets/images/stars/banners/hoang-dung.jpg', '../assets/images/events/banner-ntpmm.jpg', 'Ca sĩ - Nhạc sĩ Hoàng Dũng với chuỗi concert Yên và các đêm nhạc tự sự sâu lắng.', NULL, 42800, 'Hoàng Dũng', NULL, NULL, 1, 12),
(13, 'cheng', 'Cheng', 'NgheSi', '../assets/images/stars/cheng.jpg', '../assets/images/stars/banners/cheng.jpg', '../assets/images/stars/banners/cheng.jpg', '../assets/images/events/banner-snow.jpg', 'Nghệ sĩ trẻ Cheng với phong cách âm nhạc hiện đại, sôi động.', NULL, 16400, 'Cheng', NULL, NULL, 1, 13),
(14, 'mr-siro', 'Mr. Siro', 'NgheSi', '../assets/images/stars/mrsiro.jpg', '../assets/images/stars/banners/mr-siro.jpg', '../assets/images/stars/banners/mr-siro.jpg', '../assets/images/events/banner-skynote.jpg', 'Nhạc sĩ - Ca sĩ Mr. Siro với những bản Ballad chạm đến trái tim hàng triệu khán giả.', NULL, 49100, 'Mr. Siro', NULL, NULL, 1, 14),
(15, 'nguyen-van-chung', 'Nguyễn Văn Chung', 'NgheSi', '../assets/images/stars/nguyenvanchung.jpg', '../assets/images/stars/banners/nguyen-van-chung.jpg', '../assets/images/stars/banners/nguyen-van-chung.jpg', '../assets/images/events/banner-chao-show.jpg', 'Nhạc sĩ Nguyễn Văn Chung - Tác giả của hàng trăm ca khúc nhạc trẻ và thiếu nhi kinh điển.', NULL, 27600, 'Nguyễn Văn Chung', NULL, NULL, 1, 15),
(16, 'tang-phuc', 'Tăng Phúc', 'NgheSi', '../assets/images/stars/tangphuc.jpg', '../assets/images/stars/banners/tang-phuc.jpg', '../assets/images/stars/banners/tang-phuc.jpg', '../assets/images/events/banner-strong.jpg', 'Ca sĩ Tăng Phúc - Chủ nhân chuỗi đêm nhạc Sun Song và các bản cover triệu view.', NULL, 31200, 'Tăng Phúc', NULL, NULL, 1, 16),
(17, 'dj-mie', 'DJ Mie', 'NgheSi', '../assets/images/stars/djmie.jpg', '../assets/images/stars/banners/dj-mie.jpg', '../assets/images/stars/banners/dj-mie.jpg', '../assets/images/events/banner-tinh-ha.jpg', 'Nữ DJ hàng đầu Việt Nam khuấy đảo các lễ hội âm nhạc điện tử lớn.', NULL, 38900, 'DJ Mie', NULL, NULL, 1, 17),
(18, 'kich-idecaf', 'Kịch IDECAF', 'BanToChuc', '../assets/images/stars/idecaf.jpg', '../assets/images/stars/banners/kich-idecaf.jpg', '../assets/images/stars/banners/kich-idecaf.jpg', '../assets/images/events/banner-hon-ai-nay-giu.jpg', 'Nhà hát kịch IDECAF - Sân khấu kịch nói chuyên nghiệp hàng đầu tại TP. Hồ Chí Minh.', NULL, 44700, 'Kịch IDECAF', 93, NULL, 1, 18),
(19, 'phong-tra-ben-thanh', 'Phòng trà Bến Thành', 'SanKhau', '../assets/images/stars/phongtrabenthanh.jpg', '../assets/images/stars/banners/phong-tra-ben-thanh.jpg', '../assets/images/stars/banners/phong-tra-ben-thanh.jpg', '../assets/images/events/banner-chao-show.jpg', 'Không gian âm nhạc ấm cúng tại trung tâm Quận 1, nơi hội tụ các giọng ca hàng đầu Việt Nam.', NULL, 26300, 'Phòng trà Bến Thành', NULL, NULL, 1, 19),
(20, 'kich-doan-thanh-loc', 'KỊCH ĐOÀN THÀNH LỘC', 'BanToChuc', '../assets/images/stars/thiendang.jpg', '../assets/images/stars/banners/kich-doan-thanh-loc.jpg', '../assets/images/stars/banners/kich-doan-thanh-loc.jpg', '../assets/images/events/banner-co-giao-duyen.jpg', 'Sân khấu kịch Thiên Đăng - Kịch đoàn của NSƯT Thành Lộc với các vở diễn nghệ thuật đặc sắc.', NULL, 39500, 'THÀNH LỘC|Thiên Đăng', NULL, NULL, 1, 20),
(21, 'san-khau-moi', 'Sân Khấu Mới', 'BanToChuc', '../assets/images/stars/sankhaumoi.jpg', '../assets/images/stars/banners/san-khau-moi.jpg', '../assets/images/stars/banners/san-khau-moi.jpg', '../assets/images/events/banner-bong-ma-le-hoa.jpg', 'Đơn vị sản xuất các vở kịch tâm lý, kinh dị và giải trí đặc sắc tại TP. Hồ Chí Minh.', NULL, 19800, 'Sân Khấu Mới|LỆ HOA', NULL, NULL, 1, 21),
(22, 'sao-concert', 'SAO Concert', 'BanToChuc', '../assets/images/stars/saoconcert.jpg', '../assets/images/stars/banners/sao-concert.jpg', '../assets/images/stars/banners/sao-concert.jpg', '../assets/images/events/banner-uprize.jpg', 'Đơn vị tổ chức đại nhạc hội SAO Concert quy tụ dàn sao V-Pop đình đám.', NULL, 22400, 'SAO Concert', 10, NULL, 1, 22),
(23, 'sound-healing-concert', 'SOUND HEALING CONCERT', 'BanToChuc', '../assets/images/stars/soundhealing.jpg', '../assets/images/stars/banners/sound-healing-concert.jpg', '../assets/images/stars/banners/sound-healing-concert.jpg', '../assets/images/events/banner-sound-healing.jpg', 'Chuỗi hòa nhạc chữa lành kết hợp âm thanh chuông xoay và nhạc cụ truyền thống.', NULL, 15900, 'Sound Healing', 47, NULL, 1, 23),
(24, 'nha-hat-kich-thanh-nien', 'Nhà Hát Kịch Thanh Niên', 'BanToChuc', '../assets/images/stars/kichthanhnien.jpg', '../assets/images/stars/banners/nha-hat-kich-thanh-nien.jpg', '../assets/images/stars/banners/nha-hat-kich-thanh-nien.jpg', '../assets/images/events/banner-cuoc-phieu-luu-ky-thu.jpg', 'Sân khấu kịch trẻ trung, sáng tạo dành cho khán giả và gia đình tại TP. Hồ Chí Minh.', NULL, 23100, 'Thanh Niên|Thiếu Nhi', NULL, NULL, 1, 24),
(25, 'secret-garden', 'SECRET GARDEN', 'BanToChuc', '../assets/images/stars/secretgarden.jpg', '../assets/images/stars/banners/secret-garden.jpg', '../assets/images/stars/banners/secret-garden.jpg', '../assets/images/events/banner-moss-frame.jpg', 'Không gian biểu diễn âm nhạc Acoustic và nghệ thuật ẩn mình giữa lòng thành phố.', NULL, 17500, 'Secret Garden', NULL, NULL, 1, 25),
(26, 'cat-and-mouse', 'Cat & Mouse Live Music', 'BanToChuc', '../assets/images/stars/catandmouse.jpg', '../assets/images/stars/banners/cat-and-mouse.jpg', '../assets/images/stars/banners/cat-and-mouse.jpg', '../assets/images/stars/banners/cat-and-mouse.jpg', 'Chuỗi đêm nhạc Live Band và trình diễn nghệ thuật tương tác về đêm.', NULL, 16200, 'Cat & Mouse|Mystic Night', NULL, NULL, 1, 26),
(27, 'san-khau-nghe-thuat-truong-hung-minh', 'SÂN KHẤU NGHỆ THUẬT TRƯƠNG HÙNG MINH', 'BanToChuc', '../assets/images/stars/thegioitre.jpg', '../assets/images/stars/banners/san-khau-the-gioi-tre.jpg', '../assets/images/stars/banners/san-khau-the-gioi-tre.jpg', '../assets/images/events/banner-mat-ma-cau-co.jpg', 'Điểm hẹn sân khấu kịch giải trí sôi động tại 125 Cống Quỳnh, Quận 1, TP. Hồ Chí Minh.', NULL, 31800, 'Trương Hùng Minh', NULL, NULL, 1, 27),
(28, 'loaloa-show', 'LOALOA SHOW', 'BanToChuc', '../assets/images/stars/loaloa.jpg', '../assets/images/stars/banners/loaloa-show.jpg', '../assets/images/stars/banners/loaloa-show.jpg', '../assets/images/events/banner-loaloa-bung-sang.jpg', 'Chuỗi chương trình nghệ thuật kết hợp văn hóa dân gian và vũ điệu đương đại Việt Nam.', NULL, 20700, 'LOALOA SHOW', 137, NULL, 1, 28)
ON DUPLICATE KEY UPDATE
  `slug` = VALUES(`slug`),
  `ten_hien_thi` = VALUES(`ten_hien_thi`),
  `loai` = VALUES(`loai`),
  `avatar` = VALUES(`avatar`),
  `banner` = VALUES(`banner`),
  `banner_star` = VALUES(`banner_star`),
  `anh_bia` = VALUES(`anh_bia`),
  `mo_ta` = VALUES(`mo_ta`),
  `thong_bao` = VALUES(`thong_bao`),
  `luot_theo_doi` = VALUES(`luot_theo_doi`),
  `tu_khoa_su_kien` = VALUES(`tu_khoa_su_kien`),
  `su_kien_noi_bat_id` = VALUES(`su_kien_noi_bat_id`),
  `nghe_si_membership` = VALUES(`nghe_si_membership`),
  `da_xac_minh` = VALUES(`da_xac_minh`),
  `thu_tu` = VALUES(`thu_tu`);

ALTER TABLE `ticketstars` AUTO_INCREMENT = 29;

-- --------------------------------------------------------
-- 8. Bảng `ticketstar_sukien`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ticketstar_sukien` (
  `star_id` int(11) NOT NULL,
  `su_kien_id` int(11) NOT NULL,
  PRIMARY KEY (`star_id`,`su_kien_id`),
  KEY `fk_tss_sukien` (`su_kien_id`),
  CONSTRAINT `fk_tss_star` FOREIGN KEY (`star_id`) REFERENCES `ticketstars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tss_sukien` FOREIGN KEY (`su_kien_id`) REFERENCES `sukien` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM `ticketstar_sukien`
WHERE (`star_id` = 19 AND `su_kien_id` = 61)
   OR (`star_id` = 22 AND `su_kien_id` = 62)
   OR (`star_id` = 25 AND `su_kien_id` = 64);

INSERT INTO `ticketstar_sukien` (`star_id`, `su_kien_id`) VALUES
(1, 6), (1, 9), (1, 10), (1, 53), (1, 90),
(2, 3), (2, 4), (2, 91), (2, 92),
(3, 86), (3, 87), (3, 88), (3, 89),
(4, 6), (4, 8), (4, 74), (4, 83), (4, 84), (4, 85),
(5, 1), (5, 80), (5, 81), (5, 82),
(6, 76), (6, 77), (6, 78), (6, 79),
(7, 53), (7, 73), (7, 74), (7, 75),
(8, 68), (8, 69), (8, 70), (8, 71),
(9, 2), (9, 65), (9, 66), (9, 67),
(10, 9), (10, 10), (10, 53), (10, 73), (10, 118),
(11, 114), (11, 115), (11, 116), (11, 117),
(12, 2), (12, 6), (12, 65), (12, 113),
(13, 8), (13, 9), (13, 10), (13, 102), (13, 112),
(14, 107), (14, 108), (14, 109), (14, 110),
(15, 9), (15, 10), (15, 105), (15, 106),
(16, 101), (16, 102), (16, 103), (16, 104),
(17, 77), (17, 98), (17, 99), (17, 100),
(18, 45), (18, 48), (18, 94), (18, 95), (18, 96), (18, 97),
(19, 82), (19, 104), (19, 106),
(20, 42), (20, 119), (20, 120), (20, 121),
(21, 49), (21, 122), (21, 123), (21, 124), (21, 138),
(22, 10),
(23, 47),
(24, 52), (24, 134), (24, 135), (24, 136),
(25, 132), (25, 133),
(26, 46), (26, 51), (26, 103), (26, 129), (26, 130), (26, 131),
(27, 50), (27, 126), (27, 127), (27, 128), (27, 139),
(28, 125), (28, 137)
ON DUPLICATE KEY UPDATE
  `star_id` = VALUES(`star_id`);

-- --------------------------------------------------------
-- 9. Bảng `ticketstar_theodoi`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ticketstar_theodoi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) NOT NULL,
  `star_id` int(11) NOT NULL,
  `ngay_theo_doi` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_star` (`nguoi_dung_id`,`star_id`),
  KEY `idx_user_id` (`nguoi_dung_id`),
  KEY `idx_star_id` (`star_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ticketstar_theodoi` (`id`, `nguoi_dung_id`, `star_id`, `ngay_theo_doi`) VALUES
(1, 4, 8, '2026-11-17 22:09:48')
ON DUPLICATE KEY UPDATE
  `nguoi_dung_id` = VALUES(`nguoi_dung_id`),
  `star_id` = VALUES(`star_id`),
  `ngay_theo_doi` = VALUES(`ngay_theo_doi`);

ALTER TABLE `ticketstar_theodoi` AUTO_INCREMENT = 2;

-- --------------------------------------------------------
-- 10. Bảng `ve`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ve` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_ve` varchar(100) NOT NULL,
  `su_kien_id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `loai_ve` varchar(100) NOT NULL DEFAULT 'Vé Tiêu Chuẩn',
  `gia_ve` varchar(50) NOT NULL DEFAULT '0 đ',
  `trang_thai` varchar(50) NOT NULL DEFAULT 'thanh-cong',
  `ngay_dat` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_ve` (`ma_ve`),
  KEY `fk_ve_sukien` (`su_kien_id`),
  KEY `fk_ve_nguoidung` (`nguoi_dung_id`),
  CONSTRAINT `fk_ve_nguoidung` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoidung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ve_sukien` FOREIGN KEY (`su_kien_id`) REFERENCES `sukien` (`id`) ON DELETE CASCADE
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
(26, 'TKB-20261112-8027', 7, 2, 'VÉ DỊ KIỀU', '1.450.000 đ', 'thanh-cong', '2026-11-12 14:04:48'),
(27, 'TKB-20261115-1975', 1, 4, 'SKY LOUNGE', '8.800.000 đ', 'thanh-cong', '2026-11-15 08:11:31')
ON DUPLICATE KEY UPDATE
  `ma_ve` = VALUES(`ma_ve`),
  `su_kien_id` = VALUES(`su_kien_id`),
  `nguoi_dung_id` = VALUES(`nguoi_dung_id`),
  `loai_ve` = VALUES(`loai_ve`),
  `gia_ve` = VALUES(`gia_ve`),
  `trang_thai` = VALUES(`trang_thai`),
  `ngay_dat` = VALUES(`ngay_dat`);

ALTER TABLE `ve` AUTO_INCREMENT = 28;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;