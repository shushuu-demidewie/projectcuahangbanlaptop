<?php
/**
 * File: views/client/tracking/warranty.php
 * Giao diện Khách hàng: Cổng Tra Cứu Bảo Hành Điện Tử Chính Hãng
 * Chuẩn Bootstrap 5, Thẻ kết quả Warranty Card phong cách số hóa cao cấp
 */

$pageTitle = 'Tra Cứu Bảo Hành Điện Tử - EmShu Store';
require_once __DIR__ . '/../layouts/header.php';

$kwInput = htmlspecialchars($_GET['keyword'] ?? $_POST['keyword'] ?? $_GET['serial'] ?? '');
?>

<style>
    /* Styling Thẻ Bảo Hành Điện Tử VIP */
    .warranty-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.08);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        overflow: hidden;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .warranty-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.12);
    }

    .warranty-card-top-active {
        background: linear-gradient(135deg, #065f46 0%, #047857 100%);
        color: #ffffff;
        padding: 24px;
        position: relative;
    }

    .warranty-card-top-expired {
        background: linear-gradient(135deg, #475569 0%, #334155 100%);
        color: #ffffff;
        padding: 24px;
        position: relative;
    }

    .warranty-watermark {
        position: absolute;
        right: 15px;
        bottom: -15px;
        font-size: 6rem;
        color: rgba(255, 255, 255, 0.08);
        pointer-events: none;
        line-height: 1;
    }

    .serial-box {
        background: rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(4px);
        border: 1px dashed rgba(255, 255, 255, 0.35);
        border-radius: 10px;
        padding: 8px 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: 'JetBrains Mono', monospace;
        letter-spacing: 1px;
    }

    .days-badge-active {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        border-radius: 30px;
        padding: 6px 14px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .days-badge-expired {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
        border-radius: 30px;
        padding: 6px 14px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .policy-box {
        background: #f8fafc;
        border-left: 4px solid #2563eb;
        border-radius: 0 10px 10px 0;
        padding: 14px 18px;
    }

    .barcode-mockup {
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: 5px;
        font-size: 1.1rem;
        color: #64748b;
        display: inline-block;
        padding: 4px 8px;
        border-top: 2px solid #cbd5e1;
        border-bottom: 2px solid #cbd5e1;
    }

    @media print {
        header, footer, nav, .dock-btn, .breadcrumb, form, .badge.bg-success-subtle, h1.display-6, p.text-muted, .btn {
            display: none !important;
        }
        .container {
            max-width: 100% !important;
            padding: 0 !important;
        }
        .warranty-card {
            box-shadow: none !important;
            border: 1px solid #ccc !important;
            page-break-inside: avoid;
        }
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?url=home" class="text-decoration-none">Trang Chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Kiểm Tra Bảo Hành</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="text-center mb-5">
        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold text-uppercase mb-2">
            <i class="bi bi-patch-check-fill me-1"></i> Trung Tâm Kích Hoạt & Tra Cứu Bảo Hành Điện Tử
        </span>
        <h1 class="fw-extrabold display-6 text-dark mb-3">Tra Cứu Bảo Hành Laptop Chính Hãng</h1>
        <p class="text-muted mx-auto" style="max-width: 650px;">
            Nhập <strong>Mã Serial / IMEI</strong> (in dưới đáy thân máy laptop) hoặc <strong>Số điện thoại</strong> mua hàng để kiểm tra thời hạn bảo hành, chính sách 1 đổi 1 và ngày hết hạn.
        </p>
    </div>

    <!-- Search Form Card (To, Nổi Bật) -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 col-md-10">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="index.php" method="GET" class="d-flex flex-column gap-3">
                        <input type="hidden" name="action" value="check-warranty">

                        <div>
                            <label class="form-label fw-bold text-dark fs-6 mb-2">
                                <i class="bi bi-upc-scan text-primary me-2"></i> Nhập Mã Serial/IMEI hoặc Số Điện Thoại Mua Hàng:
                            </label>
                            <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden border">
                                <span class="input-group-text bg-light border-0 text-muted px-3">
                                    <i class="bi bi-search fs-5"></i>
                                </span>
                                <input type="text" name="keyword" class="form-control border-0 font-monospace" 
                                       placeholder="Ví dụ: SN-MSI-93902, ASUS-G15-001 hoặc 0988888999..." 
                                       value="<?= $kwInput ?>" required autofocus>
                                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                    <i class="bi bi-shield-check me-1"></i> Tra Cứu
                                </button>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center text-muted small mt-2">
                            <div>
                                <i class="bi bi-info-circle-fill text-primary me-1"></i> 
                                Tem mã Serial thường được dán ở nắp đáy laptop hoặc in trên vỏ hộp.
                            </div>
                            <a href="index.php?action=track-order" class="text-decoration-none fw-semibold text-primary">
                                <i class="bi bi-truck me-1"></i> Theo dõi tiến độ đơn hàng &rarr;
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Alert Section -->
    <?php if (!empty($errorMessage)): ?>
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 col-md-10">
                <div class="alert alert-warning shadow-sm rounded-4 p-4 d-flex align-items-center gap-3 border-warning">
                    <i class="bi bi-shield-exclamation fs-1 text-warning flex-shrink-0"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Không Tìm Thấy Thông Tin Bảo Hành</h6>
                        <div class="text-secondary small"><?= htmlspecialchars($errorMessage) ?></div>
                        <div class="mt-2 text-muted small">
                            Vui lòng kiểm tra lại từng ký tự trên tem đáy máy hoặc liên hệ hotline <strong>1900 6868</strong> để được kích hoạt bảo hành bổ sung.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- RESULTS SECTION: THẺ BẢO HÀNH ĐIỆN TỬ -->
    <?php if ($hasSearched && !empty($warranties)): ?>
        <div class="mb-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-patch-check-fill text-success me-2"></i> Kết Quả Tra Cứu Bảo Hành (<?= count($warranties) ?> thiết bị)
                </h4>
                <div class="text-muted small">
                    Từ khóa tìm kiếm: <strong class="text-primary font-monospace"><?= $kwInput ?></strong>
                </div>
            </div>

            <div class="row g-4 <?= count($warranties) === 1 ? 'justify-content-center' : '' ?>">
                <?php foreach ($warranties as $w): ?>
                    <?php 
                        $isConHan = (bool)($w['isConHan'] ?? false);
                        $daysLeft = (int)($w['soNgayConLai'] ?? 0);
                        $headerClass = $isConHan ? 'warranty-card-top-active' : 'warranty-card-top-expired';
                        $badgeClass  = $w['badgeClass'] ?? ($isConHan ? 'bg-success' : 'bg-secondary');
                        $statusText  = $w['statusText'] ?? ($isConHan ? 'ĐANG HIỆU LỰC' : 'HẾT HẠN BẢO HÀNH');
                        $itemImg     = !empty($w['hinhAnh']) ? htmlspecialchars($w['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($w['maSP'] ?? '') . '.jpg');
                    ?>
                    <div class="<?= count($warranties) === 1 ? 'col-lg-8 col-xl-7' : 'col-lg-6' ?>">
                        <div class="warranty-card h-100 d-flex flex-column">
                            <!-- Phần đầu thẻ Bảo Hành Điện Tử -->
                            <div class="<?= $headerClass ?>">
                                <i class="bi bi-shield-lock-fill warranty-watermark"></i>
                                
                                <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-white rounded-3 p-1 shadow-sm flex-shrink-0 d-none d-sm-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                            <img src="<?= $itemImg ?>" alt="<?= htmlspecialchars($w['tenSP']) ?>" class="rounded" style="max-width: 100%; max-height: 100%; object-fit: contain;" onerror="this.src='assets/images/products/SP004.jpg';">
                                        </div>
                                        <div>
                                            <span class="badge bg-white text-dark fw-bold text-uppercase px-3 py-1 rounded-pill small mb-1">
                                                <i class="bi bi-award-fill text-warning me-1"></i> Thẻ Bảo Hành Điện Tử
                                            </span>
                                            <h4 class="fw-extrabold text-white mb-0 fs-5"><?= htmlspecialchars($w['tenSP']) ?></h4>
                                            <div class="d-flex flex-wrap gap-2 text-white-50 small mt-1">
                                                <span>Mã phiếu: <strong class="text-white"><?= htmlspecialchars($w['maPhieuBH']) ?></strong></span>
                                                <?php if (!empty($w['tenTH'])): ?>
                                                    <span>• Hãng: <strong class="text-white"><?= htmlspecialchars($w['tenTH']) ?></strong></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Huy hiệu trạng thái ĐANG HIỆU LỰC (Xanh lá) hoặc HẾT HẠN BẢO HÀNH (Xám) -->
                                    <span class="badge <?= $badgeClass ?> px-3 py-2 rounded-pill fs-7 fw-bold shadow-sm flex-shrink-0 text-white">
                                        <i class="bi <?= $isConHan ? 'bi-check-circle-fill' : 'bi-clock-history' ?> me-1"></i>
                                        <?= $statusText ?>
                                    </span>
                                </div>

                                <!-- Box mã Serial/IMEI cụ thể -->
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="serial-box text-white">
                                        <i class="bi bi-upc-scan"></i>
                                        <span>MÃ SERIAL: <strong><?= htmlspecialchars($w['maSerial_IMEI']) ?></strong></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 shadow-sm" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($w['maSerial_IMEI']) ?>'); alert('Đã sao chép mã Serial: <?= htmlspecialchars($w['maSerial_IMEI']) ?>');" title="Sao chép Serial">
                                        <i class="bi bi-clipboard me-1"></i> Sao chép
                                    </button>
                                </div>
                            </div>

                            <!-- Thân thẻ thông tin chi tiết -->
                            <div class="p-4 bg-white flex-grow-1 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Thời hạn & Số ngày còn lại -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-6">
                                            <div class="p-3 rounded-3 bg-light border">
                                                <div class="text-muted small mb-1"><i class="bi bi-calendar-check text-primary me-1"></i> Ngày kích hoạt:</div>
                                                <div class="fw-bold text-dark font-monospace fs-6">
                                                    <?= !empty($w['ngayBatDau']) ? date('d/m/Y', strtotime($w['ngayBatDau'])) : 'N/A' ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 rounded-3 bg-light border">
                                                <div class="text-muted small mb-1"><i class="bi bi-calendar-x text-danger me-1"></i> Ngày hết hạn:</div>
                                                <div class="fw-bold text-dark font-monospace fs-6">
                                                    <?= !empty($w['ngayKetThuc']) ? date('d/m/Y', strtotime($w['ngayKetThuc'])) : 'N/A' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Số ngày bảo hành còn lại -->
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-4 <?= $isConHan ? 'days-badge-active' : 'days-badge-expired' ?>">
                                        <span class="d-flex align-items-center gap-2">
                                            <i class="bi <?= $isConHan ? 'bi-hourglass-split' : 'bi-alarm-fill' ?> fs-5"></i>
                                            <span class="fw-semibold">Thời gian bảo hành còn lại:</span>
                                        </span>
                                        <span class="fs-6 font-monospace">
                                            <?php if ($daysLeft > 0): ?>
                                                <strong><?= $daysLeft ?></strong> ngày nữa
                                            <?php else: ?>
                                                <strong>Đã hết hạn</strong>
                                            <?php endif; ?>
                                        </span>
                                    </div>

                                    <!-- Thông tin khách hàng sở hữu -->
                                    <div class="small text-secondary mb-4 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Chủ sở hữu máy:</span>
                                            <strong class="text-dark"><?= htmlspecialchars($w['hoVaTen'] ?? 'Khách Mua Hàng') ?></strong>
                                        </div>
                                        <?php if (!empty($w['soDienThoai'])): ?>
                                            <?php 
                                                // Ẩn 3 số giữa của SĐT để bảo mật
                                                $phone = $w['soDienThoai'];
                                                $maskedPhone = strlen($phone) >= 7 ? substr($phone, 0, 3) . '****' . substr($phone, -3) : $phone;
                                            ?>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Số điện thoại đăng ký:</span>
                                                <strong class="text-dark font-monospace"><?= htmlspecialchars($maskedPhone) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($w['email'])): ?>
                                            <div class="d-flex justify-content-between">
                                                <span>Email:</span>
                                                <strong class="text-dark"><?= htmlspecialchars($w['email']) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Ghi chú kỹ thuật nếu có -->
                                    <?php if (!empty($w['ghiChuBaoHanh'])): ?>
                                        <div class="alert alert-info py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-info-circle-fill text-primary"></i>
                                            <div><strong>Ghi chú kỹ thuật:</strong> <?= htmlspecialchars($w['ghiChuBaoHanh']) ?></div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Chính sách bảo hành chuẩn -->
                                    <div class="policy-box mb-3">
                                        <div class="fw-bold text-dark small mb-1">
                                            <i class="bi bi-shield-shaded text-primary me-1"></i> Chính sách bảo hành áp dụng:
                                        </div>
                                        <div class="small text-secondary">
                                            <?= htmlspecialchars($w['moTaChinhSach']) ?>
                                        </div>
                                        <div class="small text-success fw-semibold mt-2">
                                            <i class="bi bi-check2-circle me-1"></i> Cam kết đổi mới 30 ngày đầu nếu phát sinh lỗi phần cứng từ NSX.
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer thẻ: Barcode mô phỏng & Tiện ích -->
                                <div class="pt-3 border-top">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center text-muted small mb-3">
                                        <div class="barcode-mockup">
                                            ||| <?= htmlspecialchars($w['maSerial_IMEI']) ?> |||
                                        </div>
                                        <div>
                                            Hỗ trợ kỹ thuật: <strong class="text-primary fs-6">1900 6868</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <?php if (!empty($w['maSP'])): ?>
                                            <a href="index.php?action=client-product-detail&id=<?= urlencode($w['maSP']) ?>" class="btn btn-sm btn-outline-primary fw-semibold rounded-pill px-3">
                                                <i class="bi bi-laptop me-1"></i> Xem Chi Tiết Máy
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold rounded-pill px-3" onclick="window.print();">
                                            <i class="bi bi-printer me-1"></i> In Thẻ Bảo Hành
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Banner Chính Sách Hậu Mãi -->
    <div class="card border-0 bg-light rounded-4 p-4 mt-5">
        <div class="row g-4 align-items-center">
            <div class="col-md-3 text-center">
                <i class="bi bi-tools display-3 text-primary mb-2"></i>
                <h6 class="fw-bold text-dark">Bảo Hành Chính Hãng</h6>
                <div class="small text-muted">Linh kiện thay thế chính hãng 100%</div>
            </div>
            <div class="col-md-3 text-center border-start-md">
                <i class="bi bi-arrow-repeat display-3 text-success mb-2"></i>
                <h6 class="fw-bold text-dark">Đổi Mới 30 Ngày</h6>
                <div class="small text-muted">1 đổi 1 nhanh chóng nếu lỗi NSX</div>
            </div>
            <div class="col-md-3 text-center border-start-md">
                <i class="bi bi-cpu display-3 text-warning mb-2"></i>
                <h6 class="fw-bold text-dark">Vệ Sinh Trọn Đời</h6>
                <div class="small text-muted">Miễn phí tra keo tản nhiệt định kỳ</div>
            </div>
            <div class="col-md-3 text-center border-start-md">
                <i class="bi bi-headset display-3 text-info mb-2"></i>
                <h6 class="fw-bold text-dark">Hỗ Trợ 24/7</h6>
                <div class="small text-muted">Hotline CSKH: 1900 6868</div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
