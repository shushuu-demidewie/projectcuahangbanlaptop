<?php
/**
 * File: index.php
 * Front Controller & Router duy nhất của ứng dụng
 */

declare(strict_types=1);

// Nạp các tệp trợ giúp và điều khiển cần thiết
require_once __DIR__ . '/helpers/AuthMiddleware.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/AdminProductController.php';
require_once __DIR__ . '/controllers/AdminImportController.php';
require_once __DIR__ . '/controllers/AdminOrderController.php';
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/ProductDetailController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/CheckoutController.php';
require_once __DIR__ . '/controllers/AdminReportController.php';
require_once __DIR__ . '/controllers/AdminWarrantyController.php';
require_once __DIR__ . '/controllers/ClientWarrantyController.php';
require_once __DIR__ . '/controllers/AdminAccountController.php';
require_once __DIR__ . '/controllers/ClientOrderTrackingController.php';
require_once __DIR__ . '/controllers/ReviewController.php';
require_once __DIR__ . '/controllers/AdminPosController.php';
require_once __DIR__ . '/controllers/TrackingController.php';
require_once __DIR__ . '/controllers/AdminPromotionController.php';
require_once __DIR__ . '/controllers/ClientTrackingController.php';
require_once __DIR__ . '/controllers/AdminBackupController.php';

// Khởi tạo session an toàn ngay từ entry point
AuthMiddleware::startSession();

// Lấy tham số đường dẫn từ query string ?url=... (hoặc ?action=...)
$url = trim($_GET['url'] ?? $_GET['action'] ?? 'home');

