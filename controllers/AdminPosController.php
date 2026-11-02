<?php
/**
 * File: controllers/AdminPosController.php
 * Điều khiển màn hình Bán hàng tại quầy (POS) cho Thu ngân / Nhân viên bán hàng
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/PosModel.php';

class AdminPosController {
    private PosModel $posModel;

    public function __construct() {
        AuthMiddleware::startSession();
        $this->posModel = new PosModel();
    }

    /**
     * Màn hình bán lẻ tại quầy POS toàn màn hình
     */
    public function index(): void {
        AuthMiddleware::requireLogin();
        $currentUser = AuthMiddleware::getCurrentUser();
        $userRole = $currentUser['role'] ?? '';

        // Chỉ cho phép admin, nhân viên bán hàng, thu ngân
        $allowedRoles = ['admin', 'nv_ban_hang', 'thu_ngan'];
        if (!in_array($userRole, $allowedRoles, true)) {
            $_SESSION['flash_error'] = 'Bạn không có quyền truy cập vào Phân hệ Bán hàng tại quầy (POS)!';
            header('Location: index.php?url=admin');
            exit();
        }

        $keyword = trim($_GET['kw'] ?? '');
        $brandId = trim($_GET['brand'] ?? '');

        // Lấy danh sách laptop đang có hàng tồn kho
        $products = $this->posModel->getInStockProducts($keyword ?: null, $brandId ?: null);

        // Lấy danh sách thương hiệu để lọc nhanh
        $db = Database::getConnection();
        $brands = $db->query("SELECT maTH, tenTH FROM thuonghieu ORDER BY tenTH ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh sách khuyến mãi hợp lệ (trangThai = 1, NOW() BETWEEN ngayBatDau AND ngayKetThuc)
        $promotions = $db->query("
            SELECT maKM, tenKM, loaiKM, giaTriGiam, mucGiamToiDa, donHangToiThieu, ngayBatDau, ngayKetThuc 
            FROM khuyenmai 
            WHERE trangThai = 1 AND NOW() BETWEEN ngayBatDau AND ngayKetThuc 
            ORDER BY ngayKetThuc ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $activeMenu = 'pos';
        require_once __DIR__ . '/../views/admin/pos/index.php';
    }

    /**
     * API AJAX: Lấy danh sách mã Serial/IMEI còn trong kho của sản phẩm
     */
    public function getSerialsAjax(): void {
        header('Content-Type: application/json; charset=utf-8');

        if (!AuthMiddleware::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại!']);
            exit();
        }

        $maSP = trim($_GET['maSP'] ?? '');
        if ($maSP === '') {
            echo json_encode(['success' => false, 'message' => 'Mã sản phẩm không hợp lệ!']);
            exit();
        }

        try {
            $serials = $this->posModel->getAvailableSerials($maSP);
            echo json_encode([
                'success' => true,
                'maSP'    => $maSP,
                'count'   => count($serials),
                'serials' => $serials
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * API AJAX: Tìm nhanh khách hàng bằng số điện thoại
     */
    public function searchCustomerAjax(): void {
        header('Content-Type: application/json; charset=utf-8');

        if (!AuthMiddleware::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn.']);
            exit();
        }

        $sdt = trim($_GET['sdt'] ?? '');
        if ($sdt === '') {
            echo json_encode(['success' => false, 'message' => 'Vui lòng cung cấp số điện thoại!']);
            exit();
        }

        try {
            $customer = $this->posModel->findCustomerByPhone($sdt);
            echo json_encode([
                'success'  => true,
                'found'    => ($customer !== null),
                'customer' => $customer
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * Xử lý thanh toán đơn POS tại quầy
     */
    public function checkout(): void {
        AuthMiddleware::requireLogin();
        $currentUser = AuthMiddleware::getCurrentUser();
        $cashierId = $currentUser['maTk'] ?? 'ADMIN';

        // Hỗ trợ cả nhận JSON payload và POST form thông thường
        $inputRaw = file_get_contents('php://input');
        $jsonData = json_decode($inputRaw, true);

        $isJson = ($jsonData !== null);
        $data = $isJson ? $jsonData : $_POST;

        $orderData = [
            'maKH'                => trim($data['maKH'] ?? ''),
            'hoTen'               => trim($data['hoTen'] ?? 'Khách Mua Tại Quầy'),
            'soDienThoai'         => trim($data['soDienThoai'] ?? ''),
            'diaChi'              => trim($data['diaChi'] ?? 'Tại cửa hàng EmShu Store'),
            'phuongThucThanhToan' => trim($data['phuongThucThanhToan'] ?? 'TIEN_MAT'),
            'maKM'                => !empty($data['maKM']) ? trim((string)$data['maKM']) : null,
            'tienGiamGia'         => (float)($data['tienGiamGia'] ?? 0),
            'ghiChu'              => trim($data['ghiChu'] ?? '')
        ];

        // Lấy danh sách sản phẩm trong giỏ POS
        $cartItems = $data['items'] ?? [];
        if (is_string($cartItems)) {
            $cartItems = json_decode($cartItems, true) ?: [];
        }

        try {
            if (empty($cartItems)) {
                throw new Exception('Giỏ hàng POS đang trống. Vui lòng chọn ít nhất 1 máy tính để thanh toán!');
            }

            $result = $this->posModel->processPosOrder($orderData, $cartItems, $cashierId);

            if ($isJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success'     => true,
                    'maDH'        => $result['maDH'],
                    'maHD'        => $result['maHD'],
                    'redirectUrl' => 'index.php?action=admin-order-invoice&id=' . urlencode($result['maDH']),
                    'message'     => 'Thanh toán thành công! Đang chuyển đến hóa đơn...'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            } else {
                $_SESSION['flash_success'] = "Thanh toán thành công đơn hàng [{$result['maDH']}]!";
                header('Location: index.php?action=admin-order-invoice&id=' . urlencode($result['maDH']));
                exit();
            }
        } catch (Exception $e) {
            if ($isJson) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE);
                exit();
            } else {
                $_SESSION['flash_error'] = 'Lỗi xử lý đơn POS: ' . $e->getMessage();
                header('Location: index.php?action=admin-pos');
                exit();
            }
        }
    }
}
