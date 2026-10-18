// JS kiểm tra mật khẩu theo thời gian thực cho trang Đăng ký
document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('reg-password');
    if (passwordInput) {
        passwordInput.addEventListener('input', (e) => {
            const val = e.target.value;
            // Viết logic kiểm tra regex độ dài, ký tự đặc biệt, viết hoa ở đây...
        });
    }
});