<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn / Phiếu Giao Hàng - <?= htmlspecialchars($order['maDH'] ?? '') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 14px;
        }
        .invoice-card {
            max-width: 860px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            padding: 40px;
        }
        .invoice-header {
            border-bottom: 2px dashed #e2e8f0;
            padding-bottom: 24px;
            margin-bottom: 24px;
        }
        .brand-logo {
            font-size: 24px;
            font-weight: 800;
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .badge-status {
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
        }
        .serial-badge {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            margin-right: 4px;
            margin-top: 4px;
        }
        .table-invoice th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cbd5e1;
        }
        .table-invoice td {
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .signature-box {
            text-align: center;
            margin-top: 45px;
            padding-top: 15px;
        }
        .signature-space {
            height: 75px;
        }

        /* Print Settings */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0;
            }
            .invoice-card {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 10px 20px !important;
                box-shadow: none !important;
                border: none !important;
            }
            .no-print {
                display: none !important;
            }
            .table-invoice th {
                background-color: #f1f5f9 !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .badge-status {
                border: 1px solid #000 !important;
                color: #000 !important;
                background: transparent !important;
            }
            .serial-badge {
                border: 1px solid #000 !important;
                background: transparent !important;
            }
        }
    </style>
</head>
<body>

<!-- Thanh điều khiển trên màn hình (ẩn khi in) -->
<div class="container no-print py-3">
    <div class="d-flex justify-content-between align-items-center max-w-860 mx-auto" style="max-width: 860px;">
        <a href="index.php?action=admin-order-detail&id=<?= urlencode($order['maDH']) ?>" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Quay lại chi tiết đơn hàng
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary fw-bold px-4 shadow-sm">
                <i class="bi bi-printer me-2"></i> In Hóa Đơn (Ctrl + P)
            </button>
        </div>
    </div>
</div>

