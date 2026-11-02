---
name: php-mvc-production-pattern
description: >-
  Use when architecting production-grade PHP applications using modern MVC patterns, PSR-4 autoloading, secure PDO database wrappers, TOTP 2FA authentication, background schedulers, and clean service layers based on Borg Backup Server.
---

# PHP MVC Production Pattern & Clean Architecture

Distilled from [Borg Backup Server](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/borgbackupserver).

## When to Use This Skill
- Designing or refactoring PHP applications into a clean, maintainable MVC structure.
- Implementing secure database operations with PDO, prepared statements, and migrations.
- Adding enterprise-grade features in PHP: TOTP Two-Factor Authentication (2FA), Session security, and background workers/schedulers.
- Organizing business logic into decoupled Services instead of bloated controllers.

---

## 1. Production-Grade PHP Project Structure (PSR-4)

```text
project-root/
├── public/                 # Web server DocumentRoot (CHỈ file công khai)
│   ├── index.php           # Front Controller (Single Entry Point)
│   ├── css/
│   ├── js/
│   └── uploads/
├── src/                    # Mã nguồn hướng đối tượng có Namespace (PSR-4)
│   ├── Controllers/        # Nhận Request, gọi Service, trả View/JSON
│   ├── Services/           # Business logic, xác thực, thanh toán, giỏ hàng
│   ├── Core/               # Database wrapper, Router, Session, Config
│   └── Views/              # Templates PHP/HTML hiển thị dữ liệu
├── config/                 # Cấu hình môi trường (db, mail, app settings)
├── migrations/             # File script tạo/cập nhật bảng database
└── vendor/                 # Thư viện Composer autoload
```

---

## 2. Front Controller & Routing (`public/index.php`)

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Khởi tạo session an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
    ]);
}

// Định tuyến URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Dispatch tới Controller tương ứng
```

---

## 3. Secure Database Wrapper Pattern (PDO)

Never write raw string concatenation in SQL queries. Always use PDO with prepared statements:

```php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Rất quan trọng để chặn triệt để SQL Injection
            ]);
        }
        return self::$instance;
    }
}
```

---

## 4. Decoupled Service Layer Pattern

Keep controllers thin by offloading domain logic to specialized service classes:

```php
// Ví dụ: OrderService xử lý luồng đặt hàng
namespace App\Services;

use App\Core\Database;
use Exception;

class OrderService {
    public function placeOrder(int $userId, array $cartItems, array $shippingInfo): int {
        $db = Database::getConnection();
        $db->beginTransaction(); // Bắt buộc dùng Transaction khi tạo đơn hàng

        try {
            // 1. Kiểm tra tồn kho từng sản phẩm
            foreach ($cartItems as $item) {
                $stmt = $db->prepare("SELECT so_luong_ton FROM san_pham WHERE id = ? FOR UPDATE");
                $stmt->execute([$item['id']]);
                $stock = $stmt->fetchColumn();

                if ($stock < $item['quantity']) {
                    throw new Exception("Sản phẩm {$item['name']} không đủ số lượng tồn kho!");
                }
            }

            // 2. Tạo bản ghi đơn hàng chính (orders)
            // 3. Tạo chi tiết đơn hàng (order_items)
            // 4. Trừ số lượng tồn kho
            // 5. Xóa giỏ hàng

            $db->commit();
            return $orderId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
```

---

## 5. Security & Best Practices Checklist
- **Mật khẩu**: Dùng `password_hash($pwd, PASSWORD_DEFAULT)` và `password_verify($pwd, $hash)`.
- **Chống CSRF**: Tạo token ẩn trong session `$_SESSION['csrf_token']` và xác thực ở mọi POST form.
- **Chống XSS**: Luôn bọc biến in ra HTML qua `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`.
- **Tách biệt code**: Không bao giờ để mã PHP xử lý logic nằm chung lộn xộn trong file giao diện HTML.
