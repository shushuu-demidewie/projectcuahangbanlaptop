<?php
/**
 * File: views/client/warranty_check.php
 * Trang Tra Cứu Thời Hạn & Tiến Độ Bảo Hành Công Khai Dành Cho Khách Hàng
 */

$pageTitle = 'Tra Cứu Bảo Hành Theo Serial/IMEI - EmShu Store';
require_once __DIR__ . '/layouts/header.php';
?>

<div class="container py-5">
    <!-- KHUNG TÌM KIẾM TRỌNG TÂM -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
            <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 rounded-pill fw-bold mb-3">
                <i class="bi bi-shield-check me-1"></i> TRUNG TÂM BẢO HÀNH CHÍNH HÃNG
            </span>
            <h2 class="fw-extrabold text-dark mb-2">Tra Cứu Thời Hạn & Tiến Độ Bảo Hành</h2>
            <p class="text-secondary mb-4">
                Nhập số <strong>Serial/IMEI</strong> in dưới đáy máy hoặc <strong>Số điện thoại</strong> đã đăng ký khi mua hàng để kiểm tra thời hạn và lịch sử sửa chữa.
            </p>

            <!-- Form Tra Cứu -->
            <form action="index.php" method="GET" class="card border-0 shadow-lg rounded-4 p-2 p-md-3 bg-white">
                <input type="hidden" name="action" value="check-warranty">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-transparent border-0 text-primary ps-3">
                        <i class="bi bi-upc-scan fs-4"></i>
                    </span>
                    <input type="text" 
                           name="keyword" 
                           class="form-control border-0 shadow-none fs-6" 
                           placeholder="Nhập Serial máy (VD: SN_ASUS_F15_0001) hoặc Số điện thoại..." 
                           value="<?= htmlspecialchars($keyword ?? '') ?>" 
                           required>
                    <button type="submit" class="btn btn-primary px-4 fw-bold rounded-3 shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-search"></i>
                        <span class="d-none d-sm-inline">Tra Cứu</span>
                    </button>
                </div>
            </form>

            <!-- Gợi ý tra cứu mẫu -->
            <div class="mt-3 text-muted small">
                Gợi ý tra cứu mẫu: 
                <a href="index.php?action=check-warranty&keyword=SN_ASUS_F15_0001" class="text-decoration-none badge bg-light text-secondary border">SN_ASUS_F15_0001</a>
                <a href="index.php?action=check-warranty&keyword=SN_DELL_3520_0001" class="text-decoration-none badge bg-light text-secondary border">SN_DELL_3520_0001</a>
                <a href="index.php?action=check-warranty&keyword=0912345678" class="text-decoration-none badge bg-light text-secondary border">SĐT: 0912345678</a>
            </div>
        </div>
    </div>

    <!-- KHU VỰC KẾT QUẢ TRA CỨU -->
    <?php if ($hasSearched): ?>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        Kết quả tra cứu cho: <span class="text-primary font-monospace">"<?= htmlspecialchars($keyword) ?>"</span>
                    </h5>
                    <span class="badge bg-secondary-subtle text-secondary fs-6">
                        Tìm thấy <?= count($results) ?> phiếu bảo hành
                    </span>
                </div>

                <?php if (empty($results)): ?>
                    <!-- Không tìm thấy kết quả -->
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                        <div class="py-4">
                            <i class="bi bi-shield-x text-muted" style="font-size: 64px;"></i>
                            <h4 class="fw-bold text-dark mt-3 mb-2">Không tìm thấy thông tin bảo hành phù hợp!</h4>
                            <p class="text-muted mx-auto" style="max-width: 500px;">
                                Vui lòng kiểm tra lại tính chính xác của số Serial/IMEI hoặc Số điện thoại. Nếu máy mới mua trong 24h qua, dữ liệu có thể đang được đồng bộ lên hệ thống.
                            </p>
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <a href="index.php?action=check-warranty" class="btn btn-outline-primary px-4 fw-semibold">
                                    Thử Lại Từ Khóa Khác
                                </a>
                                <a href="tel:19006868" class="btn btn-primary px-4 fw-semibold">
                                    <i class="bi bi-telephone-fill me-1"></i> Gọi Hotline 1900 6868
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Danh sách các kết quả tìm thấy -->
                    <div class="row g-4">
                        <?php foreach ($results as $item): ?>
                            <?php 
                            $isExpired = ($item['soNgayConLai'] <= 0);
                            $thumbImg  = !empty($item['hinhAnh']) ? htmlspecialchars($item['hinhAnh']) : ('assets/images/products/' . htmlspecialchars($item['maSP']) . '.jpg');
                            
                            // Ẩn 3 số giữa của SĐT để bảo mật thông tin
                            $phone = $item['soDienThoai'] ?? '';
                            $maskedPhone = (strlen($phone) >= 9) ? substr($phone, 0, 4) . '***' . substr($phone, -3) : $phone;
                            ?>
                            <div class="col-12">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                    <!-- Header Thẻ Bảo Hành -->
                                    <div class="card-header bg-light py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-receipt text-primary fs-5"></i>
                                            <span class="fw-bold text-dark">Mã Phiếu: <strong class="text-primary font-monospace">#<?= htmlspecialchars($item['maPhieuBH']) ?></strong></span>
                                        </div>
                                        <div>
                                            <?php if ($item['trangThaiPhieu'] === 'Đang hiệu lực'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fw-bold">
                                                    <i class="bi bi-shield-check me-1"></i> Bảo Hành Đang Hiệu Lực (Còn <?= $item['soNgayConLai'] ?> ngày)
                                                </span>
                                            <?php elseif ($item['trangThaiPhieu'] === 'Đang tiếp nhận bảo hành' || $item['trangThaiPhieu'] === 'Đang sửa chữa'): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 fw-bold">
                                                    <i class="bi bi-tools me-1"></i> <?= htmlspecialchars($item['trangThaiPhieu']) ?>
                                                </span>
                                            <?php elseif ($item['trangThaiPhieu'] === 'Đã sửa xong'): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 fw-bold">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Đã Sửa Xong - Mời Khách Nhận Máy
                                                </span>
                                            <?php elseif ($item['trangThaiPhieu'] === 'Đã trả khách'): ?>
                                                <span class="badge bg-secondary px-3 py-2 fw-bold">
                                                    <i class="bi bi-person-check-fill me-1"></i> Đã Trả Khách Hoàn Tất
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger px-3 py-2 fw-bold">
                                                    <i class="bi bi-clock-history me-1"></i> Đã Hết Hạn Bảo Hành
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Thân Thẻ Bảo Hành -->
                                    <div class="card-body p-4">
                                        <div class="row align-items-center g-4">
                                            <!-- Cột 1: Ảnh máy -->
                                            <div class="col-md-3 text-center">
                                                <div class="p-2 border rounded-3 bg-white d-inline-block shadow-sm">
                                                    <img src="<?= $thumbImg ?>" 
                                                         alt="<?= htmlspecialchars($item['tenSP']) ?>" 
                                                         class="img-fluid rounded" 
                                                         style="max-height: 150px; object-fit: contain;"
                                                         onerror="this.onerror=null;this.src='assets/images/products/SP004.jpg';">
                                                </div>
                                                <div class="mt-2">
                                                    <span class="badge bg-dark font-monospace px-2 py-1">
                                                        SN: <?= htmlspecialchars($item['maSerial_IMEI']) ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Cột 2: Thông tin Máy & Khách hàng -->
                                            <div class="col-md-5 border-end">
                                                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($item['tenSP']) ?></h5>
                                                <p class="text-muted small mb-3">
                                                    Hãng sản xuất: <strong><?= htmlspecialchars($item['tenTH'] ?? '') ?></strong> | Dòng máy: <strong><?= htmlspecialchars($item['tenDM'] ?? '') ?></strong>
                                                </p>

                                                <div class="p-3 bg-light rounded-3 small">
                                                    <div class="mb-1">Khách hàng sở hữu: <strong class="text-dark"><?= htmlspecialchars($item['hoVaTen'] ?: 'Khách hàng') ?></strong></div>
                                                    <div>Số điện thoại liên hệ: <strong class="text-primary font-monospace"><?= htmlspecialchars($maskedPhone) ?></strong></div>
                                                    <?php if (!empty($item['diaChi'])): ?>
                                                        <div class="text-muted mt-1">Địa chỉ: <?= htmlspecialchars($item['diaChi']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Cột 3: Thời hạn & Tiến độ sửa chữa -->
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <div class="text-muted small">Thời hạn bảo hành:</div>
                                                    <div class="fw-bold text-dark font-monospace">
                                                        <?= date('d/m/Y', strtotime($item['ngayBatDau'])) ?> &rarr; <?= date('d/m/Y', strtotime($item['ngayKetThuc'])) ?>
                                                    </div>
                                                    <div class="small mt-1">
                                                        <?php if ($isExpired): ?>
                                                            <span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i>Đã hết hạn bảo hành</span>
                                                        <?php else: ?>
                                                            <span class="text-success fw-bold"><i class="bi bi-check2-circle me-1"></i>Còn <?= $item['soNgayConLai'] ?> ngày bảo hành</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="border-top pt-2">
                                                    <div class="text-muted small fw-semibold">Chính sách áp dụng:</div>
                                                    <div class="small text-secondary mb-2">
                                                        <?= htmlspecialchars($item['moTaChinhSach'] ?: 'Bảo hành chính hãng toàn diện phần cứng.') ?>
                                                    </div>

                                                    <?php if (!empty($item['ghiChuBaoHanh'])): ?>
                                                        <div class="alert alert-warning p-2 small mb-0 rounded-2">
                                                            <strong><i class="bi bi-info-circle me-1"></i>Tiến độ KTV:</strong> <?= htmlspecialchars($item['ghiChuBaoHanh']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Footer Thẻ Bảo Hành -->
                                    <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-headset me-1 text-primary"></i> Cần hỗ trợ khẩn cấp? Gọi Hotline Kỹ Thuật: <strong>1900 6868 (Phím 2)</strong>
                                        </small>
                                        <a href="index.php?action=client-detail&id=<?= urlencode($item['maSP']) ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                                            Xem Thông Tin Máy
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- HƯỚNG DẪN TÌM SỐ SERIAL & CAM KẾT DỊCH VỤ BẢO HÀNH -->
        <div class="row g-4 mt-2 justify-content-center">
            <div class="col-lg-5">
                <div class="p-4 bg-white border rounded-4 shadow-sm h-100">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-question-circle text-primary me-2"></i>Cách Tìm Số Serial/IMEI Laptop
                    </h5>
                    <ul class="list-unstyled text-secondary small mb-0" style="line-height: 2;">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Mặt đáy laptop:</strong> Tìm tem nhãn có chữ <code>Serial No (S/N)</code> hoặc <code>Service Tag</code>.</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Trên vỏ hộp máy:</strong> Kiểm tra tem mã vạch dán bên hông vỏ hộp carton của sản phẩm.</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Trong Windows:</strong> Bấm phím <code>Win + R</code>, gõ <code>cmd</code> rồi chạy lệnh: <code>wmic bios get serialnumber</code>.</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Trên phiếu xuất kho:</strong> Tra cứu tại cột Serial/IMEI trong hóa đơn bán lẻ đã nhận khi mua máy.</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="p-4 bg-white border rounded-4 shadow-sm h-100">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-award text-warning me-2"></i>Cam Kết Dịch Vụ Bảo Hành Vàng
                    </h5>
                    <ul class="list-unstyled text-secondary small mb-0" style="line-height: 2;">
                        <li><i class="bi bi-patch-check-fill text-primary me-2"></i><strong>100% Linh kiện zin chính hãng:</strong> Thay thế linh kiện mới xuất xưởng từ nhà sản xuất.</li>
                        <li><i class="bi bi-patch-check-fill text-primary me-2"></i><strong>1 đổi 1 trong 30 ngày:</strong> Đổi ngay máy mới tương đương nếu có lỗi phần cứng từ NSX.</li>
                        <li><i class="bi bi-patch-check-fill text-primary me-2"></i><strong>Hỗ trợ mượn máy thay thế:</strong> Cho mượn laptop sử dụng tạm nếu thời gian xử lý trên 48h.</li>
                        <li><i class="bi bi-patch-check-fill text-primary me-2"></i><strong>Bảo dưỡng & Vệ sinh trọn đời:</strong> Miễn phí tra keo tản nhiệt và vệ sinh bụi máy định kỳ.</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
