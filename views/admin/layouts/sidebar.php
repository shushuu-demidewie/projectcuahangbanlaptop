<?php
/**
 * File: views/admin/layouts/sidebar.php
 * Sidebar Animation V2 — Blue & Black Theme (Dark Slate & Sapphire Blue)
 * Thiết kế điều hướng chuẩn giao diện Đen & Xanh Dương hiện đại, sang trọng,
 * hỗ trợ đầy đủ Collapsible Smooth Animations, Live Menu Search, Dynamic Badges,
 * Responsive Mobile Drawer & Keyboard Shortcut (Ctrl+B).
 */

if (!isset($currentUser)) {
    if (class_exists('AuthMiddleware')) {
        $currentUser = AuthMiddleware::getCurrentUser();
    } else {
        @session_start();
        $currentUser = $_SESSION['user'] ?? null;
    }
}
$activeMenu = $activeMenu ?? '';
$userRole   = $currentUser['role'] ?? '';

// Ánh xạ tên vai trò hiển thị
$roleDisplayNames = [
    'admin'         => 'Quản Trị Viên (Admin)',
    'nv_kho'        => 'Nhân Viên Kho',
    'nv_ban_hang'   => 'Nhân Viên Bán Hàng',
    'ke_toan'       => 'Kế Toán',
    'thu_ngan'      => 'Thu Ngân',
    'ky_thuat_vien' => 'Kỹ Thuật Viên',
    'user'          => 'Khách Hàng',
];
$displayRoleName = $roleDisplayNames[$userRole] ?? ($userRole ?: 'Nhân Viên');

// Đếm số liệu thời gian thực cho badge (Đơn hàng chờ duyệt & Laptop sắp hết hàng)
$pendingOrdersCount = 0;
$lowStockCount = 0;
try {
    if (class_exists('Database')) {
        $dbSidebar = Database::getConnection();
        if ($dbSidebar) {
            $pendingOrdersCount = (int)$dbSidebar->query("SELECT COUNT(*) FROM donhang WHERE trangThaiDon = 'CHO_DUYET'")->fetchColumn();
            $lowStockCount = (int)$dbSidebar->query("SELECT COUNT(*) FROM sanpham WHERE soLuongTon <= 5 AND isDeleted = 0")->fetchColumn();
        }
    }
} catch (Throwable $e) {
    $pendingOrdersCount = 0;
    $lowStockCount = 0;
}
?>

<!-- Chống nháy Layout khi tải trang -->
<script>
    (function() {
        try {
            if (localStorage.getItem('emshu_admin_sidebar_collapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed-preload');
            }
        } catch (e) {}
    })();
</script>

