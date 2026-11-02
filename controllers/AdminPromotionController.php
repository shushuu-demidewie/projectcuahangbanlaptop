<?php
/**
 * File: controllers/AdminPromotionController.php
 * Điều khiển chức năng Quản Lý Khuyến Mãi & Mã Giảm Giá
 * Dành cho Quản Trị Viên (Admin) và Nhân Viên Bán Hàng / Kế Toán
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/PromotionModel.php';

class AdminPromotionController {
    private PromotionModel $promotionModel;

    public function __construct() {
        AuthMiddleware::startSession();
        $this->promotionModel = new PromotionModel();
    }

    /**
     * Bắt buộc quyền truy cập cho các thao tác quản trị
     */
    private function checkAdminAuth(): void {
        AuthMiddleware::requireRole(['admin', 'nv_ban_hang', 'ke_toan']);
    }

    /**
     * Danh sách chương trình khuyến mãi & mã voucher
     */
    public function index(): void {
        $this->checkAdminAuth();
        $currentUser = AuthMiddleware::getCurrentUser();
        $userRole    = $currentUser['role'] ?? '';

        $statusParam = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;
        $keyword     = trim($_GET['keyword'] ?? '');

        $promotions = $this->promotionModel->getAllPromotions($statusParam, $keyword);

        // Thống kê nhanh số lượng
        $stats = [
            'total'       => count($promotions),
            'dangDienRa'  => 0,
            'chuaBatDau'  => 0,
            'hetHan'      => 0,
            'tamDung'     => 0,
        ];

        foreach ($promotions as $p) {
            if ($p['tinhTrang'] === 'DANG_DIEN_RA') {
                $stats['dangDienRa']++;
            } elseif ($p['tinhTrang'] === 'CHUA_BAT_DAU') {
                $stats['chuaBatDau']++;
            } elseif ($p['tinhTrang'] === 'HET_HAN') {
                $stats['hetHan']++;
            } else {
                $stats['tamDung']++;
            }
        }

        $activeMenu = 'promotions';
        require_once __DIR__ . '/../views/admin/promotions/index.php';
    }

    /**
     * Hiển thị Form tạo mới chương trình khuyến mãi
     */
    public function create(): void {
        $this->checkAdminAuth();
        $currentUser = AuthMiddleware::getCurrentUser();
        $userRole    = $currentUser['role'] ?? '';

        $activeMenu = 'promotions';
        require_once __DIR__ . '/../views/admin/promotions/create.php';
    }

    /**
     * Tiếp nhận dữ liệu thêm mới khuyến mãi
     */
    public function store(): void {
        $this->checkAdminAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: index.php?action=admin-promotions');
            exit();
        }

        $data = [
            'maKM'            => $_POST['maKM'] ?? '',
            'tenKM'           => $_POST['tenKM'] ?? '',
            'loaiKM'          => $_POST['loaiKM'] ?? 'PHAN_TRAM',
            'giaTriGiam'      => $_POST['giaTriGiam'] ?? 0,
            'mucGiamToiDa'    => !empty($_POST['mucGiamToiDa']) ? $_POST['mucGiamToiDa'] : null,
            'donHangToiThieu' => $_POST['donHangToiThieu'] ?? 0,
            'ngayBatDau'      => $_POST['ngayBatDau'] ?? '',
            'ngayKetThuc'     => $_POST['ngayKetThuc'] ?? '',
            'trangThai'       => isset($_POST['trangThai']) ? 1 : 0
        ];

        $res = $this->promotionModel->createPromotion($data);

        if ($res['success']) {
            $_SESSION['flash_success'] = $res['message'];
            header('Location: index.php?action=admin-promotions');
            exit();
        } else {
            $_SESSION['flash_error'] = $res['message'];
            $_SESSION['old_promotion_input'] = $data;
            header('Location: index.php?action=admin-promotion-create');
            exit();
        }
    }

    /**
     * Form chỉnh sửa chương trình khuyến mãi
     */
    public function edit(): void {
        $this->checkAdminAuth();
        $currentUser = AuthMiddleware::getCurrentUser();
        $userRole    = $currentUser['role'] ?? '';

        $maKM = trim($_GET['id'] ?? $_GET['maKM'] ?? '');
        $promotion = $this->promotionModel->getPromotionById($maKM);

        if (!$promotion) {
            $_SESSION['flash_error'] = "Không tìm thấy chương trình khuyến mãi với mã '{$maKM}'!";
            header('Location: index.php?action=admin-promotions');
            exit();
        }

        $activeMenu = 'promotions';
        require_once __DIR__ . '/../views/admin/promotions/edit.php';
    }

    /**
     * Tiếp nhận dữ liệu cập nhật khuyến mãi
     */
    public function update(): void {
        $this->checkAdminAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: index.php?action=admin-promotions');
            exit();
        }

        $maKM = trim($_GET['id'] ?? $_POST['maKM'] ?? '');

        $data = [
            'tenKM'           => $_POST['tenKM'] ?? '',
            'loaiKM'          => $_POST['loaiKM'] ?? 'PHAN_TRAM',
            'giaTriGiam'      => $_POST['giaTriGiam'] ?? 0,
            'mucGiamToiDa'    => !empty($_POST['mucGiamToiDa']) ? $_POST['mucGiamToiDa'] : null,
            'donHangToiThieu' => $_POST['donHangToiThieu'] ?? 0,
            'ngayBatDau'      => $_POST['ngayBatDau'] ?? '',
            'ngayKetThuc'     => $_POST['ngayKetThuc'] ?? '',
            'trangThai'       => isset($_POST['trangThai']) ? 1 : 0
        ];

        $res = $this->promotionModel->updatePromotion($maKM, $data);

        if ($res['success']) {
            $_SESSION['flash_success'] = $res['message'];
            header('Location: index.php?action=admin-promotions');
            exit();
        } else {
            $_SESSION['flash_error'] = $res['message'];
            header('Location: index.php?action=admin-promotion-edit&id=' . urlencode($maKM));
            exit();
        }
    }

    /**
     * Đổi nhanh trạng thái Bật / Tắt khuyến mãi
     */
    public function toggle(): void {
        $this->checkAdminAuth();

        $maKM = trim($_GET['id'] ?? $_GET['maKM'] ?? '');
        if (!empty($maKM)) {
            $ok = $this->promotionModel->togglePromotionStatus($maKM);
            if ($ok) {
                $_SESSION['flash_success'] = "Đã cập nhật trạng thái khuyến mãi '{$maKM}'!";
            } else {
                $_SESSION['flash_error'] = "Không thể cập nhật trạng thái cho mã '{$maKM}'!";
            }
        }

        header('Location: index.php?action=admin-promotions');
        exit();
    }

    /**
     * Xóa chương trình khuyến mãi
     */
    public function delete(): void {
        $this->checkAdminAuth();

        $maKM = trim($_GET['id'] ?? $_GET['maKM'] ?? '');
        if (!empty($maKM)) {
            $res = $this->promotionModel->deletePromotion($maKM);
            if ($res['success']) {
                $_SESSION['flash_success'] = $res['message'];
            } else {
                $_SESSION['flash_error'] = $res['message'];
            }
        }

        header('Location: index.php?action=admin-promotions');
        exit();
    }

    /**
     * Màn hình và thao tác gán khuyến mãi cho từng danh mục máy tính
     */
    public function assignCategory(): void {
        $this->checkAdminAuth();
        $currentUser = AuthMiddleware::getCurrentUser();
        $userRole    = $currentUser['role'] ?? '';

        // Xử lý khi Submit form gán
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $maDM = (int)($_POST['maDM'] ?? 0);
            $maKM = !empty($_POST['maKM']) ? trim($_POST['maKM']) : null;

            if ($maDM > 0) {
                $ok = $this->promotionModel->assignPromotionToCategory($maDM, $maKM);
                
                // Hỗ trợ AJAX request
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode([
                        'success' => $ok, 
                        'message' => $ok ? 'Đã gán chương trình khuyến mãi cho danh mục thành công!' : 'Lỗi khi gán khuyến mãi!'
                    ]);
                    exit();
                }

                if ($ok) {
                    $_SESSION['flash_success'] = 'Cập nhật khuyến mãi cho danh mục laptop thành công!';
                } else {
                    $_SESSION['flash_error'] = 'Không thể gán khuyến mãi cho danh mục này!';
                }
            }

            header('Location: index.php?action=admin-promotion-assign-category');
            exit();
        }

        // Chế độ GET: Hiển thị bảng gán danh mục
        $categories       = $this->promotionModel->getCategoriesWithPromotion();
        $activePromotions = $this->promotionModel->getActivePromotions();

        $activeMenu = 'promotions';
        require_once __DIR__ . '/../views/admin/promotions/assign_category.php';
    }

    /**
     * API kiểm tra mã voucher khi khách hàng nhập ở giỏ hàng, Checkout hoặc Thu ngân nhập ở POS
     * Endpoint: index.php?action=api-check-voucher
     */
    public function checkVoucherAjax(): void {
        header('Content-Type: application/json; charset=UTF-8');

        $code       = trim($_POST['voucher'] ?? $_POST['code'] ?? $_GET['voucher'] ?? $_GET['code'] ?? '');
        $orderTotal = (float)($_POST['orderTotal'] ?? $_POST['subtotal'] ?? $_GET['orderTotal'] ?? $_GET['subtotal'] ?? 0);
        $maDMList   = [];

        if (isset($_POST['maDMList'])) {
            $maDMList = is_array($_POST['maDMList']) ? $_POST['maDMList'] : explode(',', (string)$_POST['maDMList']);
        } elseif (isset($_GET['maDMList'])) {
            $maDMList = is_array($_GET['maDMList']) ? $_GET['maDMList'] : explode(',', (string)$_GET['maDMList']);
        }

        $result = $this->promotionModel->checkPromotionValidity($code, $orderTotal, $maDMList);

        // Lưu vào Session nếu hợp lệ để phục vụ thanh toán
        if ($result['valid']) {
            $_SESSION['applied_voucher'] = $result['maKM'];
            $_SESSION['voucher_discount'] = $result['discount'];
        }

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
