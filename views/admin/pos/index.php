<?php
/**
 * File: views/admin/pos/index.php
 * Màn hình Bán Hàng Tại Quầy (POS) tích hợp chuẩn giao diện Quản Trị Admin
 */

$currentUser = AuthMiddleware::getCurrentUser();
$userRole = $currentUser['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bán Hàng Tại Quầy (POS) - EmShu Store Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f8fafc;
            --card-border: #e2e8f0;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            min-height: 100vh;
        }

        /* Sidebar Standard */
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
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
            overflow-y: auto;
        }

        .sidebar-nav .nav-link {
            color: #94a3b8;
            padding: 10px 14px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.92rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
            margin-bottom: 3px;
        }

        .sidebar-nav .nav-link:hover {
            background: #1e293b;
            color: #ffffff;
        }

        .sidebar-nav .nav-link.active {
            background: var(--primary-color);
            color: #ffffff;
            font-weight: 600;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 24px 32px;
        }

        /* Top Header */
        .top-navbar {
            background: #ffffff;
            border-radius: 14px;
            padding: 16px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            border: 1px solid var(--card-border);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* POS Two-Column Layout */
        .pos-grid-container {
            display: grid;
            grid-template-columns: 1fr 440px;
            gap: 20px;
            align-items: start;
        }

        @media (max-width: 1200px) {
            .pos-grid-container {
                grid-template-columns: 1fr;
            }
        }

        /* Left Pane: Catalog */
        .catalog-panel {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--card-border);
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .catalog-search-wrap {
            position: relative;
            margin-bottom: 14px;
        }

        .catalog-search-input {
            width: 100%;
            padding: 11px 16px 11px 44px;
            font-size: 0.92rem;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            transition: all 0.2s;
        }

        .catalog-search-input:focus {
            background: #ffffff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .catalog-search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .brand-pills-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 12px;
            margin-bottom: 16px;
            border-bottom: 1px dashed var(--card-border);
        }

        .brand-pill {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.15s;
            user-select: none;
        }

        .brand-pill:hover, .brand-pill.active {
            background: var(--primary-color);
            color: #ffffff;
            border-color: var(--primary-color);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 14px;
            max-height: 700px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .product-pos-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid var(--card-border);
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            user-select: none;
            position: relative;
        }

        .product-pos-card:hover {
            transform: translateY(-3px);
            border-color: #93c5fd;
            box-shadow: 0 8px 16px -4px rgba(37, 99, 235, 0.12);
        }

        .product-pos-card:active {
            transform: scale(0.98);
        }

        .card-img-box {
            height: 120px;
            width: 100%;
            background: #f8fafc;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            border: 1px solid #f1f5f9;
        }

        .card-img-box img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            transition: transform 0.25s ease;
        }

        .product-pos-card:hover .card-img-box img {
            transform: scale(1.05);
        }

        .pos-card-name {
            font-size: 0.86rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            height: 38px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            margin-bottom: 6px;
        }

        .pos-card-price {
            font-size: 0.95rem;
            font-weight: 800;
            color: #dc2626;
            font-family: 'JetBrains Mono', monospace;
            margin-bottom: 4px;
        }

        .pos-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: #64748b;
        }

        .badge-stock {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 4px;
        }

        /* Right Pane: Bill & Checkout */
        .bill-panel {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--card-border);
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 24px;
        }

        .customer-card-box {
            background: #f8fafc;
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }

        .customer-details-form {
            display: none;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
        }

        .customer-details-form.active {
            display: block;
        }

        /* Bill Items List */
        .cart-items-container {
            max-height: 320px;
            overflow-y: auto;
            margin-bottom: 16px;
            padding-right: 4px;
        }

        .empty-cart-state {
            text-align: center;
            padding: 30px 10px;
            color: #94a3b8;
        }

        .bill-item-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .bill-item-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .bill-item-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
            flex: 1;
            margin-right: 8px;
        }

        .btn-remove-item {
            color: #94a3b8;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            transition: color 0.15s;
        }

        .btn-remove-item:hover {
            color: #ef4444;
        }

        .qty-stepper-wrap {
            display: flex;
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            background: #ffffff;
        }

        .qty-step-btn {
            width: 26px;
            height: 26px;
            border: none;
            background: #ffffff;
            cursor: pointer;
            font-weight: 700;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.1s;
        }

        .qty-step-btn:hover {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        .qty-val {
            width: 30px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .serial-slots-box {
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            margin-top: 8px;
        }

        .serial-dropdown-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            font-size: 0.78rem;
        }

        .serial-dropdown-row:last-child {
            margin-bottom: 0;
        }

        .serial-select-box {
            font-size: 0.8rem;
            font-family: 'JetBrains Mono', monospace;
            padding: 3px 8px;
            border-radius: 5px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            flex: 1;
        }

        /* Calculations and CTA */
        .bill-summary-wrap {
            border-top: 1px solid var(--card-border);
            padding-top: 14px;
            margin-top: 6px;
        }

        .calc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.88rem;
            color: #64748b;
            margin-bottom: 6px;
        }

        .calc-row.total-calc {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            border-top: 1px dashed var(--card-border);
            padding-top: 8px;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        .calc-row.total-calc .final-val {
            color: #dc2626;
            font-size: 1.3rem;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Payment Method Radio Cards */
        .pay-methods-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }

        .pay-pill-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 4px;
            text-align: center;
            cursor: pointer;
            background: #f8fafc;
            font-size: 0.78rem;
            font-weight: 700;
            color: #475569;
            transition: all 0.15s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .pay-pill-card.active {
            border-color: var(--primary-color);
            background: #eff6ff;
            color: var(--primary-color);
            box-shadow: 0 0 0 1px var(--primary-color);
        }

        .quick-cash-tags {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            margin-bottom: 8px;
        }

        .quick-cash-tag {
            padding: 2px 8px;
            font-size: 0.72rem;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 4px;
            cursor: pointer;
            white-space: nowrap;
        }

        .quick-cash-tag:hover {
            background: #f1f5f9;
        }

        .btn-checkout-submit {
            width: 100%;
            padding: 13px;
            font-size: 1.05rem;
            font-weight: 800;
            background: #10b981;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: all 0.2s;
        }

        .btn-checkout-submit:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-checkout-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
    </style>
</head>
<body>

<!-- SIDEBAR ĐỒNG BỘ 100% VỚI TẤT CẢ CÁC TRANG QUẢN TRỊ -->
<?php 
$activeMenu = 'pos';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT TIÊU CHUẨN (MARGIN-LEFT = 260PX) -->
<main class="main-content">
    
    <!-- Top Header Bar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 p-2 bg-primary-subtle text-primary fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-calculator"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">Bán Hàng Tại Quầy (POS)</h5>
                <small class="text-muted">Lập đơn bán trực tiếp, gán số Serial/IMEI thực tế và xuất hóa đơn kiêm phiếu bảo hành</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                <i class="bi bi-person-circle me-1"></i> Thu Ngân: <?= htmlspecialchars($currentUser['hoTen'] ?? 'Admin') ?>
            </span>
            <a href="index.php?action=admin-orders" class="btn btn-sm btn-outline-secondary fw-semibold">
                <i class="bi bi-cart3 me-1"></i> Xem Đơn Hàng
            </a>
        </div>
    </header>

    <!-- Two-Column POS Workspace -->
    <div class="pos-grid-container">
        
        <!-- CỘT TRÁI: DANH MỤC & TÌM KIẾM LAPTOP TRONG KHO -->
        <section class="catalog-panel">
            
            <!-- Search & Brand Filters -->
            <div class="catalog-search-wrap">
                <i class="bi bi-search catalog-search-icon"></i>
                <input type="text" id="posSearchInput" class="catalog-search-input" 
                       placeholder="Gõ tên máy hoặc mã sản phẩm để lọc nhanh (Dell, ASUS, M3...)..." 
                       autocomplete="off">
            </div>

            <div class="brand-pills-scroll">
                <span class="brand-pill active" data-brand="">Tất cả thương hiệu</span>
                <?php foreach ($brands as $b): ?>
                    <span class="brand-pill" data-brand="<?= htmlspecialchars($b['maTH']) ?>">
                        <?= htmlspecialchars($b['tenTH']) ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <!-- Products Grid -->
            <div class="products-grid" id="posProductsGrid">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bi bi-box-seam display-4 d-block mb-3 opacity-50"></i>
                        <h6>Không có laptop nào đang có sẵn trong kho!</h6>
                        <small>Vui lòng tạo phiếu nhập kho trước khi bán tại quầy.</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <div class="product-pos-card" 
                             data-masp="<?= htmlspecialchars($p['maSP']) ?>"
                             data-tensp="<?= htmlspecialchars($p['tenSP']) ?>"
                             data-giaban="<?= (float)$p['giaBan'] ?>"
                             data-tonkho="<?= (int)$p['soLuongTon'] ?>"
                             data-baohanh="<?= (int)($p['thoiGianBaoHanh'] ?? 12) ?>"
                             data-brand="<?= htmlspecialchars($p['maTH'] ?? '') ?>"
                             onclick="posTerminal.addToCart('<?= htmlspecialchars($p['maSP']) ?>')">
                            
                            <div class="card-img-box">
                                <?php if (!empty($p['hinhAnh'])): ?>
                                    <img src="<?= htmlspecialchars($p['hinhAnh']) ?>" alt="<?= htmlspecialchars($p['tenSP']) ?>" loading="lazy" onerror="this.src='https://placehold.co/180x120?text=Laptop'">
                                <?php else: ?>
                                    <i class="bi bi-laptop text-secondary fs-1"></i>
                                <?php endif; ?>
                            </div>

                            <div class="pos-card-name" title="<?= htmlspecialchars($p['tenSP']) ?>">
                                <?= htmlspecialchars($p['tenSP']) ?>
                            </div>

                            <div class="pos-card-price">
                                <?= number_format($p['giaBan'], 0, ',', '.') ?> đ
                            </div>

                            <div class="pos-card-footer">
                                <span><i class="bi bi-shield-check text-success me-1"></i><?= (int)($p['thoiGianBaoHanh'] ?? 12) ?>T</span>
                                <span class="badge-stock">Tồn: <?= (int)$p['soLuongTon'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- CỘT PHẢI: GIỎ BÁN TẠI QUẦY & THANH TOÁN -->
        <section class="bill-panel">
            
            <!-- Khách hàng -->
            <div class="customer-card-box">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">
                        <i class="bi bi-person-badge text-primary me-1"></i> Khách Mua Hàng:
                    </label>
                    <span class="small text-muted" id="custTypeBadge">Vãng lai</span>
                </div>

                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                    <input type="text" id="custPhoneInput" class="form-control" 
                           placeholder="Nhập SĐT khách để tìm..." maxlength="15">
                    <button class="btn btn-outline-primary fw-bold" type="button" id="btnFindCustomer">
                        <i class="bi bi-search"></i>
                    </button>
                </div>

                <!-- Form chỉnh sửa/hiển thị chi tiết khách -->
                <div class="customer-details-form" id="custDetailsForm">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="text" id="custNameInput" class="form-control form-control-sm" 
                                   placeholder="Họ tên khách" value="Khách Mua Tại Quầy">
                        </div>
                        <div class="col-6">
                            <input type="text" id="custAddrInput" class="form-control form-control-sm" 
                                   placeholder="Địa chỉ..." value="Tại cửa hàng">
                        </div>
                    </div>
                    <input type="hidden" id="custMaKH" value="">
                </div>
            </div>

            <!-- Danh sách máy trong đơn -->
            <div class="cart-items-container" id="cartContainer">
                <div class="empty-cart-state" id="emptyCartMessage">
                    <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    <div class="fw-bold small text-dark">Chưa có sản phẩm nào</div>
                    <div style="font-size: 0.78rem;">Nhấp vào laptop ở bảng bên trái để thêm vào đơn</div>
                </div>
                <div id="cartItemsList"></div>
            </div>

            <!-- Thanh toán & Tính tiền -->
            <div class="bill-summary-wrap">
                <div class="calc-row">
                    <span>Tổng tiền hàng (<span id="cartTotalUnits">0</span> máy):</span>
                    <strong class="font-monospace text-dark" id="cartSubTotalText">0 đ</strong>
                </div>

                <div class="calc-row flex-wrap gap-2">
                    <span class="d-flex align-items-center gap-1">
                        <i class="bi bi-ticket-perforated text-danger"></i>
                        <span>Giảm giá / Voucher:</span>
                    </span>
                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <select name="maKM" id="pos-voucher-select" class="form-select form-select-sm w-auto font-monospace" style="max-width: 250px;">
                            <option value="" data-discount="0">-- Không áp dụng voucher --</option>
                            <?php if (!empty($promotions)): ?>
                                <?php foreach ($promotions as $km): ?>
                                    <option value="<?= htmlspecialchars($km['maKM']) ?>" 
                                            data-type="<?= htmlspecialchars($km['loaiKM']) ?>" 
                                            data-value="<?= (float)$km['giaTriGiam'] ?>" 
                                            data-min="<?= (float)$km['donHangToiThieu'] ?>" 
                                            data-max="<?= !is_null($km['mucGiamToiDa']) ? (float)$km['mucGiamToiDa'] : 0 ?>">
                                        <?= htmlspecialchars($km['maKM']) ?> - <?= htmlspecialchars($km['tenKM']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <strong class="font-monospace text-danger fs-6 text-nowrap" id="pos-discount-amount-text">0 đ</strong>
                        <input type="hidden" id="inputDiscount" value="0">
                    </div>
                </div>

                <div class="calc-row total-calc">
                    <span>TỔNG CẦN TRẢ:</span>
                    <span class="final-val" id="cartFinalTotalText">0 đ</span>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="pay-methods-grid">
                    <div class="pay-pill-card active" data-method="TIEN_MAT">
                        <i class="bi bi-cash-stack text-success fs-5"></i>
                        <span>Tiền mặt</span>
                    </div>
                    <div class="pay-pill-card" data-method="CHUYEN_KHOAN">
                        <i class="bi bi-qr-code text-primary fs-5"></i>
                        <span>Chuyển khoản</span>
                    </div>
                    <div class="pay-pill-card" data-method="THE">
                        <i class="bi bi-credit-card text-warning fs-5"></i>
                        <span>Quẹt thẻ</span>
                    </div>
                </div>

                <!-- Tiền khách đưa & Tiền thừa -->
                <div class="quick-cash-tags">
                    <span class="quick-cash-tag" data-val="EXACT">Đủ tiền</span>
                    <span class="quick-cash-tag" data-val="500000">500k</span>
                    <span class="quick-cash-tag" data-val="1000000">1Tr</span>
                    <span class="quick-cash-tag" data-val="2000000">2Tr</span>
                    <span class="quick-cash-tag" data-val="5000000">5Tr</span>
                    <span class="quick-cash-tag" data-val="10000000">10Tr</span>
                    <span class="quick-cash-tag" data-val="20000000">20Tr</span>
                </div>

                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text bg-light text-muted small">Tiền khách đưa:</span>
                    <input type="number" id="inputTendered" class="form-control text-end font-monospace fw-bold text-success" 
                           value="0" min="0" step="50000">
                    <span class="input-group-text bg-light">đ</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 px-1 small">
                    <span class="text-muted fw-bold">Tiền thừa trả khách:</span>
                    <strong class="font-monospace fs-6 text-success" id="changeAmountText">0 đ</strong>
                </div>

                <!-- Nút to Thanh Toán & In Hóa Đơn -->
                <button type="button" id="btnSubmitPos" class="btn-checkout-submit" disabled>
                    <i class="bi bi-printer-fill fs-5"></i>
                    <span>THANH TOÁN & IN HÓA ĐƠN</span>
                </button>
            </div>
        </section>

    </div>
</main>

<!-- Bootstrap 5 JS & SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/**
 * POS Terminal Engine (Tích hợp chuẩn khung giao diện Admin)
 */
const posTerminal = {
    cart: [],
    selectedMethod: 'TIEN_MAT',
    catalog: <?= json_encode($products, JSON_UNESCAPED_UNICODE) ?> || [],

    init() {
        this.bindEvents();
    },

    bindEvents() {
        // Lọc theo Brand Pill
        document.querySelectorAll('.brand-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                document.querySelectorAll('.brand-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                this.filterCatalog();
            });
        });

        // Tìm kiếm sản phẩm
        document.getElementById('posSearchInput')?.addEventListener('input', () => this.filterCatalog());

        // Tìm khách hàng
        document.getElementById('btnFindCustomer')?.addEventListener('click', () => this.searchCustomer());
        document.getElementById('custPhoneInput')?.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') this.searchCustomer();
        });

        // Chọn phương thức thanh toán
        document.querySelectorAll('.pay-pill-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.pay-pill-card').forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                this.selectedMethod = card.dataset.method;
                this.recalculateTotal();
            });
        });

        // Chọn/hủy Voucher & Tiền khách đưa
        document.getElementById('pos-voucher-select')?.addEventListener('change', () => this.recalculateTotal());
        document.getElementById('inputTendered')?.addEventListener('input', () => this.recalculateTotal());

        // Quick Cash tags
        document.querySelectorAll('.quick-cash-tag').forEach(tag => {
            tag.addEventListener('click', () => {
                const val = tag.dataset.val;
                const total = this.getFinalTotal();
                if (val === 'EXACT') {
                    document.getElementById('inputTendered').value = total;
                } else {
                    document.getElementById('inputTendered').value = parseInt(val, 10);
                }
                this.recalculateTotal();
            });
        });

        // Nút Thanh toán
        document.getElementById('btnSubmitPos')?.addEventListener('click', () => this.processCheckout());
    },

    filterCatalog() {
        const query = (document.getElementById('posSearchInput')?.value || '').toLowerCase().trim();
        const brand = document.querySelector('.brand-pill.active')?.dataset.brand || '';

        document.querySelectorAll('.product-pos-card').forEach(card => {
            const name = (card.dataset.tensp || '').toLowerCase();
            const code = (card.dataset.masp || '').toLowerCase();
            const cardBrand = card.dataset.brand || '';

            const matchQuery = query === '' || name.includes(query) || code.includes(query);
            const matchBrand = brand === '' || cardBrand === brand;

            card.style.display = (matchQuery && matchBrand) ? 'flex' : 'none';
        });
    },

    async addToCart(maSP) {
        const prod = this.catalog.find(p => p.maSP === maSP);
        if (!prod) return;

        let existing = this.cart.find(i => i.maSP === maSP);

        if (existing) {
            if (existing.soLuong >= existing.tonKho) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Hết hàng sẵn tồn!',
                    text: `Chỉ còn ${existing.tonKho} máy trong kho!`,
                    timer: 1500
                });
                return;
            }
            existing.soLuong++;
            existing.selectedSerials.push('');
        } else {
            // Lấy danh sách serial tồn kho
            const serials = await this.getSerials(maSP);
            if (!serials || serials.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Chưa có Serial/IMEI!',
                    text: `Sản phẩm [${prod.tenSP}] chưa có mã Serial ở trạng thái 'Trong kho'.`,
                });
                return;
            }

            this.cart.push({
                maSP: prod.maSP,
                tenSP: prod.tenSP,
                donGia: parseFloat(prod.giaBan),
                tonKho: parseInt(prod.soLuongTon, 10),
                soLuong: 1,
                thoiGianBaoHanh: parseInt(prod.thoiGianBaoHanh || 12, 10),
                availableSerials: serials,
                selectedSerials: [serials[0].maSerial_IMEI]
            });
        }

        this.renderCartUI();
    },

    async getSerials(maSP) {
        try {
            const res = await fetch(`index.php?action=admin-pos-get-serials&maSP=${encodeURIComponent(maSP)}`);
            const data = await res.json();
            return data.success ? (data.serials || []) : [];
        } catch (e) {
            console.error(e);
            return [];
        }
    },

    updateQty(maSP, delta) {
        const item = this.cart.find(i => i.maSP === maSP);
        if (!item) return;

        const next = item.soLuong + delta;
        if (next <= 0) {
            this.removeItem(maSP);
            return;
        }

        if (next > item.tonKho) {
            Swal.fire({ icon: 'warning', text: `Tồn kho tối đa: ${item.tonKho} máy!`, timer: 1500 });
            return;
        }

        if (delta > 0) {
            const picked = new Set(item.selectedSerials);
            const candidate = item.availableSerials.find(s => !picked.has(s.maSerial_IMEI));
            item.selectedSerials.push(candidate ? candidate.maSerial_IMEI : '');
        } else {
            item.selectedSerials.pop();
        }

        item.soLuong = next;
        this.renderCartUI();
    },

    removeItem(maSP) {
        this.cart = this.cart.filter(i => i.maSP !== maSP);
        this.renderCartUI();
    },

    onSerialSelect(maSP, idx, val) {
        const item = this.cart.find(i => i.maSP === maSP);
        if (!item) return;

        if (val !== '') {
            const dup = item.selectedSerials.findIndex((sn, sIdx) => sIdx !== idx && sn === val);
            if (dup !== -1) {
                Swal.fire({
                    icon: 'error',
                    title: 'Trùng mã Serial!',
                    text: `Mã [${val}] đã được gán cho Máy số ${dup + 1}!`,
                    timer: 2000
                });
                this.renderCartUI();
                return;
            }
        }

        item.selectedSerials[idx] = val;
        this.recalculateTotal();
    },

    renderCartUI() {
        const emptyMsg = document.getElementById('emptyCartMessage');
        const listEl   = document.getElementById('cartItemsList');

        if (this.cart.length === 0) {
            emptyMsg.style.display = 'block';
            listEl.innerHTML = '';
        } else {
            emptyMsg.style.display = 'none';
            let html = '';

            this.cart.forEach(item => {
                const total = item.donGia * item.soLuong;

                let serialDropdowns = '';
                for (let i = 0; i < item.soLuong; i++) {
                    const currentVal = item.selectedSerials[i] || '';
                    let optHtml = `<option value="">-- Chọn Serial máy ${i + 1} --</option>`;
                    item.availableSerials.forEach(s => {
                        const sel = (s.maSerial_IMEI === currentVal) ? 'selected' : '';
                        optHtml += `<option value="${s.maSerial_IMEI}" ${sel}>${s.maSerial_IMEI}</option>`;
                    });

                    serialDropdowns += `
                        <div class="serial-dropdown-row">
                            <span class="text-muted fw-bold">Máy ${i + 1}:</span>
                            <select class="serial-select-box" onchange="posTerminal.onSerialSelect('${item.maSP}', ${i}, this.value)">
                                ${optHtml}
                            </select>
                        </div>
                    `;
                }

                html += `
                    <div class="bill-item-card">
                        <div class="bill-item-top">
                            <div class="bill-item-name">${this.escape(item.tenSP)}</div>
                            <button type="button" class="btn-remove-item" onclick="posTerminal.removeItem('${item.maSP}')" title="Xóa máy này">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="qty-stepper-wrap">
                                <button type="button" class="qty-step-btn" onclick="posTerminal.updateQty('${item.maSP}', -1)">-</button>
                                <span class="qty-val">${item.soLuong}</span>
                                <button type="button" class="qty-step-btn" onclick="posTerminal.updateQty('${item.maSP}', 1)">+</button>
                            </div>
                            <strong class="font-monospace text-dark">${this.fmt(total)} đ</strong>
                        </div>

                        <div class="serial-slots-box">
                            <div class="small fw-bold text-dark mb-1 d-flex justify-content-between">
                                <span><i class="bi bi-upc-scan text-primary me-1"></i> Gán Serial/IMEI máy:</span>
                                <span class="badge bg-light text-secondary border">${item.thoiGianBaoHanh}T BH</span>
                            </div>
                            ${serialDropdowns}
                        </div>
                    </div>
                `;
            });

            listEl.innerHTML = html;
        }

        this.recalculateTotal();
    },

    getSubTotal() {
        return this.cart.reduce((sum, item) => sum + (item.donGia * item.soLuong), 0);
    },

    getFinalTotal() {
        const sub = this.getSubTotal();
        const disc = parseFloat(document.getElementById('inputDiscount')?.value || 0) || 0;
        return Math.max(0, sub - disc);
    },

    recalculateTotal() {
        const totalUnits = this.cart.reduce((s, i) => s + i.soLuong, 0);
        const sub = this.getSubTotal();
        const voucherSelect = document.getElementById('pos-voucher-select');
        let discount = 0;

        if (voucherSelect && voucherSelect.value !== '') {
            const opt = voucherSelect.options[voucherSelect.selectedIndex];
            if (opt) {
                const type = opt.dataset.type || 'TIEN_MAT';
                const val = parseFloat(opt.dataset.value || 0) || 0;
                const min = parseFloat(opt.dataset.min || 0) || 0;
                const max = parseFloat(opt.dataset.max || 0) || 0;

                if (sub < min) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Chưa đủ điều kiện voucher!',
                        text: `Mã [${opt.value}] yêu cầu đơn hàng tối thiểu từ ${this.fmt(min)} đ. Hiện tại đơn chỉ đạt ${this.fmt(sub)} đ.`,
                        timer: 2500,
                        showConfirmButton: false
                    });
                    voucherSelect.value = '';
                    discount = 0;
                } else {
                    if (type === 'TIEN_MAT') {
                        discount = val;
                    } else if (type === 'PHAN_TRAM') {
                        discount = sub * (val / 100);
                        if (max > 0 && discount > max) {
                            discount = max;
                        }
                    }
                    discount = Math.min(discount, sub);
                }
            }
        }

        const discountTextEl = document.getElementById('pos-discount-amount-text');
        if (discountTextEl) {
            discountTextEl.textContent = discount > 0 ? `-${this.fmt(discount)} đ` : '0 đ';
        }
        const inputDiscount = document.getElementById('inputDiscount');
        if (inputDiscount) {
            inputDiscount.value = discount;
        }

        const final = Math.max(0, sub - discount);

        const cartUnitsEl = document.getElementById('cartTotalUnits');
        if (cartUnitsEl) cartUnitsEl.textContent = totalUnits;

        const cartSubTotalEl = document.getElementById('cartSubTotalText');
        if (cartSubTotalEl) cartSubTotalEl.textContent = this.fmt(sub) + ' đ';

        const cartFinalTotalEl = document.getElementById('cartFinalTotalText');
        if (cartFinalTotalEl) cartFinalTotalEl.textContent = this.fmt(final) + ' đ';

        // Tiền thừa trả khách
        const tendered = parseFloat(document.getElementById('inputTendered')?.value || 0) || 0;
        const change = Math.max(0, tendered - final);
        const changeAmountEl = document.getElementById('changeAmountText');
        if (changeAmountEl) changeAmountEl.textContent = this.fmt(change) + ' đ';

        // Nút Checkout
        const btn = document.getElementById('btnSubmitPos');
        let valid = (this.cart.length > 0);
        for (const item of this.cart) {
            if (item.selectedSerials.length !== item.soLuong || item.selectedSerials.some(s => !s)) {
                valid = false;
                break;
            }
        }
        if (btn) btn.disabled = !valid;
    },

    recalculate() {
        this.recalculateTotal();
    },

    async searchCustomer() {
        const phone = document.getElementById('custPhoneInput')?.value.trim();
        if (!phone) {
            Swal.fire({ icon: 'info', text: 'Vui lòng nhập số điện thoại khách hàng!' });
            return;
        }

        try {
            const res = await fetch(`index.php?action=admin-pos-customer&sdt=${encodeURIComponent(phone)}`);
            const data = await res.json();

            const form = document.getElementById('custDetailsForm');
            const badge = document.getElementById('custTypeBadge');
            const name = document.getElementById('custNameInput');
            const addr = document.getElementById('custAddrInput');
            const maKH = document.getElementById('custMaKH');

            form.classList.add('active');

            if (data.found && data.customer) {
                badge.className = 'small badge bg-success-subtle text-success';
                badge.textContent = 'Khách quen: ' + data.customer.maKH;
                name.value = data.customer.hoVaTen;
                addr.value = data.customer.diaChi || 'Tại cửa hàng';
                maKH.value = data.customer.maKH;
            } else {
                badge.className = 'small badge bg-info-subtle text-info';
                badge.textContent = 'Khách mới (tạo nhanh)';
                name.value = 'Khách Mua Tại Quầy';
                addr.value = 'Tại cửa hàng';
                maKH.value = '';
            }
        } catch (e) {
            console.error(e);
        }
    },

    async processCheckout() {
        if (this.cart.length === 0) return;

        const sub = this.getSubTotal();
        const voucherSelect = document.getElementById('pos-voucher-select');
        const selectedMaKM = voucherSelect ? voucherSelect.value.trim() : '';
        const discountAmount = parseFloat(document.getElementById('inputDiscount')?.value || 0) || 0;
        const final = Math.max(0, sub - discountAmount);
        const tendered = parseFloat(document.getElementById('inputTendered')?.value || 0) || 0;

        if (this.selectedMethod === 'TIEN_MAT' && tendered < final && tendered > 0) {
            const confirmUnder = await Swal.fire({
                icon: 'warning',
                title: 'Tiền khách đưa chưa đủ!',
                text: `Khách đưa: ${this.fmt(tendered)} đ trong khi phải trả: ${this.fmt(final)} đ. Vẫn tiếp tục?`,
                showCancelButton: true,
                confirmButtonText: 'Vẫn thanh toán',
                cancelButtonText: 'Nhập lại'
            });
            if (!confirmUnder.isConfirmed) return;
        }

        let voucherRowHtml = '';
        if (selectedMaKM) {
            voucherRowHtml = `
                <div class="mb-1 text-success">
                    <i class="bi bi-ticket-perforated me-1"></i> Voucher: <strong>${this.escape(selectedMaKM)}</strong> 
                    (-${this.fmt(discountAmount)} đ)
                </div>
            `;
        }

        const confirm = await Swal.fire({
            title: 'Xác nhận thanh toán?',
            html: `
                <div class="text-start p-2">
                    <div class="mb-1">Tổng tiền hàng: <strong>${this.fmt(sub)} đ</strong></div>
                    ${voucherRowHtml}
                    <div class="mb-1">TỔNG CẦN TRẢ: <strong class="text-danger fs-5">${this.fmt(final)} đ</strong></div>
                    <div class="mb-1">Phương thức: <strong>${this.selectedMethod}</strong></div>
                    <div class="mb-1">Tổng máy: <strong>${this.cart.reduce((s, i) => s + i.soLuong, 0)} chiếc</strong></div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: '<i class="bi bi-printer me-1"></i> Xác Nhận & Xuất Hóa Đơn',
            cancelButtonText: 'Quay lại'
        });

        if (!confirm.isConfirmed) return;

        const payload = {
            maKH: document.getElementById('custMaKH')?.value || '',
            hoTen: document.getElementById('custNameInput')?.value || 'Khách Mua Tại Quầy',
            soDienThoai: document.getElementById('custPhoneInput')?.value || '',
            diaChi: document.getElementById('custAddrInput')?.value || 'Tại cửa hàng',
            phuongThucThanhToan: this.selectedMethod,
            maKM: selectedMaKM || null,
            tienGiamGia: discountAmount,
            items: this.cart.map(item => ({
                maSP: item.maSP,
                soLuong: item.soLuong,
                donGia: item.donGia,
                serials: item.selectedSerials
            }))
        };

        Swal.fire({
            title: 'Đang tạo đơn & xuất kho...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch('index.php?action=admin-pos-checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thanh toán thành công!',
                    text: `Mã đơn: ${data.maDH} | Mã HĐ: ${data.maHD}`,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = data.redirectUrl;
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Thất bại!', text: data.message });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Lỗi mạng!', text: err.message });
        }
    },

    fmt(num) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(num || 0));
    },

    escape(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
};

document.addEventListener('DOMContentLoaded', () => posTerminal.init());
</script>
</body>
</html>
