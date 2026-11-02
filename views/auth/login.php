<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Quản Lý Cửa Hàng Laptop</title>
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
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            max-width: 440px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            padding: 32px 24px;
            text-align: center;
        }
        .login-header i {
            font-size: 42px;
            margin-bottom: 8px;
            display: inline-block;
        }
        .form-floating > .form-control:focus ~ label {
            color: #2563eb;
        }
        .btn-login {
            background: #2563eb;
            color: #ffffff;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .btn-login:hover {
            background: #1d4ed8;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
    </style>
</head>
<body>

<div class="container p-3">
    <div class="login-card mx-auto">
        <!-- Header -->
        <div class="login-header">
            <i class="bi bi-laptop"></i>
            <h4 class="fw-bold mb-1">Cửa Hàng Laptop</h4>
            <p class="mb-0 text-white-50 small">Đăng nhập hệ thống quản lý & mua hàng</p>
        </div>

        <!-- Body -->
        <div class="p-4 p-sm-5">
            <!-- Thông báo Lỗi -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Thông báo Thành công từ session hoặc query string -->
            <?php 
                $logoutMsg = isset($_GET['logout']) && $_GET['logout'] === 'success' ? 'Bạn đã đăng xuất an toàn khỏi hệ thống.' : '';
                $successMsg = !empty($success) ? $success : (!empty($_SESSION['success_message']) ? $_SESSION['success_message'] : $logoutMsg);
                unset($_SESSION['success_message']);
            ?>
            <?php if (!empty($successMsg)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($successMsg) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="index.php?url=login" method="POST" autocomplete="off">
                <!-- Tên đăng nhập -->
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="tenTaiKhoan" name="tenTaiKhoan" 
                           placeholder="Tên tài khoản hoặc Email" 
                           value="<?= htmlspecialchars($tenTaiKhoan ?? '') ?>" required autofocus>
                    <label for="tenTaiKhoan"><i class="bi bi-person me-1"></i> Tên đăng nhập hoặc Email</label>
                </div>

                <!-- Mật khẩu -->
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="matKhau" name="matKhau" 
                           placeholder="Mật khẩu" required>
                    <label for="matKhau"><i class="bi bi-lock me-1"></i> Mật khẩu</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-login w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Đăng Nhập
                </button>

                <!-- Link Đăng ký -->
                <div class="text-center mt-3">
                    <span class="text-muted small">Chưa có tài khoản? </span>
                    <a href="index.php?url=register" class="text-decoration-none fw-semibold small text-primary">
                        Đăng ký tài khoản mới
                    </a>
                </div>

                <div class="text-center mt-2">
                    <a href="index.php?url=home" class="text-secondary small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Quay về Trang chủ
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
