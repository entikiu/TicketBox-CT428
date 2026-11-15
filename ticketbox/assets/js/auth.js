/* ==========================================================================
   TỆP JS AUTHENTICATION HOÀN CHỈNH - TICKETBOX
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ----------------------------------------------------------------------
    // 1. XỬ LÝ GIAO DIỆN & KẾT NỐI API ĐĂNG NHẬP (pages/login.php)
    // ----------------------------------------------------------------------
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        const emailInput = document.getElementById('loginEmail');
        const passwordInput = document.getElementById('loginPassword');
        const btnSubmit = document.getElementById('btnLoginSubmit');
        const alertError = document.getElementById('apiAlertError');

        // Bật/Tắt trạng thái nút "Tiếp Tục" (Mờ xám <-> Sáng active)
        function checkLoginFormFilled() {
            if (!emailInput || !passwordInput || !btnSubmit) return;
            const isEmailFilled = emailInput.value.trim() !== '';
            const isPasswordFilled = passwordInput.value.trim() !== '';

            if (isEmailFilled && isPasswordFilled) {
                btnSubmit.classList.add('active');
                btnSubmit.removeAttribute('disabled');
            } else {
                btnSubmit.classList.remove('active');
                btnSubmit.setAttribute('disabled', 'disabled');
            }
        }

        // Lắng nghe sự kiện gõ phím real-time
        if (emailInput && passwordInput) {
            emailInput.addEventListener('input', function () {
                clearFieldError('loginEmail');
                if (alertError) alertError.style.display = 'none';
                checkLoginFormFilled();
            });

            passwordInput.addEventListener('input', function () {
                clearFieldError('loginPassword');
                if (alertError) alertError.style.display = 'none';
                checkLoginFormFilled();
            });
        }

        // Validate Client & Gửi Fetch API khi nhấn Đăng nhập
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            let isValid = true;
            const emailVal = emailInput.value.trim();
            const passwordVal = passwordInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRegex = /^(0[3|5|7|8|9])+([0-9]{8})$/;

            if (alertError) alertError.style.display = 'none';
            clearFieldError('loginEmail');
            clearFieldError('loginPassword');

            if (!emailRegex.test(emailVal) && !phoneRegex.test(emailVal)) {
                showFieldError('loginEmail', 'Email hoặc số điện thoại không hợp lệ.');
                isValid = false;
            }

            if (passwordVal.length < 6) {
                showFieldError('loginPassword', 'Mật khẩu phải chứa ít nhất 6 ký tự.');
                isValid = false;
            }

            if (!isValid) return;

            // Khóa nút submit tạm thời
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.textContent = 'Đang xử lý...';
            }

            // GỬI REQUEST FETCH TỚI API LOGIN
            fetch('../api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: emailVal,
                    mat_khau: passwordVal
                })
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Email hoặc mật khẩu không chính xác.');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = 'home.php';
                    }
                })
                .catch(error => {
                    if (alertError) {
                        alertError.textContent = error.message;
                        alertError.style.display = 'block';
                    } else {
                        showFieldError('loginPassword', error.message);
                    }
                })
                .finally(() => {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = 'Tiếp tục';
                        checkLoginFormFilled();
                    }
                });
        });
    }

    // ----------------------------------------------------------------------
    // 2. XỬ LÝ GIAO DIỆN & KẾT NỐI API ĐĂNG KÝ (pages/register.php)
    // ----------------------------------------------------------------------
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const fullnameInput = document.getElementById('regFullname');
        const emailInput = document.getElementById('regEmail');
        const passwordInput = document.getElementById('regPassword');
        const confirmPasswordInput = document.getElementById('regConfirmPassword');
        const termsCheckbox = document.getElementById('regTerms');
        const btnSubmit = document.getElementById('btnRegisterSubmit');

        const alertError = document.getElementById('apiAlertError');
        const alertSuccess = document.getElementById('apiAlertSuccess');

        // Bảng quy chuẩn gợi ý mật khẩu 4 tiêu chí
        const pwdReqBox = document.getElementById('pwdRequirementsBox');
        const pwdReqTitle = document.getElementById('pwdReqTitle');
        const reqLength = document.getElementById('req-length');
        const reqLowerNum = document.getElementById('req-lower-num');
        const reqSpecial = document.getElementById('req-special');
        const reqUpper = document.getElementById('req-upper');

        // Kiểm tra 4 tiêu chí mật khẩu Realtime
        function checkPasswordRequirements(val) {
            if (!pwdReqBox) return false;
            const isLengthValid = val.length >= 8 && val.length <= 32;
            const hasLower = /[a-z]/.test(val);
            const hasNumber = /[0-9]/.test(val);
            const isLowerNumValid = hasLower && hasNumber;
            const isSpecialValid = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(val);
            const isUpperValid = /[A-Z]/.test(val);

            updateReqStatus(reqLength, isLengthValid);
            updateReqStatus(reqLowerNum, isLowerNumValid);
            updateReqStatus(reqSpecial, isSpecialValid);
            updateReqStatus(reqUpper, isUpperValid);

            const allValid = isLengthValid && isLowerNumValid && isSpecialValid && isUpperValid;

            if (pwdReqTitle) {
                if (allValid) {
                    pwdReqBox.classList.add('all-valid');
                    pwdReqTitle.innerHTML = '<i class="fa-solid fa-circle-check"></i> Mật khẩu đạt tiêu chuẩn';
                } else {
                    pwdReqBox.classList.remove('all-valid');
                    pwdReqTitle.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Mật khẩu chưa hợp lệ';
                }
            }

            return allValid;
        }

        function updateReqStatus(elem, isValid) {
            if (!elem) return;
            const icon = elem.querySelector('.icon-status');
            if (isValid) {
                elem.classList.add('valid');
                elem.classList.remove('invalid');
                if (icon) icon.className = 'fa-solid fa-circle-check icon-status';
            } else {
                elem.classList.add('invalid');
                elem.classList.remove('valid');
                if (icon) icon.className = 'fa-solid fa-circle-xmark icon-status';
            }
        }

        // Bật/Tắt màu nút Đăng Ký Realtime
        function checkRegisterFormFilled() {
            if (!fullnameInput || !emailInput || !passwordInput || !confirmPasswordInput || !termsCheckbox || !btnSubmit) return;
            const isPwdValid = checkPasswordRequirements(passwordInput.value);

            const isFilled = fullnameInput.value.trim() !== '' &&
                emailInput.value.trim() !== '' &&
                isPwdValid &&
                confirmPasswordInput.value.trim() !== '' &&
                termsCheckbox.checked;

            if (isFilled) {
                btnSubmit.classList.add('active');
                btnSubmit.removeAttribute('disabled');
            } else {
                btnSubmit.classList.remove('active');
                btnSubmit.setAttribute('disabled', 'disabled');
            }
        }

        // Lắng nghe thay đổi dữ liệu các ô input
        if (passwordInput) {
            passwordInput.addEventListener('input', function () {
                clearFieldError('regPassword');
                checkRegisterFormFilled();
            });
        }

        [fullnameInput, emailInput, confirmPasswordInput].forEach(input => {
            if (input) {
                input.addEventListener('input', function () {
                    clearFieldError(this.id);
                    checkRegisterFormFilled();
                });
            }
        });

        if (termsCheckbox) {
            termsCheckbox.addEventListener('change', function () {
                clearFieldError('regTerms');
                checkRegisterFormFilled();
            });
        }

        // Validate Chi tiết & Gửi Fetch API khi bấm Đăng ký
        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();

            let isValid = true;
            if (alertError) alertError.style.display = 'none';
            if (alertSuccess) alertSuccess.style.display = 'none';

            const hoTenVal = fullnameInput.value.trim();
            const emailVal = emailInput.value.trim();
            const passwordVal = passwordInput.value;
            const confirmVal = confirmPasswordInput.value;

            if (hoTenVal.length < 2) {
                showFieldError('regFullname', 'Họ và tên không được để trống.');
                isValid = false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRegex = /^(0[3|5|7|8|9])+([0-9]{8})$/;
            if (!emailRegex.test(emailVal) && !phoneRegex.test(emailVal)) {
                showFieldError('regEmail', 'Email hoặc số điện thoại không hợp lệ.');
                isValid = false;
            }

            if (!checkPasswordRequirements(passwordVal)) {
                showFieldError('regPassword', 'Mật khẩu chưa đáp ứng đầy đủ các quy chuẩn an toàn.');
                isValid = false;
            }

            if (confirmVal !== passwordVal) {
                showFieldError('regConfirmPassword', 'Mật khẩu xác nhận không trùng khớp.');
                isValid = false;
            }

            if (!termsCheckbox.checked) {
                showFieldError('regTerms', 'Bạn phải đồng ý với Điều khoản để tiếp tục.');
                isValid = false;
            }

            if (!isValid) return;

            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.textContent = 'Đang xử lý...';
            }

            // GỬI REQUEST FETCH TỚI API REGISTER
            fetch('../api/auth/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ho_ten: hoTenVal,
                    email: emailVal,
                    mat_khau: passwordVal
                })
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Đăng ký thất bại. Vui lòng thử lại.');
                    }
                    return data;
                })
                .then(data => {
                    if (alertSuccess) {
                        alertSuccess.textContent = data.message + ' Đang chuyển hướng sang trang đăng nhập...';
                        alertSuccess.style.display = 'block';
                    }
                    registerForm.reset();
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                })
                .catch(error => {
                    if (alertError) {
                        alertError.textContent = error.message;
                        alertError.style.display = 'block';
                    } else {
                        showFieldError('regEmail', error.message);
                    }
                })
                .finally(() => {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = 'Đăng ký';
                        checkRegisterFormFilled();
                    }
                });
        });
    }

    // ----------------------------------------------------------------------
    // 3. XỬ LÝ ĐĂNG XUẤT (templates/header.php)
    // ----------------------------------------------------------------------
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', function (e) {
            e.preventDefault();
            fetch('../api/auth/logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Đã có lỗi xảy ra khi đăng xuất.');
                    }
                    return data;
                })
                .then(data => {
                    window.location.href = 'login.php';
                })
                .catch(error => {
                    console.error('Lỗi đăng xuất:', error);
                    alert(error.message);
                });
        });
    }
});

// ----------------------------------------------------------------------
// 4. HÀM BỔ TRỢ TOÀN CỤC (GLOBAL FUNCTIONS)
// ----------------------------------------------------------------------
function showFieldError(inputId, message) {
    const inputElem = document.getElementById(inputId);
    if (!inputElem) return;
    const errorElem = document.getElementById('error-' + inputId);
    const groupElem = inputElem.closest('.form-group') || inputElem.closest('.form-checkbox-group');

    if (groupElem) groupElem.classList.add('has-error');
    if (errorElem) {
        errorElem.textContent = message;
        errorElem.style.color = '#ff4d4f';
    }
}

function clearFieldError(inputId) {
    const inputElem = document.getElementById(inputId);
    if (!inputElem) return;
    const errorElem = document.getElementById('error-' + inputId);
    const groupElem = inputElem.closest('.form-group') || inputElem.closest('.form-checkbox-group');

    if (groupElem) groupElem.classList.remove('has-error');
    if (errorElem) errorElem.textContent = '';
}

function toggleShowPassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

// ----------------------------------------------------------------------
// 5. XỬ LÝ GOOGLE IDENTITY SERVICES SDK
// ----------------------------------------------------------------------
function handleCredentialResponse(response) {
    const responsePayload = parseJwt(response.credential);
    const formData = new FormData();
    formData.append('action', 'google_login');
    formData.append('google_name', responsePayload.name);
    formData.append('google_email', responsePayload.email);

    fetch('login.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'home.php';
            }
        });
}

function parseJwt(token) {
    var base64Url = token.split('.')[1];
    var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function (c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
    return JSON.parse(jsonPayload);
}

/**
 * Kiểm tra tính hợp lệ của mật khẩu theo 4 tiêu chí
 * @param {string} password 
 * @returns {object} { isValid: boolean, message: string }
 */
