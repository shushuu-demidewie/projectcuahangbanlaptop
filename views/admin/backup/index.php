<?php
/**
 * File: views/admin/backup/index.php
 * Giao diện Quản trị Sao lưu & Phục hồi CSDL (Borg Backup Server Engine)
 */

$flash = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sao Lưu & Phục Hồi Dữ Liệu - EmShu Store Admin</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        .stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            border-left: 4px solid #2563eb;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        }
        .table-custom-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            overflow: hidden;
        }
        .table-custom thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-custom tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            color: #1e293b;
            font-size: 0.90rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .code-pill {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 3px 8px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: #0f172a;
        }
    </style>
</head>
<body>

<!-- SIDEBAR DÙNG CHUNG -->
<?php 
$activeMenu = 'backups';
require __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- MAIN CONTENT -->
<main class="main-content">
    <!-- Top Navbar -->
    <header class="top-navbar d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-1 fw-bold text-dark">
                <i class="bi bi-database-check me-2 text-primary"></i>Trung Tâm Sao Lưu & Phục Hồi CSDL
            </h5>
            <div class="text-muted small">
                Cơ chế sao lưu an toàn kế thừa từ kiến trúc <strong>Borg Backup Server</strong>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCreateBackup">
                <i class="bi bi-cloud-arrow-up-fill"></i>
                <span>Tạo Bản Sao Lưu Ngay</span>
            </button>
        </div>
    </header>

    <!-- Thông báo Flash Message -->
    <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> fs-5"></i>
        <div><?= htmlspecialchars($flash['message']) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Thống kê nhanh CSDL (Stats Cards) -->
    <div class="row g-3 mb-4">
        <!-- 1. Dung lượng CSDL -->
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #2563eb;">
                <div class="text-muted small fw-semibold text-uppercase">Dung lượng Database</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= number_format($stats['total_size_mb'], 2) ?> <span class="fs-6 text-muted fw-normal">MB</span></div>
                <div class="text-primary small mt-1"><i class="bi bi-server me-1"></i> CSDL: ql_cuahang_laptop</div>
            </div>
        </div>

        <!-- 2. Bảng & Bản ghi -->
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #10b981;">
                <div class="text-muted small fw-semibold text-uppercase">Cấu trúc dữ liệu</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= number_format($stats['total_tables']) ?> <span class="fs-6 text-muted fw-normal">bảng</span></div>
                <div class="text-success small mt-1"><i class="bi bi-table me-1"></i> ~<?= number_format($stats['total_rows']) ?> dòng dữ liệu</div>
            </div>
        </div>

        <!-- 3. Số bản sao lưu hiện có -->
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #8b5cf6;">
                <div class="text-muted small fw-semibold text-uppercase">Bản sao lưu lưu trữ</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= number_format($stats['backups_count']) ?> <span class="fs-6 text-muted fw-normal">file</span></div>
                <div class="text-secondary small mt-1"><i class="bi bi-archive me-1"></i> Tổng: <?= htmlspecialchars($stats['backups_total_size']) ?></div>
            </div>
        </div>

        <!-- 4. Lần sao lưu gần nhất -->
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="text-muted small fw-semibold text-uppercase">Sao lưu gần nhất</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= $stats['last_backup'] ? htmlspecialchars($stats['last_backup']) : '<span class="text-muted fs-6">Chưa có</span>' ?></div>
                <div class="text-warning small mt-1"><i class="bi bi-clock-history me-1"></i> Tự động kiểm tra tính toàn vẹn</div>
            </div>
        </div>
    </div>

    <!-- Danh sách các bản sao lưu -->
    <div class="table-custom-card mb-4">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-white">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-hdd-stack text-primary"></i>
                <span>Danh Sách File Sao Lưu (Kho lưu trữ: storage/backups)</span>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                <?= count($backups) ?> bản sao lưu khả dụng
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">STT</th>
                        <th>Tên File Sao Lưu (.sql)</th>
                        <th>Dung Lượng</th>
                        <th>Thời Gian Khởi Tạo</th>
                        <th class="text-end" style="width: 280px;">Thao Tác Quản Trị</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($backups)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Hiện chưa có bản sao lưu nào. Hãy bấm nút <strong>"Tạo Bản Sao Lưu Ngay"</strong> để tạo bản đầu tiên!
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($backups as $idx => $b): ?>
                        <tr>
                            <td class="text-muted fw-bold"><?= $idx + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-file-earmark-code text-primary fs-5"></i>
                                    <span class="code-pill fw-semibold"><?= htmlspecialchars($b['filename']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    <i class="bi bi-hdd me-1"></i><?= htmlspecialchars($b['size_formatted']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($b['created_at']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <!-- Tải về -->
                                    <a href="index.php?action=admin-backup-download&file=<?= urlencode($b['filename']) ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Tải file về máy tính">
                                        <i class="bi bi-download"></i> Tải về
                                    </a>

                                    <!-- Phục hồi -->
                                    <form action="index.php?action=admin-backup-restore" method="POST" class="d-inline" 
                                          onsubmit="return confirm('CẢNH BÁO NGUY HIỂM: Phục hồi CSDL sẽ ghi đè toàn bộ dữ liệu hiện tại bằng dữ liệu từ bản sao lưu [<?= htmlspecialchars($b['filename']) ?>].\n\nBạn có chắc chắn 100% muốn tiếp tục?');">
                                        <input type="hidden" name="file" value="<?= htmlspecialchars($b['filename']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Khôi phục dữ liệu">
                                            <i class="bi bi-arrow-counterclockwise"></i> Phục hồi
                                        </button>
                                    </form>

                                    <!-- Xóa -->
                                    <form action="index.php?action=admin-backup-delete" method="POST" class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản sao lưu [<?= htmlspecialchars($b['filename']) ?>]?');">
                                        <input type="hidden" name="file" value="<?= htmlspecialchars($b['filename']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa file">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hộp ghi chú kiến trúc Borg Backup Server -->
    <div class="card border-0 bg-light p-3 rounded-3">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-shield-lock-fill text-primary fs-3"></i>
            <div>
                <h6 class="fw-bold mb-1 text-dark">Kiến trúc Sao lưu Chuẩn Borg Backup Server (BBS Engine)</h6>
                <p class="text-muted small mb-0">
                    Cơ chế sao lưu sử dụng thuật toán xuất trực tiếp cấu trúc DDL và khối dữ liệu DML qua kết nối PDO được bảo vệ với <code>FOREIGN_KEY_CHECKS = 0</code>, tự động gói Transaction an toàn. Dữ liệu sao lưu hoàn toàn tương thích để import lại vào bất kỳ máy chủ MySQL/MariaDB nào.
                </p>
            </div>
        </div>
    </div>
</main>

<!-- Modal Tạo Bản Sao Lưu Mới -->
<div class="modal fade" id="modalCreateBackup" tabindex="-1" aria-labelledby="modalCreateBackupLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="index.php?action=admin-backup-create" method="POST" id="formCreateBackup">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-6 fw-bold" id="modalCreateBackupLabel">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Khởi Tạo Bản Sao Lưu CSDL Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Tên cơ sở dữ liệu</label>
                        <input type="text" class="form-control bg-light" value="ql_cuahang_laptop" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label for="backupNote" class="form-label fw-semibold text-dark">Ghi chú bản sao lưu (Tùy chọn)</label>
                        <input type="text" class="form-control" id="backupNote" name="note" placeholder="VD: truoc_khi_nhap_lo_hang_dell, cuoi_tuan,...">
                        <div class="form-text small">Ghi chú sẽ được gắn vào tên file để bạn dễ nhận biết mục đích sao lưu.</div>
                    </div>

                    <div class="alert alert-info py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Hệ thống sẽ trích xuất toàn bộ dữ liệu hiện tại và lưu vào thư mục <code>storage/backups</code>.
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2" id="btnSubmitBackup">
                        <i class="bi bi-check2-circle"></i>
                        <span>Bắt đầu Sao lưu</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('formCreateBackup').addEventListener('submit', function() {
    const btn = document.getElementById('btnSubmitBackup');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang sao lưu...';
});
</script>

</body>
</html>

