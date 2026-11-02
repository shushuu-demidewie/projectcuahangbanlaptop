<?php
/**
 * File: views/client/checkout.php
 * Giao diện Thanh toán và Đặt hàng Online
 */

$pageTitle = "Thanh Toán Đơn Hàng - EmShu Store";
require_once __DIR__ . '/layouts/header.php';
?>

<div class="container my-4">
    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php?url=home" class="text-decoration-none text-secondary">Trang Chủ</a></li>
            <li class="breadcrumb-item"><a href="index.php?action=cart" class="text-decoration-none text-secondary">Giỏ Hàng</a></li>
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">Tiến Hành Thanh Toán</li>
        </ol>
    </nav>

    <h4 class="fw-bold text-dark mb-4">
        <i class="bi bi-credit-card-2-front text-primary me-2"></i>Thanh Toán & Xác Nhận Đơn Hàng
    </h4>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4 rounded-3 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div><?= htmlspecialchars($error) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- CỘT TRÁI: FORM THÔNG TIN GIAO HÀNG & PHƯƠNG THỨC THANH TOÁN -->
        <div class="col-lg-7">
            <form action="index.php?action=checkout-process" method="POST" id="checkoutForm">
                <!-- 1. THÔNG TIN NGƯỜI NHẬN -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-geo-alt-fill text-danger me-2"></i>1. Thông Tin Nhận Hàng
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Họ và tên người nhận <span class="text-danger">*</span></label>
                                <input type="text" name="tenNguoiNhan" class="form-control" 
                                       placeholder="Ví dụ: Nguyễn Văn A" 
                                       value="<?= htmlspecialchars($customerDefault['tenNguoiNhan'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                <input type="tel" name="sdtNhanHang" class="form-control font-monospace" 
                                       placeholder="Ví dụ: 0912345678" 
                                       value="<?= htmlspecialchars($customerDefault['sdtNhanHang'] ?? '') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">Email nhận thông báo đơn hàng & hóa đơn</label>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="name@gmail.com" 
                                       value="<?= htmlspecialchars($customerDefault['email'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">Địa chỉ giao hàng chi tiết <span class="text-danger">*</span></label>
                                <textarea name="diaChiGiaoHang" class="form-control" rows="2" 
                                          placeholder="Số nhà, tên ngõ/đường, Phường/Xã, Quận/Huyện, Tỉnh/TP..." required><?= htmlspecialchars($customerDefault['diaChiGiaoHang'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">Ghi chú giao hàng (nếu có)</label>
                                <input type="text" name="ghiChuKhachHang" class="form-control" 
                                       placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. PHƯƠNG THỨC THANH TOÁN -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-wallet2 text-primary me-2"></i>2. Phương Thức Thanh Toán
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-check p-3 border rounded-3 mb-3 d-flex align-items-center gap-3">
                            <input class="form-check-input ms-0 mt-0" type="radio" name="paymentMethod" id="payCOD" value="COD" checked>
                            <label class="form-check-label w-100" for="payCOD" role="button">
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-cash-coin fs-4 text-success"></i> Thanh toán tiền mặt khi nhận hàng (COD)
                                </div>
                                <div class="text-muted small mt-1">Quý khách được kiểm tra hàng trước khi thanh toán cho nhân viên giao hàng.</div>
                            </label>
                        </div>

                        <div class="form-check p-3 border rounded-3 d-flex align-items-start gap-3">
                            <input class="form-check-input ms-0 mt-2" type="radio" name="paymentMethod" id="payBank" value="BANK_TRANSFER">
                            <label class="form-check-label w-100" for="payBank" role="button">
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="bi bi-qr-code fs-4 text-primary"></i> Chuyển khoản ngân hàng 24/7 (Quét mã VietQR)
                                </div>
                                <div class="text-muted small mt-1">Chuyển tiền nhanh qua tài khoản ngân hàng chính thức của EmShu Store.</div>
                                <div class="mt-2 p-2 bg-light rounded border text-secondary small">
                                    <strong>Ngân hàng:</strong> Vietcombank - CN Hà Nội<br>
                                    <strong>Số tài khoản:</strong> <code>0123456789999</code><br>
                                    <strong>Chủ tài khoản:</strong> CÔNG TY TNHH EMSHU STORE VIỆT NAM<br>
                                    <em>(Nội dung chuyển khoản: Họ tên + SĐT đặt hàng)</em>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Input ẩn mã voucher đã áp dụng -->
                <input type="hidden" name="voucherCode" value="<?= htmlspecialchars($voucherCode) ?>">

                <!-- NÚT XÁC NHẬN ĐẶT HÀNG TRÊN MOBILE -->
                <div class="d-grid d-lg-none mb-4">
                    <button type="submit" class="btn btn-shimmer btn-lg fw-bold py-3 text-uppercase shadow">
                        <i class="bi bi-check2-circle me-1"></i> Xác Nhận Đặt Hàng Ngay
                    </button>
                </div>
            </form>
        </div>

        <!-- CỘT PHẢI: TÓM TẮT ĐƠN HÀNG & MÃ KHUYẾN MÃI -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-3">Đơn Hàng Của Bạn</h5>

                <!-- DANH SÁCH MÁY MUA -->
                <div class="cart-summary-items mb-3" style="max-height: 260px; overflow-y: auto;">
                    <?php foreach ($cartItems as $it): ?>
                        <?php 
                        $itImg = !empty($it['hinhAnh']) ? htmlspecialchars($it['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($it['maSP']) . '.jpg');
                        ?>
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="d-flex align-items-center gap-2 me-2">
                                <div class="p-1 bg-light border rounded text-center" style="width: 42px; height: 42px; flex-shrink: 0;">
                                    <img src="<?= $itImg ?>" alt="<?= htmlspecialchars($it['tenSP']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" onerror="this.onerror=null;this.src='assets/images/products/SP004.jpg';">
                                </div>
                                <div>
                                    <div class="fw-bold small text-dark text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($it['tenSP']) ?>"><?= htmlspecialchars($it['tenSP']) ?></div>
                                    <div class="text-muted small">
                                        SL: <strong>x<?= (int)$it['soLuong'] ?></strong> &times; <?= number_format((float)$it['donGia'], 0, ',', '.') ?> đ
                                    </div>
                                </div>
                            </div>
                            <div class="fw-bold font-monospace text-dark text-nowrap">
                                <?= number_format((float)$it['thanhTien'], 0, ',', '.') ?> đ
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- FORM ÁP DỤNG MÃ VOUCHER KHUYẾN MÃI -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Mã giảm giá / Voucher:</label>
                    <form action="index.php?action=checkout-voucher" method="POST" class="input-group">
                        <input type="text" name="voucher_code" class="form-control text-uppercase font-monospace fw-bold" 
                               placeholder="Nhập mã voucher..." 
                               value="<?= htmlspecialchars($voucherCode) ?>">
                        <button class="btn btn-outline-primary fw-bold" type="submit">Áp dụng</button>
                    </form>

                    <!-- Gợi ý mã voucher -->
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <span class="badge bg-light text-dark border cursor-pointer" onclick="document.querySelector('input[name=voucher_code]').value='KM_BACK2SCHOOL';">
                            KM_BACK2SCHOOL (-5%)
                        </span>
                        <span class="badge bg-light text-dark border cursor-pointer" onclick="document.querySelector('input[name=voucher_code]').value='KM_GAMING1M';">
                            KM_GAMING1M (-1.000.000đ)
                        </span>
                    </div>

                    <?php if (!empty($voucherMessage)): ?>
                        <div class="text-success small fw-bold mt-2">
                            <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($voucherMessage) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <hr class="my-3">

                <!-- TÍNH TOÁN CHI PHÍ -->
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-secondary">Tổng tiền hàng:</span>
                    <span class="fw-bold font-monospace"><?= number_format($subtotal, 0, ',', '.') ?> đ</span>
                </div>

                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-secondary">Phí giao hàng toàn quốc:</span>
                    <span class="fw-semibold font-monospace text-dark">+50.000 đ</span>
                </div>

                <?php if ($voucherDiscount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 small text-success fw-bold">
                        <span>Khuyến mãi (<?= htmlspecialchars($voucherCode) ?>):</span>
                        <span class="font-monospace">-<?= number_format($voucherDiscount, 0, ',', '.') ?> đ</span>
                    </div>
                <?php endif; ?>

                <hr class="my-2">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="fw-bold text-dark fs-6 d-block">TỔNG THANH TOÁN:</span>
                        <small class="text-muted">(Đã bao gồm VAT)</small>
                    </div>
                    <span class="fs-3 fw-extrabold text-danger font-monospace">
                        <?= number_format($grandTotal, 0, ',', '.') ?> đ
                    </span>
                </div>

                <!-- NÚT SUBMIT FORM THANH TOÁN TRÊN DESKTOP -->
                <button type="button" onclick="document.getElementById('checkoutForm').submit();" 
                        class="btn btn-shimmer btn-lg fw-extrabold w-100 py-3 rounded-3 shadow text-uppercase d-none d-lg-block">
                    <i class="bi bi-check2-circle me-1"></i> Xác Nhận Đặt Hàng
                </button>

                <div class="text-center mt-3">
                    <a href="index.php?action=cart" class="text-muted small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại sửa giỏ hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
