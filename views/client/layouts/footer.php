<?php
/**
 * File: views/client/layouts/footer.php
 * Footer chuẩn dùng chung cho giao diện khách hàng
 */
?>
</main> <!-- End .main-wrapper -->

<!-- FOOTER -->
<footer class="bg-dark text-white pt-5 pb-4 mt-5 border-top border-secondary border-opacity-25">
    <div class="container">
        <div class="row g-4">
            <!-- Cột 1: Thông tin cửa hàng -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-laptop fs-3 text-primary"></i>
                    <h5 class="fw-bold mb-0 text-white">EMSHU STORE SYSTEM</h5>
                </div>
                <p class="text-secondary small mb-3">
                    Hệ thống bán lẻ máy tính xách tay, linh kiện và phụ kiện công nghệ chính hãng hàng đầu Việt Nam. Cam kết chất lượng, bảo hành tận tâm và giá thành cạnh tranh nhất thị trường.
                </p>
                <div class="text-secondary small">
                    <div class="mb-1"><i class="bi bi-geo-alt text-primary me-2"></i> 123 Đường Công Nghệ, Cầu Giấy, Hà Nội</div>
                    <div class="mb-1"><i class="bi bi-telephone text-primary me-2"></i> Hotline: <strong>1900 6868</strong> (8h00 - 21h30)</div>
                    <div><i class="bi bi-envelope text-primary me-2"></i> Email: support@emshustore.vn</div>
                </div>
            </div>

            <!-- Cột 2: Danh mục sản phẩm -->
            <div class="col-lg-2 col-md-6">
                <h6 class="text-uppercase fw-bold mb-3 text-white">Dòng Sản Phẩm</h6>
                <ul class="list-unstyled text-secondary small">
                    <li class="mb-2"><a href="index.php?action=client-home&category=1" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1"></i> Laptop Gaming</a></li>
                    <li class="mb-2"><a href="index.php?action=client-home&category=2" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1"></i> Laptop Học Tập - VP</a></li>
                    <li class="mb-2"><a href="index.php?action=client-home&category=3" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1"></i> MacBook Apple</a></li>
                    <li class="mb-2"><a href="index.php?action=client-home&category=4" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1"></i> Đồ Họa - Kỹ Thuật</a></li>
                    <li><a href="index.php?action=client-home" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1"></i> Tất cả Laptop</a></li>
                </ul>
            </div>

            <!-- Cột 3: Chính sách mua hàng & bảo hành -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-uppercase fw-bold mb-3 text-white">Chính Sách & Hỗ Trợ</h6>
                <ul class="list-unstyled text-secondary small">
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-white"><i class="bi bi-shield-check me-1"></i> Chính sách bảo hành chính hãng</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-white"><i class="bi bi-arrow-repeat me-1"></i> Chính sách đổi mới 30 ngày</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-white"><i class="bi bi-truck me-1"></i> Vận chuyển & Giao nhận toàn quốc</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-white"><i class="bi bi-credit-card me-1"></i> Phương thức thanh toán an toàn</a></li>
                    <li><a href="#" class="text-secondary text-decoration-none hover-white"><i class="bi bi-qr-code me-1"></i> Tra cứu bảo hành theo Serial/IMEI</a></li>
                </ul>
            </div>

            <!-- Cột 4: Phương thức thanh toán & Đăng ký khuyến mãi -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-uppercase fw-bold mb-3 text-white">Thanh Toán & Kết Nối</h6>
                <div class="d-flex gap-2 mb-3">
                    <span class="badge bg-secondary p-2"><i class="bi bi-cash-stack fs-6"></i> Tiền mặt (COD)</span>
                    <span class="badge bg-primary p-2"><i class="bi bi-qr-code-scan fs-6"></i> Chuyển khoản QR</span>
                </div>
                <p class="text-secondary small mb-2">Đăng ký nhận mã giảm giá đặc biệt:</p>
                <div class="input-group mb-3">
                    <input type="email" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Nhập email của bạn...">
                    <button class="btn btn-primary btn-sm fw-bold px-3" type="button">Đăng ký</button>
                </div>
            </div>
        </div>

        <hr class="border-secondary border-opacity-25 my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary">
            <div>&copy; <?= date('Y') ?> <strong>EmShu Store System</strong>. Tất cả quyền được bảo lưu.</div>
            <div class="mt-2 mt-md-0">
                <span class="me-3">Hệ thống PHP MVC thuần & PDO Database</span>
                <span>Phiên bản 2.5 Pro</span>
        </div>
    </div>
