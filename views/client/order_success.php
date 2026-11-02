<?php
/**
 * File: views/client/order_success.php
 * Màn hình Thông báo Đặt hàng thành công dành cho khách hàng
 */

$pageTitle = "Đặt Hàng Thành Công #" . htmlspecialchars($order['maDH']) . " - EmShu Store";
require_once __DIR__ . '/layouts/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- THẺ THÔNG BÁO THÀNH CÔNG -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="p-5 text-center text-white" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                    <div class="mb-3">
                        <i class="bi bi-check-circle display-1"></i>
                    </div>
                    <h2 class="fw-extrabold mb-2">ĐẶT HÀNG THÀNH CÔNG!</h2>
                    <p class="lead mb-0 text-white text-opacity-90">
                        Cảm ơn bạn đã tin tưởng và đặt mua laptop tại <strong>EmShu Store System</strong>.
                    </p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <!-- KHỐI MÃ ĐƠN HÀNG -->
                    <div class="p-3 bg-light rounded-3 text-center mb-4 border">
                        <span class="text-secondary small text-uppercase fw-semibold d-block">Mã đơn hàng của bạn:</span>
                        <span class="fs-2 fw-extrabold text-primary font-monospace" id="orderCode">
                            <?= htmlspecialchars($order['maDH']) ?>
                        </span>
                        <div class="text-muted small mt-1">
                            Ngày tạo đơn: <?= !empty($order['ngayTao']) ? date('d/m/Y H:i', strtotime($order['ngayTao'])) : date('d/m/Y H:i') ?> | 
                            Trạng thái: <span class="badge bg-warning text-dark">Chờ duyệt xuất kho</span>
                        </div>
                    </div>

                    <!-- THÔNG TIN GIAO HÀNG & PHƯƠNG THỨC -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark border-bottom pb-2">
                                <i class="bi bi-person-circle text-primary me-1"></i> Thông Tin Nhận Hàng
                            </h6>
                            <div class="small text-secondary" style="line-height: 1.8;">
                                <strong>Người nhận:</strong> <?= htmlspecialchars($order['tenNguoiNhan'] ?? '') ?><br>
                                <strong>Điện thoại:</strong> <?= htmlspecialchars($order['sdtNhanHang'] ?? '') ?><br>
                                <strong>Địa chỉ giao:</strong> <?= htmlspecialchars($order['diaChiGiaoHang'] ?? '') ?><br>
                                <?php if (!empty($order['ghiChuKhachHang'])): ?>
                                    <strong>Ghi chú:</strong> <em><?= htmlspecialchars($order['ghiChuKhachHang']) ?></em>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark border-bottom pb-2">
                                <i class="bi bi-credit-card text-success me-1"></i> Thanh Toán & Vận Chuyển
                            </h6>
                            <div class="small text-secondary" style="line-height: 1.8;">
                                <strong>Hình thức thanh toán:</strong> 
                                <span class="badge bg-secondary"><?= htmlspecialchars($order['phuongThucThanhToan'] ?? 'COD') ?></span><br>
                                <strong>Vận chuyển:</strong> <?= htmlspecialchars($order['phuongThucGiaoHang'] ?? 'Giao hàng tận nơi') ?><br>
                                <strong>Trạng thái thanh toán:</strong> 
                                <span class="badge bg-info-subtle text-info border">
                                    <?= htmlspecialchars($order['trangThaiOnline'] ?? 'Chưa thanh toán') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- DANH SÁCH SẢN PHẨM ĐÃ ĐẶT -->
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-laptop text-primary me-1"></i> Chi Tiết Sản Phẩm Đã Mua
                    </h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Tên Laptop</th>
                                    <th class="text-center" style="width: 10%;">SL</th>
                                    <th class="text-end" style="width: 20%;">Đơn giá</th>
                                    <th class="text-end" style="width: 20%;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                    <?php 
                                    $itemImg = !empty($item['hinhAnh']) ? htmlspecialchars($item['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($item['maSP']) . '.jpg');
                                    ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="p-1 bg-light border rounded text-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                                                    <img src="<?= $itemImg ?>" alt="<?= htmlspecialchars($item['tenSP']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" onerror="this.onerror=null;this.src='assets/images/products/SP004.jpg';">
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($item['tenSP']) ?></div>
                                                    <div class="text-muted" style="font-size: 11px;">Mã: <?= htmlspecialchars($item['maSP']) ?> | Hãng: <?= htmlspecialchars($item['tenTH'] ?? '') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold"><?= (int)$item['soLuong'] ?></td>
                                        <td class="text-end font-monospace"><?= number_format((float)$item['donGia'], 0, ',', '.') ?> đ</td>
                                        <td class="text-end fw-bold font-monospace"><?= number_format((float)$item['thanhTien'], 0, ',', '.') ?> đ</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- TỔNG KẾT TIỀN ĐƠN HÀNG -->
                    <div class="row justify-content-end mb-4">
                        <div class="col-sm-6">
                            <table class="table table-sm table-borderless font-monospace small">
                                <tr>
                                    <td class="text-secondary">Tiền hàng:</td>
                                    <td class="text-end fw-semibold"><?= number_format((float)$order['tongTienHang'], 0, ',', '.') ?> đ</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Phí giao hàng:</td>
                                    <td class="text-end fw-semibold">+<?= number_format((float)$order['phiVanChuyen'], 0, ',', '.') ?> đ</td>
                                </tr>
                                <?php if ((float)$order['tienGiamGia'] > 0): ?>
                                    <tr>
                                        <td class="text-success">Giảm giá voucher:</td>
                                        <td class="text-end text-success fw-semibold">-<?= number_format((float)$order['tienGiamGia'], 0, ',', '.') ?> đ</td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="border-top">
                                    <td class="fw-bold text-dark fs-6 pt-2">TỔNG THANH TOÁN:</td>
                                    <td class="fw-extrabold text-danger fs-5 text-end pt-2">
                                        <?= number_format((float)$order['tongThanhToan'], 0, ',', '.') ?> đ
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- LỜI DẶN KHÁCH HÀNG -->
                    <div class="alert alert-info border-0 rounded-3 small mb-4">
                        <h6 class="fw-bold mb-1"><i class="bi bi-telephone-inbound me-1"></i> Các bước tiếp theo:</h6>
                        <ul class="mb-0 ps-3">
                            <li>Nhân viên phụ trách của EmShu Store sẽ gọi điện thoại tới số <strong><?= htmlspecialchars($order['sdtNhanHang'] ?? '') ?></strong> trong vòng 15-30 phút để xác nhận lại đơn hàng và thông báo thời gian giao nhận dự kiến.</li>
                            <li>Đơn hàng sẽ được kiểm tra ngoại quan máy và kích hoạt tem/phiếu bảo hành điện tử trước khi xuất kho giao cho đơn vị vận chuyển.</li>
                        </ul>
                    </div>

                    <!-- NÚT ĐIỀU HƯỚNG -->
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <button onclick="window.print()" class="btn btn-outline-secondary px-4 fw-semibold">
                            <i class="bi bi-printer me-1"></i> In Đơn Hàng
                        </button>
                        <a href="index.php?action=track-order&order_code=<?= urlencode($order['maDH']) ?>" class="btn btn-outline-primary px-4 fw-bold">
                            <i class="bi bi-geo-alt me-1"></i> Theo Dõi Đơn Hàng
                        </a>
                        <a href="index.php?action=client-home" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-shop me-1"></i> Quay Lại Trang Chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
