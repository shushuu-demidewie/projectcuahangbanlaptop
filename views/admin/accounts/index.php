<?php
/**
 * File: views/admin/accounts/index.php
 * Màn hình Quản Lý Tài Khoản Hệ Thống (Dành riêng cho Ban Quản Trị Admin)
 */

$currentAdminMaTk = $_SESSION['user']['maTk'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tài Khoản - EmShu Store System</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #2563eb;
            --bg-body: #f8fafc;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: #1e293b;
        }
        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #0f172a;
            color: #94a3b8;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-brand {
            padding: 24px;
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-nav {
            list-style: none;
            padding: 16px 12px;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #94a3b8;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
            margin-bottom: 4px;
        }
        .sidebar-nav .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255,255,255,0.06);
        }
        .sidebar-nav .nav-link.active {
            color: #ffffff;
            background-color: var(--primary-color);
            font-weight: 600;
        }
        /* Main Layout */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 24px 32px;
        }
        .top-navbar {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 24px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.08);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .card-custom {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .role-badge {
            font-size: 12px;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<?php 
$activeMenu = 'accounts';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT -->
<main class="main-content">
    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-gear fs-3 text-primary"></i>
            <div>
                <h5 class="mb-0 fw-bold">Quản Lý Tài Khoản Hệ Thống</h5>
                <small class="text-muted">Cấp phát tài khoản nhân sự nội bộ, phân quyền và kiểm soát người dùng</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                <i class="bi bi-person-plus-fill"></i>
                <span>Thêm Mới Nhân Viên</span>
            </button>
        </div>
    </header>

    <!-- THÔNG BÁO FLASH NẾU CÓ -->
    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php 
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        ?>
    <?php endif; ?>

    <!-- 4 THẺ THỐNG KÊ NHANH -->
    <div class="row g-3 mb-4">
        <!-- 1. Toàn bộ tài khoản -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Toàn Bộ Tài Khoản</div>
                        <h4 class="fw-extrabold text-dark my-2 font-monospace">
                            <?= number_format($stats['total']) ?>
                        </h4>
                        <span class="badge bg-light text-secondary border small">
                            Đã khóa: <?= $stats['locked'] ?>
                        </span>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Ban Quản Trị -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Ban Quản Trị (Admin)</div>
                        <h4 class="fw-extrabold text-danger my-2 font-monospace">
                            <?= number_format($stats['admin']) ?>
                        </h4>
                        <span class="badge bg-danger-subtle text-danger small">Toàn quyền hệ thống</span>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Nhân Sự Nội Bộ -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Nhân Sự Nội Bộ</div>
                        <h4 class="fw-extrabold text-success my-2 font-monospace">
                            <?= number_format($stats['staff']) ?>
                        </h4>
                        <span class="text-muted small">Bán hàng, Kho, KTV, Thu ngân...</span>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Khách hàng -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Khách Hàng (User)</div>
                        <h4 class="fw-extrabold text-info my-2 font-monospace">
                            <?= number_format($stats['customer']) ?>
                        </h4>
                        <span class="badge bg-info-subtle text-info small">Đăng ký mua online</span>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-person-heart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- THANH BỘ LỌC TÌM KIẾM -->
    <div class="card-custom p-3 mb-4">
        <form method="GET" action="index.php" class="row g-2 align-items-center">
            <input type="hidden" name="action" value="admin-accounts">

            <!-- Lọc theo vai trò / chức vụ -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Chức vụ / Vai trò:</label>
                <select name="role" class="form-select">
                    <option value="">-- Tất cả chức vụ --</option>
                    <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>Quản Trị Viên (Admin)</option>
                    <option value="nv_ban_hang" <?= ($role === 'nv_ban_hang') ? 'selected' : '' ?>>Nhân Viên Bán Hàng</option>
                    <option value="nv_kho" <?= ($role === 'nv_kho') ? 'selected' : '' ?>>Nhân Viên Kho</option>
                    <option value="thu_ngan" <?= ($role === 'thu_ngan') ? 'selected' : '' ?>>Thu Ngân</option>
                    <option value="ke_toan" <?= ($role === 'ke_toan') ? 'selected' : '' ?>>Kế Toán</option>
                    <option value="ky_thuat_vien" <?= ($role === 'ky_thuat_vien') ? 'selected' : '' ?>>Kỹ Thuật Viên</option>
                    <option value="user" <?= ($role === 'user') ? 'selected' : '' ?>>Khách Hàng (User)</option>
                </select>
            </div>

            <!-- Lọc theo trạng thái -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Trạng thái tài khoản:</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Đang Hoạt Động (Active)</option>
                    <option value="locked" <?= ($status === 'locked') ? 'selected' : '' ?>>Đã Bị Khóa (Locked)</option>
                </select>
            </div>

            <!-- Tìm kiếm theo từ khóa -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Tìm kiếm từ khóa:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control" placeholder="Nhập họ tên, tên đăng nhập, email, SĐT..." value="<?= htmlspecialchars($keyword) ?>">
                </div>
            </div>

            <!-- Nút lọc & Reset -->
            <div class="col-md-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
                <a href="index.php?action=admin-accounts" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH TÀI KHOẢN -->
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 100px;">Mã TK</th>
                        <th>Họ Tên & Khách Hàng</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Email Liên Hệ</th>
                        <th style="width: 160px;">Chức Vụ</th>
                        <th style="width: 130px;" class="text-center">Trạng Thái</th>
                        <th style="width: 130px;">Ngày Tạo</th>
                        <th style="width: 160px;" class="text-center">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                                Không tìm thấy tài khoản nào phù hợp với bộ lọc!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($accounts as $acc): ?>
                            <?php 
                            $isCurrentAdmin = ($acc['maTk'] === $currentAdminMaTk);
                            
                            // Map badge màu theo từng Role
                            $roleInfo = match($acc['role']) {
                                'admin'         => ['label' => 'Quản Trị Viên',   'class' => 'bg-danger text-white'],
                                'nv_ban_hang'   => ['label' => 'Bán Hàng',         'class' => 'bg-primary text-white'],
                                'nv_kho'        => ['label' => 'Thủ Kho',          'class' => 'bg-warning text-dark'],
                                'thu_ngan'      => ['label' => 'Thu Ngân',         'class' => 'bg-success text-white'],
                                'ke_toan'       => ['label' => 'Kế Toán',          'class' => 'bg-info text-dark'],
                                'ky_thuat_vien' => ['label' => 'Kỹ Thuật Viên',    'class' => 'bg-dark text-white'],
                                default         => ['label' => 'Khách Hàng',       'class' => 'bg-light text-secondary border']
                            };
                            ?>
                            <tr class="<?= $isCurrentAdmin ? 'table-primary table-opacity-10' : '' ?>">
                                <td>
                                    <code class="fw-bold text-primary font-monospace"><?= htmlspecialchars($acc['maTk']) ?></code>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <?= htmlspecialchars($acc['hoTen']) ?>
                                        <?php if ($isCurrentAdmin): ?>
                                            <span class="badge bg-danger ms-1 small" style="font-size: 10px;">Bạn</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($acc['soDienThoai'])): ?>
                                        <small class="text-muted">
                                            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($acc['soDienThoai']) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-semibold text-secondary font-monospace"><?= htmlspecialchars($acc['tenTaiKhoan']) ?></span>
                                </td>
                                <td>
                                    <span class="small text-muted"><?= htmlspecialchars($acc['email']) ?></span>
                                </td>
                                <td>
                                    <span class="role-badge <?= $roleInfo['class'] ?> shadow-sm">
                                        <?= $roleInfo['label'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($acc['trangThai'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i>Hoạt động
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                            <i class="bi bi-lock-fill me-1"></i>Đã khóa
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="small text-muted"><?= date('d/m/Y', strtotime($acc['ngayTao'])) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Nút Khóa / Mở khóa -->
                                        <?php if ($isCurrentAdmin): ?>
                                            <button type="button" class="btn btn-outline-secondary" disabled title="Không thể tự khóa tài khoản của bạn">
                                                <i class="bi bi-shield-slash"></i>
                                            </button>
                                        <?php else: ?>
                                            <?php if ($acc['trangThai'] === 'active'): ?>
                                                <a href="index.php?action=admin-account-toggle&maTk=<?= urlencode($acc['maTk']) ?>" 
                                                   class="btn btn-outline-danger" 
                                                   title="Khóa tài khoản này"
                                                   onclick="return confirm('Bạn có chắc chắn muốn KHÓA tài khoản [<?= htmlspecialchars($acc['hoTen']) ?>]? Người dùng này sẽ không thể đăng nhập vào hệ thống!');">
                                                    <i class="bi bi-lock"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="index.php?action=admin-account-toggle&maTk=<?= urlencode($acc['maTk']) ?>" 
                                                   class="btn btn-outline-success" 
                                                   title="Mở khóa tài khoản này"
                                                   onclick="return confirm('MỞ KHÓA tài khoản [<?= htmlspecialchars($acc['hoTen']) ?>] để tiếp tục đăng nhập?');">
                                                    <i class="bi bi-unlock"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Nút Đặt lại mật khẩu -->
                                        <button type="button" 
                                                class="btn btn-outline-warning text-dark" 
                                                title="Đặt lại mật khẩu"
                                                onclick='openResetPwdModal(<?= json_encode($acc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                            <i class="bi bi-key"></i>
                                        </button>

                                        <!-- Nút Xem chi tiết & Đổi phân quyền -->
                                        <button type="button" 
                                                class="btn btn-outline-primary" 
                                                title="Xem chi tiết & Phân quyền"
                                                onclick='openDetailModal(<?= json_encode($acc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PHÂN TRANG -->
        <?php if ($totalPages > 1): ?>
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Hiển thị trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng <?= $totalItems ?> tài khoản)
                </small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Trang trước -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=admin-accounts&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>&keyword=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>">
                                &laquo;
                            </a>
                        </li>

                        <!-- Danh sách trang -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?action=admin-accounts&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>&keyword=<?= urlencode($keyword) ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Trang sau -->
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=admin-accounts&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>&keyword=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>">
                                &raquo;
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- ============================================================== -->
<!-- MODAL 1: THÊM MỚI TÀI KHOẢN NHÂN VIÊN                          -->
<!-- ============================================================== -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="index.php?action=admin-account-store" method="POST">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="bi bi-person-plus-fill me-2"></i>Thêm Mới Tài Khoản Nhân Viên
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Họ và tên -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Họ và Tên Nhân Viên: <span class="text-danger">*</span></label>
                        <input type="text" name="hoTen" class="form-control" placeholder="Ví dụ: Nguyễn Văn Hoàng" required>
                    </div>

                    <!-- Email công việc -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Email Liên Hệ / Công Việc: <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="Ví dụ: hoang.nv@emshustore.vn" required>
                    </div>

                    <!-- Tên đăng nhập -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Tên Đăng Nhập (Username): <span class="text-danger">*</span></label>
                        <input type="text" name="tenTaiKhoan" class="form-control font-monospace" placeholder="Ví dụ: hoangnv_banhang" required>
                    </div>

                    <!-- Mật khẩu khởi tạo -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Mật Khẩu Ban Đầu: <span class="text-danger">*</span></label>
                        <input type="password" name="matKhau" class="form-control" placeholder="Tối thiểu 6 ký tự..." minlength="6" required>
                    </div>

                    <!-- Phân quyền Role -->
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">Phân Quyền / Chức Vụ Hệ Thống: <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="">-- Chọn chức vụ --</option>
                            <option value="nv_ban_hang">Nhân Viên Bán Hàng (Quản lý đơn hàng, tư vấn)</option>
                            <option value="nv_kho">Nhân Viên Kho (Nhập hàng, kiểm kê Serial/IMEI)</option>
                            <option value="thu_ngan">Thu Ngân (Thanh toán, in hóa đơn)</option>
                            <option value="ky_thuat_vien">Kỹ Thuật Viên (Bảo hành, kiểm tra máy)</option>
                            <option value="ke_toan">Kế Toán (Báo cáo doanh số & tài chính)</option>
                            <option value="admin">Quản Trị Viên (Toàn quyền hệ thống)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy Bỏ</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-save me-1"></i> Khởi Tạo Tài Khoản
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: ĐẶT LẠI MẬT KHẨU                                      -->
<!-- ============================================================== -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="index.php?action=admin-account-reset-pwd" method="POST">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-warning text-dark">
                        <i class="bi bi-key-fill text-warning me-2"></i>Đặt Lại Mật Khẩu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="small">Tài khoản: <strong class="text-dark" id="resetModalHoTen"></strong></div>
                        <div class="small">Username: <strong class="text-primary font-monospace" id="resetModalUsername"></strong> | Mã: <code id="resetModalMaTk"></code></div>
                    </div>

                    <input type="hidden" name="maTk" id="resetFormMaTk">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Mật Khẩu Mới: <span class="text-danger">*</span></label>
                        <input type="password" name="newPassword" class="form-control" placeholder="Tối thiểu 6 ký tự..." minlength="6" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark">Xác Nhận Mật Khẩu Mới: <span class="text-danger">*</span></label>
                        <input type="password" name="confirmPassword" class="form-control" placeholder="Nhập lại mật khẩu mới..." minlength="6" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4">
                        <i class="bi bi-check2-circle me-1"></i> Xác Nhận Đổi Mật Khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 3: XEM CHI TIẾT & ĐỔI PHÂN QUYỀN                        -->
<!-- ============================================================== -->
<div class="modal fade" id="accountDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="index.php?action=admin-account-update-role" method="POST">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="bi bi-person-lines-fill me-2"></i>Chi Tiết Tài Khoản & Phân Quyền
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="maTk" id="detailFormMaTk">

                    <table class="table table-sm table-borderless mb-3">
                        <tr>
                            <td class="text-muted" style="width: 120px;">Mã Tài Khoản:</td>
                            <td><code class="fw-bold fs-6" id="detailMaTk"></code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Họ và Tên:</td>
                            <td><strong class="text-dark" id="detailHoTen"></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tên Đăng Nhập:</td>
                            <td><span class="font-monospace text-primary fw-semibold" id="detailUsername"></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td id="detailEmail"></td>
                        </tr>
                        <tr id="rowPhone" style="display: none;">
                            <td class="text-muted">Số Điện Thoại:</td>
                            <td><strong class="text-dark font-monospace" id="detailPhone"></strong></td>
                        </tr>
                        <tr id="rowAddress" style="display: none;">
                            <td class="text-muted">Địa Chỉ:</td>
                            <td id="detailAddress"></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Trạng Thái:</td>
                            <td id="detailStatus"></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ngày Đăng Ký:</td>
                            <td id="detailNgayTao"></td>
                        </tr>
                    </table>

                    <div class="border-top pt-3">
                        <label class="form-label small fw-bold text-dark">Thay Đổi Chức Vụ / Vai Trò:</label>
                        <select name="newRole" id="detailSelectRole" class="form-select">
                            <option value="admin">Quản Trị Viên (Admin)</option>
                            <option value="nv_ban_hang">Nhân Viên Bán Hàng</option>
                            <option value="nv_kho">Nhân Viên Kho</option>
                            <option value="thu_ngan">Thu Ngân</option>
                            <option value="ky_thuat_vien">Kỹ Thuật Viên</option>
                            <option value="ke_toan">Kế Toán</option>
                            <option value="user">Khách Hàng (User)</option>
                        </select>
                        <small class="text-muted mt-1 d-block">
                            * Thay đổi phân quyền sẽ áp dụng ngay trong phiên đăng nhập kế tiếp của tài khoản.
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-save me-1"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let resetPwdModalInstance = null;
let detailModalInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    resetPwdModalInstance = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    detailModalInstance   = new bootstrap.Modal(document.getElementById('accountDetailModal'));
});

// Mở modal Đặt lại mật khẩu
function openResetPwdModal(acc) {
    document.getElementById('resetFormMaTk').value = acc.maTk;
    document.getElementById('resetModalHoTen').textContent = acc.hoTen;
    document.getElementById('resetModalUsername').textContent = acc.tenTaiKhoan;
    document.getElementById('resetModalMaTk').textContent = acc.maTk;
    resetPwdModalInstance.show();
}

// Mở modal Chi tiết tài khoản
function openDetailModal(acc) {
    document.getElementById('detailFormMaTk').value = acc.maTk;
    document.getElementById('detailMaTk').textContent = acc.maTk;
    document.getElementById('detailHoTen').textContent = acc.hoTen;
    document.getElementById('detailUsername').textContent = acc.tenTaiKhoan;
    document.getElementById('detailEmail').textContent = acc.email;
    document.getElementById('detailNgayTao').textContent = acc.ngayTao;

    // Hiển thị SĐT và Địa chỉ nếu có
    const rowPhone = document.getElementById('rowPhone');
    const rowAddress = document.getElementById('rowAddress');

    if (acc.soDienThoai) {
        document.getElementById('detailPhone').textContent = acc.soDienThoai;
        rowPhone.style.display = 'table-row';
    } else {
        rowPhone.style.display = 'none';
    }

    if (acc.diaChi) {
        document.getElementById('detailAddress').textContent = acc.diaChi;
        rowAddress.style.display = 'table-row';
    } else {
        rowAddress.style.display = 'none';
    }

    // Trạng thái
    if (acc.trangThai === 'active') {
        document.getElementById('detailStatus').innerHTML = '<span class="badge bg-success">Đang hoạt động</span>';
    } else {
        document.getElementById('detailStatus').innerHTML = '<span class="badge bg-danger">Đã khóa</span>';
    }

    // Role
    document.getElementById('detailSelectRole').value = acc.role;

    detailModalInstance.show();
}
</script>

</body>
</html>
