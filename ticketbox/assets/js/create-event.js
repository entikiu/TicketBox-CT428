let currentStep = 1;
let quill = null;

function showToast(msg, isError = false) {
    const box = document.getElementById('toastBox');
    const icon = document.getElementById('toastIcon');
    const text = document.getElementById('toastText');
    text.textContent = msg;
    if (isError) {
        box.classList.add('error');
        icon.className = 'fa-solid fa-circle-exclamation';
        icon.style.color = '#ff4d4f';
    } else {
        box.classList.remove('error');
        icon.className = 'fa-solid fa-circle-check';
        icon.style.color = '#26c973';
    }
    box.style.display = 'flex';
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => {
        box.style.display = 'none';
    }, 3500);
}

function updateStepUI(step) {
    currentStep = step;
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    const activePanel = document.getElementById('stepPanel' + step);
    if (activePanel) activePanel.classList.add('active');

    document.querySelectorAll('.step-indicator .step').forEach(el => {
        const s = parseInt(el.getAttribute('data-step'), 10);
        el.classList.remove('active', 'completed');
        if (s === step) el.classList.add('active');
        else if (s < step) el.classList.add('completed');
    });

    const prevBtn = document.getElementById('btnPrevStep');
    const nextBtn = document.getElementById('btnNextStep');
    const saveBtn = document.getElementById('btnSaveDraft');

    if (prevBtn) prevBtn.style.display = step > 1 ? 'inline-block' : 'none';
    if (saveBtn) saveBtn.textContent = step === 4 ? 'Lưu nháp' : 'Lưu';
    if (nextBtn) nextBtn.textContent = step === 4 ? 'Hoàn tất & Xuất bản' : 'Tiếp tục';
}

function prevStep() {
    if (currentStep > 1) {
        updateStepUI(currentStep - 1);
    }
}

function goToStep(targetStep) {
    const eventId = parseInt(document.getElementById('currentEventId')?.value || '0', 10);
    if (targetStep > 1 && eventId <= 0) {
        showToast('Vui lòng nhập và lưu thông tin Bước 1 trước khi chuyển bước.', true);
        return;
    }
    updateStepUI(targetStep);
}

// Thêm thẻ hạng vé động tại Bước 2
let ticketCounter = 0;
function addTicketItem(data = {}) {
    const container = document.getElementById('ticketsContainer');
    if (!container) return;
    ticketCounter++;
    const idx = ticketCounter;

    const nameVal = data.ten_loai_ve || '';
    const priceVal = data.gia_ve !== undefined ? data.gia_ve : '';
    const qtyVal = data.so_luong || 100;
    const descVal = data.mo_ta || '';
    const saleStart = data.bat_dau_ban ? data.bat_dau_ban.replace(' ', 'T').slice(0, 16) : '';
    const saleEnd = data.ket_thuc_ban ? data.ket_thuc_ban.replace(' ', 'T').slice(0, 16) : '';

    const card = document.createElement('div');
    card.className = 'ticket-item-card';
    card.innerHTML = `
        <div class="ticket-item-header">
            <strong style="color: #26c973; font-size: 14px;"><i class="fa-solid fa-ticket"></i> Hạng vé #${idx}</strong>
            <button type="button" class="btn-remove-ticket" onclick="removeTicketItem(this)">
                <i class="fa-solid fa-trash"></i> Xóa vé
            </button>
        </div>
        <div class="form-row-2">
            <div class="form-group">
                <label><span>*</span> Tên loại vé</label>
                <input type="text" class="form-control t-name" placeholder="Ví dụ: Vé VIP, Vé Tiêu Chuẩn..." value="${nameVal.replace(/"/g, '&quot;')}">
            </div>
            <div class="form-row-2" style="gap: 12px;">
                <div class="form-group">
                    <label><span>*</span> Giá vé (VNĐ)</label>
                    <input type="number" min="0" step="1000" class="form-control t-price" placeholder="500000" value="${priceVal}">
                </div>
                <div class="form-group">
                    <label><span>*</span> Tổng số lượng vé</label>
                    <input type="number" min="1" class="form-control t-qty" placeholder="100" value="${qtyVal}">
                </div>
            </div>
        </div>
        <div class="form-row-2">
            <div class="form-group">
                <label>Thời gian bắt đầu mở bán</label>
                <input type="datetime-local" class="form-control t-sale-start" value="${saleStart}">
            </div>
            <div class="form-group">
                <label>Thời gian kết thúc bán vé</label>
                <input type="datetime-local" class="form-control t-sale-end" value="${saleEnd}">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label>Mô tả quyền lợi hạng vé</label>
            <input type="text" class="form-control t-desc" placeholder="Ví dụ: Kèm quà tặng lightstick, check-in lối riêng..." value="${descVal.replace(/"/g, '&quot;')}">
        </div>
    `;
    container.appendChild(card);
}

