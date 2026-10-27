<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Lấy ID sự kiện từ URL (Mặc định id = 1 nếu không truyền)
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// 2. Kho dữ liệu chi tiết đa dạng của từng sự kiện theo ID
$events_database = [
    1 => [
        'id' => 1,
        'title' => 'Live Concert SKYNOTE - Quốc Thiên',
        'date_str' => '19:30 - 23:00, 29 Tháng 11, 2026',
        'location_name' => 'Trung tâm Hội nghị Quốc gia',
        'location_address' => 'Phạm Hùng, Mễ Trì, Nam Từ Liêm, Hà Nội',
        'min_price' => '880.000 đ',
        'banner_img' => '../assets/images/events/banner-skynote.jpg',
        'organizer_name' => 'SKYNOTE Entertainment',
        'organizer_sub' => 'Đơn vị sản xuất & Tổ chức đêm nhạc',
        'categories' => [
            ['name' => 'SKY LOUNGE', 'price' => '8.800.000 đ', 'soldout' => false],
            ['name' => 'SVIP A', 'price' => '5.000.000 đ', 'soldout' => false],
            ['name' => 'VIP A', 'price' => '3.000.000 đ', 'soldout' => false],
            ['name' => 'FANZONE', 'price' => '880.000 đ', 'soldout' => true],
        ]
    ],
    2 => [
        'id' => 2,
        'title' => 'Những Thành Phố Mộng Mơ Year End 2026',
        'date_str' => '15:00 - 22:30, 12 Tháng 12, 2026',
        'location_name' => 'SECC - Trung tâm Hội chợ & Triển lãm Sài Gòn',
        'location_address' => '799 Nguyễn Văn Linh, Tân Phú, Quận 7, TP. Hồ Chí Minh',
        'min_price' => '499.000 đ',
        'banner_img' => '../assets/images/events/banner-ntpmm.jpg',
        'organizer_name' => 'Mộng Mơ Studio',
        'organizer_sub' => 'Ban tổ chức chuỗi sự kiện âm nhạc Indie',
        'categories' => [
            ['name' => 'VÉ BƯỚC THONG RONG (GA)', 'price' => '499.000 đ', 'soldout' => false],
            ['name' => 'VÉ MỘNG MƠ (SVIP)', 'price' => '1.200.000 đ', 'soldout' => false],
        ]
    ],
    3 => [
        'id' => 3,
        'title' => 'Mắt Nhắm Mắt Mở Concert',
        'date_str' => '18:00 - 22:00, 01 Tháng 11, 2026',
        'location_name' => 'Cung Thể Thao Quần Ngựa',
        'location_address' => 'Văn Cao, Liễu Giai, Ba Đình, Hà Nội',
        'min_price' => '1.600.000 đ',
        'banner_img' => '../assets/images/events/banner-mnmm.jpg',
        'organizer_name' => '1PRODUCTION',
        'organizer_sub' => 'Đơn vị quản lý & sản xuất',
        'categories' => [
            ['name' => 'GA 1', 'price' => '1.600.000 đ', 'soldout' => false],
            ['name' => 'VIP DỌC', 'price' => '3.200.000 đ', 'soldout' => false],
        ]
    ],
    4 => [
        'id' => 4,
        'title' => 'Tinh Hà "Say Hi" Concert - Đêm 4',
        'date_str' => '19:00 - 23:00, 15 Tháng 11, 2026',
        'location_name' => 'Trung tâm triển lãm Việt Nam (VEC)',
        'location_address' => 'Đường Cầu Tứ Liên, Xã Đông Anh, Thành phố Hà Nội',
        'min_price' => '800.000 đ',
        'banner_img' => '../assets/images/events/banner-tinh-ha.jpg',
        'organizer_name' => 'Vie Channel',
        'organizer_sub' => 'VieChannel: Đơn vị sản xuất',
        'categories' => [
            ['name' => 'SKY LOUNGE', 'price' => '10.000.000 đ', 'soldout' => false],
            ['name' => 'SVIP A', 'price' => '5.000.000 đ', 'soldout' => false],
            ['name' => 'FANZONE A', 'price' => '2.500.000 đ', 'soldout' => true],
        ]
    ],
    5 => [
        'id' => 5,
        'title' => 'The Starry - S\'NOW',
        'date_str' => '18:30 - 22:30, 19 Tháng 12, 2026',
        'location_name' => 'Nhà thi đấu Nguyễn Du',
        'location_address' => '116 Nguyễn Du, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh',
        'min_price' => '800.000 đ',
        'banner_img' => '../assets/images/events/banner-snow.jpg',
        'organizer_name' => 'The Starry Entertainment',
        'organizer_sub' => 'Đơn vị tổ chức đêm nhạc giáng sinh',
        'categories' => [
            ['name' => 'STANDARD', 'price' => '800.000 đ', 'soldout' => false],
            ['name' => 'VIP S\'NOW', 'price' => '1.800.000 đ', 'soldout' => false],
        ]
    ],
    6 => [
        'id' => 6,
        'title' => 'GAI MEET - NHÀ RỰC RỠ',
        'date_str' => '18:30 - 22:00, 18 Tháng 10, 2026',
        'location_name' => 'The Ants Studio',
        'location_address' => 'Vũ Tông Phan, An Phú, TP. Thủ Đức, TP. Hồ Chí Minh',
        'min_price' => '1.500.000 đ',
        'banner_img' => '../assets/images/events/banner-nha-ruc-ro.jpg',
        'organizer_name' => 'YEAH1 x 1PRODUCTION',
        'organizer_sub' => 'Đơn vị hợp tác sản xuất chuỗi show',
        'categories' => [
            ['name' => 'GA RỰC RỠ', 'price' => '1.500.000 đ', 'soldout' => false],
            ['name' => 'VIP MEET & GREET', 'price' => '3.000.000 đ', 'soldout' => false],
        ]
    ],
    7 => [
        'id' => 7,
        'title' => '(S)TRONG \'DỊ KIỀU\' SHOW 2026: ĐẾN GIÂY CUỐI CÙNG',
        'date_str' => '19:00 - 22:30, 07 Tháng 11, 2026',
        'location_name' => 'SOL 8 Live Stage',
        'location_address' => '8 Nguyễn Công Hoan, Ngọc Khánh, Ba Đình, Hà Nội',
        'min_price' => '1.450.000 đ',
        'banner_img' => '../assets/images/events/banner-strong.jpg',
        'organizer_name' => 'Strong Entertainment',
        'organizer_sub' => 'Ban tổ chức show diễn âm nhạc',
        'categories' => [
            ['name' => 'VÉ DỊ KIỀU', 'price' => '1.450.000 đ', 'soldout' => false],
            ['name' => 'VÉ ĐẾN GIÂY CUỐI CÙNG', 'price' => '2.800.000 đ', 'soldout' => false],
        ]
    ],
    8 => [
        'id' => 8,
        'title' => 'GAI MEET - DỰ ÁN NHÂN TỐ X',
        'date_str' => '18:30 - 22:00, 01 Tháng 11, 2026',
        'location_name' => 'The Ants Studio',
        'location_address' => 'Vũ Tông Phan, An Phú, TP. Thủ Đức, TP. Hồ Chí Minh',
        'min_price' => '1.500.000 đ',
        'banner_img' => '../assets/images/events/banner-nhan-to-x.jpg',
        'organizer_name' => 'YEAH1 x 1PRODUCTION',
        'organizer_sub' => 'Đơn vị hợp tác sản xuất',
        'categories' => [
            ['name' => 'NHÂN TỐ X - GA', 'price' => '1.500.000 đ', 'soldout' => false],
            ['name' => 'NHÂN TỐ X - VIP', 'price' => '3.200.000 đ', 'soldout' => false],
        ]
    ],
    9 => [
        'id' => 9,
        'title' => '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 1 & DAY 2',
        'date_str' => '17:00 - 23:00, 18 Tháng 10, 2026',
        'location_name' => 'The Global City',
        'location_address' => 'Đỗ Xuân Hợp, Phường An Phú, TP. Thủ Đức, TP. Hồ Chí Minh',
        'min_price' => '1.000.000 đ',
        'banner_img' => '../assets/images/events/banner-anh-trai.jpg',
        'organizer_name' => 'YeaH1 Group',
        'organizer_sub' => 'Đơn vị sản xuất độc quyền',
        'categories' => [
            ['name' => 'GA CHÔNG GAI', 'price' => '1.000.000 đ', 'soldout' => false],
            ['name' => 'VIP ANH TRAI', 'price' => '3.500.000 đ', 'soldout' => false],
            ['name' => 'PRESIDENT LOUNGE', 'price' => '8.000.000 đ', 'soldout' => true],
        ]
    ],
    10 => [
        'id' => 10,
        'title' => 'HIỆN DIỆN - UPRIZE CONCERT',
        'date_str' => '19:00 - 22:30, 12 Tháng 12, 2026',
        'location_name' => 'Cung Điền kinh Mỹ Đình',
        'location_address' => 'Đường Trần Hữu Dực, Cầu Diễn, Nam Từ Liêm, Hà Nội',
        'min_price' => '800.000 đ',
        'banner_img' => '../assets/images/events/banner-uprize.jpg',
        'organizer_name' => 'UPRIZE Entertainment',
        'organizer_sub' => 'Đơn vị sản xuất & Tổ chức sự kiện',
        'categories' => [
            ['name' => 'GA HIỆN DIỆN', 'price' => '800.000 đ', 'soldout' => false],
            ['name' => 'VIP UPRIZE', 'price' => '2.200.000 đ', 'soldout' => false],
        ]
    ],11 => [
        'id' => 11,
        'title' => 'Trải nghiệm bay dù lượn tại Sapa',
        'date_str' => '08:00 - 17:00, 01 Tháng 08, 2026',
        'location_name' => 'Điểm bay dù lượn Sapa',
        'location_address' => 'Đèo Ô Quy Hồ, Sapa, Lào Cai',
        'min_price' => '2.190.000 đ',
        'banner_img' => '../assets/images/events/banner-du-luon-sapa.jpg',
        'organizer_name' => 'Sapa Paragliding Club',
        'organizer_sub' => 'Đơn vị tổ chức trải nghiệm thể thao mạo hiểm',
        'categories' => [
            ['name' => 'VÉ BAY ĐƠN TRẢI NGHIỆM', 'price' => '2.190.000 đ', 'soldout' => false],
            ['name' => 'VÉ BAY ĐÔI + PHIM PHOTO', 'price' => '2.890.000 đ', 'soldout' => false],
        ]
    ],
    12 => [
        'id' => 12,
        'title' => 'SAIGON PRO-AM BASKETBALL CUP 2026 OFFICIAL MERCHANDISE',
        'date_str' => '09:00 - 21:00, 30 Tháng 09, 2026',
        'location_name' => 'Nhà thi đấu Hồ Xuân Hương',
        'location_address' => '2 Hồ Xuân Hương, Phường 6, Quận 3, TP. Hồ Chí Minh',
        'min_price' => '25.000 đ',
        'banner_img' => '../assets/images/events/banner-saigon-basketball.jpg',
        'organizer_name' => 'New Sports',
        'organizer_sub' => 'Đơn vị tổ chức giải bóng rổ Saigon Pro-Am',
        'categories' => [
            ['name' => 'MÓC KHÓA OFFICIAL', 'price' => '25.000 đ', 'soldout' => false],
            ['name' => 'ÁO T-SHIRT PRO-AM', 'price' => '350.000 đ', 'soldout' => false],
            ['name' => 'ÁO JERSEY THI ĐẤU', 'price' => '275.000 đ', 'soldout' => false],
        ]
    ],
    13 => [
        'id' => 13,
        'title' => 'TRẢI NGHIỆM ĐƯỜNG ĐUA GO - KART ĐA TẦNG TRONG NHÀ ĐẦU TIÊN TẠI VIỆT NAM',
        'date_str' => '10:00 - 22:00 hàng ngày, 19 Tháng 10, 2026',
        'location_name' => 'Vincom Mega Mall Thảo Điền',
        'location_address' => 'Tầng 5, 161 Xa lộ Hà Nội, Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh',
        'min_price' => '0 đ',
        'banner_img' => '../assets/images/events/banner-go-kart.jpg',
        'organizer_name' => 'INFINITY RACING',
        'organizer_sub' => 'Hệ thống đua xe Go-Kart đa tầng trong nhà',
        'categories' => [
            ['name' => 'VÉ VÀO CỔNG THAM QUAN', 'price' => '0 đ', 'soldout' => false],
            ['name' => 'GÓI ĐUA 10 PHÚT (SINGLE)', 'price' => '220.000 đ', 'soldout' => false],
        ]
    ],
    14 => [
        'id' => 14,
        'title' => 'Queens Of Glory - Giải Thể Thao Điện Tử Vinh Quang Nữ Hoàng',
        'date_str' => '14:00 - 21:00, 30 Tháng 10, 2026',
        'location_name' => 'VPS ARENA',
        'location_address' => '479B Hoàng Quốc Việt, Phường 13, Quận 10, TP. Hồ Chí Minh',
        'min_price' => '199.000 đ',
        'banner_img' => '../assets/images/events/banner-queens-glory.jpg',
        'organizer_name' => 'VPS Esports',
        'organizer_sub' => 'Đơn vị sản xuất giải đấu Esport nữ',
        'categories' => [
            ['name' => 'VÉ XEM THỜI TRANG & THI ĐẤU (STANDARD)', 'price' => '199.000 đ', 'soldout' => false],
            ['name' => 'VIP PASS + GIFTSET', 'price' => '499.000 đ', 'soldout' => false],
        ]
    ],
    15 => [
        'id' => 15,
        'title' => 'LCP 2026 Promotion Series',
        'date_str' => '13:00 - 21:00, 10 Tháng 11, 2026',
        'location_name' => 'VPS ARENA',
        'location_address' => '479B Hoàng Quốc Việt, Phường 13, Quận 10, TP. Hồ Chí Minh',
        'min_price' => '199.000 đ',
        'banner_img' => '../assets/images/events/banner-lcp-2026.jpg',
        'organizer_name' => 'Riot Games & VPS Entertainment',
        'organizer_sub' => 'Ban tổ chức giải đấu Liên Minh Huyền Thoại LCP',
        'categories' => [
            ['name' => 'VÉ XEM TRỰC TIẾP (DAY PASS)', 'price' => '199.000 đ', 'soldout' => false],
            ['name' => 'VIP FANZONE PASS', 'price' => '500.000 đ', 'soldout' => true],
        ]
    ],
    16 => [
        'id' => 16,
        'title' => 'Lion Championship 37 - 2026',
        'date_str' => '19:00 - 23:00, 21 Tháng 11, 2026',
        'location_name' => 'Nhà thi đấu Tây Hồ',
        'location_address' => '101 Võ Chí Công, Tây Hồ, Hà Nội',
        'min_price' => '350.000 đ',
        'banner_img' => '../assets/images/events/banner-lion-championship.jpg',
        'organizer_name' => 'Liên đoàn Võ thuật tổng hợp Việt Nam (VMMAF)',
        'organizer_sub' => 'Giải vô địch MMA chuyên nghiệp Việt Nam',
        'categories' => [
            ['name' => 'VÉ KHU C (STAND C)', 'price' => '350.000 đ', 'soldout' => false],
            ['name' => 'VÉ KHU B (STAND B)', 'price' => '600.000 đ', 'soldout' => false],
            ['name' => 'VÉ RINGSSIDE (OCTAGON VIP)', 'price' => '2.000.000 đ', 'soldout' => false],
        ]
    ],
    17 => [
        'id' => 17,
        'title' => 'VIEWING PARTY CHUNG KẾT TỔNG GIẢI ĐẤU VALORANT CHAMPIONS 2026',
        'date_str' => '11:00 - 20:00, 18 Tháng 10, 2026',
        'location_name' => 'Nhà thi đấu Tây Hồ',
        'location_address' => '101 Võ Chí Công, Phường Xuân La, Quận Tây Hồ, Hà Nội',
        'min_price' => '259.000 đ',
        'banner_img' => '../assets/images/events/banner-valorant-viewing-party.jpg',
        'organizer_name' => 'Riot Games & VNG Games',
        'organizer_sub' => 'Đơn vị phát hành & Ban tổ chức giải đấu Valorant tại Việt Nam',
        'categories' => [
            ['name' => 'VÉ TIÊU CHUẨN (STANDARD)', 'price' => '259.000 đ', 'soldout' => true],
            ['name' => 'VÉ VIP + GIFTSET ĐẶC BIỆT', 'price' => '499.000 đ', 'soldout' => true],
        ]
    ],18 => [
        'id' => 18,
        'title' => 'LOALOA SHOW: VIỆT NAM BỪNG SÁNG',
        'date_str' => '19:00 - 21:00, 27 Tháng 10, 2026',
        'location_name' => 'Nhà hát Sân khấu Nghệ thuật',
        'location_address' => 'Quận 1, TP. Hồ Chí Minh',
        'min_price' => '1.600.000 đ',
        'banner_img' => '../assets/images/events/banner-loaloa-bung-sang.jpg',
        'organizer_name' => 'LoaLoa Entertainment',
        'organizer_sub' => 'Đơn vị tổ chức show nghệ thuật văn hóa dân gian',
        'categories' => [
            ['name' => 'VÉ HẠNG BẠC (SILVER)', 'price' => '1.600.000 đ', 'soldout' => false],
            ['name' => 'VÉ HẠNG VÀNG (GOLD VIP)', 'price' => '2.500.000 đ', 'soldout' => false],
        ]
    ],
    19 => [
        'id' => 19,
        'title' => 'A LITTLE AUTUMN BAKING WORKSHOP: "PUMPKIN SPICE CINNAMON ROLL"',
        'date_str' => '09:30 - 12:00, 27 Tháng 10, 2026',
        'location_name' => 'Bakery Studio & Academy',
        'location_address' => 'Quận 3, TP. Hồ Chí Minh',
        'min_price' => '400.000 đ',
        'banner_img' => '../assets/images/events/banner-baking-pumpkin.jpg',
        'organizer_name' => 'Little Autumn Studio',
        'organizer_sub' => 'Lớp học làm bánh nghệ thuật & trải nghiệm',
        'categories' => [
            ['name' => 'VÉ THAM GIA WORKSHOP', 'price' => '400.000 đ', 'soldout' => false],
        ]
    ],
    20 => [
        'id' => 20,
        'title' => 'The "Traditional Water Puppet Show" - Múa rối nước',
        'date_str' => '18:30 - 19:30, 27 Tháng 10, 2026',
        'location_name' => 'Nhà hát Múa rối nước Rồng Vàng',
        'location_address' => '55B Nguyễn Thị Minh Khai, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh',
        'min_price' => '350.000 đ',
        'banner_img' => '../assets/images/events/banner-mua-roi-nuoc.jpg',
        'organizer_name' => 'Nhà hát Múa rối nước Rồng Vàng',
        'organizer_sub' => 'Đơn vị biểu diễn nghệ thuật múa rối nước truyền thống',
        'categories' => [
            ['name' => 'VÉ TIÊU CHUẨN', 'price' => '350.000 đ', 'soldout' => false],
            ['name' => 'VÉ VIP HÀNG ĐẦU', 'price' => '450.000 đ', 'soldout' => false],
        ]
    ],
    21 => [
        'id' => 21,
        'title' => 'LOALOA SHOW: KHI DI SẢN HÓA VŨ ĐIỆU - Live The Legacy',
        'date_str' => '19:30 - 21:30, 27 Tháng 10, 2026',
        'location_name' => 'Sân khấu Nghệ thuật Truyền thống',
        'location_address' => 'Quận 1, TP. Hồ Chí Minh',
        'min_price' => '1.600.000 đ',
        'banner_img' => '../assets/images/events/banner-loaloa-di-san.jpg',
        'organizer_name' => 'LoaLoa Entertainment',
        'organizer_sub' => 'Đơn vị sản xuất chuỗi show vũ điệu di sản',
        'categories' => [
            ['name' => 'VÉ THƯỜNG', 'price' => '1.600.000 đ', 'soldout' => false],
            ['name' => 'VÉ VIP', 'price' => '2.200.000 đ', 'soldout' => false],
        ]
    ],
    22 => [
        'id' => 22,
        'title' => 'Chào Show - The Sound of Vietnam',
        'date_str' => '19:15 - 21:00, 27 Tháng 10, 2026',
        'location_name' => 'Bảo tàng Lịch sử TP. Hồ Chí Minh',
        'location_address' => '2 Nguyễn Bỉnh Khiêm, Bến Nghé, Quận 1, TP. Hồ Chí Minh',
        'min_price' => '1.300.000 đ',
        'banner_img' => '../assets/images/events/banner-chao-show.jpg',
        'organizer_name' => 'Chào Show Production',
        'organizer_sub' => 'Đêm nhạc trình diễn âm nhạc dân tộc Việt Nam',
        'categories' => [
            ['name' => 'VÉ THAM QUAN & XEM SHOW', 'price' => '1.300.000 đ', 'soldout' => false],
            ['name' => 'VÉ PREMIUM CHÀO SHOW', 'price' => '1.800.000 đ', 'soldout' => false],
        ]
    ],
    23 => [
        'id' => 23,
        'title' => '[GARDEN ART] - ART WORKSHOP "FRUIT MOCHI CHEESECAKE"',
        'date_str' => '14:00 - 17:00, 28 Tháng 10, 2026',
        'location_name' => 'Garden Art Space',
        'location_address' => 'Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh',
        'min_price' => '370.000 đ',
        'banner_img' => '../assets/images/events/banner-mochi-cheesecake.jpg',
        'organizer_name' => 'Garden Art Studio',
        'organizer_sub' => 'Xưởng sáng tạo nghệ thuật & làm bánh',
        'categories' => [
            ['name' => 'VÉ WORKSHOP MOCHI', 'price' => '370.000 đ', 'soldout' => false],
        ]
    ],
    24 => [
        'id' => 24,
        'title' => 'ART WORKSHOP "UJI MATCHA CHEESECAKE TARTE"',
        'date_str' => '09:00 - 12:00, 28 Tháng 10, 2026',
        'location_name' => 'Garden Art Space',
        'location_address' => 'Thảo Điền, TP. Thủ Đức, TP. Hồ Chí Minh',
        'min_price' => '420.000 đ',
        'banner_img' => '../assets/images/events/banner-matcha-tarte.jpg',
        'organizer_name' => 'Garden Art Studio',
        'organizer_sub' => 'Xưởng sáng tạo nghệ thuật & làm bánh',
        'categories' => [
            ['name' => 'VÉ WORKSHOP MATCHA TARTE', 'price' => '420.000 đ', 'soldout' => false],
        ]
    ],
    25 => [
        'id' => 25,
        'title' => 'ART WORKSHOP OIL PASTEL PAINTING "MELTED THOUGHTS"',
        'date_str' => '14:30 - 17:30, 28 Tháng 10, 2026',
        'location_name' => 'Atelier Painting Studio',
        'location_address' => 'Quận 3, TP. Hồ Chí Minh',
        'min_price' => '420.000 đ',
        'banner_img' => '../assets/images/events/banner-oil-pastel.jpg',
        'organizer_name' => 'Atelier Studio',
        'organizer_sub' => 'Lớp học vẽ tranh sáp dầu nghệ thuật',
        'categories' => [
            ['name' => 'VÉ VẼ TRANH (BAO GỒM DỤNG CỤ)', 'price' => '420.000 đ', 'soldout' => false],
        ]
    ],
    26 => [
        'id' => 26,
        'title' => '[DỄ GARDEN] Moss Frame Workshop',
        'date_str' => '09:00 - 12:00, 16 Tháng 09, 2026',
        'location_name' => 'Dễ Garden Studio',
        'location_address' => 'Quận 1, TP. Hồ Chí Minh',
        'min_price' => '450.000 đ',
        'banner_img' => '../assets/images/events/banner-moss-frame.jpg',
        'organizer_name' => 'Dễ Garden',
        'organizer_sub' => 'Xưởng thiết kế & sáng tạo không gian xanh',
        'categories' => [
            ['name' => 'VÉ WORKSHOP KHUNG RÊU', 'price' => '450.000 đ', 'soldout' => false],
        ]
    ],
    27 => [
        'id' => 27,
        'title' => '[LÁ NHÀ GARDEN] Workshop Decor bí ngô Halloween',
        'date_str' => '14:00 - 17:00, 27 Tháng 10, 2026',
        'location_name' => 'Lá Nhà Garden Studio',
        'location_address' => '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh',
        'min_price' => '293.000 đ',
        'banner_img' => '../assets/images/events/banner-decor-bi-ngo.jpg',
        'organizer_name' => 'Lá Nhà Garden',
        'organizer_sub' => 'Nhận tổ chức Workshop thủ công & cây cảnh',
        'categories' => [
            ['name' => 'VÉ DECOR BÍ NGÔ HALLOWEEN', 'price' => '293.000 đ', 'soldout' => false],
        ]
    ],
    28 => [
        'id' => 28,
        'title' => '[LÁ NHÀ GARDEN] Workshop Terrarium Mini',
        'date_str' => '09:30 - 11:30, 27 Tháng 10, 2026',
        'location_name' => 'Lá Nhà Garden Studio',
        'location_address' => '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh',
        'min_price' => '395.000 đ',
        'banner_img' => '../assets/images/events/banner-terrarium-mini.jpg',
        'organizer_name' => 'Lá Nhà Garden',
        'organizer_sub' => 'Nhận tổ chức Workshop thủ công & cây cảnh',
        'categories' => [
            ['name' => 'VÉ WORKSHOP TERRARIUM MINI', 'price' => '395.000 đ', 'soldout' => false],
        ]
    ],
    29 => [
        'id' => 29,
        'title' => '[LÁ NHÀ GARDEN] Tiểu cảnh Sen đá',
        'date_str' => '14:00 - 16:30, 27 Tháng 10, 2026',
        'location_name' => 'Lá Nhà Garden Studio',
        'location_address' => '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh',
        'min_price' => '385.000 đ',
        'banner_img' => '../assets/images/events/banner-tieu-canh-sen-da.jpg',
        'organizer_name' => 'Lá Nhà Garden',
        'organizer_sub' => 'Nhận tổ chức Workshop thủ công & cây cảnh',
        'categories' => [
            ['name' => 'VÉ TRỒNG TIỂU CẢNH SEN ĐÁ', 'price' => '385.000 đ', 'soldout' => false],
        ]
    ],
    30 => [
        'id' => 30,
        'title' => '[LÁ NHÀ GARDEN] Workshop Nương gỗ',
        'date_str' => '09:00 - 12:00, 27 Tháng 10, 2026',
        'location_name' => 'Lá Nhà Garden Studio',
        'location_address' => '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh',
        'min_price' => '465.000 đ',
        'banner_img' => '../assets/images/events/banner-nuong-go.jpg',
        'organizer_name' => 'Lá Nhà Garden',
        'organizer_sub' => 'Nhận tổ chức Workshop thủ công & cây cảnh',
        'categories' => [
            ['name' => 'VÉ WORKSHOP NƯƠNG GỖ', 'price' => '465.000 đ', 'soldout' => false],
        ]
    ],
    31 => [
        'id' => 31,
        'title' => '[LÁ NHÀ GARDEN] Workshop Tranh thực vật ép Botanical art',
        'date_str' => '14:00 - 16:30, 27 Tháng 10, 2026',
        'location_name' => 'Lá Nhà Garden Studio',
        'location_address' => '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh',
        'min_price' => '305.000 đ',
        'banner_img' => '../assets/images/events/banner-botanical-art.jpg',
        'organizer_name' => 'Lá Nhà Garden',
        'organizer_sub' => 'Nhận tổ chức Workshop thủ công & cây cảnh',
        'categories' => [
            ['name' => 'VÉ LÀM TRANH ÉP THỰC VẬT', 'price' => '305.000 đ', 'soldout' => false],
        ]
    ],
    32 => [
        'id' => 32,
        'title' => '[LÁ NHÀ GARDEN] Workshop Tranh rêu độc bản',
        'date_str' => '09:30 - 12:00, 27 Tháng 10, 2026',
        'location_name' => 'Lá Nhà Garden Studio',
        'location_address' => '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh',
        'min_price' => '370.000 đ',
        'banner_img' => '../assets/images/events/banner-tranh-reu.jpg',
        'organizer_name' => 'Lá Nhà Garden',
        'organizer_sub' => 'Nhận tổ chức Workshop thủ công & cây cảnh',
        'categories' => [
            ['name' => 'VÉ LÀM TRANH RÊU ĐỘC BẢN', 'price' => '370.000 đ', 'soldout' => false],
        ]
    ],
    33 => [
        'id' => 33,
        'title' => '[LÁ NHÀ GARDEN] Workshop Túi thơm thảo mộc healing',
        'date_str' => '15:00 - 17:00, 27 Tháng 10, 2026',
        'location_name' => 'Lá Nhà Garden Studio',
        'location_address' => '355/4/12 Đường Bạch Đằng, Phường 14, Quận Bình Thạnh, TP. Hồ Chí Minh',
        'min_price' => '215.000 đ',
        'banner_img' => '../assets/images/events/banner-tui-thom-thao-moc.jpg',
        'organizer_name' => 'Lá Nhà Garden',
        'organizer_sub' => 'Nhận tổ chức Workshop thủ công & cây cảnh',
        'categories' => [
            ['name' => 'VÉ TÚI THƠM THẢO MỘC', 'price' => '215.000 đ', 'soldout' => false],
        ]
    ],34 => [
        'id' => 34,
        'title' => 'Trải Nghiệm Bay Dù Lượn Mù Cang Chải',
        'date_str' => '07:30 - 17:00, 01 Tháng 02, 2026',
        'location_name' => 'Đèo Khau Phạ',
        'location_address' => 'Đèo Khau Phạ, Huyện Mù Cang Chải, Tỉnh Yên Bái',
        'min_price' => '2.190.000 đ',
        'banner_img' => '../assets/images/events/banner-du-luon-mu-cang-chai.jpg',
        'organizer_name' => 'VietWings Paragliding',
        'organizer_sub' => 'Đơn vị tổ chức dù lượn hàng đầu Việt Nam',
        'categories' => [
            ['name' => 'VÉ BAY ĐÔI CÙNG HLV', 'price' => '2.190.000 đ', 'soldout' => false],
            ['name' => 'VÉ BAY ĐÔI + GÓI CHỤP MEDIA GOPRO', 'price' => '2.690.000 đ', 'soldout' => false],
        ]
    ],
    35 => [
        'id' => 35,
        'title' => 'KHU VUI CHƠI NHÀ CHEN - ƯỚC MƠ XANH',
        'date_str' => '08:00 - 21:00 hàng ngày, 28 Tháng 07, 2026',
        'location_name' => 'Khu Vui Chơi Nhà Chen',
        'location_address' => 'Quận 7, TP. Hồ Chí Minh',
        'min_price' => '60.000 đ',
        'banner_img' => '../assets/images/events/banner-nha-chen.jpg',
        'organizer_name' => 'Nhà Chen Kids',
        'organizer_sub' => 'Khu vui chơi trải nghiệm sáng tạo dành cho gia đình',
        'categories' => [
            ['name' => 'VÉ TRẺ EM (NGÀY THƯỜNG)', 'price' => '60.000 đ', 'soldout' => false],
            ['name' => 'VÉ TRẺ EM + NGƯỜI LỚN (CUỐI TUẦN)', 'price' => '120.000 đ', 'soldout' => false],
        ]
    ],
    36 => [
        'id' => 36,
        'title' => 'DU LỊCH VĂN HÓA SUỐI TIÊN',
        'date_str' => '07:30 - 17:30 hàng ngày, 01 Tháng 10, 2026',
        'location_name' => 'Khu Du Lịch Văn Hóa Suối Tiên',
        'location_address' => '120 Xa lộ Hà Nội, Phường Tân Phú, TP. Thủ Đức, TP. Hồ Chí Minh',
        'min_price' => '60.000 đ',
        'banner_img' => '../assets/images/events/banner-suoi-tien.jpg',
        'organizer_name' => 'Suối Tiên Theme Park',
        'organizer_sub' => 'Tập đoàn Du lịch Văn hóa Suối Tiên',
        'categories' => [
            ['name' => 'VÉ VÀO CỔNG TRẺ EM', 'price' => '60.000 đ', 'soldout' => false],
            ['name' => 'VÉ VÀO CỔNG NGƯỜI LỚN', 'price' => '150.000 đ', 'soldout' => false],
            ['name' => 'VÉ TRỌN GÓI BẮN SÚNG & BIỂN TIÊN ĐỒNG', 'price' => '350.000 đ', 'soldout' => false],
        ]
    ],
    37 => [
        'id' => 37,
        'title' => 'Bảo Tàng Phở - Bảo Tàng Ẩm Thực Đầu Tiên tại Việt Nam',
        'date_str' => '08:30 - 18:00 hàng ngày, 01 Tháng 10, 2026',
        'location_name' => 'Bảo Tàng Phở Việt Nam',
        'location_address' => 'Thành phố Nam Định, Tỉnh Nam Định',
        'min_price' => '200.000 đ',
        'banner_img' => '../assets/images/events/banner-bao-tang-pho.jpg',
        'organizer_name' => 'Hiệp hội Văn hóa Ẩm thực Việt Nam',
        'organizer_sub' => 'Đơn vị bảo tồn & phát triển văn hóa ẩm thực',
        'categories' => [
            ['name' => 'VÉ THAM QUAN & THƯỞNG THỨC PHỞ', 'price' => '200.000 đ', 'soldout' => false],
            ['name' => 'VÉ TRẢI NGHIỆM TRÁNG BÁNH PHỞ THỦ CÔNG', 'price' => '350.000 đ', 'soldout' => false],
        ]
    ],
    38 => [
        'id' => 38,
        'title' => 'PHẬT BÁO NGHIỆM TRẤN - TRIỂN LÃM DI SẢN VĂN HOÁ PHẬT GIÁO ĐỘC BẢN',
        'date_str' => '09:00 - 21:00, 27 Tháng 10, 2026',
        'location_name' => 'Trung tâm Triển lãm Nghệ thuật',
        'location_address' => 'Quận 1, TP. Hồ Chí Minh',
        'min_price' => '150.000 đ',
        'banner_img' => '../assets/images/events/banner-phat-bao-nghiem-tran.jpg',
        'organizer_name' => 'Phật Báo Nghiệm Trấn',
        'organizer_sub' => 'Ban tổ chức triển lãm di sản văn hóa Phật giáo',
        'categories' => [
            ['name' => 'VÉ THAM QUAN TRIỂN LÃM', 'price' => '150.000 đ', 'soldout' => false],
        ]
    ],
    39 => [
        'id' => 39,
        'title' => 'City Sightseeing SaiGon: Xe Buýt 2 Tầng',
        'date_str' => '08:00 - 22:30 hàng ngày, 27 Tháng 10, 2026',
        'location_name' => 'Trạm Nhà Hát Thành Phố',
        'location_address' => 'Công trường Lam Sơn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh',
        'min_price' => '105.000 đ',
        'banner_img' => '../assets/images/events/banner-bus-2-tang.jpg',
        'organizer_name' => 'City Sightseeing Vietnam',
        'organizer_sub' => 'Thương hiệu xe buýt ngắm cảnh thành phố 2 tầng',
        'categories' => [
            ['name' => 'VÉ XE BUÝT 1 VÒNG (CHUYẾN NGÀY)', 'price' => '105.000 đ', 'soldout' => false],
            ['name' => 'VÉ XE BUÝT 1 VÒNG (CHUYẾN ĐÊM)', 'price' => '145.000 đ', 'soldout' => false],
            ['name' => 'VÉ HOP-ON HOP-OFF 24 GIỜ', 'price' => '475.000 đ', 'soldout' => false],
        ]
    ],
    40 => [
        'id' => 40,
        'title' => 'TOUR ĐÊM VĂN MIẾU - VAN MIEU NIGHT TOUR',
        'date_str' => '19:00 - 22:00 (Thứ 4, 6, 7 & CN), 28 Tháng 10, 2026',
        'location_name' => 'Di tích Quốc gia đặc biệt Văn Miếu - Quốc Tử Giám',
        'location_address' => '58 Quốc Tử Giám, Văn Miếu, Đống Đa, Hà Nội',
        'min_price' => '0 đ',
        'banner_img' => '../assets/images/events/banner-tour-dem-van-mieu.jpg',
        'organizer_name' => 'Trung tâm Hoạt động VHKH Văn Miếu - Quốc Tử Giám',
        'organizer_sub' => 'Đơn vị quản lý & tổ chức trải nghiệm đêm Đạo Học',
        'categories' => [
            ['name' => 'VÉ ĐĂNG KÝ TRẢI NGHIỆM MẪU', 'price' => '0 đ', 'soldout' => false],
            ['name' => 'VÉ TOUR ĐÊM CHÍNH THỨC', 'price' => '199.000 đ', 'soldout' => false],
        ]
    ],
    41 => [
        'id' => 41,
        'title' => 'Vé Trải Nghiệm GenZania 2 tiếng',
        'date_str' => '10:00 - 21:00, 30 Tháng 10, 2026',
        'location_name' => 'Tổ hợp Trải nghiệm GenZania',
        'location_address' => 'Quận 2, TP. Thủ Đức, TP. Hồ Chí Minh',
        'min_price' => '190.000 đ',
        'banner_img' => '../assets/images/events/banner-genzania.jpg',
        'organizer_name' => 'GenZania Entertainment',
        'organizer_sub' => 'Thế giới trò chơi & giải trí tương tác cho giới trẻ',
        'categories' => [
            ['name' => 'VÉ TRẢI NGHIỆM 2 TIẾNG', 'price' => '190.000 đ', 'soldout' => false],
            ['name' => 'VÉ KHÔNG GIỚI HẠN THỜI GIAN', 'price' => '290.000 đ', 'soldout' => false],
        ]
    ],42 => [
        'id' => 42,
        'title' => 'KỊCH ĐOÀN THÀNH LỘC: CÔ GIÁO DUYÊN',
        'date_str' => '19:30 - 22:00, 28 Tháng 10, 2026',
        'location_name' => 'Sân khấu kịch Thiên Đăng',
        'location_address' => '62 Trần Hưng Đạo, Phường Phạm Ngũ Lão, Quận 1, TP. Hồ Chí Minh',
        'min_price' => '300.000 đ',
        'banner_img' => '../assets/images/events/banner-co-giao-duyen.jpg',
        'organizer_name' => 'Sân khấu kịch Thiên Đăng',
        'organizer_sub' => 'Kịch đoàn nghệ sĩ Ưu tú Thành Lộc',
        'categories' => [
            ['name' => 'VÉ HẠNG LẦU (STANDARD)', 'price' => '300.000 đ', 'soldout' => false],
            ['name' => 'VÉ HẠNG TRỆT (VIP)', 'price' => '500.000 đ', 'soldout' => false],
        ]
    ],
    43 => [
        'id' => 43,
        'title' => 'Hà Nội - Eifman Ballet Of ST. Petersubrg World Tour 2026',
        'date_str' => '19:30 - 21:30, 28 Tháng 10, 2026',
        'location_name' => 'Nhà hát Lớn Hà Nội',
        'location_address' => '1 Tràng Tiền, Phường Tràng Tiền, Quận Hoàn Kiếm, Hà Nội',
        'min_price' => '500.000 đ',
        'banner_img' => '../assets/images/events/banner-eifman-ballet.jpg',
        'organizer_name' => 'SPO & Eifman Ballet',
        'organizer_sub' => 'Đoàn múa Ballet Hàn lâm Saint Petersburg',
        'categories' => [
            ['name' => 'VÉ HẠNG C (STANDARD)', 'price' => '500.000 đ', 'soldout' => false],
            ['name' => 'VÉ HẠNG B (VIP)', 'price' => '1.200.000 đ', 'soldout' => false],
            ['name' => 'VÉ HẠNG A (SVIP)', 'price' => '2.500.000 đ', 'soldout' => false],
        ]
    ],
    44 => [
        'id' => 44,
        'title' => 'KỊCH ĐOÀN THÀNH LỘC: NGŨ QUÝ TƯƠNG PHÙNG',
        'date_str' => '19:30 - 22:00, 29 Tháng 10, 2026',
        'location_name' => 'Sân khấu kịch Thiên Đăng',
        'location_address' => '62 Trần Hưng Đạo, Phường Phạm Ngũ Lão, Quận 1, TP. Hồ Chí Minh',
        'min_price' => '300.000 đ',
        'banner_img' => '../assets/images/events/banner-ngu-quy-tuong-phung.jpg',
        'organizer_name' => 'Sân khấu kịch Thiên Đăng',
        'organizer_sub' => 'Kịch đoàn nghệ sĩ Ưu tú Thành Lộc',
        'categories' => [
            ['name' => 'VÉ HẠNG LẦU (STANDARD)', 'price' => '300.000 đ', 'soldout' => false],
            ['name' => 'VÉ HẠNG TRỆT (VIP)', 'price' => '500.000 đ', 'soldout' => false],
        ]
    ],
    45 => [
        'id' => 45,
        'title' => 'Nhà Hát Kịch IDECAF: Hồn Ai Nấy Giữ',
        'date_str' => '19:30 - 22:00, 29 Tháng 10, 2026',
        'location_name' => 'Nhà hát kịch IDECAF',
        'location_address' => '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh',
        'min_price' => '300.000 đ',
        'banner_img' => '../assets/images/events/banner-hon-ai-nay-giu.jpg',
        'organizer_name' => 'Nhà Hát Kịch IDECAF',
        'organizer_sub' => 'Sân khấu kịch IDECAF TP. Hồ Chí Minh',
        'categories' => [
            ['name' => 'VÉ HẠNG B (STANDARD)', 'price' => '300.000 đ', 'soldout' => false],
            ['name' => 'VÉ HẠNG A (VIP)', 'price' => '450.000 đ', 'soldout' => false],
        ]
    ],
    46 => [
        'id' => 46,
        'title' => 'Show Thực Cảnh Anh Hùng Cờ Lau - Đinh Bộ Lĩnh',
        'date_str' => '19:30 - 21:00, 29 Tháng 10, 2026',
        'location_name' => 'Sân khấu Thực cảnh Văn hóa',
        'location_address' => 'Hà Nội',
        'min_price' => '175.000 đ',
        'banner_img' => '../assets/images/events/banner-anh-hung-co-lau.jpg',
        'organizer_name' => 'Ban tổ chức Show Thực cảnh',
        'organizer_sub' => 'Đơn vị sản xuất chương trình sân khấu lịch sử',
        'categories' => [
            ['name' => 'VÉ THƯỜNG (STANDARD)', 'price' => '175.000 đ', 'soldout' => false],
            ['name' => 'VÉ VIP khán đài', 'price' => '350.000 đ', 'soldout' => false],
        ]
    ],
    47 => [
        'id' => 47,
        'title' => 'CADAO Collective - New Taste Of Heritage',
        'date_str' => '18:30 - 21:00, 30 Tháng 10, 2026',
        'location_name' => 'CADAO Art Stage',
        'location_address' => 'Quận 1, TP. Hồ Chí Minh',
        'min_price' => '500.000 đ',
        'banner_img' => '../assets/images/events/banner-cadao-collective.jpg',
        'organizer_name' => 'CADAO Collective',
        'organizer_sub' => 'Chương trình biểu diễn nghệ thuật di sản âm nhạc',
        'categories' => [
            ['name' => 'VÉ THAM GIA SHOW (BAO GỒM DRINK)', 'price' => '500.000 đ', 'soldout' => false],
        ]
    ],
    48 => [
        'id' => 48,
        'title' => 'Nhà Hát Kịch IDECAF: Mẹ Tôi Là Đàn Ông',
        'date_str' => '19:30 - 22:00, 30 Tháng 10, 2026',
        'location_name' => 'Nhà hát kịch IDECAF',
        'location_address' => '28 Lê Thánh Tôn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh',
        'min_price' => '300.000 đ',
        'banner_img' => '../assets/images/events/banner-me-toi-la-dan-ong.jpg',
        'organizer_name' => 'Nhà Hát Kịch IDECAF',
        'organizer_sub' => 'Sân khấu kịch IDECAF TP. Hồ Chí Minh',
        'categories' => [
            ['name' => 'VÉ HẠNG B', 'price' => '300.000 đ', 'soldout' => false],
            ['name' => 'VÉ HẠNG A (VIP)', 'price' => '450.000 đ', 'soldout' => false],
        ]
    ],
    49 => [
        'id' => 49,
        'title' => 'BÓNG MA RẠP HÁT LỆ HOA - SÂN KHẨU MỚI',
        'date_str' => '19:30 - 22:00, 30 Tháng 10, 2026',
        'location_name' => 'Sân khấu kịch Lệ Hoa (Nhà hát Trần Hữu Trang)',
        'location_address' => '136 Trần Hưng Đạo, Quận 1, TP. Hồ Chí Minh',
        'min_price' => '250.000 đ',
        'banner_img' => '../assets/images/events/banner-bong-ma-le-hoa.jpg',
        'organizer_name' => 'Sân khấu kịch Lệ Hoa',
        'organizer_sub' => 'Sân khấu kịch nghệ thuật mới',
        'categories' => [
            ['name' => 'VÉ HẠNG LẦU', 'price' => '250.000 đ', 'soldout' => false],
            ['name' => 'VÉ HẠNG TRỆT (VIP)', 'price' => '400.000 đ', 'soldout' => false],
        ]
    ],
    50 => [
        'id' => 50,
        'title' => 'Sân Khấu Thế Giới Trẻ: Mật Mã Cầu Cơ',
        'date_str' => '19:30 - 22:00, 30 Tháng 10, 2026',
        'location_name' => 'Sân khấu kịch Thế Giới Trẻ',
        'location_address' => '125 Cống Quỳnh, Phường Nguyễn Cư Trinh, Quận 1, TP. Hồ Chí Minh',
        'min_price' => '330.000 đ',
        'banner_img' => '../assets/images/events/banner-mat-ma-cau-co.jpg',
        'organizer_name' => 'Sân khấu Thế Giới Trẻ',
        'organizer_sub' => 'Đơn vị tổ chức các vở kịch tâm lý xã hội',
        'categories' => [
            ['name' => 'VÉ KHU B', 'price' => '330.000 đ', 'soldout' => false],
            ['name' => 'VÉ KHU A (VIP)', 'price' => '480.000 đ', 'soldout' => false],
        ]
    ],
    51 => [
        'id' => 51,
        'title' => 'Mystic Night Show - Magic & Comedy in Saigon',
        'date_str' => '20:00 - 22:00, 30 Tháng 10, 2026',
        'location_name' => 'Mystic Night Lounge',
        'location_address' => 'Quận 1, TP. Hồ Chí Minh',
        'min_price' => '850.000 đ',
        'banner_img' => '../assets/images/events/banner-mystic-night-show.jpg',
        'organizer_name' => 'Mystic Entertainment',
        'organizer_sub' => 'Show diễn ảo thuật kết hợp hài kịch quốc tế',
        'categories' => [
            ['name' => 'VÉ THƯỜNG + 1 COCKTAIL', 'price' => '850.000 đ', 'soldout' => false],
            ['name' => 'VÉ FRONT ROW VIP', 'price' => '1.300.000 đ', 'soldout' => false],
        ]
    ],
    52 => [
        'id' => 52,
        'title' => 'Cuộc Phiêu Lưu Kỳ Thú',
        'date_str' => '09:30 - 11:30, 31 Tháng 10, 2026',
        'location_name' => 'Nhà hát kịch Thiếu nhi',
        'location_address' => 'Quận 3, TP. Hồ Chí Minh',
        'min_price' => '120.000 đ',
        'banner_img' => '../assets/images/events/banner-cuoc-phieu-luu-ky-thu.jpg',
        'organizer_name' => 'Sân khấu kịch Tuổi Thơ',
        'organizer_sub' => 'Đơn vị sản xuất vở kịch thiếu nhi',
        'categories' => [
            ['name' => 'VÉ TRẺ EM', 'price' => '120.000 đ', 'soldout' => false],
            ['name' => 'VÉ PHỤ HUYNH', 'price' => '180.000 đ', 'soldout' => false],
        ]
    ]
];

