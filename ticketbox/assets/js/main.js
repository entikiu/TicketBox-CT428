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