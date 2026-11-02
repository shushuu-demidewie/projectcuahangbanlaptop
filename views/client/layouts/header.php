<?php
/**
 * File: views/client/layouts/header.php
 * Header dùng chung cho toàn bộ giao diện Khách hàng (Client Side)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUser = AuthMiddleware::getCurrentUser();
$isLoggedIn  = AuthMiddleware::isLoggedIn();
$isStaff     = AuthMiddleware::isStaff();

// Đếm số lượng sản phẩm trong giỏ hàng nếu chưa được truyền từ Controller
if (!isset($cartCount)) {
    require_once __DIR__ . '/../../../models/CartModel.php';
    $cartModel = new CartModel();
    $maKH = null;
    if (!empty($currentUser['maTk'])) {
        $maKH = $cartModel->getCustomerCodeByAccount($currentUser['maTk']);
    }
    $cartCount = $cartModel->getCartCount($maKH);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'EmShu Store - Hệ Thống Bán Lẻ Laptop Chính Hãng Hàng Đầu') ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Animate UI & System Design Enhancements -->
    <link href="assets/css/client-animate.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #0f172a;
            --accent: #f59e0b;
            --bg-light: #f8fafc;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-light);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-wrapper {
            flex: 1 0 auto;
        }
        /* ==============================================================
           TOOLBAR / NAVBAR DESIGN SYSTEM CAO CẤP
           ============================================================== */
        /* Header Top */
        .header-top {
            background-color: #080e1e;
            color: #94a3b8;
            font-size: 12.5px;
            padding: 7px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }
        .header-top a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .header-top a:hover {
            color: #ffffff;
        }
        .header-top-badge {
            background: rgba(37, 99, 235, 0.25);
            border: 1px solid rgba(59, 130, 246, 0.45);
            color: #93c5fd !important;
            font-size: 11.5px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 9999px;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
        }
        .header-top-badge:hover {
            background: #2563eb;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        /* Navbar Main */
        .navbar-main {
            background-color: rgba(15, 23, 42, 0.92) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 25px -4px rgba(0, 0, 0, 0.25);
            padding: 10px 0;
        }

        /* Brand Logo */
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: transform 0.25s var(--spring-snappy);
        }
        .brand-logo:hover {
            transform: translateY(-1px);
        }
        .brand-logo-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb 0%, #38bdf8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4), 0 0 20px rgba(56, 189, 248, 0.25);
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        .brand-logo:hover .brand-logo-icon {
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6), 0 0 28px rgba(56, 189, 248, 0.45);
            transform: rotate(-3deg) scale(1.05);
        }
        .brand-logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }
        .brand-title {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #ffffff;
        }
        .brand-accent {
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-left: 2px;
        }
        .brand-tagline {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 1.8px;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* Search Box */
        .search-box {
            position: relative;
            max-width: 440px;
            width: 100%;
        }
        .search-box input {
            border-radius: 9999px;
            padding-left: 44px;
            padding-right: 98px;
            height: 44px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.07);
            color: #ffffff;
            font-size: 13.5px;
            backdrop-filter: blur(8px);
            transition: all 0.25s var(--spring-smooth);
        }
        .search-box input:focus {
            background: rgba(255, 255, 255, 0.13);
            color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3), 0 8px 20px rgba(0, 0, 0, 0.25);
        }
        .search-box input::placeholder {
            color: #94a3b8;
            font-size: 13px;
        }
        .search-box .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            pointer-events: none;
        }
        .search-box .btn-search {
            position: absolute;
            right: 4px;
            top: 4px;
            bottom: 4px;
            border-radius: 9999px;
            padding: 0 16px;
            font-size: 13px;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
            transition: all 0.2s var(--spring-snappy);
        }
        .search-box .btn-search:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.5);
        }

        /* Nav Action Pills (Tra cứu, Bảo hành, Giỏ hàng, Tài khoản) */
        .nav-action-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            height: 44px;
            padding: 0 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.22s var(--spring-snappy);
            cursor: pointer;
            white-space: nowrap;
        }
        .nav-action-pill:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(59, 130, 246, 0.45);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
        }
        .nav-action-icon {
            font-size: 19px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .nav-action-texts {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
            text-align: left;
        }
        .nav-action-sub {
            font-size: 10.5px;
            font-weight: 500;
            color: #94a3b8;
        }
        .nav-action-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #ffffff;
        }
        .nav-action-cart {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.25) 0%, rgba(29, 78, 216, 0.15) 100%);
            border-color: rgba(59, 130, 246, 0.35);
        }
        .nav-action-cart:hover {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.4) 0%, rgba(29, 78, 216, 0.25) 100%);
            border-color: rgba(59, 130, 246, 0.7);
        }
        .cart-badge-pill {
            background: #ef4444;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            min-width: 20px;
            height: 20px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.5);
            transition: transform 0.25s var(--spring-snappy);
        }
        .user-avatar-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
        }

        /* Sub-Navbar (Thanh Menu Danh Mục Cao Cấp) */
        .sub-navbar-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }
        .sub-nav-scroll-container {
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            white-space: nowrap;
        }
        .sub-nav-scroll-container::-webkit-scrollbar {
            display: none;
        }
        .sub-nav-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 15px;
            border-radius: 9999px;
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            white-space: nowrap !important;
            transition: all 0.2s var(--spring-snappy);
            border: 1px solid transparent;
        }
        .sub-nav-pill:hover {
            background: #eff6ff;
            color: #2563eb;
            transform: translateY(-1px);
            border-color: #dbeafe;
        }
        .sub-nav-pill.active {
            background: #2563eb;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        .sub-nav-pill.active i {
            color: #ffffff !important;
        }
        .sub-nav-promo-pill {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 600;
            color: #9f1239;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(244, 63, 94, 0.1);
        }
        /* Live Search Dropdown */
        .live-search-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0,0,0,0.08);
            z-index: 1060;
            overflow: hidden;
            animation: liveSearchFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            max-height: 420px;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        @keyframes liveSearchFadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .live-search-dropdown::-webkit-scrollbar {
            width: 5px;
        }
        .live-search-dropdown::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 6px;
        }
        .live-search-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: #1e293b;
            text-decoration: none;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.15s ease;
        }
        .live-search-item:last-child {
            border-bottom: none;
        }
        .live-search-item:hover {
            background: #f8fafc;
            color: #2563eb;
        }
        .live-search-img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 2px;
            flex-shrink: 0;
        }
        .live-search-info {
            flex-grow: 1;
            min-width: 0;
        }
        .live-search-name {
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #0f172a;
        }
        .live-search-item:hover .live-search-name {
            color: #2563eb;
        }
        .live-search-price {
            font-size: 13px;
            font-weight: 700;
            color: #dc2626;
            font-family: 'JetBrains Mono', monospace;
        }
        .live-search-brand-badge {
            font-size: 11px;
            font-weight: 700;
            background: #eff6ff;
            color: #2563eb;
            padding: 2px 7px;
            border-radius: 4px;
            margin-left: 6px;
        }
        .live-search-footer {
            padding: 9px 14px;
            background: #f8fafc;
            text-align: center;
            font-size: 12.5px;
            font-weight: 600;
            color: #2563eb;
            border-top: 1px solid #e2e8f0;
            display: block;
            text-decoration: none;
            transition: background 0.15s;
        }
        .live-search-footer:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }
        .live-search-empty {
            padding: 24px 16px;
            text-align: center;
            color: #64748b;
            font-size: 13.5px;
        }
        /* Cart Badge */
        .cart-btn {
            position: relative;
            border-radius: 10px;
            padding: 8px 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .cart-badge {
            background: #ef4444;
            color: #ffffff;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 7px;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
        }
        /* Product Cards */
        .laptop-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            transition: all 0.25s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }
        .laptop-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.12);
            border-color: #3b82f6;
        }
        .card-badges-bar {
            min-height: 38px;
            background: #ffffff;
            z-index: 2;
        }
        .card-thumb-wrapper {
            height: 220px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 4px 14px 12px 14px;
            overflow: hidden;
        }
        .product-thumb-img {
            width: 100%;
            height: 100%;
            max-height: 210px;
            object-fit: contain;
            border-radius: 10px;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            filter: drop-shadow(0 6px 16px rgba(0,0,0,0.08));
        }
        .laptop-card:hover .product-thumb-img {
            transform: scale(1.05);
            filter: drop-shadow(0 12px 24px rgba(0,0,0,0.15));
        }
        /* Hero Banner Styling */
        .hero-section {
            background-color: #0b1120 !important;
            background-image: 
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.3) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(30, 58, 138, 0.35) 0px, transparent 50%),
                linear-gradient(135deg, #090d16 0%, #0f172a 100%) !important;
            color: #ffffff !important;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .hero-banner-frame {
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.6), 0 0 30px rgba(59, 130, 246, 0.3);
            transition: transform 0.3s ease;
        }
        .hero-banner-frame:hover {
            transform: scale(1.02);
        }
        .hero-banner-img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }
        .spec-pill {
            font-size: 11px;
            background: #f1f5f9;
            color: #475569;
            border-radius: 6px;
            padding: 3px 8px;
            font-weight: 600;
            display: inline-block;
        }
        .price-tag {
            font-size: 18px;
            font-weight: 800;
            color: #dc2626;
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body>

<!-- HEADER TOP NOTIFICATION -->
<div class="header-top">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-flex align-items-center"><i class="bi bi-shield-check text-success me-1"></i> 100% Laptop Chính Hãng</span>
            <span class="d-none d-md-inline text-secondary opacity-50">&bull;</span>
            <span class="d-none d-md-inline-flex align-items-center"><i class="bi bi-truck text-primary me-1"></i> Giao hàng toàn quốc</span>
            <span class="d-none d-lg-inline text-secondary opacity-50">&bull;</span>
            <span class="d-none d-lg-inline-flex align-items-center"><i class="bi bi-arrow-repeat text-warning me-1"></i> Đổi mới 30 ngày lỗi NSX</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-flex align-items-center"><i class="bi bi-telephone-fill text-info me-1 small"></i> Hotline: <strong class="text-white ms-1">1900 6868</strong></span>
            <?php if ($isStaff): ?>
                <a href="index.php?url=admin" class="header-top-badge">
                    <i class="bi bi-speedometer2 me-1"></i> Quản Trị Hệ Thống
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- NAVBAR CHÍNH -->
<nav class="navbar navbar-expand-lg navbar-main sticky-top">
    <div class="container">
        <!-- Brand Logo -->
        <a class="brand-logo me-xl-4 me-3" href="index.php?url=home">
            <div class="brand-logo-icon">
                <i class="bi bi-laptop-fill"></i>
            </div>
            <div class="brand-logo-text">
                <div class="brand-title">EMSHU<span class="brand-accent">STORE</span></div>
                <div class="brand-tagline">LAPTOP CHÍNH HÃNG</div>
            </div>
        </a>

        <!-- Thanh tìm kiếm tối ưu -->
        <form class="search-box me-auto my-2 my-lg-0 position-relative" action="index.php" method="GET" id="headerSearchForm" autocomplete="off">
            <input type="hidden" name="action" value="client-home">
            <i class="bi bi-search search-icon"></i>
            <input type="text" name="keyword" id="headerSearchInput" class="form-control" 
                   placeholder="Tìm máy, hãng, chip i5/i7/M3, RTX..." 
                   value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" required>
            <button type="submit" class="btn btn-search">Tìm kiếm</button>

            <!-- Dropdown danh sách kết quả Live Search -->
            <div id="liveSearchResults" class="live-search-dropdown shadow-lg d-none"></div>
        </form>

        <!-- Menu bên phải: Tra cứu + Bảo hành + Giỏ hàng + Tài khoản (Unified Nav Action Pills) -->
        <div class="d-flex align-items-center gap-2 gap-xl-3 mt-2 mt-lg-0">
            <!-- Nút Tra cứu đơn hàng -->
            <a href="index.php?action=track-order" class="nav-action-pill d-none d-sm-inline-flex" title="Tra cứu tiến độ giao hàng">
                <div class="nav-action-icon text-warning"><i class="bi bi-truck"></i></div>
                <div class="nav-action-texts d-none d-md-flex">
                    <span class="nav-action-sub">Tra cứu</span>
                    <span class="nav-action-title">Đơn hàng</span>
                </div>
            </a>

            <!-- Nút Kiểm tra bảo hành -->
            <a href="index.php?action=check-warranty" class="nav-action-pill d-none d-lg-inline-flex" title="Kiểm tra bảo hành điện tử theo Serial">
                <div class="nav-action-icon text-success"><i class="bi bi-shield-check"></i></div>
                <div class="nav-action-texts">
                    <span class="nav-action-sub">Kiểm tra</span>
                    <span class="nav-action-title">Bảo hành</span>
                </div>
            </a>

            <!-- Nút Giỏ hàng -->
            <a href="index.php?action=cart" class="nav-action-pill nav-action-cart" title="Xem giỏ hàng của bạn">
                <div class="nav-action-icon text-primary position-relative">
                    <i class="bi bi-cart3"></i>
                </div>
                <div class="nav-action-texts d-none d-sm-flex">
                    <span class="nav-action-sub">Giỏ hàng</span>
                    <span class="nav-action-title">Mua ngay</span>
                </div>
                <span class="cart-badge-pill" id="headerCartCount"><?= (int)$cartCount ?></span>
            </a>

            <!-- Trạng thái tài khoản -->
            <?php if ($isLoggedIn): ?>
                <div class="dropdown">
                    <button class="nav-action-pill dropdown-toggle border-0" 
                            type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: rgba(255, 255, 255, 0.08);">
                        <div class="user-avatar-circle">
                            <i class="bi bi-person-fill text-white"></i>
                        </div>
                        <div class="nav-action-texts d-none d-sm-flex">
                            <span class="nav-action-sub"><?= htmlspecialchars($currentUser['role'] ?? 'Khách hàng') ?></span>
                            <span class="nav-action-title"><?= htmlspecialchars($currentUser['hoTen']) ?></span>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2 rounded-4" style="min-width: 230px;">
                        <li class="px-3 py-2 border-bottom mb-1">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($currentUser['hoTen']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($currentUser['email']) ?></div>
                            <span class="badge bg-primary-subtle text-primary mt-1"><?= htmlspecialchars($currentUser['role']) ?></span>
                        </li>
                        <li>
                            <a class="dropdown-item fw-semibold py-2 rounded-3 text-dark" href="index.php?action=my-orders">
                                <i class="bi bi-bag-check me-2 text-primary"></i>Đơn Hàng Của Tôi
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item fw-semibold py-2 rounded-3 text-dark" href="index.php?action=track-order">
                                <i class="bi bi-truck me-2 text-warning"></i>Tra Cứu Đơn Hàng
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item fw-semibold py-2 rounded-3 text-dark" href="index.php?action=check-warranty">
                                <i class="bi bi-shield-check me-2 text-success"></i>Kiểm Tra Bảo Hành
                            </a>
                        </li>
                        <?php if ($isStaff): ?>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item text-primary fw-semibold py-2 rounded-3" href="index.php?url=admin">
                                    <i class="bi bi-speedometer2 me-2"></i>Trang Quản Trị Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item text-danger fw-semibold py-2 rounded-3" href="index.php?url=logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="index.php?url=login" class="nav-action-pill btn-shimmer" style="padding: 0 18px;">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Đăng Nhập</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- THANH MENU DANH MỤC CAO CẤP (SUB-NAVBAR) -->
<div class="sub-navbar-bar d-none d-md-block">
    <div class="container d-flex align-items-center justify-content-between py-2">
        <div class="sub-nav-scroll-container d-flex align-items-center gap-1">
            <a href="index.php?url=home" class="sub-nav-pill <?= (!isset($_GET['category']) && (!isset($_GET['action']) || $_GET['action'] === 'client-home' || $_GET['action'] === 'home')) ? 'active' : '' ?>">
                <i class="bi bi-house-door-fill"></i> Trang Chủ
            </a>
            <a href="index.php?action=client-home&category=1" class="sub-nav-pill <?= (isset($_GET['category']) && (string)$_GET['category'] === '1') ? 'active' : '' ?>">
                <i class="bi bi-controller text-danger"></i> Laptop Gaming
            </a>
            <a href="index.php?action=client-home&category=2" class="sub-nav-pill <?= (isset($_GET['category']) && (string)$_GET['category'] === '2') ? 'active' : '' ?>">
                <i class="bi bi-briefcase-fill text-primary"></i> Laptop Văn Phòng
            </a>
            <a href="index.php?action=client-home&category=3" class="sub-nav-pill <?= (isset($_GET['category']) && (string)$_GET['category'] === '3') ? 'active' : '' ?>">
                <i class="bi bi-apple text-dark"></i> MacBook Apple
            </a>
            <a href="index.php?action=client-home&category=4" class="sub-nav-pill <?= (isset($_GET['category']) && (string)$_GET['category'] === '4') ? 'active' : '' ?>">
                <i class="bi bi-brush-fill text-warning"></i> Đồ Họa – Kỹ Thuật
            </a>
            <a href="index.php?action=client-home" class="sub-nav-pill">
                <i class="bi bi-fire text-danger"></i> Top Bán Chạy
            </a>
        </div>
        <div class="sub-nav-promo-pill d-none d-lg-flex align-items-center gap-2">
            <span class="pulse-beacon"></span>
            <i class="bi bi-ticket-perforated-fill text-danger"></i>
            <span>Mã <strong class="text-danger font-monospace">KM_BACK2SCHOOL</strong> giảm 5% tới 1.500.000đ</span>
        </div>
    </div>
</div>


<!-- KHUNG THÔNG BÁO FLASH (NẾU CÓ) -->
<div class="container mt-3">
    <?php if (!empty($_SESSION['flash_cart_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm rounded-3" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div><?= htmlspecialchars($_SESSION['flash_cart_success']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_cart_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_cart_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div><?= htmlspecialchars($_SESSION['flash_cart_error']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_cart_error']); ?>
    <?php endif; ?>
</div>

<main class="main-wrapper">