function validatePassword(password) {
    if (password.length < 8 || password.length > 32) {
        return { isValid: false, message: "Mật khẩu phải có độ dài từ 8 đến 32 ký tự." };
    }
    if (!/[A-Z]/.test(password)) {
        return { isValid: false, message: "Mật khẩu phải chứa ít nhất một chữ cái viết hoa (A-Z)." };
    }
    if (!/[a-z]/.test(password)) {
        return { isValid: false, message: "Mật khẩu phải chứa ít nhất một chữ cái viết thường (a-z)." };
    }
    if (!/\d/.test(password)) {
        return { isValid: false, message: "Mật khẩu phải chứa ít nhất một chữ số (0-9)." };
    }
    if (!/[^a-zA-Z0-9]/.test(password)) {
        return { isValid: false, message: "Mật khẩu phải chứa ít nhất một ký tự đặc biệt (VD: @, #, $, !,...). " };
    }

    return { isValid: true, message: "" };
}

// Ví dụ bắt sự kiện khi submit form đăng ký hoặc khi nhập liệu (oninput)
document.getElementById('registerForm')?.addEventListener('submit', function (e) {
    const passwordInput = document.getElementById('password').value;
    const validation = validatePassword(passwordInput);

    if (!validation.isValid) {
        e.preventDefault();
        alert(validation.message); // Hoặc hiển thị thông báo lỗi vào DOM UI tương ứng
    }
});