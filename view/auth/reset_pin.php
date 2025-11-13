<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/helper.php';
require_once __DIR__ . '/../../function/auth.php';

$pageTitle = 'Đặt lại mã PIN';
$token = sanitizeInput($_GET['token'] ?? '');
$tokenRecord = $token ? getValidPinResetToken($token) : null;
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
                            <i class="bi bi-key fs-1 text-primary"></i>
                            <h3 class="mt-3 mb-0"><?= e($pageTitle) ?></h3>
                            <p class="text-muted">Nhập mã PIN mới cho tài khoản</p>
                        </div>

                        <?= displayFlashMessage() ?>

                        <?php if (!$tokenValid): ?>
                            <div class="alert alert-danger">
                                Token đặt lại mã PIN không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu lại.
                            </div>
                            <div class="text-center">
                                <a href="<?= SITE_URL ?>/view/auth/forgot_pin.php" class="text-decoration-none">
                                    <i class="bi bi-envelope"></i> Gửi lại yêu cầu đặt lại PIN
                                </a>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="<?= SITE_URL ?>/handler/pin_reset_handler.php?action=reset">
                                <input type="hidden" name="token" value="<?= e($token) ?>">

                                <div class="mb-3">
                                    <label for="pin_code" class="form-label">
                                        <i class="bi bi-shield-lock"></i> Mã PIN mới (6 số)
                                    </label>
                                    <input type="password" class="form-control" id="pin_code" name="pin_code" required pattern="\d{6}" maxlength="6">
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_pin" class="form-label">
                                        <i class="bi bi-shield-check"></i> Xác nhận mã PIN
                                    </label>
                                    <input type="password" class="form-control" id="confirm_pin" name="confirm_pin" required pattern="\d{6}" maxlength="6">
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="bi bi-check-circle"></i> Đặt lại mã PIN
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

