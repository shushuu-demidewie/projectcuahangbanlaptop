<?php
/**
 * File: views/client/account/my_orders.php
 * Giao diện Quản lý Lịch sử Đơn hàng dành cho Khách hàng đã Đăng nhập
 * Chuẩn Bootstrap 5, Bảng đơn hàng chi tiết, Nút xem hóa đơn điện tử & Theo dõi tiến độ
 */

$pageTitle = 'Lịch Sử Đơn Hàng Của Tôi - EmShu Store';
require_once __DIR__ . '/../layouts/header.php';

$currentUser = AuthMiddleware::getCurrentUser();
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?url=home" class="text-decoration-none">Trang Chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Đơn Hàng Của Tôi</li>
        </ol>
    </nav>

    <!-- Header Thông tin tài khoản -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; font-size: 1.6rem;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($currentUser['hoTen'] ?? 'Khách hàng') ?></h4>
                    <div class="text-muted small">
                        <span>Email: <strong><?= htmlspecialchars($currentUser['email'] ?? 'N/A') ?></strong></span>
                        <?php if (!empty($customerInfo['soDienThoai'])): ?>
                            <span class="mx-2">•</span> SĐT: <strong class="font-monospace"><?= htmlspecialchars($customerInfo['soDienThoai']) ?></strong>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="index.php?action=track-order" class="btn btn-outline-primary fw-semibold rounded-pill px-3">
                    <i class="bi bi-search me-1"></i> Tra cứu đơn hàng khác
                </a>
                <a href="index.php?action=check-warranty" class="btn btn-outline-success fw-semibold rounded-pill px-3">
                    <i class="bi bi-shield-check me-1"></i> Tra cứu bảo hành
                </a>
            </div>
        </div>
    </div>

    <!-- Danh Sách Đơn Hàng -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-box-seam text-primary me-2"></i> Danh Sách Đơn Hàng Đã Mua (<?= count($orders) ?> đơn)
            </h5>
            <span class="text-muted small">Đơn hàng mới nhất xếp ở trên cùng</span>
        </div>

        <div class="card-body p-0">
            <?php if (empty($orders)): ?>
                <!-- Empty State -->
                <div class="text-center py-5 px-3">
                    <div class="text-muted mb-3">
                        <i class="bi bi-cart-x display-3 text-secondary opacity-50"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Bạn chưa có đơn hàng nào tại EmShu Store!</h5>
                    <p class="text-muted mx-auto mb-4" style="max-width: 450px;">
                        Hàng trăm mẫu Laptop Gaming, Laptop Văn Phòng cao cấp chính hãng đang chờ bạn khám phá với nhiều khuyến mãi hấp dẫn.
                    </p>
                    <a href="index.php?url=home" class="btn btn-primary px-4 py-2 rounded-pill fw-bold">
                        <i class="bi bi-laptop me-2"></i> Khám Phá Laptop Ngay
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-secondary text-uppercase">
                                <th class="ps-4" style="width: 140px;">Mã Đơn</th>
                                <th>Sản Phẩm Đã Mua</th>
                                <th style="width: 160px;">Ngày Đặt</th>
                                <th style="width: 180px;">Tổng Tiền</th>
                                <th style="width: 160px;">Trạng Thái</th>
                                <th class="text-end pe-4" style="width: 220px;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $od): ?>
                                <?php 
                                    $st = $od['statusInfo'] ?? [
                                        'label' => $od['trangThaiDon'],
                                        'badge' => 'bg-secondary',
                                        'icon'  => 'bi-info-circle'
                                    ];
                                ?>
                                <tr>
                                    <!-- Mã Đơn Hàng -->
                                    <td class="ps-4">
                                        <a href="index.php?action=track-order&maDH=<?= urlencode($od['maDH']) ?>&sdt=<?= urlencode($od['sdtNhanHang'] ?? '') ?>" 
                                           class="fw-extrabold font-monospace text-primary text-decoration-none fs-6">
                                            #<?= htmlspecialchars($od['maDH']) ?>
                                        </a>
                                        <div class="small text-muted mt-1">
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($od['kenhBan'] ?? 'ONLINE') ?></span>
                                        </div>
                                    </td>

                                    <!-- Chi Tiết Sản Phẩm Tóm Tắt -->
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($od['items'] ?? [] as $item): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if (!empty($item['hinhAnh'])): ?>
                                                        <img src="<?= htmlspecialchars($item['hinhAnh']) ?>" alt="<?= htmlspecialchars($item['tenSP']) ?>" 
                                                             class="rounded border p-1" style="width: 38px; height: 38px; object-fit: contain; background: #fff;">
                                                    <?php else: ?>
                                                        <div class="rounded border p-1 bg-light text-center" style="width: 38px; height: 38px;">
                                                            <i class="bi bi-laptop"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="small fw-semibold text-dark text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($item['tenSP']) ?>">
                                                            <?= htmlspecialchars($item['tenSP']) ?>
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">
                                                            Số lượng: <strong>x<?= (int)$item['soLuong'] ?></strong> • <?= number_format((float)$item['donGia'], 0, ',', '.') ?> đ
                                                        </div>
                                                        <?php if (!empty($item['maSerial_IMEI'])): ?>
                                                            <div class="mt-1" style="font-size: 0.72rem;">
                                                                <span class="badge bg-light text-dark border">
                                                                    <i class="bi bi-upc-scan me-1 text-primary"></i> Serial: <strong class="font-monospace"><?= htmlspecialchars($item['maSerial_IMEI']) ?></strong>
                                                                </span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>

                                    <!-- Ngày Đặt -->
                                    <td class="small text-secondary">
                                        <div class="fw-semibold text-dark">
                                            <?= !empty($od['ngayTao']) ? date('d/m/Y', strtotime($od['ngayTao'])) : 'N/A' ?>
                                        </div>
                                        <div class="text-muted font-monospace" style="font-size: 0.78rem;">
                                            <?= !empty($od['ngayTao']) ? date('H:i', strtotime($od['ngayTao'])) : '' ?>
                                        </div>
                                    </td>

                                    <!-- Tổng Thanh Toán -->
                                    <td>
                                        <div class="fw-bold font-monospace text-danger fs-6">
                                            <?= number_format((float)$od['tongThanhToan'], 0, ',', '.') ?> đ
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                            <?= htmlspecialchars($od['phuongThucThanhToan'] ?? 'COD') ?>
                                        </div>
                                    </td>

                                    <!-- Trạng Thái -->
                                    <td>
                                        <span class="badge <?= $st['badge'] ?> px-3 py-2 rounded-pill small fw-bold">
                                            <i class="bi <?= $st['icon'] ?> me-1"></i> <?= htmlspecialchars($st['label']) ?>
                                        </span>
                                    </td>

                                    <!-- Hành Động: Theo dõi & Xem Hóa Đơn -->
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <!-- Nút Theo Dõi Tiến Độ -->
                                            <a href="index.php?action=track-order&maDH=<?= urlencode($od['maDH']) ?>&sdt=<?= urlencode($od['sdtNhanHang'] ?? '') ?>" 
                                               class="btn btn-sm btn-outline-primary fw-semibold rounded-pill" 
                                               title="Xem tiến trình 4 bước và vị trí đơn">
                                                <i class="bi bi-geo-alt me-1"></i> Tiến độ
                                            </a>

                                            <!-- Nút Xem Hóa Đơn Điện Tử -->
                                            <button type="button" class="btn btn-sm btn-light border fw-semibold rounded-pill text-dark" 
                                                    data-bs-toggle="modal" data-bs-target="#invoiceModal<?= htmlspecialchars($od['maDH']) ?>" 
                                                    title="Xem lại hóa đơn điện tử của đơn hàng này">
                                                <i class="bi bi-receipt text-success me-1"></i> Hóa đơn
                                            </button>
                                        </div>

                                        <!-- MODAL XEM HÓA ĐƠN ĐIỆN TỬ -->
                                        <div class="modal fade" id="invoiceModal<?= htmlspecialchars($od['maDH']) ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content rounded-4 border-0 shadow-lg text-start">
                                                    <div class="modal-header bg-light p-4 border-bottom">
                                                        <div>
                                                            <span class="badge bg-primary px-3 py-1 rounded-pill small fw-bold text-uppercase mb-1">
                                                                Hóa Đơn Điện Tử
                                                            </span>
                                                            <h5 class="modal-title fw-extrabold text-dark mb-0">
                                                                ĐƠN HÀNG #<?= htmlspecialchars($od['maDH']) ?>
                                                            </h5>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body p-4">
                                                        <!-- Đơn vị bán hàng -->
                                                        <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
                                                            <div>
                                                                <h6 class="fw-bold text-primary mb-1">EMSHU STORE - HỆ THỐNG LAPTOP CHÍNH HÃNG</h6>
                                                                <div class="small text-muted">Địa chỉ: 123 Đường Công Nghệ, Q. Cầu Giấy, Hà Nội</div>
                                                                <div class="small text-muted">Hotline: 1900 6868 | Email: contact@emshustore.vn</div>
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="small text-muted">Mã Hóa Đơn:</div>
                                                                <div class="fw-bold font-monospace text-dark"><?= htmlspecialchars($od['maHD'] ?: ('HD_' . $od['maDH'])) ?></div>
                                                                <div class="small text-muted">Ngày: <?= date('d/m/Y H:i', strtotime($od['ngayTao'])) ?></div>
                                                            </div>
                                                        </div>

                                                        <!-- Thông tin người nhận -->
                                                        <div class="bg-light p-3 rounded-3 mb-3 small">
                                                            <div class="row g-2">
                                                                <div class="col-sm-6">
                                                                    <span class="text-muted">Khách hàng:</span> <strong><?= htmlspecialchars($od['tenNguoiNhan'] ?: 'Khách hàng') ?></strong>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <span class="text-muted">Số điện thoại:</span> <strong class="font-monospace"><?= htmlspecialchars($od['sdtNhanHang'] ?: 'N/A') ?></strong>
                                                                </div>
                                                                <div class="col-12">
                                                                    <span class="text-muted">Địa chỉ giao:</span> <strong><?= htmlspecialchars($od['diaChiGiaoHang'] ?: 'Tại showroom') ?></strong>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Bảng sản phẩm -->
                                                        <div class="table-responsive border rounded-3 mb-3">
                                                            <table class="table table-sm align-middle mb-0">
                                                                <thead class="table-light small text-secondary">
                                                                    <tr>
                                                                        <th>Sản phẩm</th>
                                                                        <th class="text-center" style="width: 70px;">SL</th>
                                                                        <th class="text-end" style="width: 120px;">Đơn giá</th>
                                                                        <th class="text-end" style="width: 130px;">Thành tiền</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($od['items'] ?? [] as $it): ?>
                                                                        <tr class="small">
                                                                            <td>
                                                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($it['tenSP']) ?></div>
                                                                                <div class="text-muted" style="font-size: 0.72rem;">Mã SP: <?= htmlspecialchars($it['maSP']) ?></div>
                                                                                <?php if (!empty($it['maSerial_IMEI'])): ?>
                                                                                    <div class="mt-1">
                                                                                        <span class="badge bg-light text-dark border">
                                                                                            <i class="bi bi-upc-scan me-1"></i> Mã Serial/IMEI: <strong><?= htmlspecialchars($it['maSerial_IMEI']) ?></strong>
                                                                                        </span>
                                                                                    </div>
                                                                                <?php else: ?>
                                                                                    <div class="text-secondary small fst-italic mt-1">
                                                                                        (Số Serial/IMEI sẽ được cập nhật khi đơn hàng được duyệt xuất kho)
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                            <td class="text-center"><?= (int)$it['soLuong'] ?></td>
                                                                            <td class="text-end font-monospace"><?= number_format((float)$it['donGia'], 0, ',', '.') ?> đ</td>
                                                                            <td class="text-end font-monospace fw-bold"><?= number_format((float)$it['thanhTien'], 0, ',', '.') ?> đ</td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <!-- Tóm tắt thanh toán -->
                                                        <div class="d-flex flex-column align-items-end small">
                                                            <div style="width: 280px;">
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted">Tiền hàng:</span>
                                                                    <span class="font-monospace"><?= number_format((float)$od['tongTienHang'], 0, ',', '.') ?> đ</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted">Phí vận chuyển:</span>
                                                                    <span class="font-monospace"><?= number_format((float)($od['phiVanChuyen'] ?? 0), 0, ',', '.') ?> đ</span>
                                                                </div>
                                                                <?php if (!empty($od['tienGiamGia']) && (float)$od['tienGiamGia'] > 0): ?>
                                                                    <div class="d-flex justify-content-between mb-1 text-success">
                                                                        <span>Giảm giá:</span>
                                                                        <span class="font-monospace">-<?= number_format((float)$od['tienGiamGia'], 0, ',', '.') ?> đ</span>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div class="d-flex justify-content-between pt-2 border-top fw-bold text-dark fs-6">
                                                                    <span>Tổng thanh toán:</span>
                                                                    <span class="text-danger font-monospace"><?= number_format((float)$od['tongThanhToan'], 0, ',', '.') ?> đ</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer bg-light p-3 border-top d-flex justify-content-between">
                                                        <div class="small text-muted">
                                                            <i class="bi bi-shield-check text-success me-1"></i> Hóa đơn có giá trị bảo hành chính hãng
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.print()">
                                                                <i class="bi bi-printer me-1"></i> In hóa đơn
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">
                                                                Đóng
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END MODAL HÓA ĐƠN -->

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
