<?php
/**
 * File: views/admin/dashboard.php
 * Trang Quản trị Hệ thống & Bảng Điều Khiển Tổng Quan (Admin & Nhân viên nội bộ)
 * Tích hợp Biểu đồ Doanh thu theo tháng & Biểu đồ Tròn trạng thái đơn hàng (Chart.js)
 */

require_once __DIR__ . '/../../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../../config/Database.php';

// Chỉ cho phép admin và các nhân viên nội bộ truy cập
AuthMiddleware::requireRole(['admin', 'nv_kho', 'nv_ban_hang', 'ke_toan', 'thu_ngan', 'ky_thuat_vien']);

$currentUser = AuthMiddleware::getCurrentUser();

// Truy vấn số liệu thống kê thực tế từ Cơ sở dữ liệu
try {
    $db = Database::getConnection();
    $totalActiveLaptops = (int)$db->query("SELECT COUNT(*) FROM sanpham WHERE trangThai = 1 AND isDeleted = 0")->fetchColumn();
    $ordersToday        = (int)$db->query("SELECT COUNT(*) FROM donhang WHERE DATE(ngayTao) = CURDATE()")->fetchColumn();
    $pendingOrders      = (int)$db->query("SELECT COUNT(*) FROM donhang WHERE trangThaiDon = 'CHO_DUYET'")->fetchColumn();
    $lowStockLaptops    = (int)$db->query("SELECT COUNT(*) FROM sanpham WHERE soLuongTon <= 5 AND isDeleted = 0")->fetchColumn();
    
    // Tổng doanh thu thực tế (đơn hàng HOAN_THANH)
    $totalRevenue       = (float)$db->query("SELECT COALESCE(SUM(tongThanhToan), 0) FROM donhang WHERE trangThaiDon = 'HOAN_THANH'")->fetchColumn();
    
    // Doanh thu tháng này
    $revenueThisMonth   = (float)$db->query("SELECT COALESCE(SUM(tongThanhToan), 0) FROM donhang WHERE trangThaiDon = 'HOAN_THANH' AND MONTH(ngayTao) = MONTH(CURDATE()) AND YEAR(ngayTao) = YEAR(CURDATE())")->fetchColumn();
} catch (Exception $e) {
    $totalActiveLaptops = 0;
    $ordersToday        = 0;
    $pendingOrders      = 0;
    $lowStockLaptops    = 0;
    $totalRevenue       = 0;
    $revenueThisMonth   = 0;
}

// 2. Dữ liệu Doanh thu 6 tháng gần nhất cho Biểu đồ Đường/Vùng (Line/Area Chart)
$chartMonths = [];
for ($i = 5; $i >= 0; $i--) {
    $dateObj = new DateTime("-{$i} months");
    $key = $dateObj->format('Y-m');
    $label = 'Tháng ' . $dateObj->format('m/Y');
    $chartMonths[$key] = [
        'label' => $label,
        'revenue' => 0.0,
        'orders' => 0
    ];
}

