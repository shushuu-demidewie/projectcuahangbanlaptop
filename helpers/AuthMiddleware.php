<?php
/**
 * File: helpers/AuthMiddleware.php
 * Hỗ trợ xác thực và phân quyền truy cập người dùng trong hệ thống
 */

class AuthMiddleware {
    /**
     * Khởi động session an toàn nếu chưa được khởi tạo
     */
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,      // Chống đánh cắp cookie qua mã độc XSS
                'cookie_samesite' => 'Lax',      // Bảo vệ chống tấn công CSRF
                'use_strict_mode' => true,
            ]);
        }
    }

    /**
     * Kiểm tra người dùng đã đăng nhập hay chưa
     * @return bool
     */
    public static function isLoggedIn(): bool {
        self::startSession();
        return isset($_SESSION['user']) && !empty($_SESSION['user']['maTk']);
    }

    /**
     * Lấy thông tin tài khoản hiện tại từ Session
     * @return array|null
     */
    public static function getCurrentUser(): ?array {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Kiểm tra tài khoản hiện tại có thuộc danh sách quyền được phép hay không
     * @param array|string $allowedRoles Danh sách các role cho phép (ví dụ: ['admin', 'nv_ban_hang'] hoặc 'admin')
     * @return bool
     */
    public static function hasRole(array|string $allowedRoles): bool {
        if (!self::isLoggedIn()) {
            return false;
        }

        $userRole = $_SESSION['user']['role'] ?? '';

        if (is_string($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        return in_array($userRole, $allowedRoles, true);
    }

    /**
     * Kiểm tra tài khoản có phải là quản trị hoặc nhân viên nội bộ không
     * @return bool
     */
    public static function isStaff(): bool {
        $staffRoles = ['admin', 'nv_kho', 'nv_ban_hang', 'ke_toan', 'thu_ngan', 'ky_thuat_vien'];
        return self::hasRole($staffRoles);
    }

    /**
     * Bắt buộc phải đăng nhập mới được tiếp tục, nếu chưa thì chuyển hướng đến trang Login
     * @param string $redirectUrl
     */
    public static function requireLogin(string $redirectUrl = 'index.php?url=login'): void {
        if (!self::isLoggedIn()) {
            $_SESSION['error_message'] = "Vui lòng đăng nhập để tiếp tục!";
            header("Location: " . $redirectUrl);
            exit();
        }
    }

    /**
     * Bắt buộc tài khoản phải có quyền hợp lệ, nếu không thì báo lỗi 403 Forbidden
     * @param array|string $allowedRoles
     */
    public static function requireRole(array|string $allowedRoles): void {
        self::requireLogin();

        if (!self::hasRole($allowedRoles)) {
            http_response_code(403);
            die('
                <div style="font-family: Arial, sans-serif; text-align: center; margin-top: 100px;">
                    <h1 style="color: #dc3545; font-size: 48px;">403 Forbidden</h1>
                    <p style="font-size: 18px; color: #6c757d;">Bạn không có quyền truy cập vào khu vực chức năng này.</p>
                    <a href="index.php?url=home" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px;">Quay lại Trang Chủ</a>
                </div>
            ');
        }
    }
}
