<?php
/**
 * File: views/admin/promotions/create.php
 * Form Thêm Mới Chương Trình Khuyến Mãi / Voucher - EmShu Store
 */

$currentUser = AuthMiddleware::getCurrentUser();
$userRole    = $currentUser['role'] ?? '';
$flashError  = $_SESSION['flash_error'] ?? null;
$oldData     = $_SESSION['old_promotion_input'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['old_promotion_input']);

$today = date('Y-m-d\TH:i');
$nextMonth = date('Y-m-d\TH:i', strtotime('+30 days'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Khuyến Mãi Mới - EmShu Store</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
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
        }

        .form-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: 0 1px 4px rgba(0,0,0,0.02);
            padding: 28px;
        }

        .form-label {
            font-size: 0.88rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-group-text {
            background-color: #f8fafc;
            color: #64748b;
        }

        .type-radio-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .type-radio-card:hover {
            border-color: #93c5fd;
            background-color: #f8fafc;
        }

        .form-check-input:checked ~ .type-radio-card {
            border-color: var(--primary-color);
            background-color: #eff6ff;
        }
    </style>
</head>
<body>

<!-- SIDEBAR ĐỒNG BỘ -->
<?php 
$activeMenu = 'promotions';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<main class="main-content">

    <!-- Top Header Bar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <a href="index.php?action=admin-promotions" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h5 class="mb-0 fw-bold text-dark">Thêm Chương Trình Khuyến Mãi Mới</h5>
                <small class="text-muted">Tạo mới mã giảm giá (Voucher) áp dụng cho đơn hàng hoặc danh mục laptop</small>
            </div>
        </div>

        <a href="index.php?action=admin-promotions" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-x-lg me-1"></i> Hủy Bỏ
        </a>
    </header>

    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div><?= htmlspecialchars($flashError) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Form Nhập Liệu Thêm Mới -->
    <div class="form-card">
        <form action="index.php?action=admin-promotion-store" method="POST">
            
            <div class="row g-4">
                
                <!-- 1. Mã Khuyến Mãi & Tên Chương Trình -->
                <div class="col-md-5">
                    <label class="form-label">
                        Mã Khuyến Mãi (Voucher Code) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" name="maKM" id="maKMInput" class="form-control text-uppercase font-monospace fw-bold" 
                               placeholder="VD: EMSHU_500K" required 
                               value="<?= htmlspecialchars($oldData['maKM'] ?? '') ?>">
                        <button type="button" class="btn btn-outline-primary" onclick="generateRandomCode()" title="Tự sinh mã ngẫu nhiên">
                            <i class="bi bi-shuffle me-1"></i> Tự sinh
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">Chỉ gồm chữ in hoa, số và dấu gạch (_ -), không dấu.</small>
                </div>

                <div class="col-md-7">
                    <label class="form-label">
                        Tên Chương Trình Khuyến Mãi <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                        <input type="text" name="tenKM" class="form-control" 
                               placeholder="VD: Trợ giá mùa tựu trường cho tân sinh viên" required
                               value="<?= htmlspecialchars($oldData['tenKM'] ?? '') ?>">
                    </div>
                </div>

                <!-- 2. Chọn Loại Giảm Giá (Phần Trăm vs Tiền Mặt) -->
                <div class="col-12">
                    <label class="form-label d-block">Loại Khuyến Mãi & Phương Thức Giảm <span class="text-danger">*</span></label>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="d-block">
                                <input type="radio" name="loaiKM" value="PHAN_TRAM" class="form-check-input d-none" 
                                       id="radioPercent" onchange="toggleDiscountType()" 
                                       <?= (!isset($oldData['loaiKM']) || $oldData['loaiKM'] === 'PHAN_TRAM') ? 'checked' : '' ?>>
                                <div class="type-radio-card">
                                    <div class="rounded-circle p-3 bg-danger-subtle text-danger fs-4">
                                        <i class="bi bi-percent"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">Giảm Theo Phần Trăm (%)</h6>
                                        <small class="text-muted">Chiết khấu theo tỷ lệ % giá trị đơn hàng, có thể đặt mức trần giảm tối đa.</small>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="d-block">
                                <input type="radio" name="loaiKM" value="TIEN_MAT" class="form-check-input d-none" 
                                       id="radioCash" onchange="toggleDiscountType()" 
                                       <?= (isset($oldData['loaiKM']) && $oldData['loaiKM'] === 'TIEN_MAT') ? 'checked' : '' ?>>
                                <div class="type-radio-card">
                                    <div class="rounded-circle p-3 bg-success-subtle text-success fs-4">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">Giảm Theo Tiền Mặt Cố Định (VNĐ)</h6>
                                        <small class="text-muted">Trừ trực tiếp một khoản tiền cụ thể vào tổng tiền thanh toán của hóa đơn.</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 3. Mức Giảm, Mức Giảm Tối Đa & Đơn Tối Thiểu -->
                <div class="col-md-4">
                    <label class="form-label" id="labelGiaTriGiam">
                        Mức Giảm Giá (%) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" name="giaTriGiam" id="giaTriGiamInput" class="form-control font-monospace fw-bold" 
                               min="1" step="any" placeholder="VD: 10" required
                               value="<?= htmlspecialchars((string)($oldData['giaTriGiam'] ?? '')) ?>">
                        <span class="input-group-text fw-bold" id="unitGiaTriGiam">%</span>
                    </div>
                </div>

                <div class="col-md-4" id="boxMucGiamToiDa">
                    <label class="form-label">
                        Mức Giảm Tối Đa (VNĐ)
                    </label>
                    <div class="input-group">
                        <input type="number" name="mucGiamToiDa" id="mucGiamToiDaInput" class="form-control font-monospace" 
                               min="0" step="1000" placeholder="VD: 1500000 (Để trống nếu không giới hạn)"
                               value="<?= htmlspecialchars((string)($oldData['mucGiamToiDa'] ?? '')) ?>">
                        <span class="input-group-text">đ</span>
                    </div>
                    <small class="text-muted d-block mt-1">Trần tiền tối đa voucher có thể chiết khấu.</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Đơn Hàng Tối Thiểu (VNĐ) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" name="donHangToiThieu" class="form-control font-monospace" 
                               min="0" step="1000" placeholder="VD: 5000000 (0 nếu không yêu cầu)" required
                               value="<?= htmlspecialchars((string)($oldData['donHangToiThieu'] ?? '0')) ?>">
                        <span class="input-group-text">đ</span>
                    </div>
                    <small class="text-muted d-block mt-1">Giá trị đơn hàng phải đạt để được hưởng ưu đãi.</small>
                </div>

                <!-- 4. Thời Gian Áp Dụng (Bắt đầu -> Kết thúc) -->
                <div class="col-md-6">
                    <label class="form-label">
                        Ngày Bắt Đầu Áp Dụng <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="datetime-local" name="ngayBatDau" class="form-control" required
                               value="<?= htmlspecialchars($oldData['ngayBatDau'] ?? $today) ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Ngày Kết Thúc Áp Dụng <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                        <input type="datetime-local" name="ngayKetThuc" class="form-control" required
                               value="<?= htmlspecialchars($oldData['ngayKetThuc'] ?? $nextMonth) ?>">
                    </div>
                </div>

                <!-- 5. Trạng Thái Hoạt Động -->
                <div class="col-12">
                    <div class="form-check form-switch p-2 bg-light rounded-3 ps-5">
                        <input class="form-check-input ms-n4" type="checkbox" name="trangThai" id="trangThaiSwitch" value="1" checked>
                        <label class="form-check-label fw-bold text-dark" for="trangThaiSwitch">
                            Kích hoạt chương trình khuyến mãi ngay sau khi lưu
                        </label>
                        <small class="text-muted d-block">Khi kích hoạt, khách hàng và thu ngân có thể nhập mã này tại màn hình thanh toán.</small>
                    </div>
                </div>

                <!-- 6. Nút Lưu -->
                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="index.php?action=admin-promotions" class="btn btn-outline-secondary px-4">
                        Hủy Bỏ
                    </a>
                    <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Lưu Khuyến Mãi
                    </button>
                </div>

            </div>

        </form>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleDiscountType() {
    const isPercent = document.getElementById('radioPercent').checked;
    const boxMax = document.getElementById('boxMucGiamToiDa');
    const labelVal = document.getElementById('labelGiaTriGiam');
    const unitVal = document.getElementById('unitGiaTriGiam');
    const inputVal = document.getElementById('giaTriGiamInput');

    if (isPercent) {
        boxMax.style.display = 'block';
        labelVal.innerHTML = 'Mức Giảm Giá (%) <span class="text-danger">*</span>';
        unitVal.textContent = '%';
        inputVal.placeholder = 'VD: 10';
        inputVal.max = 100;
    } else {
        boxMax.style.display = 'none';
        labelVal.innerHTML = 'Mức Giảm Tiền Mặt (VNĐ) <span class="text-danger">*</span>';
        unitVal.textContent = 'đ';
        inputVal.placeholder = 'VD: 500000';
        inputVal.removeAttribute('max');
    }
}

function generateRandomCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = 'EMSHU_';
    for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('maKMInput').value = code;
}

document.addEventListener('DOMContentLoaded', toggleDiscountType);
</script>
</body>
</html>
