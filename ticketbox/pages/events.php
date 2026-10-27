<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../templates/header.php';

// 1. Nhận tham số lọc từ URL (GET Request)
$selected_location = $_GET['location'] ?? '';
$selected_sort = $_GET['sort'] ?? '';
$selected_cat = $_GET['cat'] ?? '';

// 2. Danh sách tất cả sự kiện (Có đầy đủ ID, mã địa điểm và giá gốc để lọc)
$all_events = [
    [
        'id' => 1,
        'title' => 'Live Concert SKYNOTE - Quốc Thiên',
        'price' => 'Từ 880.000 đ',
        'raw_price' => 880000,
        'date' => '29.11.2026',
        'location' => 'Hà Nội',
        'location_code' => 'hanoi',
        'category' => 'nhac-song',
        'image' => '../assets/images/events/banner-skynote.jpg'
    ],
    [
        'id' => 2,
        'title' => 'Những Thành Phố Mộng Mơ Year End',
        'price' => 'Từ 499.000 đ',
        'raw_price' => 499000,
        'date' => '12.12.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'nhac-song',
        'image' => '../assets/images/events/banner-ntpmm.jpg'
    ],
    [
        'id' => 3,
        'title' => 'Mắt Nhắm Mắt Mở',
        'price' => 'Từ 1.600.000 đ',
        'raw_price' => 1600000,
        'date' => '01.11.2026',
        'location' => 'Hà Nội',
        'location_code' => 'hanoi',
        'category' => 'nhac-song',
        'image' => '../assets/images/events/banner-mnmm.jpg'
    ],
    [
        'id' => 4,
        'title' => 'Tinh Hà "Say Hi" Concert - Đêm 4',
        'price' => 'Từ 800.000 đ',
        'raw_price' => 800000,
        'date' => '15.11.2026',
        'location' => 'Trung tâm triển lãm Việt Nam (VEC), Hà Nội',
        'location_code' => 'hanoi',
        'category' => 'nhac-song',
        'image' => '../assets/images/events/banner-tinh-ha.jpg'
    ],
    [
        'id' => 5,
        'title' => 'The Starry - S\'NOW',
        'price' => 'Từ 800.000 đ',
        'raw_price' => 800000,
        'date' => '19.12.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'nhac-song',
        'image' => '../assets/images/events/banner-snow.jpg'
    ],
    [
        'id' => 6,
        'title' => 'GAI MEET - NHÀ RỰC RỠ',
        'price' => 'Từ 1.500.000 đ',
        'raw_price' => 1500000,
        'date' => '18.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'nhac-song',
        'image' => '../assets/images/events/banner-nha-ruc-ro.jpg'
    ],
    [
        'id' => 7,
        'title' => '(S)TRONG \'DỊ KIỀU\' SHOW 2026: ĐẾN GIÂY CUỐI CÙNG',
        'price' => 'Từ 1.450.000 đ',
        'raw_price' => 1450000,
        'date' => '07.11.2026',
        'location' => 'Hà Nội',
        'location_code' => 'hanoi',
        'category' => 'nhac-song',
        'image' => '../assets/images/events/banner-strong.jpg'
    ],
    [
        'id' => 8,
        'title' => 'GAI MEET - DỰ ÁN NHÂN TỐ X',
        'price' => 'Từ 1.500.000 đ',
        'raw_price' => 1500000,
        'date' => '01.11.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'nhac-song',
        'image' => '../assets/images/events/banner-nhan-to-x.jpg'
    ],
    [
        'id' => 9,
        'title' => '[CONCERT] ANH TRAI VƯỢT NGÀN CHÔNG GAI 2026 - DAY 1 & DAY 2',
        'price' => 'Từ 1.000.000 đ',
        'raw_price' => 1000000,
        'date' => '18.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'nhac-song',
        'image' => '../assets/images/events/banner-anh-trai.jpg'
    ],
    [
        'id' => 10,
        'title' => 'HIỆN DIỆN - UPRIZE CONCERT',
        'price' => 'Từ 800.000 đ',
        'raw_price' => 800000,
        'date' => '12.12.2026',
        'location' => 'Hà Nội',
        'location_code' => 'hanoi',
        'category' => 'nhac-song',
        'image' => '../assets/images/events/banner-uprize.jpg'
    ],
    [
        'id' => 11,
        'title' => 'Trải nghiệm bay dù lượn tại Sapa',
        'price' => 'Từ 2.190.000 đ',
        'raw_price' => 2190000,
        'date' => '01.08.2026',
        'location' => 'Sapa, Lào Cai',
        'location_code' => 'hanoi',
        'category' => 'the-thao',
        'image' => '../assets/images/events/banner-du-luon-sapa.jpg'
    ],
    [
        'id' => 12,
        'title' => 'SAIGON PRO-AM BASKETBALL CUP 2026 OFFICIAL MERCHANDISE',
        'price' => 'Từ 25.000 đ',
        'raw_price' => 25000,
        'date' => '30.09.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'the-thao',
        'image' => '../assets/images/events/banner-saigon-basketball.jpg'
    ],
    [
        'id' => 13,
        'title' => 'TRẢI NGHIỆM ĐƯỜNG ĐUA GO - KART ĐA TẦNG TRONG NHÀ ĐẦU TIÊN TẠI VIỆT NAM',
        'price' => 'Từ 0 đ',
        'raw_price' => 0,
        'date' => '19.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'the-thao',
        'image' => '../assets/images/events/banner-go-kart.jpg'
    ],
    [
        'id' => 14,
        'title' => 'Queens Of Glory - Giải Thể Thao Điện Tử Vinh Quang Nữ Hoàng',
        'price' => 'Từ 199.000 đ',
        'raw_price' => 199000,
        'date' => '30.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'the-thao',
        'image' => '../assets/images/events/banner-queens-glory.jpg'
    ],
    [
        'id' => 15,
        'title' => 'LCP 2026 Promotion Series',
        'price' => 'Từ 199.000 đ',
        'raw_price' => 199000,
        'date' => '10.11.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'the-thao',
        'image' => '../assets/images/events/banner-lcp-2026.jpg'
    ],
    [
        'id' => 16,
        'title' => 'Lion Championship 37 - 2026',
        'price' => 'Từ 350.000 đ',
        'raw_price' => 350000,
        'date' => '21.11.2026',
        'location' => 'Hà Nội',
        'location_code' => 'hanoi',
        'category' => 'the-thao',
        'image' => '../assets/images/events/banner-lion-championship.jpg'
    ],
    [
        'id' => 17,
        'title' => 'VIEWING PARTY CHUNG KẾT TỔNG GIẢI ĐẤU VALORANT CHAMPIONS 2026',
        'price' => 'Từ 259.000 đ',
        'raw_price' => 259000,
        'date' => '18.10.2026',
        'location' => 'Nhà thi đấu Tây Hồ, Hà Nội',
        'location_code' => 'hanoi',
        'category' => 'the-thao',
        'image' => '../assets/images/events/banner-valorant-viewing-party.jpg'
    ],
    [
        'id' => 18,
        'title' => 'LOALOA SHOW: VIỆT NAM BỪNG SÁNG',
        'price' => 'Từ 1.600.000 đ',
        'raw_price' => 1600000,
        'date' => '27.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-loaloa-bung-sang.jpg'
    ],
    [
        'id' => 19,
        'title' => 'A LITTLE AUTUMN BAKING WORKSHOP: "PUMPKIN SPICE CINNAMON ROLL"',
        'price' => 'Từ 400.000 đ',
        'raw_price' => 400000,
        'date' => '27.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-baking-pumpkin.jpg'
    ],
    [
        'id' => 20,
        'title' => 'The "Traditional Water Puppet Show" - Múa rối nước',
        'price' => 'Từ 350.000 đ',
        'raw_price' => 350000,
        'date' => '27.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-mua-roi-nuoc.jpg'
    ],
    [
        'id' => 21,
        'title' => 'LOALOA SHOW: KHI DI SẢN HÓA VŨ ĐIỆU - Live The Legacy',
        'price' => 'Từ 1.600.000 đ',
        'raw_price' => 1600000,
        'date' => '27.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-loaloa-di-san.jpg'
    ],
    [
        'id' => 22,
        'title' => 'Chào Show - The Sound of Vietnam',
        'price' => 'Từ 1.300.000 đ',
        'raw_price' => 1300000,
        'date' => '27.10.2026',
        'location' => 'Bảo tàng Lịch sử TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-chao-show.jpg'
    ],
    [
        'id' => 23,
        'title' => '[GARDEN ART] - ART WORKSHOP "FRUIT MOCHI CHEESECAKE"',
        'price' => 'Từ 370.000 đ',
        'raw_price' => 370000,
        'date' => '28.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-mochi-cheesecake.jpg'
    ],
    [
        'id' => 24,
        'title' => 'ART WORKSHOP "UJI MATCHA CHEESECAKE TARTE"',
        'price' => 'Từ 420.000 đ',
        'raw_price' => 420000,
        'date' => '28.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-matcha-tarte.jpg'
    ],
    [
        'id' => 25,
        'title' => 'ART WORKSHOP OIL PASTEL PAINTING "MELTED THOUGHTS"',
        'price' => 'Từ 420.000 đ',
        'raw_price' => 420000,
        'date' => '28.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-oil-pastel.jpg'
    ],[
        'id' => 26,
        'title' => '[DỄ GARDEN] Moss Frame Workshop',
        'price' => 'Từ 450.000 đ',
        'raw_price' => 450000,
        'date' => '16.09.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-moss-frame.jpg'
    ],
    [
        'id' => 27,
        'title' => '[LÁ NHÀ GARDEN] Workshop Decor bí ngô Halloween',
        'price' => 'Từ 293.000 đ',
        'raw_price' => 293000,
        'date' => '27.10.2026',
        'location' => 'Bình Thạnh, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-decor-bi-ngo.jpg'
    ],
    [
        'id' => 28,
        'title' => '[LÁ NHÀ GARDEN] Workshop Terrarium Mini',
        'price' => 'Từ 395.000 đ',
        'raw_price' => 395000,
        'date' => '27.10.2026',
        'location' => 'Bình Thạnh, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-terrarium-mini.jpg'
    ],
    [
        'id' => 29,
        'title' => '[LÁ NHÀ GARDEN] Tiểu cảnh Sen đá',
        'price' => 'Từ 385.000 đ',
        'raw_price' => 385000,
        'date' => '27.10.2026',
        'location' => 'Bình Thạnh, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-tieu-canh-sen-da.jpg'
    ],
    [
        'id' => 30,
        'title' => '[LÁ NHÀ GARDEN] Workshop Nương gỗ',
        'price' => 'Từ 465.000 đ',
        'raw_price' => 465000,
        'date' => '27.10.2026',
        'location' => 'Bình Thạnh, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-nuong-go.jpg'
    ],
    [
        'id' => 31,
        'title' => '[LÁ NHÀ GARDEN] Workshop Tranh thực vật ép Botanical art',
        'price' => 'Từ 305.000 đ',
        'raw_price' => 305000,
        'date' => '27.10.2026',
        'location' => 'Bình Thạnh, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-botanical-art.jpg'
    ],
    [
        'id' => 32,
        'title' => '[LÁ NHÀ GARDEN] Workshop Tranh rêu độc bản',
        'price' => 'Từ 370.000 đ',
        'raw_price' => 370000,
        'date' => '27.10.2026',
        'location' => 'Bình Thạnh, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-tranh-reu.jpg'
    ],
    [
        'id' => 33,
        'title' => '[LÁ NHÀ GARDEN] Workshop Túi thơm thảo mộc healing',
        'price' => 'Từ 215.000 đ',
        'raw_price' => 215000,
        'date' => '27.10.2026',
        'location' => 'Bình Thạnh, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'workshop',
        'image' => '../assets/images/events/banner-tui-thom-thao-moc.jpg'
    ],[
        'id' => 34,
        'title' => 'Trải Nghiệm Bay Dù Lượn Mù Cang Chải',
        'price' => 'Từ 2.190.000 đ',
        'raw_price' => 2190000,
        'date' => '01.02.2026',
        'location' => 'Mù Cang Chải, Yên Bái',
        'location_code' => 'hanoi',
        'category' => 'trai-nghiem',
        'image' => '../assets/images/events/banner-du-luon-mu-cang-chai.jpg'
    ],
    [
        'id' => 35,
        'title' => 'KHU VUI CHƠI NHÀ CHEN - ƯỚC MƠ XANH',
        'price' => 'Từ 60.000 đ',
        'raw_price' => 60000,
        'date' => '28.07.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'trai-nghiem',
        'image' => '../assets/images/events/banner-nha-chen.jpg'
    ],
    [
        'id' => 36,
        'title' => 'DU LỊCH VĂN HÓA SUỐI TIÊN',
        'price' => 'Từ 60.000 đ',
        'raw_price' => 60000,
        'date' => '01.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'trai-nghiem',
        'image' => '../assets/images/events/banner-suoi-tien.jpg'
    ],
    [
        'id' => 37,
        'title' => 'Bảo Tàng Phở - Bảo Tàng Ẩm Thực Đầu Tiên tại Việt Nam',
        'price' => 'Từ 200.000 đ',
        'raw_price' => 200000,
        'date' => '01.10.2026',
        'location' => 'Nam Định',
        'location_code' => 'hanoi',
        'category' => 'trai-nghiem',
        'image' => '../assets/images/events/banner-bao-tang-pho.jpg'
    ],
    [
        'id' => 38,
        'title' => 'PHẬT BÁO NGHIỆM TRẤN - TRIỂN LÃM DI SẢN VĂN HOÁ PHẬT GIÁO ĐỘC BẢN',
        'price' => 'Từ 150.000 đ',
        'raw_price' => 150000,
        'date' => '27.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'trai-nghiem',
        'image' => '../assets/images/events/banner-phat-bao-nghiem-tran.jpg'
    ],
    [
        'id' => 39,
        'title' => 'City Sightseeing SaiGon: Xe Buýt 2 Tầng',
        'price' => 'Từ 105.000 đ',
        'raw_price' => 105000,
        'date' => '27.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'trai-nghiem',
        'image' => '../assets/images/events/banner-bus-2-tang.jpg'
    ],
    [
        'id' => 40,
        'title' => 'TOUR ĐÊM VĂN MIẾU - VAN MIEU NIGHT TOUR',
        'price' => 'Từ 0 đ',
        'raw_price' => 0,
        'date' => '28.10.2026',
        'location' => 'Hà Nội',
        'location_code' => 'hanoi',
        'category' => 'trai-nghiem',
        'image' => '../assets/images/events/banner-tour-dem-van-mieu.jpg'
    ],
    [
        'id' => 41,
        'title' => 'Vé Trải Nghiệm GenZania 2 tiếng',
        'price' => 'Từ 190.000 đ',
        'raw_price' => 190000,
        'date' => '30.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'trai-nghiem',
        'image' => '../assets/images/events/banner-genzania.jpg'
    ],[
        'id' => 42,
        'title' => 'KỊCH ĐOÀN THÀNH LỘC: CÔ GIÁO DUYÊN',
        'price' => 'Từ 300.000 đ',
        'raw_price' => 300000,
        'date' => '28.10.2026',
        'location' => 'Sân khấu kịch Thiên Đăng, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-co-giao-duyen.jpg'
    ],
    [
        'id' => 43,
        'title' => 'Hà Nội - Eifman Ballet Of ST. Petersubrg World Tour 2026',
        'price' => 'Từ 500.000 đ',
        'raw_price' => 500000,
        'date' => '28.10.2026',
        'location' => 'Nhà hát Lớn Hà Nội',
        'location_code' => 'hanoi',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-eifman-ballet.jpg'
    ],
    [
        'id' => 44,
        'title' => 'KỊCH ĐOÀN THÀNH LỘC: NGŨ QUÝ TƯƠNG PHÙNG',
        'price' => 'Từ 300.000 đ',
        'raw_price' => 300000,
        'date' => '29.10.2026',
        'location' => 'Sân khấu kịch Thiên Đăng, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-ngu-quy-tuong-phung.jpg'
    ],
    [
        'id' => 45,
        'title' => 'Nhà Hát Kịch IDECAF: Hồn Ai Nấy Giữ',
        'price' => 'Từ 300.000 đ',
        'raw_price' => 300000,
        'date' => '29.10.2026',
        'location' => 'Nhà hát kịch IDECAF, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-hon-ai-nay-giu.jpg'
    ],
    [
        'id' => 46,
        'title' => 'Show Thực Cảnh Anh Hùng Cờ Lau - Đinh Bộ Lĩnh',
        'price' => 'Từ 175.000 đ',
        'raw_price' => 175000,
        'date' => '29.10.2026',
        'location' => 'Hà Nội',
        'location_code' => 'hanoi',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-anh-hung-co-lau.jpg'
    ],
    [
        'id' => 47,
        'title' => 'CADAO Collective - New Taste Of Heritage',
        'price' => 'Từ 500.000 đ',
        'raw_price' => 500000,
        'date' => '30.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-cadao-collective.jpg'
    ],
    [
        'id' => 48,
        'title' => 'Nhà Hát Kịch IDECAF: Mẹ Tôi Là Đàn Ông',
        'price' => 'Từ 300.000 đ',
        'raw_price' => 300000,
        'date' => '30.10.2026',
        'location' => 'Nhà hát kịch IDECAF, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-me-toi-la-dan-ong.jpg'
    ],
    [
        'id' => 49,
        'title' => 'BÓNG MA RẠP HÁT LỆ HOA - SÂN KHẨU MỚI',
        'price' => 'Từ 250.000 đ',
        'raw_price' => 250000,
        'date' => '30.10.2026',
        'location' => 'Sân khấu kịch Lệ Hoa, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-bong-ma-le-hoa.jpg'
    ],
    [
        'id' => 50,
        'title' => 'Sân Khấu Thế Giới Trẻ: Mật Mã Cầu Cơ',
        'price' => 'Từ 330.000 đ',
        'raw_price' => 330000,
        'date' => '30.10.2026',
        'location' => 'Sân khấu Thế Giới Trẻ, TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-mat-ma-cau-co.jpg'
    ],
    [
        'id' => 51,
        'title' => 'Mystic Night Show - Magic & Comedy in Saigon',
        'price' => 'Từ 850.000 đ',
        'raw_price' => 850000,
        'date' => '30.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-mystic-night-show.jpg'
    ],
    [
        'id' => 52,
        'title' => 'Cuộc Phiêu Lưu Kỳ Thú',
        'price' => 'Từ 120.000 đ',
        'raw_price' => 120000,
        'date' => '31.10.2026',
        'location' => 'TP. Hồ Chí Minh',
        'location_code' => 'hcm',
        'category' => 'san-khau',
        'image' => '../assets/images/events/banner-cuoc-phieu-luu-ky-thu.jpg'
    ]
];

