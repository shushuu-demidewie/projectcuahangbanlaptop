<?php
/**
 * File: views/client/tracking/order.php
 * Giao diện Khách hàng: Tra Cứu Tiến Độ Đơn Hàng & Hành Trình Vận Chuyển
 * Chuẩn Bootstrap 5, Responsive, Timeline 4 bước trực quan
 */

$pageTitle = 'Tra Cứu Tiến Độ Đơn Hàng - EmShu Store';
require_once __DIR__ . '/../layouts/header.php';

$maDHInput = htmlspecialchars($_GET['maDH'] ?? $_POST['maDH'] ?? '');
$sdtInput  = htmlspecialchars($_GET['sdt'] ?? $_POST['sdt'] ?? '');
?>

<style>
    /* Styling cho thẻ tra cứu đơn hàng */
    .tracking-card-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        border-radius: 16px 16px 0 0;
    }

    /* 4-Step Visual Order Progress Timeline */
    .timeline-container {
        padding: 20px 10px 10px;
        position: relative;
    }

    .timeline-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin: 40px 0 25px 0;
    }

    .timeline-steps::before {
        content: "";
        position: absolute;
        top: 26px;
        left: 8%;
        right: 8%;
        height: 6px;
        background-color: #e2e8f0;
        z-index: 1;
        border-radius: 3px;
    }

    .timeline-progress-bar {
        position: absolute;
        top: 26px;
        left: 8%;
        height: 6px;
        background: linear-gradient(90deg, #2563eb, #10b981);
        z-index: 2;
        border-radius: 3px;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .timeline-step-node {
        position: relative;
        z-index: 3;
        text-align: center;
        width: 130px;
    }

    .timeline-icon-wrap {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background-color: #ffffff;
        border: 4px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px auto;
        font-size: 1.35rem;
        color: #94a3b8;
        transition: all 0.3s ease;
    }

    .timeline-step-node.completed .timeline-icon-wrap {
        border-color: #10b981;
        background-color: #10b981;
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    }

    .timeline-step-node.active .timeline-icon-wrap {
        border-color: #2563eb;
        background-color: #2563eb;
        color: #ffffff;
        box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.25);
        animation: pulseTimeline 2s infinite;
    }

    @keyframes pulseTimeline {
        0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.6); }
        70% { box-shadow: 0 0 0 12px rgba(37, 99, 235, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }

    .timeline-label {
        font-size: 0.88rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 2px;
    }

    .timeline-step-node.active .timeline-label {
        color: #2563eb;
    }

    .timeline-step-node.completed .timeline-label {
        color: #0f172a;
    }

    .timeline-subtext {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .serial-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        padding: 4px 10px;
        border-radius: 6px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
        font-weight: 700;
        color: #1e293b;
        transition: all 0.2s;
    }

    .serial-chip:hover {
        background: #e2e8f0;
        color: #2563eb;
        border-color: #3b82f6;
    }

    @media (max-width: 768px) {
        .timeline-steps {
            flex-direction: column;
            gap: 24px;
            margin: 20px 0;
        }
        .timeline-steps::before, .timeline-progress-bar {
            display: none;
        }
        .timeline-step-node {
            display: flex;
            align-items: center;
            width: 100%;
            text-align: left;
            gap: 16px;
        }
        .timeline-icon-wrap {
            margin: 0;
            flex-shrink: 0;
        }
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?url=home" class="text-decoration-none">Trang Chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tra Cứu Đơn Hàng</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="text-center mb-5">
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold text-uppercase mb-2">
            <i class="bi bi-box-seam me-1"></i> Trung Tâm Theo Dõi Đơn Hàng Trực Tuyến
        </span>
        <h1 class="fw-extrabold display-6 text-dark mb-3">Kiểm Tra Tiến Độ Giao Hàng</h1>
        <p class="text-muted mx-auto" style="max-width: 650px;">
            Nhập <strong>Mã đơn hàng</strong> (ví dụ: <span class="badge bg-light text-dark border">DH001</span>, <span class="badge bg-light text-dark border">DH002</span>) kết hợp với <strong>Số điện thoại</strong> đặt hàng để theo dõi chi tiết hành trình vận chuyển và số Serial máy tính xuất kho.
        </p>
    </div>

    <!-- Search Form Card -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 col-md-10">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="index.php" method="GET" class="row g-3">
                        <input type="hidden" name="action" value="track-order">

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">
                                <i class="bi bi-receipt text-primary me-1"></i> Mã Đơn Hàng (*):
                            </label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border">
                                <span class="input-group-text bg-light border-0 text-muted px-3">
                                    <i class="bi bi-hash"></i>
                                </span>
                                <input type="text" name="maDH" class="form-control border-0 font-monospace text-uppercase" 
                                       placeholder="Ví dụ: DH002..." 
                                       value="<?= $maDHInput ?>" required autofocus>
                            </div>
                            <div class="form-text small text-muted">Mã đơn nhận được sau khi đặt hàng thành công.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">
                                <i class="bi bi-telephone text-primary me-1"></i> Số Điện Thoại Đặt Hàng (*):
                            </label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border">
                                <span class="input-group-text bg-light border-0 text-muted px-3">
                                    <i class="bi bi-phone"></i>
                                </span>
                                <input type="text" name="sdt" class="form-control border-0 font-monospace" 
                                       placeholder="Ví dụ: 0988888999..." 
                                       value="<?= $sdtInput ?>" required>
                            </div>
                            <div class="form-text small text-muted">Dùng để xác thực bảo mật thông tin đơn hàng.</div>
                        </div>

                        <div class="col-12 mt-4 text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold rounded-pill shadow">
                                <i class="bi bi-search me-2"></i> Tra Cứu Tiến Độ Đơn
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 pt-3 border-top d-flex flex-wrap justify-content-between align-items-center text-muted small">
                        <span><i class="bi bi-shield-lock-fill text-success me-1"></i> Thông tin được mã hóa bảo mật tuyệt đối</span>
                        <a href="index.php?action=check-warranty" class="text-decoration-none fw-semibold text-primary">
                            <i class="bi bi-shield-check me-1"></i> Bạn muốn kiểm tra bảo hành máy tính? &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Alert Section -->
    <?php if (!empty($errorMessage)): ?>
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 col-md-10">
                <div class="alert alert-danger shadow-sm rounded-4 p-4 d-flex align-items-center gap-3 border-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-2 text-danger flex-shrink-0"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-danger">Tra Cứu Thất Bại</h6>
                        <div class="text-secondary"><?= htmlspecialchars($errorMessage) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Result Details Section -->
    <?php if ($hasSearched && !empty($order)): ?>
        <?php 
            $stepper = $order['stepper'] ?? [
                'currentStep' => 1,
                'isCancelled' => false,
                'statusLabel' => 'Đang xử lý',
                'badgeClass'  => 'bg-primary',
                'stepPercent' => 25
            ];
            $currentStep = (int)$stepper['currentStep'];
            $isCancelled = (bool)$stepper['isCancelled'];
        ?>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5">
            <!-- Header Thông Tin Tổng Quan -->
            <div class="tracking-card-header p-4 p-md-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="text-uppercase small text-white-50 fw-semibold tracking-wider">Thông Tin Đơn Hàng</div>
                        <h2 class="fw-extrabold font-monospace text-warning mb-1">
                            #<?= htmlspecialchars($order['maDH']) ?>
                        </h2>
                        <div class="text-white-50 small">
                            Thời gian đặt hàng: <strong class="text-white"><?= !empty($order['ngayTao']) ? date('d/m/Y H:i:s', strtotime($order['ngayTao'])) : 'N/A' ?></strong>
                            <span class="mx-2">•</span> Kênh đặt: <span class="badge bg-light text-dark fw-bold"><?= htmlspecialchars($order['kenhBan'] ?? 'ONLINE') ?></span>
                        </div>
                    </div>

                    <div class="text-md-end">
                        <div class="small text-white-50 mb-1">Trạng thái hiện tại:</div>
                        <span class="badge <?= $stepper['badgeClass'] ?> px-4 py-2 rounded-pill fs-6 fw-bold shadow-sm">
                            <?= htmlspecialchars($stepper['statusLabel']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-4 p-md-5 bg-white">
                <!-- KHUNG TIẾN TRÌNH 4 BƯỚC TIMELINE -->
                <div class="mb-5">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-clock-history text-primary me-2"></i> Tiến Trình Xử Lý & Vận Chuyển
                    </h5>

                    <?php if ($isCancelled): ?>
                        <div class="alert alert-danger rounded-4 p-4 text-center my-4">
                            <i class="bi bi-x-circle-fill display-4 text-danger d-block mb-2"></i>
                            <h4 class="fw-bold text-danger">ĐƠN HÀNG ĐÃ ĐƯỢC HỦY</h4>
                            <p class="text-secondary mb-0">
                                Đơn hàng này đã kết thúc xử lý. Mọi mã Serial (nếu đã cấp) đã được hoàn trả về kho hàng.
                                Vui lòng liên hệ hotline <strong>1900 6868</strong> để được hỗ trợ lại nếu cần thiết.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="timeline-container">
                            <div class="timeline-steps">
                                <!-- Progress line width calculation -->
                                <?php 
                                    $pWidth = '0%';
                                    if ($currentStep === 1) $pWidth = '12%';
                                    elseif ($currentStep === 2) $pWidth = '42%';
                                    elseif ($currentStep === 3) $pWidth = '74%';
                                    elseif ($currentStep >= 4) $pWidth = '100%';
                                ?>
                                <div class="timeline-progress-bar" style="width: <?= $pWidth ?>;"></div>

                                <!-- Bước 1: Đã đặt hàng thành công -->
                                <div class="timeline-step-node <?= ($currentStep > 1) ? 'completed' : (($currentStep === 1) ? 'active' : '') ?>">
                                    <div class="timeline-icon-wrap">
                                        <i class="bi bi-check2-circle"></i>
                                    </div>
                                    <div>
                                        <div class="timeline-label">1. Đã đặt hàng</div>
                                        <div class="timeline-subtext">Đã ghi nhận đơn</div>
                                    </div>
                                </div>

                                <!-- Bước 2: Đã duyệt đơn & Xuất kho -->
                                <div class="timeline-step-node <?= ($currentStep > 2) ? 'completed' : (($currentStep === 2) ? 'active' : '') ?>">
                                    <div class="timeline-icon-wrap">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <div>
                                        <div class="timeline-label">2. Duyệt & Xuất kho</div>
                                        <div class="timeline-subtext"><?= $order['hasSerials'] ? 'Đã gán Serial máy' : 'Chuẩn bị thiết bị' ?></div>
                                    </div>
                                </div>

                                <!-- Bước 3: Đang giao hàng -->
                                <div class="timeline-step-node <?= ($currentStep > 3) ? 'completed' : (($currentStep === 3) ? 'active' : '') ?>">
                                    <div class="timeline-icon-wrap">
                                        <i class="bi bi-truck"></i>
                                    </div>
                                    <div>
                                        <div class="timeline-label">3. Đang giao hàng</div>
                                        <div class="timeline-subtext">Đang trên đường giao</div>
                                    </div>
                                </div>

                                <!-- Bước 4: Giao hàng thành công -->
                                <div class="timeline-step-node <?= ($currentStep >= 4) ? 'completed' : '' ?>">
                                    <div class="timeline-icon-wrap">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <div>
                                        <div class="timeline-label">4. Giao thành công</div>
                                        <div class="timeline-subtext">Kích hoạt BH điện tử</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- KHUNG THÔNG TIN NGƯỜI NHẬN & PHƯƠNG THỨC THANH TOÁN -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 bg-light border h-100 shadow-sm">
                            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Thông Tin Nhận Hàng
                            </h6>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                                <li>
                                    <span class="text-muted">Người nhận:</span> 
                                    <strong class="text-dark fs-6"><?= htmlspecialchars($order['tenNguoiNhan'] ?: 'Khách hàng') ?></strong>
                                </li>
                                <li>
                                    <span class="text-muted">Số điện thoại:</span> 
                                    <strong class="text-dark font-monospace"><?= htmlspecialchars($order['sdtNhanHang'] ?: 'N/A') ?></strong>
                                </li>
                                <li>
                                    <span class="text-muted">Địa chỉ nhận hàng:</span> 
                                    <strong class="text-dark"><?= htmlspecialchars($order['diaChiGiaoHang'] ?: 'Nhận tại showroom EmShu Store') ?></strong>
                                </li>
                                <?php if (!empty($order['ghiChuKhachHang'])): ?>
                                    <li class="p-2 rounded bg-white border mt-1">
                                        <span class="text-muted">Ghi chú của bạn:</span> 
                                        <span class="fst-italic text-secondary"><?= htmlspecialchars($order['ghiChuKhachHang']) ?></span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-4 rounded-4 bg-light border h-100 shadow-sm">
                            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                                <i class="bi bi-credit-card-2-front text-primary me-1"></i> Thanh Toán & Vận Chuyển
                            </h6>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">Phương thức thanh toán:</span>
                                    <strong class="text-dark"><?= htmlspecialchars($order['phuongThucThanhToan'] ?? 'COD') ?></strong>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">Hình thức giao hàng:</span>
                                    <strong class="text-dark"><?= htmlspecialchars($order['phuongThucGiaoHang'] ?? 'Giao hàng tiêu chuẩn') ?></strong>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">Tổng tiền hàng:</span>
                                    <strong class="text-dark font-monospace"><?= number_format((float)$order['tongTienHang'], 0, ',', '.') ?> đ</strong>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">Phí vận chuyển:</span>
                                    <span class="text-dark font-monospace"><?= number_format((float)($order['phiVanChuyen'] ?? 0), 0, ',', '.') ?> đ</span>
                                </li>
                                <?php if (!empty($order['tienGiamGia']) && (float)$order['tienGiamGia'] > 0): ?>
                                    <li class="d-flex justify-content-between text-success">
                                        <span>Giảm giá Voucher:</span>
                                        <strong class="font-monospace">-<?= number_format((float)$order['tienGiamGia'], 0, ',', '.') ?> đ</strong>
                                    </li>
                                <?php endif; ?>
                                <li class="d-flex justify-content-between pt-2 border-top mt-1 align-items-center">
                                    <span class="fw-bold text-dark">Tổng thanh toán:</span>
                                    <span class="text-danger fw-extrabold fs-4 font-monospace">
                                        <?= number_format((float)$order['tongThanhToan'], 0, ',', '.') ?> đ
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- BẢNG TÓM TẮT SẢN PHẨM TRONG ĐƠN HÀNG -->
                <div class="mb-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-laptop text-primary me-2"></i> Danh Sách Sản Phẩm Đã Mua (<?= count($order['items'] ?? []) ?>)
                    </h5>

                    <div class="table-responsive rounded-4 border overflow-hidden">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-secondary text-uppercase">
                                    <th style="width: 70px;">Ảnh</th>
                                    <th>Tên Laptop & Bảo Hành</th>
                                    <th class="text-center" style="width: 100px;">Số Lượng</th>
                                    <th class="text-end" style="width: 140px;">Đơn Giá</th>
                                    <th class="text-end" style="width: 160px;">Thành Tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] ?? [] as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['hinhAnh'])): ?>
                                                <img src="<?= htmlspecialchars($item['hinhAnh']) ?>" alt="<?= htmlspecialchars($item['tenSP']) ?>" 
                                                     class="rounded-3 border p-1" style="width: 58px; height: 58px; object-fit: contain; background: #fff;">
                                            <?php else: ?>
                                                <div class="rounded-3 border p-2 bg-light text-center text-muted" style="width: 58px; height: 58px;">
                                                    <i class="bi bi-laptop fs-4"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="index.php?action=product-detail&id=<?= urlencode($item['maSP']) ?>" class="fw-bold text-dark text-decoration-none hover-primary">
                                                <?= htmlspecialchars($item['tenSP']) ?>
                                            </a>
                                            <div class="text-muted small mt-1">
                                                <span class="badge bg-light text-secondary border">Mã: <?= htmlspecialchars($item['maSP']) ?></span>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">
                                                    <i class="bi bi-shield-check me-1"></i>BH: <?= (int)($item['thoiGianBaoHanh'] ?? 12) ?> tháng
                                                </span>
                                            </div>

                                            <!-- DANH SÁCH SERIAL / IMEI NẾU ĐÃ GÁN -->
                                            <?php if (!empty($item['serials'])): ?>
                                                <div class="mt-2">
                                                    <span class="small fw-bold text-success d-block mb-1">
                                                        <i class="bi bi-upc-scan me-1"></i> Mã Serial/IMEI máy đã xuất kho:
                                                    </span>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach ($item['serials'] as $sn): ?>
                                                            <a href="index.php?action=check-warranty&keyword=<?= urlencode($sn['maSerial_IMEI']) ?>" 
                                                               class="serial-chip text-decoration-none" 
                                                               title="Bấm vào để tra cứu Thẻ bảo hành điện tử của chiếc laptop này">
                                                                <i class="bi bi-qr-code text-primary"></i>
                                                                <span><?= htmlspecialchars($sn['maSerial_IMEI']) ?></span>
                                                                <i class="bi bi-arrow-up-right small text-muted"></i>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="small text-muted fst-italic mt-2">
                                                    <i class="bi bi-hourglass me-1 text-warning"></i> Chờ bộ phận kỹ thuật kiểm tra và gán mã Serial trước khi xuất kho.
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center fw-bold fs-6"><?= (int)$item['soLuong'] ?></td>
                                        <td class="text-end font-monospace text-secondary"><?= number_format((float)$item['donGia'], 0, ',', '.') ?> đ</td>
                                        <td class="text-end font-monospace fw-bold text-danger fs-6"><?= number_format((float)$item['thanhTien'], 0, ',', '.') ?> đ</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="d-flex flex-wrap justify-content-center gap-3 pt-3">
                    <a href="index.php?action=check-warranty" class="btn btn-outline-success fw-bold px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> Cổng Tra Cứu Bảo Hành Điện Tử
                    </a>
                    <a href="index.php?url=home" class="btn btn-primary fw-bold px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-bag-plus me-1"></i> Tiếp Tục Mua Sắm
                    </a>
                </div>

            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
