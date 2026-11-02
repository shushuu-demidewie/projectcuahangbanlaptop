<?php
/**
 * File: controllers/ClientOrderTrackingController.php
 * Tra cứu tiến trình đơn hàng & hành trình vận chuyển công khai
 */

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/OrderTrackingModel.php';
require_once __DIR__ . '/../models/CartModel.php';

class ClientOrderTrackingController {
    private OrderTrackingModel $trackingModel;
    private CartModel $cartModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->trackingModel = new OrderTrackingModel();
        $this->cartModel     = new CartModel();
    }

    /**
     * Màn hình tra cứu hành trình đơn hàng
     */
    public function search(): void {
        $keyword = trim($_GET['keyword'] ?? '');
        $orders  = [];
        $hasSearched = false;

        if ($keyword !== '') {
            $hasSearched = true;
            $orders = $this->trackingModel->searchOrders($keyword);
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        $maKH = null;
        if (!empty($currentUser['maTk'])) {
            $maKH = $this->cartModel->getCustomerCodeByAccount($currentUser['maTk']);
        }
        $cartCount = $this->cartModel->getCartCount($maKH);

        require_once __DIR__ . '/../views/client/order_tracking.php';
    }
}
