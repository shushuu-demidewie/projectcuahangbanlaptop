<?php
/**
 * File: models/TrackingModel.php
 * Nghiệp vụ tra cứu công khai cho khách hàng:
 * 1. Tra cứu bảo hành theo IMEI/Serial/SĐT
 * 2. Theo dõi hành trình và tiến độ đơn hàng Online
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class TrackingModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Tra cứu lịch sử bảo hành theo mã Serial/IMEI hoặc SĐT khách
     * JOIN PhieuBaoHanh, SerialSanPham, SanPham, KhachHang, ThuongHieu, DanhMuc
     */
    public function checkWarrantyBySerialOrPhone(string $keyword): array {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

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
                    kh.diaChi,
                    DATEDIFF(pbh.ngayKetThuc, NOW()) AS soNgayConLai,
                    CASE 
                        WHEN pbh.ngayKetThuc < NOW() THEN 1 
                        WHEN pbh.trangThai = 'HET_HAN' THEN 1
                        ELSE 0 
                    END AS isExpired
                FROM phieubaohanh pbh
                JOIN serialsanpham sn ON pbh.maSerial_IMEI = sn.maSerial_IMEI
                JOIN sanpham sp ON sn.maSP = sp.maSP
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                LEFT JOIN khachhang kh ON pbh.maKH = kh.maKH
                WHERE pbh.maSerial_IMEI = :exactSerial
                   OR kh.soDienThoai = :exactPhone
                   OR pbh.maSerial_IMEI LIKE :likeSerial
                   OR kh.soDienThoai LIKE :likePhone
                   OR pbh.maPhieuBH LIKE :likeCode
                ORDER BY pbh.ngayKetThuc DESC";

        $like = '%' . $keyword . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':exactSerial' => $keyword,
            ':exactPhone'  => $keyword,
            ':likeSerial'  => $like,
            ':likePhone'   => $like,
            ':likeCode'    => $like
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tra cứu tiến độ đơn hàng online theo mã đơn hàng hoặc kết hợp SĐT
     */
    public function trackOrderByCodeOrPhone(string $maDH, ?string $sdt = null): ?array {
        $maDH = trim($maDH);
        $sdt  = !empty($sdt) ? trim($sdt) : null;

        if ($maDH === '' && empty($sdt)) {
            return null;
        }

        $sql = "SELECT 
                    dh.*,
                    kh.hoVaTen AS tenKhachHang,
                    kh.soDienThoai AS sdtKhachHang,
                    kh.diaChi AS diaChiKhachHang,
                    udh.tenNguoiNhan,
                    udh.sdtNhanHang,
                    udh.diaChiGiaoHang,
                    udh.ghiChuKhachHang,
                    udh.maGiaoDichOnline,
                    udh.trangThaiThanhToan AS trangThaiOnline,
                    hd.maHD,
                    hd.ngayXuat AS ngayXuatHD,
                    hd.trangThaiThanhToan AS trangThaiHD,
                    acc.hoTen AS tenNguoiPhuTrach
                FROM donhang dh
                LEFT JOIN khachhang kh ON dh.maKH = kh.maKH
                LEFT JOIN userdathang udh ON dh.maDH = udh.maDH
                LEFT JOIN hoadon hd ON dh.maDH = hd.maDH
                LEFT JOIN accounts acc ON dh.nguoiPhuTrach = acc.maTk
                WHERE 1 = 1";

        $params = [];

        if ($maDH !== '') {
            $sql .= " AND dh.maDH = :maDH";
            $params[':maDH'] = $maDH;
        }

        if ($sdt !== null) {
            $sql .= " AND (kh.soDienThoai = :sdt1 OR udh.sdtNhanHang = :sdt2)";
            $params[':sdt1'] = $sdt;
            $params[':sdt2'] = $sdt;
        }

        $sql .= " ORDER BY dh.ngayTao DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // Lấy danh sách sản phẩm trong đơn và các Serial tương ứng đã gán
        $itemSql = "SELECT 
                        ctd.id AS idCTDH,
                        ctd.maSP,
                        ctd.soLuong,
                        ctd.donGia,
                        ctd.thanhTien,
                        sp.tenSP,
                        sp.hinhAnh,
                        sp.thoiGianBaoHanh,
                        th.tenTH
                    FROM chitietdonhang ctd
                    LEFT JOIN sanpham sp ON ctd.maSP = sp.maSP
                    LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                    WHERE ctd.maDH = :maDH";

        $itemStmt = $this->db->prepare($itemSql);
        $itemStmt->execute([':maDH' => $order['maDH']]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy serial đã gán cho từng sản phẩm
        $serialSql = "SELECT maSerial_IMEI, maSP, idChiTietDonHang, trangThai 
                      FROM serialsanpham 
                      WHERE idChiTietDonHang = :idCTDH";
        $serialStmt = $this->db->prepare($serialSql);

        foreach ($items as &$it) {
            $serialStmt->execute([':idCTDH' => $it['idCTDH']]);
            $it['assignedSerials'] = $serialStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($it);

        $order['items'] = $items;

        // Phân tích trạng thái đơn thành bước tiến độ (Timeline 4 bước)
        // Bước 1: Tiếp nhận đơn
        // Bước 2: Đóng gói & Gán Serial
        // Bước 3: Đang vận chuyển
        // Bước 4: Hoàn thành / Đã giao
        $status = strtoupper($order['trangThaiDon'] ?? '');
        $currentStep = 1;

        if ($status === 'HOAN_THANH') {
            $currentStep = 4;
        } elseif ($status === 'DANG_GIAO' || $status === 'DANG_VAN_CHUYEN') {
            $currentStep = 3;
        } elseif (in_array($status, ['DANG_DONG_GOI', 'DANG_XU_LY', 'DA_XAC_NHAN'], true)) {
            $currentStep = 2;
        } elseif ($status === 'CHO_DUYET') {
            $currentStep = 1;
        } elseif ($status === 'DA_HUY') {
            $currentStep = -1; // Đã hủy
        }

        $order['timelineStep'] = $currentStep;

        return $order;
    }
}
