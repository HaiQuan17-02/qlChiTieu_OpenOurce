<?php
// setup_pin_handler.php - Xử lý thiết lập mã PIN
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function/auth.php';
require_once __DIR__ . '/../function/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pinCode = sanitizeInput($_POST['pin_code'] ?? '');
    $confirmPin = sanitizeInput($_POST['confirm_pin'] ?? '');
    
    // Validate
    if ($pinCode !== $confirmPin) {
        setFlashMessage('error', 'Mã PIN xác nhận không khớp');
        redirect(SITE_URL . '/view/auth/setup_pin.php');
    }
    
    $result = setPinCode($pinCode);
    
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
        redirect(SITE_URL . '/dashboard.php');
    } else {
        setFlashMessage('error', $result['message']);
        redirect(SITE_URL . '/view/auth/setup_pin.php');
    }
} else {
    redirect(SITE_URL . '/view/auth/setup_pin.php');
}
?>

