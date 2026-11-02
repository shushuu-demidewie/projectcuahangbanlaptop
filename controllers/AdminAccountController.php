<?php
/**
 * File: controllers/AdminAccountController.php
 * Điều khiển Phân hệ Quản lý Tài khoản (Chỉ dành riêng cho Admin tối cao)
 */

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/AccountManagerModel.php';

class AdminAccountController {
    private AccountManagerModel $accountModel;

    public function __construct() {
        AuthMiddleware::startSession();
        // BẢO VỆ TUYỆT ĐỐI: Chỉ duy nhất tài khoản có role 'admin' mới được truy cập
        AuthMiddleware::requireRole(['admin']);
        $this->accountModel = new AccountManagerModel();
    }

    /**
     * Màn hình danh sách tài khoản & bộ lọc
     */
    public function index(): void {
        $role    = trim($_GET['role'] ?? '');
        $status  = trim($_GET['status'] ?? '');
        $keyword = trim($_GET['keyword'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $limit   = 10;

        $accounts   = $this->accountModel->getAllAccounts($role, $status, $keyword, $page, $limit);
        $totalItems = $this->accountModel->countAccounts($role, $status, $keyword);
        $totalPages = (int)ceil($totalItems / $limit);
        $stats      = $this->accountModel->countAccountsByRole();

        require_once __DIR__ . '/../views/admin/accounts/index.php';
    }

    /**
     * Xử lý thêm mới tài khoản nhân viên nội bộ
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        $hoTen       = trim($_POST['hoTen'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $tenTaiKhoan = trim($_POST['tenTaiKhoan'] ?? '');
        $matKhau     = trim($_POST['matKhau'] ?? '');
        $role        = trim($_POST['role'] ?? '');

        // Danh sách quyền hợp lệ
        $allowedRoles = ['admin', 'nv_kho', 'nv_ban_hang', 'thu_ngan', 'ke_toan', 'ky_thuat_vien'];

        // 1. Kiểm tra rỗng
        if (empty($hoTen) || empty($email) || empty($tenTaiKhoan) || empty($matKhau) || empty($role)) {
            $_SESSION['flash_message'] = 'Vui lòng điền đầy đủ tất cả các trường thông tin bắt buộc!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        // 2. Kiểm tra định dạng email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_message'] = 'Định dạng email không hợp lệ!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        // 3. Kiểm tra độ dài mật khẩu
        if (strlen($matKhau) < 6) {
            $_SESSION['flash_message'] = 'Mật khẩu phải chứa ít nhất 6 ký tự để đảm bảo an toàn!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        // 4. Kiểm tra phân quyền hợp lệ
        if (!in_array($role, $allowedRoles, true)) {
            $_SESSION['flash_message'] = 'Chức vụ phân quyền không hợp lệ!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        // 5. Kiểm tra trùng Username
        if ($this->accountModel->isUsernameExists($tenTaiKhoan)) {
            $_SESSION['flash_message'] = "Tên đăng nhập [{$tenTaiKhoan}] đã tồn tại trong hệ thống. Vui lòng chọn tên khác!";
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        // 6. Kiểm tra trùng Email
        if ($this->accountModel->isEmailExists($email)) {
            $_SESSION['flash_message'] = "Địa chỉ email [{$email}] đã được đăng ký bởi tài khoản khác!";
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        // 7. Thực hiện tạo tài khoản
        $newMaTk = $this->accountModel->createEmployeeAccount([
            'hoTen'       => $hoTen,
            'email'       => $email,
            'tenTaiKhoan' => $tenTaiKhoan,
            'matKhau'     => $matKhau,
            'role'        => $role
        ]);

        if ($newMaTk) {
            $_SESSION['flash_message'] = "Đã khởi tạo thành công tài khoản nhân viên [{$hoTen}] với mã [{$newMaTk}]!";
            $_SESSION['flash_type']    = 'success';
        } else {
            $_SESSION['flash_message'] = "Đã có lỗi xảy ra trong quá trình tạo tài khoản. Vui lòng thử lại!";
            $_SESSION['flash_type']    = 'danger';
        }

        header('Location: index.php?action=admin-accounts');
        exit;
    }

    /**
     * Khóa hoặc Mở khóa tài khoản nhanh
     */
    public function toggleStatus(): void {
        $maTk = trim($_GET['maTk'] ?? $_POST['maTk'] ?? '');

        if (empty($maTk)) {
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        // AN TOÀN TUYỆT ĐỐI: Không cho phép tự khóa chính tài khoản Admin đang thao tác
        $currentAdminMaTk = $_SESSION['user']['maTk'] ?? '';
        if ($maTk === $currentAdminMaTk) {
            $_SESSION['flash_message'] = 'CẢNH BÁO: Bạn không thể tự khóa chính tài khoản Admin của mình!';
            $_SESSION['flash_type']    = 'warning';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        $account = $this->accountModel->getAccountDetail($maTk);
        if (!$account) {
            $_SESSION['flash_message'] = 'Không tìm thấy tài khoản cần cập nhật trạng thái!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        // Đảo ngược trạng thái
        $newStatus = ($account['trangThai'] === 'active') ? 'locked' : 'active';
        $statusLabel = ($newStatus === 'active') ? 'Mở khóa kích hoạt' : 'Khóa tài khoản';

        $success = $this->accountModel->updateAccountRoleAndStatus($maTk, $account['role'], $newStatus);

        if ($success) {
            $_SESSION['flash_message'] = "Đã {$statusLabel} thành công cho tài khoản [{$account['hoTen']} - {$maTk}]!";
            $_SESSION['flash_type']    = ($newStatus === 'active') ? 'success' : 'secondary';
        } else {
            $_SESSION['flash_message'] = "Lỗi khi cập nhật trạng thái cho tài khoản [{$maTk}]!";
            $_SESSION['flash_type']    = 'danger';
        }

        header('Location: index.php?action=admin-accounts');
        exit;
    }

    /**
     * Đặt lại mật khẩu mới cho tài khoản
     */
    public function resetPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        $maTk            = trim($_POST['maTk'] ?? '');
        $newPassword     = trim($_POST['newPassword'] ?? '');
        $confirmPassword = trim($_POST['confirmPassword'] ?? '');

        if (empty($maTk) || empty($newPassword)) {
            $_SESSION['flash_message'] = 'Vui lòng nhập mật khẩu mới!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['flash_message'] = 'Mật khẩu mới phải có tối thiểu 6 ký tự!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_message'] = 'Xác nhận mật khẩu không khớp!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        $account = $this->accountModel->getAccountDetail($maTk);
        if (!$account) {
            $_SESSION['flash_message'] = 'Tài khoản không tồn tại!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        $success = $this->accountModel->resetAccountPassword($maTk, $newPassword);

        if ($success) {
            $_SESSION['flash_message'] = "Đã đặt lại mật khẩu mới thành công cho tài khoản [{$account['hoTen']} ({$maTk})]!";
            $_SESSION['flash_type']    = 'success';
        } else {
            $_SESSION['flash_message'] = "Lỗi khi đặt lại mật khẩu cho tài khoản [{$maTk}]!";
            $_SESSION['flash_type']    = 'danger';
        }

        header('Location: index.php?action=admin-accounts');
        exit;
    }

    /**
     * Cập nhật quyền hạn (Role) cho tài khoản
     */
    public function updateRole(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        $maTk    = trim($_POST['maTk'] ?? '');
        $newRole = trim($_POST['newRole'] ?? '');

        $allowedRoles = ['admin', 'nv_kho', 'nv_ban_hang', 'thu_ngan', 'ke_toan', 'ky_thuat_vien', 'user'];
        if (!in_array($newRole, $allowedRoles, true)) {
            $_SESSION['flash_message'] = 'Chức vụ không hợp lệ!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        $account = $this->accountModel->getAccountDetail($maTk);
        if (!$account) {
            $_SESSION['flash_message'] = 'Tài khoản không tồn tại!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-accounts');
            exit;
        }

        $success = $this->accountModel->updateAccountRoleAndStatus($maTk, $newRole, $account['trangThai']);
        if ($success) {
            $_SESSION['flash_message'] = "Đã cập nhật chức vụ thành công cho tài khoản [{$account['hoTen']}]!";
            $_SESSION['flash_type']    = 'success';
        } else {
            $_SESSION['flash_message'] = "Lỗi khi cập nhật chức vụ!";
            $_SESSION['flash_type']    = 'danger';
        }

        header('Location: index.php?action=admin-accounts');
        exit;
    }
}
