<?php
// transaction.php - Redirect đến transaction list
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function/auth.php';
require_once __DIR__ . '/function/helper.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Check if user needs to setup PIN
if (!hasPinCode()) {
    redirect(SITE_URL . '/view/auth/setup_pin.php');
}

redirect(SITE_URL . '/view/transaction/index.php');
?>