// 3. Lấy dữ liệu sự kiện theo ID (nếu không có thì mặc định lấy sự kiện 1)
$event_detail = isset($events_database[$event_id]) ? $events_database[$event_id] : $events_database[1];

include '../templates/header.php';
?>

<main class="container" style="margin-top: 25px;">
    <!-- 1. KHUNG THẺ VÉ THÔNG TIN CHÍNH -->
    <div class="ticket-stub-card">
        <div class="stub-left">
            <h1 class="stub-title"><?php echo htmlspecialchars($event_detail['title']); ?></h1>
            
            <div class="stub-info">
                <i class="fa-regular fa-calendar-check"></i>
                <span><?php echo $event_detail['date_str']; ?></span>
            </div>
            
            <div class="stub-info">
                <i class="fa-solid fa-location-dot"></i>
                <div>
                    <strong><?php echo htmlspecialchars($event_detail['location_name']); ?></strong>
                    <p style="font-size: 12px; color: #a0a3bd; margin-top: 2px;"><?php echo htmlspecialchars($event_detail['location_address']); ?></p>
                </div>
            </div>

            <div class="stub-divider"></div>

            <div class="stub-price-row">
                <div class="stub-price-link">Giá từ <span><?php echo $event_detail['min_price']; ?></span></div>
                
                <!-- Form gửi thông tin mua vé -->
                <form action="buy-ticket.php" method="POST">
                    <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($event_detail['title']); ?>">
                    <input type="hidden" name="category" value="<?php echo $event_detail['categories'][0]['name']; ?>">
                    <input type="hidden" name="price" value="<?php echo $event_detail['categories'][0]['price']; ?>">
                    <input type="hidden" name="location" value="<?php echo htmlspecialchars($event_detail['location_name']); ?>">
                    <input type="hidden" name="date" value="<?php echo $event_detail['date_str']; ?>">
                    <input type="hidden" name="image" value="<?php echo $event_detail['banner_img']; ?>">
                    <button type="submit" class="btn-buy-primary" style="border:none; cursor:pointer;">Mua vé ngay</button>
                </form>
            </div>
        </div>

        <div class="notch notch-top"></div>
        <div class="notch notch-bottom"></div>

        <div class="stub-right">
            <img src="<?php echo $event_detail['banner_img']; ?>" alt="<?php echo htmlspecialchars($event_detail['title']); ?>" onerror="this.src='https://via.placeholder.com/600x340/1b1537/fff?text=Ticketbox';">
        </div>
    </div>

    <!-- 2. DANH SÁCH HẠNG VÉ ĐỘNG -->
    <div class="detail-block" style="margin-top: 35px;">
        <h3 style="margin-bottom: 20px;">Lịch diễn & Hạng vé</h3>
        <div class="schedule-accordion">
            <?php foreach ($event_detail['categories'] as $cat): ?>
                <div class="cat-item">
                    <span><i class="fa-solid fa-chevron-right"></i> <?php echo $cat['name']; ?></span>
                    <div style="display:flex; align-items:center; gap:15px;">
                        <strong class="price-green"><?php echo $cat['price']; ?></strong>
                        
                        <?php if ($cat['soldout']): ?>
                            <span class="badge-soldout">Hết vé</span>
                        <?php else: ?>
                            <form action="buy-ticket.php" method="POST">
                                <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($event_detail['title']); ?>">
                                <input type="hidden" name="category" value="<?php echo $cat['name']; ?>">
                                <input type="hidden" name="price" value="<?php echo $cat['price']; ?>">
                                <input type="hidden" name="location" value="<?php echo htmlspecialchars($event_detail['location_name']); ?>">
                                <input type="hidden" name="date" value="<?php echo $event_detail['date_str']; ?>">
                                <input type="hidden" name="image" value="<?php echo $event_detail['banner_img']; ?>">
                                <button type="submit" class="btn-buy-sm">Mua vé</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 3. BẢNG BAN TỔ CHỨC ĐỘNG -->
    <div class="detail-block" style="margin-top: 30px;">
        <h3 style="color: var(--primary-color); margin-bottom: 15px;">Ban tổ chức</h3>
        <div class="organizer-card">
            <div class="org-avatar">
                <span style="font-size: 16px; font-weight: 900; color: #ff3366; text-align:center;">
                    <?php echo strtoupper(substr($event_detail['organizer_name'], 0, 3)); ?>
                </span>
            </div>
            <div>
                <h4 style="font-size: 18px; margin-bottom: 4px;"><?php echo htmlspecialchars($event_detail['organizer_name']); ?></h4>
                <p style="color: var(--text-muted); font-size: 14px;"><?php echo htmlspecialchars($event_detail['organizer_sub']); ?></p>
            </div>
        </div>
    </div>
</main>

<?php include '../templates/footer.php'; ?>