<?php
/**
 * File: views/client/home.php
 * Giao diện Trang chủ mua hàng dành cho khách hàng với ảnh sản phẩm chất lượng cao
 */

$pageTitle = "EmShu Store - Hệ Thống Laptop Chính Hãng Hàng Đầu";
require_once __DIR__ . '/layouts/header.php';
?>

<!-- HERO BANNER KHUYẾN MÃI CÔNG NGHỆ CAO CẤP -->
<div class="hero-section py-5">
    <div class="container">
        <div class="row align-items-center py-2">
            <div class="col-lg-6 text-white pe-lg-4">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 mb-3 rounded-pill text-uppercase shadow-sm d-inline-flex align-items-center">
                    <span class="pulse-beacon"></span>
                    <i class="bi bi-stars me-1"></i> Ưu đãi đặc quyền 2026
                </span>
                <h1 class="display-4 fw-extrabold mb-3 text-white" style="line-height: 1.15; letter-spacing: -1px;">
                    Laptop Chính Hãng <br><span class="text-info" style="text-shadow: 0 0 20px rgba(56, 189, 248, 0.4);">Hiệu Năng Vượt Trội</span>
                </h1>
                <p class="lead text-light text-opacity-80 mb-4" style="font-size: 1.1rem;">
                    Sở hữu ngay các dòng laptop Gaming RTX, MacBook M3 và Ultrabook văn phòng chính hãng với chế độ bảo hành 1 đổi 1 trong 30 ngày và trả góp 0%.
                </p>
                
                <!-- Khối Voucher khuyến mãi nổi bật với Border Beam -->
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <div class="border-beam-badge text-white small">
                        <i class="bi bi-ticket-perforated text-warning me-1"></i>
                        <span class="text-warning fw-bold">KM_BACK2SCHOOL:</span> Giảm 5% tối đa 1.500.000đ
                    </div>
                    <div class="border-beam-badge text-white small">
                        <i class="bi bi-ticket-perforated text-danger me-1"></i>
                        <span class="text-danger fw-bold">KM_GAMING1M:</span> Trợ giá 1.000.000đ cho Gaming
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="#featured-products" class="btn btn-shimmer btn-lg fw-bold px-4 py-3 rounded-pill shadow">
                        <i class="bi bi-bag-check me-2"></i> Khám Phá Ngay
                    </a>
                    <a href="index.php?action=client-home&category=1" class="btn btn-outline-light btn-spring btn-lg fw-semibold px-4 py-3 rounded-pill">
                        <i class="bi bi-controller me-2 text-danger"></i> Laptop Gaming
                    </a>
                </div>
            </div>

            <!-- Banner hình ảnh Laptop 3D Showcase -->
            <div class="col-lg-6 mt-4 mt-lg-0 text-center">
                <div class="hero-banner-frame">
                    <img src="assets/images/banners/hero-banner.jpg" 
                         alt="EmShu Store Flagship Showcase" 
                         class="hero-banner-img"
                         onerror="this.style.display='none';">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CONTAINER NỘI DUNG CHÍNH -->
