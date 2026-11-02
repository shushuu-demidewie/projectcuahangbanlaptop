<?php
/**
 * File: controllers/CheckoutController.php
 * Controller xử lý thủ tục Thanh toán và Hoàn tất đơn hàng Online
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../models/ClientOrderModel.php';

class CheckoutController {
    private CartModel $cartModel;
    private ClientOrderModel $orderModel;

    public function __construct() {
        AuthMiddleware::startSession();
        $this->cartModel = new CartModel();
        $this->orderModel = new ClientOrderModel();
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
     * Màn hình Nhập thông tin thanh toán & giao nhận
     */
    public function index(): void {
        $maKH = $this->getCurrentMaKH();
        $cartItems = $this->cartModel->getCartItems($maKH);

        if (empty($cartItems)) {
            $_SESSION['flash_cart_error'] = "Giỏ hàng của bạn đang trống! Hãy chọn sản phẩm trước khi thanh toán.";
            header("Location: index.php?url=home");
            exit();
        }

        // Kiểm tra xem có sản phẩm nào hết hàng hoặc vượt tồn kho không
        foreach ($cartItems as $item) {
            if ($item['isOutOfStock'] || $item['isOverStock']) {
                $_SESSION['flash_cart_error'] = "Sản phẩm {$item['tenSP']} không đủ số lượng trong kho, vui lòng kiểm tra lại giỏ hàng!";
                header("Location: index.php?action=cart");
                exit();
            }
        }

        $subtotal = 0.0;
        foreach ($cartItems as $it) {
            $subtotal += (float)$it['thanhTien'];
        }

        $shippingFee = 50000.0;
        $currentUser = AuthMiddleware::getCurrentUser();

        // Lấy thông tin khách hàng nếu đã đăng nhập
        $customerDefault = [
            'tenNguoiNhan'    => $currentUser['hoTen'] ?? '',
            'sdtNhanHang'     => '',
            'email'           => $currentUser['email'] ?? '',
            'diaChiGiaoHang'  => ''
        ];

        if ($maKH) {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT hoVaTen, soDienThoai, email, diaChi FROM khachhang WHERE maKH = :maKH LIMIT 1");
            $stmt->execute([':maKH' => $maKH]);
            $kh = $stmt->fetch();
            if ($kh) {
                $customerDefault['tenNguoiNhan']   = $kh['hoVaTen'] ?: $customerDefault['tenNguoiNhan'];
                $customerDefault['sdtNhanHang']    = $kh['soDienThoai'] ?: '';
                $customerDefault['email']          = $kh['email'] ?: $customerDefault['email'];
                $customerDefault['diaChiGiaoHang'] = $kh['diaChi'] ?: '';
            }
        }

        $voucherCode = trim($_SESSION['applied_voucher'] ?? '');
        $voucherDiscount = 0.0;
        $voucherMessage = '';

        if (!empty($voucherCode)) {
            $voucherRes = $this->orderModel->checkVoucher($voucherCode, $subtotal);
            if ($voucherRes['valid']) {
                $voucherDiscount = $voucherRes['discount'];
                $voucherMessage = $voucherRes['message'];
            } else {
                unset($_SESSION['applied_voucher']);
            }
        }

        $grandTotal = max(0, $subtotal + $shippingFee - $voucherDiscount);
        $cartCount = $this->cartModel->getCartCount($maKH);

        $error = $_SESSION['flash_checkout_error'] ?? '';
        unset($_SESSION['flash_checkout_error']);

        require_once __DIR__ . '/../views/client/checkout.php';
    }

    /**
     * Endpoint kiểm tra hoặc áp dụng Voucher (AJAX hoặc Form submit)
     */
    public function checkVoucher(): void {
        $code = trim($_POST['voucher_code'] ?? '');
        $maKH = $this->getCurrentMaKH();
        $cartItems = $this->cartModel->getCartItems($maKH);

        $subtotal = 0.0;
        foreach ($cartItems as $it) {
            $subtotal += (float)$it['thanhTien'];
        }

        $res = $this->orderModel->checkVoucher($code, $subtotal);
        if ($res['valid']) {
            $_SESSION['applied_voucher'] = $res['maKM'];
        } else {
            unset($_SESSION['applied_voucher']);
        }

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($res);
            exit();
        }

        if (!$res['valid']) {
            $_SESSION['flash_checkout_error'] = $res['message'];
        }

        header("Location: index.php?action=checkout");
        exit();
    }

    /**
     * Tiếp nhận POST form thanh toán và tiến hành tạo đơn hàng
     */
    public function process(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=checkout");
            exit();
        }

        $tenNguoiNhan    = trim($_POST['tenNguoiNhan'] ?? '');
        $sdtNhanHang     = trim($_POST['sdtNhanHang'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $diaChiGiaoHang  = trim($_POST['diaChiGiaoHang'] ?? '');
        $ghiChuKhachHang = trim($_POST['ghiChuKhachHang'] ?? '');
        $paymentMethod   = trim($_POST['paymentMethod'] ?? 'COD');
        $voucherCode     = trim($_POST['voucherCode'] ?? $_SESSION['applied_voucher'] ?? '');

        // Xác thực dữ liệu bắt buộc
        if (empty($tenNguoiNhan) || empty($sdtNhanHang) || empty($diaChiGiaoHang)) {
            $_SESSION['flash_checkout_error'] = "Vui lòng nhập đầy đủ Họ tên, Số điện thoại và Địa chỉ nhận hàng!";
            header("Location: index.php?action=checkout");
            exit();
        }

        if (!preg_match('/^(0[3|5|7|8|9])[0-9]{8}$/', $sdtNhanHang)) {
            $_SESSION['flash_checkout_error'] = "Số điện thoại nhận hàng không hợp lệ (gồm 10 số, bắt đầu bằng 03, 05, 07, 08, 09)!";
            header("Location: index.php?action=checkout");
            exit();
        }

        $maKH = $this->getCurrentMaKH();
        $cartItems = $this->cartModel->getCartItems($maKH);

        if (empty($cartItems)) {
            $_SESSION['flash_cart_error'] = "Giỏ hàng của bạn đang trống!";
            header("Location: index.php?url=home");
            exit();
        }

        $customerInfo = [
            'tenNguoiNhan'    => $tenNguoiNhan,
            'sdtNhanHang'     => $sdtNhanHang,
            'email'           => $email,
            'diaChiGiaoHang'  => $diaChiGiaoHang,
            'ghiChuKhachHang' => $ghiChuKhachHang
        ];

        try {
            $orderResult = $this->orderModel->createOnlineOrder(
                $customerInfo,
                $cartItems,
                $paymentMethod,
                $ghiChuKhachHang,
                $maKH,
                $voucherCode
            );

            $maDH = is_array($orderResult) ? ($orderResult['maDH'] ?? '') : (string)$orderResult;

            if (!empty($maDH)) {
                unset($_SESSION['applied_voucher']);
                header("Location: index.php?action=order-success&id=" . urlencode($maDH));
                exit();
            } else {
                $_SESSION['flash_checkout_error'] = $orderResult['message'] ?? 'Không thể khởi tạo đơn hàng!';
                header("Location: index.php?action=checkout");
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['flash_checkout_error'] = $e->getMessage();
            header("Location: index.php?action=checkout");
            exit();
        }
    }

    /**
     * Trang thông báo Đặt hàng thành công
     */
    public function success(string $maDH): void {
        if (empty($maDH)) {
            header("Location: index.php?url=home");
            exit();
        }

        $order = $this->orderModel->getOrderSummary($maDH);
        if (!$order) {
            $_SESSION['flash_client_error'] = "Không tìm thấy thông tin đơn hàng!";
            header("Location: index.php?url=home");
            exit();
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        $cartCount = 0; // Sau khi đặt đơn thành công, giỏ hàng đã làm sạch

        require_once __DIR__ . '/../views/client/order_success.php';
    }
}
