<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/helper.php';
require_once __DIR__ . '/../../function/auth.php';

$pageTitle = 'Quên mã PIN';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

$currentUser = getCurrentUser();
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
                            <i class="bi bi-shield-lock fs-1 text-primary"></i>
                            <h3 class="mt-3 mb-0"><?= e($pageTitle) ?></h3>
                            <p class="text-muted">Xác minh thông tin tài khoản trước khi đặt lại mã PIN</p>
                        </div>

                        <?= displayFlashMessage() ?>

                        <form method="POST" action="<?= SITE_URL ?>/handler/pin_reset_handler.php?action=request">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person"></i> Tên đăng nhập
                                </label>
                                <input type="text" class="form-control" id="username" name="username"
                                       value="<?= e($currentUser['username'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= e($currentUser['email'] ?? '') ?>" required>
                            </div>

                            <p class="small text-muted">
                                Chúng tôi sẽ gửi liên kết đặt lại mã PIN tới email đã đăng ký nếu thông tin trùng khớp.
                            </p>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-send"></i> Gửi liên kết đặt lại
                            </button>
                        </form>

                        <div class="text-center">
                            <a href="<?= SITE_URL ?>/view/profile/index.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Quay lại hồ sơ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

