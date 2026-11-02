<?php
/**
 * File: views/admin/imports/create.php
 * Giao diện Tạo Phiếu Nhập Kho & Nhập Số Serial/IMEI
 */

$currentUser = AuthMiddleware::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Phiếu Nhập Kho - EmShu Store Admin</title>
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
            padding: 28px;
            margin-bottom: 24px;
        }
        .item-row {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
            position: relative;
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Lập Phiếu Nhập Kho Mới</h4>
            <p class="text-muted small mb-0">Nhập lô hàng laptop từ nhà phân phối và gán số Serial/IMEI cho từng máy</p>
        </div>
        <a href="index.php?action=admin-imports" class="btn btn-outline-secondary">
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

    <form action="index.php?action=admin-import-store" method="POST" id="importForm" autocomplete="off">
        <!-- PHẦN 1: THÔNG TIN PHIẾU NHẬP -->
        <div class="form-card">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                <i class="bi bi-info-circle me-1"></i> 1. Thông Tin Nhà Cung Cấp & Hóa Đơn
            </h6>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Nhà Cung Cấp <span class="text-danger">*</span></label>
                    <select name="maNCC" class="form-select" required>
                        <option value="">-- Chọn Nhà Cung Cấp --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= htmlspecialchars($s['maNCC']) ?>" 
                                <?= (isset($oldData['maNCC']) && $oldData['maNCC'] === $s['maNCC']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['tenCongTy']) ?> (<?= htmlspecialchars($s['maNCC']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Số Hóa Đơn NCC</label>
                    <input type="text" name="soHoaDonNCC" class="form-control" placeholder="VD: HD_FPT_9812" 
                           value="<?= htmlspecialchars($oldData['soHoaDonNCC'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Số Tiền Đã Trả Trước / Đặt Cọc (VNĐ)</label>
                    <input type="number" name="tongTienTraTruocNCC" class="form-control" placeholder="0" min="0" step="1000"
                           value="<?= htmlspecialchars((string)($oldData['tongTienTraTruocNCC'] ?? '0')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ghi Chú Phiếu Nhập</label>
                    <input type="text" name="ghiChu" class="form-control" placeholder="Ghi chú về lô hàng, hạn thanh toán hoặc tình trạng kiện hàng..." 
                           value="<?= htmlspecialchars($oldData['ghiChu'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- PHẦN 2: DANH SÁCH LAPTOP NHẬP & SERIAL/IMEI -->
        <div class="form-card">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h6 class="fw-bold text-primary mb-0">
                    <i class="bi bi-laptop me-1"></i> 2. Danh Sách Laptop & Quản Lý Serial/IMEI
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" id="btnAddRow">
                    <i class="bi bi-plus-lg me-1"></i> Thêm Dòng Laptop
                </button>
            </div>

            <!-- Container chứa các dòng sản phẩm -->
            <div id="itemsContainer">
                <!-- Dòng sản phẩm mẫu đầu tiên -->
                <div class="item-row" data-index="0">
                    <div class="row g-3 align-items-start">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Chọn Laptop <span class="text-danger">*</span></label>
                            <select name="items[0][maSP]" class="form-select select-product" required>
                                <option value="">-- Chọn sản phẩm laptop --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= htmlspecialchars($p['maSP']) ?>" data-price="<?= $p['giaBan'] ?>">
                                        [<?= htmlspecialchars($p['maSP']) ?>] <?= htmlspecialchars($p['tenSP']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Số lượng <span class="text-danger">*</span></label>
                            <input type="number" name="items[0][soLuongNhap]" class="form-control input-qty" min="1" value="1" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Giá vốn nhập (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" name="items[0][giaVonNhap]" class="form-control input-cost" min="1000" step="1000" placeholder="VD: 15000000" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Thành tiền tạm tính</label>
                            <div class="form-control bg-light fw-bold text-danger subtotal-display">0 đ</div>
                        </div>

                        <!-- Ô nhập mã Serial/IMEI -->
                        <div class="col-md-11 mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold mb-0 text-dark">
                                    <i class="bi bi-upc-scan me-1 text-primary"></i>Nhập danh sách mã Serial / IMEI (mỗi mã 1 dòng):
                                </label>
                                <span class="badge bg-secondary serial-counter">Đã nhập: 0 / 1 mã</span>
                            </div>
                            <textarea name="items[0][serials]" class="form-control font-monospace textarea-serials" rows="2" 
                                      placeholder="VD:&#10;SN_ASUS_F15_1001&#10;SN_ASUS_F15_1002" required></textarea>
                            <small class="text-muted" style="font-size: 11px;">Mỗi chiếc laptop cần đúng 1 mã Serial duy nhất không trùng lặp để quản lý bảo hành.</small>
                        </div>

                        <div class="col-md-1 mt-4 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Xóa dòng này" style="display: none;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TỔNG TIỀN PHIẾU NHẬP -->
            <div class="bg-light p-3 rounded-3 mt-4 d-flex justify-content-between align-items-center border">
                <div>
                    <span class="text-muted me-2">Tổng giá trị đơn nhập hàng:</span>
                    <strong class="fs-4 text-danger" id="totalAmountDisplay">0 đ</strong>
                </div>
                <div class="d-flex gap-2">
                    <a href="index.php?action=admin-imports" class="btn btn-secondary px-4">Hủy Bỏ</a>
                    <button type="submit" class="btn btn-primary px-5 fw-semibold shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Hoàn Tất Nhập Kho
                    </button>
                </div>
            </div>
        </div>
    </form>
</main>

<!-- Template dòng sản phẩm ẩn để clone qua JavaScript -->
<template id="rowTemplate">
    <div class="item-row" data-index="{INDEX}">
        <div class="row g-3 align-items-start">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Chọn Laptop <span class="text-danger">*</span></label>
                <select name="items[{INDEX}][maSP]" class="form-select select-product" required>
                    <option value="">-- Chọn sản phẩm laptop --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= htmlspecialchars($p['maSP']) ?>" data-price="<?= $p['giaBan'] ?>">
                            [<?= htmlspecialchars($p['maSP']) ?>] <?= htmlspecialchars($p['tenSP']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold">Số lượng <span class="text-danger">*</span></label>
                <input type="number" name="items[{INDEX}][soLuongNhap]" class="form-control input-qty" min="1" value="1" required>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold">Giá vốn nhập (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" name="items[{INDEX}][giaVonNhap]" class="form-control input-cost" min="1000" step="1000" placeholder="VD: 15000000" required>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold">Thành tiền tạm tính</label>
                <div class="form-control bg-light fw-bold text-danger subtotal-display">0 đ</div>
            </div>

            <div class="col-md-11 mt-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold mb-0 text-dark">
                        <i class="bi bi-upc-scan me-1 text-primary"></i>Nhập danh sách mã Serial / IMEI (mỗi mã 1 dòng):
                    </label>
                    <span class="badge bg-secondary serial-counter">Đã nhập: 0 / 1 mã</span>
                </div>
                <textarea name="items[{INDEX}][serials]" class="form-control font-monospace textarea-serials" rows="2" 
                          placeholder="Mỗi dòng 1 mã Serial..." required></textarea>
                <small class="text-muted" style="font-size: 11px;">Mỗi chiếc laptop cần đúng 1 mã Serial duy nhất không trùng lặp.</small>
            </div>

            <div class="col-md-1 mt-4 text-end">
                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Xóa dòng này">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 0;
    const container = document.getElementById('itemsContainer');
    const template = document.getElementById('rowTemplate').innerHTML;
    const btnAddRow = document.getElementById('btnAddRow');
    const totalDisplay = document.getElementById('totalAmountDisplay');

    function formatVND(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    function calculateTotal() {
        let grandTotal = 0;
        const rows = container.querySelectorAll('.item-row');
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
            const cost = parseFloat(row.querySelector('.input-cost').value) || 0;
            const subtotal = qty * cost;
            row.querySelector('.subtotal-display').textContent = formatVND(subtotal);
            grandTotal += subtotal;

            // Cập nhật bộ đếm Serial
            updateSerialCounter(row);
        });
        totalDisplay.textContent = formatVND(grandTotal);
    }

    function updateSerialCounter(row) {
        const qty = parseInt(row.querySelector('.input-qty').value) || 0;
        const textarea = row.querySelector('.textarea-serials');
        const counter = row.querySelector('.serial-counter');

        const lines = textarea.value.split(/[\r\n,]+/).map(s => s.trim()).filter(s => s.length > 0);
        const count = lines.length;

        counter.textContent = `Đã nhập: ${count} / ${qty} mã`;
        if (count === qty && qty > 0) {
            counter.className = 'badge bg-success serial-counter';
        } else if (count > qty) {
            counter.className = 'badge bg-danger serial-counter';
            counter.textContent = `Thừa mã: ${count} / ${qty} mã!`;
        } else {
            counter.className = 'badge bg-warning text-dark serial-counter';
        }
    }

    // Lắng nghe sự kiện input trên form
    container.addEventListener('input', function (e) {
        if (e.target.classList.contains('input-qty') || e.target.classList.contains('input-cost') || e.target.classList.contains('textarea-serials')) {
            calculateTotal();
        }
    });

    // Thêm dòng mới
    btnAddRow.addEventListener('click', function () {
        rowIndex++;
        const newHtml = template.replace(/{INDEX}/g, rowIndex);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newHtml;
        const newRow = tempDiv.firstElementChild;
        container.appendChild(newRow);
        updateRemoveButtons();
        calculateTotal();
    });

    // Xóa dòng
    container.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove-row');
        if (btn) {
            const row = btn.closest('.item-row');
            if (container.querySelectorAll('.item-row').length > 1) {
                row.remove();
                updateRemoveButtons();
                calculateTotal();
            }
        }
    });

    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.item-row');
        rows.forEach(r => {
            const btn = r.querySelector('.btn-remove-row');
            if (rows.length === 1) {
                btn.style.display = 'none';
            } else {
                btn.style.display = 'inline-block';
            }
        });
    }

    calculateTotal();
});
</script>
</body>
</html>
