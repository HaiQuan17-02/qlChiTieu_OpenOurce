<?php
// config.php - Cấu hình chung cho hệ thống

// Site configuration
define('SITE_NAME', 'Quản Lý Chi Tiêu');
define('SITE_URL', 'http://localhost/quanLyChiTieu');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>

