<?php
/**
 * File: controllers/HomeController.php
 * Controller xử lý hiển thị Trang chủ và Tìm kiếm sản phẩm phía Khách hàng
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/ClientProductModel.php';
require_once __DIR__ . '/../models/CartModel.php';

class HomeController {
    private ClientProductModel $productModel;
    private CartModel $cartModel;

    public function __construct() {
        AuthMiddleware::startSession();
        $this->productModel = new ClientProductModel();
        $this->cartModel = new CartModel();
    }

    /**
     * Hiển thị trang chủ website
     */
    public function index(): void {
        $currentUser = AuthMiddleware::getCurrentUser();
        $maKH = null;
        if (!empty($currentUser['maTk'])) {
            $maKH = $this->cartModel->getCustomerCodeByAccount($currentUser['maTk']);
            // Tự động đồng bộ giỏ hàng session nếu có
            $this->cartModel->syncSessionCartToDatabase($maKH);
        }

        $cartCount = $this->cartModel->getCartCount($maKH);

        // Lấy danh mục và hãng
        $categories = $this->productModel->getActiveCategories();
        $brands = $this->productModel->getActiveBrands();

        // Lọc / tìm kiếm
        $keyword    = trim($_GET['keyword'] ?? '');
        $maDM       = !empty($_GET['category']) ? (int)$_GET['category'] : null;
        $maTH       = !empty($_GET['brand']) ? trim($_GET['brand']) : null;
        $priceRange = trim($_GET['price'] ?? '');
        $sortBy     = trim($_GET['sort'] ?? 'newest');
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $limit      = 12;

        $isFiltering = (!empty($keyword) || !empty($maDM) || !empty($maTH) || !empty($priceRange));

        if ($isFiltering) {
            $products = $this->productModel->searchProducts($keyword, $maDM, $maTH, $priceRange, $sortBy, $page, $limit);
            $totalProducts = $this->productModel->countSearchProducts($keyword, $maDM, $maTH, $priceRange);
            $totalPages = (int)ceil($totalProducts / $limit);
        } else {
            // Mặc định lấy sản phẩm nổi bật
            $products = $this->productModel->getFeaturedProducts(12);
            $totalProducts = count($products);
            $totalPages = 1;

            // Sản phẩm theo từng nhóm danh mục nổi bật
            $gamingLaptops = $this->productModel->getProductsByCategory(1, 4);
            $officeLaptops = $this->productModel->getProductsByCategory(2, 4);
            $macbooks      = $this->productModel->getProductsByCategory(3, 4);
            $creatorLaptops= $this->productModel->getProductsByCategory(4, 4);
        }

        require_once __DIR__ . '/../views/client/home.php';
    }

    /**
     * API Live Search sản phẩm phục vụ tự động hoàn thành trên Header
     * Endpoint: index.php?action=api-search-products&keyword=...
     */
    public function apiSearch(): void {
        header('Content-Type: application/json; charset=UTF-8');
        $keyword = trim($_GET['keyword'] ?? $_POST['keyword'] ?? '');

        if (empty($keyword)) {
            echo json_encode([], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $results = $this->productModel->searchProducts($keyword, null, null, '', 'newest', 1, 8);
        $formatted = [];

        foreach ($results as $item) {
            $formatted[] = [
                'maSP'      => $item['maSP'],
                'tenSP'     => $item['tenSP'],
                'giaBan'    => (float)$item['giaBan'],
                'giaBanFmt' => number_format((float)$item['giaBan'], 0, ',', '.') . ' đ',
                'hinhAnh'   => $item['hinhAnh'] ?? '',
                'tenTH'     => $item['tenTH'] ?? '',
                'tenDM'     => $item['tenDM'] ?? '',
                'url'       => 'index.php?action=client-product-detail&id=' . urlencode($item['maSP'])
            ];
        }

        echo json_encode($formatted, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
