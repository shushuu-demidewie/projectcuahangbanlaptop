<?php
/**
 * File: views/admin/serials/index.php
 * Giao diện Tra cứu & Quản lý danh sách Serial / IMEI từng chiếc laptop
 */

$currentUser = AuthMiddleware::getCurrentUser();
$statusCounts = $statusCounts ?? ['total' => 0, 'Trong kho' => 0, 'Đã bán' => 0, 'Đang bảo hành' => 0, 'Lỗi' => 0];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra Cứu & Quản Lý Serial / IMEI - EmShu Store Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #2563eb;
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
            background: var(--primary);
            color: #ffffff;
            font-weight: 600;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 28px 36px;
        }

        /* Top Header Card */
        .top-navbar-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px 24px;
            border: 1px solid var(--card-border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .page-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .page-icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #eff6ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            border: 1px solid #bfdbfe;
        }

        /* Stats Cards Row */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 14px 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .stat-val {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1.2;
            font-family: 'JetBrains Mono', monospace;
        }

        .stat-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Filter Card */
        .filter-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            padding: 20px;
            margin-bottom: 24px;
        }

        .status-filter-pills {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px dashed var(--card-border);
            overflow-x: auto;
        }

        .status-pill-btn {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            text-decoration: none;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
        }

        .status-pill-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .status-pill-btn.active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }

        /* Table Card */
        .table-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .table thead th {
            background: #f8fafc;
            font-size: 0.74rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Serial Code Chip */
        .serial-chip {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.88rem;
            font-weight: 700;
            color: #0f172a;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .copy-serial-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            font-size: 0.85rem;
            line-height: 1;
            transition: color 0.15s;
        }

        .copy-serial-btn:hover {
            color: var(--primary);
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .dot-indicator {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        .status-badge.in-stock {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .status-badge.in-stock .dot-indicator {
            background: #10b981;
        }

        .status-badge.sold {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .status-badge.sold .dot-indicator {
            background: #3b82f6;
        }

        .status-badge.warranty {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }
        .status-badge.warranty .dot-indicator {
            background: #f59e0b;
        }

        .status-badge.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .status-badge.error .dot-indicator {
            background: #ef4444;
        }
    </style>
</head>
<body>

<!-- SIDEBAR CHUẨN DÙNG CHUNG -->
<?php 
$activeMenu = 'serials';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT -->
<main class="main-content">
    
    <!-- Top Navbar Card -->
    <header class="top-navbar-card">
        <div class="page-title-wrap">
            <div class="page-icon-badge">
                <i class="bi bi-upc-scan"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Tra Cứu & Quản Lý Serial / IMEI</h4>
                <small class="text-muted">Theo dõi định danh từng chiếc laptop: tình trạng tồn kho, bảo hành, đã bán</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="index.php?action=admin-pos" class="btn btn-outline-success fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-calculator"></i>
                <span>Bán Hàng POS</span>
            </a>
            <a href="index.php?action=admin-import-create" class="btn btn-primary fw-bold d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Nhập Thêm Máy Mới</span>
            </a>
        </div>
    </header>

    <!-- Overview Stats Counters -->
    <div class="stats-grid">
        <a href="index.php?action=admin-serials" class="stat-card">
            <div>
                <div class="stat-label">Tổng Mã Máy</div>
                <div class="stat-val text-dark"><?= number_format($statusCounts['total'] ?? 0) ?></div>
            </div>
            <i class="bi bi-boxes fs-2 text-secondary opacity-50"></i>
        </a>

        <a href="index.php?action=admin-serials&trangThai=Trong+kho" class="stat-card">
            <div>
                <div class="stat-label">Trong Kho</div>
                <div class="stat-val text-success"><?= number_format($statusCounts['Trong kho'] ?? 0) ?></div>
            </div>
            <i class="bi bi-check-circle-fill fs-2 text-success opacity-75"></i>
        </a>

        <a href="index.php?action=admin-serials&trangThai=Đã+bán" class="stat-card">
            <div>
                <div class="stat-label">Đã Bán Ra</div>
                <div class="stat-val text-primary"><?= number_format($statusCounts['Đã bán'] ?? 0) ?></div>
            </div>
            <i class="bi bi-bag-check-fill fs-2 text-primary opacity-75"></i>
        </a>

        <a href="index.php?action=admin-serials&trangThai=Đang+bảo+hành" class="stat-card">
            <div>
                <div class="stat-label">Đang Bảo Hành</div>
                <div class="stat-val text-warning"><?= number_format($statusCounts['Đang bảo hành'] ?? 0) ?></div>
            </div>
            <i class="bi bi-wrench-adjustable-circle-fill fs-2 text-warning opacity-75"></i>
        </a>

        <a href="index.php?action=admin-serials&trangThai=Lỗi" class="stat-card">
            <div>
                <div class="stat-label">Lỗi Kỹ Thuật</div>
                <div class="stat-val text-danger"><?= number_format($statusCounts['Lỗi'] ?? 0) ?></div>
            </div>
            <i class="bi bi-exclamation-triangle-fill fs-2 text-danger opacity-75"></i>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <!-- Quick Status Pills -->
        <div class="status-filter-pills">
            <span class="small fw-bold text-muted me-2 align-self-center">Lọc nhanh:</span>
            <a href="index.php?action=admin-serials&keyword=<?= urlencode($keyword) ?>&maSP=<?= urlencode($maSP) ?>" 
               class="status-pill-btn <?= empty($trangThai) ? 'active' : '' ?>">
                Tất cả (<?= $statusCounts['total'] ?? 0 ?>)
            </a>
            <a href="index.php?action=admin-serials&keyword=<?= urlencode($keyword) ?>&maSP=<?= urlencode($maSP) ?>&trangThai=Trong+kho" 
               class="status-pill-btn <?= ($trangThai === 'Trong kho') ? 'active' : '' ?>">
                <span class="dot-indicator" style="background:#10b981;"></span> Trong kho (<?= $statusCounts['Trong kho'] ?? 0 ?>)
            </a>
            <a href="index.php?action=admin-serials&keyword=<?= urlencode($keyword) ?>&maSP=<?= urlencode($maSP) ?>&trangThai=Đã+bán" 
               class="status-pill-btn <?= ($trangThai === 'Đã bán') ? 'active' : '' ?>">
                <span class="dot-indicator" style="background:#3b82f6;"></span> Đã bán (<?= $statusCounts['Đã bán'] ?? 0 ?>)
            </a>
            <a href="index.php?action=admin-serials&keyword=<?= urlencode($keyword) ?>&maSP=<?= urlencode($maSP) ?>&trangThai=Đang+bảo+hành" 
               class="status-pill-btn <?= ($trangThai === 'Đang bảo hành') ? 'active' : '' ?>">
                <span class="dot-indicator" style="background:#f59e0b;"></span> Đang bảo hành (<?= $statusCounts['Đang bảo hành'] ?? 0 ?>)
            </a>
            <a href="index.php?action=admin-serials&keyword=<?= urlencode($keyword) ?>&maSP=<?= urlencode($maSP) ?>&trangThai=Lỗi" 
               class="status-pill-btn <?= ($trangThai === 'Lỗi') ? 'active' : '' ?>">
                <span class="dot-indicator" style="background:#ef4444;"></span> Lỗi (<?= $statusCounts['Lỗi'] ?? 0 ?>)
            </a>
        </div>

        <form action="index.php" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="admin-serials">

            <div class="col-md-5">
                <label class="form-label small fw-semibold text-muted mb-1">Mã Serial / IMEI hoặc Mã phiếu nhập:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0" 
                           placeholder="Nhập chính xác hoặc 1 phần mã Serial..." 
                           value="<?= htmlspecialchars($keyword) ?>">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Dòng Laptop:</label>
                <select name="maSP" class="form-select">
                    <option value="">-- Tất cả sản phẩm laptop --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= htmlspecialchars($p['maSP']) ?>" <?= ($maSP === $p['maSP']) ? 'selected' : '' ?>>
                            [<?= htmlspecialchars($p['maSP']) ?>] <?= htmlspecialchars($p['tenSP']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary fw-bold px-4 flex-grow-1">
                    <i class="bi bi-funnel me-1"></i> Tra cứu
                </button>
                <a href="index.php?action=admin-serials" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 230px;">Mã Serial / IMEI</th>
                        <th>Dòng Laptop</th>
                        <th>Phiếu Nhập Gốc</th>
                        <th>Ngày Nhập Kho</th>
                        <th>Ngày Xuất Bán</th>
                        <th>Trạng Thái</th>
                        <th>Ghi Chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($serials)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <div class="fw-bold">Không tìm thấy số Serial / IMEI nào phù hợp!</div>
                                <div class="small">Vui lòng thay đổi từ khóa hoặc bộ lọc để tra cứu lại.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($serials as $s): ?>
                            <tr>
                                <td>
                                    <div class="serial-chip">
                                        <i class="bi bi-upc-scan text-secondary small"></i>
                                        <span><?= htmlspecialchars($s['maSerial_IMEI']) ?></span>
                                        <button type="button" class="copy-serial-btn" 
                                                onclick="copyToClipboard('<?= htmlspecialchars($s['maSerial_IMEI']) ?>', this)" 
                                                title="Sao chép mã Serial">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['tenSP'] ?? $s['maSP']) ?></div>
                                    <small class="text-muted">Mã máy: <span class="font-monospace text-primary fw-semibold"><?= htmlspecialchars($s['maSP']) ?></span></small>
                                </td>
                                <td>
                                    <?php if (!empty($s['maPhieu'])): ?>
                                        <a href="index.php?action=admin-import-detail&id=<?= urlencode($s['maPhieu']) ?>" 
                                           class="fw-semibold text-primary text-decoration-none">
                                            <i class="bi bi-receipt me-1"></i><?= htmlspecialchars($s['maPhieu']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($s['ngayNhap'])): ?>
                                        <div class="small fw-bold text-dark">
                                            <i class="bi bi-calendar3 me-1 text-muted"></i><?= date('d/m/Y', strtotime($s['ngayNhap'])) ?>
                                        </div>
                                        <small class="text-muted"><?= date('H:i', strtotime($s['ngayNhap'])) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($s['ngayXuatKho'])): ?>
                                        <div class="small fw-bold text-danger">
                                            <i class="bi bi-calendar-check me-1"></i><?= date('d/m/Y', strtotime($s['ngayXuatKho'])) ?>
                                        </div>
                                        <small class="text-muted"><?= date('H:i', strtotime($s['ngayXuatKho'])) ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Chưa xuất</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($s['trangThai'] === 'Trong kho'): ?>
                                        <span class="status-badge in-stock">
                                            <span class="dot-indicator"></span> Trong kho
                                        </span>
                                    <?php elseif ($s['trangThai'] === 'Đã bán'): ?>
                                        <span class="status-badge sold">
                                            <span class="dot-indicator"></span> Đã bán
                                        </span>
                                    <?php elseif ($s['trangThai'] === 'Đang bảo hành'): ?>
                                        <span class="status-badge warranty">
                                            <span class="dot-indicator"></span> Đang bảo hành
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge error">
                                            <span class="dot-indicator"></span> <?= htmlspecialchars($s['trangThai']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-secondary"><?= htmlspecialchars($s['ghiChu'] ?: '-') ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">
                    Trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng số: <strong><?= number_format($totalItems) ?></strong> mã máy)
                </small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=admin-serials&keyword=<?= urlencode($keyword) ?>&maSP=<?= urlencode($maSP) ?>&trangThai=<?= urlencode($trangThai) ?>&page=<?= $page - 1 ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?action=admin-serials&keyword=<?= urlencode($keyword) ?>&maSP=<?= urlencode($maSP) ?>&trangThai=<?= urlencode($trangThai) ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=admin-serials&keyword=<?= urlencode($keyword) ?>&maSP=<?= urlencode($maSP) ?>&trangThai=<?= urlencode($trangThai) ?>&page=<?= $page + 1 ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
        setTimeout(() => {
            btn.innerHTML = originalIcon;
        }, 1500);
    }).catch(err => {
        console.error('Không thể sao chép: ', err);
    });
}
</script>
</body>
</html>
