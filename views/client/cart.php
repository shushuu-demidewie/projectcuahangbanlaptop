<?php
/**
 * File: views/client/cart.php
 * Giao diện Giỏ hàng của khách hàng
 */

$pageTitle = "Giỏ Hàng Của Bạn - EmShu Store";
require_once __DIR__ . '/layouts/header.php';
?>

<div class="container my-4">
    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php?url=home" class="text-decoration-none text-secondary">Trang Chủ</a></li>
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">Giỏ Hàng Của Bạn</li>
        </ol>
    </nav>

    <h4 class="fw-bold text-dark mb-4">
        <i class="bi bi-cart3 text-primary me-2"></i>Giỏ Hàng Của Bạn 
        <span class="text-muted fs-6 fw-normal">(<?= count($cartItems) ?> dòng sản phẩm)</span>
    </h4>

    <?php if (empty($cartItems)): ?>
        <!-- TRẠNG THÁI GIỎ HÀNG TRỐNG -->
        <div class="card border-0 shadow-sm rounded-4 text-center py-5 my-4">
            <div class="card-body">
                <div class="text-muted mb-3" style="font-size: 72px;">
                    <i class="bi bi-cart-x"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Giỏ hàng của bạn đang trống!</h4>
                <p class="text-secondary small mb-4">Bạn chưa chọn bất kỳ sản phẩm nào. Hãy khám phá hàng trăm mẫu laptop chính hãng với giá ưu đãi ngay hôm nay.</p>
                <a href="index.php?url=home" class="btn btn-primary btn-lg fw-bold px-4 py-2 rounded-3">
                    <i class="bi bi-arrow-left me-1"></i> Khám Phá Laptop Ngay
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- CỘT TRÁI: DANH SÁCH SẢN PHẨM TRONG GIỎ -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-secondary small text-uppercase fw-bold">
                                    <th class="ps-4" style="width: 45%;">Sản phẩm</th>
                                    <th class="text-end" style="width: 18%;">Đơn giá</th>
                                    <th class="text-center" style="width: 17%;">Số lượng</th>
                                    <th class="text-end" style="width: 15%;">Thành tiền</th>
                                    <th class="text-center" style="width: 5%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $it): ?>
                                    <tr>
                                        <!-- Thông tin Laptop -->
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <?php 
                                                $itImg = !empty($it['hinhAnh']) ? htmlspecialchars($it['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($it['maSP']) . '.jpg');
                                                ?>
                                                <div class="p-1 bg-white border rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px; flex-shrink: 0;">
                                                    <img src="<?= $itImg ?>" alt="<?= htmlspecialchars($it['tenSP']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" onerror="this.onerror=null;this.src='assets/images/products/SP004.jpg';">
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-1">
                                                        <a href="index.php?action=client-detail&id=<?= urlencode($it['maSP']) ?>" class="text-dark text-decoration-none hover-primary">
                                                            <?= htmlspecialchars($it['tenSP']) ?>
                                                        </a>
                                                    </h6>
                                                    <div class="small text-muted">
                                                        Mã: <code><?= htmlspecialchars($it['maSP']) ?></code> | Hãng: <strong><?= htmlspecialchars($it['tenTH'] ?? '') ?></strong>
                                                    </div>

                                                    <!-- Cảnh báo nếu số lượng vượt quá tồn kho -->
                                                    <?php if ($it['isOutOfStock']): ?>
                                                        <div class="text-danger small fw-bold mt-1">
                                                            <i class="bi bi-exclamation-circle-fill me-1"></i> Hiện đã hết hàng!
                                                        </div>
                                                    <?php elseif ($it['isOverStock']): ?>
                                                        <div class="text-warning small fw-bold mt-1">
                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Kho chỉ còn <?= (int)$it['soLuongTon'] ?> máy!
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Đơn giá -->
                                        <td class="text-end font-monospace fw-semibold text-secondary">
                                            <?= number_format((float)$it['donGia'], 0, ',', '.') ?> đ
                                        </td>

                                        <!-- Điều chỉnh số lượng -->
                                        <td class="text-center">
                                            <form action="index.php?action=cart-update" method="POST" class="d-inline-flex align-items-center justify-content-center">
                                                <input type="hidden" name="identifier" value="<?= htmlspecialchars((string)$it['maGioHang']) ?>">
                                                
                                                <div class="input-group input-group-sm" style="max-width: 120px;">
                                                    <button class="btn btn-outline-secondary" type="submit" name="quantity" value="<?= max(0, (int)$it['soLuong'] - 1) ?>" title="Giảm 1">
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                    <input type="text" class="form-control text-center font-monospace fw-bold bg-white" 
                                                           value="<?= (int)$it['soLuong'] ?>" readonly>
                                                    <button class="btn btn-outline-secondary" type="submit" name="quantity" 
                                                            value="<?= min((int)$it['soLuongTon'], (int)$it['soLuong'] + 1) ?>" 
                                                            title="Tăng 1" <?= ((int)$it['soLuong'] >= (int)$it['soLuongTon']) ? 'disabled' : '' ?>>
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>

                                        <!-- Thành tiền -->
                                        <td class="text-end font-monospace fw-bold text-danger">
                                            <?= number_format((float)$it['thanhTien'], 0, ',', '.') ?> đ
                                        </td>

                                        <!-- Nút Xóa -->
                                        <td class="text-center">
                                            <a href="index.php?action=cart-remove&id=<?= urlencode((string)$it['maGioHang']) ?>" 
                                               class="btn btn-sm text-danger hover-danger" 
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?');" 
                                               title="Xóa khỏi giỏ">
                                                <i class="bi bi-trash fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Nút tiếp tục mua sắm -->
                <a href="index.php?url=home" class="btn btn-outline-secondary fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Tiếp tục mua thêm laptop
                </a>
            </div>

            <!-- CỘT PHẢI: TÓM TẮT ĐƠN HÀNG VÀ THANH TOÁN -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-3">Tóm Tắt Đơn Hàng</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Tiền hàng tạm tính:</span>
                        <span class="fw-bold font-monospace"><?= number_format($subtotal, 0, ',', '.') ?> đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Phí vận chuyển toàn quốc:</span>
                        <span class="fw-semibold font-monospace text-dark">50.000 đ</span>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 p-2 small mt-2 mb-3">
                        <i class="bi bi-tag-fill text-primary me-1"></i> Bạn có thể nhập mã giảm giá tại bước <strong>Thanh toán</strong> tiếp theo!
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <span class="fw-bold text-dark d-block">TỔNG TẠM TÍNH:</span>
                            <small class="text-muted">(Đã bao gồm VAT & vận chuyển)</small>
                        </div>
                        <span class="fs-4 fw-extrabold text-danger font-monospace">
                            <?= number_format($subtotal + 50000, 0, ',', '.') ?> đ
                        </span>
                    </div>

                    <a href="index.php?action=checkout" class="btn btn-shimmer btn-lg fw-bold w-100 py-3 rounded-3 shadow text-uppercase">
                        Tiến Hành Thanh Toán <i class="bi bi-arrow-right ms-1"></i>
                    </a>

                    <!-- Cam kết -->
                    <div class="mt-4 pt-3 border-top text-secondary small">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-shield-check text-success fs-5"></i>
                            <span>Bảo hành chính hãng điện tử toàn quốc</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-box2-heart text-primary fs-5"></i>
                            <span>Đóng gói cẩn thận, bao gồm phiếu bảo hành</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