try {
    $stmtM = $db->query("
        SELECT 
            DATE_FORMAT(ngayTao, '%Y-%m') as mKey,
            COALESCE(SUM(tongThanhToan), 0) as rev,
            COUNT(*) as cnt
        FROM donhang
        WHERE trangThaiDon = 'HOAN_THANH'
          AND ngayTao >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY mKey
        ORDER BY mKey ASC
    ");
    while ($r = $stmtM->fetch(PDO::FETCH_ASSOC)) {
        if (isset($chartMonths[$r['mKey']])) {
            $chartMonths[$r['mKey']]['revenue'] = (float)$r['rev'];
            $chartMonths[$r['mKey']]['orders']  = (int)$r['cnt'];
        }
    }
} catch (Exception $e) {}

$chartLabels   = array_column($chartMonths, 'label');
$chartRevenues = array_column($chartMonths, 'revenue');
$chartOrders   = array_column($chartMonths, 'orders');

// 3. Dữ liệu Cơ cấu Trạng thái Đơn hàng cho Biểu đồ Tròn (Doughnut Chart)
$orderStatusMap = [
    'HOAN_THANH' => ['label' => 'Hoàn thành', 'color' => '#10b981', 'count' => 0],
    'CHO_DUYET'  => ['label' => 'Chờ duyệt',  'color' => '#f59e0b', 'count' => 0],
    'DANG_GIAO'  => ['label' => 'Đang giao',  'color' => '#3b82f6', 'count' => 0],
    'DA_HUY'     => ['label' => 'Đã hủy',     'color' => '#ef4444', 'count' => 0],
];
try {
    $stmtStatus = $db->query("SELECT trangThaiDon, COUNT(*) as cnt FROM donhang GROUP BY trangThaiDon");
    while ($r = $stmtStatus->fetch(PDO::FETCH_ASSOC)) {
        $st = $r['trangThaiDon'];
        if (isset($orderStatusMap[$st])) {
            $orderStatusMap[$st]['count'] = (int)$r['cnt'];
        }
    }
} catch (Exception $e) {}

$totalOrdersAll = array_sum(array_column($orderStatusMap, 'count'));
$statusLabels = [];
$statusCounts = [];
$statusColors = [];
foreach ($orderStatusMap as $item) {
    $statusLabels[] = $item['label'];
    $statusCounts[] = $item['count'];
    $statusColors[] = $item['color'];
}

// Ánh xạ tên vai trò sang tiếng Việt thân thiện
$roleNames = [
    'admin'         => 'Quản Trị Viên (Admin)',
    'nv_kho'        => 'Nhân Viên Kho',
    'nv_ban_hang'   => 'Nhân Viên Bán Hàng',
    'ke_toan'       => 'Kế Toán',
    'thu_ngan'      => 'Thu Ngân',
    'ky_thuat_vien' => 'Kỹ Thuật Viên',
    'user'          => 'Khách Hàng',
];
$displayRole = $roleNames[$currentUser['role']] ?? $currentUser['role'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng Điều Khiển Quản Trị - EmShu Store</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js 4 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        .top-navbar {
            background: #ffffff;
            border-radius: 14px;
            padding: 16px 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        .stat-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 20px 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            border: 1px solid #e2e8f0;
            border-left: 4px solid #2563eb;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        }
        .stat-card .stat-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2.2rem;
            opacity: 0.15;
            transition: transform 0.25s ease, opacity 0.25s ease;
        }
        .stat-card:hover .stat-icon {
            opacity: 0.35;
            transform: translateY(-50%) scale(1.1);
        }
        .chart-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            transition: box-shadow 0.2s ease;
        }
        .chart-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        }
        .chart-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border-radius: 14px 14px 0 0;
        }
        .chart-card-body {
            padding: 22px;
            position: relative;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<?php 
$activeMenu = 'dashboard';
require __DIR__ . '/layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT -->
<main class="main-content">
    <!-- Top Navbar -->
    <header class="top-navbar">
        <div>
            <h5 class="mb-1 fw-bold text-dark">
                Xin chào <span class="text-primary"><?= htmlspecialchars($currentUser['hoTen']) ?></span>
            </h5>
            <div class="text-muted small">
                <i class="bi bi-shield-check me-1 text-success"></i>
                Vai trò hiện tại: <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1"><?= htmlspecialchars($displayRole) ?></span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="text-secondary small">Mã NV: <strong><?= htmlspecialchars($currentUser['maTk']) ?></strong></span>
            <a href="index.php?url=logout" class="btn btn-sm btn-danger px-3">
                <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
            </a>
        </div>
    </header>

    <!-- Header Tổng quan -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-speedometer2 text-primary"></i> Tổng quan
        </h5>
        <span class="text-muted small">Cập nhật lúc: <?= date('d/m/Y H:i') ?></span>
    </div>

    <!-- Thống kê nhanh (4 KPI Cards chuẩn hình mẫu) -->
    <div class="row g-3 mb-4">
        <!-- 1. Laptop Đang Bán -->
        <div class="col-md-3">
            <a href="index.php?action=admin-products" class="text-decoration-none">
                <div class="stat-card" style="border-left-color: #2563eb;">
                    <div class="text-muted small fw-semibold text-uppercase">Laptop Đang Bán</div>
                    <div class="fs-3 fw-bold text-dark mt-1"><?= number_format($totalActiveLaptops) ?></div>
                    <div class="text-primary small mt-1"><i class="bi bi-box-seam me-1"></i> Xem danh sách</div>
                    <i class="bi bi-laptop stat-icon text-primary"></i>
                </div>
            </a>
        </div>

        <!-- 2. Đơn Chờ Xử Lý -->
        <div class="col-md-3">
            <a href="index.php?action=admin-orders&status=CHO_DUYET" class="text-decoration-none">
                <div class="stat-card" style="border-left-color: #f59e0b;">
                    <div class="text-muted small fw-semibold text-uppercase">Đơn Chờ Xử Lý</div>
                    <div class="fs-3 fw-bold text-dark mt-1"><?= number_format($pendingOrders) ?></div>
                    <div class="text-warning small mt-1"><i class="bi bi-clock-history me-1"></i> Cần duyệt sớm</div>
                    <i class="bi bi-cart3 stat-icon text-warning"></i>
                </div>
            </a>
        </div>

        <!-- 3. Tổng Doanh Thu (Doanh thu thực tế đơn hoàn thành) -->
        <div class="col-md-3">
            <a href="index.php?action=admin-reports" class="text-decoration-none">
                <div class="stat-card" style="border-left-color: #10b981;">
                    <div class="text-muted small fw-semibold text-uppercase">Tổng Doanh Thu</div>
                    <div class="fs-4 fw-bold text-success mt-1"><?= number_format($totalRevenue, 0, ',', '.') ?> đ</div>
                    <div class="text-muted small mt-1"><i class="bi bi-arrow-up-right text-success me-1"></i> Đơn đã hoàn tất</div>
                    <i class="bi bi-currency-dollar stat-icon text-success"></i>
                </div>
            </a>
        </div>

        <!-- 4. Tồn Kho Sắp Hết -->
        <div class="col-md-3">
            <a href="index.php?action=admin-products" class="text-decoration-none">
                <div class="stat-card" style="border-left-color: #ef4444;">
                    <div class="text-muted small fw-semibold text-uppercase">Tồn Kho Sắp Hết</div>
                    <div class="fs-3 fw-bold text-dark mt-1"><?= number_format($lowStockLaptops) ?></div>
                    <div class="text-danger small mt-1"><i class="bi bi-exclamation-triangle me-1"></i> Dưới 5 máy</div>
                    <i class="bi bi-box-arrow-in-down stat-icon text-danger"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- KHU VỰC ĐỒ THỊ DOANH THU & TRẠNG THÁI (2 BIỂU ĐỒ SONG SONG) -->
    <div class="row g-3 mb-4">
        <!-- 1. Biểu đồ đường Doanh thu theo tháng (Chiếm 8 cột) -->
        <div class="col-lg-8">
            <div class="chart-card h-100">
                <div class="chart-card-header">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-graph-up text-primary"></i>
                            <span>Doanh thu theo tháng</span>
                        </h6>
                        <small class="text-muted">Biến động doanh số bán hàng trong 6 tháng gần nhất</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        Doanh thu (VNĐ)
                    </span>
                </div>
                <div class="chart-card-body">
                    <div style="height: 310px; position: relative;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Biểu đồ tròn Trạng thái đơn hàng (Chiếm 4 cột) -->
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="chart-card-header">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-pie-chart-fill text-primary"></i>
                            <span>Trạng thái đơn hàng</span>
                        </h6>
                        <small class="text-muted">Tổng: <strong><?= number_format($totalOrdersAll) ?></strong> đơn hàng</small>
                    </div>
                    <a href="index.php?action=admin-orders" class="small text-decoration-none text-primary">
                        Chi tiết <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
                <div class="chart-card-body d-flex flex-column justify-content-between">
                    <div style="height: 220px; position: relative;">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <!-- Chú thích chi tiết -->
                    <div class="row g-2 mt-2 pt-2 border-top">
                        <?php foreach ($orderStatusMap as $item): ?>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 small">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background-color: <?= $item['color'] ?>; display: inline-block;"></span>
                                <span class="text-muted"><?= htmlspecialchars($item['label']) ?>:</span>
                                <strong class="text-dark ms-auto"><?= number_format($item['count']) ?></strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng thông tin hệ thống -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Thông tin phiên làm việc</h6>
            <span class="badge bg-success-subtle text-success">Đang hoạt động (Active)</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light" style="width: 220px;">Mã tài khoản (maTk):</th>
                            <td><code><?= htmlspecialchars($currentUser['maTk']) ?></code></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Họ và tên:</th>
                            <td class="fw-semibold"><?= htmlspecialchars($currentUser['hoTen']) ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Tên tài khoản (Username):</th>
                            <td><?= htmlspecialchars($currentUser['tenTaiKhoan'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Email:</th>
                            <td><?= htmlspecialchars($currentUser['email'] ?? 'Chưa cập nhật') ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Quyền hạn trong hệ thống:</th>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($currentUser['role']) ?></span> (<?= htmlspecialchars($displayRole) ?>)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script khởi tạo Đồ thị Doanh thu & Trạng thái (Chart.js) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Dữ liệu từ PHP
    const monthlyLabels   = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
    const monthlyRevenues = <?= json_encode($chartRevenues) ?>;
    const monthlyOrders   = <?= json_encode($chartOrders) ?>;

    const statusLabels = <?= json_encode($statusLabels, JSON_UNESCAPED_UNICODE) ?>;
    const statusCounts = <?= json_encode($statusCounts) ?>;
    const statusColors = <?= json_encode($statusColors) ?>;

    // === BIỂU ĐỒ 1: DOANH THU THEO THÁNG (LINE / AREA CHART VỚI GRADIENT XANH DƯƠNG) ===
    const ctxRevenue = document.getElementById('revenueChart');
    if (ctxRevenue) {
        const revenueCtx = ctxRevenue.getContext('2d');
        // Tạo dải màu gradient mượt dưới đường vẽ
        const gradientBlue = revenueCtx.createLinearGradient(0, 0, 0, 300);
        gradientBlue.addColorStop(0, 'rgba(37, 99, 235, 0.28)');
        gradientBlue.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: monthlyRevenues,
                    borderColor: '#2563eb',
                    borderWidth: 2.5,
                    backgroundColor: gradientBlue,
                    fill: true,
                    tension: 0.35, // Độ cong mềm mại của đường
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4.5,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: '#1d4ed8',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 2.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Ẩn legend vì đã có tiêu đề rõ ràng
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 8,
                        boxPadding: 6,
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed.y || 0;
                                return ' Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(val) + ' đ';
                            },
                            afterLabel: function(context) {
                                const idx = context.dataIndex;
                                const orders = monthlyOrders[idx] || 0;
                                return ' Số đơn hoàn thành: ' + orders + ' đơn';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 12,
                                family: "'Plus Jakarta Sans', sans-serif"
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 11,
                                family: "'Plus Jakarta Sans', sans-serif"
                            },
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(0) + ' Tr';
                                } else if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + ' K';
                                }
                                return value + ' đ';
                            }
                        }
                    }
                }
            }
        });
    }

    // === BIỂU ĐỒ 2: TRẠNG THÁI ĐƠN HÀNG (DOUGHNUT CHART VÀNH KHUYÊN) ===
    const ctxStatus = document.getElementById('statusChart');
    if (ctxStatus) {
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: statusColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%', // Độ rỗng ở giữa giống hình mẫu
                plugins: {
                    legend: {
                        display: false // Đã có danh sách chú thích chi tiết bên dưới
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const val = context.parsed || 0;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ' ' + context.label + ': ' + val + ' đơn (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

</body>
</html>
