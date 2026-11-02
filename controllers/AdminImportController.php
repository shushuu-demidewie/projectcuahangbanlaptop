<?php
/**
 * File: controllers/AdminImportController.php
 * Điều khiển các nghiệp vụ Nhập Kho & Quản lý Serial/IMEI
 */

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/ImportReceiptModel.php';
require_once __DIR__ . '/../models/SupplierModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/SerialModel.php';

class AdminImportController {
    private ImportReceiptModel $receiptModel;
    private SupplierModel $supplierModel;
    private ProductModel $productModel;
    private SerialModel $serialModel;

    public function __construct() {
        AuthMiddleware::startSession();
        // Chỉ Quản trị viên (admin) và Nhân viên kho (nv_kho) mới được truy cập phân hệ nhập kho
        AuthMiddleware::requireRole(['admin', 'nv_kho']);

        $this->receiptModel  = new ImportReceiptModel();
        $this->supplierModel = new SupplierModel();
        $this->productModel  = new ProductModel();
        $this->serialModel   = new SerialModel();
    }

    /**
     * Danh sách phiếu nhập kho
     */
    public function index(): void {
        $keyword = trim($_GET['keyword'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $limit   = 10;

        $receipts   = $this->receiptModel->getReceipts($keyword, $page, $limit);
        $totalItems = $this->receiptModel->countReceipts($keyword);
        $totalPages = (int)ceil($totalItems / $limit);

        require_once __DIR__ . '/../views/admin/imports/index.php';
    }

    /**
     * Màn hình tạo phiếu nhập kho mới
     */
    public function create(): void {
        $suppliers = $this->supplierModel->getAllSuppliers();
        // Lấy danh sách laptop đang kinh doanh để nạp vào dropdown chọn máy nhập
        $products  = $this->productModel->getProducts('', null, null, 1, 1000);

        $error   = $_SESSION['flash_error'] ?? '';
        $oldData = $_SESSION['flash_old'] ?? [];
        unset($_SESSION['flash_error'], $_SESSION['flash_old']);

        require_once __DIR__ . '/../views/admin/imports/create.php';
    }

    /**
     * Xử lý lưu phiếu nhập kho và các số Serial
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=admin-imports");
            exit();
        }

        $currentUser = AuthMiddleware::getCurrentUser();
        $nguoiNhap   = $currentUser['maTk'];

        $maNCC               = trim($_POST['maNCC'] ?? '');
        $soHoaDonNCC         = trim($_POST['soHoaDonNCC'] ?? '');
        $tongTienTraTruocNCC = (float)($_POST['tongTienTraTruocNCC'] ?? 0);
        $ghiChuPhieu         = trim($_POST['ghiChu'] ?? '');
        $rawItems            = $_POST['items'] ?? [];

        // Validate thông tin chung
        if (empty($maNCC)) {
            $_SESSION['flash_error'] = 'Vui lòng chọn Nhà cung cấp!';
            $_SESSION['flash_old'] = $_POST;
            header("Location: index.php?action=admin-import-create");
            exit();
        }

        if (empty($rawItems) || !is_array($rawItems)) {
            $_SESSION['flash_error'] = 'Vui lòng thêm ít nhất 1 dòng sản phẩm laptop cần nhập kho!';
            $_SESSION['flash_old'] = $_POST;
            header("Location: index.php?action=admin-import-create");
            exit();
        }

        $dataDetails = [];
        $serialList  = [];
        $allEnteredSerials = [];
        $tongTienNhap = 0;

        // Duyệt và validate từng dòng sản phẩm nhập
        foreach ($rawItems as $index => $item) {
            $maSP        = trim($item['maSP'] ?? '');
            $soLuongNhap = (int)($item['soLuongNhap'] ?? 0);
            $giaVonNhap  = (float)($item['giaVonNhap'] ?? 0);
            $rawSerials  = trim($item['serials'] ?? '');
            $itemGhiChu  = trim($item['ghiChu'] ?? '');

            if (empty($maSP)) {
                $_SESSION['flash_error'] = "Dòng số " . ($index + 1) . ": Chưa chọn sản phẩm laptop!";
                $_SESSION['flash_old'] = $_POST;
                header("Location: index.php?action=admin-import-create");
                exit();
            }

            if ($soLuongNhap <= 0) {
                $_SESSION['flash_error'] = "Dòng số " . ($index + 1) . ": Số lượng nhập phải lớn hơn 0!";
                $_SESSION['flash_old'] = $_POST;
                header("Location: index.php?action=admin-import-create");
                exit();
            }

            if ($giaVonNhap <= 0) {
                $_SESSION['flash_error'] = "Dòng số " . ($index + 1) . ": Giá vốn nhập phải lớn hơn 0 VNĐ!";
                $_SESSION['flash_old'] = $_POST;
                header("Location: index.php?action=admin-import-create");
                exit();
            }

            // Tách các mã Serial (theo từng dòng hoặc dấu phẩy)
            $lines = preg_split('/[\r\n,]+/', $rawSerials);
            $cleanSerials = [];
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (!empty($trimmed)) {
                    $cleanSerials[] = $trimmed;
                }
            }

            // Kiểm tra số lượng Serial nhập vào có đúng bằng số lượng sản phẩm nhập không
            if (count($cleanSerials) !== $soLuongNhap) {
                $_SESSION['flash_error'] = "Dòng " . ($index + 1) . " [{$maSP}]: Bạn khai báo số lượng {$soLuongNhap} máy nhưng chỉ nhập " . count($cleanSerials) . " mã Serial/IMEI. Vui lòng nhập đủ mỗi máy 1 mã!";
                $_SESSION['flash_old'] = $_POST;
                header("Location: index.php?action=admin-import-create");
                exit();
            }

            // Kiểm tra trùng lặp serial ngay trong form đang nhập
            foreach ($cleanSerials as $s) {
                if (in_array($s, $allEnteredSerials, true)) {
                    $_SESSION['flash_error'] = "Mã Serial [{$s}] bị nhập trùng lặp nhiều lần trong form hiện tại!";
                    $_SESSION['flash_old'] = $_POST;
                    header("Location: index.php?action=admin-import-create");
                    exit();
                }
                $allEnteredSerials[] = $s;
            }

            $thanhTien = $soLuongNhap * $giaVonNhap;
            $tongTienNhap += $thanhTien;

            $dataDetails[] = [
                'maSP'        => $maSP,
                'soLuongNhap' => $soLuongNhap,
                'giaVonNhap'  => $giaVonNhap,
                'thanhTien'   => $thanhTien,
                'ghiChu'      => $itemGhiChu
            ];
            $serialList[] = $cleanSerials;
        }

        // Tự động sinh mã phiếu dạng: PN-YYYYMMDD-XXXX
        $maPhieu = 'PN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // Xác định trạng thái thanh toán
        $trangThaiThanhToan = 'CHUA_THANH_TOAN';
        if ($tongTienTraTruocNCC >= $tongTienNhap) {
            $trangThaiThanhToan = 'DA_THANH_TOAN';
        } elseif ($tongTienTraTruocNCC > 0) {
            $trangThaiThanhToan = 'CON_NO';
        }

        $dataReceipt = [
            'maPhieu'             => $maPhieu,
            'ngayNhap'            => date('Y-m-d H:i:s'),
            'maNCC'               => $maNCC,
            'nguoiNhap'           => $nguoiNhap,
            'soHoaDonNCC'         => $soHoaDonNCC,
            'tongTienTraTruocNCC' => $tongTienTraTruocNCC,
            'tongTienNhap'        => $tongTienNhap,
            'trangThaiThanhToan'  => $trangThaiThanhToan,
            'trangThaiNhapKho'    => 'DA_NHAP_KHO',
            'ghiChu'              => $ghiChuPhieu
        ];

        try {
            // Gọi Model thực thi Transaction an toàn
            $this->receiptModel->createReceipt($dataReceipt, $dataDetails, $serialList);
            $_SESSION['flash_success'] = "Tạo phiếu nhập kho [{$maPhieu}] và nhập " . count($allEnteredSerials) . " số Serial thành công!";
            header("Location: index.php?action=admin-import-detail&id=" . urlencode($maPhieu));
            exit();
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Lỗi xử lý nhập kho: " . $e->getMessage();
            $_SESSION['flash_old'] = $_POST;
            header("Location: index.php?action=admin-import-create");
            exit();
        }
    }

    /**
     * Xem chi tiết 1 phiếu nhập kho kèm các mã Serial cụ thể
     */
    public function show(string $maPhieu): void {
        if (empty($maPhieu)) {
            header("Location: index.php?action=admin-imports");
            exit();
        }

        $receipt = $this->receiptModel->getReceiptById($maPhieu);
        if (!$receipt) {
            $_SESSION['flash_error'] = "Không tìm thấy phiếu nhập kho có mã [{$maPhieu}]!";
            header("Location: index.php?action=admin-imports");
            exit();
        }

        $details = $this->receiptModel->getReceiptDetails($maPhieu);
        require_once __DIR__ . '/../views/admin/imports/detail.php';
    }

    /**
     * Màn hình tra cứu nhanh danh sách số Serial/IMEI trong toàn hệ thống
     */
    public function serialList(): void {
        $maSP      = trim($_GET['maSP'] ?? '');
        $trangThai = trim($_GET['trangThai'] ?? '');
        $keyword   = trim($_GET['keyword'] ?? '');
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $limit     = 15;

        $serials    = $this->serialModel->getSerials($maSP, $trangThai, $keyword, $page, $limit);
        $totalItems = $this->serialModel->countSerials($maSP, $trangThai, $keyword);
        $totalPages = (int)ceil($totalItems / $limit);
        $statusCounts = $this->serialModel->getSerialStatusCounts();

        // Lấy danh sách sản phẩm phục vụ dropdown lọc
        $products = $this->productModel->getProducts('', null, null, 1, 1000);

        require_once __DIR__ . '/../views/admin/serials/index.php';
    }
}