function removeTicketItem(btn) {
    const container = document.getElementById('ticketsContainer');
    if (container.children.length <= 1) {
        showToast('Sự kiện phải có ít nhất 1 loại vé.', true);
        return;
    }
    btn.closest('.ticket-item-card').remove();
}

function collectTicketsData() {
    const cards = document.querySelectorAll('#ticketsContainer .ticket-item-card');
    const list = [];
    cards.forEach(card => {
        list.push({
            name: card.querySelector('.t-name').value.trim(),
            price: parseInt(card.querySelector('.t-price').value || '0', 10),
            qty: parseInt(card.querySelector('.t-qty').value || '0', 10),
            sale_start: card.querySelector('.t-sale-start').value,
            sale_end: card.querySelector('.t-sale-end').value,
            desc: card.querySelector('.t-desc').value.trim()
        });
    });
    return list;
}

async function saveCurrentStep(advanceNext = false, publishMode = 'cong_khai') {
    const eventIdInput = document.getElementById('currentEventId');
    const eventId = parseInt(eventIdInput.value || '0', 10);
    const formData = new FormData();
    formData.append('event_id', eventId);

    if (currentStep === 1) {
        formData.append('ajax_action', 'save_step_1');
        formData.append('event_name', document.getElementById('eventName').value);
        formData.append('category', document.getElementById('eventCategory').value);
        formData.append('event_description', quill ? quill.root.innerHTML : document.getElementById('eventDescription').value);
        formData.append('event_mode', document.getElementById('eventMode').value);
        formData.append('organizer_name', document.getElementById('organizerName').value);
        formData.append('organizer_info', document.getElementById('organizerInfo').value);

        if (document.getElementById('eventMode').value === 'online') {
            formData.append('online_url', document.getElementById('onlineUrl')?.value || '');
        } else {
            formData.append('venue_name', document.getElementById('venueName')?.value || '');
            formData.append('street_address', document.getElementById('streetAddress')?.value || '');
            formData.append('ward', document.getElementById('wardInput')?.value || '');
            formData.append('district', document.getElementById('districtInput')?.value || '');
            formData.append('city', document.getElementById('cityInput')?.value || '');
        }

        const posterFile = document.getElementById('inputPoster').files[0];
        const bannerFile = document.getElementById('inputBanner').files[0];
        if (posterFile) formData.append('poster', posterFile);
        if (bannerFile) formData.append('banner', bannerFile);

    } else if (currentStep === 2) {
        formData.append('ajax_action', 'save_step_2');
        formData.append('start_time', document.getElementById('eventStartTime').value);
        formData.append('end_time', document.getElementById('eventEndTime').value);
        formData.append('tickets_json', JSON.stringify(collectTicketsData()));

    } else if (currentStep === 3) {
        formData.append('ajax_action', 'save_step_3');
        formData.append('custom_url', document.getElementById('customUrlInput').value);
        const privEl = document.querySelector('input[name="eventPrivacy"]:checked');
        formData.append('privacy', privEl ? privEl.value : 'public');
        formData.append('thank_you_msg', document.getElementById('thankYouMsg').value);

    } else if (currentStep === 4) {
        formData.append('ajax_action', 'publish_event');
        formData.append('publish_mode', publishMode);
        formData.append('account_holder', document.getElementById('bankHolder').value);
        formData.append('account_number', document.getElementById('bankNumber').value);
        formData.append('bank_name', document.getElementById('bankName').value);
        formData.append('bank_branch', document.getElementById('bankBranch').value);
        formData.append('business_type', document.getElementById('businessType').value);
        formData.append('tax_code', document.getElementById('taxCode').value);
    }

    const btnNext = document.getElementById('btnNextStep');
    const btnSave = document.getElementById('btnSaveDraft');
    btnNext.disabled = true;
    btnSave.disabled = true;

    try {
        const res = await fetch('create-event.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.status === 'success') {
            if (data.event_id) {
                eventIdInput.value = data.event_id;
            }
            showToast(data.message);

            if (currentStep === 4 && data.redirect_url) {
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1000);
            } else if (advanceNext && currentStep < 4) {
                updateStepUI(currentStep + 1);
            }
        } else {
            showToast(data.message || 'Có lỗi xảy ra.', true);
        }
    } catch (err) {
        showToast('Lỗi kết nối máy chủ.', true);
    } finally {
        btnNext.disabled = false;
        btnSave.disabled = false;
    }
}

