<?php
/**
 * File: views/admin/promotions/assign_category.php
 * Giao diện Gán Khuyến Mãi Theo Danh Mục Laptop - EmShu Store
 */

$currentUser = AuthMiddleware::getCurrentUser();
$userRole    = $currentUser['role'] ?? '';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gán Khuyến Mãi Cho Danh Mục - EmShu Store</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

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
        }

        .table-custom-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--card-border);
            box-shadow: 0 1px 4px rgba(0,0,0,0.02);
            overflow: hidden;
        }

        .table thead th {
            background: #f8fafc;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 13px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .code-pill {
            font-family: 'JetBrains Mono', monospace;
            background: #eff6ff;
            color: #2563eb;
            border: 1px dashed #bfdbfe;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.82rem;
        }
    </style>
</head>
<body>

<!-- SIDEBAR ĐỒNG BỘ -->
<?php 
$activeMenu = 'promotions';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<main class="main-content">

    <!-- Top Header Bar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <a href="index.php?action=admin-promotions" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h5 class="mb-0 fw-bold text-dark">Gán Khuyến Mãi Cho Danh Mục Laptop</h5>
                <small class="text-muted">Áp dụng voucher chiết khấu tự động cho toàn bộ máy tính thuộc từng danh mục</small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="index.php?action=admin-promotions" class="btn btn-outline-secondary fw-semibold">
                <i class="bi bi-ticket-perforated me-1"></i> Danh Sách Khuyến Mãi
            </a>
        </div>
    </header>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div><?= htmlspecialchars($flashSuccess) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div><?= htmlspecialchars($flashError) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="alert alert-info d-flex align-items-start gap-3 mb-4 rounded-3 border-0 bg-primary-subtle text-primary">
        <i class="bi bi-info-circle-fill fs-4 mt-1"></i>
        <div>
            <strong class="d-block mb-1">Cơ chế áp dụng theo Danh mục:</strong>
            Khi bạn gán một mã khuyến mãi cho một Danh mục (ví dụ: <em>Laptop Gaming</em>), khách hàng mua bất kỳ dòng máy nào thuộc danh mục này sẽ được áp dụng trực tiếp ưu đãi của mã đó. Bạn có thể thay đổi hoặc gỡ bỏ bất kỳ lúc nào.
        </div>
    </div>

    <!-- Bảng Gán Danh Mục -->
    <div class="table-custom-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;" class="text-center">Mã DM</th>
                        <th>Danh Mục Laptop</th>
                        <th class="text-center" style="width: 120px;">Số Laptop</th>
                        <th>Khuyến Mãi Đang Áp Dụng</th>
                        <th style="width: 340px;">Gán Mã Khuyến Mãi Mới</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Chưa có danh mục sản phẩm nào trong hệ thống!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $dm): ?>
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">
                                    #<?= $dm['maDM'] ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($dm['tenDM']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($dm['moTa'] ?? '') ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                        <?= (int)$dm['soLaptop'] ?> laptop
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($dm['maKM'])): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="code-pill">
                                                <?= htmlspecialchars($dm['maKM']) ?>
                                            </span>
                                            <div>
                                                <div class="fw-semibold text-dark small"><?= htmlspecialchars($dm['tenKM'] ?? '') ?></div>
                                                <small class="text-danger fw-bold">
                                                    <?php if ($dm['loaiKM'] === 'PHAN_TRAM'): ?>
                                                        -<?= $dm['giaTriGiam'] ?>% (Tối đa: <?= !empty($dm['mucGiamToiDa']) ? number_format($dm['mucGiamToiDa'], 0, ',', '.') . ' đ' : 'Không giới hạn' ?>)
                                                    <?php else: ?>
                                                        -<?= number_format($dm['giaTriGiam'], 0, ',', '.') ?> đ
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border px-2 py-1">
                                            <i class="bi bi-dash-circle me-1"></i> Chưa gán khuyến mãi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="index.php?action=admin-promotion-assign-category" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="maDM" value="<?= $dm['maDM'] ?>">
                                        
                                        <select name="maKM" class="form-select form-select-sm">
                                            <option value="">-- Gỡ bỏ / Không khuyến mãi --</option>
                                            <?php foreach ($activePromotions as $act): ?>
                                                <option value="<?= htmlspecialchars($act['maKM']) ?>" 
                                                        <?= ($dm['maKM'] === $act['maKM']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($act['maKM']) ?> - <?= htmlspecialchars($act['tenKM']) ?> (<?= $act['loaiKM'] === 'PHAN_TRAM' ? '-' . $act['giaTriGiam'] . '%' : '-' . number_format($act['giaTriGiam'], 0, ',', '.') . 'đ' ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3 text-nowrap">
                                            <i class="bi bi-check-lg me-1"></i> Lưu
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
