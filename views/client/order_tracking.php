<?php
/**
 * File: views/client/order_tracking.php
 * Giao diện Tra cứu Hành trình & Tiến trình Đơn Hàng (Order Tracking Stepper)
 */

$pageTitle = 'Tra Cứu Tiến Độ Đơn Hàng - EmShu Store';
require_once __DIR__ . '/layouts/header.php';
?>

<style>
/* CSS cho Order Tracking Stepper */
.tracking-stepper {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin: 40px 0 30px;
}
.tracking-stepper::before {
    content: '';
    position: absolute;
    top: 24px;
    left: 40px;
    right: 40px;
    height: 4px;
    background: #e2e8f0;
    z-index: 1;
}
.stepper-progress-bar {
    position: absolute;
    top: 24px;
    left: 40px;
    height: 4px;
    background: #10b981;
    z-index: 2;
    transition: width 0.4s ease;
}
.step-item {
    position: relative;
    z-index: 3;
    text-align: center;
    flex: 1;
}
.step-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #ffffff;
    border: 3px solid #cbd5e1;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 22px;
    transition: all 0.3s;
}
.step-item.active .step-icon {
    border-color: #3b82f6;
    background: #eff6ff;
    color: #2563eb;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}
.step-item.completed .step-icon {
    border-color: #10b981;
    background: #10b981;
    color: #ffffff;
}
.step-title {
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 2px;
}
.step-item.active .step-title {
    color: #1e293b;
}
.step-item.completed .step-title {
    color: #059669;
}
.step-desc {
    font-size: 11px;
    color: #94a3b8;
}
.serial-pill-client {
    font-family: monospace;
    font-size: 13px;
    font-weight: bold;
    padding: 4px 10px;
    border-radius: 6px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    margin: 2px;
}
</style>

<div class="container py-5">
    <!-- KHUNG TÌM KIẾM ĐƠN HÀNG -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
            <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 rounded-pill fw-bold mb-3">
                <i class="bi bi-geo-alt-fill me-1"></i> THEO DÕI ĐƠN HÀNG TRỰC TUYẾN
            </span>
            <h2 class="fw-extrabold text-dark mb-2">Hành Trình & Tiến Độ Giao Hàng</h2>
            <p class="text-secondary mb-4">
                Nhập <strong>Mã đơn hàng</strong> (ví dụ: <code>DH001</code>) hoặc <strong>Số điện thoại</strong> nhận hàng để kiểm tra tiến trình xử lý và số Serial của chiếc máy thuộc về bạn.
            </p>

            <form action="index.php" method="GET" class="card border-0 shadow-lg rounded-4 p-2 p-md-3 bg-white">
                <input type="hidden" name="action" value="track-order">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-transparent border-0 text-primary ps-3">
                        <i class="bi bi-truck fs-4"></i>
                    </span>
                    <input type="text" 
                           name="keyword" 
                           class="form-control border-0 shadow-none fs-6" 
                           placeholder="Nhập Mã đơn hàng (VD: DH001) hoặc Số điện thoại..." 
                           value="<?= htmlspecialchars($keyword ?? '') ?>" 
                           required>
                    <button type="submit" class="btn btn-primary px-4 fw-bold rounded-3 shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-search"></i>
                        <span class="d-none d-sm-inline">Theo Dõi</span>
                    </button>
                </div>
            </form>

            <!-- Gợi ý mã mẫu -->
            <div class="mt-3 text-muted small">
                Đơn mẫu để thử: 
                <a href="index.php?action=track-order&keyword=DH001" class="text-decoration-none badge bg-light text-secondary border">DH001</a>
                <a href="index.php?action=track-order&keyword=DH002" class="text-decoration-none badge bg-light text-secondary border">DH002</a>
                <a href="index.php?action=track-order&keyword=DH003" class="text-decoration-none badge bg-light text-secondary border">DH003</a>
                <a href="index.php?action=track-order&keyword=0912345678" class="text-decoration-none badge bg-light text-secondary border">SĐT: 0912345678</a>
            </div>
        </div>
    </div>

    <!-- KẾT QUẢ TÌM KIẾM ĐƠN HÀNG -->
    <?php if ($hasSearched): ?>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        Kết quả tra cứu cho: <span class="text-primary font-monospace">"<?= htmlspecialchars($keyword) ?>"</span>
                    </h5>
                    <span class="badge bg-secondary-subtle text-secondary fs-6">
                        Tìm thấy <?= count($orders) ?> đơn hàng
                    </span>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                        <i class="bi bi-bag-x text-muted" style="font-size: 64px;"></i>
                        <h4 class="fw-bold text-dark mt-3 mb-2">Không tìm thấy đơn hàng nào!</h4>
                        <p class="text-muted mx-auto" style="max-width: 480px;">
                            Vui lòng kiểm tra lại tính chính xác của Mã đơn hoặc Số điện thoại mua hàng. Nếu vừa đặt hàng, quý khách hãy đợi 1-2 phút để hệ thống ghi nhận.
                        </p>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <a href="index.php?action=track-order" class="btn btn-outline-primary px-4">Thử Lại Từ Khóa Khác</a>
                            <a href="tel:19006868" class="btn btn-primary px-4"><i class="bi bi-telephone-fill me-1"></i> Gọi Hotline 1900 6868</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-4">
                        <?php foreach ($orders as $od): ?>
                            <?php 
                            $st = $od['stepper'];
                            $stepNum = $st['currentStep'];
                            // Tính % độ dài của thanh tiến độ
                            $progressBarWidth = match($stepNum) {
                                1 => '0%',
                                2 => '33%',
                                3 => '66%',
                                4 => '100%',
                                default => '0%'
                            };
                            ?>
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <!-- Header đơn hàng -->
                                <div class="card-header bg-light py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-box-seam text-primary fs-5"></i>
                                        <span class="fw-bold text-dark">Mã Đơn: <strong class="text-primary font-monospace fs-6">#<?= htmlspecialchars($od['maDH']) ?></strong></span>
                                        <span class="text-muted small">| Ngày đặt: <?= date('d/m/Y H:i', strtotime($od['ngayTao'])) ?></span>
                                    </div>
                                    <div>
                                        <?php if ($st['isCancelled']): ?>
                                            <span class="badge bg-danger px-3 py-2 fw-bold fs-6">Đã Hủy Đơn Hàng</span>
                                        <?php elseif ($od['trangThaiDon'] === 'HOAN_THANH'): ?>
                                            <span class="badge bg-success px-3 py-2 fw-bold fs-6"><i class="bi bi-check-circle-fill me-1"></i> Giao Hàng Thành Công</span>
                                        <?php elseif ($od['trangThaiDon'] === 'DANG_GIAO'): ?>
                                            <span class="badge bg-primary px-3 py-2 fw-bold fs-6"><i class="bi bi-truck me-1"></i> Đang Giao Hàng</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-3 py-2 fw-bold fs-6"><i class="bi bi-clock-history me-1"></i> Chờ Xác Nhận & Đóng Gói</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-body p-4">
                                    <!-- THANH TIẾN ĐỘ STEPPER -->
                                    <?php if (!$st['isCancelled']): ?>
                                        <div class="tracking-stepper">
                                            <div class="stepper-progress-bar" style="width: <?= $progressBarWidth ?>;"></div>

                                            <!-- Bước 1 -->
                                            <div class="step-item <?= ($stepNum >= 1) ? (($stepNum === 1) ? 'active' : 'completed') : '' ?>">
                                                <div class="step-icon"><i class="bi bi-bag-check"></i></div>
                                                <div class="step-title">Đặt Hàng</div>
                                                <div class="step-desc">Đã tạo đơn thành công</div>
                                            </div>

                                            <!-- Bước 2 -->
                                            <div class="step-item <?= ($stepNum >= 2) ? (($stepNum === 2) ? 'active' : 'completed') : '' ?>">
                                                <div class="step-icon"><i class="bi bi-upc-scan"></i></div>
                                                <div class="step-title">Gán Serial Máy</div>
                                                <div class="step-desc"><?= !empty($od['serials']) ? 'Đã xuất số máy' : 'Chờ kho kiểm kê' ?></div>
                                            </div>

                                            <!-- Bước 3 -->
                                            <div class="step-item <?= ($stepNum >= 3) ? (($stepNum === 3) ? 'active' : 'completed') : '' ?>">
                                                <div class="step-icon"><i class="bi bi-truck"></i></div>
                                                <div class="step-title">Đang Giao Hàng</div>
                                                <div class="step-desc">Shipper đang phát hàng</div>
                                            </div>

                                            <!-- Bước 4 -->
                                            <div class="step-item <?= ($stepNum >= 4) ? 'completed active' : '' ?>">
                                                <div class="step-icon"><i class="bi bi-check2-circle"></i></div>
                                                <div class="step-title">Hoàn Thành</div>
                                                <div class="step-desc">Khách đã nhận máy</div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-danger p-3 rounded-3 mb-4">
                                            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                                            Đơn hàng này đã bị hủy. Mọi thắc mắc vui lòng liên hệ tổng đài <strong>1900 6868</strong> để được hỗ trợ.
                                        </div>
                                    <?php endif; ?>

                                    <!-- THÔNG TIN CHI TIẾT SẢN PHẨM & SERIAL XUẤT KHO -->
                                    <div class="row g-4 mt-1">
                                        <!-- Danh sách Laptop trong đơn -->
                                        <div class="col-lg-7">
                                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                                <i class="bi bi-laptop text-primary me-2"></i>Sản Phẩm Trong Đơn
                                            </h6>
                                            <div class="table-responsive">
                                                <table class="table table-borderless align-middle mb-0">
                                                    <tbody>
                                                        <?php foreach ($od['items'] as $item): ?>
                                                            <?php 
                                                            $itemImg = !empty($item['hinhAnh']) ? htmlspecialchars($item['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($item['maSP']) . '.jpg');
                                                            ?>
                                                            <tr class="border-bottom">
                                                                <td style="width: 60px;">
                                                                    <img src="<?= $itemImg ?>" alt="" class="rounded border p-1" style="width: 50px; height: 50px; object-fit: contain; background: #fff;" onerror="this.onerror=null;this.src='assets/images/products/SP004.jpg';">
                                                                </td>
                                                                <td>
                                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($item['tenSP']) ?></div>
                                                                    <small class="text-muted">Hãng: <?= htmlspecialchars($item['tenTH'] ?? '') ?> | BH: <?= $item['thoiGianBaoHanh'] ?> tháng</small>
                                                                </td>
                                                                <td class="text-center" style="width: 70px;">
                                                                    x<?= (int)$item['soLuong'] ?>
                                                                </td>
                                                                <td class="text-end fw-bold font-monospace text-primary" style="width: 120px;">
                                                                    <?= number_format((float)$item['thanhTien'], 0, ',', '.') ?> đ
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- SỐ SERIAL/IMEI CỦA MÁY ĐƯỢC CẤP CHO ĐƠN NÀY -->
                                            <?php if (!empty($od['serials'])): ?>
                                                <div class="p-3 bg-light rounded-3 border mt-3">
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <i class="bi bi-patch-check-fill text-success fs-5"></i>
                                                        <strong class="text-dark small">Mã Serial/IMEI máy của bạn (Bảo hành chính hãng):</strong>
                                                    </div>
                                                    <div>
                                                        <?php foreach ($od['serials'] as $sn): ?>
                                                            <span class="serial-pill-client">
                                                                <i class="bi bi-upc-scan me-1 text-primary"></i><?= htmlspecialchars($sn['maSerial_IMEI']) ?>
                                                                <a href="index.php?action=check-warranty&keyword=<?= urlencode($sn['maSerial_IMEI']) ?>" class="badge bg-primary text-white ms-2 text-decoration-none" target="_blank" title="Tra cứu bảo hành cho số máy này">
                                                                    Tra cứu BH
                                                                </a>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Cột Phải: Thông tin người nhận & Thanh toán -->
                                        <div class="col-lg-5">
                                            <div class="p-3 bg-light rounded-3 border h-100">
                                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">
                                                    <i class="bi bi-geo-alt text-danger me-1"></i> Địa Chỉ Nhận Hàng
                                                </h6>
                                                <div class="small mb-1">Người nhận: <strong><?= htmlspecialchars($od['tenNguoiNhan'] ?: 'Khách hàng') ?></strong></div>
                                                <div class="small mb-1">Số điện thoại: <strong class="text-primary font-monospace"><?= htmlspecialchars($od['sdtNhanHang'] ?: '-') ?></strong></div>
                                                <div class="small text-secondary mb-3">Địa chỉ: <?= htmlspecialchars($od['diaChiGiaoHang'] ?: 'Nhận tại cửa hàng') ?></div>

                                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">
                                                    <i class="bi bi-credit-card text-success me-1"></i> Thanh Toán & Vận Chuyển
                                                </h6>
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="text-muted">Kênh mua hàng:</span>
                                                    <span class="badge <?= ($od['kenhBan'] === 'ONLINE') ? 'bg-info text-dark' : 'bg-secondary' ?>"><?= htmlspecialchars($od['kenhBan']) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="text-muted">Phương thức:</span>
                                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($od['phuongThucThanhToan'] ?: 'COD') ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between small mb-1">
                                                    <span class="text-muted">Trạng thái thanh toán:</span>
                                                    <span class="fw-bold <?= ($od['trangThaiThanhToan'] === 'DA_THANH_TOAN') ? 'text-success' : 'text-danger' ?>">
                                                        <?= ($od['trangThaiThanhToan'] === 'DA_THANH_TOAN') ? 'Đã Thanh Toán' : 'Chưa Thanh Toán' ?>
                                                    </span>
                                                </div>

                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold text-dark">Tổng Thanh Toán:</span>
                                                    <span class="fs-5 fw-extrabold text-danger font-monospace">
                                                        <?= number_format((float)$od['tongThanhToan'], 0, ',', '.') ?> đ
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
