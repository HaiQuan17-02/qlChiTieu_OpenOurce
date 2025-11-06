<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/auth.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Đăng nhập';

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
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-wallet2 fs-1 text-primary"></i>
                            <h3 class="mt-3 mb-0"><?= e(SITE_NAME) ?></h3>
                            <p class="text-muted">Đăng nhập vào tài khoản</p>
                        </div>
                        
                        <?= displayFlashMessage() ?>
                        
                        <form method="POST" action="<?= SITE_URL ?>/handler/login_handler.php">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person"></i> Tên đăng nhập hoặc Email
                                </label>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i> Mật khẩu
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">
                                Chưa có tài khoản? 
                                <a href="<?= SITE_URL ?>/view/auth/register.php" class="text-decoration-none">Đăng ký ngay</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

