<?php
/**
 * File: views/admin/orders/detail.php
 * Giao diện Chi tiết Đơn hàng & Quản lý gán Serial/IMEI xuất bán
 */

$currentUser = AuthMiddleware::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng <?= htmlspecialchars($order['maDH']) ?> - EmShu Store Admin</title>
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
        .card-custom {
            background: #ffffff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            padding: 24px;
            margin-bottom: 24px;
        }
        .serial-tag {
            display: inline-flex;
            align-items: center;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 4px 10px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            margin: 2px;
        }
        .serial-tag .btn-remove {
            color: #ef4444;
            cursor: pointer;
            margin-left: 6px;
            text-decoration: none;
            font-weight: bold;
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-receipt text-primary me-2"></i>Đơn Hàng: <span class="text-primary"><?= htmlspecialchars($order['maDH']) ?></span>
                <?php if ($order['kenhBan'] === 'TAI_QUAY'): ?>
                    <span class="badge bg-secondary ms-2 fs-6">Tại quầy</span>
                <?php else: ?>
                    <span class="badge bg-info ms-2 fs-6">Online</span>
                <?php endif; ?>
            </h4>
            <p class="text-muted small mb-0">Ngày tạo: <?= date('d/m/Y H:i', strtotime($order['ngayTao'])) ?> | Nhân viên phụ trách: <strong><?= htmlspecialchars($order['tenNguoiPhuTrach'] ?: 'Chưa phân công') ?></strong></p>
        </div>

        <div class="d-flex gap-2">
            <a href="index.php?action=admin-order-invoice&id=<?= urlencode($order['maDH']) ?>" class="btn btn-outline-dark" target="_blank">
                <i class="bi bi-printer me-1"></i> In Hóa Đơn / Phiếu Giao
            </a>
            <a href="index.php?action=admin-orders" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($success) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($error) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- CỘT TRÁI: THÔNG TIN KHÁCH HÀNG & THAY ĐỔI TRẠNG THÁI -->
        <div class="col-lg-4">
            <!-- Trạng thái đơn hàng -->
            <div class="card-custom">
                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                    <i class="bi bi-flag me-1"></i> Trạng Thái Đơn Hàng
                </h6>
                <div class="text-center p-3 mb-3 rounded bg-light">
                    <div class="small text-muted mb-1">Hiện tại:</div>
                    <?php if ($order['trangThaiDon'] === 'CHO_DUYET'): ?>
                        <span class="badge bg-warning fs-6 text-dark px-3 py-2"><i class="bi bi-clock me-1"></i>Đang Chờ Duyệt</span>
                    <?php elseif ($order['trangThaiDon'] === 'DANG_GIAO'): ?>
                        <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-truck me-1"></i>Đang Giao Hàng</span>
                    <?php elseif ($order['trangThaiDon'] === 'HOAN_THANH'): ?>
                        <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i>Đã Hoàn Thành</span>
                    <?php else: ?>
                        <span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-x-circle me-1"></i>Đã Hủy Đơn</span>
                    <?php endif; ?>
                </div>

                <!-- Form cập nhật trạng thái -->
                <?php if ($order['trangThaiDon'] !== 'HOAN_THANH' && $order['trangThaiDon'] !== 'DA_HUY'): ?>
                    <form action="index.php?action=admin-order-status" method="POST">
                        <input type="hidden" name="maDH" value="<?= htmlspecialchars($order['maDH']) ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Chuyển sang trạng thái:</label>
                            <select name="status" class="form-select">
                                <?php if ($order['trangThaiDon'] === 'CHO_DUYET'): ?>
                                    <option value="DANG_GIAO">Duyệt & Đang giao hàng</option>
                                <?php endif; ?>
                                <option value="HOAN_THANH">Hoàn thành (Đã thanh toán & cấp Bảo hành)</option>
                                <option value="DA_HUY">Hủy đơn hàng (Hoàn trả máy về kho)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-semibold"
                                onclick="return confirm('Bạn có chắc chắn muốn chuyển trạng thái đơn hàng này?');">
                            <i class="bi bi-check2-circle me-1"></i> Cập Nhật Trạng Thái
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-muted small text-center fst-italic">
                        Đơn hàng đã kết thúc ở trạng thái này và không thể sửa đổi thêm.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Khách hàng & Giao hàng -->
            <div class="card-custom">
                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                    <i class="bi bi-person me-1"></i> Thông Tin Khách Hàng
                </h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 100px;">Khách hàng:</td>
                        <td class="fw-bold"><?= htmlspecialchars($order['tenNguoiNhan'] ?: ($order['tenKhachHangGoc'] ?: 'Khách vãng lai')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Điện thoại:</td>
                        <td class="fw-semibold text-primary"><?= htmlspecialchars($order['sdtNhanHang'] ?: ($order['sdtKhachHangGoc'] ?: '-')) ?></td>
                    </tr>
                    <?php if (!empty($order['emailKhachHang'])): ?>
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td><?= htmlspecialchars($order['emailKhachHang']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Địa chỉ nhận:</td>
                        <td><?= htmlspecialchars($order['diaChiGiaoHang'] ?: ($order['diaChiKhachHangGoc'] ?: 'Nhận tại cửa hàng')) ?></td>
                    </tr>
                    <?php if (!empty($order['ghiChuKhachHang'])): ?>
                    <tr>
                        <td class="text-muted">Ghi chú:</td>
                        <td class="fst-italic text-secondary"><?= htmlspecialchars($order['ghiChuKhachHang']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Thông tin hóa đơn đã xuất nếu hoàn thành -->
            <?php if (!empty($order['maHD'])): ?>
            <div class="card-custom bg-light border">
                <h6 class="fw-bold text-success mb-2">
                    <i class="bi bi-receipt-cutoff me-1"></i> Hóa Đơn Điện Tử Đã Xuất
                </h6>
                <div class="small mb-1">Mã HĐ: <strong class="text-dark"><?= htmlspecialchars($order['maHD']) ?></strong></div>
                <div class="small mb-2">Ngày xuất: <?= date('d/m/Y H:i', strtotime($order['ngayXuatHD'])) ?></div>
                <a href="index.php?action=admin-order-invoice&id=<?= urlencode($order['maDH']) ?>" class="btn btn-sm btn-outline-success w-100" target="_blank">
                    <i class="bi bi-printer me-1"></i> Xem & In Hóa Đơn
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- CỘT PHẢI: BẢNG SẢN PHẨM & FORM GÁN SERIAL MÁY -->
        <div class="col-lg-8">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-laptop me-1"></i> Danh Sách Sản Phẩm & Gán Serial/IMEI
                    </h6>
                    <span class="badge bg-secondary-subtle text-secondary">
                        <?= count($items) ?> chủng loại laptop
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>Sản Phẩm Laptop</th>
                                <th style="width: 140px;" class="text-end">Đơn Giá</th>
                                <th style="width: 90px;" class="text-center">Số Lượng</th>
                                <th style="width: 150px;" class="text-end">Thành Tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($item['tenSP'] ?? $item['maSP']) ?></div>
                                        <div class="small text-muted mb-2">
                                            Mã: <code><?= htmlspecialchars($item['maSP']) ?></code> | Hãng: <?= htmlspecialchars($item['tenTH'] ?? '-') ?> | BH: <?= $item['thoiGianBaoHanh'] ?> tháng
                                        </div>

                                        <!-- KHU VỰC GÁN & HIỂN THỊ SERIAL/IMEI -->
                                        <div class="p-2 bg-light rounded border">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="small fw-semibold text-secondary">
                                                    <i class="bi bi-upc-scan me-1 text-primary"></i>Mã Serial/IMEI xuất máy:
                                                </span>
                                                <span class="badge <?= (count($item['assignedSerials']) >= $item['soLuong']) ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                    Đã gán: <?= count($item['assignedSerials']) ?> / <?= $item['soLuong'] ?> máy
                                                </span>
                                            </div>

                                            <!-- Danh sách serial đã gán -->
                                            <div class="mb-2">
                                                <?php if (empty($item['assignedSerials'])): ?>
                                                    <span class="small text-danger fst-italic">Chưa gán số Serial cho sản phẩm này!</span>
                                                <?php else: ?>
                                                    <?php foreach ($item['assignedSerials'] as $sn): ?>
                                                        <span class="serial-tag">
                                                            <?= htmlspecialchars($sn['maSerial_IMEI']) ?>
                                                            <?php if ($order['trangThaiDon'] !== 'DA_HUY'): ?>
                                                                <a href="index.php?action=admin-order-remove-serial&maDH=<?= urlencode($order['maDH']) ?>&maSerial=<?= urlencode($sn['maSerial_IMEI']) ?>" 
                                                                   class="btn-remove" title="Thu hồi số máy này về kho"
                                                                   onclick="return confirm('Bạn có chắc muốn gỡ bỏ Serial [<?= htmlspecialchars($sn['maSerial_IMEI']) ?>] khỏi đơn hàng này?');">
                                                                    &times;
                                                                </a>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Dropdown gán Serial còn trong kho (chỉ hiện khi chưa gán đủ và đơn chưa hủy) -->
                                            <?php if (count($item['assignedSerials']) < $item['soLuong'] && $order['trangThaiDon'] !== 'DA_HUY'): ?>
                                                <form action="index.php?action=admin-order-assign-serial" method="POST" class="d-flex flex-wrap gap-2 align-items-center mt-2">
                                                    <input type="hidden" name="maDH" value="<?= htmlspecialchars($order['maDH']) ?>">
                                                    <input type="hidden" name="idChiTietDH" value="<?= $item['id'] ?>">
                                                    
                                                    <select name="maSerial" class="form-select form-select-sm" required style="max-width: 280px;">
                                                        <option value="">-- Chọn mã máy có sẵn trong kho --</option>
                                                        <?php foreach ($item['availableSerials'] as $avail): ?>
                                                            <option value="<?= htmlspecialchars($avail['maSerial_IMEI']) ?>">
                                                                <?= htmlspecialchars($avail['maSerial_IMEI']) ?> (<?= htmlspecialchars($avail['ghiChu'] ?: 'Nguyên seal') ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>

                                                    <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                                                        <i class="bi bi-plus-circle me-1"></i> Gán Serial
                                                    </button>

                                                    <?php if (!empty($item['availableSerials'])): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success text-nowrap" 
                                                                onclick="(function(btn){
                                                                    const form = btn.closest('form');
                                                                    const sel = form.querySelector('select[name=maSerial]');
                                                                    if (sel && sel.options.length > 1) {
                                                                        sel.selectedIndex = 1;
                                                                        form.submit();
                                                                    }
                                                                })(this)" title="Tự động chọn máy đầu tiên trong kho">
                                                            <i class="bi bi-lightning-charge-fill me-1"></i> Chọn nhanh
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                                <?php if (empty($item['availableSerials'])): ?>
                                                    <div class="small text-danger mt-1">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Kho hàng hiện không còn Serial nào trạng thái 'Trong kho'. Cần nhập kho thêm!
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold"><?= number_format($item['donGia'], 0, ',', '.') ?> đ</td>
                                    <td class="text-center fw-bold fs-6"><?= $item['soLuong'] ?></td>
                                    <td class="text-end fw-bold text-danger fs-6"><?= number_format($item['thanhTien'], 0, ',', '.') ?> đ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Tổng tiền hàng:</td>
                                <td class="text-end fw-bold"><?= number_format($order['tongTienHang'], 0, ',', '.') ?> đ</td>
                            </tr>
                            <?php if ($order['phiVanChuyen'] > 0): ?>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Phí vận chuyển:</td>
                                <td class="text-end fw-bold">+<?= number_format($order['phiVanChuyen'], 0, ',', '.') ?> đ</td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($order['tienGiamGia'] > 0): ?>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold text-success">Khuyến mãi / Giảm giá:</td>
                                <td class="text-end fw-bold text-success">-<?= number_format($order['tienGiamGia'], 0, ',', '.') ?> đ</td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="3" class="text-end fw-bold text-uppercase fs-6">Tổng Thanh Toán:</td>
                                <td class="text-end fw-bold fs-5 text-danger"><?= number_format($order['tongThanhToan'], 0, ',', '.') ?> đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
