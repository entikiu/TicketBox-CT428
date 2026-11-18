document.addEventListener('DOMContentLoaded', function () {
    // 1. Điều khiển mở/đóng Sidebar trên Mobile & Tablet
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggleBtn && sidebar && overlay) {
        const closeSidebar = function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        };

        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 992 && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });
    }

    // 2. Hộp thoại xác nhận thao tác xóa (thay thế onclick inline)
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const message = el.getAttribute('data-confirm') || 'Bạn có chắc chắn muốn thực hiện thao tác này?';
            if (!window.confirm(message)) {
                e.preventDefault();
            }
        });
    });
});