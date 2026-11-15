/* ==========================================================================
   XỬ LÝ KHUNG TÌM KIẾM AJAX REAL-TIME - TICKETBOX
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    const BASE_PATH = '/TicketBox-CT428/ticketbox';
    const wrapper = document.getElementById('tbSearchWrapper');
    const form = document.getElementById('tbSearchForm');
    const input = document.getElementById('tbSearchInput');
    const clearBtn = document.getElementById('tbSearchClearBtn');
    const dropdown = document.getElementById('tbSearchDropdown');
    const trendingSection = document.getElementById('tbTrendingSection');
    const exploreSection = document.getElementById('tbExploreSection');
    const resultsGrid = document.getElementById('tbAjaxResultsGrid');
    const headingEl = document.getElementById('tbResultsHeading');
    const resetBtn = document.getElementById('tbResetFilterBtn');
    const tabs = document.querySelectorAll('.tb-explore-tab');
    const trendingBtns = document.querySelectorAll('.tb-trending-item');
    const exploreCards = document.querySelectorAll('.tb-explore-card');

    if (!wrapper || !input || !dropdown || !resultsGrid) return;

    // Tự động tạo lớp phủ tối nền
    let backdrop = document.getElementById('tbSearchBackdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'tbSearchBackdrop';
        backdrop.className = 'tb-search-backdrop';
        document.body.appendChild(backdrop);
    }

    let debounceTimer = null;
    let isInitialLoaded = false;
    let currentController = null;

    function openDropdown() {
        dropdown.classList.add('open');
        backdrop.classList.add('open');
        if (!isInitialLoaded) {
            loadEventsAJAX();
        }
    }

    function closeDropdown() {
        dropdown.classList.remove('open');
        backdrop.classList.remove('open');
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeImagePath(rawPath) {
        const fallback = `${BASE_PATH}/assets/images/events/banner-tinh-ha.jpg`;
        if (!rawPath) return fallback;
        const path = String(rawPath).trim();
        if (/^https?:\/\//i.test(path)) return path;
        if (path.startsWith('/')) return path;
        return `${BASE_PATH}/${path.replace(/^(\.\.\/)+/, '')}`;
    }

    function formatTicketPrice(rawPrice) {
        const num = Number(rawPrice || 0);
        if (num <= 0) return 'Miễn phí';
        return 'Từ ' + num.toLocaleString('vi-VN') + 'đ';
    }

    function extractShortCity(address) {
        const addr = String(address || '');
        if (/hồ chí minh|tp\.?\s*hcm|thủ đức/i.test(addr)) return 'TP.HCM';
        if (/hà nội/i.test(addr)) return 'Hà Nội';
        if (/đà lạt|lâm đồng/i.test(addr)) return 'Đà Lạt';
        if (/sapa|lào cai/i.test(addr)) return 'Sapa';
        if (/yên bái/i.test(addr)) return 'Yên Bái';
        if (/nam định/i.test(addr)) return 'Nam Định';
        const parts = addr.split(',');
        return parts[parts.length - 1].trim() || 'Việt Nam';
    }

    function formatShortDate(dateTimeStr) {
        if (!dateTimeStr) return '';
        const d = new Date(String(dateTimeStr).replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return '';
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        return `${day} Th ${month}`;
    }

    function toggleDiscoverySections(hasTextQuery) {
        if (trendingSection) {
            trendingSection.style.display = hasTextQuery ? 'none' : 'flex';
        }
        if (exploreSection) {
            exploreSection.style.display = hasTextQuery ? 'none' : 'block';
        }
    }

    async function loadEventsAJAX({ keyword = '', categoryId = null, label = '' } = {}) {
        if (currentController) {
            currentController.abort();
        }
        currentController = new AbortController();

        const trimmedKw = keyword.trim();
        toggleDiscoverySections(trimmedKw !== '');

        resultsGrid.innerHTML = `
            <div class="tb-search-state-msg">
                <i class="fa-solid fa-spinner fa-spin"></i> Đang tìm kiếm sự kiện...
            </div>
        `;

        const params = new URLSearchParams();
        if (trimmedKw !== '') {
            params.set('keyword', trimmedKw);
        }
        if (categoryId) {
            params.set('danh_muc_id', String(categoryId));
        }

        const isFiltered = trimmedKw !== '' || Boolean(categoryId);
        if (headingEl) {
            headingEl.textContent = isFiltered
                ? (label ? `Kết quả cho "${label}"` : `Kết quả tìm kiếm "${trimmedKw}"`)
                : 'Gợi ý dành cho bạn';
        }
        if (resetBtn) {
            resetBtn.style.display = isFiltered ? 'inline-block' : 'none';
        }

        try {
            const queryStr = params.toString() ? `?${params.toString()}` : '';
            const response = await fetch(`${BASE_PATH}/api/events/list.php${queryStr}`, {
                method: 'GET',
                credentials: 'same-origin',
                signal: currentController.signal,
                headers: { 'Accept': 'application/json' }
            });

            const result = await response.json();
            if (!response.ok || result.success === false) {
                resultsGrid.innerHTML = `<div class="tb-search-state-msg">${escapeHtml(result.message || 'Không thể tải dữ liệu.')}</div>`;
                return;
            }

            const allEvents = Array.isArray(result.data) ? result.data : [];
            const totalCount = Number(result.total ?? allEvents.length);
            renderEventCards(allEvents.slice(0, 6), totalCount, queryStr);
            isInitialLoaded = true;
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error('Lỗi tìm kiếm AJAX:', error);
            resultsGrid.innerHTML = `<div class="tb-search-state-msg">Không thể kết nối đến máy chủ API.</div>`;
        }
    }

    function renderEventCards(events, totalCount, queryStr) {
        if (events.length === 0) {
            resultsGrid.innerHTML = `
                <div class="tb-search-state-msg">
                    Không tìm thấy sự kiện nào phù hợp với từ khóa của bạn.
                </div>
            `;
            return;
        }

        const fallbackImg = `${BASE_PATH}/assets/images/events/banner-tinh-ha.jpg`;
        const cardsHtml = events.map(ev => {
            const id = Number(ev.id);
            const title = escapeHtml(ev.ten_su_kien || ev.title);
            const price = formatTicketPrice(ev.gia_thap_nhat);
            const city = escapeHtml(extractShortCity(ev.dia_diem));
            const dateText = escapeHtml(formatShortDate(ev.thoi_gian));
            const imgSrc = escapeHtml(normalizeImagePath(ev.hinh_anh));

            return `
                <a href="${BASE_PATH}/pages/event-detail.php?id=${id}" class="tb-result-card">
                    <div class="tb-result-thumb">
                        <img src="${imgSrc}" alt="${title}" onerror="this.src='${fallbackImg}'">
                    </div>
                    <h5 class="tb-result-title">${title}</h5>
                    <p class="tb-result-price">${price}</p>
                    <div class="tb-result-meta">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>${city}${dateText ? ' • ' + dateText : ''}</span>
                    </div>
                </a>
            `;
        }).join('');

        const viewAllHtml = totalCount > 6
            ? `<div class="tb-results-footer">
                   <a href="${BASE_PATH}/pages/events.php${queryStr}" class="tb-view-all-btn">
                       Xem tất cả ${totalCount} sự kiện <i class="fa-solid fa-arrow-right"></i>
                   </a>
               </div>`
            : '';

        resultsGrid.innerHTML = cardsHtml + viewAllHtml;
    }

    // 1. Mở dropdown khi focus vào ô tìm kiếm và giữ mở khi thao tác bên trong
    input.addEventListener('focus', openDropdown);

    wrapper.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // 2. Gõ phím tìm kiếm real-time (Debounce 300ms)
    input.addEventListener('input', () => {
        const val = input.value;
        if (!dropdown.classList.contains('open')) {
            openDropdown();
        }
        if (clearBtn) {
            clearBtn.style.display = val.length > 0 ? 'flex' : 'none';
        }
        exploreCards.forEach(c => c.classList.remove('selected'));

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            loadEventsAJAX({ keyword: val });
        }, 300);
    });

    // Ngăn reload trang khi nhấn Enter hoặc bấm nút "Tìm kiếm"
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            clearTimeout(debounceTimer);
            openDropdown();
            loadEventsAJAX({ keyword: input.value });
        });
    }

    // 3. Nút xóa nhanh nội dung ô tìm kiếm
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            input.value = '';
            clearBtn.style.display = 'none';
            exploreCards.forEach(c => c.classList.remove('selected'));
            input.focus();
            loadEventsAJAX();
        });
    }

    // 4. Bấm vào từ khóa xu hướng
    trendingBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const kw = btn.getAttribute('data-keyword') || '';
            input.value = kw;
            if (clearBtn) clearBtn.style.display = 'flex';
            exploreCards.forEach(c => c.classList.remove('selected'));
            loadEventsAJAX({ keyword: kw, label: kw });
        });
    });

    // 5. Chuyển đổi Tab Thể loại / Thành phố
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const target = tab.getAttribute('data-tab');
            const catGrid = document.getElementById('tab-category');
            const cityGrid = document.getElementById('tab-city');
            if (catGrid) catGrid.style.display = target === 'category' ? 'grid' : 'none';
            if (cityGrid) cityGrid.style.display = target === 'city' ? 'grid' : 'none';
        });
    });

    // 6. Bấm vào thẻ Thể loại hoặc Thành phố để lọc AJAX
    exploreCards.forEach(card => {
        card.addEventListener('click', () => {
            exploreCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');

            const catId = card.getAttribute('data-category-id');
            const city = card.getAttribute('data-city');
            const label = card.getAttribute('data-label') || '';

            if (catId) {
                input.value = '';
                if (clearBtn) clearBtn.style.display = 'none';
                loadEventsAJAX({ categoryId: Number(catId), label });
            } else if (city) {
                input.value = city;
                if (clearBtn) clearBtn.style.display = 'flex';
                loadEventsAJAX({ keyword: city, label });
            }
        });
    });

    // 7. Nút đặt lại bộ lọc về mặc định
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            input.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            exploreCards.forEach(c => c.classList.remove('selected'));
            loadEventsAJAX();
        });
    }

    // 8. Đóng dropdown khi click ra vùng tối bên ngoài hoặc nhấn ESC
    backdrop.addEventListener('click', closeDropdown);

    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });
});