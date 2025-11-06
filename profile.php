<?php
// profile.php - Hồ sơ người dùng
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function/auth.php';
require_once __DIR__ . '/function/helper.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

if (!hasPinCode()) {
    redirect(SITE_URL . '/view/auth/setup_pin.php');
}

redirect(SITE_URL . '/view/profile/index.php');
?>

