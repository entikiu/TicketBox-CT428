document.addEventListener('DOMContentLoaded', () => {
    // 1. HERO BANNER SLIDER 
    const heroSlider = document.getElementById('heroSlider');
    const bannerLeftBtn = document.getElementById('bannerLeftBtn');
    const bannerRightBtn = document.getElementById('bannerRightBtn');

    if (heroSlider) {
        const heroWrapper = heroSlider.closest('.hero-slider-wrapper');
        const originalItems = Array.from(heroSlider.querySelectorAll('.banner-item'));
        const origCount = originalItems.length;

        if (origCount > 1) {
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
                autoTimer = setInterval(slideNext, 3500); 
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

    // 2. CUỘN SỰ KIỆN NỔI BẬT
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

    // 3. CUỘN DANH SÁCH FEATURED STARS

    const starsTrack = document.getElementById('starsTrack');
    const starPrevBtn = document.getElementById('starPrevBtn');
    const starNextBtn = document.getElementById('starNextBtn');

    if (starsTrack && starPrevBtn && starNextBtn) {
        const updateStarArrows = () => {
            starPrevBtn.style.display = starsTrack.scrollLeft > 10 ? 'flex' : 'none';
            const isAtEnd = starsTrack.scrollLeft + starsTrack.clientWidth >= starsTrack.scrollWidth - 10;
            starNextBtn.style.display = isAtEnd ? 'none' : 'flex';
        };

        const scrollStars = (direction) => {
            const card = starsTrack.querySelector('.star-card');
            const step = card ? (card.offsetWidth + 12) * 5 : 680;
            starsTrack.scrollBy({
                left: direction * step,
                behavior: 'smooth'
            });
        };

        starPrevBtn.addEventListener('click', () => scrollStars(-1));
        starNextBtn.addEventListener('click', () => scrollStars(1));
        starsTrack.addEventListener('scroll', updateStarArrows);
        window.addEventListener('resize', updateStarArrows);

        updateStarArrows();
    }

    // 4. SLIDER THẺ THÀNH VIÊN 
    const membershipTrack = document.getElementById('membershipTrack');
    const memPrevBtn = document.getElementById('memPrevBtn');
    const memNextBtn = document.getElementById('memNextBtn');

    if (membershipTrack && memPrevBtn && memNextBtn) {
        const updateMembershipArrows = () => {
            const thumb = membershipTrack.querySelector('.membership-thumb');
            if (thumb && thumb.offsetHeight > 0) {
                const centerTop = (thumb.offsetHeight / 2) + 'px';
                memPrevBtn.style.top = centerTop;
                memNextBtn.style.top = centerTop;
            }

            memPrevBtn.style.display = membershipTrack.scrollLeft > 10 ? 'flex' : 'none';
            const isAtEnd = membershipTrack.scrollLeft + membershipTrack.clientWidth >= membershipTrack.scrollWidth - 10;
            memNextBtn.style.display = isAtEnd ? 'none' : 'flex';
        };

        const scrollMembership = (direction) => {
            const card = membershipTrack.querySelector('.membership-card');
            const step = card ? (card.offsetWidth + 20) * 2 : membershipTrack.clientWidth * 0.7;
            membershipTrack.scrollBy({
                left: direction * step,
                behavior: 'smooth'
            });
        };

        memPrevBtn.addEventListener('click', () => scrollMembership(-1));
        memNextBtn.addEventListener('click', () => scrollMembership(1));
        membershipTrack.addEventListener('scroll', updateMembershipArrows);
        window.addEventListener('resize', updateMembershipArrows);
        window.addEventListener('load', updateMembershipArrows);

        updateMembershipArrows();
    }

    // 5. SLIDER SỰ KIỆN THEO DANH MỤC
    const categorySliders = document.querySelectorAll('.cat-event-slider');

    if (categorySliders.length > 0) {
        const updateCategorySliderArrows = (track, prevBtn, nextBtn) => {
            if (!track || !prevBtn || !nextBtn) return;

            prevBtn.style.display = track.scrollLeft > 10 ? 'flex' : 'none';
            const isAtEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 10;
            nextBtn.style.display = isAtEnd ? 'none' : 'flex';
        };

        const scrollCategorySlider = (track, direction) => {
            if (!track) return;
            const card = track.querySelector('.event-card');
            const step = card ? (card.offsetWidth + 20) * 4 : track.clientWidth * 0.85;
            track.scrollBy({
                left: direction * step,
                behavior: 'smooth'
            });
        };

        const initAllCategorySliders = () => {
            categorySliders.forEach((track) => {
                const prevBtn = document.getElementById(track.id + '_prev');
                const nextBtn = document.getElementById(track.id + '_next');
                updateCategorySliderArrows(track, prevBtn, nextBtn);
            });
        };

        categorySliders.forEach((track) => {
            const prevBtn = document.getElementById(track.id + '_prev');
            const nextBtn = document.getElementById(track.id + '_next');

            if (prevBtn) {
                prevBtn.addEventListener('click', () => scrollCategorySlider(track, -1));
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', () => scrollCategorySlider(track, 1));
            }

            track.addEventListener('scroll', () => {
                updateCategorySliderArrows(track, prevBtn, nextBtn);
            });
        });

        window.addEventListener('resize', initAllCategorySliders);
        initAllCategorySliders();
    }

    // 6. DROPDOWN MENU TÀI KHOẢN Ở HEADER
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

});