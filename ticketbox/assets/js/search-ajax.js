window.toggleAccountMenu = function (event) {
    if (event) event.stopPropagation();
    const dropdown = document.getElementById('accountDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
};

document.addEventListener('DOMContentLoaded', function () {
    // ==========================================
    // 1. XỬ LÝ NÚT ĐĂNG XUẤT Ở HEADER
    // ==========================================
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', function (e) {
            if (typeof handleLogout === 'function') {
                e.preventDefault();
                handleLogout();
            }
        });
    }

    // ==========================================
    // 2. TÌM KIẾM AJAX REAL-TIME (SỰ KIỆN + TICKETSTARS)
    // ==========================================
    const wrapper = document.getElementById('tbSearchWrapper');
    const input = document.getElementById('tbSearchInput');
    const clearBtn = document.getElementById('tbSearchClearBtn');
    const dropdown = document.getElementById('tbSearchDropdown');
    const starsSection = document.getElementById('tbStarsResultsSection');
    const starsGrid = document.getElementById('tbStarsResultsGrid');
    const resultsHeading = document.getElementById('tbResultsHeading');
    const resetFilterBtn = document.getElementById('tbResetFilterBtn');
    const resultsGrid = document.getElementById('tbAjaxResultsGrid');

    if (!wrapper || !input || !dropdown || !resultsGrid) return;

    let state = {
        q: input.value.trim(),
        categoryId: '',
        label: '',
        loaded: false,
        activeIndex: -1
    };
    let debounceTimer = null;
    let activeController = null;

    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openDropdown() {
        dropdown.classList.add('show');
        dropdown.style.display = 'block';
        if (!state.loaded) {
            state.loaded = true;
            fetchResults();
        }
    }

    function closeDropdown() {
        dropdown.classList.remove('show');
        dropdown.style.display = 'none';
        state.activeIndex = -1;
    }

    function syncUI() {
        if (clearBtn) {
            clearBtn.style.display = input.value.trim() !== '' ? 'flex' : 'none';
        }
        if (resetFilterBtn) {
            resetFilterBtn.style.display = (state.q !== '' || state.categoryId !== '') ? 'inline-block' : 'none';
        }
        document.querySelectorAll('.tb-explore-card').forEach(card => {
            card.classList.toggle('active', card.getAttribute('data-category-id') === state.categoryId);
        });
    }

    async function fetchResults() {
        syncUI();
        if (activeController) activeController.abort();
        activeController = new AbortController();

        const params = new URLSearchParams();
        params.set('tb_ajax_search', '1');
        if (state.q) params.set('q', state.q);
        if (state.categoryId) params.set('category_id', state.categoryId);

        try {
            const res = await fetch(`${window.location.pathname}?${params.toString()}`, {
                signal: activeController.signal,
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            renderResults(data);
        } catch (err) {
            if (err.name !== 'AbortError') {
                resultsGrid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:18px;color:#9ca3af;">Không thể tải kết quả lúc này.</div>';
            }
        }
    }

    function renderResults(data) {
        const stars = Array.isArray(data.stars) ? data.stars : [];
        const events = Array.isArray(data.events) ? data.events : [];
        state.activeIndex = -1;

        if (starsSection && starsGrid) {
            if (stars.length > 0) {
                starsSection.style.display = 'block';
                starsGrid.innerHTML = stars.map(st => `
                    <a href="organizer.php?slug=${encodeURIComponent(st.slug)}" class="tb-star-result-card tb-nav-item">
                        <img src="${esc(st.avatar)}" alt="${esc(st.ten_hien_thi)}" class="tb-star-result-avatar"
                             onerror="this.src='https://via.placeholder.com/80/16181d/26c973?text=STAR'">
                        <div class="tb-star-result-meta">
                            <div class="tb-star-result-name">
                                <span>${esc(st.ten_hien_thi)}</span>
                                ${Number(st.da_xac_minh) === 1 ? '<i class="fa-solid fa-circle-check" style="color:#26c973;font-size:12px;"></i>' : ''}
                            </div>
                            <div class="tb-star-result-sub">${esc(st.loai_label)} &bull; ${esc(st.followers_text)} theo dõi</div>
                        </div>
                    </a>
                `).join('');
            } else {
                starsSection.style.display = 'none';
                starsGrid.innerHTML = '';
            }
        }

        if (resultsHeading) {
            if (state.q) {
                resultsHeading.textContent = `Kết quả cho "${state.q}"`;
            } else if (state.label) {
                resultsHeading.textContent = `Thể loại: ${state.label}`;
            } else {
                resultsHeading.textContent = 'Gợi ý dành cho bạn';
            }
        }

        if (events.length === 0) {
            resultsGrid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:20px;color:#9ca3af;">Không tìm thấy sự kiện nào phù hợp.</div>';
            return;
        }

        resultsGrid.innerHTML = events.map(ev => `
            <a href="event-detail.php?id=${Number(ev.id)}" class="tb-result-card tb-nav-item">
                <div class="tb-result-thumb">
                    <img src="${esc(ev.image)}" alt="${esc(ev.title)}"
                         onerror="this.src='https://via.placeholder.com/320x180/16181d/26c973?text=Ticketbox'">
                </div>
                <div class="tb-result-info">
                    <div class="tb-result-title">${esc(ev.title)}</div>
                    <div class="tb-result-price">${esc(ev.price)}</div>
                    <div class="tb-result-date">
                        <i class="fa-regular fa-calendar"></i> ${esc(ev.date)} &bull; ${esc(ev.city)}
                    </div>
                </div>
            </a>
        `).join('');
    }

    input.addEventListener('focus', openDropdown);
    input.addEventListener('click', openDropdown);

    input.addEventListener('input', function () {
        state.q = input.value.trim();
        if (state.q !== '') {
            state.categoryId = '';
            state.label = '';
        }
        openDropdown();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchResults, 220);
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function (e) {
            e.preventDefault();
            input.value = '';
            state.q = '';
            state.categoryId = '';
            state.label = '';
            input.focus();
            fetchResults();
        });
    }

    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function (e) {
            e.preventDefault();
            input.value = '';
            state.q = '';
            state.categoryId = '';
            state.label = '';
            fetchResults();
        });
    }

    dropdown.addEventListener('click', function (e) {
        const trendBtn = e.target.closest('.tb-trending-item');
        if (trendBtn) {
            e.preventDefault();
            const kw = trendBtn.getAttribute('data-keyword') || '';
            input.value = kw;
            state.q = kw;
            state.categoryId = '';
            state.label = '';
            fetchResults();
            return;
        }

        const catCard = e.target.closest('.tb-explore-card');
        if (catCard) {
            e.preventDefault();
            input.value = '';
            state.q = '';
            state.categoryId = catCard.getAttribute('data-category-id') || '';
            state.label = catCard.getAttribute('data-label') || '';
            fetchResults();
        }
    });

    input.addEventListener('keydown', function (e) {
        const items = Array.from(dropdown.querySelectorAll('.tb-nav-item'));
        if (e.key === 'Escape') {
            closeDropdown();
            input.blur();
            return;
        }
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            state.activeIndex = (state.activeIndex + 1) % items.length;
            items.forEach((el, i) => el.classList.toggle('is-active', i === state.activeIndex));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            state.activeIndex = (state.activeIndex - 1 + items.length) % items.length;
            items.forEach((el, i) => el.classList.toggle('is-active', i === state.activeIndex));
        } else if (e.key === 'Enter' && state.activeIndex >= 0 && items[state.activeIndex]) {
            e.preventDefault();
            items[state.activeIndex].click();
        }
    });

    document.addEventListener('click', function (e) {
        if (!wrapper.contains(e.target)) {
            closeDropdown();
        }
    });
});