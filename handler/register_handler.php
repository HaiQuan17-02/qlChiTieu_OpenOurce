<?php
// register_handler.php - Xử lý đăng ký
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function/auth.php';
require_once __DIR__ . '/../function/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullname = sanitizeInput($_POST['fullname'] ?? '');
    
    // Validate
    if ($password !== $confirmPassword) {
        setFlashMessage('error', 'Mật khẩu xác nhận không khớp');
        redirect(SITE_URL . '/view/auth/register.php');
    }
    
    $result = register($username, $email, $password, $fullname);
    
    if ($result['success']) {
        // Redirect to setup PIN page
        redirect(SITE_URL . '/view/auth/setup_pin.php');
    } else {
        setFlashMessage('error', $result['message']);
        redirect(SITE_URL . '/view/auth/register.php');
    }
} else {
    redirect(SITE_URL . '/view/auth/register.php');
}
?>