<!-- Khung trang Hóa đơn / Phiếu xuất kho -->
<div class="invoice-card">
    <!-- Header: Thông tin cửa hàng & Tiêu đề -->
    <div class="invoice-header">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <div class="brand-logo mb-1">
                    <i class="bi bi-laptop"></i> EMSHU STORE SYSTEM
                </div>
                <div class="text-secondary small">
                    <strong>Hệ thống phân phối Laptop & Phụ kiện chính hãng</strong><br>
                    <i class="bi bi-geo-alt me-1"></i> 123 Đường Công Nghệ, Quận Cầu Giấy, Hà Nội<br>
                    <i class="bi bi-telephone me-1"></i> Hotline: 1900 6868 - 0988.888.999<br>
                    <i class="bi bi-envelope me-1"></i> Email: support@emshustore.vn | Web: www.emshustore.vn
                </div>
            </div>
            <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                <h4 class="fw-extrabold text-uppercase text-primary mb-1">HÓA ĐƠN BÁN HÀNG</h4>
                <div class="text-secondary small mb-2">KIÊM PHIẾU XUẤT KHO & BẢO HÀNH</div>
                <div class="fw-bold font-monospace text-dark fs-5">#<?= htmlspecialchars($order['maDH']) ?></div>
                <div class="small text-muted">
                    Ngày lập: <?= !empty($order['ngayTao']) ? date('d/m/Y H:i', strtotime($order['ngayTao'])) : date('d/m/Y H:i') ?>
                </div>
                <?php if (!empty($order['maHD'])): ?>
                    <div class="small text-success fw-bold mt-1">
                        <i class="bi bi-check-circle-fill me-1"></i> Mã HĐ điện tử: <?= htmlspecialchars($order['maHD']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Thông tin khách hàng & Giao dịch -->
    <div class="row mb-4">
        <div class="col-6">
            <h6 class="text-uppercase fw-bold text-secondary mb-2" style="font-size: 11px; letter-spacing: 0.5px;">
                <i class="bi bi-person-circle me-1"></i> Khách hàng nhận hàng
            </h6>
            <div class="fw-bold text-dark fs-6">
                <?= htmlspecialchars(!empty($order['tenNguoiNhan']) ? $order['tenNguoiNhan'] : ($order['tenKhachHangGoc'] ?? 'Khách vãng lai')) ?>
            </div>
            <div class="text-secondary small mt-1">
                <strong>Số điện thoại:</strong> <?= htmlspecialchars(!empty($order['sdtNhanHang']) ? $order['sdtNhanHang'] : ($order['sdtKhachHangGoc'] ?? 'Chưa cập nhật')) ?><br>
                <strong>Địa chỉ:</strong> <?= htmlspecialchars(!empty($order['diaChiGiaoHang']) ? $order['diaChiGiaoHang'] : ($order['diaChiKhachHangGoc'] ?? 'Tại cửa hàng')) ?><br>
                <?php if (!empty($order['ghiChuKhachHang'])): ?>
                    <strong class="text-danger">Ghi chú:</strong> <em><?= htmlspecialchars($order['ghiChuKhachHang']) ?></em>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-6 text-end">
            <h6 class="text-uppercase fw-bold text-secondary mb-2" style="font-size: 11px; letter-spacing: 0.5px;">
                <i class="bi bi-info-circle me-1"></i> Thông tin giao dịch
            </h6>
            <div class="text-secondary small">
                <strong>Kênh bán hàng:</strong> 
                <span class="badge <?= $order['kenhBan'] === 'ONLINE' ? 'bg-info text-dark' : 'bg-success' ?>">
                    <?= $order['kenhBan'] === 'ONLINE' ? 'ĐƠN ONLINE' : 'TẠI QUẦY' ?>
                </span><br>
                <div class="mt-1">
                    <strong>Phương thức TT:</strong> 
                    <?= htmlspecialchars($order['phuongThucThanhToan'] ?? 'Tiền mặt') ?>
                </div>
                <div class="mt-1">
                    <strong>Phương thức GH:</strong> 
                    <?= htmlspecialchars($order['phuongThucGiaoHang'] ?? 'Nhận tại quầy') ?>
                </div>
                <div class="mt-1">
                    <strong>Nhân viên phụ trách:</strong> 
                    <?= htmlspecialchars($order['tenNguoiPhuTrach'] ?? 'Hệ thống') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách sản phẩm & Serial xuất kho -->
    <div class="table-responsive mb-3">
        <table class="table table-bordered table-invoice align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">STT</th>
                    <th style="width: 45%;">Tên Laptop & Mã Serial/IMEI</th>
                    <th class="text-center" style="width: 12%;">Bảo hành</th>
                    <th class="text-center" style="width: 8%;">SL</th>
                    <th class="text-end" style="width: 15%;">Đơn giá</th>
                    <th class="text-end" style="width: 15%;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                foreach ($items as $item): 
                ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $stt++ ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($item['tenSP']) ?></div>
                            <div class="small text-muted mb-1">Mã SP: <?= htmlspecialchars($item['maSP']) ?> | Hãng: <?= htmlspecialchars($item['tenTH'] ?? '') ?></div>
                            
                            <!-- Danh sách mã Serial/IMEI đã gán xuất kho -->
                            <?php if (!empty($item['assignedSerials'])): ?>
                                <div class="mt-1">
                                    <span class="small fw-semibold text-secondary">Mã Serial/IMEI xuất bán:</span><br>
                                    <?php foreach ($item['assignedSerials'] as $serial): ?>
                                        <span class="serial-badge">
                                            <i class="bi bi-qr-code me-1"></i><?= htmlspecialchars($serial['maSerial_IMEI']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="small text-muted fst-italic mt-1">(Chưa gán Serial máy)</div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                <?= (int)($item['thoiGianBaoHanh'] ?? 12) ?> tháng
                            </span>
                        </td>
                        <td class="text-center fw-bold"><?= (int)$item['soLuong'] ?></td>
                        <td class="text-end font-monospace"><?= number_format((float)$item['donGia'], 0, ',', '.') ?> đ</td>
                        <td class="text-end fw-bold font-monospace"><?= number_format((float)$item['thanhTien'], 0, ',', '.') ?> đ</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Tổng kết thanh toán -->
    <div class="row">
        <div class="col-sm-6">
            <div class="border rounded p-3 bg-light-subtle small">
                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-shield-check text-primary me-1"></i> Chính sách bảo hành & Đổi trả:</h6>
                <ul class="mb-0 ps-3 text-secondary" style="line-height: 1.6;">
                    <li>Bảo hành 1 đổi 1 trong 30 ngày nếu có lỗi từ nhà sản xuất.</li>
                    <li>Sản phẩm được bảo hành điện tử chính hãng theo số <strong>Serial / IMEI</strong> in trên phiếu này.</li>
                    <li>Vui lòng giữ phiếu này và kiểm tra nguyên vẹn tem bảo hành khi nhận máy.</li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6">
            <table class="table table-sm table-borderless font-monospace ms-auto" style="max-width: 320px;">
                <tr>
                    <td class="text-secondary">Tổng tiền hàng:</td>
                    <td class="text-end fw-semibold"><?= number_format((float)($order['tongTienHang'] ?? 0), 0, ',', '.') ?> đ</td>
                </tr>
                <tr>
                    <td class="text-secondary">Phí vận chuyển:</td>
                    <td class="text-end fw-semibold">+<?= number_format((float)($order['phiVanChuyen'] ?? 0), 0, ',', '.') ?> đ</td>
                </tr>
                <?php if ((float)($order['tienGiamGia'] ?? 0) > 0): ?>
                    <tr>
                        <td class="text-success">Giảm giá / Voucher:</td>
                        <td class="text-end text-success fw-semibold">-<?= number_format((float)$order['tienGiamGia'], 0, ',', '.') ?> đ</td>
                    </tr>
                <?php endif; ?>
                <tr class="border-top">
                    <td class="fs-6 fw-bold text-dark pt-2">TỔNG THANH TOÁN:</td>
                    <td class="fs-5 fw-extrabold text-danger text-end pt-2">
                        <?= number_format((float)($order['tongThanhToan'] ?? 0), 0, ',', '.') ?> đ
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Chữ ký các bên -->
    <div class="row signature-box">
        <div class="col-4">
            <div class="fw-bold">NGƯỜI MUA HÀNG</div>
            <div class="small text-muted">(Ký, ghi rõ họ tên)</div>
            <div class="signature-space"></div>
            <div class="small fw-semibold text-secondary">
                <?= htmlspecialchars(!empty($order['tenNguoiNhan']) ? $order['tenNguoiNhan'] : ($order['tenKhachHangGoc'] ?? '')) ?>
            </div>
        </div>
        <div class="col-4">
            <div class="fw-bold">THỦ KHO / GIAO NHẬN</div>
            <div class="small text-muted">(Ký, ghi rõ họ tên)</div>
            <div class="signature-space"></div>
        </div>
        <div class="col-4">
            <div class="fw-bold">NGƯỜI LẬP HÓA ĐƠN</div>
            <div class="small text-muted">(Ký, đóng dấu nếu có)</div>
            <div class="signature-space"></div>
            <div class="small fw-semibold text-secondary">
                <?= htmlspecialchars($order['tenNguoiPhuTrach'] ?? $_SESSION['user']['hoTen'] ?? 'Nhân viên bán hàng') ?>
            </div>
        </div>
    </div>

    <!-- Lời cảm ơn -->
    <div class="text-center mt-4 pt-3 border-top text-muted small fst-italic">
        Cảm ơn Quý khách đã tin tưởng và ủng hộ sản phẩm tại EmShu Store System!<br>
        Tổng đài chăm sóc & hỗ trợ kỹ thuật: <strong>1900 6868</strong>
    </div>
</div>

</body>
</html>
