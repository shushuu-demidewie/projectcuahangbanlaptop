<?php
/**
 * File: models/OrderModel.php
 * Thao tác với các bảng: `donhang`, `chitietdonhang`, `userdathang`, `hoadon`, `phieubaohanh`
 */

require_once __DIR__ . '/../config/Database.php';

class OrderModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách đơn hàng có bộ lọc kênh bán, trạng thái, tìm kiếm và phân trang
     */
    public function getOrders(string $channel = '', string $status = '', string $keyword = '', int $page = 1, int $limit = 10): array {
        $offset = ($page - 1) * $limit;
        $params = [];

        $sql = "SELECT 
                    dh.maDH, dh.maKH, kh.hoVaTen, kh.soDienThoai, 
                    dh.nguoiPhuTrach, acc.hoTen AS tenNguoiPhuTrach, 
                    dh.ngayTao, dh.kenhBan, dh.phuongThucThanhToan, dh.phuongThucGiaoHang, 
                    dh.phiVanChuyen, dh.tongTienHang, dh.tienGiamGia, dh.tongThanhToan, 
                    dh.trangThaiDon,
                    udh.tenNguoiNhan, udh.sdtNhanHang
                FROM donhang dh
                LEFT JOIN khachhang kh ON dh.maKH = kh.maKH
                LEFT JOIN accounts acc ON dh.nguoiPhuTrach = acc.maTk
                LEFT JOIN userdathang udh ON dh.maDH = udh.maDH
                WHERE 1=1";

        if (!empty($channel)) {
            $sql .= " AND dh.kenhBan = :channel";
            $params[':channel'] = $channel;
        }

        if (!empty($status)) {
            $sql .= " AND dh.trangThaiDon = :status";
            $params[':status'] = $status;
        }

        if (!empty($keyword)) {
            $sql .= " AND (dh.maDH LIKE :kw1 OR kh.hoVaTen LIKE :kw2 OR kh.soDienThoai LIKE :kw3 OR udh.tenNguoiNhan LIKE :kw4 OR udh.sdtNhanHang LIKE :kw5)";
            $kwVal = "%" . trim($keyword) . "%";
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
            $params[':kw4'] = $kwVal;
            $params[':kw5'] = $kwVal;
        }

        $sql .= " ORDER BY dh.ngayTao DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số đơn hàng thỏa mãn bộ lọc (phục vụ phân trang)
     */
    public function countOrders(string $channel = '', string $status = '', string $keyword = ''): int {
        $params = [];
        $sql = "SELECT COUNT(*) 
                FROM donhang dh
                LEFT JOIN khachhang kh ON dh.maKH = kh.maKH
                LEFT JOIN userdathang udh ON dh.maDH = udh.maDH
                WHERE 1=1";

        if (!empty($channel)) {
            $sql .= " AND dh.kenhBan = :channel";
            $params[':channel'] = $channel;
        }

        if (!empty($status)) {
            $sql .= " AND dh.trangThaiDon = :status";
            $params[':status'] = $status;
        }

        if (!empty($keyword)) {
            $sql .= " AND (dh.maDH LIKE :kw1 OR kh.hoVaTen LIKE :kw2 OR kh.soDienThoai LIKE :kw3 OR udh.tenNguoiNhan LIKE :kw4 OR udh.sdtNhanHang LIKE :kw5)";
            $kwVal = "%" . trim($keyword) . "%";
            $params[':kw1'] = $kwVal;
            $params[':kw2'] = $kwVal;
            $params[':kw3'] = $kwVal;
            $params[':kw4'] = $kwVal;
            $params[':kw5'] = $kwVal;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Thống kê số lượng đơn theo các trạng thái
     */
    public function getOrderStats(): array {
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN trangThaiDon = 'CHO_DUYET' THEN 1 ELSE 0 END) AS choDuyet,
                    SUM(CASE WHEN trangThaiDon = 'DANG_GIAO' THEN 1 ELSE 0 END) AS dangGiao,
                    SUM(CASE WHEN trangThaiDon = 'HOAN_THANH' THEN 1 ELSE 0 END) AS hoanThanh,
                    SUM(CASE WHEN trangThaiDon = 'DA_HUY' THEN 1 ELSE 0 END) AS daHuy
                FROM donhang";
        $stmt = $this->db->query($sql);
        return $stmt->fetch() ?: ['total' => 0, 'choDuyet' => 0, 'dangGiao' => 0, 'hoanThanh' => 0, 'daHuy' => 0];
    }

    /**
     * Lấy thông tin tổng quan của 1 đơn hàng (kèm thông tin khách hàng, giao hàng online)
     */
    public function getOrderDetail(string $maDH): array|false {
        $sql = "SELECT 
                    dh.*, 
                    kh.hoVaTen AS tenKhachHangGoc, kh.soDienThoai AS sdtKhachHangGoc, kh.email AS emailKhachHang, kh.diaChi AS diaChiKhachHangGoc,
                    acc.hoTen AS tenNguoiPhuTrach,
                    udh.idDatHang, udh.tenNguoiNhan, udh.sdtNhanHang, udh.diaChiGiaoHang, udh.ghiChuKhachHang, udh.maGiaoDichOnline, udh.trangThaiThanhToan AS trangThaiOnline,
                    hd.maHD, hd.ngayXuat AS ngayXuatHD, hd.trangThaiThanhToan AS trangThaiHoaDon
                FROM donhang dh
                LEFT JOIN khachhang kh ON dh.maKH = kh.maKH
                LEFT JOIN accounts acc ON dh.nguoiPhuTrach = acc.maTk
                LEFT JOIN userdathang udh ON dh.maDH = udh.maDH
                LEFT JOIN hoadon hd ON dh.maDH = hd.maDH
                WHERE dh.maDH = :maDH 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maDH' => $maDH]);
        return $stmt->fetch();
    }

    /**
     * Lấy danh sách sản phẩm trong đơn hàng kèm các số Serial/IMEI đã gán
     */
    public function getOrderItems(string $maDH): array {
        $sql = "SELECT 
                    ctd.id, ctd.maDH, ctd.maSP, sp.tenSP, sp.thoiGianBaoHanh, sp.giaBan, 
                    th.tenTH, 
                    ctd.soLuong, ctd.donGia, ctd.thanhTien
                FROM chitietdonhang ctd
                LEFT JOIN sanpham sp ON ctd.maSP = sp.maSP
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE ctd.maDH = :maDH
                ORDER BY ctd.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maDH' => $maDH]);
        $items = $stmt->fetchAll();

        // Lấy danh sách Serial đã được gán cho từng dòng chi tiết đơn hàng
        foreach ($items as &$item) {
            $item['assignedSerials'] = $this->getAssignedSerials((int)$item['id']);
            // Lấy danh sách Serial còn khả dụng trong kho của laptop này để nhân viên lựa chọn gán
            $item['availableSerials'] = $this->getAvailableSerialsByProduct($item['maSP']);
        }

        return $items;
    }

    /**
     * Lấy các mã Serial/IMEI đã gán vào dòng sản phẩm của đơn hàng
     */
    public function getAssignedSerials(int $idChiTietDH): array {
        $sql = "SELECT maSerial_IMEI, maSP, ngayXuatKho, trangThai, ghiChu 
                FROM serialsanpham 
                WHERE idChiTietDonHang = :idChiTietDH 
                ORDER BY maSerial_IMEI ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idChiTietDH' => $idChiTietDH]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy các mã Serial/IMEI của một laptop đang có sẵn trong kho ('Trong kho')
     */
    public function getAvailableSerialsByProduct(string $maSP): array {
        $sql = "SELECT maSerial_IMEI, idChiTietNhap, ghiChu 
                FROM serialsanpham 
                WHERE maSP = :maSP AND trangThai = 'Trong kho' AND idChiTietDonHang IS NULL
                ORDER BY maSerial_IMEI ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maSP' => $maSP]);
        return $stmt->fetchAll();
    }

    /**
     * Gán số Serial/IMEI vào dòng đơn hàng (Chuyển trạng thái sang 'Đã bán')
     */
    public function assignSerialToOrder(string $maSerial, int $idChiTietDH): bool {
        // Kiểm tra xem dòng chi tiết đơn này đã gán đủ số lượng máy chưa
        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM serialsanpham WHERE idChiTietDonHang = :id");
        $stmtCount->execute([':id' => $idChiTietDH]);
        $currentCount = (int)$stmtCount->fetchColumn();

        $stmtQty = $this->db->prepare("SELECT soLuong, maSP FROM chitietdonhang WHERE id = :id");
        $stmtQty->execute([':id' => $idChiTietDH]);
        $item = $stmtQty->fetch();

        if (!$item) {
            throw new Exception("Dòng sản phẩm đơn hàng không tồn tại!");
        }

        if ($currentCount >= $item['soLuong']) {
            throw new Exception("Sản phẩm này đã được gán đủ số lượng Serial ({$item['soLuong']} máy)!");
        }

        $sql = "UPDATE serialsanpham 
                SET idChiTietDonHang = :idChiTietDH, 
                    trangThai = 'Đã bán', 
                    ngayXuatKho = NOW() 
                WHERE maSerial_IMEI = :maSerial AND trangThai = 'Trong kho' AND idChiTietDonHang IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':idChiTietDH' => $idChiTietDH,
            ':maSerial'    => $maSerial
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Mã Serial [{$maSerial}] không khả dụng hoặc đã được xuất bán!");
        }

        // Nếu đơn hàng này đã ở trạng thái HOAN_THANH, tự động tạo Phiếu Bảo Hành cho Serial vừa gán
        $stmtOrder = $this->db->prepare("SELECT dh.maDH, dh.maKH, dh.trangThaiDon, sp.thoiGianBaoHanh 
                                         FROM chitietdonhang ctd
                                         JOIN donhang dh ON ctd.maDH = dh.maDH
                                         JOIN sanpham sp ON ctd.maSP = sp.maSP
                                         WHERE ctd.id = :id");
        $stmtOrder->execute([':id' => $idChiTietDH]);
        $orderInfo = $stmtOrder->fetch();

        if ($orderInfo && $orderInfo['trangThaiDon'] === 'HOAN_THANH') {
            $stmtCheckPBH = $this->db->prepare("SELECT COUNT(*) FROM phieubaohanh WHERE maSerial_IMEI = :serial");
            $stmtCheckPBH->execute([':serial' => $maSerial]);
            if ((int)$stmtCheckPBH->fetchColumn() === 0) {
                $maPBH = 'PBH_' . strtoupper(substr(uniqid(), -8));
                $months = max(1, (int)($orderInfo['thoiGianBaoHanh'] ?? 12));
                $sqlInsertPBH = "INSERT INTO phieubaohanh (maPhieuBH, maSerial_IMEI, maKH, ngayBatDau, ngayKetThuc, moTaChinhSach, trangThai) 
                                 VALUES (:maPhieuBH, :serial, :maKH, NOW(), DATE_ADD(NOW(), INTERVAL :months MONTH), :moTa, 'CON_HAN')";
                $stmtInsertPBH = $this->db->prepare($sqlInsertPBH);
                $stmtInsertPBH->execute([
                    ':maPhieuBH' => $maPBH,
                    ':serial'    => $maSerial,
                    ':maKH'      => $orderInfo['maKH'],
                    ':months'    => $months,
                    ':moTa'      => "Bảo hành chính hãng {$months} tháng tính từ ngày hoàn tất đơn hàng {$orderInfo['maDH']}"
                ]);
            }
        }

        return true;
    }

    /**
     * Gỡ bỏ mã Serial đã gán (Nhả lại về kho 'Trong kho')
     */
    public function removeAssignedSerial(string $maSerial): bool {
        // Gỡ phiếu bảo hành nếu có
        $stmtDelPBH = $this->db->prepare("DELETE FROM phieubaohanh WHERE maSerial_IMEI = :serial");
        $stmtDelPBH->execute([':serial' => $maSerial]);

        $sql = "UPDATE serialsanpham 
                SET idChiTietDonHang = NULL, 
                    trangThai = 'Trong kho', 
                    ngayXuatKho = NULL 
                WHERE maSerial_IMEI = :maSerial";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':maSerial' => $maSerial]);
    }

    /**
     * Cập nhật trạng thái đơn hàng (Duyệt giao, Hoàn thành, Hủy)
     * Khi HOAN_THANH: Tự động tạo Hóa Đơn và Phiếu Bảo Hành cho từng Serial
     */
    public function updateOrderStatus(string $maDH, string $status, string $nguoiPhuTrach): bool {
        $order = $this->getOrderDetail($maDH);
        if (!$order) {
            throw new Exception("Đơn hàng không tồn tại!");
        }

        $this->db->beginTransaction();

        try {
            // 1. Cập nhật trạng thái đơn hàng
            $stmt = $this->db->prepare("UPDATE donhang SET trangThaiDon = :status, nguoiPhuTrach = :nguoiPhuTrach WHERE maDH = :maDH");
            $stmt->execute([
                ':status'        => $status,
                ':nguoiPhuTrach' => $nguoiPhuTrach,
                ':maDH'          => $maDH
            ]);

            // 2. Nếu chuyển sang trạng thái HOAN_THANH: Tự động tạo Hóa Đơn & Phiếu Bảo Hành
            if ($status === 'HOAN_THANH') {
                $this->generateInvoiceAndWarranty($order);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Tự động sinh Hóa đơn (HoaDon) và Phiếu bảo hành (PhieuBaoHanh)
     */
    private function generateInvoiceAndWarranty(array $order): void {
        $maDH = $order['maDH'];

        // 2.1 Kiểm tra xem Hóa đơn đã tồn tại chưa
        $stmtCheckHD = $this->db->prepare("SELECT COUNT(*) FROM hoadon WHERE maDH = :maDH");
        $stmtCheckHD->execute([':maDH' => $maDH]);
        if ((int)$stmtCheckHD->fetchColumn() === 0) {
            $maHD = 'HD_' . date('Ymd') . '_' . strtoupper(substr(uniqid(), -4));
            $tenKhach = $order['tenNguoiNhan'] ?: ($order['tenKhachHangGoc'] ?: 'Khách hàng vãng lai');
            $sdt      = $order['sdtNhanHang'] ?: ($order['sdtKhachHangGoc'] ?: '');
            $diaChi   = $order['diaChiGiaoHang'] ?: ($order['diaChiKhachHangGoc'] ?: '');

            $sqlHD = "INSERT INTO hoadon (maHD, maDH, ngayXuat, tenKhachHang, soDienThoai, diaChiKhachHang, soTienThanhToan, trangThaiThanhToan, ghiChu) 
                      VALUES (:maHD, :maDH, NOW(), :tenKhach, :sdt, :diaChi, :soTien, 'DA_THANH_TOAN', :ghiChu)";
            $stmtInsertHD = $this->db->prepare($sqlHD);
            $stmtInsertHD->execute([
                ':maHD'     => $maHD,
                ':maDH'     => $maDH,
                ':tenKhach' => $tenKhach,
                ':sdt'      => $sdt,
                ':diaChi'   => $diaChi,
                ':soTien'   => $order['tongThanhToan'],
                ':ghiChu'   => 'Xuất hóa đơn tự động khi hoàn thành đơn hàng ' . $maDH
            ]);
        }

        // 2.2 Tạo Phiếu bảo hành cho từng Serial máy đã gán vào đơn hàng này
        $sqlSerials = "SELECT s.maSerial_IMEI, sp.maSP, sp.thoiGianBaoHanh 
                       FROM serialsanpham s
                       JOIN chitietdonhang ctd ON s.idChiTietDonHang = ctd.id
                       JOIN sanpham sp ON s.maSP = sp.maSP
                       WHERE ctd.maDH = :maDH";
        $stmtSerials = $this->db->prepare($sqlSerials);
        $stmtSerials->execute([':maDH' => $maDH]);
        $serials = $stmtSerials->fetchAll();

        $stmtCheckPBH = $this->db->prepare("SELECT COUNT(*) FROM phieubaohanh WHERE maSerial_IMEI = :serial");
        $sqlInsertPBH = "INSERT INTO phieubaohanh (maPhieuBH, maSerial_IMEI, maKH, ngayBatDau, ngayKetThuc, moTaChinhSach, trangThai) 
                         VALUES (:maPhieuBH, :serial, :maKH, NOW(), DATE_ADD(NOW(), INTERVAL :months MONTH), :moTa, 'CON_HAN')";
        $stmtInsertPBH = $this->db->prepare($sqlInsertPBH);

        foreach ($serials as $s) {
            $serial = $s['maSerial_IMEI'];
            $months = max(1, (int)($s['thoiGianBaoHanh'] ?? 12));

            $stmtCheckPBH->execute([':serial' => $serial]);
            if ((int)$stmtCheckPBH->fetchColumn() === 0) {
                $maPBH = 'PBH_' . strtoupper(substr(uniqid(), -8));
                $stmtInsertPBH->execute([
                    ':maPhieuBH' => $maPBH,
                    ':serial'    => $serial,
                    ':maKH'      => $order['maKH'],
                    ':months'    => $months,
                    ':moTa'      => "Bảo hành chính hãng {$months} tháng tính từ ngày hoàn tất đơn hàng {$maDH}"
                ]);
            }
        }
    }

    /**
     * Hủy đơn hàng và tự động giải phóng các mã Serial đã gán về lại trạng thái 'Trong kho'
     */
    public function cancelOrder(string $maDH, string $nguoiPhuTrach, string $lyDo = ''): bool {
        $this->db->beginTransaction();

        try {
            // 1. Cập nhật trạng thái đơn sang DA_HUY
            $stmt = $this->db->prepare("UPDATE donhang SET trangThaiDon = 'DA_HUY', nguoiPhuTrach = :nguoiPhuTrach WHERE maDH = :maDH");
            $stmt->execute([
                ':nguoiPhuTrach' => $nguoiPhuTrach,
                ':maDH'          => $maDH
            ]);

            // 2. Giải phóng toàn bộ mã Serial của đơn này về lại kho ('Trong kho')
            $sqlRelease = "UPDATE serialsanpham s
                           JOIN chitietdonhang ctd ON s.idChiTietDonHang = ctd.id
                           SET s.idChiTietDonHang = NULL, 
                               s.trangThai = 'Trong kho', 
                               s.ngayXuatKho = NULL,
                               s.ghiChu = CONCAT(COALESCE(s.ghiChu, ''), ' [Nhả lại do hủy đơn {$maDH}]')
                           WHERE ctd.maDH = :maDH";
            $stmtRelease = $this->db->prepare($sqlRelease);
            $stmtRelease->execute([':maDH' => $maDH]);

            // 3. Tự động hoàn lại số lượng tồn kho cho bảng sanpham
            $sqlRestoreStock = "UPDATE sanpham sp
                                JOIN (
                                    SELECT maSP, SUM(soLuong) as totalQty 
                                    FROM chitietdonhang 
                                    WHERE maDH = :maDH 
                                    GROUP BY maSP
                                ) ctd ON sp.maSP = ctd.maSP
                                SET sp.soLuongTon = sp.soLuongTon + ctd.totalQty";
            $stmtRestoreStock = $this->db->prepare($sqlRestoreStock);
            $stmtRestoreStock->execute([':maDH' => $maDH]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
