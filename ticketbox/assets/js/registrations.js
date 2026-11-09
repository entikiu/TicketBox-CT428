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