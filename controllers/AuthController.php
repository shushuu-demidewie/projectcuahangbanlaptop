<?php
/**
 * File: controllers/AuthController.php
 * Điều khiển các luồng Xác thực: Đăng nhập, Đăng ký, Đăng xuất
 */

require_once __DIR__ . '/../models/AccountModel.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class AuthController {
    private AccountModel $accountModel;

    public function __construct() {
        $this->accountModel = new AccountModel();
        AuthMiddleware::startSession();
    }

    /**
     * Xử lý Đăng nhập (GET: hiển thị form, POST: xác thực & lưu session)
     */
    public function login(): void {
        // Nếu đã đăng nhập rồi thì chuyển hướng thẳng theo quyền hạn
        if (AuthMiddleware::isLoggedIn()) {
            $this->redirectBasedOnRole($_SESSION['user']['role']);
            return;
        }

        $error = '';
        $tenTaiKhoan = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tenTaiKhoan = trim($_POST['tenTaiKhoan'] ?? '');
            $matKhau = trim($_POST['matKhau'] ?? '');

            if (empty($tenTaiKhoan) || empty($matKhau)) {
                $error = 'Vui lòng nhập đầy đủ Tên đăng nhập và Mật khẩu!';
            } else {
                $account = $this->accountModel->checkLogin($tenTaiKhoan, $matKhau);

                if ($account) {
                    // Ngăn chặn tấn công Session Fixation bằng cách đổi Session ID mới
                    session_regenerate_id(true);

                    // Lưu các thông tin cốt lõi vào Session
                    $_SESSION['user'] = [
                        'maTk'        => $account['maTk'],
                        'hoTen'       => $account['hoTen'],
                        'email'       => $account['email'],
                        'tenTaiKhoan' => $account['tenTaiKhoan'],
                        'role'        => $account['role'],
                    ];

                    $_SESSION['success_message'] = "Đăng nhập thành công! Chào mừng " . htmlspecialchars($account['hoTen']);

                    // Chuyển hướng theo vai trò (Role-based redirection)
                    $this->redirectBasedOnRole($account['role']);
                    return;
                } else {
                    $error = 'Tài khoản hoặc mật khẩu không chính xác, hoặc tài khoản đã bị khóa!';
                }
            }
        }

        // Nạp giao diện form Đăng nhập
        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Xử lý Đăng xuất
     */
    public function logout(): void {
        // Xóa toàn bộ dữ liệu session
        $_SESSION = [];

        // Xóa cookie session phía client nếu có
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        // Hủy session trên server
        session_destroy();

        // Chuyển hướng về trang đăng nhập kèm thông báo
        header("Location: index.php?url=login&logout=success");
        exit();
    }

    /**
     * Xử lý Đăng ký tài khoản khách hàng mới (Role mặc định là 'user')
     */
    public function register(): void {
        if (AuthMiddleware::isLoggedIn()) {
            $this->redirectBasedOnRole($_SESSION['user']['role']);
            return;
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hoTen       = trim($_POST['hoTen'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $tenTaiKhoan = trim($_POST['tenTaiKhoan'] ?? '');
            $matKhau     = trim($_POST['matKhau'] ?? '');
            $reMatKhau   = trim($_POST['reMatKhau'] ?? '');

            if (empty($hoTen) || empty($email) || empty($tenTaiKhoan) || empty($matKhau)) {
                $error = 'Vui lòng điền đầy đủ tất cả các trường thông tin!';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Địa chỉ Email không đúng định dạng!';
            } elseif (strlen($matKhau) < 6) {
                $error = 'Mật khẩu phải có độ dài tối thiểu từ 6 ký tự!';
            } elseif ($matKhau !== $reMatKhau) {
                $error = 'Mật khẩu xác nhận nhập lại không khớp!';
            } elseif ($this->accountModel->isUsernameExists($tenTaiKhoan)) {
                $error = 'Tên tài khoản này đã có người sử dụng. Vui lòng chọn tên khác!';
            } elseif ($this->accountModel->isEmailExists($email)) {
                $error = 'Email này đã được đăng ký tài khoản khác!';
            } else {
                $isRegistered = $this->accountModel->register(null, $hoTen, $email, $tenTaiKhoan, $matKhau);
                if ($isRegistered) {
                    $success = 'Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay.';
                } else {
                    $error = 'Đã có lỗi xảy ra trong quá trình tạo tài khoản. Vui lòng thử lại!';
                }
            }
        }

        require_once __DIR__ . '/../views/auth/register.php';
    }

    /**
     * Chuyển hướng người dùng dựa theo vai trò (Role)
     * @param string $role
     */
    private function redirectBasedOnRole(string $role): void {
        $staffRoles = ['admin', 'nv_kho', 'nv_ban_hang', 'ke_toan', 'thu_ngan', 'ky_thuat_vien'];

        if (in_array($role, $staffRoles, true)) {
            // Quản trị viên và Nhân viên chuyển vào trang Quản trị
            header("Location: index.php?url=admin");
        } else {
            // Khách hàng / người dùng thông thường chuyển về Trang Chủ
            header("Location: index.php?url=home");
        }
        exit();
    }
}
