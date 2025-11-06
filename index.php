<?php
// index.php - Trang khởi động
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function/auth.php';
require_once __DIR__ . '/function/helper.php';

// Redirect based on login status
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
} else {
    redirect(SITE_URL . '/view/auth/login.php');
}
?>

