<?php
/**
 * File: controllers/ClientWarrantyController.php
 * Phục vụ khách hàng tra cứu thời hạn và chính sách bảo hành công khai
 */

require_once __DIR__ . '/../models/WarrantyModel.php';
require_once __DIR__ . '/../models/CartModel.php';

class ClientWarrantyController {
    private WarrantyModel $warrantyModel;
    private CartModel $cartModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->warrantyModel = new WarrantyModel();
        $this->cartModel     = new CartModel();
    }

    /**
     * Màn hình tra cứu bảo hành công khai
     */
    public function search(): void {
        $keyword = trim($_GET['keyword'] ?? '');
        $results = [];
        $hasSearched = false;

        if ($keyword !== '') {
            $hasSearched = true;
            $results = $this->warrantyModel->searchWarrantyByKeyword($keyword);
        }

        // Lấy số lượng giỏ hàng hiển thị trên Header client
        $cartCount = $this->cartModel->getTotalCount();

        require_once __DIR__ . '/../views/client/warranty_check.php';
    }
}