// 3. Xử lý Lọc dữ liệu theo Địa điểm
if (!empty($selected_location)) {
    $all_events = array_filter($all_events, function ($e) use ($selected_location) {
        return $e['location_code'] === $selected_location;
    });
}

// 4. Xử lý Lọc dữ liệu theo Danh mục từ Navbar (nếu có)
if (!empty($selected_cat)) {
    $all_events = array_filter($all_events, function ($e) use ($selected_cat) {
        return $e['category'] === $selected_cat;
    });
}

// 5. Xử lý Sắp xếp giá tiền
if ($selected_sort === 'price-asc') {
    usort($all_events, fn($a, $b) => $a['raw_price'] <=> $b['raw_price']);
} elseif ($selected_sort === 'price-desc') {
    usort($all_events, fn($a, $b) => $b['raw_price'] <=> $a['raw_price']);
}
?>

<main class="container" style="margin-top: 30px; min-height: 70vh;">
    <!-- Tiêu đề trang & Form Bộ lọc -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <h2 class="section-title" style="margin: 0;">
            <i class="fa-solid fa-compass" style="color: var(--primary-color);"></i> Tất cả sự kiện
        </h2>

        <!-- Form Bộ lọc tự động submit khi thay đổi lựa chọn -->
        <form method="GET" action="events.php" style="display: flex; gap: 10px;">
            <?php if (!empty($selected_cat)): ?>
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($selected_cat); ?>">
            <?php endif; ?>

            <select name="location" onchange="this.form.submit()" class="category-btn" style="background: var(--card-bg); color: #fff; border: 1px solid var(--border-color); padding: 8px 15px; outline: none; cursor: pointer;">
                <option value="">Tất cả địa điểm</option>
                <option value="hanoi" <?php echo $selected_location === 'hanoi' ? 'selected' : ''; ?>>Hà Nội</option>
                <option value="hcm" <?php echo $selected_location === 'hcm' ? 'selected' : ''; ?>>TP. Hồ Chí Minh</option>
                <option value="danang" <?php echo $selected_location === 'danang' ? 'selected' : ''; ?>>Đà Nẵng</option>
            </select>

            <select name="sort" onchange="this.form.submit()" class="category-btn" style="background: var(--card-bg); color: #fff; border: 1px solid var(--border-color); padding: 8px 15px; outline: none; cursor: pointer;">
                <option value="">Sắp xếp: Mới nhất</option>
                <option value="price-asc" <?php echo $selected_sort === 'price-asc' ? 'selected' : ''; ?>>Giá: Thấp đến Cao</option>
                <option value="price-desc" <?php echo $selected_sort === 'price-desc' ? 'selected' : ''; ?>>Giá: Cao đến Thấp</option>
            </select>
        </form>
    </div>

 <!-- Lưới hiển thị sự kiện -->
    <?php if (empty($all_events)): ?>
        <div style="text-align: center; padding: 60px 0; color: var(--text-muted);">
            <i class="fa-solid fa-calendar-xmark" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            <p>Không tìm thấy sự kiện phù hợp với bộ lọc.</p>
            <a href="events.php" style="color: var(--primary-color); font-size: 14px; text-decoration: underline; margin-top: 10px; display: inline-block;">Xem tất cả sự kiện</a>
        </div>
    <?php else: ?>
        <div class="event-grid">
            <?php 
            $index = 0;
            foreach ($all_events as $event) {
                // Ẩn toàn bộ sự kiện từ chỉ số index >= 8 (từ sự kiện thứ 9 trở đi)
                $is_hidden = ($index >= 8);
                $extra_class = $is_hidden ? 'extra-event-item' : '';
                $extra_style = $is_hidden ? 'style="display: none;"' : '';
                
                echo '<div class="' . $extra_class . '" ' . $extra_style . '>';
                include '../templates/event-card.php';
                echo '</div>';
                
                $index++;
            }
            ?>
        </div>

        <!-- Nút Xem thêm chỉ xuất hiện khi số lượng sự kiện ban đầu > 8 -->
        <?php if (count($all_events) > 8): ?>
            <div class="load-more-wrapper" id="loadMoreWrapper">
                <button type="button" id="btnLoadMoreEvents" class="btn-load-more">
                    Xem thêm sự kiện<i class="fa-solid fa-chevron-down"></i>
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<!-- SCRIPT XỬ LÝ NÚT XEM THÊM (HIỂN THỊ THÊM 8 EVENT MỖI LẦN BẤM) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnLoadMore = document.getElementById('btnLoadMoreEvents');
    const loadMoreWrapper = document.getElementById('loadMoreWrapper');

    if (btnLoadMore) {
        btnLoadMore.addEventListener('click', function() {
            // 1. Tìm tất cả các sự kiện đang bị ẩn (mang class extra-event-item và style display: none)
            const hiddenItems = Array.from(document.querySelectorAll('.extra-event-item'))
                                     .filter(item => item.style.display === 'none');
            
            // 2. Lấy ra tối đa 8 sự kiện tiếp theo để hiển thị
            const itemsToShow = hiddenItems.slice(0, 8);
            itemsToShow.forEach(item => {
                item.style.display = 'block';
            });

            // 3. Tính số lượng còn bị ẩn sau lượt bấm này
            const remainingHiddenCount = hiddenItems.length - itemsToShow.length;

            // 4. Nếu đã hiển thị hết (không còn sự kiện ẩn), tự động ẩn nút Xem thêm
            if (remainingHiddenCount <= 0 && loadMoreWrapper) {
                loadMoreWrapper.style.display = 'none';
            }
        });
    }
});
</script>

<?php include '../templates/footer.php'; ?>