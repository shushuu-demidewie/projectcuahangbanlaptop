<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Quản Lý Cửa Hàng Laptop</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .register-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .register-header {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
        }
        .register-header i {
            font-size: 38px;
            margin-bottom: 6px;
            display: inline-block;
        }
        .btn-register {
            background: #0d9488;
            color: #ffffff;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .btn-register:hover {
            background: #0f766e;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.35);
        }
    </style>
</head>
<body>

<div class="container p-3">
    <div class="register-card mx-auto">
        <!-- Header -->
        <div class="register-header">
            <i class="bi bi-person-plus-fill"></i>
            <h4 class="fw-bold mb-1">Tạo Tài Khoản Khách Hàng</h4>
            <p class="mb-0 text-white-50 small">Trải nghiệm mua sắm laptop chính hãng, giá tốt</p>
        </div>

        <!-- Body -->
        <div class="p-4 p-sm-5">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>
                        <?= htmlspecialchars($success) ?><br>
                        <a href="index.php?url=login" class="fw-bold text-success text-decoration-underline">Bấm vào đây để Đăng nhập</a>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="index.php?url=register" method="POST" autocomplete="off">
                <!-- Họ và tên -->
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="hoTen" name="hoTen" 
                           placeholder="Họ và tên" value="<?= htmlspecialchars($_POST['hoTen'] ?? '') ?>" required>
                    <label for="hoTen"><i class="bi bi-card-heading me-1"></i> Họ và tên</label>
                </div>

                <!-- Email -->
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <label for="email"><i class="bi bi-envelope me-1"></i> Địa chỉ Email</label>
                </div>

                <!-- Tên tài khoản -->
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="tenTaiKhoan" name="tenTaiKhoan" 
                           placeholder="Tên tài khoản" value="<?= htmlspecialchars($_POST['tenTaiKhoan'] ?? '') ?>" required>
                    <label for="tenTaiKhoan"><i class="bi bi-person me-1"></i> Tên tài khoản đăng nhập</label>
                </div>

                <!-- Mật khẩu -->
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="matKhau" name="matKhau" 
                           placeholder="Mật khẩu" required minlength="6">
                    <label for="matKhau"><i class="bi bi-lock me-1"></i> Mật khẩu (tối thiểu 6 ký tự)</label>
                </div>

                <!-- Xác nhận mật khẩu -->
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="reMatKhau" name="reMatKhau" 
                           placeholder="Xác nhận mật khẩu" required minlength="6">
                    <label for="reMatKhau"><i class="bi bi-shield-check me-1"></i> Nhập lại mật khẩu</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-register w-100 mb-3">
                    <i class="bi bi-check2-circle me-1"></i> Hoàn Tất Đăng Ký
                </button>

                <!-- Link Đăng nhập -->
                <div class="text-center mt-3">
                    <span class="text-muted small">Đã có tài khoản? </span>
                    <a href="index.php?url=login" class="text-decoration-none fw-semibold small text-teal">
                        Đăng nhập ngay
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
