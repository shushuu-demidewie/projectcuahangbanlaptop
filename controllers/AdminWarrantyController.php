<?php
/**
 * File: controllers/AdminWarrantyController.php
 * Điều khiển Quản lý Bảo hành và Tiến độ sửa chữa dành cho Kỹ thuật viên & Admin
 */

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/WarrantyModel.php';

class AdminWarrantyController {
    private WarrantyModel $warrantyModel;

    public function __construct() {
        AuthMiddleware::startSession();
        // Cho phép Admin, Kỹ thuật viên, Thu ngân và Nhân viên bán hàng truy cập
        AuthMiddleware::requireRole(['admin', 'ky_thuat_vien', 'nv_ban_hang', 'thu_ngan']);
        $this->warrantyModel = new WarrantyModel();
    }

    /**
     * Danh sách phiếu bảo hành
     */
    public function index(): void {
        $status  = trim($_GET['status'] ?? '');
        $keyword = trim($_GET['keyword'] ?? '');

        $tickets = $this->warrantyModel->getWarrantyTickets($status, $keyword);

        require_once __DIR__ . '/../views/admin/warranty/index.php';
    }

    /**
     * Cập nhật trạng thái phiếu bảo hành và tiến độ máy
     */
    public function updateStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-warranty');
            exit;
        }

        $maPhieuBH        = trim($_POST['maPhieuBH'] ?? '');
        $trangThaiPhieu   = trim($_POST['trangThaiPhieu'] ?? '');
        $trangThaiSerial  = trim($_POST['trangThaiSerial'] ?? '');
        $ghiChu           = trim($_POST['ghiChu'] ?? '');

        if (empty($maPhieuBH) || empty($trangThaiPhieu)) {
            $_SESSION['flash_message'] = 'Vui lòng cung cấp đầy đủ thông tin cập nhật!';
            $_SESSION['flash_type']    = 'danger';
            header('Location: index.php?action=admin-warranty');
            exit;
        }

        $success = $this->warrantyModel->updateWarrantyStatus($maPhieuBH, $trangThaiPhieu, $trangThaiSerial, $ghiChu);

        if ($success) {
            $_SESSION['flash_message'] = "Đã cập nhật trạng thái phiếu bảo hành [{$maPhieuBH}] thành công!";
            $_SESSION['flash_type']    = 'success';
        } else {
            $_SESSION['flash_message'] = "Lỗi khi cập nhật phiếu bảo hành [{$maPhieuBH}]!";
            $_SESSION['flash_type']    = 'danger';
        }

        header('Location: index.php?action=admin-warranty');
        exit;
    }
}
