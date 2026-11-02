<?php
/**
 * File: controllers/AdminProductController.php
 * Quản lý danh sách, thêm, sửa, xóa mềm Laptop phía Admin
 */

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/BrandModel.php';

class AdminProductController {
    private ProductModel $productModel;
    private CategoryModel $categoryModel;
    private BrandModel $brandModel;

    public function __construct() {
        AuthMiddleware::startSession();
        // Chỉ cho phép admin, nhân viên kho và nhân viên bán hàng truy cập
        AuthMiddleware::requireRole(['admin', 'nv_kho', 'nv_ban_hang']);

        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->brandModel = new BrandModel();
    }

    /**
     * Danh sách sản phẩm (hỗ trợ tìm kiếm, lọc danh mục, hãng và phân trang)
     */
    public function index(): void {
        $keyword = trim($_GET['keyword'] ?? '');
        $maDM    = !empty($_GET['maDM']) ? (int)$_GET['maDM'] : null;
        $maTH    = !empty($_GET['maTH']) ? trim($_GET['maTH']) : null;
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $limit   = 10;

        // Lấy danh sách sản phẩm và tổng số bản ghi
        $products   = $this->productModel->getProducts($keyword, $maDM, $maTH, $page, $limit);
        $totalItems = $this->productModel->countProducts($keyword, $maDM, $maTH);
        $totalPages = (int)ceil($totalItems / $limit);

        // Lấy danh mục và hãng phục vụ bộ lọc
        $categories = $this->categoryModel->getAllCategories();
        $brands     = $this->brandModel->getAllBrands();

        require_once __DIR__ . '/../views/admin/products/index.php';
    }

    /**
     * Hiển thị form Thêm mới laptop
     */
    public function create(): void {
        $categories = $this->categoryModel->getAllCategories();
        $brands     = $this->brandModel->getAllBrands();
        $error      = $_SESSION['flash_error'] ?? '';
        $oldData    = $_SESSION['flash_old'] ?? [];
        unset($_SESSION['flash_error'], $_SESSION['flash_old']);

        require_once __DIR__ . '/../views/admin/products/create.php';
    }

    /**
     * Xử lý lưu thông tin laptop mới vào Database
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=admin-products");
            exit();
        }

        $maSP            = strtoupper(trim($_POST['maSP'] ?? ''));
        $tenSP           = trim($_POST['tenSP'] ?? '');
        $giaBan          = (float)($_POST['giaBan'] ?? 0);
        $soLuongTon      = (int)($_POST['soLuongTon'] ?? 0);
        $maTH            = trim($_POST['maTH'] ?? '');
        $maDM            = (int)($_POST['maDM'] ?? 0);
        $thoiGianBaoHanh = max(0, (int)($_POST['thoiGianBaoHanh'] ?? 12));
        $viTriKho        = trim($_POST['viTriKho'] ?? 'KHO_TONG');
        $trangThai       = isset($_POST['trangThai']) ? (int)$_POST['trangThai'] : 1;
        $thongSoKyThuat  = trim($_POST['thongSoKyThuat'] ?? '');
        $moTa            = trim($_POST['moTa'] ?? '');

        // Validate dữ liệu
        if (empty($maSP) || empty($tenSP) || empty($maTH) || empty($maDM)) {
            $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ các thông tin bắt buộc (*)!';
            $_SESSION['flash_old'] = $_POST;
            header("Location: index.php?action=admin-product-create");
            exit();
        }

        if ($this->productModel->isProductCodeExists($maSP)) {
            $_SESSION['flash_error'] = "Mã sản phẩm '{$maSP}' đã tồn tại trong hệ thống. Vui lòng chọn mã khác!";
            $_SESSION['flash_old'] = $_POST;
            header("Location: index.php?action=admin-product-create");
            exit();
        }

        if ($giaBan <= 0) {
            $_SESSION['flash_error'] = 'Giá bán sản phẩm phải lớn hơn 0 VNĐ!';
            $_SESSION['flash_old'] = $_POST;
            header("Location: index.php?action=admin-product-create");
            exit();
        }

        if ($soLuongTon < 0) {
            $_SESSION['flash_error'] = 'Số lượng tồn kho không được là số âm!';
            $_SESSION['flash_old'] = $_POST;
            header("Location: index.php?action=admin-product-create");
            exit();
        }

        $data = [
            'maSP'            => $maSP,
            'tenSP'           => $tenSP,
            'giaBan'          => $giaBan,
            'soLuongTon'      => $soLuongTon,
            'maTH'            => $maTH,
            'maDM'            => $maDM,
            'thoiGianBaoHanh' => $thoiGianBaoHanh,
            'viTriKho'        => $viTriKho,
            'trangThai'       => $trangThai,
            'thongSoKyThuat'  => $thongSoKyThuat,
            'moTa'            => $moTa
        ];

        if ($this->productModel->addProduct($data)) {
            $_SESSION['flash_success'] = "Thêm mới sản phẩm [{$tenSP}] thành công!";
            header("Location: index.php?action=admin-products");
        } else {
            $_SESSION['flash_error'] = "Không thể thêm sản phẩm vào cơ sở dữ liệu. Vui lòng kiểm tra lại!";
            $_SESSION['flash_old'] = $_POST;
            header("Location: index.php?action=admin-product-create");
        }
        exit();
    }

    /**
     * Hiển thị form Sửa thông tin laptop
     */
    public function edit(string $maSP): void {
        $product = $this->productModel->getProductById($maSP);

        if (!$product) {
            $_SESSION['flash_error'] = "Sản phẩm không tồn tại hoặc đã bị xóa!";
            header("Location: index.php?action=admin-products");
            exit();
        }

        $categories = $this->categoryModel->getAllCategories();
        $brands     = $this->brandModel->getAllBrands();
        $error      = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_error']);

        require_once __DIR__ . '/../views/admin/products/edit.php';
    }

