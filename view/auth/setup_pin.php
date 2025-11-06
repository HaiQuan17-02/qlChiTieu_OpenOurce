<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/auth.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Thiết lập mã PIN';

// Nếu chưa đăng nhập thì chuyển về đăng nhập
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Nếu đã có PIN thì chuyển về dashboard
if (hasPinCode()) {
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
                            <i class="bi bi-shield-check fs-1 text-primary"></i>
                            <h3 class="mt-3 mb-2">Thiết lập mã PIN</h3>
                            <p class="text-muted">Tạo mã PIN 6 số để bảo vệ ví của bạn</p>
                        </div>
                        
                        <?= displayFlashMessage() ?>
                        
                        <form method="POST" action="<?= SITE_URL ?>/handler/setup_pin_handler.php" id="pinForm">
                            <div class="mb-3">
                                <label for="pin_code" class="form-label">
                                    <i class="bi bi-shield-lock"></i> Mã PIN
                                </label>
                                <input type="password" class="form-control form-control-lg text-center" id="pin_code" 
                                       name="pin_code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" 
                                       required autofocus>
                                <small class="text-muted">Nhập 6 số</small>
                                <small class="text-danger d-none" id="pinError"></small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_pin" class="form-label">
                                    <i class="bi bi-shield-check"></i> Xác nhận mã PIN
                                </label>
                                <input type="password" class="form-control form-control-lg text-center" id="confirm_pin" 
                                       name="confirm_pin" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-check-circle"></i> Xác nhận
                            </button>
                        </form>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <small>Mã PIN sẽ được dùng để xác thực khi xem chi tiết ví</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validate PIN match
        document.getElementById('confirm_pin').addEventListener('input', function() {
            const pin = document.getElementById('pin_code').value;
            const confirmPin = this.value;
            
            if (pin !== confirmPin) {
                this.setCustomValidity('Mã PIN không khớp');
                document.getElementById('pinError').textContent = 'Mã PIN không khớp';
                document.getElementById('pinError').classList.remove('d-none');
            } else {
                this.setCustomValidity('');
                document.getElementById('pinError').classList.add('d-none');
            }
        });

        document.getElementById('pin_code').addEventListener('input', function() {
            document.getElementById('confirm_pin').value = '';
            document.getElementById('pinError').classList.add('d-none');
        });
    </script>
</body>
</html>

