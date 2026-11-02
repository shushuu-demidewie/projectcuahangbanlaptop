<?php
/**
 * File: views/admin/imports/detail.php
 * Giao diện Xem Chi Tiết Phiếu Nhập Kho & Danh Sách Serial Đã Gán
 */

$currentUser = AuthMiddleware::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phiếu Nhập <?= htmlspecialchars($receipt['maPhieu']) ?> - EmShu Store Admin</title>
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
        .detail-card {
            background: #ffffff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            padding: 32px;
            margin-bottom: 24px;
        }
        .serial-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            padding: 3px 8px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 12px;
            margin: 2px;
        }
        @media print {
            .sidebar, .top-navbar, .no-print {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .detail-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
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
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-text text-primary me-2"></i>Phiếu Nhập: <span class="text-primary"><?= htmlspecialchars($receipt['maPhieu']) ?></span></h4>
            <p class="text-muted small mb-0">Ngày lập: <?= date('d/m/Y H:i:s', strtotime($receipt['ngayNhap'])) ?> | Người lập: <strong><?= htmlspecialchars($receipt['tenNguoiNhap'] ?? $receipt['nguoiNhap']) ?></strong></p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-dark">
                <i class="bi bi-printer me-1"></i> In Phiếu Nhập
            </button>
            <a href="index.php?action=admin-imports" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- HÓA ĐƠN CHI TIẾT -->
    <div class="detail-card">
        <!-- HEADER PHIẾU -->
        <div class="row pb-4 mb-4 border-bottom">
            <div class="col-sm-6">
                <h5 class="fw-bold text-dark mb-1">CỬA HÀNG BÁN LAPTOP CHÍNH HÃNG</h5>
                <p class="text-muted small mb-1">Địa chỉ: Hệ thống phân phối & Bảo hành Laptop toàn quốc</p>
                <p class="text-muted small mb-0">Hotline: 1900 6868 | Email: contact@emshustore.vn</p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <h4 class="fw-bold text-primary mb-1">PHIẾU NHẬP KHO HÀNG HÓA</h4>
                <div class="fw-bold text-danger fs-5">Mã: <?= htmlspecialchars($receipt['maPhieu']) ?></div>
                <div class="small text-muted mt-1">
                    Trạng thái: 
                    <span class="badge bg-success-subtle text-success"><?= htmlspecialchars($receipt['trangThaiNhapKho']) ?></span>
                    <?php if ($receipt['trangThaiThanhToan'] === 'DA_THANH_TOAN'): ?>
                        <span class="badge bg-success">Đã thanh toán</span>
                    <?php elseif ($receipt['trangThaiThanhToan'] === 'CON_NO'): ?>
                        <span class="badge bg-warning text-dark">Còn nợ NCC</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Chưa thanh toán</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- THÔNG TIN NHÀ CUNG CẤP & HÓA ĐƠN -->
        <div class="row g-3 mb-4 p-3 bg-light rounded-3">
            <div class="col-md-6">
                <div class="text-uppercase small fw-bold text-muted mb-1">Đơn vị cung cấp:</div>
                <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($receipt['tenCongTy'] ?? $receipt['maNCC']) ?></h6>
                <div class="small text-secondary"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($receipt['diaChiTruSo'] ?? 'Chưa cập nhật địa chỉ') ?></div>
                <div class="small text-secondary"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($receipt['sdtNCC'] ?? 'N/A') ?> | <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($receipt['emailNCC'] ?? 'N/A') ?></div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="text-uppercase small fw-bold text-muted mb-1">Thông tin chứng từ:</div>
                <div class="small mb-1">Số Hóa Đơn NCC: <strong><?= htmlspecialchars($receipt['soHoaDonNCC'] ?: 'Không có') ?></strong></div>
                <div class="small mb-1">Người lập phiếu: <strong><?= htmlspecialchars($receipt['tenNguoiNhap'] ?? $receipt['nguoiNhap']) ?></strong> (<code><?= htmlspecialchars($receipt['nguoiNhap']) ?></code>)</div>
                <div class="small text-muted">Ghi chú: <?= htmlspecialchars($receipt['ghiChu'] ?: 'Không có ghi chú thêm') ?></div>
            </div>
        </div>

        <!-- BẢNG CHI TIẾT SẢN PHẨM & MÃ SERIAL/IMEI -->
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check text-primary me-2"></i>Danh Sách Laptop Đã Nhập Kho</h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 50px;" class="text-center">STT</th>
                        <th>Tên Sản Phẩm Laptop</th>
                        <th style="width: 140px;" class="text-end">Đơn Giá Vốn</th>
                        <th style="width: 90px;" class="text-center">Số Lượng</th>
                        <th style="width: 160px;" class="text-end">Thành Tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $stt = 0;
                        $totalQty = 0;
                        foreach ($details as $row): 
                            $stt++;
                            $totalQty += $row['soLuongNhap'];
                    ?>
                        <tr>
                            <td class="text-center fw-bold text-muted"><?= $stt ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['tenSP'] ?? $row['maSP']) ?></div>
                                <div class="small text-muted mb-2">Mã SP: <code><?= htmlspecialchars($row['maSP']) ?></code></div>

                                <!-- Danh sách các mã Serial đã gán cho dòng này -->
                                <div class="p-2 bg-light rounded border">
                                    <div class="small fw-semibold text-secondary mb-1">
                                        <i class="bi bi-upc-scan me-1 text-primary"></i>Danh sách <?= count($row['serials']) ?> mã Serial/IMEI đã nhập:
                                    </div>
                                    <div>
                                        <?php if (empty($row['serials'])): ?>
                                            <span class="text-muted small fst-italic">Chưa có mã Serial nào được lưu!</span>
                                        <?php else: ?>
                                            <?php foreach ($row['serials'] as $sn): ?>
                                                <span class="serial-badge" title="Trạng thái: <?= htmlspecialchars($sn['trangThai']) ?>">
                                                    <?= htmlspecialchars($sn['maSerial_IMEI']) ?>
                                                    <?php if ($sn['trangThai'] === 'Đã bán'): ?>
                                                        <i class="bi bi-check-circle-fill text-success" title="Đã xuất bán"></i>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end fw-semibold"><?= number_format($row['giaVonNhap'], 0, ',', '.') ?> đ</td>
                            <td class="text-center fs-6 fw-bold"><?= $row['soLuongNhap'] ?></td>
                            <td class="text-end fw-bold text-danger fs-6"><?= number_format($row['thanhTienNhap'], 0, ',', '.') ?> đ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold text-uppercase">Tổng số lượng máy:</td>
                        <td class="text-center fw-bold fs-6 text-primary"><?= $totalQty ?> máy</td>
                        <td class="text-end fw-bold fs-5 text-danger"><?= number_format($receipt['tongTienNhap'], 0, ',', '.') ?> đ</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end fw-semibold">Số tiền đã thanh toán / đặt cọc:</td>
                        <td class="text-end fw-bold text-success"><?= number_format($receipt['tongTienTraTruocNCC'], 0, ',', '.') ?> đ</td>
                    </tr>
                    <?php $debt = $receipt['tongTienNhap'] - $receipt['tongTienTraTruocNCC']; ?>
                    <?php if ($debt > 0): ?>
                    <tr>
                        <td colspan="4" class="text-end fw-bold text-danger">Còn công nợ phải trả NCC:</td>
                        <td class="text-end fw-bold text-danger"><?= number_format($debt, 0, ',', '.') ?> đ</td>
                    </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        </div>

        <!-- CHỮ KÝ XÁC NHẬN (KHI IN PHIẾU) -->
        <div class="row text-center mt-5 pt-3">
            <div class="col-4">
                <div class="fw-bold">NGƯỜI GIAO HÀNG (NCC)</div>
                <div class="small text-muted fst-italic mb-5">(Ký, ghi rõ họ tên)</div>
            </div>
            <div class="col-4">
                <div class="fw-bold">THỦ KHO NHẬN HÀNG</div>
                <div class="small text-muted fst-italic mb-5">(Ký, ghi rõ họ tên)</div>
            </div>
            <div class="col-4">
                <div class="fw-bold">KẾ TOÁN TRƯỞNG / QUẢN TRỊ</div>
                <div class="small text-muted fst-italic mb-5">(Ký, đóng dấu)</div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