<div class="container my-5" id="featured-products">

    <!-- BỘ LỌC TÌM KIẾM ĐA TIÊU CHÍ -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="index.php" method="GET" class="row g-3 align-items-center">
                <input type="hidden" name="action" value="client-home">

                <!-- Tìm từ khóa -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Tìm kiếm máy:</label>
                    <input type="text" name="keyword" class="form-control" placeholder="Tên máy, mã SP, chip..." 
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                </div>

                <!-- Lọc Danh mục -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Dòng sản phẩm:</label>
                    <select name="category" class="form-select">
                        <option value="">-- Tất cả danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['maDM'] ?>" <?= (isset($_GET['category']) && (string)$_GET['category'] === (string)$cat['maDM']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['tenDM']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Lọc Thương hiệu -->
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Hãng sản xuất:</label>
                    <select name="brand" class="form-select">
                        <option value="">-- Tất cả hãng --</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['maTH'] ?>" <?= (isset($_GET['brand']) && (string)$_GET['brand'] === (string)$b['maTH']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['tenTH']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Lọc Khoảng giá -->
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Mức giá:</label>
                    <select name="price" class="form-select">
                        <option value="">-- Mọi mức giá --</option>
                        <option value="under_15" <?= (($_GET['price'] ?? '') === 'under_15') ? 'selected' : '' ?>>Dưới 15 triệu</option>
                        <option value="15_20" <?= (($_GET['price'] ?? '') === '15_20') ? 'selected' : '' ?>>Từ 15 - 20 triệu</option>
                        <option value="20_30" <?= (($_GET['price'] ?? '') === '20_30') ? 'selected' : '' ?>>Từ 20 - 30 triệu</option>
                        <option value="over_30" <?= (($_GET['price'] ?? '') === 'over_30') ? 'selected' : '' ?>>Trên 30 triệu</option>
                    </select>
                </div>

                <!-- Nút Lọc & Reset -->
                <div class="col-md-2 d-flex gap-2 align-items-end pt-3">
                    <button type="submit" class="btn btn-primary fw-bold w-100">
                        <i class="bi bi-funnel me-1"></i> Lọc
                    </button>
                    <a href="index.php?url=home" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- TIÊU ĐỀ KHU VỰC SẢN PHẨM -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <?php if ($isFiltering): ?>
                    <i class="bi bi-search text-primary me-2"></i>Kết Quả Tìm Kiếm
                    <span class="text-muted fs-6 fw-normal">(<?= count($products) ?> sản phẩm phù hợp)</span>
                <?php else: ?>
                    <i class="bi bi-fire text-danger me-2"></i>Sản Phẩm Laptop Nổi Bật Mới Nhất
                <?php endif; ?>
            </h4>
            <div class="text-secondary small">Trang bị chip thế hệ mới nhất, nguyên seal nguyên hộp 100%</div>
        </div>

        <?php if ($isFiltering): ?>
            <a href="index.php?url=home" class="btn btn-sm btn-outline-primary fw-semibold">
                <i class="bi bi-grid me-1"></i> Xem tất cả danh mục
            </a>
        <?php endif; ?>
    </div>

    <!-- DANH SÁCH SẢN PHẨM DẠNG GRID CARD VỚI ẢNH CHÂN THỰC -->
    <?php if (empty($products)): ?>
        <div class="card border-0 shadow-sm rounded-4 py-5 text-center">
            <div class="card-body">
                <i class="bi bi-laptop text-muted" style="font-size: 64px;"></i>
                <h5 class="fw-bold mt-3 text-secondary">Không tìm thấy sản phẩm nào phù hợp!</h5>
                <p class="text-muted small">Hãy thử tìm kiếm với từ khóa khác hoặc điều chỉnh lại mức giá lọc.</p>
                <a href="index.php?url=home" class="btn btn-primary px-4 fw-semibold mt-2">Xem Tất Cả Laptop</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
            <?php foreach ($products as $p): ?>
                <?php 
                $imgSrc = !empty($p['hinhAnh']) ? htmlspecialchars($p['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($p['maSP']) . '.jpg');
                ?>
                <div class="col">
                    <div class="laptop-card shadow-sm">
                        <!-- Khung Badge trên cùng tách biệt, không bao giờ bị đè -->
                        <div class="card-badges-bar d-flex justify-content-between align-items-center px-3 pt-3 pb-1">
                            <span class="badge bg-primary fw-bold px-2 py-1 shadow-sm">
                                <?= htmlspecialchars($p['tenTH'] ?? '') ?>
                            </span>

                            <?php if ((int)$p['soLuongTon'] <= 0): ?>
                                <span class="badge bg-secondary fw-semibold">
                                    Hết hàng
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success fw-semibold">
                                    <i class="bi bi-check2 me-1"></i>Còn <?= (int)$p['soLuongTon'] ?> máy
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Khung hình ảnh máy chân thực, to & rõ ràng -->
                        <div class="card-thumb-wrapper">
                            <a href="index.php?action=client-product-detail&id=<?= urlencode($p['maSP']) ?>" class="d-flex align-items-center justify-content-center w-100 h-100">
                                <img src="<?= $imgSrc ?>" 
                                     alt="<?= htmlspecialchars($p['tenSP']) ?>" 
                                     class="product-thumb-img"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.onerror=null;this.src='assets/images/products/SP004.jpg';">
                            </a>
                        </div>

                        <!-- Thân Card thông tin -->
                        <div class="card-body d-flex flex-column p-3">
                            <div class="text-muted small mb-1"><?= htmlspecialchars($p['tenDM'] ?? '') ?></div>
                            <h6 class="fw-bold text-dark mb-2 text-truncate-2" style="min-height: 42px;" title="<?= htmlspecialchars($p['tenSP']) ?>">
                                <a href="index.php?action=client-product-detail&id=<?= urlencode($p['maSP']) ?>" class="text-dark text-decoration-none hover-primary">
                                    <?= htmlspecialchars($p['tenSP']) ?>
                                </a>
                            </h6>

                            <!-- Tóm tắt cấu hình: Chip - RAM - SSD - VGA -->
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <?php if (!empty($p['specs']['cpu'])): ?>
                                    <span class="spec-pill"><i class="bi bi-cpu me-1"></i><?= htmlspecialchars($p['specs']['cpu']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($p['specs']['ram'])): ?>
                                    <span class="spec-pill"><i class="bi bi-memory me-1"></i><?= htmlspecialchars($p['specs']['ram']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($p['specs']['ssd'])): ?>
                                    <span class="spec-pill"><i class="bi bi-device-hdd me-1"></i><?= htmlspecialchars($p['specs']['ssd']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($p['specs']['vga'])): ?>
                                    <span class="spec-pill text-danger fw-bold"><i class="bi bi-gpu-card me-1"></i><?= htmlspecialchars($p['specs']['vga']) ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Giá tiền & Nút Mua -->
                            <div class="mt-auto pt-2 border-top">
                                <div class="price-tag mb-2">
                                    <?= number_format((float)$p['giaBan'], 0, ',', '.') ?> đ
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-spring btn-sm px-2" title="Xem nhanh cấu hình" onclick="openQuickView('<?= htmlspecialchars($p['maSP']) ?>')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="index.php?action=client-product-detail&id=<?= urlencode($p['maSP']) ?>" class="btn btn-outline-secondary btn-spring btn-sm fw-semibold flex-grow-1">
                                        Chi tiết
                                    </a>

                                    <?php if ((int)$p['soLuongTon'] > 0): ?>
                                        <button type="button" class="btn btn-primary btn-spring btn-sm px-3" title="Thêm vào giỏ" onclick="addToCartAjax('<?= htmlspecialchars($p['maSP']) ?>', 1, this)">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm px-3" disabled title="Tạm hết hàng">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- NẾU LÀ TRANG CHỦ MẶC ĐỊNH: HIỂN THỊ CÁC KHỐI SẢN PHẨM THEO DANH MỤC CÓ HÌNH ẢNH -->
    <?php if (!$isFiltering): ?>
        
        <!-- 1. KHỐI LAPTOP GAMING -->
        <?php if (!empty($gamingLaptops)): ?>
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-controller text-danger me-2"></i>Laptop Gaming Chiến Mọi Tựa Game
                    </h5>
                    <a href="index.php?action=client-home&category=1" class="text-primary text-decoration-none small fw-semibold">
                        Xem tất cả Gaming <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
                    <?php foreach ($gamingLaptops as $p): ?>
                        <?php 
                        $imgSrc = !empty($p['hinhAnh']) ? htmlspecialchars($p['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($p['maSP']) . '.jpg');
                        ?>
                        <div class="col">
                            <div class="laptop-card shadow-sm">
                                <div class="card-badges-bar d-flex justify-content-between align-items-center px-3 pt-3 pb-1">
                                    <span class="badge bg-danger fw-bold px-2 py-1 shadow-sm">Gaming</span>
                                    <span class="badge bg-secondary-subtle text-secondary small fw-semibold"><?= htmlspecialchars($p['tenTH'] ?? '') ?></span>
                                </div>
                                <div class="card-thumb-wrapper" style="height: 195px;">
                                    <a href="index.php?action=client-product-detail&id=<?= urlencode($p['maSP']) ?>" class="d-flex align-items-center justify-content-center w-100 h-100">
                                        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($p['tenSP']) ?>" class="product-thumb-img" onerror="this.onerror=null;this.src='assets/images/products/SP001.jpg';">
                                    </a>
                                </div>
                                <div class="card-body p-3">
                                    <h6 class="fw-bold text-dark text-truncate mb-1">
                                        <a href="index.php?action=client-product-detail&id=<?= urlencode($p['maSP']) ?>" class="text-dark text-decoration-none hover-primary">
                                            <?= htmlspecialchars($p['tenSP']) ?>
                                        </a>
                                    </h6>
                                    <div class="price-tag fs-6 mb-2">
                                        <?= number_format((float)$p['giaBan'], 0, ',', '.') ?> đ
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm px-2" title="Xem nhanh cấu hình" onclick="openQuickView('<?= htmlspecialchars($p['maSP']) ?>')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="index.php?action=client-product-detail&id=<?= urlencode($p['maSP']) ?>" class="btn btn-outline-danger btn-sm flex-grow-1 fw-semibold">
                                            Xem Chi Tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 2. KHỐI MACBOOK APPLE -->
        <?php if (!empty($macbooks)): ?>
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-apple text-dark me-2"></i>Apple MacBook Chính Hãng Apple Việt Nam
                    </h5>
                    <a href="index.php?action=client-home&category=3" class="text-primary text-decoration-none small fw-semibold">
                        Xem tất cả MacBook <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
                    <?php foreach ($macbooks as $p): ?>
                        <?php 
                        $imgSrc = !empty($p['hinhAnh']) ? htmlspecialchars($p['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($p['maSP']) . '.jpg');
                        ?>
                        <div class="col">
                            <div class="laptop-card shadow-sm">
                                <div class="card-badges-bar d-flex justify-content-between align-items-center px-3 pt-3 pb-1">
                                    <span class="badge bg-dark fw-bold px-2 py-1 shadow-sm"><i class="bi bi-apple me-1"></i>Apple</span>
                                    <span class="badge bg-success-subtle text-success border border-success small fw-semibold">Chính hãng</span>
                                </div>
                                <div class="card-thumb-wrapper" style="height: 195px;">
                                    <a href="index.php?action=client-product-detail&id=<?= urlencode($p['maSP']) ?>" class="d-flex align-items-center justify-content-center w-100 h-100">
                                        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($p['tenSP']) ?>" class="product-thumb-img" onerror="this.onerror=null;this.src='assets/images/products/SP004.jpg';">
                                    </a>
                                </div>
                                <div class="card-body p-3">
                                    <h6 class="fw-bold text-dark text-truncate mb-1">
                                        <a href="index.php?action=client-product-detail&id=<?= urlencode($p['maSP']) ?>" class="text-dark text-decoration-none hover-primary">
                                            <?= htmlspecialchars($p['tenSP']) ?>
                                        </a>
                                    </h6>
                                    <div class="price-tag fs-6 mb-2">
                                        <?= number_format((float)$p['giaBan'], 0, ',', '.') ?> đ
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-dark btn-sm px-2" title="Xem nhanh cấu hình" onclick="openQuickView('<?= htmlspecialchars($p['maSP']) ?>')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="index.php?action=client-product-detail&id=<?= urlencode($p['maSP']) ?>" class="btn btn-outline-dark btn-sm flex-grow-1 fw-semibold">
                                            Xem Chi Tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- CHÍNH SÁCH CAM KẾT VÀNG (SPOTLIGHT CARDS) -->
    <div class="row g-3 py-4 mt-2">
        <div class="col-md-3">
            <div class="p-3 bg-white border rounded-4 d-flex align-items-center gap-3 shadow-sm spotlight-card">
                <i class="bi bi-patch-check fs-2 text-primary"></i>
                <div>
                    <div class="fw-bold text-dark">100% Chính Hãng</div>
                    <div class="text-muted small">Cam kết đền 200% nếu phát hiện giả</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white border rounded-4 d-flex align-items-center gap-3 shadow-sm spotlight-card">
                <i class="bi bi-truck fs-2 text-success"></i>
                <div>
                    <div class="fw-bold text-dark">Giao Hàng Toàn Quốc</div>
                    <div class="text-muted small">Kiểm tra máy trước khi thanh toán</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white border rounded-4 d-flex align-items-center gap-3 shadow-sm spotlight-card">
                <i class="bi bi-arrow-repeat fs-2 text-warning"></i>
                <div>
                    <div class="fw-bold text-dark">1 Đổi 1 Trong 30 Ngày</div>
                    <div class="text-muted small">Nếu phát sinh lỗi từ phần cứng NSX</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 bg-white border rounded-4 d-flex align-items-center gap-3 shadow-sm spotlight-card">
                <i class="bi bi-headset fs-2 text-info"></i>
                <div>
                    <div class="fw-bold text-dark">Hỗ Trợ Kỹ Thuật 24/7</div>
                    <div class="text-muted small">Hotline: 1900 6868 miễn phí cước</div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