</footer>

<!-- FLOATING QUICK DOCK (ANIMATE UI SPEED DIAL) -->
<div class="floating-quick-dock d-none d-sm-flex">
    <a href="tel:19006868" class="dock-btn dock-btn-hotline" title="Hotline tư vấn 24/7">
        <i class="bi bi-telephone-fill"></i>
        <span class="dock-tooltip">Hotline: 1900 6868</span>
    </a>
    <a href="index.php?action=track-order" class="dock-btn" title="Tra cứu tiến độ giao hàng">
        <i class="bi bi-truck text-warning"></i>
        <span class="dock-tooltip">Tra cứu đơn hàng</span>
    </a>
    <a href="index.php?action=check-warranty" class="dock-btn" title="Kiểm tra bảo hành điện tử">
        <i class="bi bi-shield-check text-success"></i>
        <span class="dock-tooltip">Bảo hành điện tử</span>
    </a>
    <button type="button" id="btnScrollToTop" class="dock-btn" title="Lên đầu trang">
        <i class="bi bi-arrow-up"></i>
        <span class="dock-tooltip">Về đầu trang</span>
    </button>
</div>

<!-- ============================================================== -->
<!-- TOAST THÔNG BÁO THÊM GIỎ HÀNG AJAX SINH ĐỘNG (ANIMATE UI)     -->
<!-- ============================================================== -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080; margin-top: 60px;">
    <div id="cartToast" class="toast border-0 shadow-lg rounded-4 overflow-hidden" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4500">
        <div class="toast-header bg-success text-white py-2 px-3">
            <i class="bi bi-check-circle-fill me-2 fs-6"></i>
            <strong class="me-auto">Thêm vào giỏ thành công!</strong>
            <small class="text-white opacity-75">Vừa xong</small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <img id="toastProductImg" src="assets/images/products/SP001.jpg" alt="" class="rounded border p-1" style="width: 58px; height: 58px; object-fit: contain; background: #fff;">
                <div class="flex-grow-1 overflow-hidden">
                    <div id="toastProductName" class="fw-bold text-dark text-truncate small mb-1">Tên laptop</div>
                    <div id="toastProductPrice" class="text-danger fw-extrabold font-monospace small mb-2">0 đ</div>
                    <div class="d-flex gap-2">
                        <a href="index.php?action=cart" class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold" style="font-size: 11px;">
                            <i class="bi bi-cart3 me-1"></i> Xem Giỏ Hàng
                        </a>
                        <a href="index.php?action=checkout" class="btn btn-sm btn-primary py-1 px-2 fw-semibold" style="font-size: 11px;">
                            Thanh Toán Ngay &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL XEM NHANH SẢN PHẨM (QUICK VIEW MODAL)                    -->
