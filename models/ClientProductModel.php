<?php
/**
 * File: models/ClientProductModel.php
 * Model xử lý truy vấn sản phẩm dành cho khách hàng
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class ClientProductModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách sản phẩm nổi bật/mới nhất theo danh mục
     */
    public function getFeaturedProducts(int $limit = 12): array {
        $sql = "SELECT 
                    sp.maSP, sp.tenSP, sp.giaBan, sp.soLuongTon, sp.thoiGianBaoHanh, 
                    sp.thongSoKyThuat, sp.moTa, sp.hinhAnh, sp.maDM, sp.maTH,
                    dm.tenDM,
                    th.tenTH, th.logo AS logoTH
                FROM sanpham sp
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE sp.trangThai = 1 AND sp.isDeleted = 0
                ORDER BY sp.maSP DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $p['specs'] = $this->parseSpecs($p['thongSoKyThuat'] ?? '');
        }

        return $products;
    }

    /**
     * Lấy danh sách laptop mới nhất và nổi bật phân theo danh mục (Gaming, Văn phòng, MacBook, Đồ họa)
     */
    public function getHomeProducts(): array {
        return [
            'featured' => $this->getFeaturedProducts(12),
            'gaming'   => $this->getProductsByCategory(1, 4),
            'office'   => $this->getProductsByCategory(2, 4),
            'macbook'  => $this->getProductsByCategory(3, 4),
            'creator'  => $this->getProductsByCategory(4, 4),
        ];
    }

    /**
     * Lấy danh sách sản phẩm theo từng danh mục
     */
    public function getProductsByCategory(int $maDM, int $limit = 8): array {
        $sql = "SELECT 
                    sp.maSP, sp.tenSP, sp.giaBan, sp.soLuongTon, sp.thoiGianBaoHanh, 
                    sp.thongSoKyThuat, sp.moTa, sp.hinhAnh, sp.maDM, sp.maTH,
                    dm.tenDM,
                    th.tenTH, th.logo AS logoTH
                FROM sanpham sp
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE sp.maDM = :maDM AND sp.trangThai = 1 AND sp.isDeleted = 0
                ORDER BY sp.maSP DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':maDM', $maDM, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $p['specs'] = $this->parseSpecs($p['thongSoKyThuat'] ?? '');
        }

        return $products;
    }

    /**
     * Lấy chi tiết một sản phẩm theo mã SP
     */
    public function getProductDetail(string $maSP): array|false {
        $sql = "SELECT 
                    sp.maSP, sp.tenSP, sp.giaBan, sp.soLuongTon, sp.thoiGianBaoHanh, 
                    sp.thongSoKyThuat, sp.moTa, sp.hinhAnh, sp.viTriKho, sp.trangThai, sp.maDM, sp.maTH,
                    dm.tenDM, dm.moTa AS moTaDM,
                    th.tenTH, th.logo AS logoTH, th.xuatXu AS xuatXuTH
                FROM sanpham sp
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE sp.maSP = :maSP AND sp.trangThai = 1 AND sp.isDeleted = 0
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maSP' => $maSP]);
        $product = $stmt->fetch();

        if ($product) {
            $product['specs'] = $this->parseSpecs($product['thongSoKyThuat'] ?? '');
            $product['isAvailable'] = ((int)($product['soLuongTon'] ?? 0)) > 0;
        }

        return $product;
    }

    /**
     * Lấy các sản phẩm liên quan (hỗ trợ cả getRelatedProducts($maDM, $maSP) và ($maSP, $maDM))
     */
    public function getRelatedProducts($param1, $param2, int $limit = 4): array {
        if (is_numeric($param1) && !is_numeric($param2)) {
            $maDM = (int)$param1;
            $currentMaSP = (string)$param2;
        } else {
            $currentMaSP = (string)$param1;
            $maDM = (int)$param2;
        }

        $sql = "SELECT 
                    sp.maSP, sp.tenSP, sp.giaBan, sp.soLuongTon, sp.thoiGianBaoHanh, 
                    sp.thongSoKyThuat, sp.hinhAnh, sp.maDM, sp.maTH,
                    dm.tenDM, th.tenTH
                FROM sanpham sp
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE sp.maDM = :maDM AND sp.maSP != :currentMaSP AND sp.trangThai = 1 AND sp.isDeleted = 0
                ORDER BY RAND()
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':maDM', $maDM, PDO::PARAM_INT);
        $stmt->bindValue(':currentMaSP', $currentMaSP, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $p['specs'] = $this->parseSpecs($p['thongSoKyThuat'] ?? '');
        }

        return $products;
    }

    /**
     * Tìm kiếm và lọc sản phẩm theo đa tiêu chí
     */
    public function searchProducts(
        string $keyword = '', 
        ?int $maDM = null, 
        ?string $maTH = null, 
        string $priceRange = '',
        string $sortBy = 'newest',
        int $page = 1, 
        int $limit = 12
    ): array {
        $sql = "SELECT 
                    sp.maSP, sp.tenSP, sp.giaBan, sp.soLuongTon, sp.thoiGianBaoHanh, 
                    sp.thongSoKyThuat, sp.moTa, sp.hinhAnh, sp.maDM, sp.maTH,
                    dm.tenDM, th.tenTH, th.logo AS logoTH
                FROM sanpham sp
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE sp.trangThai = 1 AND sp.isDeleted = 0";

        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (sp.tenSP LIKE :kw1 OR sp.thongSoKyThuat LIKE :kw2 OR th.tenTH LIKE :kw3 OR sp.maSP LIKE :kw4)";
            $kwVal = "%" . trim($keyword) . "%";
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
            $params[':kw4'] = $kwVal;
        }

        if (!empty($maDM)) {
            $sql .= " AND sp.maDM = :maDM";
            $params[':maDM'] = $maDM;
        }

        if (!empty($maTH)) {
            $sql .= " AND sp.maTH = :maTH";
            $params[':maTH'] = $maTH;
        }

        // Lọc theo khoảng giá
        switch ($priceRange) {
            case 'under_15':
                $sql .= " AND sp.giaBan < 15000000";
                break;
            case '15_20':
                $sql .= " AND sp.giaBan >= 15000000 AND sp.giaBan <= 20000000";
                break;
            case '20_30':
                $sql .= " AND sp.giaBan >= 20000000 AND sp.giaBan <= 30000000";
                break;
            case 'over_30':
                $sql .= " AND sp.giaBan > 30000000";
                break;
        }

        // Sắp xếp
        switch ($sortBy) {
            case 'price_asc':
                $sql .= " ORDER BY sp.giaBan ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY sp.giaBan DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY sp.tenSP ASC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY sp.maSP DESC";
                break;
        }

        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $products = $stmt->fetchAll();
        foreach ($products as &$p) {
            $p['specs'] = $this->parseSpecs($p['thongSoKyThuat'] ?? '');
        }

        return $products;
    }

    /**
     * Đếm tổng số sản phẩm theo điều kiện lọc
     */
    public function countSearchProducts(string $keyword = '', ?int $maDM = null, ?string $maTH = null, string $priceRange = ''): int {
        $sql = "SELECT COUNT(*) FROM sanpham sp
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE sp.trangThai = 1 AND sp.isDeleted = 0";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (sp.tenSP LIKE :kw1 OR sp.thongSoKyThuat LIKE :kw2 OR th.tenTH LIKE :kw3 OR sp.maSP LIKE :kw4)";
            $kwVal = "%" . trim($keyword) . "%";
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
            $params[':kw4'] = $kwVal;
        }

        if (!empty($maDM)) {
            $sql .= " AND sp.maDM = :maDM";
            $params[':maDM'] = $maDM;
        }

        if (!empty($maTH)) {
            $sql .= " AND sp.maTH = :maTH";
            $params[':maTH'] = $maTH;
        }

        switch ($priceRange) {
            case 'under_15':
                $sql .= " AND sp.giaBan < 15000000";
                break;
            case '15_20':
                $sql .= " AND sp.giaBan >= 15000000 AND sp.giaBan <= 20000000";
                break;
            case '20_30':
                $sql .= " AND sp.giaBan >= 20000000 AND sp.giaBan <= 30000000";
                break;
            case 'over_30':
                $sql .= " AND sp.giaBan > 30000000";
                break;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy tất cả danh mục đang hoạt động
     */
    public function getActiveCategories(): array {
        $sql = "SELECT maDM, tenDM, moTa FROM danhmuc WHERE trangThai = 1 ORDER BY maDM ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Lấy tất cả thương hiệu đang hoạt động
     */
    public function getActiveBrands(): array {
        $sql = "SELECT maTH, tenTH, logo, xuatXu FROM thuonghieu WHERE trangThai = 1 ORDER BY tenTH ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Phân tách chuỗi thông số kỹ thuật dạng "CPU: ... | RAM: ... | SSD: ..."
     * thành mảng có cấu trúc dễ hiển thị
     */
    public function parseSpecs(string $rawSpecs): array {
        $specs = [
            'cpu'     => '',
            'ram'     => '',
            'ssd'     => '',
            'vga'     => '',
            'screen'  => '',
            'details' => []
        ];

        if (empty(trim($rawSpecs))) {
            return $specs;
        }

        $parts = explode('|', $rawSpecs);
        foreach ($parts as $part) {
            $item = trim($part);
            if (empty($item)) continue;

            $pair = explode(':', $item, 2);
            $key = trim($pair[0] ?? '');
            $val = trim($pair[1] ?? '');

            if (!empty($key)) {
                $specs['details'][$key] = $val;

                $lowerKey = mb_strtolower($key);
                if (str_contains($lowerKey, 'cpu') || str_contains($lowerKey, 'chip')) {
                    $specs['cpu'] = $val;
                } elseif (str_contains($lowerKey, 'ram')) {
                    $specs['ram'] = $val;
                } elseif (str_contains($lowerKey, 'ssd') || str_contains($lowerKey, 'ổ cứng') || str_contains($lowerKey, 'rom')) {
                    $specs['ssd'] = $val;
                } elseif (str_contains($lowerKey, 'vga') || str_contains($lowerKey, 'gpu') || str_contains($lowerKey, 'đồ họa')) {
                    $specs['vga'] = $val;
                } elseif (str_contains($lowerKey, 'màn hình') || str_contains($lowerKey, 'screen') || str_contains($lowerKey, 'display')) {
                    $specs['screen'] = $val;
                }
            }
        }

        return $specs;
    }
}
