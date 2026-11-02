<?php
/**
 * File: models/WarrantyTrackingModel.php
 * Quản trị nghiệp vụ Tra cứu Bảo hành Điện tử dành cho Khách hàng
 * CSDL: PhieuBaoHanh, SerialSanPham, SanPham, KhachHang, ThuongHieu, DanhMuc
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class WarrantyTrackingModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Tra cứu thông tin bảo hành theo Mã Serial/IMEI hoặc Số điện thoại mua hàng
     *
     * @param string $keyword Mã Serial (VD: SN_DELL_3520_0001) hoặc Số điện thoại (VD: 0987654321)
     * @return array Danh sách thẻ bảo hành thỏa mãn điều kiện
     */
    public function searchWarranty(string $keyword): array {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        // Tách chuỗi số để tìm kiếm linh hoạt theo SĐT
        $digitsOnly = preg_replace('/[^0-9]/', '', $keyword);

        $sql = "SELECT 
                    pbh.maPhieuBH,
                    pbh.maSerial_IMEI,
                    pbh.maKH,
                    pbh.ngayBatDau,
                    pbh.ngayKetThuc,
                    pbh.moTaChinhSach,
                    pbh.trangThai AS trangThaiPhieu,
                    pbh.ghiChu AS ghiChuBaoHanh,
                    sn.trangThai AS trangThaiSerial,
                    sn.ngayXuatKho,
                    sp.maSP,
                    sp.tenSP,
                    sp.hinhAnh,
                    sp.thoiGianBaoHanh,
                    th.tenTH,
                    dm.tenDM,
                    kh.hoVaTen,
                    kh.soDienThoai,
                    kh.email,
                    DATEDIFF(pbh.ngayKetThuc, CURDATE()) AS soNgayConLai
                FROM phieubaohanh pbh
                INNER JOIN serialsanpham sn ON pbh.maSerial_IMEI = sn.maSerial_IMEI
                INNER JOIN sanpham sp ON sn.maSP = sp.maSP
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN khachhang kh ON pbh.maKH = kh.maKH
                WHERE pbh.maSerial_IMEI = :exactSerial
                   OR pbh.maSerial_IMEI LIKE :likeSerial
                   OR kh.soDienThoai = :exactPhone
                   OR pbh.maPhieuBH = :exactPhieu
                   OR (:hasDigits = 1 AND REPLACE(REPLACE(REPLACE(COALESCE(kh.soDienThoai, ''), ' ', ''), '.', ''), '-', '') LIKE :phoneKw)
                ORDER BY pbh.ngayKetThuc DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':exactSerial' => $keyword,
            ':likeSerial'  => '%' . $keyword . '%',
            ':exactPhone'  => $keyword,
            ':exactPhieu'  => $keyword,
            ':hasDigits'   => !empty($digitsOnly) ? 1 : 0,
            ':phoneKw'     => '%' . $digitsOnly . '%'
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Chuẩn hóa trạng thái ('ĐANG HIỆU LỰC' / 'HẾT HẠN BẢO HÀNH') và số ngày còn lại
        foreach ($results as &$row) {
            $daysLeft = (int)($row['soNgayConLai'] ?? 0);
            $dbStatus = strtoupper(trim((string)($row['trangThaiPhieu'] ?? '')));

            // Nếu còn hạn (DATEDIFF > 0) và chưa bị đánh dấu hủy/hết hạn
            if ($daysLeft > 0 && $dbStatus !== 'HET_HAN' && $dbStatus !== 'DA_HUY') {
                $row['trangThai']   = 'Còn hạn bảo hành';
                $row['isConHan']    = true;
                $row['badgeClass']  = 'bg-success';
                $row['statusText']  = 'ĐANG HIỆU LỰC';
            } else {
                $row['trangThai']   = 'Hết hạn bảo hành';
                $row['isConHan']    = false;
                $row['badgeClass']  = 'bg-secondary';
                $row['statusText']  = 'HẾT HẠN BẢO HÀNH';
            }

            // Gán mô tả chính sách mặc định nếu chưa có
            if (empty($row['moTaChinhSach'])) {
                $thoiGian = !empty($row['thoiGianBaoHanh']) ? $row['thoiGianBaoHanh'] . ' tháng' : '12 tháng';
                $row['moTaChinhSach'] = "Bảo hành chính hãng {$thoiGian}. Cam kết 1 đổi 1 trong 30 ngày nếu phát sinh lỗi phần cứng từ nhà sản xuất.";
            }
        }
        unset($row);

        return $results;
    }
}
