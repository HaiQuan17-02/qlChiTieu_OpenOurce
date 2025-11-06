<?php
// wallets.php - Redirect to wallets page
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function/auth.php';
require_once __DIR__ . '/function/helper.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Check if user needs to setup PIN
if (!hasPinCode()) {
    redirect(SITE_URL . '/view/auth/setup_pin.php');
}

// Redirect to wallets view
redirect(SITE_URL . '/view/wallet/index.php');

