document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('eventSlider');
    const leftBtn = document.getElementById('slideLeftBtn');
    const rightBtn = document.getElementById('slideRightBtn');

    if (slider && leftBtn && rightBtn) {
        // Mỗi lần bấm cuộn khoảng cách bằng 3 thẻ sự kiện (~800px)
        const scrollAmount = 800;

        leftBtn.addEventListener('click', () => {
            slider.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        });

        rightBtn.addEventListener('click', () => {
            slider.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });
    }
});
document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. Xử lý cuộn cho Hero Banner Slider ---
    const heroSlider = document.getElementById('heroSlider');
    const bannerLeftBtn = document.getElementById('bannerLeftBtn');
    const bannerRightBtn = document.getElementById('bannerRightBtn');

    if (heroSlider && bannerLeftBtn && bannerRightBtn) {
        bannerLeftBtn.addEventListener('click', () => {
            // Cuộn sang trái 1 khoảng bằng chiều rộng khung chứa
            heroSlider.scrollBy({
                left: -heroSlider.clientWidth,
                behavior: 'smooth'
            });
        });

        bannerRightBtn.addEventListener('click', () => {
            // Cuộn sang phải 1 khoảng bằng chiều rộng khung chứa
            heroSlider.scrollBy({
                left: heroSlider.clientWidth,
                behavior: 'smooth'
            });
        });
    }

    // --- 2. Xử lý cuộn cho Sự kiện nổi bật ---
    const eventSlider = document.getElementById('eventSlider');
    const slideLeftBtn = document.getElementById('slideLeftBtn');
    const slideRightBtn = document.getElementById('slideRightBtn');

    if (eventSlider && slideLeftBtn && slideRightBtn) {
        slideLeftBtn.addEventListener('click', () => {
            eventSlider.scrollBy({
                left: -800,
                behavior: 'smooth'
            });
        });

        slideRightBtn.addEventListener('click', () => {
            eventSlider.scrollBy({
                left: 800,
                behavior: 'smooth'
            });
        });
    }
});
document.addEventListener('DOMContentLoaded', () => {
    // 1. Xử lý bật/tắt Dropdown Menu Tài khoản ở Header
    const accountBtn = document.getElementById('accountMenuBtn');
    const accountDropdown = document.getElementById('accountDropdown');

    if (accountBtn && accountDropdown) {
        accountBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            accountDropdown.classList.toggle('show');
        });

        // Click ra ngoài màn hình sẽ tự động đóng menu
        document.addEventListener('click', () => {
            accountDropdown.classList.remove('show');
        });
    }
});
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. TƯƠNG TÁC DROPDOWN MENU TÀI KHOẢN
    const accountBtn = document.getElementById('accountMenuBtn');
    const accountDropdown = document.getElementById('accountDropdown');

    if (accountBtn && accountDropdown) {
        accountBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            accountDropdown.classList.toggle('show');
        });

        // Click ra ngoài cửa sổ sẽ tự đóng menu
        document.addEventListener('click', () => {
            accountDropdown.classList.remove('show');
        });
    }

    // 2. TƯƠNG TÁC NÚT CUỘN HERO BANNER (TRÁI / PHẢI)
    const heroSlider = document.getElementById('heroSlider');
    const bannerLeftBtn = document.getElementById('bannerLeftBtn');
    const bannerRightBtn = document.getElementById('bannerRightBtn');

    if (heroSlider && bannerLeftBtn && bannerRightBtn) {
        bannerLeftBtn.addEventListener('click', () => {
            heroSlider.scrollBy({ left: -600, behavior: 'smooth' });
        });
        bannerRightBtn.addEventListener('click', () => {
            heroSlider.scrollBy({ left: 600, behavior: 'smooth' });
        });
    }

    // 3. TƯƠNG TÁC NÚT CUỘN SỰ KIỆN NỔI BẬT (TRÁI / PHẢI)
    const eventSlider = document.getElementById('eventSlider');
    const slideLeftBtn = document.getElementById('slideLeftBtn');
    const slideRightBtn = document.getElementById('slideRightBtn');

    if (eventSlider && slideLeftBtn && slideRightBtn) {
        slideLeftBtn.addEventListener('click', () => {
            eventSlider.scrollBy({ left: -550, behavior: 'smooth' });
        });
        slideRightBtn.addEventListener('click', () => {
            eventSlider.scrollBy({ left: 550, behavior: 'smooth' });
        });
    }
});
// ==========================================
// CHỨC NĂNG BỔ TRỢ PHẦN FOOTER
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    // 1. Xử lý chuyển đổi Ngôn ngữ (VI / EN)
    const langBtns = document.querySelectorAll('.lang-btn');
    
    langBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Loại bỏ active hiện tại
            langBtns.forEach(b => b.classList.remove('active'));
            // Thêm active cho nút vừa bấm
            this.classList.add('active');
            
            const selectedLang = this.getAttribute('data-lang');
            
            // Lưu lựa chọn ngôn ngữ vào LocalStorage
            localStorage.setItem('ticketbox_lang', selectedLang);
            
            // Thông báo chuyển đổi ngôn ngữ thành công (Tích hợp thực tế)
            console.log('Đã chuyển ngôn ngữ sang:', selectedLang.toUpperCase());
        });
    });

    // Tự động khôi phục ngôn ngữ đã chọn trước đó
    const savedLang = localStorage.getItem('ticketbox_lang');
    if (savedLang) {
        const activeBtn = document.querySelector(`.lang-btn[data-lang="${savedLang}"]`);
        if (activeBtn) {
            langBtns.forEach(b => b.classList.remove('active'));
            activeBtn.classList.add('active');
        }
    }
});