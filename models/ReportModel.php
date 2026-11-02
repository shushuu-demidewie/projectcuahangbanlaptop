<?php
/**
 * File: models/ReportModel.php
 * Nghiệp vụ Báo Cáo Doanh Số & Đối Soát Tài Chính
 * Dành cho Quản Trị Viên (Admin) và Kế Toán (ke_toan)
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class ReportModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Chuẩn hóa khoảng thời gian lọc (Mặc định: từ đầu tháng hiện tại đến hôm nay)
     */
    public function sanitizeDateRange(?string $fromDate, ?string $toDate): array {
        $today = date('Y-m-d');
        $firstDayOfMonth = date('Y-m-01');

        $from = !empty($fromDate) ? trim($fromDate) : $firstDayOfMonth;
        $to   = !empty($toDate) ? trim($toDate) : $today;

        // Đảm bảo định dạng Y-m-d hợp lệ
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = $firstDayOfMonth;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = $today;
        }

        // Nếu từ ngày > đến ngày, đảo lại
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        return ['from' => $from, 'to' => $to];
    }

    /**
     * Báo cáo Tài Chính Tổng Hợp trong kỳ lọc:
     * 1. Tổng doanh thu bán hàng (Đơn hoàn thành)
     * 2. Tổng giá vốn hàng bán (COGS)
     * 3. Lợi nhuận gộp ước tính & Tỷ suất lợi nhuận
     * 4. Tổng chi phí nhập hàng trong kỳ
     * 5. Tổng công nợ còn phải trả nhà cung cấp (tất cả các phiếu chưa thanh toán xong)
     */
    public function getFinancialSummary(?string $fromDate = null, ?string $toDate = null): array {
        $range = $this->sanitizeDateRange($fromDate, $toDate);
        $from = $range['from'];
        $to   = $range['to'];

        // 1. Tổng doanh thu bán hàng (các đơn hoàn thành)
        $sqlRevenue = "SELECT 
                            COALESCE(SUM(tongThanhToan), 0) AS tongDoanhThu,
                            COALESCE(SUM(tongTienHang), 0) AS tongTienHang,
                            COALESCE(SUM(tienGiamGia), 0) AS tongTienGiam,
                            COUNT(*) AS soDonHoanThanh
                       FROM donhang 
                       WHERE trangThaiDon = 'HOAN_THANH'
                         AND DATE(ngayTao) BETWEEN :from AND :to";

        $stmtRev = $this->db->prepare($sqlRevenue);
        $stmtRev->execute([':from' => $from, ':to' => $to]);
        $revData = $stmtRev->fetch(PDO::FETCH_ASSOC);

        $tongDoanhThu    = (float)($revData['tongDoanhThu'] ?? 0);
        $soDonHoanThanh  = (int)($revData['soDonHoanThanh'] ?? 0);
        $tongTienHang    = (float)($revData['tongTienHang'] ?? 0);
        $tongTienGiam    = (float)($revData['tongTienGiam'] ?? 0);

        // 2. Tổng số lượng máy bán ra trong kỳ
        $sqlQty = "SELECT COALESCE(SUM(ctdh.soLuong), 0) 
                   FROM chitietdonhang ctdh
                   JOIN donhang dh ON ctdh.maDH = dh.maDH
                   WHERE dh.trangThaiDon = 'HOAN_THANH'
                     AND DATE(dh.ngayTao) BETWEEN :from AND :to";
        $stmtQty = $this->db->prepare($sqlQty);
        $stmtQty->execute([':from' => $from, ':to' => $to]);
        $soMayDaBan = (int)$stmtQty->fetchColumn();

        // 3. Tổng giá vốn hàng bán (COGS): Lấy giá vốn nhập gần nhất của sản phẩm trong chitietnhap
        $sqlCogs = "SELECT 
                        COALESCE(SUM(
                            ctdh.soLuong * COALESCE(
                                (SELECT ctn.giaVonNhap FROM chitietnhap ctn WHERE ctn.maSP = ctdh.maSP ORDER BY ctn.id DESC LIMIT 1),
                                sp.giaBan * 0.75
                            )
                        ), 0) AS tongGiaVon
                    FROM chitietdonhang ctdh
                    JOIN donhang dh ON ctdh.maDH = dh.maDH
                    LEFT JOIN sanpham sp ON ctdh.maSP = sp.maSP
                    WHERE dh.trangThaiDon = 'HOAN_THANH'
                      AND DATE(dh.ngayTao) BETWEEN :from AND :to";

        $stmtCogs = $this->db->prepare($sqlCogs);
        $stmtCogs->execute([':from' => $from, ':to' => $to]);
        $tongGiaVon = (float)$stmtCogs->fetchColumn();

        // 4. Lợi nhuận gộp ước tính = Doanh thu - Giá vốn
        $loiNhuanGop = max(0, $tongDoanhThu - $tongGiaVon);
        $tiLeLoiNhuan = ($tongDoanhThu > 0) ? round(($loiNhuanGop / $tongDoanhThu) * 100, 1) : 0.0;

        // 5. Tổng chi phí nhập hàng trong kỳ từ các phiếu nhập đã tạo
        $sqlImport = "SELECT 
                        COALESCE(SUM(tongTienNhap), 0) AS tongChiPhiNhap,
                        COALESCE(SUM(tongTienTraTruocNCC), 0) AS tongTienDaTraNCC,
                        COUNT(*) AS soPhieuNhap
                      FROM phieunhap 
                      WHERE DATE(ngayNhap) BETWEEN :from AND :to";
        $stmtImp = $this->db->prepare($sqlImport);
        $stmtImp->execute([':from' => $from, ':to' => $to]);
        $impData = $stmtImp->fetch(PDO::FETCH_ASSOC);

        $tongChiPhiNhap   = (float)($impData['tongChiPhiNhap'] ?? 0);
        $tongTienDaTraNCC = (float)($impData['tongTienDaTraNCC'] ?? 0);
        $soPhieuNhap      = (int)($impData['soPhieuNhap'] ?? 0);

        // 6. Tổng công nợ còn phải trả nhà cung cấp (tất cả các phiếu nhập chưa thanh toán hết)
        $sqlDebt = "SELECT 
                        COALESCE(SUM(tongTienNhap - tongTienTraTruocNCC), 0) AS tongCongNo,
                        COUNT(*) AS soPhieuConNo
                    FROM phieunhap 
                    WHERE trangThaiThanhToan != 'DA_THANH_TOAN'
                      AND (tongTienNhap - tongTienTraTruocNCC) > 0";
        $stmtDebt = $this->db->query($sqlDebt);
        $debtData = $stmtDebt->fetch(PDO::FETCH_ASSOC);

        $tongCongNoNCC = (float)($debtData['tongCongNo'] ?? 0);
        $soPhieuConNo  = (int)($debtData['soPhieuConNo'] ?? 0);

        return [
            'fromDate'         => $from,
            'toDate'           => $to,
            'tongDoanhThu'     => $tongDoanhThu,
            'soDonHoanThanh'   => $soDonHoanThanh,
            'soMayDaBan'       => $soMayDaBan,
            'tongTienHang'     => $tongTienHang,
            'tongTienGiam'     => $tongTienGiam,
            'tongGiaVon'       => $tongGiaVon,
            'loiNhuanGop'      => $loiNhuanGop,
            'tiLeLoiNhuan'     => $tiLeLoiNhuan,
            'tongChiPhiNhap'   => $tongChiPhiNhap,
            'tongTienDaTraNCC' => $tongTienDaTraNCC,
            'soPhieuNhap'      => $soPhieuNhap,
            'tongCongNoNCC'    => $tongCongNoNCC,
            'soPhieuConNo'     => $soPhieuConNo
        ];
    }

    /**
     * Thống kê doanh thu theo từng ngày trong kỳ để dựng Biểu đồ Cột/Đường (Line/Bar Chart)
     */
    public function getRevenueByDays(int $days = 7, ?string $fromDate = null, ?string $toDate = null): array {
        if (!empty($fromDate) && !empty($toDate)) {
            $range = $this->sanitizeDateRange($fromDate, $toDate);
            $from = $range['from'];
            $to   = $range['to'];
        } else {
            $to   = date('Y-m-d');
            $from = date('Y-m-d', strtotime("-{$days} days"));
        }

        $sql = "SELECT 
                    DATE(ngayTao) AS ngay,
                    DATE_FORMAT(ngayTao, '%d/%m') AS ngayFmt,
                    COALESCE(SUM(tongThanhToan), 0) AS doanhThu,
                    COUNT(*) AS soDon
                FROM donhang 
                WHERE trangThaiDon = 'HOAN_THANH'
                  AND DATE(ngayTao) BETWEEN :from AND :to
                GROUP BY DATE(ngayTao), DATE_FORMAT(ngayTao, '%d/%m')
                ORDER BY DATE(ngayTao) ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lập bảng map theo ngày
        $revenueMap = [];
        foreach ($rows as $r) {
            $revenueMap[$r['ngay']] = [
                'ngayFmt'   => $r['ngayFmt'],
                'doanhThu'  => (float)$r['doanhThu'],
                'soDon'     => (int)$r['soDon']
            ];
        }

        // Tạo chuỗi ngày liên tục từ $from đến $to
        $labels   = [];
        $revenues = [];
        $orders   = [];

        $current = strtotime($from);
        $end     = strtotime($to);

        while ($current <= $end) {
            $dateKey = date('Y-m-d', $current);
            $fmt     = date('d/m', $current);

            $labels[]   = $fmt;
            $revenues[] = isset($revenueMap[$dateKey]) ? $revenueMap[$dateKey]['doanhThu'] : 0.0;
            $orders[]   = isset($revenueMap[$dateKey]) ? $revenueMap[$dateKey]['soDon'] : 0;

            $current = strtotime('+1 day', $current);
        }

        return [
            'labels'   => $labels,
            'revenues' => $revenues,
            'orders'   => $orders
        ];
    }

    /**
     * Thống kê tỷ lệ doanh thu theo Kênh bán (Tại quầy vs Đặt Online) để dựng Biểu đồ Tròn (Pie/Doughnut Chart)
     */
    public function getRevenueByChannel(?string $fromDate = null, ?string $toDate = null): array {
        $range = $this->sanitizeDateRange($fromDate, $toDate);
        $from = $range['from'];
        $to   = $range['to'];

        $sql = "SELECT 
                    kenhBan, 
                    COUNT(*) AS soDon, 
                    COALESCE(SUM(tongThanhToan), 0) AS doanhThu 
                FROM donhang 
                WHERE trangThaiDon = 'HOAN_THANH'
                  AND DATE(ngayTao) BETWEEN :from AND :to
                GROUP BY kenhBan";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [
            'ONLINE' => ['soDon' => 0, 'doanhThu' => 0.0, 'percent' => 0.0],
            'TAI_QUAY' => ['soDon' => 0, 'doanhThu' => 0.0, 'percent' => 0.0],
            'totalRevenue' => 0.0
        ];

        foreach ($rows as $r) {
            $ch = $r['kenhBan'];
            if (isset($result[$ch])) {
                $result[$ch]['soDon'] = (int)$r['soDon'];
                $result[$ch]['doanhThu'] = (float)$r['doanhThu'];
                $result['totalRevenue'] += (float)$r['doanhThu'];
            }
        }

        $tot = $result['totalRevenue'] > 0 ? $result['totalRevenue'] : 1;
        $result['ONLINE']['percent'] = round(($result['ONLINE']['doanhThu'] / $tot) * 100, 1);
        $result['TAI_QUAY']['percent'] = round(($result['TAI_QUAY']['doanhThu'] / $tot) * 100, 1);

        return $result;
    }

    /**
     * Top 5 laptop có số lượng bán nhiều nhất trong các đơn hoàn thành
     */
    public function getTopSellingProducts(int $limit = 5, ?string $fromDate = null, ?string $toDate = null): array {
        $range = $this->sanitizeDateRange($fromDate, $toDate);
        $from = $range['from'];
        $to   = $range['to'];

        $sql = "SELECT 
                    ctdh.maSP,
                    sp.tenSP,
                    sp.giaBan,
                    sp.hinhAnh,
                    th.tenTH,
                    dm.tenDM,
                    SUM(ctdh.soLuong) AS tongSoLuongBan,
                    SUM(ctdh.thanhTien) AS tongDoanhThu
                FROM chitietdonhang ctdh
                JOIN donhang dh ON ctdh.maDH = dh.maDH
                LEFT JOIN sanpham sp ON ctdh.maSP = sp.maSP
                LEFT JOIN thuonghieu th ON sp.maTH = th.maTH
                LEFT JOIN danhmuc dm ON sp.maDM = dm.maDM
                WHERE dh.trangThaiDon = 'HOAN_THANH'
                  AND DATE(dh.ngayTao) BETWEEN :from AND :to
                GROUP BY ctdh.maSP, sp.tenSP, sp.giaBan, sp.hinhAnh, th.tenTH, dm.tenDM
                ORDER BY tongSoLuongBan DESC, tongDoanhThu DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':from', $from);
        $stmt->bindValue(':to', $to);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Danh sách đối soát công nợ chi tiết theo từng Nhà cung cấp
     */
    public function getSupplierDebtList(int $limit = 50): array {
        $sql = "SELECT 
                    pn.maPhieu,
                    pn.ngayNhap,
                    ncc.maNCC,
                    ncc.tenCongTy,
                    ncc.soDienThoai,
                    ncc.email,
                    pn.soHoaDonNCC,
                    pn.tongTienNhap,
                    pn.tongTienTraTruocNCC,
                    (pn.tongTienNhap - pn.tongTienTraTruocNCC) AS conNo,
                    pn.trangThaiThanhToan
                FROM phieunhap pn
                LEFT JOIN nhacungcap ncc ON pn.maNCC = ncc.maNCC
                ORDER BY (pn.tongTienNhap - pn.tongTienTraTruocNCC) DESC, pn.ngayNhap DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy toàn bộ danh sách đơn hàng hoàn thành phục vụ xuất file CSV / Excel
     */
    public function getOrderExportData(?string $fromDate = null, ?string $toDate = null): array {
        $range = $this->sanitizeDateRange($fromDate, $toDate);
        $from = $range['from'];
        $to   = $range['to'];

        $sql = "SELECT 
                    dh.maDH,
                    dh.ngayTao,
                    COALESCE(kh.hoVaTen, udh.tenNguoiNhan, 'Khách vãng lai') AS tenKhachHang,
                    COALESCE(kh.soDienThoai, udh.sdtNhanHang, '') AS soDienThoai,
                    dh.kenhBan,
                    dh.phuongThucThanhToan,
                    dh.tongTienHang,
                    dh.tienGiamGia,
                    dh.tongThanhToan,
                    dh.trangThaiDon
                FROM donhang dh
                LEFT JOIN khachhang kh ON dh.maKH = kh.maKH
                LEFT JOIN userdathang udh ON dh.maDH = udh.maDH
                WHERE dh.trangThaiDon = 'HOAN_THANH'
                  AND DATE(dh.ngayTao) BETWEEN :from AND :to
                ORDER BY dh.ngayTao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
