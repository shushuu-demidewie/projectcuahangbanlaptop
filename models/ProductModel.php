<?php
/**
 * File: models/ProductModel.php
 * Thao tác với bảng `sanpham` (Laptop & Thiết bị)
 */

require_once __DIR__ . '/../config/Database.php';

class ProductModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách sản phẩm có bộ lọc (từ khóa, danh mục, thương hiệu) và phân trang
     * Chỉ lấy các sản phẩm chưa bị xóa mềm (isDeleted = 0)
     *
     * @param string $keyword
     * @param int|null $maDM
     * @param string|null $maTH
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getProducts(string $keyword = '', ?int $maDM = null, ?string $maTH = null, int $page = 1, int $limit = 10): array {
        $offset = ($page - 1) * $limit;
        $params = [];

        $sql = "SELECT 
                    sp.maSP, sp.tenSP, sp.giaBan, sp.soLuongTon, 
                    sp.maTH, th.tenTH, 
                    sp.maDM, dm.tenDM, 
                    sp.thoiGianBaoHanh, sp.thongSoKyThuat, sp.moTa, sp.viTriKho, 
                    sp.trangThai, sp.isDeleted, sp.hinhAnh
                FROM sanpham sp
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE sp.isDeleted = 0";

        if (!empty($keyword)) {
            $sql .= " AND (sp.tenSP LIKE :kw1 OR sp.maSP LIKE :kw2 OR th.tenTH LIKE :kw3)";
            $kwVal = "%" . trim($keyword) . "%";
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
        }

        if (!empty($maDM)) {
            $sql .= " AND sp.maDM = :maDM";
            $params[':maDM'] = $maDM;
        }

        if (!empty($maTH)) {
            $sql .= " AND sp.maTH = :maTH";
            $params[':maTH'] = $maTH;
        }

        $sql .= " ORDER BY sp.maSP DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        // Bind value cho LIMIT và OFFSET dạng số nguyên
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số lượng sản phẩm thỏa mãn điều kiện lọc (phục vụ phân trang)
     */
    public function countProducts(string $keyword = '', ?int $maDM = null, ?string $maTH = null): int {
        $params = [];
        $sql = "SELECT COUNT(*) FROM sanpham sp 
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE sp.isDeleted = 0";

        if (!empty($keyword)) {
            $sql .= " AND (sp.tenSP LIKE :kw1 OR sp.maSP LIKE :kw2 OR th.tenTH LIKE :kw3)";
            $kwVal = "%" . trim($keyword) . "%";
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
        }

        if (!empty($maDM)) {
            $sql .= " AND sp.maDM = :maDM";
            $params[':maDM'] = $maDM;
        }

        if (!empty($maTH)) {
            $sql .= " AND sp.maTH = :maTH";
            $params[':maTH'] = $maTH;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy thông tin chi tiết một sản phẩm theo mã SP
     */
    public function getProductById(string $maSP): array|false {
        $sql = "SELECT 
                    sp.*, 
                    dm.tenDM, 
                    th.tenTH
                FROM sanpham sp
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE sp.maSP = :maSP AND sp.isDeleted = 0 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maSP' => $maSP]);
        return $stmt->fetch();
    }

    /**
     * Kiểm tra mã sản phẩm đã tồn tại hay chưa
     */
    public function isProductCodeExists(string $maSP): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sanpham WHERE maSP = :maSP");
        $stmt->execute([':maSP' => $maSP]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Thêm mới laptop
     */
    public function addProduct(array $data): bool {
        $sql = "INSERT INTO sanpham 
                (maSP, tenSP, giaBan, soLuongTon, maTH, maDM, thoiGianBaoHanh, thongSoKyThuat, moTa, viTriKho, trangThai, isDeleted) 
                VALUES 
                (:maSP, :tenSP, :giaBan, :soLuongTon, :maTH, :maDM, :thoiGianBaoHanh, :thongSoKyThuat, :moTa, :viTriKho, :trangThai, 0)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':maSP'            => $data['maSP'],
            ':tenSP'           => $data['tenSP'],
            ':giaBan'          => $data['giaBan'],
            ':soLuongTon'      => $data['soLuongTon'] ?? 0,
            ':maTH'            => $data['maTH'],
            ':maDM'            => $data['maDM'],
            ':thoiGianBaoHanh' => $data['thoiGianBaoHanh'] ?? 12,
            ':thongSoKyThuat'  => $data['thongSoKyThuat'] ?? '',
            ':moTa'            => $data['moTa'] ?? '',
            ':viTriKho'        => $data['viTriKho'] ?? 'KHO_TONG',
            ':trangThai'       => $data['trangThai'] ?? 1
        ]);
    }

    /**
     * Cập nhật thông tin laptop
     */
    public function updateProduct(string $maSP, array $data): bool {
        $sql = "UPDATE sanpham 
                SET tenSP = :tenSP, 
                    giaBan = :giaBan, 
                    soLuongTon = :soLuongTon, 
                    maTH = :maTH, 
                    maDM = :maDM, 
                    thoiGianBaoHanh = :thoiGianBaoHanh, 
                    thongSoKyThuat = :thongSoKyThuat, 
                    moTa = :moTa, 
                    viTriKho = :viTriKho, 
                    trangThai = :trangThai 
                WHERE maSP = :maSP AND isDeleted = 0";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':maSP'            => $maSP,
            ':tenSP'           => $data['tenSP'],
            ':giaBan'          => $data['giaBan'],
            ':soLuongTon'      => $data['soLuongTon'] ?? 0,
            ':maTH'            => $data['maTH'],
            ':maDM'            => $data['maDM'],
            ':thoiGianBaoHanh' => $data['thoiGianBaoHanh'] ?? 12,
            ':thongSoKyThuat'  => $data['thongSoKyThuat'] ?? '',
            ':moTa'            => $data['moTa'] ?? '',
            ':viTriKho'        => $data['viTriKho'] ?? 'KHO_TONG',
            ':trangThai'       => $data['trangThai'] ?? 1
        ]);
    }

    /**
     * Xóa mềm sản phẩm (Đánh dấu isDeleted = 1)
     */
    public function softDeleteProduct(string $maSP): bool {
        $stmt = $this->db->prepare("UPDATE sanpham SET isDeleted = 1 WHERE maSP = :maSP");
        return $stmt->execute([':maSP' => $maSP]);
    }
}
