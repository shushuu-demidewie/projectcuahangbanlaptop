---
name: continuous-learning-extractor
description: >-
  Use when summarizing project achievements, extracting reusable architecture decisions, documenting fixed bugs, or persisting session learnings into the learnings directory for future development sessions.
---

# Continuous Learning & Knowledge Extractor

Manages automated project learning and knowledge retention, saving structured markdown notes into [learnings/](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/learnings) and [.agents/learnings/](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/.agents/learnings).

## When to Use This Skill
- At the end of a feature implementation, debugging session, or architectural decision.
- Extracting "what worked", "what broke", and "why we chose design X over Y".
- Recording project-specific gotchas, database schemas, and conventions.

---

## 1. Extraction Workflow

Whenever completing a meaningful task:
1. Identify if a relevant learning file already exists in `repo/learnings/` (e.g., `laptop-store-auth.md`, `cart-session.md`, `database-design.md`).
2. If it exists, append or update the existing file.
3. If new, create a new file named `kebab-case.md`.

---

## 2. Standard Learning Document Template

```markdown
# [Feature / Topic Name]

> Tóm tắt kiến thức đúc kết từ dự án.
> Cập nhật lần cuối: YYYY-MM-DD

---

## 1. Architecture & Design Rationale
- Lý do lựa chọn giải pháp hiện tại: (ví dụ: Dùng PDO Transaction cho Checkout thay vì Auto-commit).
- Trade-offs đã đánh giá.

## 2. Bugs Encountered & Solutions
- **Triệu chứng (Symptoms)**: Mô tả lỗi.
- **Nguyên nhân gốc rễ (Root Cause)**: Tại sao xảy ra.
- **Cách khắc phục (Fix)**: Code diff hoặc giải pháp.

## 3. How-To & Reusable Steps
1. Bước 1...
2. Bước 2...

## 4. Conventions & Best Practices
- Quy chuẩn đặt tên, thư mục, hàm xử lý dữ liệu.
```
