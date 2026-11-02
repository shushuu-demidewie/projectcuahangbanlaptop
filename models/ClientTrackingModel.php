<?php
/**
 * File: models/ClientTrackingModel.php
 * Phân hệ: Cổng Tra Cứu Bảo Hành & Tiến Độ Đơn Hàng Dành Cho Khách Hàng
 * Sử dụng PDO với Prepared Statements chống triệt để SQL Injection
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class ClientTrackingModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * 1. Tra cứu tiến độ đơn hàng theo Mã đơn hàng kết hợp Số điện thoại nhận hàng
     * Bảo mật: Chỉ khi nhập đúng Mã đơn VÀ Số điện thoại khớp nhau mới hiển thị thông tin.
     * JOIN: DonHang, UserDatHang, ChiTietDonHang, SanPham
     *
     * @param string $maDH Mã đơn hàng (ví dụ: DH002)
     * @param string $sdt  Số điện thoại nhận hàng
     * @return array|null Trả về mảng chi tiết đơn hàng hoặc null nếu không tìm thấy
     */
    public function trackOrder(string $maDH, string $sdt): ?array {
        $maDH = trim($maDH);
        $sdt  = preg_replace('/[^0-9]/', '', trim($sdt)); // Chuẩn hóa SĐT chỉ giữ lại số

        if (empty($maDH) || empty($sdt)) {
            return null;
        }

        // Truy vấn thông tin đơn hàng và thông tin người nhận
        $sqlOrder = "SELECT 
                        dh.maDH,
                        dh.maKH,
                        dh.ngayTao,
                        dh.kenhBan,
                        dh.phuongThucThanhToan,
                        dh.phuongThucGiaoHang,
                        dh.phiVanChuyen,
                        dh.tongTienHang,
                        dh.tienGiamGia,
                        dh.tongThanhToan,
                        dh.trangThaiDon,
                        udh.tenNguoiNhan,
                        udh.sdtNhanHang,
                        udh.diaChiGiaoHang,
                        udh.ghiChuKhachHang,
                        udh.trangThaiThanhToan AS trangThaiThanhToanOnline,
                        kh.hoVaTen AS tenKhachHang,
                        kh.soDienThoai AS sdtKhachHang
                    FROM donhang dh
                    INNER JOIN userdathang udh ON dh.maDH = udh.maDH
                    LEFT JOIN khachhang kh ON dh.maKH = kh.maKH
                    WHERE dh.maDH = :maDH 
                      AND (
                          REPLACE(REPLACE(REPLACE(udh.sdtNhanHang, ' ', ''), '.', ''), '-', '') = :sdt
                          OR REPLACE(REPLACE(REPLACE(COALESCE(kh.soDienThoai, ''), ' ', ''), '.', ''), '-', '') = :sdtKH
                      )
                    LIMIT 1";

        $stmt = $this->db->prepare($sqlOrder);
        $stmt->execute([
            ':maDH'  => $maDH,
            ':sdt'   => $sdt,
            ':sdtKH' => $sdt
        ]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            return null;
        }

        // Lấy chi tiết các sản phẩm trong đơn kèm số Serial/IMEI máy (nếu kho đã gán)
        $sqlItems = "SELECT 
                        ctd.id AS idChiTietDH,
                        ctd.maDH,
                        ctd.maSP,
                        ctd.soLuong,
                        ctd.donGia,
                        ctd.thanhTien,
                        sp.tenSP,
                        sp.hinhAnh,
                        sp.thoiGianBaoHanh
                    FROM chitietdonhang ctd
                    INNER JOIN sanpham sp ON ctd.maSP = sp.maSP
                    WHERE ctd.maDH = :maDH
                    ORDER BY ctd.id ASC";

        $stmtItems = $this->db->prepare($sqlItems);
        $stmtItems->execute([':maDH' => $maDH]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh sách Serial/IMEI gắn với từng dòng chi tiết đơn hàng
        $sqlSerials = "SELECT 
                          s.maSerial_IMEI,
                          s.idChiTietDonHang,
                          s.ngayXuatKho,
                          s.trangThai
                       FROM serialsanpham s
                       INNER JOIN chitietdonhang ctd ON s.idChiTietDonHang = ctd.id
                       WHERE ctd.maDH = :maDH";

        $stmtSerials = $this->db->prepare($sqlSerials);
        $stmtSerials->execute([':maDH' => $maDH]);
        $serials = $stmtSerials->fetchAll(PDO::FETCH_ASSOC);

        $serialsByItem = [];
        foreach ($serials as $sr) {
            $serialsByItem[$sr['idChiTietDonHang']][] = $sr;
        }

        foreach ($items as &$item) {
            $item['serials'] = $serialsByItem[$item['idChiTietDH']] ?? [];
        }
        unset($item);

        $order['items'] = $items;
        $order['hasSerials'] = !empty($serials);

        // Tính toán thông tin Stepper Timeline 4 bước trực quan
        $order['stepper'] = $this->calculateOrderTimeline($order['trangThaiDon'], $order['hasSerials']);

        return $order;
    }

    /**
     * Tính toán bước tiến độ cho Timeline (1 đến 4 bước)
     * 1. Đã đặt hàng thành công
     * 2. Đã duyệt đơn & Xuất kho
     * 3. Đang giao hàng
     * 4. Giao hàng thành công
     */
    public function calculateOrderTimeline(string $trangThaiDon, bool $hasSerials): array {
        $status = strtoupper(trim($trangThaiDon));

        if ($status === 'DA_HUY' || $status === 'HUY') {
            return [
                'currentStep' => 0,
                'isCancelled' => true,
                'statusKey'   => 'DA_HUY',
                'statusLabel' => 'Đã Hủy Đơn',
                'badgeClass'  => 'bg-danger',
                'stepPercent' => 0
            ];
        }

        $step = 1;
        $stepPercent = 25;
        $label = 'Đã đặt hàng thành công (Chờ xác nhận)';
        $badgeClass = 'bg-warning text-dark';

        if ($status === 'HOAN_THANH') {
            $step = 4;
            $stepPercent = 100;
            $label = 'Giao hàng thành công (Hoàn tất)';
            $badgeClass = 'bg-success';
        } elseif ($status === 'DANG_GIAO') {
            $step = 3;
            $stepPercent = 75;
            $label = 'Đang giao hàng tới người nhận';
            $badgeClass = 'bg-primary';
        } elseif ($status === 'CHO_DUYET') {
            if ($hasSerials) {
                $step = 2;
                $stepPercent = 50;
                $label = 'Đã duyệt đơn & Chuẩn bị xuất kho (Đã gán Serial máy)';
                $badgeClass = 'bg-info text-dark';
            } else {
                $step = 1;
                $stepPercent = 25;
                $label = 'Đã tiếp nhận đơn hàng (Chờ chuẩn bị máy)';
                $badgeClass = 'bg-warning text-dark';
            }
        }

        return [
            'currentStep' => $step,
            'isCancelled' => false,
            'statusKey'   => $status,
            'statusLabel' => $label,
            'badgeClass'  => $badgeClass,
            'stepPercent' => $stepPercent
        ];
    }

    /**
     * 2. Tra cứu thông tin bảo hành điện tử theo Mã Serial/IMEI hoặc Số điện thoại khách hàng
     * JOIN: PhieuBaoHanh, SerialSanPham, SanPham, KhachHang
     * Lấy: Tên laptop, Mã Serial, Ngày kích hoạt (ngayBatDau), Ngày hết hạn (ngayKetThuc),
     *      Số ngày còn lại, Trạng thái phiếu bảo hành ('Đang hiệu lực' / 'Hết hạn'), Mô tả chính sách.
     *
     * @param string $keyword Mã Serial/IMEI hoặc Số điện thoại
     * @return array Danh sách phiếu bảo hành tìm thấy
     */
    public function checkWarranty(string $keyword): array {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        // Tách số điện thoại thuần để tìm kiếm chính xác nếu keyword là SĐT
        $phoneDigits = preg_replace('/[^0-9]/', '', $keyword);

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
                    kh.hoVaTen,
                    kh.soDienThoai,
                    kh.email,
                    DATEDIFF(pbh.ngayKetThuc, CURDATE()) AS soNgayConLai
                FROM phieubaohanh pbh
                INNER JOIN serialsanpham sn ON pbh.maSerial_IMEI = sn.maSerial_IMEI
                INNER JOIN sanpham sp ON sn.maSP = sp.maSP
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
            ':hasDigits'   => !empty($phoneDigits) ? 1 : 0,
            ':phoneKw'     => '%' . $phoneDigits . '%'
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Chuẩn hóa trạng thái ('Đang hiệu lực' / 'Hết hạn') & số ngày còn lại
        foreach ($results as &$row) {
            $daysLeft = (int)($row['soNgayConLai'] ?? 0);
            $dbStatus = strtoupper(trim((string)$row['trangThaiPhieu']));

            // Kiểm tra tính hiệu lực
            if ($daysLeft > 0 && $dbStatus !== 'HET_HAN' && $dbStatus !== 'DA_HUY') {
                $row['trangThaiHienThi'] = 'Đang hiệu lực';
                $row['isConHan'] = true;
                $row['badgeColor'] = 'success';
            } else {
                $row['trangThaiHienThi'] = 'Hết hạn';
                $row['isConHan'] = false;
                $row['badgeColor'] = 'danger';
            }

            // Gán mô tả chính sách mặc định nếu chưa có
            if (empty($row['moTaChinhSach'])) {
                $thoiGian = !empty($row['thoiGianBaoHanh']) ? $row['thoiGianBaoHanh'] . ' tháng' : '12 tháng';
                $row['moTaChinhSach'] = "Bảo hành phần cứng chính hãng {$thoiGian}. 1 đổi 1 trong 30 ngày nếu phát sinh lỗi phần cứng từ nhà sản xuất.";
            }
        }
        unset($row);

        return $results;
    }

    /**
     * 3. Lấy danh sách lịch sử tất cả các đơn hàng mà khách đã mua
     * Áp dụng khi khách đã đăng nhập tài khoản khách hàng
     *
     * @param string $maKH Mã khách hàng (ví dụ: KH001)
     * @return array Danh sách đơn hàng sắp xếp mới nhất lên đầu
     */
    public function getCustomerOrderHistory(string $maKH): array {
        $maKH = trim($maKH);
        if (empty($maKH)) {
            return [];
        }

        $sql = "SELECT 
                    dh.maDH,
                    dh.maKH,
                    dh.ngayTao,
                    dh.kenhBan,
                    dh.phuongThucThanhToan,
                    dh.phuongThucGiaoHang,
                    dh.phiVanChuyen,
                    dh.tongTienHang,
                    dh.tienGiamGia,
                    dh.tongThanhToan,
                    dh.trangThaiDon,
                    udh.tenNguoiNhan,
                    udh.sdtNhanHang,
                    udh.diaChiGiaoHang,
                    udh.ghiChuKhachHang,
                    hd.maHD,
                    hd.trangThaiThanhToan AS trangThaiHoaDon,
                    COUNT(ctd.id) AS tongSoDongSP,
                    COALESCE(SUM(ctd.soLuong), 0) AS tongSoLuongSP
                FROM donhang dh
                LEFT JOIN userdathang udh ON dh.maDH = udh.maDH
                LEFT JOIN hoadon hd ON dh.maDH = hd.maDH
                LEFT JOIN chitietdonhang ctd ON dh.maDH = ctd.maDH
                WHERE dh.maKH = :maKH
                GROUP BY dh.maDH
                ORDER BY dh.ngayTao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maKH' => $maKH]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy thêm danh sách sản phẩm tóm tắt cho từng đơn hàng (kèm mã Serial/IMEI nếu đã gán)
        foreach ($orders as &$order) {
            $sqlItems = "SELECT 
                            ctd.id,
                            ctd.maDH,
                            ctd.maSP,
                            ctd.soLuong,
                            ctd.donGia,
                            ctd.thanhTien,
                            sp.tenSP,
                            sp.hinhAnh,
                            sp.thoiGianBaoHanh,
                            GROUP_CONCAT(sr.maSerial_IMEI SEPARATOR ', ') AS maSerial_IMEI
                        FROM chitietdonhang ctd
                        INNER JOIN sanpham sp ON ctd.maSP = sp.maSP
                        LEFT JOIN serialsanpham sr ON ctd.id = sr.idChiTietDonHang
                        WHERE ctd.maDH = :maDH
                        GROUP BY ctd.id, ctd.maDH, ctd.maSP, ctd.soLuong, ctd.donGia, ctd.thanhTien, sp.tenSP, sp.hinhAnh, sp.thoiGianBaoHanh
                        ORDER BY ctd.id ASC";
            $stmtItems = $this->db->prepare($sqlItems);
            $stmtItems->execute([':maDH' => $order['maDH']]);
            $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            // Gán nhãn trạng thái tiếng Việt và class badge
            $order['statusInfo'] = match(strtoupper(trim($order['trangThaiDon']))) {
                'HOAN_THANH' => ['label' => 'Đã hoàn thành', 'badge' => 'bg-success', 'icon' => 'bi-check-circle-fill'],
                'DANG_GIAO'  => ['label' => 'Đang giao hàng', 'badge' => 'bg-primary', 'icon' => 'bi-truck'],
                'CHO_DUYET'  => ['label' => 'Chờ duyệt đơn', 'badge' => 'bg-warning text-dark', 'icon' => 'bi-hourglass-split'],
                'DA_HUY'     => ['label' => 'Đã hủy', 'badge' => 'bg-danger', 'icon' => 'bi-x-circle-fill'],
                default      => ['label' => $order['trangThaiDon'], 'badge' => 'bg-secondary', 'icon' => 'bi-info-circle']
            };
        }
        unset($order);

        return $orders;
    }

    /**
     * Tra cứu mã KH từ mã tài khoản accounts.maTk
     */
    public function getCustomerCodeByAccountId(string $maTk): ?string {
        $stmt = $this->db->prepare("SELECT maKH FROM khachhang WHERE maTk = :maTk LIMIT 1");
        $stmt->execute([':maTk' => $maTk]);
        $maKH = $stmt->fetchColumn();
        return $maKH ? (string)$maKH : null;
    }

    /**
     * Lấy thông tin khách hàng theo maKH
     */
    public function getCustomerInfo(string $maKH): ?array {
        $stmt = $this->db->prepare("SELECT * FROM khachhang WHERE maKH = :maKH LIMIT 1");
        $stmt->execute([':maKH' => $maKH]);
        $kh = $stmt->fetch(PDO::FETCH_ASSOC);
        return $kh ?: null;
    }
}
