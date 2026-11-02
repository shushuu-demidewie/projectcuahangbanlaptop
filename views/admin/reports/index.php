<?php
/**
 * File: views/admin/reports/index.php
 * Màn hình Báo Cáo Doanh Số & Đối Soát Tài Chính
 * Dành cho Quản Trị Viên (Admin) và Kế Toán (ke_toan)
 */

$currentUser = AuthMiddleware::getCurrentUser();
$userRole    = $currentUser['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Doanh Số & Đối Soát Tài Chính - EmShu Store</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f8fafc;
            --card-border: #e2e8f0;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            min-height: 100vh;
        }

        /* Sidebar Standard */
        .sidebar {
            width: var(--sidebar-width);
            background: #0f172a;
            color: #e2e8f0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 0 24px 20px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 700;
            text-decoration: none;
        }

        .sidebar-nav {
            list-style: none;
            padding: 16px 12px;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-nav .nav-link {
            color: #94a3b8;
            padding: 10px 14px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.92rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
            margin-bottom: 3px;
        }

        .sidebar-nav .nav-link:hover {
            background: #1e293b;
            color: #ffffff;
        }

        .sidebar-nav .nav-link.active {
            background: var(--primary-color);
            color: #ffffff;
            font-weight: 600;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 24px 32px;
        }

        /* Top Header */
        .top-navbar {
            background: #ffffff;
            border-radius: 14px;
            padding: 16px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            border: 1px solid var(--card-border);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* Filter Box */
        .filter-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--card-border);
            padding: 18px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            margin-bottom: 24px;
        }

        .quick-date-btn {
            padding: 4px 10px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 6px;
            cursor: pointer;
            color: #475569;
            transition: all 0.15s;
        }

        .quick-date-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* 4 Financial KPI Cards */
        .kpi-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--card-border);
            padding: 18px 22px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -4px rgba(0,0,0,0.06);
        }

        .kpi-card.green-accent {
            border-left: 4px solid #10b981;
        }
        .kpi-card.blue-accent {
            border-left: 4px solid #2563eb;
        }
        .kpi-card.orange-accent {
            border-left: 4px solid #f59e0b;
        }
        .kpi-card.red-accent {
            border-left: 4px solid #ef4444;
        }

        .kpi-icon-badge {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        .kpi-val {
            font-size: 1.55rem;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            line-height: 1.25;
            margin: 8px 0 4px 0;
        }

        .kpi-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Chart & Content Cards */
        .chart-box-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--card-border);
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.02);
            height: 100%;
        }

        .table-custom-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--card-border);
            box-shadow: 0 1px 4px rgba(0,0,0,0.02);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .table thead th {
            background: #f8fafc;
            font-size: 0.74rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 13px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table tbody td {
            padding: 13px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .rank-badge {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.8rem;
        }
        .rank-1 { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .rank-2 { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .rank-3 { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
        .rank-other { background: #f8fafc; color: #94a3b8; }
    </style>
</head>
<body>

<!-- SIDEBAR ĐỒNG BỘ 100% -->
<?php 
$activeMenu = 'reports';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT TIÊU CHUẨN (MARGIN-LEFT = 260PX) -->
<main class="main-content">

    <!-- Top Header Bar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-2 bg-primary-subtle text-primary fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-graph-up"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">Báo Cáo Doanh Số & Đối Soát Tài Chính</h5>
                <small class="text-muted">Theo dõi doanh thu thực tế, giá vốn hàng bán (COGS), lợi nhuận gộp và công nợ nhà cung cấp</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 small">
                <i class="bi bi-shield-check text-success me-1"></i> Phân quyền: <strong class="text-uppercase"><?= htmlspecialchars($userRole) ?></strong>
            </span>
            <a href="index.php?action=admin-report-export&fromDate=<?= urlencode($summary['fromDate']) ?>&toDate=<?= urlencode($summary['toDate']) ?>" 
               class="btn btn-success fw-bold d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span>Xuất File CSV / Excel</span>
            </a>
        </div>
    </header>

    <!-- Filter Form Card -->
    <div class="filter-card">
        <form action="index.php" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="admin-reports">

            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">
                    <i class="bi bi-calendar-event me-1 text-primary"></i> Từ ngày:
                </label>
                <input type="date" name="fromDate" id="inputFromDate" class="form-control" 
                       value="<?= htmlspecialchars($summary['fromDate']) ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">
                    <i class="bi bi-calendar-check me-1 text-primary"></i> Đến ngày:
                </label>
                <input type="date" name="toDate" id="inputToDate" class="form-control" 
                       value="<?= htmlspecialchars($summary['toDate']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary mb-1">Chọn nhanh khoảng thời gian:</label>
                <div class="d-flex gap-1 flex-wrap">
                    <button type="button" class="quick-date-btn" onclick="setPreset('today')">Hôm nay</button>
                    <button type="button" class="quick-date-btn" onclick="setPreset('7days')">7 ngày</button>
                    <button type="button" class="quick-date-btn" onclick="setPreset('thisMonth')">Tháng này</button>
                    <button type="button" class="quick-date-btn" onclick="setPreset('lastMonth')">Tháng trước</button>
                </div>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary fw-bold w-100">
                    <i class="bi bi-funnel me-1"></i> Lọc Số Liệu
                </button>
                <a href="index.php?action=admin-reports" class="btn btn-outline-secondary" title="Đặt lại về tháng này">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- 4 Financial KPI Cards -->
    <div class="row g-3 mb-4">
        
        <!-- 1. Tổng Doanh Thu Bán Hàng (Màu xanh lá) -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-card green-accent">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Tổng Doanh Thu Bán Hàng</div>
                        <div class="kpi-val text-success">
                            <?= number_format($summary['tongDoanhThu'], 0, ',', '.') ?> đ
                        </div>
                    </div>
                    <div class="kpi-icon-badge bg-success-subtle text-success">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
                <div class="small text-muted pt-2 border-top mt-2 d-flex justify-content-between">
                    <span><i class="bi bi-check2-circle text-success me-1"></i>Đơn hoàn thành:</span>
                    <strong class="text-dark font-monospace"><?= $summary['soDonHoanThanh'] ?> đơn (<?= $summary['soMayDaBan'] ?> máy)</strong>
                </div>
            </div>
        </div>

        <!-- 2. Lợi Nhuận Gộp Ước Tính (Màu xanh dương) -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-card blue-accent">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Lợi Nhuận Gộp Ước Tính</div>
                        <div class="kpi-val text-primary">
                            <?= number_format($summary['loiNhuanGop'], 0, ',', '.') ?> đ
                        </div>
                    </div>
                    <div class="kpi-icon-badge bg-primary-subtle text-primary">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <div class="small text-muted pt-2 border-top mt-2 d-flex justify-content-between">
                    <span><i class="bi bi-pie-chart text-primary me-1"></i>Tỷ suất lợi nhuận:</span>
                    <strong class="text-primary font-monospace"><?= $summary['tiLeLoiNhuan'] ?>% / Doanh thu</strong>
                </div>
            </div>
        </div>

        <!-- 3. Giá Vốn Đã Bán & Chi Phí Nhập (Màu cam) -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-card orange-accent">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Giá Vốn Hàng Bán (COGS)</div>
                        <div class="kpi-val text-warning" style="color: #d97706 !important;">
                            <?= number_format($summary['tongGiaVon'], 0, ',', '.') ?> đ
                        </div>
                    </div>
                    <div class="kpi-icon-badge bg-warning-subtle text-warning">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
                <div class="small text-muted pt-2 border-top mt-2 d-flex justify-content-between">
                    <span><i class="bi bi-truck text-warning me-1"></i>Tổng nhập trong kỳ:</span>
                    <strong class="text-dark font-monospace"><?= number_format($summary['tongChiPhiNhap'], 0, ',', '.') ?> đ</strong>
                </div>
            </div>
        </div>

        <!-- 4. Công Nợ Phải Trả Nhà Cung Cấp (Màu đỏ) -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-card red-accent">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title">Công Nợ Phải Trả NCC</div>
                        <div class="kpi-val text-danger">
                            <?= number_format($summary['tongCongNoNCC'], 0, ',', '.') ?> đ
                        </div>
                    </div>
                    <div class="kpi-icon-badge bg-danger-subtle text-danger">
                        <i class="bi bi-exclamation-octagon"></i>
                    </div>
                </div>
                <div class="small text-muted pt-2 border-top mt-2 d-flex justify-content-between">
                    <span><i class="bi bi-receipt-cutoff text-danger me-1"></i>Phiếu chưa thanh toán:</span>
                    <strong class="text-danger font-monospace"><?= $summary['soPhieuConNo'] ?> phiếu nhập</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Biểu đồ 1: Doanh thu theo từng ngày -->
        <div class="col-lg-8">
            <div class="chart-box-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i>Biến Động Doanh Thu Theo Từng Ngày
                    </h6>
                    <span class="badge bg-light text-secondary border small">Đơn vị: VNĐ</span>
                </div>
                <div style="height: 270px; width: 100%;">
                    <canvas id="dailyRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Biểu đồ 2: Tỷ trọng kênh bán hàng -->
        <div class="col-lg-4">
            <div class="chart-box-card d-flex flex-column justify-content-between">
                <div>
                    <h6 class="fw-bold mb-3 text-dark">
                        <i class="bi bi-pie-chart-fill text-primary me-2"></i>Tỷ Trọng Kênh Bán Hàng
                    </h6>
                    <div style="height: 190px; width: 100%;">
                        <canvas id="salesChannelChart"></canvas>
                    </div>
                </div>

                <div class="border-top pt-3 mt-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-circle p-1" style="background-color: #10b981;">&nbsp;</span>
                            <span class="small fw-bold text-dark">Bán Tại Quầy (POS)</span>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-success font-monospace"><?= number_format($channelData['TAI_QUAY']['doanhThu'], 0, ',', '.') ?> đ</span>
                            <small class="text-muted d-block"><?= $channelData['TAI_QUAY']['percent'] ?>% (<?= $channelData['TAI_QUAY']['soDon'] ?> đơn)</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-circle p-1" style="background-color: #3b82f6;">&nbsp;</span>
                            <span class="small fw-bold text-dark">Đặt Mua Online</span>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-primary font-monospace"><?= number_format($channelData['ONLINE']['doanhThu'], 0, ',', '.') ?> đ</span>
                            <small class="text-muted d-block"><?= $channelData['ONLINE']['percent'] ?>% (<?= $channelData['ONLINE']['soDon'] ?> đơn)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Detailed Tables Row -->
    <div class="row g-3">
        
        <!-- Bảng 1: Top 5 Laptop Bán Chạy Nhất -->
        <div class="col-lg-6">
            <div class="table-custom-card h-100 mb-0">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-trophy-fill text-warning me-2"></i>Top 5 Laptop Bán Chạy Nhất (Trong kỳ)
                    </h6>
                    <span class="badge bg-primary-subtle text-primary">Đơn hoàn thành</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">Top</th>
                                <th>Laptop / Dòng Máy</th>
                                <th class="text-center" style="width: 90px;">Đã Bán</th>
                                <th class="text-end" style="width: 150px;">Doanh Thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topProducts)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">
                                        Không có đơn hàng hoàn thành nào trong khoảng thời gian đã chọn.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($topProducts as $idx => $prod): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php 
                                                $rankClass = 'rank-other';
                                                if ($idx === 0) $rankClass = 'rank-1';
                                                elseif ($idx === 1) $rankClass = 'rank-2';
                                                elseif ($idx === 2) $rankClass = 'rank-3';
                                            ?>
                                            <span class="rank-badge <?= $rankClass ?>"><?= $idx + 1 ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($prod['tenSP']) ?>">
                                                <?= htmlspecialchars($prod['tenSP']) ?>
                                            </div>
                                            <small class="text-muted">
                                                Mã: <code class="text-primary"><?= htmlspecialchars($prod['maSP']) ?></code> | <?= htmlspecialchars($prod['tenTH'] ?? '') ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary fw-bold font-monospace px-2 py-1 fs-6">
                                                <?= (int)$prod['tongSoLuongBan'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-danger font-monospace">
                                            <?= number_format((float)$prod['tongDoanhThu'], 0, ',', '.') ?> đ
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bảng 2: Bảng Đối Soát Công Nợ Nhà Cung Cấp -->
        <div class="col-lg-6">
            <div class="table-custom-card h-100 mb-0">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-journal-check text-primary me-2"></i>Bảng Đối Soát Công Nợ Nhà Cung Cấp
                    </h6>
                    <a href="index.php?action=admin-imports" class="btn btn-sm btn-outline-primary fw-semibold py-0 px-2 small">
                        Xem Phiếu Nhập
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Phiếu / NCC</th>
                                <th class="text-end" style="width: 110px;">Tiền Nhập</th>
                                <th class="text-end" style="width: 100px;">Đã Trả</th>
                                <th class="text-end" style="width: 110px;">Còn Nợ Lại</th>
                                <th class="text-center" style="width: 100px;">Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($supplierDebts)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        Chưa có bản ghi phiếu nhập kho nào trong hệ thống!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($supplierDebts as $deb): ?>
                                    <?php $conNo = (float)$deb['conNo']; ?>
                                    <tr>
                                        <td>
                                            <a href="index.php?action=admin-import-detail&id=<?= urlencode($deb['maPhieu']) ?>" 
                                               class="fw-bold text-primary text-decoration-none small">
                                                <?= htmlspecialchars($deb['maPhieu']) ?>
                                            </a>
                                            <div class="small fw-semibold text-dark text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($deb['tenCongTy'] ?? '') ?>">
                                                <?= htmlspecialchars($deb['tenCongTy'] ?? 'Chưa rõ NCC') ?>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.72rem;">
                                                <?= !empty($deb['ngayNhap']) ? date('d/m/Y', strtotime($deb['ngayNhap'])) : '' ?>
                                            </small>
                                        </td>
                                        <td class="text-end font-monospace small">
                                            <?= number_format((float)$deb['tongTienNhap'], 0, ',', '.') ?> đ
                                        </td>
                                        <td class="text-end font-monospace small text-success">
                                            <?= number_format((float)$deb['tongTienTraTruocNCC'], 0, ',', '.') ?> đ
                                        </td>
                                        <td class="text-end font-monospace small fw-bold <?= $conNo > 0 ? 'text-danger' : 'text-muted' ?>">
                                            <?= number_format($conNo, 0, ',', '.') ?> đ
                                        </td>
                                        <td class="text-center">
                                            <?php if ($deb['trangThaiThanhToan'] === 'DA_THANH_TOAN'): ?>
                                                <span class="badge bg-success-subtle text-success small">Đã trả đủ</span>
                                            <?php elseif ($deb['trangThaiThanhToan'] === 'THANH_TOAN_MOT_PHAN'): ?>
                                                <span class="badge bg-warning-subtle text-warning small">Còn nợ</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger small">Chưa thanh toán</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Dữ liệu từ Controller truyền sang
const chartDaysData    = <?= $chartDaysJson ?>;
const chartChannelData = <?= $chartChannelJson ?>;

// Hàm định dạng tiền tệ VNĐ cho Chart.js
function formatVND(value) {
    return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Biểu đồ Đường/Cột: Doanh thu theo từng ngày
    const ctxDaily = document.getElementById('dailyRevenueChart');
    if (ctxDaily && chartDaysData.labels) {
        new Chart(ctxDaily, {
            type: 'bar',
            data: {
                labels: chartDaysData.labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: chartDaysData.revenues,
                    backgroundColor: 'rgba(37, 99, 235, 0.75)',
                    hoverBackgroundColor: '#2563eb',
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Doanh thu: ' + formatVND(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) {
                                if (val >= 1000000) {
                                    return (val / 1000000).toFixed(0) + ' Tr';
                                }
                                return val;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Biểu đồ Tròn (Doughnut): Tỷ trọng kênh bán hàng
    const ctxChannel = document.getElementById('salesChannelChart');
    if (ctxChannel) {
        const revPos    = chartChannelData.TAI_QUAY ? chartChannelData.TAI_QUAY.doanhThu : 0;
        const revOnline = chartChannelData.ONLINE ? chartChannelData.ONLINE.doanhThu : 0;

        new Chart(ctxChannel, {
            type: 'doughnut',
            data: {
                labels: ['Tại Quầy (POS)', 'Mua Online'],
                datasets: [{
                    data: [revPos, revOnline],
                    backgroundColor: ['#10b981', '#3b82f6'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + formatVND(context.raw);
                            }
                        }
                    }
                },
                cutout: '68%'
            }
        });
    }
});

// Hàm chọn nhanh khoảng thời gian
function setPreset(type) {
    const fromInput = document.getElementById('inputFromDate');
    const toInput   = document.getElementById('inputToDate');
    const now       = new Date();

    const formatDate = (d) => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    };

    if (type === 'today') {
        const todayStr = formatDate(now);
        fromInput.value = todayStr;
        toInput.value   = todayStr;
    } else if (type === '7days') {
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(now.getDate() - 7);
        fromInput.value = formatDate(sevenDaysAgo);
        toInput.value   = formatDate(now);
    } else if (type === 'thisMonth') {
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        fromInput.value = formatDate(firstDay);
        toInput.value   = formatDate(now);
    } else if (type === 'lastMonth') {
        const firstDayLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const lastDayLastMonth  = new Date(now.getFullYear(), now.getMonth(), 0);
        fromInput.value = formatDate(firstDayLastMonth);
        toInput.value   = formatDate(lastDayLastMonth);
    }
}
</script>
</body>
</html>
