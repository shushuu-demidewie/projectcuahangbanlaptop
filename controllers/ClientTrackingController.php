<?php
/**
 * File: controllers/ClientTrackingController.php
 * Phân hệ: Cổng Tra Cứu Bảo Hành & Tiến Độ Đơn Hàng Dành Cho Khách Hàng
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/WarrantyTrackingModel.php';
require_once __DIR__ . '/../models/OrderTrackingModel.php';
require_once __DIR__ . '/../models/ClientTrackingModel.php';
require_once __DIR__ . '/../models/CartModel.php';

class ClientTrackingController {
    private WarrantyTrackingModel $warrantyModel;
    private OrderTrackingModel $orderModel;
    private ClientTrackingModel $clientTrackingModel;
    private CartModel $cartModel;

    public function __construct() {
        AuthMiddleware::startSession();
        $this->warrantyModel       = new WarrantyTrackingModel();
        $this->orderModel          = new OrderTrackingModel();
        $this->clientTrackingModel = new ClientTrackingModel();
        $this->cartModel           = new CartModel();
    }

    /**
     * Helper: Đếm số lượng sản phẩm trong giỏ hàng để hiển thị trên Header
     */
    private function getCartCount(): int {
        $currentUser = AuthMiddleware::getCurrentUser();
        $maKH = null;
        if (!empty($currentUser['maTk'])) {
            $maKH = $this->cartModel->getCustomerCodeByAccount($currentUser['maTk']);
        }
        return $this->cartModel->getCartCount($maKH);
    }

    /**
     * 1. Tra cứu tiến độ đơn hàng & hành trình vận chuyển
     * Route: index.php?action=track-order
     * Tham số: ?action=track-order&id=DH002 hoặc ?action=track-order&maDH=DH002&sdt=0988888999
     */
    public function orderTracking(): void {
        $maDH = trim((string)($_GET['id'] ?? $_GET['maDH'] ?? $_POST['maDH'] ?? ''));
        $sdt  = trim((string)($_GET['sdt'] ?? $_POST['sdt'] ?? ''));

        $order = null;
        $hasSearched = false;
        $errorMessage = '';

        if (!empty($maDH) || !empty($sdt)) {
            $hasSearched = true;

            // Nếu chỉ nhập SĐT mà không có mã đơn
            if (empty($maDH) && !empty($sdt)) {
                $orders = $this->orderModel->searchOrders($sdt);
                if (!empty($orders)) {
                    $order = $orders[0];
                } else {
                    $errorMessage = 'Không tìm thấy đơn hàng nào gắn với số điện thoại [' . htmlspecialchars($sdt) . ']. Vui lòng kiểm tra lại!';
                }
            } else {
                // Tra cứu theo Mã đơn (kết hợp SĐT nếu có)
                $order = $this->orderModel->trackOrder($maDH, !empty($sdt) ? $sdt : null);
                if (!$order) {
                    if (!empty($sdt)) {
                        $errorMessage = 'Không tìm thấy thông tin đơn hàng với mã [' . htmlspecialchars($maDH) . '] và số điện thoại [' . htmlspecialchars($sdt) . ']. Vui lòng kiểm tra lại!';
                    } else {
                        $errorMessage = 'Không tìm thấy thông tin đơn hàng với mã [' . htmlspecialchars($maDH) . ']. Vui lòng kiểm tra lại tính chính xác của mã đơn!';
                    }
                }
            }
        }

        $cartCount = $this->getCartCount();
        $pageTitle = 'Tra Cứu Tiến Độ Đơn Hàng - EmShu Store';

        // Nạp view giao diện tra cứu đơn hàng
        require_once __DIR__ . '/../views/client/tracking/order.php';
    }

    /**
     * 2. Tra cứu thẻ bảo hành điện tử laptop
     * Route: index.php?action=check-warranty
     * Tham số: ?action=check-warranty&keyword=SN_DELL_3520_0001
     */
    public function warranty(): void {
        $keyword = trim((string)($_GET['keyword'] ?? $_POST['keyword'] ?? $_GET['serial'] ?? ''));

        $warranties = [];
        $hasSearched = false;
        $errorMessage = '';

        if (!empty($keyword)) {
            $hasSearched = true;
            $warranties = $this->warrantyModel->searchWarranty($keyword);
            if (empty($warranties)) {
                $errorMessage = 'Không tìm thấy thiết bị hoặc số điện thoại [' . htmlspecialchars($keyword) . '] trong hệ thống bảo hành điện tử!';
            }
        }

        $cartCount = $this->getCartCount();
        $pageTitle = 'Tra Cứu Bảo Hành Điện Tử - EmShu Store';

        // Nạp view giao diện tra cứu bảo hành
        require_once __DIR__ . '/../views/client/tracking/warranty.php';
    }

    /**
     * Alias method for backward compatibility
     */
    public function warrantyCheck(): void {
        $this->warranty();
    }

    /**
     * 3. Quản lý lịch sử tất cả các đơn hàng của khách hàng đang đăng nhập
     * Route: index.php?action=my-orders
     */
    public function myOrders(): void {
        // Yêu cầu bắt buộc đăng nhập tài khoản khách hàng
        if (!AuthMiddleware::isLoggedIn()) {
            $_SESSION['error_message'] = 'Vui lòng đăng nhập để xem lịch sử mua hàng của bạn!';
            header('Location: index.php?url=login&redirect=my-orders');
            exit;
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        $maTk = $currentUser['maTk'] ?? '';

        // Lấy mã khách hàng liên kết với tài khoản
        $maKH = $this->clientTrackingModel->getCustomerCodeByAccountId($maTk);
        
        $orders = [];
        $customerInfo = null;

        if ($maKH) {
            $orders = $this->clientTrackingModel->getCustomerOrderHistory($maKH);
            $customerInfo = $this->clientTrackingModel->getCustomerInfo($maKH);
        }

        $cartCount = $this->getCartCount();
        $pageTitle = 'Đơn Hàng Của Tôi - EmShu Store';

        // Nạp view lịch sử đơn hàng
        require_once __DIR__ . '/../views/client/account/my_orders.php';
    }
}
