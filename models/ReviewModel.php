<?php
/**
 * File: models/ReviewModel.php
 * Quản lý Đánh giá & Chấm sao sản phẩm (1 - 5 sao)
 */

require_once __DIR__ . '/../config/Database.php';

class ReviewModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách đánh giá của một sản phẩm
     */
    public function getReviewsByProduct(string $maSP, int $limit = 20): array {
        $sql = "SELECT id, maSP, hoTen, soDienThoai, soSao, noiDung, ngayDanhGia, trangThai
                FROM danhgia
                WHERE maSP = :maSP AND trangThai = 'approved'
                ORDER BY ngayDanhGia DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':maSP', $maSP);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tính toán tổng quan đánh giá: Điểm trung bình, tổng số lượt và phân bố sao
     */
    public function getRatingSummary(string $maSP): array {
        $sql = "SELECT soSao, COUNT(*) as sl 
                FROM danhgia 
                WHERE maSP = :maSP AND trangThai = 'approved' 
                GROUP BY soSao";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maSP' => $maSP]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $starCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $totalReviews = 0;
        $totalStars = 0;

        foreach ($rows as $r) {
            $star = (int)$r['soSao'];
            $count = (int)$r['sl'];
            if (isset($starCounts[$star])) {
                $starCounts[$star] = $count;
            }
            $totalReviews += $count;
            $totalStars += ($star * $count);
        }

        $avgRating = $totalReviews > 0 ? round($totalStars / $totalReviews, 1) : 5.0;

        return [
            'avgRating'    => $avgRating,
            'totalReviews' => $totalReviews,
            'starCounts'   => $starCounts
        ];
    }

    /**
     * Thêm mới đánh giá
     */
    public function addReview(array $data): bool {
        $sql = "INSERT INTO danhgia (maSP, hoTen, soDienThoai, soSao, noiDung, ngayDanhGia, trangThai)
                VALUES (:maSP, :hoTen, :soDienThoai, :soSao, :noiDung, NOW(), 'approved')";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':maSP'        => trim($data['maSP']),
            ':hoTen'       => trim($data['hoTen']),
            ':soDienThoai' => trim($data['soDienThoai'] ?? ''),
            ':soSao'       => max(1, min(5, (int)($data['soSao'] ?? 5))),
            ':noiDung'     => trim($data['noiDung'])
        ]);
    }
}