    /**
     * Xử lý Cập nhật thông tin laptop
     */
    public function update(string $maSP): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=admin-products");
            exit();
        }

        $tenSP           = trim($_POST['tenSP'] ?? '');
        $giaBan          = (float)($_POST['giaBan'] ?? 0);
        $soLuongTon      = (int)($_POST['soLuongTon'] ?? 0);
        $maTH            = trim($_POST['maTH'] ?? '');
        $maDM            = (int)($_POST['maDM'] ?? 0);
        $thoiGianBaoHanh = max(0, (int)($_POST['thoiGianBaoHanh'] ?? 12));
        $viTriKho        = trim($_POST['viTriKho'] ?? 'KHO_TONG');
        $trangThai       = isset($_POST['trangThai']) ? (int)$_POST['trangThai'] : 1;
        $thongSoKyThuat  = trim($_POST['thongSoKyThuat'] ?? '');
        $moTa            = trim($_POST['moTa'] ?? '');

        if (empty($tenSP) || empty($maTH) || empty($maDM)) {
            $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ các thông tin bắt buộc (*)!';
            header("Location: index.php?action=admin-product-edit&id=" . urlencode($maSP));
            exit();
        }

        if ($giaBan <= 0) {
            $_SESSION['flash_error'] = 'Giá bán sản phẩm phải lớn hơn 0 VNĐ!';
            header("Location: index.php?action=admin-product-edit&id=" . urlencode($maSP));
            exit();
        }

        if ($soLuongTon < 0) {
            $_SESSION['flash_error'] = 'Số lượng tồn kho không được là số âm!';
            header("Location: index.php?action=admin-product-edit&id=" . urlencode($maSP));
            exit();
        }

        $data = [
            'tenSP'           => $tenSP,
            'giaBan'          => $giaBan,
            'soLuongTon'      => $soLuongTon,
            'maTH'            => $maTH,
            'maDM'            => $maDM,
            'thoiGianBaoHanh' => $thoiGianBaoHanh,
            'viTriKho'        => $viTriKho,
            'trangThai'       => $trangThai,
            'thongSoKyThuat'  => $thongSoKyThuat,
            'moTa'            => $moTa
        ];

        if ($this->productModel->updateProduct($maSP, $data)) {
            $_SESSION['flash_success'] = "Cập nhật sản phẩm [{$maSP}] thành công!";
            header("Location: index.php?action=admin-products");
        } else {
            $_SESSION['flash_error'] = "Không thể cập nhật thông tin sản phẩm. Vui lòng thử lại!";
            header("Location: index.php?action=admin-product-edit&id=" . urlencode($maSP));
        }
        exit();
    }

    /**
     * Xử lý Xóa mềm sản phẩm (soft delete)
     */
    public function delete(string $maSP): void {
        if ($this->productModel->softDeleteProduct($maSP)) {
            $_SESSION['flash_success'] = "Đã chuyển sản phẩm [{$maSP}] vào thùng rác thành công!";
        } else {
            $_SESSION['flash_error'] = "Lỗi khi xóa sản phẩm!";
        }
        header("Location: index.php?action=admin-products");
        exit();
    }
}
