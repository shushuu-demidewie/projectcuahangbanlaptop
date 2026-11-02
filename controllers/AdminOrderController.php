<?php
/**
 * File: controllers/AdminOrderController.php
 * Điều khiển các nghiệp vụ Quản lý Đơn hàng: Danh sách, Duyệt đơn, Gán Serial máy, Xuất hóa đơn
 */

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/OrderModel.php';

class AdminOrderController {
    private OrderModel $orderModel;

    public function __construct() {
        AuthMiddleware::startSession();
        // Cho phép admin, nhân viên bán hàng, thu ngân, kho và kế toán truy cập
        AuthMiddleware::requireRole(['admin', 'nv_ban_hang', 'thu_ngan', 'nv_kho', 'ke_toan']);
        $this->orderModel = new OrderModel();
    }

    /**
     * Danh sách tất cả đơn hàng & thống kê nhanh
     */
    public function index(): void {
        $channel = trim($_GET['channel'] ?? '');
        $status  = trim($_GET['status'] ?? '');
        $keyword = trim($_GET['keyword'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $limit   = 10;

        $orders     = $this->orderModel->getOrders($channel, $status, $keyword, $page, $limit);
        $totalItems = $this->orderModel->countOrders($channel, $status, $keyword);
        $totalPages = (int)ceil($totalItems / $limit);
        $stats      = $this->orderModel->getOrderStats();

        require_once __DIR__ . '/../views/admin/orders/index.php';
    }

    /**
     * Màn hình chi tiết đơn hàng
     */
    public function detail(string $maDH): void {
        if (empty($maDH)) {
            header("Location: index.php?action=admin-orders");
            exit();
        }

        $order = $this->orderModel->getOrderDetail($maDH);
        if (!$order) {
            $_SESSION['flash_error'] = "Không tìm thấy đơn hàng [{$maDH}]!";
            header("Location: index.php?action=admin-orders");
            exit();
        }

        $items = $this->orderModel->getOrderItems($maDH);

        $error = $_SESSION['flash_error'] ?? '';
        $success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require_once __DIR__ . '/../views/admin/orders/detail.php';
    }

    /**
     * Gán số Serial/IMEI máy có sẵn trong kho vào đơn hàng
     */
    public function assignSerial(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=admin-orders");
            exit();
        }

        $maDH        = trim($_POST['maDH'] ?? '');
        $idChiTietDH = (int)($_POST['idChiTietDH'] ?? 0);
        $maSerial    = trim($_POST['maSerial'] ?? '');

        if (empty($maDH) || empty($idChiTietDH) || empty($maSerial)) {
            $_SESSION['flash_error'] = 'Dữ liệu gán Serial không hợp lệ!';
            header("Location: index.php?action=admin-order-detail&id=" . urlencode($maDH));
            exit();
        }

        try {
            $this->orderModel->assignSerialToOrder($maSerial, $idChiTietDH);
            $_SESSION['flash_success'] = "Đã gán thành công mã máy Serial [{$maSerial}] vào đơn hàng!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Lỗi gán Serial: " . $e->getMessage();
        }

        header("Location: index.php?action=admin-order-detail&id=" . urlencode($maDH));
        exit();
    }

    /**
     * Gỡ bỏ mã Serial đã gán cho đơn hàng
     */
    public function removeSerial(): void {
        $maDH     = trim($_POST['maDH'] ?? $_GET['maDH'] ?? '');
        $maSerial = trim($_POST['maSerial'] ?? $_GET['maSerial'] ?? '');

        if (!empty($maSerial)) {
            $this->orderModel->removeAssignedSerial($maSerial);
            $_SESSION['flash_success'] = "Đã thu hồi mã Serial [{$maSerial}] về kho hàng thành công!";
        }

        header("Location: index.php?action=admin-order-detail&id=" . urlencode($maDH));
        exit();
    }

    /**
     * Cập nhật trạng thái đơn hàng (Duyệt giao, Hoàn thành, Hủy)
     */
    public function updateStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=admin-orders");
            exit();
        }

        $currentUser   = AuthMiddleware::getCurrentUser();
        $nguoiPhuTrach = $currentUser['maTk'];

        $maDH   = trim($_POST['maDH'] ?? '');
        $status = trim($_POST['status'] ?? '');

        $allowedStatuses = ['CHO_DUYET', 'DANG_GIAO', 'HOAN_THANH', 'DA_HUY'];
        if (empty($maDH) || !in_array($status, $allowedStatuses, true)) {
            $_SESSION['flash_error'] = 'Trạng thái chuyển đổi không hợp lệ!';
            header("Location: index.php?action=admin-order-detail&id=" . urlencode($maDH));
            exit();
        }

        try {
            if ($status === 'DA_HUY') {
                $lyDo = trim($_POST['lyDoHuy'] ?? 'Khách hủy đơn hoặc hết hàng');
                $this->orderModel->cancelOrder($maDH, $nguoiPhuTrach, $lyDo);
                $_SESSION['flash_success'] = "Đã hủy đơn hàng [{$maDH}] và tự động hoàn trả số Serial máy về lại kho!";
            } else {
                $this->orderModel->updateOrderStatus($maDH, $status, $nguoiPhuTrach);
                if ($status === 'HOAN_THANH') {
                    $_SESSION['flash_success'] = "Đơn hàng [{$maDH}] đã HOÀN THÀNH! Hệ thống đã tự động xuất Hóa đơn và cấp Phiếu bảo hành cho từng chiếc máy!";
                } else {
                    $_SESSION['flash_success'] = "Cập nhật trạng thái đơn hàng [{$maDH}] sang [{$status}] thành công!";
                }
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Không thể cập nhật trạng thái đơn: " . $e->getMessage();
        }

        header("Location: index.php?action=admin-order-detail&id=" . urlencode($maDH));
        exit();
    }

    /**
     * Màn hình In hóa đơn bán hàng / Phiếu giao hàng
     */
    public function printInvoice(string $maDH): void {
        if (empty($maDH)) {
            header("Location: index.php?action=admin-orders");
            exit();
        }

        $order = $this->orderModel->getOrderDetail($maDH);
        if (!$order) {
            $_SESSION['flash_error'] = "Đơn hàng không tồn tại!";
            header("Location: index.php?action=admin-orders");
            exit();
        }

        $items = $this->orderModel->getOrderItems($maDH);
        require_once __DIR__ . '/../views/admin/orders/invoice.php';
    }
}
