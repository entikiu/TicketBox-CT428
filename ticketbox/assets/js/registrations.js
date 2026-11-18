async function registerEvent(eventId) {
    try {
        const response = await fetch("../api/registrations/register.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "same-origin",
            body: JSON.stringify({
                event_id: eventId
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            alert(result.message || "Đăng ký thất bại.");
            return;
        }

        alert("Đăng ký vé thành công! Mã vé: " + (result.data ? result.data.ticket_code : ""));
        window.location.reload();

    } catch (error) {
        console.error(error);
        alert("Không thể kết nối đến máy chủ API.");
    }
}

/**
 * Cancel a ticket registration via AJAX.
 * @param {number} ticketId - ID of the ticket (ve.id).
 * @param {HTMLButtonElement} [btn] - The clicked button (disabled while the request runs).
 */
async function cancelTicket(ticketId, btn) {
    ticketId = parseInt(ticketId, 10);
    if (!ticketId || ticketId < 1) {
        alert('Không xác định được vé cần hủy.');
        return;
    }
    if (!confirm('Bạn có chắc chắn muốn hủy vé này? Thao tác này không thể hoàn tác.')) return;

    const originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang hủy...';
    }

    try {
        const response = await fetch('../api/registrations/cancel.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ registration_id: ticketId })
        });

        let data = {};
        try {
            data = await response.json();
        } catch (parseErr) {
            data = { success: false, message: 'Phản hồi từ máy chủ không hợp lệ (HTTP ' + response.status + ').' };
        }

        if (response.ok && data.success) {
            alert('Hủy vé thành công!');
            window.location.reload();
            return;
        }

        if (response.status === 401) {
            alert(data.message || 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
            window.location.href = 'login.php';
            return;
        }

        alert(data.message || 'Không thể hủy vé.');
    } catch (err) {
        console.error(err);
        alert('Lỗi kết nối tới server.');
    }

    if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}
