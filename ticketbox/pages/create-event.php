<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo sự kiện - Organizer Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: #111115;
            color: #ffffff;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* --- SIDEBAR TRÁI --- */
        .sidebar {
            width: 260px;
            background-color: #18181c;
            border-right: 1px solid #2a2a32;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #2a2a32;
            cursor: pointer;
        }

        .sidebar-header img {
            width: 30px;
        }

        .sidebar-header h3 {
            color: #26c973;
            font-size: 16px;
            font-weight: 700;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            flex-grow: 1;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #a0a3bd;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li.active a {
            background-color: rgba(38, 201, 115, 0.1);
            color: #26c973;
            border-left: 3px solid #26c973;
        }

        .sidebar-menu li a i {
            margin-right: 15px;
            font-size: 16px;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #2a2a32;
        }

        /* --- NỘI DUNG CHÍNH --- */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background: radial-gradient(circle at top right, #1a1a24, #111115);
        }

        /* --- HEADER BÊN TRONG --- */
        .top-header {
            display: flex;
            justify-content: flex-end;
            padding: 15px 30px;
            border-bottom: 1px solid #2a2a32;
            background-color: #111115;
            align-items: center;
            gap: 15px;
        }

        .btn-create {
            background-color: #26c973;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }

        .user-profile img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #2a2a32;
        }

        /* --- THANH TIẾN TRÌNH BƯỚC --- */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            padding: 20px 40px;
            background-color: #111115;
            border-bottom: 1px solid #2a2a32;
            align-items: center;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #a0a3bd;
            font-size: 14px;
            font-weight: 600;
        }

        .step.active {
            color: #fff;
        }

        .step-number {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #2a2a32;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .step.active .step-number {
            background-color: #26c973;
            color: #fff;
        }

        .step-actions {
            display: flex;
            gap: 10px;
        }

        .btn-save {
            background: #fff;
            color: #111;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-next {
            background: #26c973;
            color: #fff;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }

        /* --- KHUNG NHẬP FORM --- */
        .form-container {
            padding: 30px 40px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .form-section {
            background-color: #18181c;
            border: 1px solid #2a2a32;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .section-title {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .section-title span {
            color: #ff4d4f;
            margin-right: 5px;
        }

        /* Khung Upload Ảnh */
        .upload-wrapper {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .upload-box {
            border: 1px dashed #4a4a55;
            border-radius: 8px;
            background-color: #222228;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            color: #a0a3bd;
            padding: 30px;
        }

        .upload-box:hover {
            border-color: #26c973;
            background-color: rgba(38, 201, 115, 0.05);
        }

        .upload-box i {
            font-size: 30px;
            color: #26c973;
            margin-bottom: 15px;
        }

        .upload-box.poster {
            width: 30%;
            height: 350px;
        }

        .upload-box.banner {
            width: 70%;
            height: 350px;
        }

        /* Input Fields */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 8px;
        }

        .form-group label span {
            color: #ff4d4f;
        }

        .form-control {
            width: 100%;
            background-color: #fff;
            border: none;
            padding: 12px 15px;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
            outline: none;
        }

        .form-control:focus {
            box-shadow: 0 0 0 2px rgba(38, 201, 115, 0.5);
        }
    </style>
</head>

<body>

    <!-- SIDEBAR TRÁI -->
    <div class="sidebar">
        <div>
            <div class="sidebar-header">
                <i class="fa-solid fa-ticket" style="color: #26c973; font-size: 20px;"></i>
                <h3>Organizer Center</h3>
                <i class="fa-solid fa-chevron-down" style="color: #a0a3bd; font-size: 12px; margin-left: auto;"></i>
            </div>

            <ul class="sidebar-menu">
                <li class="active">
                    <a href="#"><i class="fa-solid fa-calendar-star"></i> Sự kiện của tôi</a>
                </li>
                <li>
                    <a href="#"><i class="fa-solid fa-folder-open"></i> Quản lý báo cáo</a>
                </li>
                <li>
                    <a href="#"><i class="fa-solid fa-file-contract"></i> Điều khoản cho Ban tổ chức</a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <span style="color: #a0a3bd; font-size: 13px;">Ngôn ngữ: </span>
            <span style="background: #2a2a32; padding: 4px 10px; border-radius: 12px; font-size: 12px; float:right;">Vie 🇻🇳</span>
        </div>
    </div>

    <!-- NỘI DUNG CHÍNH -->
    <div class="main-content">

        <!-- TOP HEADER -->
        <div class="top-header">
            <button class="btn-create"><i class="fa-solid fa-plus"></i> Tạo sự kiện</button>
            <div class="user-profile">
                <img src="../assets/images/default-avatar.png" alt="Tài khoản">
                Tài khoản <i class="fa-solid fa-caret-down" style="font-size: 12px;"></i>
            </div>
        </div>

        <!-- THANH TIẾN TRÌNH -->
        <div class="step-indicator">
            <div class="step active">
                <div class="step-number">1</div>
                Thông tin sự kiện
            </div>
            <div class="step">
                <div class="step-number">2</div>
                Thời gian & Loại vé
            </div>
            <div class="step">
                <div class="step-number">3</div>
                Cài đặt
            </div>
            <div class="step">
                <div class="step-number">4</div>
                Thông tin thanh toán
            </div>

            <div class="step-actions">
                <button class="btn-save">Lưu</button>
                <button class="btn-next">Tiếp tục</button>
            </div>
        </div>

        <!-- FORM NHẬP LIỆU -->
        <div class="form-container">
            <form>

                <!-- BỘ UPLOAD ẢNH -->
                <div class="form-section">
                    <div class="section-title"><span>*</span> Upload hình ảnh <a href="#" style="color:#26c973; font-weight:normal; margin-left: 10px; text-decoration: none;">Xem vị trí hiển thị các ảnh</a></div>

                    <div class="upload-wrapper">
                        <!-- Khung Poster -->
                        <div class="upload-box poster" id="boxPoster" onclick="document.getElementById('inputPoster').click()">
                            <i class="fa-solid fa-box-archive"></i>
                            <p style="font-size: 13px; font-weight: 500;">Thêm ảnh sự kiện<br>(720x958)</p>
                        </div>
                        <input type="file" id="inputPoster" name="poster" accept="image/jpeg, image/png, image/webp" hidden>

                        <!-- Khung Banner -->
                        <div class="upload-box banner" id="boxBanner" onclick="document.getElementById('inputBanner').click()">
                            <i class="fa-solid fa-box-archive"></i>
                            <p style="font-size: 13px; font-weight: 500;">Thêm ảnh nền sự kiện<br>(1280x720)</p>
                        </div>
                        <input type="file" id="inputBanner" name="banner" accept="image/jpeg, image/png, image/webp" hidden>
                    </div>

                    <!-- Tên sự kiện -->
                    <div class="form-group">
                        <label><span>*</span> Tên sự kiện</label>
                        <div style="position: relative;">
                            <input type="text" id="eventName" name="event_name" class="form-control" placeholder="Nhập tên sự kiện" maxlength="100">
                            <span id="eventNameCount" style="position: absolute; right: 15px; top: 12px; font-size: 12px; color: #a0a3bd;">0 / 100</span>
                        </div>
                    </div>

                    <!-- Trình soạn thảo văn bản (Quill.js) -->
                    <div class="form-group">
                        <label><span>*</span> Thông tin sự kiện</label>
                        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
                        <div id="editor-container" style="height: 300px; background: #fff; color: #333; border-radius: 0 0 4px 4px;"></div>
                        <input type="hidden" name="event_description" id="eventDescription">
                    </div>
                </div>

                <!-- ĐỊA ĐIỂM -->
                <div class="form-section">
                    <div class="section-title"><span>*</span> Hình thức & Địa chỉ sự kiện</div>
                    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <button type="button" id="btnOffline" style="background:#26c973; color:#fff; border:none; padding:8px 25px; border-radius:4px; font-weight:bold; cursor:pointer;"><i class="fa-regular fa-map" style="margin-right:5px;"></i> Offline</button>
                        <button type="button" id="btnOnline" style="background:#3a3a45; color:#fff; border:none; padding:8px 25px; border-radius:4px; font-weight:bold; cursor:pointer;"><i class="fa-regular fa-circle-play" style="margin-right:5px;"></i> Online</button>
                    </div>

                    <!-- Khu vực thay đổi động giữa Offline và Online -->
                    <div id="locationFields">
                        <div class="form-group">
                            <label>Tên tòa nhà / Địa điểm cụ thể</label>
                            <input type="text" class="form-control" name="venue_name" placeholder="Ví dụ: Hội trường A - Trường Đại học Cần Thơ">
                        </div>

                        <div class="form-group">
                            <label><span>*</span> Số nhà, tên đường</label>
                            <input type="text" class="form-control" name="street_address" placeholder="Ví dụ: Số 3 đường 3/2">
                        </div>

                        <div class="form-group">
                            <label><span>*</span> Phường / Xã</label>
                            <input type="text" class="form-control" name="ward" placeholder="Ví dụ: Phường Xuân Khánh">
                        </div>

                        <div class="form-group">
                            <label><span>*</span> Quận / Huyện</label>
                            <input type="text" class="form-control" name="district" placeholder="Ví dụ: Quận Ninh Kiều">
                        </div>

                        <div class="form-group">
                            <label><span>*</span> Tỉnh / Thành phố</label>
                            <input type="text" class="form-control" name="city" placeholder="Ví dụ: Thành phố Cần Thơ">
                        </div>
                    </div>
                </div>

            </form>
        </div>

    </div>

</body>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {

    // 1. KHỞI TẠO TRÌNH SOẠN THẢO VĂN BẢN (RICH TEXT EDITOR)
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Nhập giới thiệu sự kiện, chi tiết chương trình, khách mời...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });

    // Cập nhật dữ liệu từ Quill vào thẻ input hidden trước khi submit
    quill.on('text-change', function() {
        document.getElementById('eventDescription').value = quill.root.innerHTML;
    });

    // 2. XỬ LÝ PREVIEW ẢNH UPLOAD (KÈM VALIDATE DUNG LƯỢNG)
    function setupImageUpload(inputId, boxId) {
        const input = document.getElementById(inputId);
        const box = document.getElementById(boxId);

        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('Vui lòng tải lên định dạng hình ảnh (JPG, PNG).');
                input.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('Dung lượng ảnh tối đa là 5MB.');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                box.style.backgroundImage = `url('${event.target.result}')`;
                box.style.backgroundSize = 'cover';
                box.style.backgroundPosition = 'center';
                box.innerHTML = ''; 
                box.style.border = 'none'; 
            };
            reader.readAsDataURL(file);
        });
    }
    setupImageUpload('inputPoster', 'boxPoster');
    setupImageUpload('inputBanner', 'boxBanner');

    // 3. ĐẾM KÝ TỰ THỜI GIAN THỰC
    const eventNameInput = document.getElementById('eventName');
    const eventNameCount = document.getElementById('eventNameCount');
    
    eventNameInput.addEventListener('input', function() {
        const len = this.value.length;
        eventNameCount.textContent = `${len} / 100`;
        if (len >= 100) {
            eventNameCount.style.color = '#ff4d4f'; 
        } else {
            eventNameCount.style.color = '#a0a3bd';
        }
    });

    // 4. CHUYỂN ĐỔI OFFLINE / ONLINE
    const btnOffline = document.getElementById('btnOffline');
    const btnOnline = document.getElementById('btnOnline');
    const locationFields = document.getElementById('locationFields'); 

    btnOnline.addEventListener('click', function() {
        btnOnline.style.background = '#26c973';
        btnOffline.style.background = '#3a3a45';
        locationFields.innerHTML = `
            <div class="form-group">
                <label><span>*</span> Đường dẫn sự kiện trực tuyến (Zoom, Google Meet, Microsoft Teams,...)</label>
                <input type="url" class="form-control" name="online_url" placeholder="https://...">
            </div>
        `;
    });

    btnOffline.addEventListener('click', function() {
        btnOffline.style.background = '#26c973';
        btnOnline.style.background = '#3a3a45';
        locationFields.innerHTML = `
            <div class="form-group">
                <label>Tên tòa nhà / Địa điểm cụ thể</label>
                <input type="text" class="form-control" name="venue_name" placeholder="Ví dụ: Hội trường A - Trường Đại học Cần Thơ">
            </div>
            <div class="form-group">
                <label><span>*</span> Số nhà, tên đường</label>
                <input type="text" class="form-control" name="street_address" placeholder="Ví dụ: Số 3 đường 3/2">
            </div>
            <div class="form-group">
                <label><span>*</span> Phường / Xã</label>
                <input type="text" class="form-control" name="ward" placeholder="Ví dụ: Phường Xuân Khánh">
            </div>
            <div class="form-group">
                <label><span>*</span> Quận / Huyện</label>
                <input type="text" class="form-control" name="district" placeholder="Ví dụ: Quận Ninh Kiều">
            </div>
            <div class="form-group">
                <label><span>*</span> Tỉnh / Thành phố</label>
                <input type="text" class="form-control" name="city" placeholder="Ví dụ: Thành phố Cần Thơ">
            </div>
        `;
    });
});

// Xử lý nút Lưu (AJAX)
document.querySelector('.btn-save').addEventListener('click', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('event_name', document.getElementById('eventName').value);
    formData.append('event_description', document.getElementById('eventDescription').value);
    
    const posterInput = document.getElementById('inputPoster');
    if(posterInput.files.length > 0) {
        formData.append('poster', posterInput.files[0]);
    }

    const btn = this;
    const originalText = btn.innerText;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';
    btn.disabled = true;

    fetch('ajax/save_event_step1.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            alert('Đã lưu thông tin sự kiện!');
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        alert('Lỗi kết nối mạng.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>
</html>