<?php
/**
 * File: models/PromotionModel.php
 * Nghiệp vụ Quản Lý Khuyến Mãi & Mã Giảm Giá (Class 16 KhuyenMai & Class 2 DanhMuc)
 * Dành cho Quản Trị Viên (Admin) và Nhân Viên Bán Hàng / Kế Toán
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class PromotionModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách tất cả khuyến mãi, hỗ trợ lọc theo trạng thái và tìm kiếm từ khóa
     */
    public function getAllPromotions(?int $status = null, ?string $keyword = null): array {
        $sql = "SELECT 
                    km.*,
                    (SELECT COUNT(*) FROM danhmuc dm WHERE dm.maKM = km.maKM) AS soDanhMucApDung
                FROM khuyenmai km
                WHERE 1 = 1";
        $params = [];

        if ($status !== null && $status >= 0) {
            $sql .= " AND km.trangThai = :trangThai";
            $params[':trangThai'] = $status;
        }

        if (!empty($keyword)) {
            $sql .= " AND (km.maKM LIKE :kw1 OR km.tenKM LIKE :kw2)";
            $params[':kw1'] = '%' . trim($keyword) . '%';
            $params[':kw2'] = '%' . trim($keyword) . '%';
        }

        $sql .= " ORDER BY km.trangThai DESC, km.ngayKetThuc DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $now = date('Y-m-d H:i:s');
        foreach ($rows as &$item) {
            $item['soDanhMucApDung'] = (int)$item['soDanhMucApDung'];
            $item['giaTriGiam']      = (float)$item['giaTriGiam'];
            $item['mucGiamToiDa']    = !is_null($item['mucGiamToiDa']) ? (float)$item['mucGiamToiDa'] : null;
            $item['donHangToiThieu'] = (float)$item['donHangToiThieu'];
            $item['trangThai']       = (int)$item['trangThai'];

            // Xác định trạng thái thực tế dựa trên ngày giờ hiện tại
            if ($item['trangThai'] === 0) {
                $item['tinhTrang']     = 'TAM_DUNG';
                $item['tinhTrangText'] = 'Tạm dừng';
                $item['badgeClass']    = 'bg-secondary';
            } elseif ($now < $item['ngayBatDau']) {
                $item['tinhTrang']     = 'CHUA_BAT_DAU';
                $item['tinhTrangText'] = 'Sắp diễn ra';
                $item['badgeClass']    = 'bg-info text-dark';
            } elseif ($now > $item['ngayKetThuc']) {
                $item['tinhTrang']     = 'HET_HAN';
                $item['tinhTrangText'] = 'Đã kết thúc';
                $item['badgeClass']    = 'bg-danger';
            } else {
                $item['tinhTrang']     = 'DANG_DIEN_RA';
                $item['tinhTrangText'] = 'Đang diễn ra';
                $item['badgeClass']    = 'bg-success';
            }
        }
        unset($item);

        return $rows;
    }

    /**
     * Lấy chi tiết thông tin một mã khuyến mãi theo maKM
     */
    public function getPromotionById(string $maKM): ?array {
        $sql = "SELECT 
                    km.*,
                    (SELECT COUNT(*) FROM danhmuc dm WHERE dm.maKM = km.maKM) AS soDanhMucApDung
                FROM khuyenmai km
                WHERE km.maKM = :maKM LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maKM' => trim($maKM)]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            return null;
        }

        $now = date('Y-m-d H:i:s');
        $item['soDanhMucApDung'] = (int)$item['soDanhMucApDung'];
        $item['giaTriGiam']      = (float)$item['giaTriGiam'];
        $item['mucGiamToiDa']    = !is_null($item['mucGiamToiDa']) ? (float)$item['mucGiamToiDa'] : null;
        $item['donHangToiThieu'] = (float)$item['donHangToiThieu'];
        $item['trangThai']       = (int)$item['trangThai'];

        if ($item['trangThai'] === 0) {
            $item['tinhTrang']     = 'TAM_DUNG';
            $item['tinhTrangText'] = 'Tạm dừng';
            $item['badgeClass']    = 'bg-secondary';
        } elseif ($now < $item['ngayBatDau']) {
            $item['tinhTrang']     = 'CHUA_BAT_DAU';
            $item['tinhTrangText'] = 'Sắp diễn ra';
            $item['badgeClass']    = 'bg-info text-dark';
        } elseif ($now > $item['ngayKetThuc']) {
            $item['tinhTrang']     = 'HET_HAN';
            $item['tinhTrangText'] = 'Đã kết thúc';
            $item['badgeClass']    = 'bg-danger';
        } else {
            $item['tinhTrang']     = 'DANG_DIEN_RA';
            $item['tinhTrangText'] = 'Đang diễn ra';
            $item['badgeClass']    = 'bg-success';
        }

        return $item;
    }

    /**
     * Kiểm tra mã khuyến mãi đã tồn tại hay chưa
     */
    public function exists(string $maKM): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM khuyenmai WHERE maKM = :maKM LIMIT 1");
        $stmt->execute([':maKM' => trim($maKM)]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Lấy danh sách các khuyến mãi đang kích hoạt để đưa vào dropdown chọn
     */
    public function getActivePromotions(): array {
        $sql = "SELECT maKM, tenKM, loaiKM, giaTriGiam, mucGiamToiDa, donHangToiThieu, ngayBatDau, ngayKetThuc 
                FROM khuyenmai 
                WHERE trangThai = 1 AND ngayKetThuc >= NOW()
                ORDER BY tenKM ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm mới chương trình khuyến mãi / Voucher
     */
    public function createPromotion(array $data): array {
        $maKM            = strtoupper(trim($data['maKM'] ?? ''));
        $tenKM           = trim($data['tenKM'] ?? '');
        $loaiKM          = trim($data['loaiKM'] ?? 'PHAN_TRAM');
        $giaTriGiam      = (float)($data['giaTriGiam'] ?? 0);
        $mucGiamToiDa    = !empty($data['mucGiamToiDa']) ? (float)$data['mucGiamToiDa'] : null;
        $donHangToiThieu = (float)($data['donHangToiThieu'] ?? 0);
        $ngayBatDau      = trim($data['ngayBatDau'] ?? '');
        $ngayKetThuc     = trim($data['ngayKetThuc'] ?? '');
        $trangThai       = isset($data['trangThai']) ? (int)$data['trangThai'] : 1;

        // Validation
        if (empty($maKM)) {
            return ['success' => false, 'message' => 'Vui lòng nhập Mã khuyến mãi (Voucher Code)!'];
        }

        if (!preg_match('/^[A-Z0-9_\-]{3,50}$/', $maKM)) {
            return ['success' => false, 'message' => 'Mã khuyến mãi chỉ chứa chữ cái tiếng Anh, số, dấu gạch ngang (-) hoặc gạch dưới (_), từ 3 đến 50 ký tự!'];
        }

        if ($this->exists($maKM)) {
            return ['success' => false, 'message' => "Mã khuyến mãi '{$maKM}' đã tồn tại trong hệ thống, vui lòng chọn mã khác!"];
        }

        if (empty($tenKM)) {
            return ['success' => false, 'message' => 'Vui lòng nhập Tên chương trình khuyến mãi!'];
        }

        if (!in_array($loaiKM, ['PHAN_TRAM', 'TIEN_MAT'], true)) {
            return ['success' => false, 'message' => 'Loại khuyến mãi không hợp lệ (chỉ chấp nhận Phần trăm hoặc Tiền mặt)!'];
        }

        if ($giaTriGiam <= 0) {
            return ['success' => false, 'message' => 'Giá trị giảm giá phải lớn hơn 0!'];
        }

        if ($loaiKM === 'PHAN_TRAM' && $giaTriGiam > 100) {
            return ['success' => false, 'message' => 'Giảm giá theo phần trăm không được vượt quá 100%!'];
        }

        if ($donHangToiThieu < 0) {
            return ['success' => false, 'message' => 'Đơn hàng tối thiểu không được âm!'];
        }

        if (empty($ngayBatDau) || empty($ngayKetThuc)) {
            return ['success' => false, 'message' => 'Vui lòng chọn ngày bắt đầu và ngày kết thúc!'];
        }

        // Chuẩn hóa ngày giờ (nếu chỉ có ngày Y-m-d thì thêm giờ)
        if (strlen($ngayBatDau) === 10) {
            $ngayBatDau .= ' 00:00:00';
        }
        if (strlen($ngayKetThuc) === 10) {
            $ngayKetThuc .= ' 23:59:59';
        }

        if ($ngayKetThuc <= $ngayBatDau) {
            return ['success' => false, 'message' => 'Ngày kết thúc phải lớn hơn ngày bắt đầu khuyến mãi!'];
        }

        // Nếu loại là Tiền mặt thì không cần mức giảm tối đa
        if ($loaiKM === 'TIEN_MAT') {
            $mucGiamToiDa = $giaTriGiam;
        }

        $sql = "INSERT INTO khuyenmai (
                    maKM, tenKM, loaiKM, giaTriGiam, mucGiamToiDa, 
                    donHangToiThieu, ngayBatDau, ngayKetThuc, trangThai
                ) VALUES (
                    :maKM, :tenKM, :loaiKM, :giaTriGiam, :mucGiamToiDa, 
                    :donHangToiThieu, :ngayBatDau, :ngayKetThuc, :trangThai
                )";

        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':maKM'            => $maKM,
            ':tenKM'           => $tenKM,
            ':loaiKM'          => $loaiKM,
            ':giaTriGiam'      => $giaTriGiam,
            ':mucGiamToiDa'    => $mucGiamToiDa,
            ':donHangToiThieu' => $donHangToiThieu,
            ':ngayBatDau'      => $ngayBatDau,
            ':ngayKetThuc'     => $ngayKetThuc,
            ':trangThai'       => $trangThai
        ]);

        if ($ok) {
            return ['success' => true, 'message' => "Tạo chương trình khuyến mãi '{$tenKM}' thành công!", 'maKM' => $maKM];
        }

        return ['success' => false, 'message' => 'Lỗi khi lưu vào cơ sở dữ liệu!'];
    }

    /**
     * Chỉnh sửa thông tin khuyến mãi
     */
    public function updatePromotion(string $maKM, array $data): array {
        $old = $this->getPromotionById($maKM);
        if (!$old) {
            return ['success' => false, 'message' => 'Không tìm thấy chương trình khuyến mãi cần cập nhật!'];
        }

        $tenKM           = trim($data['tenKM'] ?? '');
        $loaiKM          = trim($data['loaiKM'] ?? 'PHAN_TRAM');
        $giaTriGiam      = (float)($data['giaTriGiam'] ?? 0);
        $mucGiamToiDa    = !empty($data['mucGiamToiDa']) ? (float)$data['mucGiamToiDa'] : null;
        $donHangToiThieu = (float)($data['donHangToiThieu'] ?? 0);
        $ngayBatDau      = trim($data['ngayBatDau'] ?? '');
        $ngayKetThuc     = trim($data['ngayKetThuc'] ?? '');
        $trangThai       = isset($data['trangThai']) ? (int)$data['trangThai'] : 1;

        if (empty($tenKM)) {
            return ['success' => false, 'message' => 'Vui lòng nhập Tên chương trình khuyến mãi!'];
        }

        if (!in_array($loaiKM, ['PHAN_TRAM', 'TIEN_MAT'], true)) {
            return ['success' => false, 'message' => 'Loại khuyến mãi không hợp lệ!'];
        }

        if ($giaTriGiam <= 0) {
            return ['success' => false, 'message' => 'Giá trị giảm giá phải lớn hơn 0!'];
        }

        if ($loaiKM === 'PHAN_TRAM' && $giaTriGiam > 100) {
            return ['success' => false, 'message' => 'Giảm giá theo phần trăm không được vượt quá 100%!'];
        }

        if ($donHangToiThieu < 0) {
            return ['success' => false, 'message' => 'Đơn hàng tối thiểu không được âm!'];
        }

        if (empty($ngayBatDau) || empty($ngayKetThuc)) {
            return ['success' => false, 'message' => 'Vui lòng chọn ngày bắt đầu và ngày kết thúc!'];
        }

        if (strlen($ngayBatDau) === 10) {
            $ngayBatDau .= ' 00:00:00';
        }
        if (strlen($ngayKetThuc) === 10) {
            $ngayKetThuc .= ' 23:59:59';
        }

        if ($ngayKetThuc <= $ngayBatDau) {
            return ['success' => false, 'message' => 'Ngày kết thúc phải lớn hơn ngày bắt đầu!'];
        }

        if ($loaiKM === 'TIEN_MAT') {
            $mucGiamToiDa = $giaTriGiam;
        }

        $sql = "UPDATE khuyenmai SET
                    tenKM           = :tenKM,
                    loaiKM          = :loaiKM,
                    giaTriGiam      = :giaTriGiam,
                    mucGiamToiDa    = :mucGiamToiDa,
                    donHangToiThieu = :donHangToiThieu,
                    ngayBatDau      = :ngayBatDau,
                    ngayKetThuc     = :ngayKetThuc,
                    trangThai       = :trangThai
                WHERE maKM = :maKM";

        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':tenKM'           => $tenKM,
            ':loaiKM'          => $loaiKM,
            ':giaTriGiam'      => $giaTriGiam,
            ':mucGiamToiDa'    => $mucGiamToiDa,
            ':donHangToiThieu' => $donHangToiThieu,
            ':ngayBatDau'      => $ngayBatDau,
            ':ngayKetThuc'     => $ngayKetThuc,
            ':trangThai'       => $trangThai,
            ':maKM'            => $maKM
        ]);

        if ($ok) {
            return ['success' => true, 'message' => "Cập nhật khuyến mãi '{$tenKM}' thành công!"];
        }

        return ['success' => false, 'message' => 'Lỗi khi cập nhật cơ sở dữ liệu!'];
    }

    /**
     * Đổi trạng thái Kích hoạt (1) <-> Tạm dừng (0) nhanh chóng
     */
    public function togglePromotionStatus(string $maKM): bool {
        $old = $this->getPromotionById($maKM);
        if (!$old) {
            return false;
        }

        $newStatus = ($old['trangThai'] === 1) ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE khuyenmai SET trangThai = :newStatus WHERE maKM = :maKM");
        return $stmt->execute([
            ':newStatus' => $newStatus,
            ':maKM'      => $maKM
        ]);
    }

    /**
     * Xóa khuyến mãi (Nếu đã phát sinh đơn hàng thì cảnh báo không xóa cứng, nếu chưa dùng thì gỡ khỏi danh mục và xóa)
     */
    public function deletePromotion(string $maKM): array {
        // Kiểm tra xem mã KM đã được áp dụng trong đơn hàng nào chưa
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM donhang WHERE maKM = :maKM");
        $stmtCheck->execute([':maKM' => $maKM]);
        $usedCount = (int)$stmtCheck->fetchColumn();

        if ($usedCount > 0) {
            // Không xóa cứng để đảm bảo toàn vẹn dữ liệu đơn hàng cũ, chuyển sang Tạm dừng
            $this->db->prepare("UPDATE khuyenmai SET trangThai = 0 WHERE maKM = :maKM")->execute([':maKM' => $maKM]);
            return [
                'success' => false, 
                'message' => "Mã khuyến mãi này đã được sử dụng trong {$usedCount} đơn hàng trong quá khứ! Hệ thống đã tự động chuyển trạng thái sang 'Tạm dừng' thay vì xóa hoàn toàn để đảm bảo tính toàn vẹn chứng từ tài chính."
            ];
        }

        // Gỡ liên kết khỏi bảng danhmuc
        $this->db->prepare("UPDATE danhmuc SET maKM = NULL WHERE maKM = :maKM")->execute([':maKM' => $maKM]);

        // Xóa khỏi bảng khuyenmai
        $stmtDel = $this->db->prepare("DELETE FROM khuyenmai WHERE maKM = :maKM");
        $ok = $stmtDel->execute([':maKM' => $maKM]);

        return $ok ? ['success' => true, 'message' => 'Đã xóa chương trình khuyến mãi thành công!'] : ['success' => false, 'message' => 'Lỗi khi xóa khuyến mãi!'];
    }

    /**
     * Kiểm tra tính hợp lệ của mã khuyến mãi và tính số tiền giảm giá thực tế
     * Phục vụ cả API Bán hàng tại quầy (POS) lẫn Khách mua Online (Checkout)
     */
    public function checkPromotionValidity(string $maKM, float $orderTotal, array $maDMList = []): array {
        $code = strtoupper(trim($maKM));
        if (empty($code)) {
            return [
                'valid'    => false,
                'discount' => 0.0,
                'message'  => 'Vui lòng nhập mã khuyến mãi / voucher!'
            ];
        }

        $km = $this->getPromotionById($code);
        if (!$km) {
            return [
                'valid'    => false,
                'discount' => 0.0,
                'message'  => 'Mã khuyến mãi không tồn tại trong hệ thống!'
            ];
        }

        if ($km['trangThai'] !== 1) {
            return [
                'valid'    => false,
                'discount' => 0.0,
                'message'  => 'Mã khuyến mãi này hiện đang tạm dừng hoạt động!'
            ];
        }

        $now = date('Y-m-d H:i:s');
        if ($now < $km['ngayBatDau']) {
            return [
                'valid'    => false,
                'discount' => 0.0,
                'message'  => 'Chương trình khuyến mãi này chưa đến thời gian áp dụng (Bắt đầu từ: ' . date('d/m/Y H:i', strtotime($km['ngayBatDau'])) . ')!'
            ];
        }

        if ($now > $km['ngayKetThuc']) {
            return [
                'valid'    => false,
                'discount' => 0.0,
                'message'  => 'Mã khuyến mãi đã hết hạn sử dụng vào ngày ' . date('d/m/Y H:i', strtotime($km['ngayKetThuc'])) . '!'
            ];
        }

        $minOrder = (float)$km['donHangToiThieu'];
        if ($orderTotal < $minOrder) {
            return [
                'valid'    => false,
                'discount' => 0.0,
                'message'  => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($minOrder, 0, ',', '.') . ' đ để áp dụng mã này (Hiện tại: ' . number_format($orderTotal, 0, ',', '.') . ' đ)!'
            ];
        }

        // Tính số tiền giảm giá
        $discount = 0.0;
        if ($km['loaiKM'] === 'PHAN_TRAM') {
            $discount = ($orderTotal * (float)$km['giaTriGiam']) / 100.0;
            if (!empty($km['mucGiamToiDa']) && (float)$km['mucGiamToiDa'] > 0) {
                $discount = min($discount, (float)$km['mucGiamToiDa']);
            }
        } else {
            // TIEN_MAT
            $discount = (float)$km['giaTriGiam'];
        }

        // Số tiền giảm không được vượt quá tổng tiền hàng
        $discount = min($discount, $orderTotal);
        $finalTotal = max(0, $orderTotal - $discount);

        return [
            'valid'           => true,
            'maKM'            => $km['maKM'],
            'tenKM'           => $km['tenKM'],
            'loaiKM'          => $km['loaiKM'],
            'giaTriGiam'      => (float)$km['giaTriGiam'],
            'mucGiamToiDa'    => !is_null($km['mucGiamToiDa']) ? (float)$km['mucGiamToiDa'] : null,
            'donHangToiThieu' => $minOrder,
            'discount'        => $discount,
            'finalTotal'      => $finalTotal,
            'message'         => 'Áp dụng mã giảm giá thành công! Giảm ngay ' . number_format($discount, 0, ',', '.') . ' đ',
            'promotion'       => $km
        ];
    }

    /**
     * Lấy toàn bộ danh mục kèm mã khuyến mãi đang được gán và số lượng laptop thuộc danh mục
     */
    public function getCategoriesWithPromotion(): array {
        $sql = "SELECT 
                    dm.maDM, 
                    dm.tenDM, 
                    dm.maKM, 
                    dm.moTa, 
                    dm.trangThai AS dmTrangThai,
                    km.tenKM, 
                    km.loaiKM, 
                    km.giaTriGiam, 
                    km.mucGiamToiDa, 
                    km.ngayBatDau, 
                    km.ngayKetThuc, 
                    km.trangThai AS kmTrangThai,
                    (SELECT COUNT(*) FROM sanpham sp WHERE sp.maDM = dm.maDM AND sp.isDeleted = 0) AS soLaptop
                FROM danhmuc dm
                LEFT JOIN khuyenmai km ON dm.maKM = km.maKM
                ORDER BY dm.maDM ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gán hoặc gỡ mã khuyến mãi áp dụng cho một danh mục laptop
     */
    public function assignPromotionToCategory(int $maDM, ?string $maKM): bool {
        $cleanMaKM = !empty($maKM) ? trim($maKM) : null;

        if ($cleanMaKM !== null) {
            // Kiểm tra mã KM có tồn tại hay không
            if (!$this->exists($cleanMaKM)) {
                return false;
            }
        }

        $stmt = $this->db->prepare("UPDATE danhmuc SET maKM = :maKM WHERE maDM = :maDM");
        return $stmt->execute([
            ':maKM' => $cleanMaKM,
            ':maDM' => $maDM
        ]);
    }
}
