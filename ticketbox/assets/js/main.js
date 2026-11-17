document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // 1. HERO BANNER SLIDER (TỰ CUỘN & CUỘN VÔ CỰC)
    // ==========================================
    const heroSlider = document.getElementById('heroSlider');
    const bannerLeftBtn = document.getElementById('bannerLeftBtn');
    const bannerRightBtn = document.getElementById('bannerRightBtn');

    if (heroSlider) {
        const heroWrapper = heroSlider.closest('.hero-slider-wrapper');
        const originalItems = Array.from(heroSlider.querySelectorAll('.banner-item'));
        const origCount = originalItems.length;

        if (origCount > 1) {
            // Nhân bản toàn bộ banner nối vào cuối để tạo vòng lặp vô cực
            originalItems.forEach((item) => {
                const clone = item.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                heroSlider.appendChild(clone);
            });

            let currentIndex = 0;
            let isMoving = false;
            let autoTimer = null;

            const getStepWidth = () => {
                const firstItem = heroSlider.querySelector('.banner-item');
                if (!firstItem) return 0;
                const styles = window.getComputedStyle(heroSlider);
                const gap = parseFloat(styles.columnGap || styles.gap) || 20;
                return firstItem.offsetWidth + gap;
            };

            const slideNext = () => {
                if (isMoving) return;
                const step = getStepWidth();
                if (!step) return;

                isMoving = true;
                currentIndex++;
                heroSlider.scrollTo({ left: currentIndex * step, behavior: 'smooth' });

                setTimeout(() => {
                    if (currentIndex >= origCount) {
                        currentIndex -= origCount;
                        heroSlider.scrollLeft = currentIndex * step;
                    }
                    isMoving = false;
                }, 450);
            };

            const slidePrev = () => {
                if (isMoving) return;
                const step = getStepWidth();
                if (!step) return;

                isMoving = true;
                if (currentIndex <= 0) {
                    currentIndex = origCount;
                    heroSlider.scrollLeft = currentIndex * step;

                    requestAnimationFrame(() => {
                        currentIndex--;
                        heroSlider.scrollTo({ left: currentIndex * step, behavior: 'smooth' });
                        setTimeout(() => {
                            isMoving = false;
                        }, 450);
                    });
                } else {
                    currentIndex--;
                    heroSlider.scrollTo({ left: currentIndex * step, behavior: 'smooth' });
                    setTimeout(() => {
                        isMoving = false;
                    }, 450);
                }
            };

            const startAutoSlide = () => {
                stopAutoSlide();
                autoTimer = setInterval(slideNext, 3500); // Tự cuộn mỗi 3.5 giây
            };

            const stopAutoSlide = () => {
                if (autoTimer) {
                    clearInterval(autoTimer);
                    autoTimer = null;
                }
            };

            if (bannerRightBtn) {
                bannerRightBtn.addEventListener('click', () => {
                    slideNext();
                    startAutoSlide();
                });
            }

            if (bannerLeftBtn) {
                bannerLeftBtn.addEventListener('click', () => {
                    slidePrev();
                    startAutoSlide();
                });
            }

            // Tạm dừng khi rê chuột vào banner
            if (heroWrapper) {
                heroWrapper.addEventListener('mouseenter', stopAutoSlide);
                heroWrapper.addEventListener('mouseleave', startAutoSlide);
            }

            // Giữ đúng vị trí khi đổi kích thước cửa sổ
            window.addEventListener('resize', () => {
                const step = getStepWidth();
                if (step) {
                    heroSlider.scrollLeft = currentIndex * step;
                }
            });

            startAutoSlide();
        }
    }

    // ==========================================
    // 2. CUỘN SỰ KIỆN NỔI BẬT (TRÁI / PHẢI)
    // ==========================================
    const eventSlider = document.getElementById('eventSlider');
    const slideLeftBtn = document.getElementById('slideLeftBtn');
    const slideRightBtn = document.getElementById('slideRightBtn');

    if (eventSlider && slideLeftBtn && slideRightBtn) {
        const scrollAmount = 800;

        slideLeftBtn.addEventListener('click', () => {
            eventSlider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });

        slideRightBtn.addEventListener('click', () => {
            eventSlider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    }

    // ==========================================
    // 3. CUỘN DANH SÁCH TICKETSTARS (TRÁI / PHẢI)
    // ==========================================
    const starsTrack = document.getElementById('starsTrack');
    const starPrevBtn = document.getElementById('starPrevBtn');
    const starNextBtn = document.getElementById('starNextBtn');

    if (starsTrack && starPrevBtn && starNextBtn) {
        const scrollAmount = 350;

        starNextBtn.addEventListener('click', () => {
            starsTrack.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });

        starPrevBtn.addEventListener('click', () => {
            starsTrack.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
    }

    // ==========================================
    // 4. DROPDOWN MENU TÀI KHOẢN Ở HEADER
    // ==========================================
    const accountBtn = document.getElementById('accountMenuBtn');
    const accountDropdown = document.getElementById('accountDropdown');

    if (accountBtn && accountDropdown) {
        accountBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            accountDropdown.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            accountDropdown.classList.remove('show');
        });
    }

    // ==========================================
    // 5. CHUYỂN ĐỔI NGÔN NGỮ Ở FOOTER (VI / EN)
    // ==========================================
    const langBtns = document.querySelectorAll('.lang-btn');

    langBtns.forEach((btn) => {
        btn.addEventListener('click', function () {
            langBtns.forEach((b) => b.classList.remove('active'));
            this.classList.add('active');

            const selectedLang = this.getAttribute('data-lang');
            localStorage.setItem('ticketbox_lang', selectedLang);
        });
    });

    const savedLang = localStorage.getItem('ticketbox_lang');
    if (savedLang) {
        const activeBtn = document.querySelector(`.lang-btn[data-lang="${savedLang}"]`);
        if (activeBtn) {
            langBtns.forEach((b) => b.classList.remove('active'));
            activeBtn.classList.add('active');
        }
    }
});