<!-- GOOGLE FONTS & BOOTSTRAP ICONS -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    :root {
        --sidebar-width: 265px;
        --sidebar-collapsed-width: 78px;
        --sidebar-bg-main: #090d16;
        --sidebar-bg-surface: #0f172a;
        --sidebar-border: rgba(59, 130, 246, 0.18);
        --sidebar-border-subtle: rgba(255, 255, 255, 0.07);
        --primary-blue: #2563eb;
        --primary-blue-hover: #1d4ed8;
        --primary-blue-light: #60a5fa;
        --primary-blue-glow: rgba(37, 99, 235, 0.45);
        --primary-blue-bg: rgba(37, 99, 235, 0.14);
        --bg-body: #f8fafc;
        --transition-bezier: cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Thu gọn toàn hệ thống */
    html.sidebar-collapsed-preload body,
    body.sidebar-collapsed {
        --sidebar-width: var(--sidebar-collapsed-width) !important;
    }

    body {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
        background-color: var(--bg-body);
        color: #1e293b;
        margin: 0;
        padding: 0;
    }

    /* === KHUNG SIDEBAR CHÍNH (ĐEN & XANH DƯƠNG) === */
    .sidebar {
        width: var(--sidebar-width) !important;
        background: linear-gradient(180deg, var(--sidebar-bg-main) 0%, #0c1220 55%, #070a12 100%) !important;
        color: #e2e8f0 !important;
        position: fixed !important;
        top: 0 !important;
        bottom: 0 !important;
        left: 0 !important;
        z-index: 1040 !important;
        padding: 0 !important;
        margin: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        border-right: 1px solid var(--sidebar-border) !important;
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.45), 0 0 35px rgba(37, 99, 235, 0.05) !important;
        transition: width 0.25s var(--transition-bezier), transform 0.25s var(--transition-bezier) !important;
        backdrop-filter: blur(16px);
    }

    /* === NÚT TOGGLE THU/PHÓNG NỔI TRÊN VIỀN (FLOATING EDGE TOGGLE) === */
    .sidebar-edge-toggle {
        position: absolute !important;
        right: -13px !important;
        top: 22px !important;
        width: 26px !important;
        height: 26px !important;
        border-radius: 50% !important;
        background: #111827 !important;
        border: 1px solid var(--primary-blue-light) !important;
        color: var(--primary-blue-light) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        z-index: 1060 !important;
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.4) !important;
        transition: all 0.22s ease !important;
        padding: 0 !important;
        outline: none !important;
    }
    .sidebar-edge-toggle:hover {
        background: var(--primary-blue) !important;
        color: #ffffff !important;
        box-shadow: 0 0 16px rgba(37, 99, 235, 0.8) !important;
        transform: scale(1.15) !important;
    }
    .sidebar-edge-toggle i {
        font-size: 0.75rem !important;
        transition: transform 0.25s ease !important;
        display: inline-block;
    }

    /* === HEADER: LOGO & THƯƠNG HIỆU === */
    .sidebar-header {
        padding: 16px 14px !important;
        border-bottom: 1px solid var(--sidebar-border-subtle) !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        flex-shrink: 0;
        min-height: 72px;
        position: relative;
    }

    .sidebar-brand-wrapper {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        text-decoration: none !important;
        overflow: hidden;
        flex-grow: 1;
    }

    .sidebar-brand-logo {
        width: 42px !important;
        height: 42px !important;
        min-width: 42px !important;
        border-radius: 12px !important;
        background: linear-gradient(135deg, #1d4ed8 0%, #38bdf8 100%) !important;
        box-shadow: 0 0 16px var(--primary-blue-glow), inset 0 1px 1px rgba(255, 255, 255, 0.3) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #ffffff !important;
        font-size: 1.3rem !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease !important;
        flex-shrink: 0;
    }
    .sidebar-brand-wrapper:hover .sidebar-brand-logo {
        transform: rotate(-6deg) scale(1.08);
        box-shadow: 0 0 22px rgba(59, 130, 246, 0.7) !important;
    }

    .sidebar-brand-info {
        display: flex;
        flex-direction: column;
        white-space: nowrap;
        overflow: hidden;
    }
    .sidebar-brand-title {
        color: #ffffff !important;
        font-size: 1.05rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.3px;
        line-height: 1.2;
    }
    .sidebar-brand-badge {
        color: var(--primary-blue-light);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 3px;
    }
    .sidebar-brand-badge::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 8px #22c55e;
    }

    /* === THANH TÌM KIẾM NHANH (LIVE SEARCH) === */
    .sidebar-search-box {
        padding: 12px 14px 6px 14px !important;
        flex-shrink: 0;
    }
    .sidebar-search-inner {
        position: relative;
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--sidebar-border-subtle);
        border-radius: 11px;
        padding: 7px 12px;
        transition: all 0.25s ease;
    }
    .sidebar-search-inner:focus-within {
        border-color: var(--primary-blue-light);
        background: rgba(37, 99, 235, 0.08);
        box-shadow: 0 0 14px rgba(37, 99, 235, 0.25);
    }
    .sidebar-search-inner i {
        color: #94a3b8;
        font-size: 0.95rem;
        margin-right: 8px;
    }
    .sidebar-search-inner:focus-within i {
        color: var(--primary-blue-light);
    }
    .sidebar-search-input {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        color: #f8fafc !important;
        font-size: 0.85rem !important;
        width: 100% !important;
        padding: 0 !important;
    }
    .sidebar-search-input::placeholder {
        color: #64748b;
        font-size: 0.82rem;
    }
    .sidebar-search-clear {
        color: #94a3b8;
        cursor: pointer;
        font-size: 0.85rem;
        display: none;
        padding-left: 4px;
    }
    .sidebar-search-clear:hover {
        color: #ffffff;
    }

    /* Tiêu đề nhóm */
    .sidebar-section-title {
        padding: 12px 18px 4px 18px !important;
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 1.1px !important;
        color: #64748b !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }

    /* === DANH SÁCH MENU (NAV ITEMS) === */
    .sidebar-nav {
        list-style: none !important;
        padding: 6px 10px !important;
        margin: 0 !important;
        flex-grow: 1 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: thin !important;
        scrollbar-color: rgba(59, 130, 246, 0.25) transparent !important;
    }
    /* Thanh cuộn siêu mỏng, không thô và tiệp màu giao diện */
    .sidebar-nav::-webkit-scrollbar {
        width: 3.5px !important;
    }
    .sidebar-nav::-webkit-scrollbar-track {
        background: transparent !important;
    }
    .sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.25) !important;
        border-radius: 10px !important;
    }
    .sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: var(--primary-blue-light) !important;
    }

    .sidebar-nav li {
        margin: 0 0 3px 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    /* Nav Link */
    .sidebar-nav .nav-link {
        color: #94a3b8 !important;
        padding: 9.5px 12px !important;
        border-radius: 11px !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        font-size: 0.90rem !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        transition: all 0.2s var(--transition-bezier) !important;
        position: relative !important;
        border: 1px solid transparent !important;
        line-height: 1.35 !important;
        overflow: hidden;
    }

    .sidebar-nav .nav-link .nav-icon {
        font-size: 1.18rem !important;
        width: 26px !important;
        height: 26px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #94a3b8 !important;
        transition: all 0.2s ease !important;
        flex-shrink: 0 !important;
    }

    .sidebar-nav .nav-link .nav-text {
        white-space: nowrap !important;
        flex-grow: 1 !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Badges Xanh & Đỏ */
    .sidebar-nav .nav-badge {
        background: rgba(37, 99, 235, 0.25) !important;
        color: #bfdbfe !important;
        border: 1px solid rgba(59, 130, 246, 0.45) !important;
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.35) !important;
        border-radius: 999px !important;
        padding: 2px 7px !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        letter-spacing: 0.2px;
    }
    .sidebar-nav .nav-badge.badge-danger-neon {
        background: rgba(239, 68, 68, 0.22) !important;
        color: #fecaca !important;
        border-color: rgba(239, 68, 68, 0.5) !important;
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.35) !important;
    }

    /* Hover */
    .sidebar-nav .nav-link:hover {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #ffffff !important;
        transform: translateX(4px) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    .sidebar-nav .nav-link:hover .nav-icon {
        color: var(--primary-blue-light) !important;
        transform: scale(1.15) !important;
        filter: drop-shadow(0 0 8px rgba(96, 165, 250, 0.6)) !important;
    }

    /* Active (Xanh Dương Hiện Đại) */
    .sidebar-nav .nav-link.active {
        background: linear-gradient(90deg, rgba(37, 99, 235, 0.28) 0%, rgba(59, 130, 246, 0.08) 80%, transparent 100%) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        border: 1px solid rgba(59, 130, 246, 0.4) !important;
        box-shadow: inset 0 0 14px rgba(37, 99, 235, 0.15) !important;
        transform: none !important;
    }
    .sidebar-nav .nav-link.active::before {
        content: '' !important;
        position: absolute !important;
        left: 0 !important;
        top: 15% !important;
        bottom: 15% !important;
        width: 4px !important;
        border-radius: 0 4px 4px 0 !important;
        background: var(--primary-blue-light) !important;
        box-shadow: 0 0 10px var(--primary-blue-light), 0 0 20px var(--primary-blue) !important;
    }
    .sidebar-nav .nav-link.active .nav-icon {
        color: #93c5fd !important;
        filter: drop-shadow(0 0 10px rgba(96, 165, 250, 0.85)) !important;
    }

    /* === KHỐI FOOTER (USER CARD + ĐĂNG XUẤT) === */
    .sidebar-footer {
        padding: 10px 10px 12px 10px !important;
        border-top: 1px solid var(--sidebar-border-subtle) !important;
        background: rgba(8, 11, 18, 0.95) !important;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .sidebar-pro-card {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.14) 0%, rgba(15, 23, 42, 0.6) 100%);
        border: 1px solid rgba(59, 130, 246, 0.25);
        border-radius: 12px;
        padding: 9px 11px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        text-decoration: none;
        color: inherit;
        transition: all 0.25s ease;
    }
    .sidebar-pro-card:hover {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.22) 0%, rgba(30, 58, 138, 0.2) 100%);
        border-color: var(--primary-blue-light);
        box-shadow: 0 0 16px rgba(37, 99, 235, 0.3);
    }
    .sidebar-pro-card-left {
        display: flex;
        align-items: center;
        gap: 9px;
        overflow: hidden;
    }
    .sidebar-pro-avatar {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: rgba(37, 99, 235, 0.25);
        border: 1px solid #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #93c5fd;
        font-weight: 700;
        font-size: 0.85rem;
        flex-shrink: 0;
    }
    .sidebar-pro-details {
        display: flex;
        flex-direction: column;
        white-space: nowrap;
        overflow: hidden;
    }
    .sidebar-pro-name {
        color: #ffffff;
        font-size: 0.84rem;
        font-weight: 700;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    .sidebar-pro-role {
        color: #94a3b8;
        font-size: 0.70rem;
    }
    .sidebar-pro-arrow {
        color: var(--primary-blue-light);
        font-size: 0.85rem;
    }

    /* Nút Đăng xuất */
    .sidebar-logout-btn {
        background: rgba(239, 68, 68, 0.08) !important;
        border: 1px solid rgba(239, 68, 68, 0.25) !important;
        color: #fca5a5 !important;
        padding: 8px 12px !important;
        border-radius: 10px !important;
        font-size: 0.86rem !important;
        font-weight: 600 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        text-decoration: none !important;
        transition: all 0.22s ease !important;
    }
    .sidebar-logout-btn:hover {
        background: #ef4444 !important;
        color: #ffffff !important;
        border-color: #ef4444 !important;
        box-shadow: 0 0 16px rgba(239, 68, 68, 0.5) !important;
    }

    /* ==============================================================
       QUY TẮC TRIỆT ĐỂ CHO TRẠNG THÁI THU GỌN (COLLAPSED STATE)
       Ẩn 100% bằng display: none để các icon đồng bộ và căn giữa thẳng hàng
       ============================================================== */
    body.sidebar-collapsed .sidebar-nav .nav-link > :not(.nav-icon),
    body.sidebar-collapsed .sidebar-nav .nav-link .bi-arrow-up-right,
    body.sidebar-collapsed .sidebar-brand-info,
    body.sidebar-collapsed .sidebar-search-box,
    body.sidebar-collapsed .sidebar-section-title,
    body.sidebar-collapsed .sidebar-nav .nav-text,
    body.sidebar-collapsed .sidebar-nav .nav-badge,
    body.sidebar-collapsed .sidebar-pro-details,
    body.sidebar-collapsed .sidebar-pro-arrow,
    body.sidebar-collapsed .sidebar-logout-btn span {
        display: none !important;
    }

    /* Header khi thu gọn: căn giữa logo */
    body.sidebar-collapsed .sidebar-header {
        justify-content: center !important;
        padding: 14px 0 !important;
    }
    body.sidebar-collapsed .sidebar-brand-wrapper {
        justify-content: center !important;
        flex-grow: 0 !important;
    }

    /* Nút Toggle viền đổi hướng mũi tên */
    body.sidebar-collapsed .sidebar-edge-toggle i {
        transform: rotate(180deg) !important;
    }

    /* Ẩn scrollbar khi thu gọn để không chiếm chiều ngang */
    body.sidebar-collapsed .sidebar-nav {
        padding: 8px 6px !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    body.sidebar-collapsed .sidebar-nav::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
    }

    /* Toàn bộ các icon điều hướng căn giữa chuẩn 100% thẳng hàng */
    body.sidebar-collapsed .sidebar-nav .nav-link {
        justify-content: center !important;
        padding: 11px 0 !important;
        gap: 0 !important;
        width: 100% !important;
        text-align: center !important;
    }
    body.sidebar-collapsed .sidebar-nav .nav-link .nav-icon {
        margin: 0 auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    body.sidebar-collapsed .sidebar-nav .nav-link:hover {
        transform: scale(1.1) !important;
    }

    /* Avatar và nút đăng xuất căn giữa đồng bộ hoàn hảo */
    body.sidebar-collapsed .sidebar-pro-card {
        justify-content: center !important;
        padding: 0 !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        margin: 0 auto 4px auto !important;
        width: 44px !important;
        height: 44px !important;
        display: flex !important;
        align-items: center !important;
    }
    body.sidebar-collapsed .sidebar-pro-card-left {
        justify-content: center !important;
        gap: 0 !important;
        width: 100% !important;
    }
    body.sidebar-collapsed .sidebar-pro-avatar {
        width: 40px !important;
        height: 40px !important;
        margin: 0 auto !important;
        border-radius: 10px !important;
    }
    body.sidebar-collapsed .sidebar-logout-btn {
        padding: 0 !important;
        width: 40px !important;
        height: 40px !important;
        margin: 0 auto !important;
        border-radius: 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* === FLOATING TOOLTIP KHI THU GỌN === */
    body.sidebar-collapsed .sidebar-nav .nav-link:hover::after {
        content: attr(data-title);
        position: fixed;
        left: 88px;
        background: #0f172a;
        color: #ffffff;
        padding: 7px 14px;
        border-radius: 9px;
        border: 1px solid rgba(59, 130, 246, 0.45);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5), 0 0 14px rgba(37, 99, 235, 0.35);
        font-size: 0.82rem;
        font-weight: 600;
        white-space: nowrap;
        z-index: 1090;
        pointer-events: none;
        animation: blueTooltipIn 0.16s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes blueTooltipIn {
        from {
            opacity: 0;
            transform: translateX(-6px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* === BỐ CỤC MAIN CONTENT TOÀN HỆ THỐNG === */
    .main-content {
        margin-left: var(--sidebar-width) !important;
        padding: 24px 32px !important;
        min-height: 100vh !important;
        transition: margin-left 0.25s var(--transition-bezier) !important;
    }

    .top-navbar {
        background: #ffffff !important;
        border-radius: 14px !important;
        padding: 16px 24px !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03) !important;
        border: 1px solid #e2e8f0 !important;
        margin-bottom: 24px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 16px !important;
    }

    /* Mobile Backdrop */
    .sidebar-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.65);
        backdrop-filter: blur(4px);
        z-index: 1035;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    body.mobile-sidebar-open .sidebar-backdrop {
        opacity: 1;
        pointer-events: auto;
    }

    .mobile-sidebar-toggle {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1d4ed8 0%, #38bdf8 100%);
        color: #ffffff;
        box-shadow: 0 0 20px rgba(37, 99, 235, 0.5);
        border: none;
        outline: none;
        z-index: 1030;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        cursor: pointer;
    }

    @media (max-width: 991.98px) {
        .mobile-sidebar-toggle {
            display: flex;
        }
        .sidebar {
            transform: translateX(-100%) !important;
            width: 265px !important;
        }
        body.mobile-sidebar-open .sidebar {
            transform: translateX(0) !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 16px !important;
        }
        .sidebar-edge-toggle {
            display: none !important;
        }
    }
</style>

<!-- Mobile Overlay Backdrop -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Nút mở menu trên di động -->
<button class="mobile-sidebar-toggle" id="mobileSidebarToggle" title="Mở danh mục điều khiển">
    <i class="bi bi-list"></i>
</button>

<!-- === ASIDE SIDEBAR V2 BLUE & BLACK === -->
<aside class="sidebar" id="appSidebar">
    <!-- Nút Thu/Phóng Nổi ở Viền Phải (Floating Edge Toggle) -->
    <button class="sidebar-edge-toggle" id="sidebarEdgeToggle" title="Thu gọn / Mở rộng Sidebar (Ctrl + B)">
        <i class="bi bi-chevron-left" id="sidebarToggleIcon"></i>
    </button>

    <!-- 1. Header (Logo + Tên Hệ Thống) -->
    <div class="sidebar-header">
        <a href="index.php?url=admin" class="sidebar-brand-wrapper" title="Bảng điều khiển quản trị">
            <div class="sidebar-brand-logo">
                <i class="bi bi-laptop"></i>
            </div>
            <div class="sidebar-brand-info">
                <span class="sidebar-brand-title">EmShu Store Admin</span>
            </div>
        </a>
    </div>

    <!-- 2. Thanh Tìm Kiếm Menu Nhanh (Live Search) -->
    <div class="sidebar-search-box">
        <div class="sidebar-search-inner">
            <i class="bi bi-search"></i>
            <input type="text" id="sidebarSearchInput" class="sidebar-search-input" placeholder="Tìm kiếm tính năng..." autocomplete="off">
            <i class="bi bi-x-circle sidebar-search-clear" id="sidebarSearchClear" title="Xóa tìm kiếm"></i>
        </div>
    </div>

    <!-- 3. Tiêu đề nhóm Menu -->
    <div class="sidebar-section-title">
        <span>ĐIỀU HƯỚNG CHÍNH</span>
        <i class="bi bi-grid-fill" style="font-size: 0.65rem; color: #3b82f6;"></i>
    </div>

    <!-- 4. Danh sách các Mục Điều Hướng -->
    <ul class="sidebar-nav" id="sidebarNavList">
        <!-- 4.1 Bảng điều khiển -->
        <li class="menu-item" data-keywords="dashboard bang dieu khien thong ke kpi tong quan">
            <a href="index.php?url=admin" class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>" data-title="Bảng điều khiển">
                <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
                <span class="nav-text">Bảng điều khiển</span>
            </a>
        </li>

        <!-- 4.2 Quản lý Laptop -->
        <li class="menu-item" data-keywords="laptop san pham quan ly laptop ton kho gia ban danh muc">
            <a href="index.php?action=admin-products" class="nav-link <?= $activeMenu === 'products' ? 'active' : '' ?>" data-title="Quản lý Laptop">
                <span class="nav-icon"><i class="bi bi-box-seam"></i></span>
                <span class="nav-text">Quản lý Laptop</span>
                <?php if ($lowStockCount > 0): ?>
                    <span class="nav-badge badge-danger-neon" title="<?= $lowStockCount ?> laptop sắp hết hàng"><?= $lowStockCount ?></span>
                <?php endif; ?>
            </a>
        </li>

        <!-- 4.3 Quản lý Nhập kho & Tra cứu Serial/IMEI -->
        <?php if (in_array($userRole, ['admin', 'nv_kho'], true)): ?>
        <li class="menu-item" data-keywords="nhap kho phieu nhap nha cung cap so luong nhap">
            <a href="index.php?action=admin-imports" class="nav-link <?= $activeMenu === 'imports' ? 'active' : '' ?>" data-title="Quản lý Nhập kho">
                <span class="nav-icon"><i class="bi bi-box-arrow-in-down"></i></span>
                <span class="nav-text">Quản lý Nhập kho</span>
            </a>
        </li>
        <li class="menu-item" data-keywords="tra cuu serial imei quet ma barcode bao hanh may">
            <a href="index.php?action=admin-serials" class="nav-link <?= $activeMenu === 'serials' ? 'active' : '' ?>" data-title="Tra cứu Serial/IMEI">
                <span class="nav-icon"><i class="bi bi-upc-scan"></i></span>
                <span class="nav-text">Tra cứu Serial/IMEI</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- 4.4 Bán hàng POS, Quản lý Đơn hàng & Khuyến mãi -->
        <?php if (in_array($userRole, ['admin', 'nv_ban_hang', 'thu_ngan'], true)): ?>
        <li class="menu-item" data-keywords="pos ban hang tai quay tinh tien hoa don in bill">
            <a href="index.php?action=admin-pos" class="nav-link <?= $activeMenu === 'pos' ? 'active' : '' ?>" data-title="Bán Hàng POS">
                <span class="nav-icon"><i class="bi bi-calculator"></i></span>
                <span class="nav-text">Bán Hàng POS</span>
            </a>
        </li>
        <li class="menu-item" data-keywords="don hang quan ly don dat hang cho duyet giao hang van chuyen">
            <a href="index.php?action=admin-orders" class="nav-link <?= $activeMenu === 'orders' ? 'active' : '' ?>" data-title="Quản lý Đơn hàng">
                <span class="nav-icon"><i class="bi bi-cart3"></i></span>
                <span class="nav-text">Quản lý Đơn hàng</span>
                <?php if ($pendingOrdersCount > 0): ?>
                    <span class="nav-badge" title="<?= $pendingOrdersCount ?> đơn chờ duyệt"><?= $pendingOrdersCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="menu-item" data-keywords="khuyen mai voucher giam gia ma coupon campaign">
            <a href="index.php?action=admin-promotions" class="nav-link <?= $activeMenu === 'promotions' ? 'active' : '' ?>" data-title="Quản lý Khuyến mãi">
                <span class="nav-icon"><i class="bi bi-ticket-perforated"></i></span>
                <span class="nav-text">Quản lý Khuyến mãi</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- 4.5 Quản lý Bảo hành -->
        <?php if (in_array($userRole, ['admin', 'ky_thuat_vien', 'nv_ban_hang', 'thu_ngan'], true)): ?>
        <li class="menu-item" data-keywords="bao hanh sua chua bao tri tiep nhan may ky thuat vien">
            <a href="index.php?action=admin-warranty" class="nav-link <?= $activeMenu === 'warranty' ? 'active' : '' ?>" data-title="Quản lý Bảo hành">
                <span class="nav-icon"><i class="bi bi-shield-check"></i></span>
                <span class="nav-text">Quản lý Bảo hành</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- 4.6 Quản lý Tài khoản (Chỉ Admin) -->
        <?php if ($userRole === 'admin'): ?>
        <li class="menu-item" data-keywords="tai khoan nguoi dung phan quyen nhan vien admin khach hang">
            <a href="index.php?action=admin-accounts" class="nav-link <?= $activeMenu === 'accounts' ? 'active' : '' ?>" data-title="Quản lý Tài khoản">
                <span class="nav-icon"><i class="bi bi-people"></i></span>
                <span class="nav-text">Quản lý Tài khoản</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- 4.7 Báo cáo doanh số & Tồn kho -->
        <?php if (in_array($userRole, ['admin', 'ke_toan'], true)): ?>
        <li class="menu-item" data-keywords="bao cao doanh so doanh thu loi nhuan ton kho thong ke bieu do">
            <a href="index.php?action=admin-reports" class="nav-link <?= $activeMenu === 'reports' ? 'active' : '' ?>" data-title="Báo cáo doanh số">
                <span class="nav-icon"><i class="bi bi-graph-up"></i></span>
                <span class="nav-text">Báo cáo doanh số</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- 4.8 Sao lưu & Phục hồi CSDL (Chỉ Admin) -->
        <?php if ($userRole === 'admin'): ?>
        <li class="menu-item" data-keywords="sao luu phuc hoi csdl database backup restore borg sql">
            <a href="index.php?action=admin-backups" class="nav-link <?= $activeMenu === 'backups' ? 'active' : '' ?>" data-title="Sao lưu CSDL">
                <span class="nav-icon"><i class="bi bi-database-check"></i></span>
                <span class="nav-text">Sao lưu CSDL</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- 4.9 Xem Website Khách Hàng -->
        <li class="menu-item" data-keywords="website khach hang xem shop cua hang trang chu client">
            <a href="index.php?action=client-home" class="nav-link" target="_blank" data-title="Xem Website Khách">
                <span class="nav-icon"><i class="bi bi-shop"></i></span>
                <span class="nav-text">Xem Website Khách</span>
                <i class="bi bi-arrow-up-right ms-auto" style="font-size: 0.75rem; color: #64748b;"></i>
            </a>
        </li>
    </ul>

    <!-- 5. Khối Footer (User Card + Nút Đăng Xuất) -->
    <div class="sidebar-footer">
        <a href="index.php?action=client-home" target="_blank" class="sidebar-pro-card" title="Xem cửa hàng khách hàng">
            <div class="sidebar-pro-card-left">
                <div class="sidebar-pro-avatar">
                    <?= mb_strtoupper(mb_substr($currentUser['hoTen'] ?? 'AD', 0, 1, 'UTF-8'), 'UTF-8') ?>
                </div>
                <div class="sidebar-pro-details">
                    <span class="sidebar-pro-name"><?= htmlspecialchars($currentUser['hoTen'] ?? 'Quản Trị') ?></span>
                    <span class="sidebar-pro-role"><?= htmlspecialchars($displayRoleName) ?></span>
                </div>
            </div>
            <i class="bi bi-box-arrow-up-right sidebar-pro-arrow"></i>
        </a>

        <a href="index.php?url=logout" class="sidebar-logout-btn" title="Đăng xuất khỏi hệ thống">
            <i class="bi bi-box-arrow-right"></i>
            <span>Đăng Xuất</span>
        </a>
    </div>
</aside>

<!-- === SCRIPT ĐIỀU KHIỂN TƯƠNG TÁC (JS) === -->
<script>
(function() {
    function initSidebar() {
        const body = document.body;
        const edgeToggle = document.getElementById('sidebarEdgeToggle');
        const mobileToggle = document.getElementById('mobileSidebarToggle');
        const backdrop = document.getElementById('sidebarBackdrop');
        const searchInput = document.getElementById('sidebarSearchInput');
        const searchClear = document.getElementById('sidebarSearchClear');
        const menuItems = document.querySelectorAll('#sidebarNavList .menu-item');

        if (!body) return;

        // Trạng thái thu gọn từ localStorage
        const isCollapsed = localStorage.getItem('emshu_admin_sidebar_collapsed') === '1';
        if (isCollapsed) {
            body.classList.add('sidebar-collapsed');
        } else {
            body.classList.remove('sidebar-collapsed');
            document.documentElement.classList.remove('sidebar-collapsed-preload');
        }

        // Toggle trạng thái
        function toggleSidebar() {
            body.classList.toggle('sidebar-collapsed');
            const collapsedNow = body.classList.contains('sidebar-collapsed');
            if (collapsedNow) {
                document.documentElement.classList.add('sidebar-collapsed-preload');
                localStorage.setItem('emshu_admin_sidebar_collapsed', '1');
            } else {
                document.documentElement.classList.remove('sidebar-collapsed-preload');
                localStorage.setItem('emshu_admin_sidebar_collapsed', '0');
            }
        }

        if (edgeToggle) {
            edgeToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });
        }

        // Phím tắt Ctrl + B / Cmd + B
        window.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
                e.preventDefault();
                toggleSidebar();
            }
        });

        // Mobile Drawer
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                body.classList.toggle('mobile-sidebar-open');
            });
        }
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                body.classList.remove('mobile-sidebar-open');
            });
        }

        // Live Search Filter
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                if (query.length > 0) {
                    if (searchClear) searchClear.style.display = 'inline-block';
                    if (body.classList.contains('sidebar-collapsed')) {
                        toggleSidebar();
                    }
                } else {
                    if (searchClear) searchClear.style.display = 'none';
                }

                menuItems.forEach(function(item) {
                    const text = item.innerText.toLowerCase();
                    const keywords = item.getAttribute('data-keywords') || '';
                    if (query === '' || text.includes(query) || keywords.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            if (searchClear) {
                searchClear.addEventListener('click', function() {
                    searchInput.value = '';
                    searchClear.style.display = 'none';
                    menuItems.forEach(function(item) {
                        item.style.display = '';
                    });
                    searchInput.focus();
                });
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }
})();
</script>
