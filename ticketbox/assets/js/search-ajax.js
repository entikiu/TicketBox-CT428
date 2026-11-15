document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('tbSearchWrapper');
    const form = document.getElementById('tbSearchForm');
    const input = document.getElementById('tbSearchInput');
    const clearBtn = document.getElementById('tbSearchClearBtn');
    const dropdown = document.getElementById('tbSearchDropdown');
    const resultsBox = document.getElementById('tbAjaxResultsGrid');
    const heading = document.getElementById('tbResultsHeading');
    const resetBtn = document.getElementById('tbResetFilterBtn');

    if (!form || !input || !dropdown || !resultsBox) return;

    let debounceTimer = null;
    let activeCity = '';
    let activeCategory = '';

    function openDropdown() {
        dropdown.classList.add('show');
        dropdown.style.display = 'block';
    }

    function closeDropdown() {
        dropdown.classList.remove('show');
        dropdown.style.display = 'none';
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderEvents(events) {
        if (!events || events.length === 0) {
            resultsBox.innerHTML = '<p style="color:#9ca0ab;grid-column:1/-1;padding:12px 0;">Không tìm thấy sự kiện phù hợp với từ khóa hoặc địa chỉ này.</p>';
            return;
        }

        resultsBox.innerHTML = events.map(ev => {
            const price = Number(ev.gia_thap_nhat || 0).toLocaleString('vi-VN');
            const img = ev.hinh_anh || '../assets/images/logo.png';
            return `
                <a href="event-detail.php?id=${ev.id}" class="tb-result-card">
                    <img src="${escapeHtml(img)}" alt="${escapeHtml(ev.ten_su_kien)}" onerror="this.src='../assets/images/logo.png'">
                    <div class="tb-result-info">
                        <div class="tb-result-title">${escapeHtml(ev.ten_su_kien)}</div>
                        <div class="tb-result-price">Từ ${price} đ</div>
                        <div class="tb-result-loc"><i class="fa-solid fa-location-dot"></i> ${escapeHtml(ev.dia_diem || '')}</div>
                    </div>
                </a>
            `;
        }).join('');
    }

    async function fetchSuggestions() {
        const q = input.value.trim();
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (activeCity) params.set('city', activeCity);
        if (activeCategory) params.set('category_id', activeCategory);

        heading.textContent = (q || activeCity || activeCategory) ? 'Kết quả tìm kiếm' : 'Gợi ý dành cho bạn';
        if (resetBtn) {
            resetBtn.style.display = (activeCity || activeCategory) ? 'inline-block' : 'none';
        }

        try {
            const res = await fetch(`../api/events/search-events.php?${params.toString()}`);
            const json = await res.json();
            if (json.status === 'success') {
                renderEvents(json.data);
            }
        } catch (err) {
            console.error('Lỗi tải gợi ý tìm kiếm:', err);
        }
    }

    // Mở dropdown & tải gợi ý khi bấm vào ô tìm kiếm
    input.addEventListener('focus', function () {
        openDropdown();
        fetchSuggestions();
    });

    // Gõ chữ hoặc địa chỉ -> hiện gợi ý sau 250ms
    input.addEventListener('input', function () {
        if (clearBtn) {
            clearBtn.style.display = input.value.trim() !== '' ? 'flex' : 'none';
        }
        openDropdown();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchSuggestions, 250);
    });

    // Nhấn Enter hoặc nút Tìm kiếm -> chuyển sang trang events.php
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const q = input.value.trim();
        const targetUrl = q ? `events.php?search=${encodeURIComponent(q)}` : 'events.php';
        window.location.href = targetUrl;
    });

    // Nút xóa nhanh từ khóa
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            clearBtn.style.display = 'none';
            input.focus();
            fetchSuggestions();
        });
    }

    // Click từ khóa xu hướng
    document.querySelectorAll('.tb-trending-item').forEach(btn => {
        btn.addEventListener('click', function () {
            input.value = this.dataset.keyword || '';
            if (clearBtn) clearBtn.style.display = 'flex';
            fetchSuggestions();
        });
    });

    // Chuyển tab Khám phá theo Thể loại / Thành phố
    document.querySelectorAll('.tb-explore-tab').forEach(tabBtn => {
        tabBtn.addEventListener('click', function () {
            document.querySelectorAll('.tb-explore-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const target = this.dataset.tab;
            document.getElementById('tab-category').style.display = target === 'category' ? 'grid' : 'none';
            document.getElementById('tab-city').style.display = target === 'city' ? 'grid' : 'none';
        });
    });

    // Click thẻ Thể loại hoặc Thành phố
    document.querySelectorAll('.tb-explore-card').forEach(card => {
        card.addEventListener('click', function () {
            activeCity = this.dataset.city || '';
            activeCategory = this.dataset.categoryId || '';
            fetchSuggestions();
        });
    });

    // Đặt lại bộ lọc
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            activeCity = '';
            activeCategory = '';
            fetchSuggestions();
        });
    }

    // Đóng dropdown khi click ra ngoài
    document.addEventListener('click', function (e) {
        if (wrapper && !wrapper.contains(e.target)) {
            closeDropdown();
        }
    });
});