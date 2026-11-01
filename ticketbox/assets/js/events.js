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

// Gắn sự kiện (Debounce cho ô tìm kiếm để tránh gọi API liên tục)
let timeout = null;
searchInput.addEventListener('keyup', () => {
    clearTimeout(timeout);
    timeout = setTimeout(fetchEvents, 500); // Đợi 500ms sau khi ngừng gõ
});

filterStatus.addEventListener('change', fetchEvents);

// Gọi lần đầu khi tải trang
document.addEventListener('DOMContentLoaded', fetchEvents);