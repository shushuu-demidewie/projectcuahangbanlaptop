---
name: ai-learnings
description: Tự động trích xuất, tổng hợp và lưu trữ bài học (learnings) từ cuộc hội thoại hiện tại vào thư mục .agents/learnings/ theo chủ đề để tái sử dụng cho các phiên làm việc sau.
---

# Quy trình tự động Lưu Learnings từ Conversation vào `.agents/learnings/`

Lưu learnings từ conversation hiện tại vào `.agents/learnings/` theo chủ đề.

---

## Bước 1: Scan file hiện tại & Xác định Feature

### 1.1: Scan thư mục learnings
AI **PHẢI** đọc danh sách file trong `.agents/learnings/` trước tiên để biết những feature nào đã có file.

### 1.2: Xác định feature từ conversation
AI phân tích conversation hiện tại và xác định tên feature/chức năng chính đã thực hiện.

### 1.3: Quyết định tạo mới hay cập nhật
- **Nếu đã có file chứa feature đó** (ví dụ: conversation về notification mà `notification.md` đã tồn tại) $\rightarrow$ **Cập nhật file đó**. KHÔNG tạo file mới.
- **Nếu chưa có file nào liên quan** $\rightarrow$ Tạo file mới với tên **kebab-case**, ngắn gọn.
  - *Ví dụ*: `notification.md`, `user-segmentation.md`, `splash-screen.md`

### 1.4: Thông báo ngắn
- Nếu **cập nhật file cũ**: `> "Đang cập nhật learnings vào: [tên file]"`
- Nếu **tạo file mới**: `> "Đang tạo file learnings mới: [tên file]"`

---

## Bước 2: Trích xuất Learnings

AI quét toàn bộ conversation và phân loại thông tin có giá trị tái sử dụng vào 4 nhóm:

| Section | Nội dung | Ví dụ |
| :--- | :--- | :--- |
| **`## Architecture`** | Quyết định kiến trúc, design rationale, tại sao chọn A thay B | *"Dùng WorkManager thay AlarmManager vì..."* |
| **`## Bugs & Solutions`** | Lỗi đã gặp, root cause, cách fix | *"Lỗi NPE khi... $\rightarrow$ Fix bằng cách..."* |
| **`## How-To`** | Quy trình step-by-step để thực hiện một việc cụ thể | *"Cách thêm notification type mới: 1. Tạo model..."* |
| **`## Patterns`** | Coding patterns, conventions, cách dùng API/library | *"SharedPreferences dùng pattern X cho boolean flags"* |

**Tự động thực thi** – KHÔNG hỏi xác nhận, trực tiếp ghi vào file.

---

## Bước 3: Merge & Ghi file

### File path: `.agents/learnings/[feature-name].md`

### Nếu file CHƯA tồn tại $\rightarrow$ Tạo mới theo template:

```markdown
# [Feature Name]

> Tổng hợp kiến thức về [mô tả ngắn feature] trong dự án.
> Cập nhật lần cuối: YYYY-MM-DD

---

## Architecture

### [Tiêu đề]
- **Ngày**: YYYY-MM-DD
- **Chi tiết**: [Nội dung – ngắn gọn, tập trung vào giá trị tái sử dụng]
- **Files liên quan**: `path/to/file.ext`

---

## Bugs & Solutions

### [Tiêu đề]
- **Ngày**: YYYY-MM-DD
- **Vấn đề**: [Mô tả bug]
- **Root cause**: [Nguyên nhân gốc]
- **Fix**: [Cách fix]
- **Files liên quan**: `path/to/file.ext`

---

## How-To

### [Tiêu đề]
- **Ngày**: YYYY-MM-DD
- **Bước thực hiện**:
  1. Step 1
  2. Step 2
- **Files liên quan**: `path/to/file.ext`

---

## Patterns

### [Tiêu đề]
- **Ngày**: YYYY-MM-DD
- **Chi tiết**: [Mô tả pattern]
- **Ví dụ code** (nếu cần):
```[language]
// code snippet