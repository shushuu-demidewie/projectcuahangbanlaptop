---
name: developer-roadmap-advisor
description: >-
  Use when designing learning roadmaps, analyzing skill gaps, or looking up curriculum concepts and best practices across 96 technology domains (specifically PHP, Backend, Frontend, Full Stack, DevOps, and AI) based on roadmap.sh.
---

# Developer Roadmap Advisor & Curriculum Expert

Distilled from [roadmap.sh developer-roadmap](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/developer-roadmap).

## When to Use This Skill
- Guiding the user on what technologies, concepts, and tools to learn next.
- Auditing an existing project against industry-standard learning milestones.
- Querying structured technical summaries across 96 domains in the local repository.
- Deep-diving into modern PHP (cơ sở dữ liệu, bảo mật, OOP, framework Laravel/Symfony).

---

## 1. Local Knowledge Base Navigation

The repository contains 96 detailed roadmaps with discrete markdown explanations in `roadmaps/<domain>/content/`:

- **PHP Ecosystem**: [roadmaps/php/content](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/developer-roadmap/roadmaps/php/content) (120+ markdown files).
- **Backend Path**: [roadmaps/backend](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/developer-roadmap/roadmaps/backend) (APIs, Authentication, Databases, Caching).
- **Frontend Path**: [roadmaps/frontend](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/developer-roadmap/roadmaps/frontend) (HTML, CSS, Modern JS, Web Performance).
- **Full Stack Path**: [roadmaps/full-stack](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/developer-roadmap/roadmaps/full-stack).
- **Computer Science**: [roadmaps/computer-science](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/developer-roadmap/roadmaps/computer-science).
- **AI & Machine Learning**: [roadmaps/ai-engineer](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/developer-roadmap/roadmaps/ai-engineer).

---

## 2. PHP Development Roadmap (Quick Reference for `ql_cuahang_laptop`)

When working with PHP web projects, verify compliance with these essential roadmap milestones:

1. **Language Fundamentals**:
   - Modern PHP 8+ features: Named arguments, Constructor promotion, Match expression, Nullsafe operator `?->`, Union types.
2. **Object-Oriented Programming (OOP)**:
   - Encapsulation, Inheritance, Polymorphism, Abstract classes, Interfaces, Traits.
   - PSR Standards: PSR-1 (Basic Coding Standard), PSR-4 (Autoloading), PSR-12 (Coding Style).
3. **Database & Data Access**:
   - Always use **PDO with Prepared Statements** (NEVER concatenate variables into SQL strings).
   - Database migrations and relational data modeling.
4. **Security Must-Haves**:
   - **SQL Injection**: Prepared statements with parameterized queries.
   - **XSS (Cross-Site Scripting)**: Sanitize output using `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')`.
   - **CSRF (Cross-Site Request Forgery)**: Implement session-based anti-CSRF tokens for all POST/PUT/DELETE forms.
   - **Password Security**: Use `password_hash($pwd, PASSWORD_DEFAULT)` and `password_verify()`.
5. **Session & State Management**:
   - Cookie flags: `HttpOnly`, `Secure`, `SameSite=Strict`.
   - Session regeneration on privilege elevation (`session_regenerate_id(true)`).

---

## 3. How to Advise Users on Learning Paths

1. **Assess Current Baseline**: Clarify current programming language comfort and project goals.
2. **Define Milestone Target**: Formulate a sequential roadmap (Beginner $\to$ Intermediate $\to$ Advanced).
3. **Link to Concrete Concept Summaries**: Point the user to specific local files in `repo/developer-roadmap/roadmaps/...` for direct self-study.
