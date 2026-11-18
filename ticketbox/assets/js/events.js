// 1. PHÂN TRANG XEM THÊM SỰ KIỆN THEO LƯỚI

function getGridBatchSize(gridEl) {
    if (!gridEl) return 8;
    const cols = window.getComputedStyle(gridEl).gridTemplateColumns.trim().split(/\s+/).length;
    if (cols === 1) return 4;
    if (cols === 2 || cols === 3) return 6;
    return 8;
}

function syncInitialSectionEvents(gridId, itemClass, wrapperId) {
    const gridEl = document.getElementById(gridId);
    if (!gridEl || gridEl.classList.contains('is-expanded')) return;

    const items = Array.from(gridEl.querySelectorAll('.' + itemClass));
    const limit = getGridBatchSize(gridEl);

    items.forEach((el, idx) => {
        el.style.display = idx < limit ? '' : 'none';
    });

    const wrapper = document.getElementById(wrapperId);
    if (wrapper) {
        wrapper.style.display = items.length > limit ? '' : 'none';
    }
}

function loadMoreSectionEvents(gridId, itemClass, wrapperId) {
    const gridEl = document.getElementById(gridId);
    if (!gridEl) return;

    gridEl.classList.add('is-expanded');
    const batchSize = getGridBatchSize(gridEl);

    const hiddenItems = Array.from(gridEl.querySelectorAll('.' + itemClass))
        .filter(el => el.style.display === 'none');

    hiddenItems.slice(0, batchSize).forEach(el => {
        el.style.display = '';
    });

    const remaining = Array.from(gridEl.querySelectorAll('.' + itemClass))
        .filter(el => el.style.display === 'none');
    if (remaining.length === 0) {
        const wrapper = document.getElementById(wrapperId);
        if (wrapper) wrapper.style.display = 'none';
    }
}

function initAllEventGrids() {
    syncInitialSectionEvents('upcomingEventsGrid', 'upcoming-event-item', 'loadMoreUpcomingWrapper');
    syncInitialSectionEvents('pastEventsGrid', 'past-event-item', 'loadMorePastWrapper');
}

document.addEventListener('DOMContentLoaded', function () {
  
    initAllEventGrids();
    document.querySelectorAll('.btn-load-more[data-grid]').forEach(function (btn) {
        if (btn.dataset.bound === '1') return;
        btn.dataset.bound = '1';

        btn.addEventListener('click', function () {
            const gridId = btn.getAttribute('data-grid');
            const itemClass = btn.getAttribute('data-item');
            const wrapperId = btn.getAttribute('data-wrapper');
            loadMoreSectionEvents(gridId, itemClass, wrapperId);
        });
    });

    // Tự động submit form khi thay đổi bộ lọc sắp xếp
    const sortSelect = document.getElementById('eventsSortSelect');
    if (sortSelect && sortSelect.form) {
        sortSelect.addEventListener('change', function () {
            sortSelect.form.submit();
        });
    }

    // 2. NÚT XEM THÊM & FETCH
    const btnLoadMore = document.getElementById('btnLoadMoreEvents');
    const loadMoreWrapper = document.getElementById('loadMoreWrapper');

    if (btnLoadMore) {
        btnLoadMore.addEventListener('click', function () {
            // 1. Tìm tất cả các sự kiện đang bị ẩn từ thứ 9 trở đi
            const hiddenItems = Array.from(document.querySelectorAll('.extra-event-item'))
                .filter(item => item.style.display === 'none');

            // 2. Hiển thị tối đa 8 sự kiện tiếp theo
            const itemsToShow = hiddenItems.slice(0, 8);
            itemsToShow.forEach(item => {
                item.style.display = 'block';
            });

            // 3. Tính số lượng sự kiện còn lại đang bị ẩn
            const remainingHiddenCount = hiddenItems.length - itemsToShow.length;

            // 4. Tự động ẩn nút Xem thêm khi đã mở hết danh sách
            if (remainingHiddenCount <= 0 && loadMoreWrapper) {
                loadMoreWrapper.style.display = 'none';
            }
        });
    }
});

window.addEventListener('resize', initAllEventGrids);

// Lấy các phần tử DOM
const searchInput = document.getElementById('searchInput');
const filterStatus = document.getElementById('filterStatus');
const eventsContainer = document.getElementById('eventsContainer');

// Hàm Fetch API lấy danh sách sự kiện
async function fetchEvents() {
    const keyword = searchInput.value.trim();
    const status = filterStatus.value;

    // Hiển thị trạng thái đang tải
    eventsContainer.innerHTML = '<p>Đang tải dữ liệu...</p>';

    try {
        // Gửi request GET tới API với query parameters
        const response = await fetch(`/api/events/list.php?keyword=${encodeURIComponent(keyword)}&status=${status}`);
        const result = await response.json();

        if (result.status === 'success') {
            renderEvents(result.data);
        } else {
            eventsContainer.innerHTML = `<p class="error">${result.message}</p>`;
        }
    } catch (error) {
        console.error('Lỗi khi tải sự kiện:', error);
        eventsContainer.innerHTML = '<p class="error">Không thể kết nối đến máy chủ.</p>';
    }
}

// Hàm render giao diện sự kiện
function renderEvents(events) {
    if (events.length === 0) {
        eventsContainer.innerHTML = '<p>Không tìm thấy sự kiện nào phù hợp.</p>';
        return;
    }

    eventsContainer.innerHTML = events.map(event => `
        <div class="event-card">
            <h3>${event.TenSuKien}</h3>
            <p>Thời gian: ${event.ThoiGian}</p>
            <p>Vé còn lại: <span id="seats-${event.ID}">${event.SoLuongToiDa - event.SoNguoiDaDangKy}</span></p>
            <button class="btn-register" data-id="${event.ID}">Đăng ký tham dự</button>
        </div>
    `).join('');
}

let timeout = null;
if (searchInput) {
    searchInput.addEventListener('keyup', () => {
        clearTimeout(timeout);
        timeout = setTimeout(fetchEvents, 500); 
    });
}

if (filterStatus) {
    filterStatus.addEventListener('change', fetchEvents);
}

if (typeof fetchEvents === 'function' && (searchInput || filterStatus)) {
    document.addEventListener('DOMContentLoaded', fetchEvents);
}