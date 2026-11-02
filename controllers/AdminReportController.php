<?php
/**
 * File: controllers/AdminReportController.php
 * Điều khiển màn hình Báo Cáo Doanh Số & Đối Soát Tài Chính
 * Dành cho Quản Trị Viên (Admin) và Kế Toán (ke_toan)
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../models/ReportModel.php';

class AdminReportController {
    private ReportModel $reportModel;

    public function __construct() {
        AuthMiddleware::startSession();
        // Cấp quyền truy cập cho Admin và Kế toán
        AuthMiddleware::requireRole(['admin', 'ke_toan']);
        $this->reportModel = new ReportModel();
    }

    /**
     * Màn hình Báo cáo Doanh số & Đối soát Tài chính
     */
    public function index(): void {
        $currentUser = AuthMiddleware::getCurrentUser();
        $userRole    = $currentUser['role'] ?? '';

        // Tiếp nhận bộ lọc thời gian (Mặc định: đầu tháng đến hôm nay)
        $fromDate = trim($_GET['fromDate'] ?? date('Y-m-01'));
        $toDate   = trim($_GET['toDate'] ?? date('Y-m-d'));

        // Lấy dữ liệu tổng hợp tài chính
        $summary = $this->reportModel->getFinancialSummary($fromDate, $toDate);

        // Lấy dữ liệu biểu đồ doanh thu theo ngày
        $revenueDays = $this->reportModel->getRevenueByDays(14, $fromDate, $toDate);

        // Lấy dữ liệu tỷ trọng kênh bán (Online vs Tại quầy)
        $channelData = $this->reportModel->getRevenueByChannel($fromDate, $toDate);

        // Lấy danh sách Top 5 laptop bán chạy
        $topProducts = $this->reportModel->getTopSellingProducts(5, $fromDate, $toDate);

        // Lấy bảng đối soát công nợ nhà cung cấp
        $supplierDebts = $this->reportModel->getSupplierDebtList(50);

        // Chuẩn bị dữ liệu JSON cho Chart.js
        $chartDaysJson    = json_encode($revenueDays, JSON_UNESCAPED_UNICODE);
        $chartChannelJson = json_encode($channelData, JSON_UNESCAPED_UNICODE);

        $activeMenu = 'reports';
        require_once __DIR__ . '/../views/admin/reports/index.php';
    }

    /**
     * Xuất dữ liệu báo cáo doanh số ra file CSV / Excel hỗ trợ tiếng Việt đầy đủ (UTF-8 BOM)
     */
    public function exportExcel(): void {
        $fromDate = trim($_GET['fromDate'] ?? date('Y-m-01'));
        $toDate   = trim($_GET['toDate'] ?? date('Y-m-d'));

        $summary = $this->reportModel->getFinancialSummary($fromDate, $toDate);
        $orders  = $this->reportModel->getOrderExportData($fromDate, $toDate);

        $fileName = 'Bao_Cao_Doanh_So_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Ghi BOM UTF-8 để Microsoft Excel trên Windows hiển thị đúng tiếng Việt
        fwrite($output, "\xEF\xBB\xBF");

        // 1. Dòng tiêu đề báo cáo
        fputcsv($output, ['BÁO CÁO DOANH SỐ & ĐỐI SOÁT TÀI CHÍNH - EMSHU STORE']);
        fputcsv($output, ['Khoảng thời gian:', "Từ {$summary['fromDate']} đến {$summary['toDate']}"]);
        fputcsv($output, ['Ngày xuất file:', date('d/m/Y H:i:s')]);
        fputcsv($output, []); // Dòng trống

        // 2. Tóm tắt chỉ số tài chính
        fputcsv($output, ['CHỈ SỐ TÀI CHÍNH', 'GIÁ TRỊ (VNĐ)']);
        fputcsv($output, ['Tổng Doanh Thu Bán Hàng', number_format($summary['tongDoanhThu'], 0, ',', '.') . ' đ']);
        fputcsv($output, ['Tổng Giá Vốn Hàng Bán (COGS)', number_format($summary['tongGiaVon'], 0, ',', '.') . ' đ']);
        fputcsv($output, ['Lợi Nhuận Gộp Ước Tính', number_format($summary['loiNhuanGop'], 0, ',', '.') . ' đ (' . $summary['tiLeLoiNhuan'] . '%)']);
        fputcsv($output, ['Tổng Chi Phí Nhập Hàng', number_format($summary['tongChiPhiNhap'], 0, ',', '.') . ' đ']);
        fputcsv($output, ['Tổng Tiền Đã Trả NCC', number_format($summary['tongTienDaTraNCC'], 0, ',', '.') . ' đ']);
        fputcsv($output, ['Tổng Công Nợ Còn Phải Trả NCC', number_format($summary['tongCongNoNCC'], 0, ',', '.') . ' đ']);
        fputcsv($output, ['Số Đơn Hoàn Thành Trong Kỳ', $summary['soDonHoanThanh'] . ' đơn']);
        fputcsv($output, ['Số Laptop Đã Xuất Bán', $summary['soMayDaBan'] . ' chiếc']);
        fputcsv($output, []); // Dòng trống

        // 3. Danh sách chi tiết các đơn hàng hoàn thành
        fputcsv($output, ['DANH SÁCH CHI TIẾT ĐƠN HÀNG HOÀN THÀNH']);
        fputcsv($output, [
            'STT',
            'Mã Đơn',
            'Ngày Đặt',
            'Tên Khách Hàng',
            'Số Điện Thoại',
            'Kênh Bán',
            'Phương Thức TT',
            'Tiền Hàng',
            'Giảm Giá',
            'Tổng Thanh Toán',
            'Trạng Thái'
        ]);

        $stt = 1;
        foreach ($orders as $o) {
            fputcsv($output, [
                $stt++,
                $o['maDH'],
                date('d/m/Y H:i', strtotime($o['ngayTao'])),
                $o['tenKhachHang'],
                $o['soDienThoai'],
                ($o['kenhBan'] === 'TAI_QUAY' ? 'Tại Quầy' : 'Online'),
                $o['phuongThucThanhToan'],
                $o['tongTienHang'],
                $o['tienGiamGia'],
                $o['tongThanhToan'],
                'Hoàn thành'
            ]);
        }

        fclose($output);
        exit();
    }
}
