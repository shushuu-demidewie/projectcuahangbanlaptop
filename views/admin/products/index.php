<?php
/**
 * File: views/admin/products/index.php
 * Giao diện Quản lý Danh sách Sản phẩm (Laptop)
 */

$currentUser = AuthMiddleware::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - EmShu Store Admin</title>
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
        /* Sidebar */
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
        /* Main Content */
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
$activeMenu = 'products';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT -->
<main class="main-content">
    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-laptop-fill fs-3 text-primary"></i>
            <div>
                <h5 class="mb-0 fw-bold">Danh Sách Laptop</h5>
                <small class="text-muted">Tổng cộng <?= number_format($totalItems) ?> sản phẩm trong hệ thống</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <a href="index.php?action=admin-product-create" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Thêm Mới Laptop</span>
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

    <!-- Bộ lọc & Tìm kiếm -->
    <div class="filter-card">
        <form method="GET" action="index.php" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="admin-products">

            <!-- Từ khóa -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Tìm kiếm tên, mã hoặc hãng:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control" placeholder="Nhập tên SP, mã SP hoặc hãng laptop..." 
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                </div>
            </div>

            <!-- Danh mục -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Danh mục:</label>
                <select name="maDM" class="form-select">
                    <option value="">-- Tất cả danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['maDM'] ?>" <?= (isset($_GET['maDM']) && (string)$_GET['maDM'] === (string)$cat['maDM']) ? 'selected' : (($maDM ?? null) == $cat['maDM'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($cat['tenDM']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Thương hiệu -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Thương hiệu / Hãng:</label>
                <select name="maTH" class="form-select">
                    <option value="">-- Tất cả thương hiệu --</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?= htmlspecialchars($brand['maTH']) ?>" <?= (isset($_GET['maTH']) && $_GET['maTH'] === $brand['maTH']) ? 'selected' : (($maTH ?? null) === $brand['maTH'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($brand['tenTH']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nút tìm kiếm & Reset -->
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Lọc</button>
                <a href="index.php?action=admin-products" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Bảng danh sách sản phẩm -->
    <div class="card table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 65px;" class="text-center">Ảnh</th>
                        <th style="width: 100px;">Mã SP</th>
                        <th>Tên Laptop & Hãng</th>
                        <th>Danh Mục</th>
                        <th>Giá Bán</th>
                        <th>Tồn Kho</th>
                        <th>Bảo Hành</th>
                        <th>Trạng Thái</th>
                        <th class="text-center" style="width: 130px;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Không tìm thấy sản phẩm laptop nào phù hợp với bộ lọc!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td class="text-center">
                                    <?php if (!empty($p['hinhAnh']) && file_exists(__DIR__ . '/../../../' . $p['hinhAnh'])): ?>
                                        <img src="<?= htmlspecialchars($p['hinhAnh']) ?>" alt="" class="rounded border p-1 shadow-sm" style="width: 50px; height: 50px; object-fit: contain; background: #fff;">
                                    <?php else: ?>
                                        <div class="rounded border d-inline-flex align-items-center justify-content-center bg-light text-muted" style="width: 50px; height: 50px;">
                                            <i class="bi bi-laptop fs-4"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code class="fw-bold text-primary"><?= htmlspecialchars($p['maSP']) ?></code>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($p['tenSP']) ?></div>
                                    <small class="text-muted">
                                        <i class="bi bi-tag me-1"></i>Hãng: <strong><?= htmlspecialchars($p['tenTH'] ?? $p['maTH']) ?></strong>
                                        <?php if (!empty($p['viTriKho'])): ?>
                                            | Vị trí: <?= htmlspecialchars($p['viTriKho']) ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['tenDM'] ?? 'Chưa phân loại') ?></span>
                                </td>
                                <td>
                                    <strong class="text-danger"><?= number_format($p['giaBan'], 0, ',', '.') ?> đ</strong>
                                </td>
                                <td>
                                    <?php if ($p['soLuongTon'] > 5): ?>
                                        <span class="badge bg-success-subtle text-success"><?= $p['soLuongTon'] ?> máy</span>
                                    <?php elseif ($p['soLuongTon'] > 0): ?>
                                        <span class="badge bg-warning-subtle text-warning"><?= $p['soLuongTon'] ?> máy</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger">Hết hàng</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="small text-secondary"><?= $p['thoiGianBaoHanh'] ?> tháng</span>
                                </td>
                                <td>
                                    <?php if ($p['trangThai'] == 1): ?>
                                        <span class="badge bg-success">Đang kinh doanh</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ngừng bán</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?action=admin-product-edit&id=<?= urlencode($p['maSP']) ?>" 
                                           class="btn btn-outline-primary" title="Sửa thông tin">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="index.php?action=admin-product-delete&id=<?= urlencode($p['maSP']) ?>" 
                                           class="btn btn-outline-danger" title="Xóa vào thùng rác"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm [<?= htmlspecialchars(addslashes($p['tenSP'])) ?>] vào thùng rác không?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang (Pagination) -->
        <?php if ($totalPages > 1): ?>
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng: <?= $totalItems ?> sản phẩm)
                </small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Nút Trước -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=admin-products&keyword=<?= urlencode($keyword) ?>&maDM=<?= urlencode((string)$maDM) ?>&maTH=<?= urlencode((string)$maTH) ?>&page=<?= $page - 1 ?>">
                                Trước
                            </a>
                        </li>

                        <!-- Các trang số -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?action=admin-products&keyword=<?= urlencode($keyword) ?>&maDM=<?= urlencode((string)$maDM) ?>&maTH=<?= urlencode((string)$maTH) ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Nút Sau -->
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=admin-products&keyword=<?= urlencode($keyword) ?>&maDM=<?= urlencode((string)$maDM) ?>&maTH=<?= urlencode((string)$maTH) ?>&page=<?= $page + 1 ?>">
                                Sau
                            </a>
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
