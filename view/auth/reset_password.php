<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/helper.php';
require_once __DIR__ . '/../../function/auth.php';

$pageTitle = 'Đặt lại mật khẩu';
$token = sanitizeInput($_GET['token'] ?? '');
$tokenRecord = $token ? getValidPasswordResetToken($token) : null;
$tokenValid = $tokenRecord !== null;
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
                            <p class="text-muted">Nhập mật khẩu mới cho tài khoản</p>
                        </div>

                        <?= displayFlashMessage() ?>

                        <?php if (!$tokenValid): ?>
                            <div class="alert alert-danger">
                                Token đặt lại mật khẩu không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu lại.
                            </div>
                            <div class="text-center">
                                <a href="<?= SITE_URL ?>/view/auth/forgot_password.php" class="text-decoration-none">
                                    <i class="bi bi-envelope"></i> Gửi lại yêu cầu đặt lại mật khẩu
                                </a>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="<?= SITE_URL ?>/handler/password_reset_handler.php?action=reset">
                                <input type="hidden" name="token" value="<?= e($token) ?>">

                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-lock"></i> Mật khẩu mới (≥ 6 ký tự)
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="bi bi-lock-fill"></i> Xác nhận mật khẩu
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="bi bi-check-circle"></i> Đặt lại mật khẩu
                                </button>
                            </form>

                            <div class="text-center">
                                <a href="<?= SITE_URL ?>/view/auth/login.php" class="text-decoration-none">
                                    <i class="bi bi-arrow-left"></i> Quay lại đăng nhập
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

