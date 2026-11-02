<?php
/**
 * File: controllers/TrackingController.php
 * Phục vụ khách hàng tra cứu công khai:
 * 1. Tra cứu thời hạn & lịch sử bảo hành theo IMEI/Serial/SĐT
 * 2. Theo dõi tiến độ đơn hàng Online (4 bước trực quan)
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/TrackingModel.php';
require_once __DIR__ . '/../models/CartModel.php';

class TrackingController {
    private TrackingModel $trackingModel;
    private CartModel $cartModel;

    public function __construct() {
        AuthMiddleware::startSession();
        $this->trackingModel = new TrackingModel();
        $this->cartModel     = new CartModel();
    }

    /**
     * Màn hình tra cứu thời hạn bảo hành công khai
     */
    public function warranty(): void {
        $keyword = trim($_GET['keyword'] ?? $_GET['serial'] ?? $_GET['kw'] ?? '');
        $warranties = [];
        $hasSearched = false;

        if ($keyword !== '') {
            $hasSearched = true;
            $warranties = $this->trackingModel->checkWarrantyBySerialOrPhone($keyword);
        }

        // Đếm giỏ hàng cho Header client
        $currentUser = AuthMiddleware::getCurrentUser();
        $maKH = null;
        if (!empty($currentUser['maTk'])) {
            $maKH = $this->cartModel->getCustomerCodeByAccount($currentUser['maTk']);
        }
        $cartCount = $this->cartModel->getCartCount($maKH);

        require_once __DIR__ . '/../views/client/tracking/warranty.php';
    }

    /**
     * Màn hình theo dõi trạng thái & tiến độ đơn hàng
     */
    public function order(): void {
        $maDH = trim($_GET['maDH'] ?? $_GET['order_id'] ?? $_POST['maDH'] ?? '');
        $sdt  = trim($_GET['sdt'] ?? $_GET['phone'] ?? $_POST['sdt'] ?? '');
        $order = null;
        $hasSearched = false;

        if ($maDH !== '' || $sdt !== '') {
            $hasSearched = true;
            $order = $this->trackingModel->trackOrderByCodeOrPhone($maDH, $sdt ?: null);
        }

        // Đếm giỏ hàng cho Header client
        $currentUser = AuthMiddleware::getCurrentUser();
        $maKH = null;
        if (!empty($currentUser['maTk'])) {
            $maKH = $this->cartModel->getCustomerCodeByAccount($currentUser['maTk']);
        }
        $cartCount = $this->cartModel->getCartCount($maKH);

        require_once __DIR__ . '/../views/client/tracking/order.php';
    }
}
