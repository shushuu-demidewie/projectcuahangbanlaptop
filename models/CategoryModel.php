<?php
/**
 * File: models/CategoryModel.php
 * Thao tác với bảng `danhmuc` (Danh mục laptop)
 */

require_once __DIR__ . '/../config/Database.php';

class CategoryModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách tất cả danh mục đang hoạt động (trangThai = 1)
     * @param bool $onlyActive Chỉ lấy danh mục đang hoạt động
     * @return array
     */
    public function getAllCategories(bool $onlyActive = true): array {
        $sql = "SELECT maDM, tenDM, maKM, moTa, trangThai FROM danhmuc";
        if ($onlyActive) {
            $sql .= " WHERE trangThai = 1";
        }
        $sql .= " ORDER BY tenDM ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Lấy thông tin 1 danh mục theo mã ID
     * @param int $maDM
     * @return array|false
     */
    public function getCategoryById(int $maDM): array|false {
        $stmt = $this->db->prepare("SELECT maDM, tenDM, maKM, moTa, trangThai FROM danhmuc WHERE maDM = :maDM LIMIT 1");
        $stmt->execute([':maDM' => $maDM]);
        return $stmt->fetch();
    }

    /**
     * Thêm danh mục mới
     */
    public function addCategory(array $data): bool {
        $sql = "INSERT INTO danhmuc (tenDM, maKM, moTa, trangThai) 
                VALUES (:tenDM, :maKM, :moTa, :trangThai)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':tenDM'     => $data['tenDM'],
            ':maKM'      => $data['maKM'] ?? null,
            ':moTa'      => $data['moTa'] ?? null,
            ':trangThai' => $data['trangThai'] ?? 1
        ]);
    }

    /**
     * Cập nhật danh mục
     */
    public function updateCategory(int $maDM, array $data): bool {
        $sql = "UPDATE danhmuc 
                SET tenDM = :tenDM, maKM = :maKM, moTa = :moTa, trangThai = :trangThai 
                WHERE maDM = :maDM";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':maDM'      => $maDM,
            ':tenDM'     => $data['tenDM'],
            ':maKM'      => $data['maKM'] ?? null,
            ':moTa'      => $data['moTa'] ?? null,
            ':trangThai' => $data['trangThai'] ?? 1
        ]);
    }

    /**
     * Xóa danh mục
     */
    public function deleteCategory(int $maDM): bool {
        $stmt = $this->db->prepare("DELETE FROM danhmuc WHERE maDM = :maDM");
        return $stmt->execute([':maDM' => $maDM]);
    }
}
