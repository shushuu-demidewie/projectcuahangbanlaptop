<?php
/**
 * File: models/BrandModel.php
 * Thao tác với bảng `thuonghieu` (Thương hiệu laptop: Dell, Asus, Lenovo, HP, Apple...)
 */

require_once __DIR__ . '/../config/Database.php';

class BrandModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách tất cả thương hiệu
     * @param bool $onlyActive Chỉ lấy thương hiệu đang hoạt động (trangThai = 1)
     * @return array
     */
    public function getAllBrands(bool $onlyActive = true): array {
        $sql = "SELECT maTH, tenTH, logo, xuatXu, moTa, trangThai FROM thuonghieu";
        if ($onlyActive) {
            $sql .= " WHERE trangThai = 1";
        }
        $sql .= " ORDER BY tenTH ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Lấy thông tin 1 thương hiệu theo mã
     * @param string $maTH
     * @return array|false
     */
    public function getBrandById(string $maTH): array|false {
        $stmt = $this->db->prepare("SELECT maTH, tenTH, logo, xuatXu, moTa, trangThai FROM thuonghieu WHERE maTH = :maTH LIMIT 1");
        $stmt->execute([':maTH' => $maTH]);
        return $stmt->fetch();
    }

    /**
     * Thêm thương hiệu mới
     */
    public function addBrand(array $data): bool {
        $sql = "INSERT INTO thuonghieu (maTH, tenTH, logo, xuatXu, moTa, trangThai) 
                VALUES (:maTH, :tenTH, :logo, :xuatXu, :moTa, :trangThai)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':maTH'      => $data['maTH'],
            ':tenTH'     => $data['tenTH'],
            ':logo'      => $data['logo'] ?? null,
            ':xuatXu'    => $data['xuatXu'] ?? null,
            ':moTa'      => $data['moTa'] ?? null,
            ':trangThai' => $data['trangThai'] ?? 1
        ]);
    }

    /**
     * Cập nhật thương hiệu
     */
    public function updateBrand(string $maTH, array $data): bool {
        $sql = "UPDATE thuonghieu 
                SET tenTH = :tenTH, logo = :logo, xuatXu = :xuatXu, moTa = :moTa, trangThai = :trangThai 
                WHERE maTH = :maTH";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':maTH'      => $maTH,
            ':tenTH'     => $data['tenTH'],
            ':logo'      => $data['logo'] ?? null,
            ':xuatXu'    => $data['xuatXu'] ?? null,
            ':moTa'      => $data['moTa'] ?? null,
            ':trangThai' => $data['trangThai'] ?? 1
        ]);
    }

    /**
     * Xóa thương hiệu
     */
    public function deleteBrand(string $maTH): bool {
        $stmt = $this->db->prepare("DELETE FROM thuonghieu WHERE maTH = :maTH");
        return $stmt->execute([':maTH' => $maTH]);
    }
}
