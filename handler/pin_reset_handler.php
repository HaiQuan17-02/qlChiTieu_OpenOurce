<?php
// pin_reset_handler.php - Xử lý yêu cầu đặt lại mã PIN
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function/auth.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/mail.php';

$action = $_GET['action'] ?? '';

if ($action === 'request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Vui lòng đăng nhập để đặt lại mã PIN');
        redirect(SITE_URL . '/view/auth/login.php');
    }

    $email = sanitizeInput($_POST['email'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');

    if (empty($email) || empty($username)) {
        setFlashMessage('error', 'Vui lòng nhập đầy đủ email và tên đăng nhập');
        redirect(SITE_URL . '/view/auth/forgot_pin.php');
    }

    if (!isValidEmail($email)) {
        setFlashMessage('error', 'Email không hợp lệ');
        redirect(SITE_URL . '/view/auth/forgot_pin.php');
    }

    $user = getUserByEmailAndUsername($email, $username);
    if (!$user || $user['id'] != getCurrentUserId()) {
        setFlashMessage('error', 'Thông tin không khớp với tài khoản của bạn');
        redirect(SITE_URL . '/view/auth/forgot_pin.php');
    }

    $tokenResult = createPinResetToken($user['id']);
    if (!$tokenResult['success']) {
        setFlashMessage('error', $tokenResult['message'] ?? 'Không thể tạo token đặt lại PIN');
        redirect(SITE_URL . '/view/auth/forgot_pin.php');
    }

    $resetLink = SITE_URL . '/view/auth/reset_pin.php?token=' . urlencode($tokenResult['token']);
    $subject = '[' . SITE_NAME . '] Đặt lại mã PIN';
    $body = '<p>Chào ' . htmlspecialchars($user['fullname'] ?? $user['username'], ENT_QUOTES, 'UTF-8') . ',</p>';
    $body .= '<p>Bạn vừa yêu cầu đặt lại mã PIN cho tài khoản ' . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . '.</p>';
    $body .= '<p>Vui lòng nhấp vào liên kết sau để đặt lại mã PIN (liên kết có hiệu lực trong 1 giờ):</p>';
    $body .= '<p><a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '</a></p>';
    $body .= '<p>Nếu bạn không yêu cầu thao tác này, hãy bỏ qua email này.</p>';
    $body .= '<p>Trân trọng,<br>' . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . '</p>';

    sendEmail($user['email'], $subject, $body, ['is_html' => true]);

    setFlashMessage('success', 'Đã gửi email hướng dẫn đặt lại mã PIN');
    redirect(SITE_URL . '/view/auth/login.php');
} elseif ($action === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = sanitizeInput($_POST['token'] ?? '');
    $pinCode = sanitizeInput($_POST['pin_code'] ?? '');
    $confirmPin = sanitizeInput($_POST['confirm_pin'] ?? '');

    if (empty($token)) {
        setFlashMessage('error', 'Token không hợp lệ');
        redirect(SITE_URL . '/view/auth/forgot_pin.php');
    }

    if ($pinCode !== $confirmPin) {
        setFlashMessage('error', 'Mã PIN xác nhận không khớp');
        redirect(SITE_URL . '/view/auth/reset_pin.php?token=' . urlencode($token));
    }

    $resetResult = resetPinWithToken($token, $pinCode);
    if ($resetResult['success']) {
        setFlashMessage('success', $resetResult['message']);
        redirect(SITE_URL . '/view/auth/login.php');
    } else {
        setFlashMessage('error', $resetResult['message'] ?? 'Không thể đặt lại mã PIN');
        redirect(SITE_URL . '/view/auth/reset_pin.php?token=' . urlencode($token));
    }
} else {
    redirect(SITE_URL . '/view/auth/login.php');
}
?>

