<?php
/**
 * File: controllers/ProductDetailController.php
 * Controller hiển thị chi tiết sản phẩm laptop
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/ClientProductModel.php';
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../models/ReviewModel.php';

class ProductDetailController {
    private ClientProductModel $productModel;
    private CartModel $cartModel;
    private ReviewModel $reviewModel;

    public function __construct() {
        AuthMiddleware::startSession();
        $this->productModel = new ClientProductModel();
        $this->cartModel = new CartModel();
        $this->reviewModel = new ReviewModel();
    }

    /**
     * Hiển thị chi tiết một sản phẩm
     */
    public function detail(string $maSP): void {
        if (empty($maSP)) {
            header("Location: index.php?url=home");
            exit();
        }

        $product = $this->productModel->getProductDetail($maSP);
        if (!$product) {
            $_SESSION['flash_client_error'] = "Sản phẩm không tồn tại hoặc đã ngừng kinh doanh!";
            header("Location: index.php?url=home");
            exit();
        }

        // Lấy sản phẩm liên quan
        $relatedProducts = $this->productModel->getRelatedProducts($maSP, (int)$product['maDM'], 4);

        $currentUser = AuthMiddleware::getCurrentUser();
        $maKH = null;
        if (!empty($currentUser['maTk'])) {
            $maKH = $this->cartModel->getCustomerCodeByAccount($currentUser['maTk']);
        }
        // Lấy danh sách đánh giá & thống kê sao của sản phẩm
        $reviews = $this->reviewModel->getReviewsByProduct($maSP);
        $ratingSummary = $this->reviewModel->getRatingSummary($maSP);

        require_once __DIR__ . '/../views/client/product_detail.php';
    }

    /**
     * API trả về dữ liệu Xem Nhanh Sản Phẩm (Quick View Modal) qua AJAX
     */
    public function quickView(string $maSP): void {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($maSP)) {
            echo json_encode(['success' => false, 'message' => 'Mã sản phẩm không hợp lệ!']);
            exit();
        }

        $product = $this->productModel->getProductDetail($maSP);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh!']);
            exit();
        }

        $imgSrc = !empty($product['hinhAnh']) ? $product['hinhAnh'] : ('assets/images/products/' . $product['maSP'] . '.jpg');

        echo json_encode([
            'success' => true,
            'product' => [
                'maSP'            => $product['maSP'],
                'tenSP'           => $product['tenSP'],
                'giaBan'          => (float)$product['giaBan'],
                'giaBanFormatted' => number_format((float)$product['giaBan'], 0, ',', '.') . ' đ',
                'soLuongTon'      => (int)$product['soLuongTon'],
                'tenTH'           => $product['tenTH'] ?? '',
                'tenDM'           => $product['tenDM'] ?? '',
                'thoiGianBaoHanh' => $product['thoiGianBaoHanh'] ?? 12,
                'specs'           => $product['specs'] ?? [],
                'moTa'            => $product['moTa'] ?? '',
                'hinhAnh'         => $imgSrc
            ]
        ]);
        exit();
    }
}