<!-- ============================================================== -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1.5" id="qvBrand">Hãng</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div class="row g-4 align-items-center">
                    <!-- Ảnh laptop lớn -->
                    <div class="col-md-6 text-center">
                        <div class="p-3 bg-light rounded-4 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                            <img id="qvImage" src="assets/images/products/SP001.jpg" alt="" class="img-fluid" style="max-height: 230px; object-fit: contain; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.12));">
                        </div>
                        <div class="small text-muted mt-2">
                            <i class="bi bi-shield-check text-success me-1"></i> Bảo hành <strong id="qvWarranty">12</strong> tháng chính hãng
                        </div>
                    </div>

                    <!-- Thông tin chi tiết -->
                    <div class="col-md-6">
                        <h5 class="fw-bold text-dark mb-1" id="qvTitle">Tên Laptop</h5>
                        <div class="small text-muted mb-3" id="qvCategory">Danh mục</div>

                        <div class="p-3 bg-light rounded-3 mb-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small text-uppercase" style="font-size: 11px;">Giá niêm yết:</div>
                                <div class="fs-4 fw-extrabold text-danger font-monospace mb-0" id="qvPrice">0 đ</div>
                            </div>
                            <div id="qvStockBadge">
                                <span class="badge bg-success-subtle text-success border border-success">Sẵn máy</span>
                            </div>
                        </div>

                        <!-- Cấu hình nhanh -->
                        <div class="mb-3">
                            <div class="text-muted small fw-bold mb-2">Cấu hình chi tiết:</div>
                            <div class="d-flex flex-wrap gap-1" id="qvSpecsPills">
                                <!-- Specs pills will be injected here -->
                            </div>
                        </div>

                        <!-- Thêm vào giỏ hàng -->
                        <div class="d-flex gap-2 align-items-center mt-4">
                            <div style="width: 90px;">
                                <input type="number" id="qvQuantity" class="form-control text-center fw-bold font-monospace" value="1" min="1" max="10">
                            </div>
                            <button type="button" id="qvBtnAddToCart" class="btn btn-primary fw-bold flex-grow-1 py-2">
                                <i class="bi bi-cart-plus me-1"></i> Thêm Vào Giỏ
                            </button>
                        </div>
                        <div class="mt-2 text-center">
                            <a id="qvFullDetailLink" href="#" class="text-decoration-none small fw-semibold text-secondary hover-primary">
                                Xem trang chi tiết đầy đủ &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .hover-white:hover {
        color: #ffffff !important;
        padding-left: 4px;
        transition: all 0.2s ease;
    }
    .hover-primary:hover {
        color: var(--primary) !important;
    }
    .badge-pop {
        transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
</style>

<!-- SCRIPTS TƯƠNG TÁC CAO (AJAX CART & QUICK VIEW) -->
<script>
let cartToastInstance = null;
let quickViewModalInstance = null;
let currentQuickViewMaSP = '';

document.addEventListener('DOMContentLoaded', function() {
    const toastEl = document.getElementById('cartToast');
    if (toastEl) cartToastInstance = new bootstrap.Toast(toastEl);

    const qvModalEl = document.getElementById('quickViewModal');
    if (qvModalEl) quickViewModalInstance = new bootstrap.Modal(qvModalEl);

    // Xử lý nút Add to Cart trong Quick View Modal
    const qvBtn = document.getElementById('qvBtnAddToCart');
    if (qvBtn) {
        qvBtn.addEventListener('click', function() {
            const qty = parseInt(document.getElementById('qvQuantity').value) || 1;
            if (currentQuickViewMaSP) {
                addToCartAjax(currentQuickViewMaSP, qty, qvBtn);
                if (quickViewModalInstance) quickViewModalInstance.hide();
            }
        });
    }
});

/**
 * Thêm vào giỏ hàng bằng Ajax không reload trang
 */
function addToCartAjax(maSP, quantity = 1, buttonEl = null) {
    let originalHtml = '';
    if (buttonEl) {
        originalHtml = buttonEl.innerHTML;
        buttonEl.disabled = true;
        buttonEl.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
    }

    const formData = new URLSearchParams();
    formData.append('maSP', maSP);
    formData.append('quantity', quantity);

    fetch('index.php?action=cart-add', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (buttonEl) {
            buttonEl.disabled = false;
            buttonEl.innerHTML = originalHtml;
        }

        if (data.success) {
            // Cập nhật số lượng giỏ hàng trên Header với hiệu ứng Pop
            const headerBadge = document.getElementById('headerCartCount');
            if (headerBadge) {
                headerBadge.textContent = data.cartCount;
                headerBadge.style.transform = 'scale(1.4)';
                setTimeout(() => { headerBadge.style.transform = 'scale(1)'; }, 250);
            }

            // Hiển thị Toast thông báo đẹp mắt
            if (data.product) {
                document.getElementById('toastProductName').textContent = data.product.tenSP;
                document.getElementById('toastProductPrice').textContent = new Intl.NumberFormat('vi-VN').format(data.product.giaBan) + ' đ';
                if (data.product.hinhAnh) {
                    document.getElementById('toastProductImg').src = data.product.hinhAnh;
                }
            }
            if (cartToastInstance) {
                cartToastInstance.show();
            }
        } else {
            alert(data.message || 'Không thể thêm sản phẩm vào giỏ!');
        }
    })
    .catch(err => {
        if (buttonEl) {
            buttonEl.disabled = false;
            buttonEl.innerHTML = originalHtml;
        }
        console.error('Add to cart error:', err);
    });
}

