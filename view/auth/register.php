<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/auth.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Đăng ký';

// Nếu đã đăng nhập thì chuyển về dashboard
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - <?= e(SITE_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body class="auth-body">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-wallet2 fs-1 text-primary"></i>
                            <h3 class="mt-3 mb-0">Đăng ký tài khoản</h3>
                            <p class="text-muted">Tạo tài khoản mới</p>
                        </div>
                        
                        <?= displayFlashMessage() ?>
                        
                        <form method="POST" action="<?= SITE_URL ?>/handler/register_handler.php">
                            <div class="mb-3">
                                <label for="fullname" class="form-label">
                                    <i class="bi bi-person-badge"></i> Họ và tên
                                </label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person"></i> Tên đăng nhập
                                </label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i> Mật khẩu
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                <small class="text-muted">Tối thiểu 6 ký tự</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-lock-fill"></i> Xác nhận mật khẩu
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-person-plus"></i> Đăng ký
                            </button>
                        </form>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <small>Sau khi đăng ký, bạn sẽ được yêu cầu tạo mã PIN để bảo vệ ví của mình</small>
                        </div>
                        
                        <div class="text-center">
                            <p class="mb-0">
                                Đã có tài khoản? 
                                <a href="<?= SITE_URL ?>/view/auth/login.php" class="text-decoration-none">Đăng nhập</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validate password match
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Mật khẩu không khớp');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>

