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
            resultsBox.innerHTML = '<p style="color:#9ca0ab;grid-column:1/-1;padding:12px 0;margin:0;">Không tìm thấy sự kiện phù hợp.</p>';
            return;
        }

        resultsBox.innerHTML = events.map(ev => {
            const price = Number(ev.gia_thap_nhat || 0).toLocaleString('vi-VN');
            const img = ev.hinh_anh || '../assets/images/logo.png';
            return `
                <a href="event-detail.php?id=${ev.id}" class="tb-result-card">
                    <img src="${escapeHtml(img)}" alt="${escapeHtml(ev.ten_su_kien)}" onerror="this.src='../assets/images/logo.png'">
                    <div style="padding:8px 10px;">
                        <div style="font-size:13px;font-weight:700;line-height:1.35;height:35px;overflow:hidden;margin-bottom:4px;">${escapeHtml(ev.ten_su_kien)}</div>
                        <div style="color:#26c973;font-size:12.5px;font-weight:700;">Từ ${price} đ</div>
                        <div style="color:#9ca0ab;font-size:11.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;">
                            <i class="fa-solid fa-location-dot"></i> ${escapeHtml(ev.dia_diem || '')}
                        </div>
                    </div>
                </a>
            `;
        }).join('');
    }

    async function fetchSuggestions() {
        const q = input.value.trim();
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (activeCategory) params.set('category_id', activeCategory);

        heading.textContent = (q || activeCategory) ? 'Kết quả tìm kiếm' : 'Gợi ý dành cho bạn';
        if (resetBtn) {
            resetBtn.style.display = activeCategory ? 'inline-block' : 'none';
        }

        const endpoints = [
            `../api/events/search-events.php?${params.toString()}`,
            `../api/search-events.php?${params.toString()}`
        ];

        for (const url of endpoints) {
            try {
                const res = await fetch(url);
                if (!res.ok) continue;
                const json = await res.json();
                if (json.status === 'success') {
                    renderEvents(json.data);
                    return;
                }
            } catch (err) {
                // Thử đường dẫn kế tiếp
            }
        }
    }

    input.addEventListener('focus', function () {
        openDropdown();
        fetchSuggestions();
    });

    input.addEventListener('input', function () {
        if (clearBtn) {
            clearBtn.style.display = input.value.trim() !== '' ? 'flex' : 'none';
        }
        openDropdown();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchSuggestions, 250);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const q = input.value.trim();
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (activeCategory) params.set('cat', activeCategory);
        const qs = params.toString();
        window.location.href = qs ? `events.php?${qs}` : 'events.php';
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            clearBtn.style.display = 'none';
            input.focus();
            fetchSuggestions();
        });
    }

    document.querySelectorAll('.tb-trending-item').forEach(btn => {
        btn.addEventListener('click', function () {
            input.value = this.dataset.keyword || '';
            if (clearBtn) clearBtn.style.display = 'flex';
            fetchSuggestions();
        });
    });

    document.querySelectorAll('.tb-explore-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function () {
            activeCategory = this.dataset.categoryId || '';
            fetchSuggestions();
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            activeCategory = '';
            fetchSuggestions();
        });
    }

    document.addEventListener('click', function (e) {
        if (wrapper && !wrapper.contains(e.target)) {
            closeDropdown();
        }
    });
});