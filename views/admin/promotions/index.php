<?php
/**
 * File: views/admin/promotions/index.php
 * Màn hình Quản Lý Khuyến Mãi & Mã Giảm Giá - EmShu Store
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
    <title>Quản Lý Khuyến Mãi & Mã Giảm Giá - EmShu Store</title>
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

        .stat-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--card-border);
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            height: 100%;
            transition: transform 0.15s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
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
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.15s;
        }

        .code-pill:hover {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-discount-percent {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            font-weight: 700;
        }

        .badge-discount-cash {
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
            font-weight: 700;
        }
    </style>
</head>
<body>

<!-- SIDEBAR ĐỒNG BỘ 100% -->
<?php 
$activeMenu = 'promotions';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT TIÊU CHUẨN (MARGIN-LEFT = 260PX) -->
<main class="main-content">

    <!-- Top Header Bar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-2 bg-danger-subtle text-danger fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-ticket-perforated-fill"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">Quản Lý Khuyến Mãi & Mã Giảm Giá</h5>
                <small class="text-muted">Cấu hình voucher giảm giá, chiết khấu phần trăm, tiền mặt và gán khuyến mãi theo danh mục sản phẩm</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="index.php?action=admin-promotion-assign-category" class="btn btn-outline-primary fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3"></i>
                <span>Gán Cho Danh Mục</span>
            </a>
            <a href="index.php?action=admin-promotion-create" class="btn btn-primary fw-bold d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Thêm Mã Khuyến Mãi</span>
            </a>
        </div>
    </header>

    <!-- Thông báo Flash Messages -->
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div><?= htmlspecialchars($flashSuccess) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div><?= htmlspecialchars($flashError) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- 4 Thẻ Thống Kê Nhanh -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Tổng số chương trình</div>
                        <h3 class="fw-bold mb-0 text-dark mt-1"><?= $stats['total'] ?></h3>
                    </div>
                    <div class="rounded-circle p-2 bg-primary-subtle text-primary">
                        <i class="bi bi-tags-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Đang diễn ra</div>
                        <h3 class="fw-bold mb-0 text-success mt-1"><?= $stats['dangDienRa'] ?></h3>
                    </div>
                    <div class="rounded-circle p-2 bg-success-subtle text-success">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Sắp diễn ra</div>
                        <h3 class="fw-bold mb-0 text-info mt-1"><?= $stats['chuaBatDau'] ?></h3>
                    </div>
                    <div class="rounded-circle p-2 bg-info-subtle text-info">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Tạm dừng / Hết hạn</div>
                        <h3 class="fw-bold mb-0 text-secondary mt-1"><?= ($stats['tamDung'] + $stats['hetHan']) ?></h3>
                    </div>
                    <div class="rounded-circle p-2 bg-secondary-subtle text-secondary">
                        <i class="bi bi-pause-circle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thanh tìm kiếm & bộ lọc -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="index.php" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="action" value="admin-promotions">

                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="keyword" class="form-control border-start-0 bg-light" 
                               placeholder="Tìm theo Mã voucher hoặc Tên chương trình..." 
                               value="<?= htmlspecialchars($keyword ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select bg-light">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="1" <?= (isset($statusParam) && $statusParam === 1) ? 'selected' : '' ?>>Kích hoạt (Hoạt động)</option>
                        <option value="0" <?= (isset($statusParam) && $statusParam === 0) ? 'selected' : '' ?>>Tạm dừng hoạt động</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="bi bi-funnel me-1"></i> Lọc Dữ Liệu
                    </button>
                    <a href="index.php?action=admin-promotions" class="btn btn-outline-secondary" title="Đặt lại">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng danh sách Khuyến mãi -->
    <div class="table-custom-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 170px;">Mã Khuyến Mãi</th>
                        <th>Tên Chương Trình</th>
                        <th style="width: 140px;">Loại & Mức Giảm</th>
                        <th class="text-end" style="width: 130px;">Đơn Tối Thiểu</th>
                        <th class="text-end" style="width: 130px;">Giảm Tối Đa</th>
                        <th style="width: 200px;">Thời Gian Áp Dụng</th>
                        <th class="text-center" style="width: 120px;">Danh Mục</th>
                        <th class="text-center" style="width: 130px;">Trạng Thái</th>
                        <th class="text-end" style="width: 130px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($promotions)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-ticket-perforated fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Không tìm thấy chương trình khuyến mãi nào phù hợp!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($promotions as $km): ?>
                            <tr>
                                <td>
                                    <span class="code-pill" onclick="copyCode('<?= htmlspecialchars($km['maKM']) ?>')" title="Nhấp để sao chép mã">
                                        <i class="bi bi-copy me-1"></i><?= htmlspecialchars($km['maKM']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($km['tenKM']) ?></div>
                                </td>
                                <td>
                                    <?php if ($km['loaiKM'] === 'PHAN_TRAM'): ?>
                                        <span class="badge badge-discount-percent px-2 py-1">
                                            <i class="bi bi-percent me-1"></i>Giảm <?= $km['giaTriGiam'] ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-discount-cash px-2 py-1">
                                            <i class="bi bi-cash-stack me-1"></i>-<?= number_format($km['giaTriGiam'], 0, ',', '.') ?> đ
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end font-monospace small">
                                    <?= number_format($km['donHangToiThieu'], 0, ',', '.') ?> đ
                                </td>
                                <td class="text-end font-monospace small">
                                    <?php if ($km['loaiKM'] === 'PHAN_TRAM' && !empty($km['mucGiamToiDa'])): ?>
                                        <?= number_format($km['mucGiamToiDa'], 0, ',', '.') ?> đ
                                    <?php elseif ($km['loaiKM'] === 'TIEN_MAT'): ?>
                                        <span class="text-muted"><?= number_format($km['giaTriGiam'], 0, ',', '.') ?> đ</span>
                                    <?php else: ?>
                                        <span class="text-muted">Không giới hạn</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <div class="text-muted">
                                        <i class="bi bi-calendar-event text-primary me-1"></i>
                                        <?= date('d/m/Y', strtotime($km['ngayBatDau'])) ?> &rarr; <?= date('d/m/Y', strtotime($km['ngayKetThuc'])) ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.72rem;">
                                        Giờ: <?= date('H:i', strtotime($km['ngayBatDau'])) ?> - <?= date('H:i', strtotime($km['ngayKetThuc'])) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($km['soDanhMucApDung'] > 0): ?>
                                        <a href="index.php?action=admin-promotion-assign-category" class="badge bg-primary-subtle text-primary border text-decoration-none">
                                            <?= $km['soDanhMucApDung'] ?> danh mục
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Tất cả (Chung)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $km['badgeClass'] ?> px-2 py-1 small">
                                        <?= $km['tinhTrangText'] ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <!-- Nút Bật / Tắt -->
                                        <a href="index.php?action=admin-promotion-toggle&id=<?= urlencode($km['maKM']) ?>" 
                                           class="btn btn-sm <?= $km['trangThai'] === 1 ? 'btn-outline-warning' : 'btn-outline-success' ?> p-1 px-2"
                                           title="<?= $km['trangThai'] === 1 ? 'Tạm dừng mã này' : 'Kích hoạt mã này' ?>">
                                            <i class="bi <?= $km['trangThai'] === 1 ? 'bi-pause-fill' : 'bi-play-fill' ?>"></i>
                                        </a>

                                        <!-- Nút Sửa -->
                                        <a href="index.php?action=admin-promotion-edit&id=<?= urlencode($km['maKM']) ?>" 
                                           class="btn btn-sm btn-outline-primary p-1 px-2" title="Chỉnh sửa">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <!-- Nút Xóa -->
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2" 
                                                onclick="confirmDelete('<?= htmlspecialchars($km['maKM']) ?>', '<?= htmlspecialchars($km['tenKM']) ?>')"
                                                title="Xóa chương trình">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Xác Nhận Xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận xóa khuyến mãi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                Bạn có chắc chắn muốn xóa mã khuyến mãi <strong id="deletePromoName" class="text-primary font-monospace"></strong> không?
                <div class="small text-muted mt-2">
                    Lưu ý: Nếu mã này đã từng được sử dụng trong các đơn hàng đã tạo, hệ thống sẽ tự động chuyển sang trạng thái <strong>Tạm dừng</strong> để bảo toàn lịch sử hóa đơn.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger fw-bold">Đồng ý xóa</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert('Đã sao chép mã voucher: ' + code);
    }).catch(err => {
        console.error('Không thể sao chép: ', err);
    });
}

function confirmDelete(maKM, tenKM) {
    document.getElementById('deletePromoName').textContent = `${maKM} (${tenKM})`;
    document.getElementById('confirmDeleteBtn').href = 'index.php?action=admin-promotion-delete&id=' + encodeURIComponent(maKM);
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
</body>
</html>
