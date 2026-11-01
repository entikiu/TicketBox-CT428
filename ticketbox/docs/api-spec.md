
# Tài liệu API - TicketBox CT428
# Đặc tả đề xuất


## 1. Đăng ký sự kiện

Endpoint: POST /api/registrations/register.php

Request:
{
  "event_id": 1
}

Response thành công:
{
  "success": true,
  "message": "Đăng ký thành công",
  "data": {
    "ticket_code": "TB-ABC123"
  }
}

## 2. Hủy đăng ký

Endpoint: POST /api/registrations/cancel.php

Request:
{
  "registration_id": 1
}

Response:
{
  "success": true,
  "message": "Hủy đăng ký thành công"
}

## 3. Xem vé cá nhân

Endpoint: GET /api/registrations/my-tickets.php

Yêu cầu:
- Người dùng đã đăng nhập.
- Chỉ trả về vé của người dùng hiện tại.

## 4. Quy tắc nghiệp vụ

- Phải đăng nhập trước khi đăng ký vé.
- Không được đăng ký trùng vé hợp lệ.
- Không được đăng ký khi sự kiện hết chỗ.
- Chỉ chủ sở hữu vé mới được hủy vé.
- Vé đã check-in không được hủy.
- Vé đã hủy có thể đăng ký lại nếu còn chỗ.
