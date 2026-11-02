---
name: app-ideas-spec-generator
description: >-
  Use when defining project requirements, writing User Stories with acceptance criteria, scoping software features across tiers (Beginner, Intermediate, Advanced), or scaffolding practical web applications (especially E-commerce / Online Stores) based on Florin Pop's app-ideas.
---

# App Ideas & Software Spec Generator

Distilled from [app-ideas](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/app-ideas).

## When to Use This Skill
- The user wants to build a new feature or application and needs a structured specification.
- Converting ambiguous project ideas into actionable, testable **User Stories** (`- [ ] User can...`).
- Designing tiered scopes: **Tier 1 (MVP/Beginner)**, **Tier 2 (Intermediate/Core)**, **Tier 3 (Advanced/Bonus)**.
- Tailoring requirements for E-commerce & Store Management (matching `ql_cuahang_laptop`).

---

## 1. Project Specification Standard Template

When writing a software spec or task breakdown, follow this standard structure:

```markdown
# [Project Name]

**Tier:** [1-Beginner | 2-Intermediate | 3-Advanced]

## Goal & Constraints
- Clear, 2-3 sentence statement of what problem the project solves.
- Key technical constraints (e.g., PHP + MySQL, no third-party framework, responsive CSS).

## User Stories (Functional Requirements)
- [ ] User can [action] to [achieve result].
- [ ] User can see [data/component] on [page/modal].
- [ ] User can input [data] with validation error displayed if [invalid condition].

## Bonus / Advanced Features
- [ ] User can [advanced capability, e.g., export to PDF, filter by multiple criteria].
- [ ] Admin can [back-office management action].

## Data Model & Key Entities
- Entity definitions, primary keys, relationships (1-n, n-n).
```

---

## 2. E-Commerce / Laptop Store Blueprint (Direct Reference)

Refer to [Projects/2-Intermediate/Simple-Online-Store.md](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/app-ideas/Projects/2-Intermediate/Simple-Online-Store.md) for the core customer journey:

1. **Product Catalog**:
   - Grid of product cards: Thumbnail, title, specs (CPU, RAM, SSD), price, badge (sale/new).
   - Filter & Sort: Price range, brand, category, in-stock only.
2. **Product Details Page**:
   - Image gallery, full technical specifications table, stock availability, "Add to Cart" button.
3. **Shopping Cart Flow**:
   - Add/remove items, update quantities with dynamic subtotal & grand total calculation.
   - Guard against stock over-allocation: Validate `quantity <= stock_quantity`.
4. **Checkout & Order Creation**:
   - Shipping address and customer contact validation.
   - Order placement with unique order code generation and order confirmation view.
5. **Admin Management (Back-Office)**:
   - Product CRUD (Thêm, sửa, xóa laptop, upload ảnh).
   - Order Status Tracking (Chờ duyệt, Đang giao, Đã giao, Đã hủy).
   - Inventory tracking (Quản lý số lượng tồn kho).

---

## 3. Tier Selection Guide
- **Tier 1 (Beginner)**: Static layouts, basic CRUD, client-side state, form validation.
- **Tier 2 (Intermediate)**: Relational databases, authentication, session carts, REST APIs.
- **Tier 3 (Advanced)**: Payment gateway integration, background workers, real-time notifications, audit logs.
