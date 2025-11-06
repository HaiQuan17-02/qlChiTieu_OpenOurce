<?php
// login_handler.php - Xử lý đăng nhập
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function/auth.php';
require_once __DIR__ . '/../function/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = login($username, $password);
    
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
        redirect(SITE_URL . '/dashboard.php');
    } else {
        setFlashMessage('error', $result['message']);
        redirect(SITE_URL . '/view/auth/login.php');
    }
} else {
    redirect(SITE_URL . '/view/auth/login.php');
}
?>

