document.addEventListener('DOMContentLoaded', function() {
    const btnLoadMore = document.getElementById('btnLoadMoreEvents');
    const loadMoreWrapper = document.getElementById('loadMoreWrapper');

    if (btnLoadMore) {
        btnLoadMore.addEventListener('click', function() {
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