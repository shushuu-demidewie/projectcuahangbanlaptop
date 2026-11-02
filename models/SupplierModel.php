<?php
/**
 * File: models/SupplierModel.php
 * Thao tác với bảng `nhacungcap` (Nhà cung cấp linh kiện & Laptop)
 */

require_once __DIR__ . '/../config/Database.php';

class SupplierModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách tất cả nhà cung cấp đang hợp tác (trangThai = 1)
     */
    public function getAllSuppliers(bool $onlyActive = true): array {
        $sql = "SELECT maNCC, tenCongTy, soDienThoai, email, diaChiTruSo, quocTich, maSoThue, trangThai 
                FROM nhacungcap";
        if ($onlyActive) {
            $sql .= " WHERE trangThai = 1";
        }
        $sql .= " ORDER BY tenCongTy ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Lấy thông tin 1 nhà cung cấp theo mã NCC
     */
    public function getSupplierById(string $maNCC): array|false {
        $stmt = $this->db->prepare("SELECT * FROM nhacungcap WHERE maNCC = :maNCC LIMIT 1");
        $stmt->execute([':maNCC' => $maNCC]);
        return $stmt->fetch();
    }
}
