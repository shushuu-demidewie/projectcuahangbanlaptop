<?php
/**
 * File: views/admin/warranty/index.php
 * Màn hình Quản lý Tiếp nhận & Xử lý Bảo hành (Dành cho Kỹ thuật viên & Admin)
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bảo Hành - EmShu Store System</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #2563eb;
            --bg-body: #f8fafc;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: #1e293b;
        }
        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #0f172a;
            color: #94a3b8;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-brand {
            padding: 24px;
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-nav {
            list-style: none;
            padding: 16px 12px;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #94a3b8;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
            margin-bottom: 4px;
        }
        .sidebar-nav .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255,255,255,0.06);
        }
        .sidebar-nav .nav-link.active {
            color: #ffffff;
            background-color: var(--primary-color);
            font-weight: 600;
        }
        /* Main Layout */
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
        .card-custom {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .serial-badge {
            font-family: monospace;
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 700;
        }
        .prod-thumb-mini {
            width: 44px;
            height: 44px;
            object-fit: contain;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 2px;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<?php 
$activeMenu = 'warranty';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT -->
<main class="main-content">
    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-check fs-3 text-primary"></i>
            <div>
                <h5 class="mb-0 fw-bold">Quản Lý & Tiếp Nhận Bảo Hành</h5>
                <small class="text-muted">Theo dõi tiến độ sửa chữa, bảo dưỡng linh kiện theo số Serial/IMEI máy</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="index.php?action=check-warranty" target="_blank" class="btn btn-outline-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-search"></i>
                <span>Xem Trang Tra Cứu Khách</span>
            </a>
        </div>
    </header>

    <!-- THÔNG BÁO FLASH NẾU CÓ -->
    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php 
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        ?>
    <?php endif; ?>

    <!-- THANH BỘ LỌC TÌM KIẾM -->
    <div class="card-custom p-3 mb-4">
        <form method="GET" action="index.php" class="row g-2 align-items-center">
            <input type="hidden" name="action" value="admin-warranty">

            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control" placeholder="Tìm theo Mã Phiếu, Serial/IMEI, Tên khách, SĐT..." value="<?= htmlspecialchars($keyword ?? '') ?>">
                </div>
            </div>

            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Đang hiệu lực" <?= ($status === 'Đang hiệu lực') ? 'selected' : '' ?>>Đang hiệu lực (Chưa lỗi)</option>
                    <option value="Đang tiếp nhận bảo hành" <?= ($status === 'Đang tiếp nhận bảo hành') ? 'selected' : '' ?>>Đang tiếp nhận bảo hành</option>
                    <option value="Đang sửa chữa" <?= ($status === 'Đang sửa chữa') ? 'selected' : '' ?>>Đang sửa chữa</option>
                    <option value="Đã sửa xong" <?= ($status === 'Đã sửa xong') ? 'selected' : '' ?>>Đã sửa xong (Chờ khách lấy)</option>
                    <option value="Đã trả khách" <?= ($status === 'Đã trả khách') ? 'selected' : '' ?>>Đã trả khách hoàn tất</option>
                    <option value="Hết hạn" <?= ($status === 'Hết hạn') ? 'selected' : '' ?>>Hết hạn bảo hành</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i>Lọc Dữ Liệu
                </button>
                <a href="index.php?action=admin-warranty" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH PHIẾU BẢO HÀNH -->
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 130px;">Mã Phiếu</th>
                        <th style="width: 170px;">Mã Serial/IMEI</th>
                        <th>Sản Phẩm Laptop</th>
                        <th>Khách Hàng & SĐT</th>
                        <th style="width: 180px;">Thời Hạn BH</th>
                        <th style="width: 150px;">Trạng Thái Phiếu</th>
                        <th style="width: 120px;" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-x fs-1 d-block mb-2 text-secondary"></i>
                                Không tìm thấy phiếu bảo hành nào phù hợp với bộ lọc!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-primary">#<?= htmlspecialchars($t['maPhieuBH']) ?></span>
                                </td>
                                <td>
                                    <span class="serial-badge">
                                        <?= htmlspecialchars($t['maSerial_IMEI']) ?>
                                    </span>
                                    <div class="small text-muted mt-1">
                                        Máy: <span class="badge bg-light text-dark border"><?= htmlspecialchars($t['trangThaiSerial']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($t['hinhAnh']) && file_exists(__DIR__ . '/../../../' . $t['hinhAnh'])): ?>
                                            <img src="<?= htmlspecialchars($t['hinhAnh']) ?>" class="prod-thumb-mini" alt="">
                                        <?php else: ?>
                                            <div class="prod-thumb-mini d-flex align-items-center justify-content-center bg-light text-muted">
                                                <i class="bi bi-laptop"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($t['tenSP']) ?>">
                                                <?= htmlspecialchars($t['tenSP']) ?>
                                            </div>
                                            <small class="text-muted">Mã: <code><?= htmlspecialchars($t['maSP']) ?></code> | Hãng: <?= htmlspecialchars($t['tenTH'] ?? '') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($t['hoVaTen'] ?: 'Khách vãng lai') ?></div>
                                    <div class="small text-primary font-monospace">
                                        <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($t['soDienThoai'] ?: 'Chưa có SĐT') ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-muted">Bắt đầu: <?= date('d/m/Y', strtotime($t['ngayBatDau'])) ?></div>
                                    <div class="small fw-bold <?= ($t['soNgayConLai'] > 0) ? 'text-dark' : 'text-danger' ?>">
                                        Hết hạn: <?= date('d/m/Y', strtotime($t['ngayKetThuc'])) ?>
                                    </div>
                                    <?php if ($t['soNgayConLai'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success small">Còn <?= $t['soNgayConLai'] ?> ngày</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger small">Đã hết hạn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = match($t['trangThaiPhieu']) {
                                        'Đang hiệu lực' => 'bg-success',
                                        'Đang tiếp nhận bảo hành' => 'bg-warning text-dark',
                                        'Đang sửa chữa' => 'bg-info text-dark',
                                        'Đã sửa xong' => 'bg-primary',
                                        'Đã trả khách' => 'bg-secondary',
                                        default => 'bg-danger'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?> mb-1">
                                        <?= htmlspecialchars($t['trangThaiPhieu']) ?>
                                    </span>
                                    <?php if (!empty($t['ghiChuBaoHanh'])): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 140px;" title="<?= htmlspecialchars($t['ghiChuBaoHanh']) ?>">
                                            <i class="bi bi-chat-left-dots me-1"></i><?= htmlspecialchars($t['ghiChuBaoHanh']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary fw-semibold"
                                            onclick='openUpdateModal(<?= json_encode($t, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-pencil-square me-1"></i>Xử Lý
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- MODAL CẬP NHẬT TRẠNG THÁI TIẾN ĐỘ BẢO HÀNH -->
<div class="modal fade" id="updateWarrantyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="index.php?action=admin-warranty-update" method="POST">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="bi bi-tools me-2"></i>Cập Nhật Tiến Độ Bảo Hành
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="row g-2 small">
                            <div class="col-6">Mã Phiếu: <strong class="text-dark font-monospace" id="modalMaPhieu"></strong></div>
                            <div class="col-6">Serial: <strong class="text-primary font-monospace" id="modalSerial"></strong></div>
                            <div class="col-12">Khách Hàng: <strong class="text-dark" id="modalKhachHang"></strong> (<span id="modalSdt"></span>)</div>
                            <div class="col-12">Máy: <strong class="text-dark" id="modalTenSP"></strong></div>
                        </div>
                    </div>

                    <input type="hidden" name="maPhieuBH" id="formMaPhieu">

                    <!-- Trạng thái phiếu bảo hành -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Trạng Thái Xử Lý Phiếu:</label>
                        <select name="trangThaiPhieu" id="formTrangThaiPhieu" class="form-select" required>
                            <option value="Đang hiệu lực">Đang hiệu lực (Chưa lỗi / Máy bình thường)</option>
                            <option value="Đang tiếp nhận bảo hành">Đang tiếp nhận bảo hành (Khách vừa mang tới)</option>
                            <option value="Đang sửa chữa">Đang sửa chữa (KTV đang thay thế/khắc phục)</option>
                            <option value="Đã sửa xong">Đã sửa xong (Đang test, chờ bàn giao)</option>
                            <option value="Đã trả khách">Đã trả khách (Hoàn tất trả máy)</option>
                            <option value="Từ chối bảo hành">Từ chối bảo hành (Rơi vỡ, ngấm nước, tem rách)</option>
                        </select>
                    </div>

                    <!-- Trạng thái Serial máy -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Cập Nhật Trạng Thái Serial Trong Kho/Hệ Thống:</label>
                        <select name="trangThaiSerial" id="formTrangThaiSerial" class="form-select" required>
                            <option value="Đang bảo hành">Đang bảo hành</option>
                            <option value="Đã bán">Đã bán (Khách đang giữ/đã nhận lại máy)</option>
                            <option value="Trong kho">Trong kho (Đổi mới trả kho / thu hồi)</option>
                            <option value="Lỗi">Lỗi (Chờ chuyển hãng / xác nhận hỏng)</option>
                        </select>
                    </div>

                    <!-- Ghi chú của Kỹ thuật viên -->
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">Ghi Chú Kỹ Thuật / Lịch Sử Khắc Phục:</label>
                        <textarea name="ghiChu" id="formGhiChu" class="form-control" rows="3" placeholder="Nhập tình trạng lỗi ban đầu, linh kiện đã thay thế, ngày hẹn khách..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-save me-1"></i> Lưu Tiến Độ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let warrantyModal = null;
document.addEventListener('DOMContentLoaded', function() {
    warrantyModal = new bootstrap.Modal(document.getElementById('updateWarrantyModal'));
});

function openUpdateModal(ticket) {
    document.getElementById('modalMaPhieu').textContent = '#' + ticket.maPhieuBH;
    document.getElementById('modalSerial').textContent = ticket.maSerial_IMEI;
    document.getElementById('modalKhachHang').textContent = ticket.hoVaTen || 'Khách vãng lai';
    document.getElementById('modalSdt').textContent = ticket.soDienThoai || 'Chưa có SĐT';
    document.getElementById('modalTenSP').textContent = ticket.tenSP;

    document.getElementById('formMaPhieu').value = ticket.maPhieuBH;
    document.getElementById('formTrangThaiPhieu').value = ticket.trangThaiPhieu || 'Đang tiếp nhận bảo hành';
    document.getElementById('formTrangThaiSerial').value = ticket.trangThaiSerial || 'Đang bảo hành';
    document.getElementById('formGhiChu').value = ticket.ghiChuBaoHanh || '';

    warrantyModal.show();
}
</script>

</body>
</html>
