<?php
/**
 * File: views/client/product_detail.php
 * Giao diện Chi tiết Sản phẩm Laptop
 */

$pageTitle = htmlspecialchars($product['tenSP']) . " - EmShu Store";
require_once __DIR__ . '/layouts/header.php';
?>

<div class="container my-4">
    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php?url=home" class="text-decoration-none text-secondary">Trang Chủ</a></li>
            <li class="breadcrumb-item">
                <a href="index.php?action=client-home&category=<?= $product['maDM'] ?>" class="text-decoration-none text-secondary">
                    <?= htmlspecialchars($product['tenDM'] ?? 'Laptop') ?>
                </a>
            </li>
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">
                <?= htmlspecialchars($product['tenSP']) ?>
            </li>
        </ol>
    </nav>

    <div class="row g-4 mb-5">
        <!-- CỘT TRÁI: ẢNH LAPTOP, TRẠNG THÁI KHO (CÒN HÀNG / TẠM HẾT), CHÍNH SÁCH BẢO HÀNH CHÍNH HÃNG -->
        <div class="col-lg-5">
            <!-- Khối Ảnh và Trạng thái kho -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                <div class="p-4">
                    <!-- Khung Badge trên cùng tách biệt, không bao giờ bị đè -->
                    <div class="d-flex justify-content-between align-items-center w-100 mb-3">
                        <!-- Badge Hãng -->
                        <span class="badge bg-primary px-3 py-2 fs-6 fw-bold shadow-sm">
                            <i class="bi bi-laptop me-1"></i><?= htmlspecialchars($product['tenTH'] ?? '') ?>
                        </span>

                        <!-- Trạng thái kho: Còn hàng / Tạm hết -->
                        <div>
                            <?php if ((int)$product['soLuongTon'] > 0): ?>
                                <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6 fw-bold shadow-sm">
                                    <i class="bi bi-check-circle-fill me-1"></i> Còn hàng (<?= (int)$product['soLuongTon'] ?> máy)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary px-3 py-2 fs-6 fw-bold shadow-sm">
                                    <i class="bi bi-slash-circle me-1"></i> Tạm hết hàng
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php 
                    $prodImg = !empty($product['hinhAnh']) ? htmlspecialchars($product['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($product['maSP']) . '.jpg');
                    ?>
                    <!-- Khung Ảnh chụp sản phẩm trung thực, sắc nét -->
                    <div class="product-detail-img-box d-flex align-items-center justify-content-center w-100 rounded-3 overflow-hidden bg-light p-2 shadow-xs border" style="min-height: 320px; max-height: 380px;">
                        <img src="<?= $prodImg ?>" 
                             alt="<?= htmlspecialchars($product['tenSP']) ?>" 
                             class="img-fluid rounded-2" 
                             style="max-height: 350px; width: 100%; object-fit: contain; transition: transform 0.3s ease;"
                             onmouseover="this.style.transform='scale(1.02)'"
                             onmouseout="this.style.transform='scale(1)'"
                             onerror="this.onerror=null;this.src='assets/images/products/SP004.jpg';">
                    </div>
                    <div class="text-center text-muted small mt-2">
                        <i class="bi bi-camera me-1"></i> Ảnh chụp thực tế sản phẩm chính hãng tại showroom
                    </div>
                </div>
            </div>

            <!-- Khối Chính sách bảo hành chính hãng -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-primary fs-5"></i> Chính Sách Bảo Hành Chính Hãng
                </h6>
                <div class="d-flex flex-column gap-3 small text-secondary">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-patch-check-fill text-success fs-5 mt-n1"></i>
                        <div>
                            <strong class="text-dark d-block">Bảo hành chính hãng <?= (int)($product['thoiGianBaoHanh'] ?? 12) ?> tháng</strong>
                            Cam kết linh kiện chính hãng 100%, bảo hành tại hệ thống ủy quyền toàn quốc.
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-arrow-repeat text-primary fs-5 mt-n1"></i>
                        <div>
                            <strong class="text-dark d-block">Lỗi 1 đổi 1 trong 30 ngày đầu</strong>
                            Nếu máy phát sinh lỗi kỹ thuật phần cứng do nhà sản xuất, đổi ngay máy mới.
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-upc-scan text-warning fs-5 mt-n1"></i>
                        <div>
                            <strong class="text-dark d-block">Bảo hành điện tử theo Serial / IMEI</strong>
                            Tra cứu thời hạn và lịch sử bảo hành trực tuyến 24/7 chỉ với mã máy hoặc số điện thoại.
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-tools text-info fs-5 mt-n1"></i>
                        <div>
                            <strong class="text-dark d-block">Hỗ trợ kỹ thuật & vệ sinh máy trọn đời</strong>
                            Miễn phí cài đặt hệ điều hành, phần mềm văn phòng và vệ sinh tra keo tản nhiệt.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: TÊN MÁY, GIÁ BÁN TO NỔI BẬT, Ô CHỌN SỐ LƯỢNG, NÚT "THÊM VÀO GIỎ HÀNG" VÀ "MUA NGAY" -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white h-100">
                <!-- Tên Laptop -->
                <h3 class="fw-extrabold text-dark mb-2" style="line-height: 1.3;">
                    <?= htmlspecialchars($product['tenSP']) ?>
                </h3>

                <!-- Thông tin phụ: Mã SP, Hãng, Xuất xứ -->
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3 text-secondary small">
                    <span>Mã SP: <code class="text-primary fw-bold"><?= htmlspecialchars($product['maSP']) ?></code></span>
                    <span>&bull;</span>
                    <span>Thương hiệu: <strong class="text-dark"><?= htmlspecialchars($product['tenTH'] ?? 'Chính hãng') ?></strong></span>
                    <span>&bull;</span>
                    <span>Dòng máy: <strong class="text-dark"><?= htmlspecialchars($product['tenDM'] ?? 'Laptop') ?></strong></span>
                </div>

                <!-- Đánh giá sao -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
                    <div class="text-warning fs-6">
                        <?php 
                        $avg = (float)($ratingSummary['avgRating'] ?? 5.0);
                        for ($i = 1; $i <= 5; $i++): 
                            if ($i <= round($avg)): ?>
                                <i class="bi bi-star-fill"></i>
                            <?php else: ?>
                                <i class="bi bi-star"></i>
                            <?php endif; 
                        endfor; ?>
                    </div>
                    <span class="fw-bold text-dark"><?= number_format($avg, 1) ?></span>
                    <span class="text-muted small">
                        (<a href="#reviews-section" class="text-decoration-none text-primary fw-semibold"><?= (int)($ratingSummary['totalReviews'] ?? 0) ?> đánh giá</a>)
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success ms-2 small">
                        <i class="bi bi-patch-check-fill me-1"></i> Hàng Mới 100% Nguyên Seal
                    </span>
                </div>

                <!-- GIÁ BÁN TO NỔI BẬT -->
                <div class="p-3 bg-light rounded-3 mb-4 d-flex align-items-baseline gap-3">
                    <span class="text-secondary small fw-bold text-uppercase">Giá bán chính hãng:</span>
                    <span class="fs-1 fw-extrabold text-danger font-monospace">
                        <?= number_format((float)$product['giaBan'], 0, ',', '.') ?> đ
                    </span>
                    <span class="badge bg-danger-subtle text-danger fw-bold ms-auto">Đã gồm VAT</span>
                </div>

                <!-- FORM CHỌN SỐ LƯỢNG VÀ NÚT THÊM VÀO GIỎ / MUA NGAY -->
                <?php $isOutOfStock = ((int)$product['soLuongTon'] <= 0); ?>
                <form action="index.php?action=cart-add" method="POST" class="mb-4">
                    <input type="hidden" name="maSP" value="<?= htmlspecialchars($product['maSP']) ?>">

                    <!-- Ô chọn số lượng -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary mb-2">Số lượng đặt mua:</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="input-group" style="width: 140px;">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="var q=document.getElementById('inputQty'); if(parseInt(q.value)>1) q.value=parseInt(q.value)-1;" 
                                        <?= $isOutOfStock ? 'disabled' : '' ?>>
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" id="inputQty" name="quantity" class="form-control text-center fw-bold font-monospace" 
                                       value="1" min="1" max="<?= max(1, (int)$product['soLuongTon']) ?>" <?= $isOutOfStock ? 'disabled' : '' ?>>
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="var q=document.getElementById('inputQty'); if(parseInt(q.value) < <?= (int)$product['soLuongTon'] ?>) q.value=parseInt(q.value)+1;" 
                                        <?= $isOutOfStock ? 'disabled' : '' ?>>
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <span class="small text-muted">
                                <?php if (!$isOutOfStock): ?>
                                    (Kho sẵn có: <strong class="text-success"><?= (int)$product['soLuongTon'] ?></strong> sản phẩm)
                                <?php else: ?>
                                    <span class="text-danger fw-bold"><i class="bi bi-slash-circle me-1"></i> Tạm hết hàng</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Nút Thêm vào giỏ hàng và Mua ngay -->
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-outline-primary btn-spring btn-lg fw-bold w-100 py-3 rounded-3" <?= $isOutOfStock ? 'disabled' : '' ?>>
                                <i class="bi bi-cart-plus fs-5 me-1"></i> Thêm Vào Giỏ Hàng
                            </button>
                        </div>
                        <div class="col-sm-6">
                            <button type="submit" name="buy_now" value="1" class="btn btn-shimmer btn-lg fw-extrabold w-100 py-3 rounded-3 shadow text-uppercase" <?= $isOutOfStock ? 'disabled' : '' ?>>
                                <i class="bi bi-lightning-charge-fill me-1"></i> Mua Ngay
                            </button>
                        </div>
                    </div>

                    <?php if ($isOutOfStock): ?>
                        <div class="alert alert-warning text-center py-2 small fw-semibold mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Sản phẩm hiện đang tạm hết hàng. Quý khách vui lòng liên hệ hotline <strong>1900 6868</strong> để được hỗ trợ đặt trước!
                        </div>
                    <?php endif; ?>
                </form>

                <!-- KHỐI QUÀ TẶNG & ƯU ĐÃI ĐẶC QUYỀN KHI MUA HÀNG -->
                <div class="border rounded-3 p-3 bg-light-subtle">
                    <h6 class="fw-bold text-danger mb-2">
                        <i class="bi bi-gift-fill me-1"></i> Ưu đãi đặc quyền khi đặt mua online:
                    </h6>
                    <ul class="mb-0 small text-secondary ps-3" style="line-height: 1.8;">
                        <li>Nhập mã voucher <strong>KM_BACK2SCHOOL</strong> giảm ngay 5% (tối đa 1.500.000đ).</li>
                        <li>Tặng kèm Balo laptop chống sốc cao cấp & Chuột không dây chính hãng.</li>
                        <li>Miễn phí giao hàng tận nơi toàn quốc - Kiểm tra máy trước khi thanh toán.</li>
                        <li>Hỗ trợ chuyển đổi trả góp 0% lãi suất qua thẻ tín dụng.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- BÊN DƯỚI: BẢNG THÔNG SỐ KỸ THUẬT VÀ MÔ TẢ -->
    <!-- ========================================== -->
    <div class="row g-4 mb-5">
        <!-- BẢNG THÔNG SỐ KỸ THUẬT CHI TIẾT RÕ TỪNG LINH KIỆN -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-cpu text-primary me-2"></i>Bảng Thông Số Kỹ Thuật Chi Tiết
                    </h5>
                    <span class="badge bg-light text-dark border">Cấu hình xuất xưởng</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-4 text-secondary fw-semibold" style="width: 35%;">Hãng sản xuất:</th>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($product['tenTH'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-4 text-secondary fw-semibold">Dòng sản phẩm:</th>
                                <td><?= htmlspecialchars($product['tenDM'] ?? '') ?></td>
                            </tr>
                            <?php if (!empty($product['specs']['cpu'])): ?>
                                <tr>
                                    <th class="ps-4 text-secondary fw-semibold">Bộ vi xử lý (CPU):</th>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($product['specs']['cpu']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['specs']['ram'])): ?>
                                <tr>
                                    <th class="ps-4 text-secondary fw-semibold">Bộ nhớ trong (RAM):</th>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($product['specs']['ram']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['specs']['ssd'])): ?>
                                <tr>
                                    <th class="ps-4 text-secondary fw-semibold">Ổ cứng lưu trữ:</th>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($product['specs']['ssd']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['specs']['vga'])): ?>
                                <tr>
                                    <th class="ps-4 text-secondary fw-semibold">Card đồ họa (VGA):</th>
                                    <td class="fw-bold text-danger"><?= htmlspecialchars($product['specs']['vga']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['specs']['screen'])): ?>
                                <tr>
                                    <th class="ps-4 text-secondary fw-semibold">Màn hình hiển thị:</th>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($product['specs']['screen']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th class="ps-4 text-secondary fw-semibold">Thời hạn bảo hành:</th>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success fw-bold">
                                        <?= (int)($product['thoiGianBaoHanh'] ?? 12) ?> tháng chính hãng
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-4 text-secondary fw-semibold">Hình thức quản lý:</th>
                                <td class="text-muted small">Bảo hành điện tử theo số Serial / IMEI</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BÀI VIẾT ĐÁNH GIÁ MÔ TẢ MÁY -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-file-text text-primary me-2"></i>Đặc Điểm & Mô Tả Sản Phẩm
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-secondary" style="line-height: 1.8;">
                        <?= nl2br(htmlspecialchars($product['moTa'] ?? 'Đang cập nhật bài viết chi tiết cho sản phẩm này.')) ?>
                    </p>
                    <div class="alert alert-info border-0 rounded-3 small mb-0 mt-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Máy bán ra nguyên seal/nguyên hộp 100%, kèm củ cáp sạc zin theo máy, sổ hướng dẫn sử dụng và phiếu xuất kho kiêm bảo hành điện tử.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PHÂN HỆ ĐÁNH GIÁ & CHẤM ĐIỂM SAO KHÁCH HÀNG -->
    <!-- ========================================== -->
    <div id="reviews-section" class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-chat-heart text-danger me-2"></i>Khách Hàng Đánh Giá & Nhận Xét
            </h5>
            <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fw-bold">
                <?= (int)($ratingSummary['totalReviews'] ?? 0) ?> Đánh Giá
            </span>
        </div>

        <div class="card-body p-4">
            <!-- Thông báo flash -->
            <?php if (!empty($_SESSION['flash_review_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div><?= htmlspecialchars($_SESSION['flash_review_success']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_review_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_review_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div><?= htmlspecialchars($_SESSION['flash_review_error']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_review_error']); ?>
            <?php endif; ?>

            <!-- THỐNG KÊ ĐIỂM SỐ VÀ CÁC MỨC SAO -->
            <div class="row g-4 align-items-center mb-4 pb-4 border-bottom">
                <div class="col-md-4 text-center border-end">
                    <div class="display-3 fw-bold text-warning mb-1">
                        <?= number_format((float)($ratingSummary['avgRating'] ?? 5.0), 1) ?>
                    </div>
                    <div class="text-warning fs-5 mb-2">
                        <?php 
                        $avg = (float)($ratingSummary['avgRating'] ?? 5.0);
                        for ($i = 1; $i <= 5; $i++): 
                            if ($i <= round($avg)): ?>
                                <i class="bi bi-star-fill"></i>
                            <?php else: ?>
                                <i class="bi bi-star"></i>
                            <?php endif; 
                        endfor; ?>
                    </div>
                    <div class="text-muted small">
                        Dựa trên <strong><?= (int)($ratingSummary['totalReviews'] ?? 0) ?></strong> đánh giá từ người dùng
                    </div>
                </div>

                <!-- THANH TỈ LỆ SAO (5 -> 1) -->
                <div class="col-md-5">
                    <?php 
                    $totalR = (int)($ratingSummary['totalReviews'] ?? 0);
                    $starsCount = $ratingSummary['starsCount'] ?? [];
                    for ($s = 5; $s >= 1; $s--):
                        $cnt = (int)($starsCount[$s] ?? 0);
                        $pct = $totalR > 0 ? round(($cnt / $totalR) * 100) : 0;
                    ?>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="small fw-semibold text-secondary" style="width: 45px;"><?= $s ?> <i class="bi bi-star-fill text-warning"></i></span>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="small text-muted font-monospace" style="width: 55px; text-align: right;"><?= $cnt ?> (<?= $pct ?>%)</span>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- NÚT MỞ FORM ĐÁNH GIÁ -->
                <div class="col-md-3 text-center">
                    <p class="text-secondary small mb-3">Bạn đã hoặc đang sử dụng mẫu laptop này?</p>
                    <a href="#formReviewCollapse" class="btn btn-outline-danger fw-bold px-3 py-2 shadow-sm rounded-pill" data-bs-toggle="collapse" role="button" aria-expanded="false">
                        <i class="bi bi-pencil-square me-1"></i> Viết Đánh Giá Ngay
                    </a>
                </div>
            </div>

            <!-- FORM GỬI ĐÁNH GIÁ MỚI (COLLAPSIBLE HOẶC MỞ SẴN) -->
            <div class="collapse mb-4" id="formReviewCollapse">
                <div class="p-4 bg-light rounded-4 border">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-send-fill text-danger me-2"></i>Gửi Đánh Giá Của Bạn Cho Sản Phẩm Này
                    </h6>
                    <form action="index.php?action=review-submit" method="POST">
                        <input type="hidden" name="maSP" value="<?= htmlspecialchars($product['maSP']) ?>">
                        
                        <!-- CHỌN SỐ SAO -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Bạn đánh giá sản phẩm này mấy sao? <span class="text-danger">*</span></label>
                            <input type="hidden" name="soSao" id="inputRatingScore" value="5">
                            <div class="d-flex align-items-center gap-2" id="starPickerContainer">
                                <div class="rating-stars-interactive fs-3 text-warning cursor-pointer" style="cursor: pointer;">
                                    <i class="bi bi-star-fill star-item" data-value="1"></i>
                                    <i class="bi bi-star-fill star-item" data-value="2"></i>
                                    <i class="bi bi-star-fill star-item" data-value="3"></i>
                                    <i class="bi bi-star-fill star-item" data-value="4"></i>
                                    <i class="bi bi-star-fill star-item" data-value="5"></i>
                                </div>
                                <span class="badge bg-warning text-dark fw-bold px-2 py-1" id="starPickerLabel">5 Sao - Cực kỳ hài lòng</span>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Họ và tên của bạn: <span class="text-danger">*</span></label>
                                <input type="text" name="hoTen" class="form-control" placeholder="Ví dụ: Nguyễn Văn A" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Số điện thoại (để xác thực đã mua hàng): <span class="text-danger">*</span></label>
                                <input type="tel" name="soDienThoai" class="form-control" placeholder="Ví dụ: 0912345678" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Nội dung cảm nhận thực tế: <span class="text-danger">*</span></label>
                            <textarea name="noiDung" rows="3" class="form-control" placeholder="Chia sẻ cảm nhận về hiệu năng, màn hình, tản nhiệt, thời lượng pin sau khi trải nghiệm máy..." required minlength="10"></textarea>
                            <div class="form-text small text-muted">Nhận xét tối thiểu 10 ký tự, lịch sự và không chứa thông tin quảng cáo.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#formReviewCollapse">Hủy</button>
                            <button type="submit" class="btn btn-danger btn-sm fw-bold px-4">
                                <i class="bi bi-check-lg me-1"></i> Gửi Đánh Giá
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- DANH SÁCH BÌNH LUẬN & ĐÁNH GIÁ THỰC TẾ -->
            <div class="review-list">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div class="p-3 mb-3 border rounded-3 bg-white shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <!-- Avatar initials -->
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                        <?= mb_strtoupper(mb_substr(trim($rev['hoTen']), 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0">
                                            <?= htmlspecialchars($rev['hoTen']) ?>
                                            <span class="badge bg-success-subtle text-success border border-success ms-1 small" style="font-size: 0.7rem;">
                                                <i class="bi bi-check2-circle"></i> Đã mua tại cửa hàng
                                            </span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($rev['ngayDanhGia'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- Số sao của review -->
                                <div class="text-warning fs-6">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <?php if ($s <= (int)$rev['soSao']): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star text-muted opacity-25"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-secondary mb-0 small ps-5" style="line-height: 1.6;">
                                <?= nl2br(htmlspecialchars($rev['noiDung'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4 bg-light rounded-3 text-muted">
                        <i class="bi bi-chat-square-dots display-6 d-block mb-2 text-secondary opacity-50"></i>
                        <p class="mb-2 fw-semibold">Chưa có đánh giá nào cho sản phẩm này.</p>
                        <p class="small text-secondary mb-3">Hãy là khách hàng đầu tiên chia sẻ trải nghiệm sử dụng mẫu laptop này!</p>
                        <a href="#formReviewCollapse" class="btn btn-outline-danger btn-sm fw-bold px-3" data-bs-toggle="collapse">
                            <i class="bi bi-pencil me-1"></i> Để Lại Đánh Giá Đầu Tiên
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    // Xử lý chọn sao tương tác (Interactive Star Rating)
    document.addEventListener('DOMContentLoaded', function() {
        const starLabels = {
            1: "1 Sao - Rất không hài lòng",
            2: "2 Sao - Chưa hài lòng",
            3: "3 Sao - Bình thường",
            4: "4 Sao - Rất hài lòng",
            5: "5 Sao - Cực kỳ hài lòng"
        };
        const starItems = document.querySelectorAll('#starPickerContainer .star-item');
        const hiddenInput = document.getElementById('inputRatingScore');
        const labelText = document.getElementById('starPickerLabel');

        if (starItems.length > 0 && hiddenInput) {
            function updateStars(val) {
                hiddenInput.value = val;
                if (labelText) labelText.textContent = starLabels[val] || (val + " Sao");
                starItems.forEach(item => {
                    const itemVal = parseInt(item.getAttribute('data-value'));
                    if (itemVal <= val) {
                        item.classList.remove('bi-star');
                        item.classList.add('bi-star-fill');
                    } else {
                        item.classList.remove('bi-star-fill');
                        item.classList.add('bi-star');
                    }
                });
            }

            starItems.forEach(item => {
                item.addEventListener('click', function() {
                    const val = parseInt(this.getAttribute('data-value'));
                    updateStars(val);
                });
                item.addEventListener('mouseenter', function() {
                    const hoverVal = parseInt(this.getAttribute('data-value'));
                    starItems.forEach(i => {
                        const iVal = parseInt(i.getAttribute('data-value'));
                        if (iVal <= hoverVal) {
                            i.classList.remove('bi-star');
                            i.classList.add('bi-star-fill');
                        } else {
                            i.classList.remove('bi-star-fill');
                            i.classList.add('bi-star');
                        }
                    });
                });
            });

            const pickerContainer = document.getElementById('starPickerContainer');
            if (pickerContainer) {
                pickerContainer.addEventListener('mouseleave', function() {
                    updateStars(parseInt(hiddenInput.value) || 5);
                });
            }
        }
    });
    </script>

    <!-- SẢN PHẨM CÙNG DANH MỤC LIÊN QUAN -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="mt-5 border-top pt-4">
            <h4 class="fw-bold text-dark mb-4">
                <i class="bi bi-grid-fill text-primary me-2"></i>Sản Phẩm Cùng Dòng Bạn Có Thể Thích
            </h4>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
                <?php foreach ($relatedProducts as $rp): ?>
                    <?php 
                    $rpImg = !empty($rp['hinhAnh']) ? htmlspecialchars($rp['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($rp['maSP']) . '.jpg');
                    ?>
                    <div class="col">
                        <div class="laptop-card shadow-sm">
                            <div class="card-badges-bar d-flex justify-content-between align-items-center px-3 pt-3 pb-1">
                                <span class="badge bg-secondary fw-semibold">
                                    <?= htmlspecialchars($rp['tenTH'] ?? '') ?>
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success small fw-semibold">Sẵn máy</span>
                            </div>
                            <div class="card-thumb-wrapper" style="height: 195px;">
                                <a href="index.php?action=client-product-detail&id=<?= urlencode($rp['maSP']) ?>" class="d-flex align-items-center justify-content-center w-100 h-100">
                                    <img src="<?= $rpImg ?>" alt="<?= htmlspecialchars($rp['tenSP']) ?>" class="product-thumb-img" onerror="this.onerror=null;this.src='assets/images/products/SP004.jpg';">
                                </a>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="fw-bold text-dark text-truncate mb-1">
                                    <a href="index.php?action=client-product-detail&id=<?= urlencode($rp['maSP']) ?>" class="text-dark text-decoration-none hover-primary">
                                        <?= htmlspecialchars($rp['tenSP']) ?>
                                    </a>
                                </h6>
                                <div class="price-tag fs-6 mb-2">
                                    <?= number_format((float)$rp['giaBan'], 0, ',', '.') ?> đ
                                </div>
                                <a href="index.php?action=client-product-detail&id=<?= urlencode($rp['maSP']) ?>" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                                    Xem Ngay
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
