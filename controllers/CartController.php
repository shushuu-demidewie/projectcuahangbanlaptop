<?php
/**
 * File: controllers/CartController.php
 * Controller xử lý Giỏ hàng (Xem, Thêm, Sửa số lượng, Xóa)
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../models/ClientProductModel.php';

class CartController {
    private CartModel $cartModel;
    private ClientProductModel $productModel;

    public function __construct() {
        AuthMiddleware::startSession();
        $this->cartModel = new CartModel();
        $this->productModel = new ClientProductModel();
    }

    /**
     * Lấy mã khách hàng của phiên hiện tại nếu đã đăng nhập
     */
    private function getCurrentMaKH(): ?string {
        $currentUser = AuthMiddleware::getCurrentUser();
        if (!empty($currentUser['maTk'])) {
            return $this->cartModel->getCustomerCodeByAccount($currentUser['maTk']);
        }
        return null;
    }

    /**
     * Trang xem giỏ hàng
     */
    public function index(): void {
        $maKH = $this->getCurrentMaKH();
        if ($maKH) {
            $this->cartModel->syncSessionCartToDatabase($maKH);
        }

        $cartItems = $this->cartModel->getCartItems($maKH);
        $cartCount = $this->cartModel->getCartCount($maKH);

        // Tính tổng tiền giỏ hàng
        $subtotal = 0.0;
        foreach ($cartItems as $it) {
            $subtotal += (float)$it['thanhTien'];
        }

        $currentUser = AuthMiddleware::getCurrentUser();

        require_once __DIR__ . '/../views/client/cart.php';
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function add(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=cart");
            exit();
        }

        $maSP = trim($_POST['maSP'] ?? '');
        $qty  = max(1, (int)($_POST['quantity'] ?? 1));
        $isBuyNow = isset($_POST['buy_now']);

        if (empty($maSP)) {
            $_SESSION['flash_cart_error'] = "Vui lòng chọn sản phẩm hợp lệ!";
            header("Location: index.php?url=home");
            exit();
        }

        $maKH = $this->getCurrentMaKH();
        $result = $this->cartModel->addToCart($maKH, $maSP, $qty);

        // Kiểm tra xem request có phải qua AJAX không
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit();
        }

        if ($result['success']) {
            $_SESSION['flash_cart_success'] = $result['message'];
            if ($isBuyNow) {
                header("Location: index.php?action=checkout");
                exit();
            }
        } else {
            $_SESSION['flash_cart_error'] = $result['message'];
        }

        header("Location: index.php?action=cart");
        exit();
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=cart");
            exit();
        }

        $identifier = trim((string)($_POST['identifier'] ?? ''));
        $quantity   = (int)($_POST['quantity'] ?? 1);

        $maKH = $this->getCurrentMaKH();
        $result = $this->cartModel->updateQuantity($maKH, $identifier, $quantity);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit();
        }

        if (!$result['success']) {
            $_SESSION['flash_cart_error'] = $result['message'];
        } else {
            $_SESSION['flash_cart_success'] = $result['message'];
        }

        header("Location: index.php?action=cart");
        exit();
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function remove(): void {
        $identifier = trim((string)($_GET['id'] ?? $_POST['identifier'] ?? ''));

        if (!empty($identifier)) {
            $maKH = $this->getCurrentMaKH();
            $result = $this->cartModel->removeFromCart($maKH, $identifier);
            $_SESSION['flash_cart_success'] = $result['message'];
        }

        header("Location: index.php?action=cart");
        exit();
    }
}