/**
 * Mở modal Xem Nhanh Sản Phẩm
 */
function openQuickView(maSP) {
    currentQuickViewMaSP = maSP;
    if (quickViewModalInstance) {
        // Reset dữ liệu trước khi tải
        document.getElementById('qvTitle').textContent = 'Đang tải thông tin...';
        document.getElementById('qvSpecsPills').innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span>';
        document.getElementById('qvQuantity').value = 1;
        quickViewModalInstance.show();
    }

    fetch('index.php?action=product-quickview&id=' + encodeURIComponent(maSP))
        .then(res => res.json())
        .then(res => {
            if (res.success && res.product) {
                const p = res.product;
                document.getElementById('qvTitle').textContent = p.tenSP;
                document.getElementById('qvCategory').textContent = p.tenDM;
                document.getElementById('qvBrand').textContent = p.tenTH;
                document.getElementById('qvPrice').textContent = p.giaBanFormatted;
                document.getElementById('qvWarranty').textContent = p.thoiGianBaoHanh;
                document.getElementById('qvImage').src = p.hinhAnh;
                document.getElementById('qvFullDetailLink').href = 'index.php?action=client-product-detail&id=' + encodeURIComponent(p.maSP);

                // Tình trạng kho
                if (p.soLuongTon > 0) {
                    document.getElementById('qvStockBadge').innerHTML = '<span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check2 me-1"></i>Còn ' + p.soLuongTon + ' máy</span>';
                    document.getElementById('qvBtnAddToCart').disabled = false;
                } else {
                    document.getElementById('qvStockBadge').innerHTML = '<span class="badge bg-danger">Tạm hết hàng</span>';
                    document.getElementById('qvBtnAddToCart').disabled = true;
                }

                // Cấu hình Specs Pills
                let specsHtml = '';
                if (p.specs) {
                    if (p.specs.cpu) specsHtml += '<span class="spec-pill"><i class="bi bi-cpu me-1"></i>' + p.specs.cpu + '</span>';
                    if (p.specs.ram) specsHtml += '<span class="spec-pill"><i class="bi bi-memory me-1"></i>' + p.specs.ram + '</span>';
                    if (p.specs.ssd) specsHtml += '<span class="spec-pill"><i class="bi bi-device-hdd me-1"></i>' + p.specs.ssd + '</span>';
                    if (p.specs.vga) specsHtml += '<span class="spec-pill text-danger fw-bold"><i class="bi bi-gpu-card me-1"></i>' + p.specs.vga + '</span>';
                    if (p.specs.screen) specsHtml += '<span class="spec-pill"><i class="bi bi-display me-1"></i>' + p.specs.screen + '</span>';
                }
                document.getElementById('qvSpecsPills').innerHTML = specsHtml || '<span class="text-muted small">Đang cập nhật cấu hình</span>';
            }
        })
        .catch(err => {
            console.error('Quick view error:', err);
        });
}
</script>

<!-- Animate UI & System Design Engine JS -->
<script src="assets/js/client-system.js"></script>

</body>
</html>

