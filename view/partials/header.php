<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?> <?= e(SITE_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
        <nav class="navbar-top">
            <div class="navbar-brand-mobile">
                <i class="bi bi-wallet2"></i> <?= e(SITE_NAME) ?>
            </div>
            <div class="navbar-actions">
                <div class="notification-bell">
                    <i class="bi bi-bell"></i>
                </div>
                <div class="user-dropdown">
                    <div class="user-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="user-name">
                        <?= e($_SESSION['fullname'] ?? 'User') ?>
                    </div>
                </div>
            </div>
        </nav>
    <?php endif; ?>

