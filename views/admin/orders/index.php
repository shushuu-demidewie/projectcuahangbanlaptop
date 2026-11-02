<?php
/**
 * File: views/admin/orders/index.php
 * Giao diện Quản lý Danh sách Đơn Hàng (Admin & Bán Hàng)
 */

$currentUser = AuthMiddleware::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - EmShu Store Admin</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #2563eb;
            --bg-color: #f8fafc;
        }
        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
        }
        .sidebar-nav .nav-link {
            color: #94a3b8;
            padding: 11px 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }
        .sidebar-nav .nav-link:hover, .sidebar-nav .nav-link.active {
            background: #1e293b;
            color: #ffffff;
        }
        .sidebar-nav .nav-link.active {
            background: var(--primary-color);
            color: #ffffff;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 24px 32px;
        }
        .top-navbar {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 24px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .kpi-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            border-left: 4px solid #cbd5e1;
        }
        .table-card, .filter-card {
            background: #ffffff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        .table-card {
            overflow: hidden;
        }
        .filter-card {
            padding: 20px;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<?php 
$activeMenu = 'orders';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT -->
<main class="main-content">
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-cart-check-fill fs-3 text-primary"></i>
            <div>
                <h5 class="mb-0 fw-bold">Quản Lý Đơn Hàng Bán Lẻ</h5>
                <small class="text-muted">Duyệt đơn, gán mã máy Serial/IMEI xuất kho & xuất hóa đơn bảo hành</small>
            </div>
        </div>
        <div>
            <span class="badge bg-primary-subtle text-primary px-3 py-2 fw-semibold">
                Tổng đơn: <?= number_format($stats['total'] ?? 0) ?>
            </span>
        </div>
    </header>

    <!-- Thống kê trạng thái KPI -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="kpi-card" style="border-left-color: #f59e0b;">
                <div class="text-muted small fw-semibold text-uppercase">Chờ Duyệt</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= $stats['choDuyet'] ?? 0 ?></div>
                <div class="small text-warning"><i class="bi bi-clock-history me-1"></i>Đơn mới cần duyệt</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left-color: #3b82f6;">
                <div class="text-muted small fw-semibold text-uppercase">Đang Vận Chuyển</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= $stats['dangGiao'] ?? 0 ?></div>
                <div class="small text-primary"><i class="bi bi-truck me-1"></i>Đang giao cho khách</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left-color: #10b981;">
                <div class="text-muted small fw-semibold text-uppercase">Đã Hoàn Thành</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= $stats['hoanThanh'] ?? 0 ?></div>
                <div class="small text-success"><i class="bi bi-check-circle me-1"></i>Đã xuất hóa đơn & bảo hành</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="border-left-color: #ef4444;">
                <div class="text-muted small fw-semibold text-uppercase">Đơn Đã Hủy</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= $stats['daHuy'] ?? 0 ?></div>
                <div class="small text-danger"><i class="bi bi-x-circle me-1"></i>Đã nhả Serial về kho</div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Bộ lọc & Tìm kiếm -->
    <div class="filter-card">
        <form action="index.php" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="admin-orders">

            <!-- Kênh bán -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Kênh bán hàng:</label>
                <select name="channel" class="form-select">
                    <option value="">-- Tất cả kênh --</option>
                    <option value="TAI_QUAY" <?= ($channel === 'TAI_QUAY') ? 'selected' : '' ?>>Tại quầy (Showroom)</option>
                    <option value="ONLINE" <?= ($channel === 'ONLINE') ? 'selected' : '' ?>>Đặt hàng Online</option>
                </select>
            </div>

            <!-- Trạng thái -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Trạng thái đơn:</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="CHO_DUYET" <?= ($status === 'CHO_DUYET') ? 'selected' : '' ?>>Chờ duyệt</option>
                    <option value="DANG_GIAO" <?= ($status === 'DANG_GIAO') ? 'selected' : '' ?>>Đang giao hàng</option>
                    <option value="HOAN_THANH" <?= ($status === 'HOAN_THANH') ? 'selected' : '' ?>>Đã hoàn thành</option>
                    <option value="DA_HUY" <?= ($status === 'DA_HUY') ? 'selected' : '' ?>>Đã hủy</option>
                </select>
            </div>

            <!-- Tìm kiếm -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Tìm kiếm mã đơn / tên / SĐT khách:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control" placeholder="Mã ĐH, Họ tên hoặc Số điện thoại..." 
                           value="<?= htmlspecialchars($keyword) ?>">
                </div>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Lọc</button>
                <a href="index.php?action=admin-orders" class="btn btn-outline-secondary" title="Đặt lại">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Bảng danh sách đơn hàng -->
    <div class="card table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 120px;">Mã Đơn</th>
                        <th>Ngày Đặt</th>
                        <th>Khách Hàng / Nhận Hàng</th>
                        <th>Kênh Bán</th>
                        <th>Tổng Thanh Toán</th>
                        <th>Thanh Toán</th>
                        <th>Trạng Thái Đơn</th>
                        <th class="text-center" style="width: 150px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Chưa có đơn hàng nào phù hợp với bộ lọc!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <code class="fw-bold text-primary fs-6"><?= htmlspecialchars($o['maDH']) ?></code>
                                    <?php if (!empty($o['tenNguoiPhuTrach'])): ?>
                                        <div class="small text-muted" title="Nhân viên phụ trách">
                                            <i class="bi bi-person me-1"></i><?= htmlspecialchars($o['tenNguoiPhuTrach']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($o['ngayTao'])) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($o['ngayTao'])) ?></small>
                                </td>
                                <td>
                                    <?php $displayName = $o['tenNguoiNhan'] ?: ($o['hoVaTen'] ?: 'Khách vãng lai'); ?>
                                    <?php $displayPhone = $o['sdtNhanHang'] ?: ($o['soDienThoai'] ?: '-'); ?>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($displayName) ?></div>
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($displayPhone) ?></small>
                                </td>
                                <td>
                                    <?php if ($o['kenhBan'] === 'TAI_QUAY'): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border">
                                            <i class="bi bi-shop me-1"></i>Tại quầy
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info-subtle text-info-emphasis border">
                                            <i class="bi bi-globe me-1"></i>Online
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-danger fs-6"><?= number_format($o['tongThanhToan'], 0, ',', '.') ?> đ</strong>
                                    <?php if ($o['tienGiamGia'] > 0): ?>
                                        <div class="small text-success">Giảm: -<?= number_format($o['tienGiamGia'], 0, ',', '.') ?> đ</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="small text-dark fw-semibold"><?= htmlspecialchars($o['phuongThucThanhToan']) ?></span>
                                </td>
                                <td>
                                    <?php if ($o['trangThaiDon'] === 'CHO_DUYET'): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis">
                                            <i class="bi bi-clock me-1"></i>Chờ duyệt
                                        </span>
                                    <?php elseif ($o['trangThaiDon'] === 'DANG_GIAO'): ?>
                                        <span class="badge bg-primary-subtle text-primary">
                                            <i class="bi bi-truck me-1"></i>Đang giao
                                        </span>
                                    <?php elseif ($o['trangThaiDon'] === 'HOAN_THANH'): ?>
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="bi bi-check-circle me-1"></i>Hoàn thành
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger">
                                            <i class="bi bi-x-circle me-1"></i>Đã hủy
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?action=admin-order-detail&id=<?= urlencode($o['maDH']) ?>" 
                                           class="btn btn-outline-primary" title="Xem chi tiết & Gán Serial máy">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
                                        <a href="index.php?action=admin-order-invoice&id=<?= urlencode($o['maDH']) ?>" 
                                           class="btn btn-outline-secondary" title="In hóa đơn">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng: <?= $totalItems ?> đơn hàng)
                </small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=admin-orders&channel=<?= urlencode($channel) ?>&status=<?= urlencode($status) ?>&keyword=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>">Trước</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?action=admin-orders&channel=<?= urlencode($channel) ?>&status=<?= urlencode($status) ?>&keyword=<?= urlencode($keyword) ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=admin-orders&channel=<?= urlencode($channel) ?>&status=<?= urlencode($status) ?>&keyword=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>">Sau</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
