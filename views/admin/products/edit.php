<?php
/**
 * File: views/admin/products/edit.php
 * Giao diện Form Chỉnh sửa thông tin Laptop
 */

$currentUser = AuthMiddleware::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Laptop - <?= htmlspecialchars($product['maSP']) ?></title>
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
        .form-card {
            background: #ffffff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            padding: 32px;
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Chỉnh Sửa Laptop: <span class="text-primary"><?= htmlspecialchars($product['maSP']) ?></span></h4>
            <p class="text-muted small mb-0">Cập nhật thông số kỹ thuật, giá bán và trạng thái kinh doanh của sản phẩm</p>
        </div>
        <a href="index.php?action=admin-products" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại Danh sách
        </a>
    </div>

    <!-- Error Alert -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($error) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form action="index.php?action=admin-product-update&id=<?= urlencode($product['maSP']) ?>" method="POST" autocomplete="off">
            <!-- PHẦN 1: THÔNG TIN CƠ BẢN -->
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-info-circle me-1"></i> 1. Thông Tin Nhận Diện
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Mã Sản Phẩm (Khóa chính)</label>
                    <input type="text" class="form-control bg-light fw-bold text-primary" 
                           value="<?= htmlspecialchars($product['maSP']) ?>" readonly disabled>
                    <small class="text-muted">Mã định danh không thể thay đổi</small>
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-semibold">Tên Laptop <span class="text-danger">*</span></label>
                    <input type="text" name="tenSP" class="form-control" 
                           value="<?= htmlspecialchars($product['tenSP']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Thương Hiệu / Hãng <span class="text-danger">*</span></label>
                    <select name="maTH" class="form-select" required>
                        <option value="">-- Chọn thương hiệu --</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= htmlspecialchars($b['maTH']) ?>" <?= ($product['maTH'] === $b['maTH']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['tenTH']) ?> (<?= htmlspecialchars($b['xuatXu'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Danh Mục <span class="text-danger">*</span></label>
                    <select name="maDM" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['maDM'] ?>" <?= ($product['maDM'] == $c['maDM']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['tenDM']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- PHẦN 2: GIÁ & KHO VẬN -->
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-currency-dollar me-1"></i> 2. Giá Bán, Kho & Bảo Hành
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Giá Bán (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" name="giaBan" class="form-control" min="1000" step="1000" 
                           value="<?= htmlspecialchars((string)$product['giaBan']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Số Lượng Tồn Kho</label>
                    <input type="number" name="soLuongTon" class="form-control" min="0" 
                           value="<?= htmlspecialchars((string)$product['soLuongTon']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Bảo Hành (Tháng)</label>
                    <input type="number" name="thoiGianBaoHanh" class="form-control" min="0" 
                           value="<?= htmlspecialchars((string)$product['thoiGianBaoHanh']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Vị Trí Lưu Kho</label>
                    <input type="text" name="viTriKho" class="form-control" 
                           value="<?= htmlspecialchars($product['viTriKho'] ?? 'KHO_TONG') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Trạng Thái Kinh Doanh</label>
                    <select name="trangThai" class="form-select">
                        <option value="1" <?= ($product['trangThai'] == 1) ? 'selected' : '' ?>>Đang kinh doanh (Hiển thị bán)</option>
                        <option value="0" <?= ($product['trangThai'] == 0) ? 'selected' : '' ?>>Ngừng kinh doanh (Tạm ẩn)</option>
                    </select>
                </div>
            </div>

            <!-- PHẦN 3: CẤU HÌNH & MÔ TẢ CHI TIẾT -->
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-cpu me-1"></i> 3. Cấu Hình Kỹ Thuật & Mô Tả
            </h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Thông Số Kỹ Thuật</label>
                    <textarea name="thongSoKyThuat" class="form-control" rows="5"><?= htmlspecialchars($product['thongSoKyThuat'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mô Tả Chi Tiết / Điểm Nổi Bật</label>
                    <textarea name="moTa" class="form-control" rows="5"><?= htmlspecialchars($product['moTa'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- BUTTONS -->
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="index.php?action=admin-products" class="btn btn-secondary px-4">Hủy Bỏ</a>
                <button type="submit" class="btn btn-primary px-5 fw-semibold">
                    <i class="bi bi-save me-1"></i> Cập Nhật Thay Đổi
                </button>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
