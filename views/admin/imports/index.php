<?php
/**
 * File: views/admin/imports/index.php
 * Giao diện Quản lý Danh sách Phiếu Nhập Kho
 */

$currentUser = AuthMiddleware::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Nhập Kho - EmShu Store Admin</title>
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
        .table-card {
            background: #ffffff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .filter-card {
            background: #ffffff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            padding: 20px;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<?php 
$activeMenu = 'imports';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT -->
<main class="main-content">
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-box-arrow-in-down fs-3 text-primary"></i>
            <div>
                <h5 class="mb-0 fw-bold">Danh Sách Phiếu Nhập Kho</h5>
                <small class="text-muted">Quản lý lô hàng nhập từ nhà cung cấp & kiểm kê Serial/IMEI</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="index.php?action=admin-serials" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-upc-scan"></i> Tra cứu Serial
            </a>
            <a href="index.php?action=admin-import-create" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Tạo Phiếu Nhập Mới</span>
            </a>
        </div>
    </header>

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

    <!-- Filter Card -->
    <div class="filter-card">
        <form action="index.php" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="admin-imports">
            <div class="col-md-9">
                <label class="form-label small fw-semibold text-muted">Tìm kiếm phiếu nhập:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control" 
                           placeholder="Nhập mã phiếu, tên nhà cung cấp hoặc số hóa đơn NCC..." 
                           value="<?= htmlspecialchars($keyword) ?>">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Tìm kiếm</button>
                <a href="index.php?action=admin-imports" class="btn btn-outline-secondary" title="Đặt lại">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 140px;">Mã Phiếu</th>
                        <th>Ngày Nhập</th>
                        <th>Nhà Cung Cấp</th>
                        <th>Người Nhập</th>
                        <th>Tổng Tiền Nhập</th>
                        <th>Thanh Toán</th>
                        <th>Trạng Thái Kho</th>
                        <th class="text-center" style="width: 120px;">Chi Tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($receipts)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Chưa có phiếu nhập kho nào phù hợp với tìm kiếm!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($receipts as $r): ?>
                            <tr>
                                <td>
                                    <code class="fw-bold text-primary fs-6"><?= htmlspecialchars($r['maPhieu']) ?></code>
                                    <?php if (!empty($r['soHoaDonNCC'])): ?>
                                        <div class="small text-muted">HĐ: <?= htmlspecialchars($r['soHoaDonNCC']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($r['ngayNhap'])) ?></div>
                                    <small class="text-muted"><?= date('H:i', strtotime($r['ngayNhap'])) ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($r['tenCongTy'] ?? $r['maNCC']) ?></div>
                                    <small class="text-secondary">Mã NCC: <?= htmlspecialchars($r['maNCC']) ?></small>
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold"><?= htmlspecialchars($r['tenNguoiNhap'] ?? $r['nguoiNhap']) ?></span>
                                    <div class="small text-muted"><?= htmlspecialchars($r['nguoiNhap']) ?></div>
                                </td>
                                <td>
                                    <strong class="text-danger fs-6"><?= number_format($r['tongTienNhap'], 0, ',', '.') ?> đ</strong>
                                    <?php if ($r['tongTienTraTruocNCC'] > 0 && $r['tongTienTraTruocNCC'] < $r['tongTienNhap']): ?>
                                        <div class="small text-warning">Đã cọc: <?= number_format($r['tongTienTraTruocNCC'], 0, ',', '.') ?> đ</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['trangThaiThanhToan'] === 'DA_THANH_TOAN'): ?>
                                        <span class="badge bg-success-subtle text-success">Đã thanh toán</span>
                                    <?php elseif ($r['trangThaiThanhToan'] === 'CON_NO'): ?>
                                        <span class="badge bg-warning-subtle text-warning">Còn công nợ</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger">Chưa thanh toán</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        <i class="bi bi-box-seam me-1"></i><?= htmlspecialchars($r['trangThaiNhapKho']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?action=admin-import-detail&id=<?= urlencode($r['maPhieu']) ?>" 
                                       class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-eye"></i> Xem
                                    </a>
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
                    Trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng: <?= $totalItems ?> phiếu)
                </small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=admin-imports&keyword=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>">Trước</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?action=admin-imports&keyword=<?= urlencode($keyword) ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=admin-imports&keyword=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>">Sau</a>
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
