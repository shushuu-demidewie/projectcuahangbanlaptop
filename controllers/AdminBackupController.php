<?php
/**
 * File: controllers/AdminBackupController.php
 * Controller Quản trị Hệ thống Sao lưu & Phục hồi CSDL (Chỉ dành cho Admin)
 * Kế thừa triết lý từ Borg Backup Server
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/DatabaseBackupService.php';

class AdminBackupController
{
    private DatabaseBackupService $backupService;

    public function __construct()
    {
        // Chỉ tài khoản có vai trò 'admin' mới được phép truy cập
        AuthMiddleware::requireRole(['admin']);
        $this->backupService = new DatabaseBackupService();
    }

    /**
     * Hiển thị bảng điều khiển quản trị sao lưu & phục hồi
     */
    public function index(): void
    {
        $currentUser = AuthMiddleware::getCurrentUser();
        $stats = $this->backupService->getDatabaseStats();
        $backups = $this->backupService->getBackupsList();

        $activeMenu = 'backups';
        require __DIR__ . '/../views/admin/backup/index.php';
    }

    /**
     * Tạo một bản sao lưu CSDL mới
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-backups');
            exit;
        }

        $note = trim($_POST['note'] ?? '');
        $result = $this->backupService->createBackup($note);

        if ($result['success']) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => $result['message']
            ];
        }

        header('Location: index.php?action=admin-backups');
        exit;
    }

    /**
     * Tải file sao lưu về máy
     */
    public function download(): void
    {
        $file = $_GET['file'] ?? '';
        if ($file === '') {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Không tìm thấy file sao lưu cần tải!'
            ];
            header('Location: index.php?action=admin-backups');
            exit;
        }

        $this->backupService->downloadBackup($file);
    }

    /**
     * Phục hồi CSDL từ bản sao lưu đã chọn
     */
    public function restore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-backups');
            exit;
        }

        $file = trim($_POST['file'] ?? '');
        if ($file === '') {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Vui lòng chọn file sao lưu hợp lệ để phục hồi!'
            ];
            header('Location: index.php?action=admin-backups');
            exit;
        }

        $result = $this->backupService->restoreBackup($file);

        if ($result['success']) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => $result['message']
            ];
        }

        header('Location: index.php?action=admin-backups');
        exit;
    }

    /**
     * Xóa một file sao lưu
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-backups');
            exit;
        }

        $file = trim($_POST['file'] ?? '');
        if ($file === '') {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Không tìm thấy file sao lưu cần xóa!'
            ];
            header('Location: index.php?action=admin-backups');
            exit;
        }

        if ($this->backupService->deleteBackup($file)) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "Đã xóa bản sao lưu [{$file}] thành công!"
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Không thể xóa file sao lưu [{$file}]!"
            ];
        }

        header('Location: index.php?action=admin-backups');
        exit;
    }
}