// Điều hướng tới Controller và View tương ứng
switch ($url) {
    // === CÁC ROUTE PHÍA KHÁCH HÀNG (CLIENT SIDE) ===
    case '':
    case 'home':
    case 'client-home':
        $homeCtrl = new HomeController();
        $homeCtrl->index();
        break;

    case 'api-search-products':
        $homeCtrl = new HomeController();
        $homeCtrl->apiSearch();
        break;

    case 'client-detail':
    case 'product-detail':
    case 'client-product-detail':
        $detailCtrl = new ProductController();
        $detailCtrl->detail($_GET['id'] ?? '');
        break;

    case 'product-quickview':
        $detailCtrl = new ProductDetailController();
        $detailCtrl->quickView($_GET['id'] ?? '');
        break;

    case 'review-submit':
        $reviewCtrl = new ReviewController();
        $reviewCtrl->store();
        break;

    case 'track-order':
    case 'order-tracking':
        $trackingCtrl = new ClientTrackingController();
        $trackingCtrl->orderTracking();
        break;

    case 'cart':
        $cartCtrl = new CartController();
        $cartCtrl->index();
        break;

    case 'cart-add':
        $cartCtrl = new CartController();
        $cartCtrl->add();
        break;

    case 'cart-update':
        $cartCtrl = new CartController();
        $cartCtrl->update();
        break;

    case 'cart-remove':
        $cartCtrl = new CartController();
        $cartCtrl->remove();
        break;

    case 'checkout':
        $checkoutCtrl = new CheckoutController();
        $checkoutCtrl->index();
        break;

    case 'checkout-voucher':
        $checkoutCtrl = new CheckoutController();
        $checkoutCtrl->checkVoucher();
        break;

    case 'checkout-process':
        $checkoutCtrl = new CheckoutController();
        $checkoutCtrl->process();
        break;

    case 'order-success':
        $checkoutCtrl = new CheckoutController();
        $checkoutCtrl->success($_GET['id'] ?? '');
        break;

    case 'login':
        // Đăng nhập tài khoản
        $auth = new AuthController();
        $auth->login();
        break;

    case 'logout':
        // Đăng xuất
        $auth = new AuthController();
        $auth->logout();
        break;

    case 'register':
        // Đăng ký khách hàng mới
        $auth = new AuthController();
        $auth->register();
        break;

    case 'admin':
    case 'admin/dashboard':
        // Trang Quản trị (Admin & Nhân viên nội bộ)
        require_once __DIR__ . '/views/admin/dashboard.php';
        break;

    // === CÁC ROUTE QUẢN LÝ LAPTOP (ADMIN) ===
    case 'admin-products':
        $prodCtrl = new AdminProductController();
        $prodCtrl->index();
        break;

    case 'admin-product-create':
        $prodCtrl = new AdminProductController();
        $prodCtrl->create();
        break;

    case 'admin-product-store':
        $prodCtrl = new AdminProductController();
        $prodCtrl->store();
        break;

    case 'admin-product-edit':
        $prodCtrl = new AdminProductController();
        $prodCtrl->edit($_GET['id'] ?? '');
        break;

    case 'admin-product-update':
        $prodCtrl = new AdminProductController();
        $prodCtrl->update($_GET['id'] ?? '');
        break;

    case 'admin-product-delete':
        $prodCtrl = new AdminProductController();
        $prodCtrl->delete($_GET['id'] ?? '');
        break;

    // === CÁC ROUTE NHẬP KHO & SERIAL / IMEI (ADMIN) ===
    case 'admin-imports':
        $importCtrl = new AdminImportController();
        $importCtrl->index();
        break;

    case 'admin-import-create':
        $importCtrl = new AdminImportController();
        $importCtrl->create();
        break;

    case 'admin-import-store':
        $importCtrl = new AdminImportController();
        $importCtrl->store();
        break;

    case 'admin-import-detail':
        $importCtrl = new AdminImportController();
        $importCtrl->show($_GET['id'] ?? '');
        break;

    case 'admin-serials':
        $importCtrl = new AdminImportController();
        $importCtrl->serialList();
        break;

    // === CÁC ROUTE QUẢN LÝ ĐƠN HÀNG (ADMIN) ===
    case 'admin-orders':
        $orderCtrl = new AdminOrderController();
        $orderCtrl->index();
        break;

    case 'admin-order-detail':
        $orderCtrl = new AdminOrderController();
        $orderCtrl->detail($_GET['id'] ?? '');
        break;

    case 'admin-order-assign-serial':
        $orderCtrl = new AdminOrderController();
        $orderCtrl->assignSerial();
        break;

    case 'admin-order-remove-serial':
        $orderCtrl = new AdminOrderController();
        $orderCtrl->removeSerial();
        break;

    case 'admin-order-status':
        $orderCtrl = new AdminOrderController();
        $orderCtrl->updateStatus();
        break;

    case 'admin-order-invoice':
        $orderCtrl = new AdminOrderController();
        $orderCtrl->printInvoice($_GET['id'] ?? '');
        break;

    // === CÁC ROUTE BÁN HÀNG TẠI QUẦY (POS) DÀNH CHO THU NGÂN / NHÂN VIÊN BÁN HÀNG ===
    case 'admin-pos':
        $posCtrl = new AdminPosController();
        $posCtrl->index();
        break;

    case 'admin-pos-get-serials':
        $posCtrl = new AdminPosController();
        $posCtrl->getSerialsAjax();
        break;

    case 'admin-pos-customer':
        $posCtrl = new AdminPosController();
        $posCtrl->searchCustomerAjax();
        break;

    case 'admin-pos-checkout':
        $posCtrl = new AdminPosController();
        $posCtrl->checkout();
        break;

    // === ROUTE TRA CỨU BẢO HÀNH KHÁCH HÀNG (CLIENT) ===
    case 'check-warranty':
    case 'warranty-check':
        $trackingCtrl = new ClientTrackingController();
        $trackingCtrl->warranty();
        break;

    // === ROUTE LỊCH SỬ ĐƠN HÀNG CỦA KHÁCH HÀNG (CLIENT) ===
    case 'my-orders':
    case 'my-order':
    case 'client-orders':
        $trackingCtrl = new ClientTrackingController();
        $trackingCtrl->myOrders();
        break;

    // === ROUTE BÁO CÁO DOANH SỐ & TỒN KHO (ADMIN) ===
    case 'admin-reports':
        $reportCtrl = new AdminReportController();
        $reportCtrl->index();
        break;

    case 'admin-report-export':
        $reportCtrl = new AdminReportController();
        $reportCtrl->exportExcel();
        break;

    // === CÁC ROUTE QUẢN LÝ KHUYẾN MÃI & VOUCHER (ADMIN & POS) ===
    case 'admin-promotions':
        $promoCtrl = new AdminPromotionController();
        $promoCtrl->index();
        break;

    case 'admin-promotion-create':
        $promoCtrl = new AdminPromotionController();
        $promoCtrl->create();
        break;

    case 'admin-promotion-store':
        $promoCtrl = new AdminPromotionController();
        $promoCtrl->store();
        break;

    case 'admin-promotion-edit':
        $promoCtrl = new AdminPromotionController();
        $promoCtrl->edit();
        break;

    case 'admin-promotion-update':
        $promoCtrl = new AdminPromotionController();
        $promoCtrl->update();
        break;

    case 'admin-promotion-toggle':
        $promoCtrl = new AdminPromotionController();
        $promoCtrl->toggle();
        break;

    case 'admin-promotion-delete':
        $promoCtrl = new AdminPromotionController();
        $promoCtrl->delete();
        break;

    case 'admin-promotion-assign-category':
        $promoCtrl = new AdminPromotionController();
        $promoCtrl->assignCategory();
        break;

    case 'api-check-voucher':
        $promoCtrl = new AdminPromotionController();
        $promoCtrl->checkVoucherAjax();
        break;

    // === CÁC ROUTE QUẢN LÝ BẢO HÀNH (ADMIN) ===
    case 'admin-warranty':
        $warrantyCtrl = new AdminWarrantyController();
        $warrantyCtrl->index();
        break;

    case 'admin-warranty-update':
        $warrantyCtrl = new AdminWarrantyController();
        $warrantyCtrl->updateStatus();
        break;

    // === CÁC ROUTE QUẢN LÝ TÀI KHOẢN (ADMIN TỐI CAO) ===
    case 'admin-accounts':
        $accCtrl = new AdminAccountController();
        $accCtrl->index();
        break;

    case 'admin-account-store':
        $accCtrl = new AdminAccountController();
        $accCtrl->store();
        break;

    case 'admin-account-toggle':
        $accCtrl = new AdminAccountController();
        $accCtrl->toggleStatus();
        break;

    case 'admin-account-reset-pwd':
        $accCtrl = new AdminAccountController();
        $accCtrl->resetPassword();
        break;

    case 'admin-account-update-role':
        $accCtrl = new AdminAccountController();
        $accCtrl->updateRole();
        break;

    // === CÁC ROUTE SAO LƯU & PHỤC HỒI CSDL (BORG BACKUP SERVER ENGINE) ===
    case 'admin-backups':
        $backupCtrl = new AdminBackupController();
        $backupCtrl->index();
        break;

    case 'admin-backup-create':
        $backupCtrl = new AdminBackupController();
        $backupCtrl->create();
        break;

    case 'admin-backup-download':
        $backupCtrl = new AdminBackupController();
        $backupCtrl->download();
        break;

    case 'admin-backup-restore':
        $backupCtrl = new AdminBackupController();
        $backupCtrl->restore();
        break;

    case 'admin-backup-delete':
        $backupCtrl = new AdminBackupController();
        $backupCtrl->delete();
        break;

    default:
        // Trang 404 Không tìm thấy trang
        http_response_code(404);
        echo '
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <title>404 - Không tìm thấy trang</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light d-flex align-items-center justify-content-center vh-100">
            <div class="text-center p-4">
                <h1 class="display-1 fw-bold text-danger">404</h1>
                <h3 class="mb-3">Trang bạn yêu cầu không tồn tại!</h3>
                <p class="text-muted mb-4">Vui lòng kiểm tra lại đường dẫn hoặc quay về trang chủ.</p>
                <a href="index.php?url=home" class="btn btn-primary px-4">Về Trang Chủ</a>
            </div>
        </body>
        </html>
        ';
        break;
}
