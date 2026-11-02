<?php
/**
 * File: models/OrderTrackingModel.php
 * Tra cứu tiến trình đơn hàng & hành trình vận chuyển theo Mã Đơn hoặc SĐT
 * CSDL: DonHang, UserDatHang, ChiTietDonHang, SanPham, SerialSanPham, KhachHang
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class OrderTrackingModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Tra cứu thông tin chi tiết 1 đơn hàng theo Mã đơn hoặc kết hợp Số điện thoại
     *
     * @param string $maDH Mã đơn hàng (ví dụ: DH002, DH006)
     * @param string|null $sdt Số điện thoại đặt hàng (tùy chọn hoặc kiểm tra bảo mật)
     * @return array|null Chi tiết đơn hàng kèm danh sách sản phẩm, Serial máy và Stepper tiến trình
     */
    public function trackOrder(string $maDH, ?string $sdt = null): ?array {
        $maDH = trim($maDH);
        if ($maDH === '') {
            return null;
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
                    ud.tenNguoiNhan,
                    ud.sdtNhanHang,
                    ud.diaChiGiaoHang,
                    ud.ghiChuKhachHang,
                    ud.trangThaiThanhToan,
                    kh.hoVaTen AS tenKhachHang,
                    kh.soDienThoai AS sdtKhachHang
                FROM donhang dh
                LEFT JOIN userdathang ud ON dh.maDH = ud.maDH
                LEFT JOIN khachhang kh ON dh.maKH = kh.maKH
                WHERE (dh.maDH = :maDH OR dh.maDH = :maDHUpper)";

        $params = [
            ':maDH'      => $maDH,
            ':maDHUpper' => strtoupper($maDH)
        ];

        // Nếu có truyền SĐT, kiểm tra bảo mật khớp SĐT người nhận hoặc SĐT khách
        if (!empty($sdt)) {
            $digits = preg_replace('/[^0-9]/', '', $sdt);
            $sql .= " AND (
                        REPLACE(REPLACE(REPLACE(COALESCE(ud.sdtNhanHang, ''), ' ', ''), '.', ''), '-', '') = :sdt
                        OR REPLACE(REPLACE(REPLACE(COALESCE(kh.soDienThoai, ''), ' ', ''), '.', ''), '-', '') = :sdtKH
                    )";
            $params[':sdt']   = $digits;
            $params[':sdtKH'] = $digits;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // Lấy danh sách sản phẩm trong đơn hàng
        $order['items']   = $this->getOrderItems($order['maDH']);
        $order['serials'] = $this->getOrderSerials($order['maDH']);
        $order['hasSerials'] = !empty($order['serials']);

        // Gắn danh sách serial cho từng sản phẩm
        $serialsByItem = [];
        $sqlSr = "SELECT maSerial_IMEI, maSP, idChiTietDonHang, trangThai, ngayXuatKho 
                  FROM serialsanpham 
                  WHERE idChiTietDonHang IN (SELECT id FROM chitietdonhang WHERE maDH = :maDH)";
        $stmtSr = $this->db->prepare($sqlSr);
        $stmtSr->execute([':maDH' => $order['maDH']]);
        $allSr = $stmtSr->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allSr as $s) {
            $serialsByItem[$s['idChiTietDonHang']][] = $s;
        }
        foreach ($order['items'] as &$it) {
            $it['serials'] = $serialsByItem[$it['id']] ?? [];
        }
        unset($it);

        // Tính toán tiến trình Stepper 4 bước
        $order['stepper'] = $this->computeStepperProgress($order['trangThaiDon'], $order['hasSerials']);

        return $order;
    }

    /**
     * Tìm kiếm danh sách đơn hàng theo Mã đơn hoặc Số điện thoại
     */
    public function searchOrders(string $keyword): array {
        $keyword = trim($keyword);
        if ($keyword === '') {
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
                    ud.tenNguoiNhan,
                    ud.sdtNhanHang,
                    ud.diaChiGiaoHang,
                    ud.ghiChuKhachHang,
                    ud.trangThaiThanhToan
                FROM donhang dh
                LEFT JOIN userdathang ud ON dh.maDH = ud.maDH
                WHERE dh.maDH = :kw1 
                   OR ud.sdtNhanHang = :kw2
                   OR dh.maDH LIKE :kw3
                ORDER BY dh.ngayTao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':kw1' => $keyword,
            ':kw2' => $keyword,
            ':kw3' => '%' . $keyword . '%'
        ]);

        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as &$order) {
            $order['items']      = $this->getOrderItems($order['maDH']);
            $order['serials']    = $this->getOrderSerials($order['maDH']);
            $order['hasSerials'] = !empty($order['serials']);
            $order['stepper']    = $this->computeStepperProgress($order['trangThaiDon'], $order['hasSerials']);
        }
        unset($order);

        return $orders;
    }

    /**
     * Lấy các sản phẩm trong đơn hàng
     */
    public function getOrderItems(string $maDH): array {
        $sql = "SELECT 
                    ctdh.id,
                    ctdh.maSP,
                    ctdh.soLuong,
                    ctdh.donGia,
                    ctdh.thanhTien,
                    sp.tenSP,
                    sp.hinhAnh,
                    sp.thoiGianBaoHanh,
                    th.tenTH
                FROM chitietdonhang ctdh
                JOIN sanpham sp ON ctdh.maSP = sp.maSP
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                WHERE ctdh.maDH = :maDH";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maDH' => $maDH]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách số Serial/IMEI máy đã xuất kho cho đơn hàng
     */
    public function getOrderSerials(string $maDH): array {
        $sql = "SELECT 
                    sn.maSerial_IMEI,
                    sn.maSP,
                    sn.trangThai,
                    sn.ngayXuatKho,
                    sp.tenSP
                FROM serialsanpham sn
                JOIN sanpham sp ON sn.maSP = sp.maSP
                WHERE sn.idChiTietDonHang IN (
                    SELECT id FROM chitietdonhang WHERE maDH = :maDH
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maDH' => $maDH]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tính toán bước tiến độ hiện tại cho Stepper (1 đến 4 bước)
     * 1. Chờ xác nhận (CHO_DUYET)
     * 2. Đã xuất kho & Đóng gói (gán Serial máy)
     * 3. Đang vận chuyển (DANG_GIAO)
     * 4. Giao thành công (HOAN_THANH)
     */
    public function computeStepperProgress(string $status, bool $hasSerials): array {
        $st = strtoupper(trim($status));
        if ($st === 'DA_HUY' || $st === 'HUY') {
            return [
                'currentStep' => 0,
                'isCancelled' => true,
                'statusLabel' => 'Đã Hủy Đơn Hàng',
                'badgeClass'  => 'bg-danger'
            ];
        }

        $step = 1; // 1: Chờ xác nhận
        $label = 'Chờ xác nhận (Đã ghi nhận đơn)';
        $badge = 'bg-warning text-dark';

        if ($st === 'HOAN_THANH') {
            $step = 4; // 4: Giao thành công
            $label = 'Giao thành công (Kích hoạt BH)';
            $badge = 'bg-success';
        } elseif ($st === 'DANG_GIAO' || $st === 'DANG_VAN_CHUYEN') {
            $step = 3; // 3: Đang vận chuyển
            $label = 'Đang vận chuyển hỏa tốc';
            $badge = 'bg-primary';
        } elseif ($hasSerials || in_array($st, ['DANG_DONG_GOI', 'DANG_XU_LY', 'DA_XAC_NHAN'], true)) {
            $step = 2; // 2: Đã xuất kho & Đóng gói (gán Serial máy)
            $label = 'Đã xuất kho & Đóng gói (Gán Serial máy)';
            $badge = 'bg-info text-dark';
        }

        return [
            'currentStep' => $step,
            'isCancelled' => false,
            'statusLabel' => $label,
            'badgeClass'  => $badge
        ];
    }
}
