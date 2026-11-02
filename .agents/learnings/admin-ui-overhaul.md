# Admin UI Overhaul

> Tổng hợp kiến thức về đợt đại tu (UI/UX Pro Max Overhaul) các module quản trị như Quảng cáo, Khuyến mãi, SEO, Chatting.
> Cập nhật lần cuối: 2026-09-17

---

## Architecture

### Chuyển đổi dữ liệu Mock sang Real API & Audit Log
- **Ngày**: 2026-09-17
- **Chi tiết**: Các trang Frontend quản trị đã được loại bỏ hoàn toàn dữ liệu giả, thay thế bằng các `apiFetch` calls trực tiếp tới backend MongoDB (thông qua Mongoose). Đồng thời, bổ sung model `AdminLog` để tracking toàn bộ hoạt động (CRUD, Đăng nhập) của admin nhân sự.
- **Files liên quan**: `backend/models/admin-log.model.js`, `frontend/src/admin/pages/vouchers/PlatformVouchers.js`

### Kiến trúc dự phòng cho Chat (Socket + REST Fallback)
- **Ngày**: 2026-09-17
- **Chi tiết**: Ở module Hỗ trợ trực tuyến (Chatting), kết hợp sử dụng `socket.io-client` để emit tin nhắn realtime (`send_message`), đồng thời gọi REST API POST `/api/admin/chat/:room` để đảm bảo tin nhắn luôn được lưu trữ thành công vào CSDL kể cả khi socket gặp sự cố.
- **Files liên quan**: `frontend/src/admin/pages/chatting/Chatting.js`, `backend/controllers/admin/chat.controller.js`

---

## Bugs & Solutions

### Lỗi hiển thị sai màu background icon trên tiêu đề trang
- **Ngày**: 2026-09-17
- **Vấn đề**: Icon ở các thẻ `<h1 className="adm-page-title">` có nền màu xanh dương lệch tông so với màu chủ đạo của hệ thống (`system accent`).
- **Root cause**: Thuộc tính CSS inline (`background`, `padding`, `borderRadius`) được apply trực tiếp vào thẻ `<i>` chứa icon, khiến background bị đè cứng màu (hardcoded variable) thay vì trong suốt.
- **Fix**: Loại bỏ hoàn toàn thuộc tính `background`, `padding`, và `borderRadius` ra khỏi thẻ `<i>`, giữ lại thuộc tính `color: "var(--adm-accent)"` để đồng nhất với hệ thống.
- **Files liên quan**: `frontend/src/admin/pages/ads/advertisement.js`, `frontend/src/admin/pages/chatting/Chatting.js`, `frontend/src/admin/pages/seo/SeoList.js`

---

## How-To

### Nâng cấp một Component lên chuẩn UI/UX Pro Max
- **Ngày**: 2026-09-17
- **Bước thực hiện**:
  1. Loại bỏ các file CSS riêng lẻ thô cứng, import stylesheet tiêu chuẩn: `import "../../css/shared/admin-components.css";`
  2. Bọc toàn bộ page bằng class `adm-page`.
  3. Xây dựng Header chuẩn với `adm-page-header`, `adm-page-title`, và `adm-page-sub`.
  4. Sử dụng `adm-card` cho các khu vực nội dung, kết hợp `adm-table` cho danh sách dữ liệu.
  5. Thêm các Dashboard Stats (Thẻ thống kê thông minh) ở phía trên table để tổng hợp số liệu trực quan (ví dụ: số voucher đang chạy, số bài viết SEO draft).
  6. Áp dụng `ToastProvider` (`notifyApp`) thay thế cho `alert()` mặc định của trình duyệt để có trải nghiệm thông báo chuyên nghiệp.
- **Files liên quan**: `frontend/src/admin/pages/vouchers/PlatformVouchers.js`

---

## Patterns

### Mẫu thiết kế Optimistic UI (Cập nhật giao diện lạc quan)
- **Ngày**: 2026-09-17
- **Chi tiết**: Trong tính năng gửi tin nhắn, áp dụng pattern Optimistic UI bằng cách ngay lập tức đưa tin nhắn mới vào mảng `messages` và hiển thị ra UI, sau đó mới gọi API và emit qua Socket. Điều này mang lại cảm giác cực nhanh cho user.
- **Ví dụ code**:
```javascript
const sendMessage = async () => {
    // 1. Render ngay lập tức ra UI
    const tempId = Date.now().toString();
    const newMsg = { id: tempId, from: "admin", text: input.trim(), time };
    setMessages(prev => [...prev, newMsg]);
    setInput("");

    // 2. Gửi qua mạng ngầm
    try {
        await apiFetch(`/api/admin/chat/${activeRoom}`, { method: "POST", body: JSON.stringify({ text }) });
        socket.emit("send_message", payload);
    } catch (err) {
        notifyApp("Lỗi gửi tin nhắn", "error");
    }
};
```

### Tích hợp TinyMCE chuẩn chỉ
- **Ngày**: 2026-09-17
- **Chi tiết**: Bọc TinyMCE Editor trong một card có style `overflow: hidden; border: none;` để ẩn viền mặc định. Cấu hình `content_style` cho khớp font chữ `Inter` của hệ thống.
- **Ví dụ code**:
```javascript
<div className="adm-card" style={{overflow: 'hidden'}}>
  <Editor
    init={{
      content_style: "body { font-family:Inter,sans-serif; font-size:16px; color: #334155; }",
      skin: "oxide",
      border: 'none'
    }}
  />
</div>
```