async function deleteOrganizerEvent(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa sự kiện này?')) return;
    const fd = new FormData();
    fd.append('ajax_action', 'delete_event');
    fd.append('event_id', id);
    const res = await fetch('create-event.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.status === 'success') {
        document.getElementById('org-card-' + id)?.remove();
        showToast(data.message);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const editorEl = document.getElementById('editor-container');
    if (editorEl) {
        quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Nhập giới thiệu sự kiện, chi tiết chương trình, khách mời...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        });

        quill.on('text-change', function () {
            document.getElementById('eventDescription').value = quill.root.innerHTML;
        });
    }

    // Preview ảnh Upload
    function setupImageUpload(inputId, boxId) {
        const input = document.getElementById(inputId);
        const box = document.getElementById(boxId);
        if (!input || !box) return;

        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                showToast('Vui lòng tải lên định dạng hình ảnh (JPG, PNG, WEBP).', true);
                input.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showToast('Dung lượng ảnh tối đa là 5MB.', true);
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
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

    // Đếm ký tự tên sự kiện
    const eventNameInput = document.getElementById('eventName');
    const eventNameCount = document.getElementById('eventNameCount');
    if (eventNameInput && eventNameCount) {
        eventNameInput.addEventListener('input', function () {
            const len = this.value.length;
            eventNameCount.textContent = `${len} / 100`;
            eventNameCount.style.color = len >= 100 ? '#ff4d4f' : '#a0a3bd';
        });
    }

    // Chuyển đổi Offline / Online
    const btnOffline = document.getElementById('btnOffline');
    const btnOnline = document.getElementById('btnOnline');
    const locationFields = document.getElementById('locationFields');
    const eventModeInput = document.getElementById('eventMode');

    if (btnOnline && btnOffline && locationFields) {
        btnOnline.addEventListener('click', function () {
            eventModeInput.value = 'online';
            btnOnline.style.background = '#26c973';
            btnOffline.style.background = '#3a3a45';
            locationFields.innerHTML = `
                <div class="form-group">
                    <label><span>*</span> Đường dẫn sự kiện trực tuyến (Zoom, Google Meet, Microsoft Teams,...)</label>
                    <input type="url" class="form-control" id="onlineUrl" placeholder="https://...">
                </div>
            `;
        });

        btnOffline.addEventListener('click', function () {
            eventModeInput.value = 'offline';
            btnOffline.style.background = '#26c973';
            btnOnline.style.background = '#3a3a45';
            locationFields.innerHTML = `
                <div class="form-group">
                    <label>Tên tòa nhà / Địa điểm cụ thể</label>
                    <input type="text" class="form-control" id="venueName" placeholder="Ví dụ: Hội trường A - Trường Đại học Cần Thơ">
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label><span>*</span> Số nhà, tên đường</label>
                        <input type="text" class="form-control" id="streetAddress" placeholder="Ví dụ: Số 3 đường 3/2">
                    </div>
                    <div class="form-group">
                        <label><span>*</span> Phường / Xã</label>
                        <input type="text" class="form-control" id="wardInput" placeholder="Ví dụ: Phường Xuân Khánh">
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label><span>*</span> Quận / Huyện</label>
                        <input type="text" class="form-control" id="districtInput" placeholder="Ví dụ: Quận Ninh Kiều">
                    </div>
                    <div class="form-group">
                        <label><span>*</span> Tỉnh / Thành phố</label>
                        <input type="text" class="form-control" id="cityInput" placeholder="Ví dụ: Thành phố Cần Thơ">
                    </div>
                </div>
            `;
        });
    }

    // Khởi tạo danh sách hạng vé ở Bước 2
    const ticketsContainer = document.getElementById('ticketsContainer');
    if (ticketsContainer) {
        let initialTickets = [];
        try {
            initialTickets = JSON.parse(ticketsContainer.dataset.initialTickets || '[]');
        } catch (e) {
            initialTickets = [];
        }

        if (Array.isArray(initialTickets) && initialTickets.length > 0) {
            initialTickets.forEach(t => addTicketItem(t));
        } else {
            addTicketItem();
        }
    }

    // Nút Lưu & Tiếp tục
    document.getElementById('btnSaveDraft')?.addEventListener('click', function () {
        saveCurrentStep(false, 'nhap');
    });

    document.getElementById('btnNextStep')?.addEventListener('click', function () {
        saveCurrentStep(true, 'cong_khai');
    });
});