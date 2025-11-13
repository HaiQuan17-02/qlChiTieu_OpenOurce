<?php
// password_reset_handler.php - Xử lý yêu cầu đặt lại mật khẩu
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function/auth.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/mail.php';

$action = $_GET['action'] ?? '';

if ($action === 'request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');

    if (empty($email) || empty($username)) {
        setFlashMessage('error', 'Vui lòng nhập đầy đủ email và tên đăng nhập');
        redirect(SITE_URL . '/view/auth/forgot_password.php');
    }

    if (!isValidEmail($email)) {
        setFlashMessage('error', 'Email không hợp lệ');
        redirect(SITE_URL . '/view/auth/forgot_password.php');
    }

    $user = getUserByEmailAndUsername($email, $username);
    if (!$user) {
        setFlashMessage('error', 'Thông tin không khớp với tài khoản đã đăng ký');
        redirect(SITE_URL . '/view/auth/forgot_password.php');
    }

    $tokenResult = createPasswordResetToken($user['id']);
    if (!$tokenResult['success']) {
        setFlashMessage('error', $tokenResult['message'] ?? 'Không thể tạo token đặt lại mật khẩu');
        redirect(SITE_URL . '/view/auth/forgot_password.php');
    }

    $resetLink = SITE_URL . '/view/auth/reset_password.php?token=' . urlencode($tokenResult['token']);
    $subject = '[' . SITE_NAME . '] Đặt lại mật khẩu';
    $body = '<p>Chào ' . htmlspecialchars($user['fullname'] ?? $user['username'], ENT_QUOTES, 'UTF-8') . ',</p>';
    $body .= '<p>Bạn vừa yêu cầu đặt lại mật khẩu cho tài khoản ' . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . '.</p>';
    $body .= '<p>Vui lòng nhấp vào liên kết sau để đặt lại mật khẩu (liên kết có hiệu lực trong 1 giờ):</p>';
    $body .= '<p><a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '</a></p>';
    $body .= '<p>Nếu bạn không yêu cầu thao tác này, hãy bỏ qua email này.</p>';
    $body .= '<p>Trân trọng,<br>' . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . '</p>';

    sendEmail($user['email'], $subject, $body, ['is_html' => true]);

    setFlashMessage('success', 'Đã gửi email hướng dẫn đặt lại mật khẩu');
    redirect(SITE_URL . '/view/auth/login.php');
} elseif ($action === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = sanitizeInput($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($token)) {
        setFlashMessage('error', 'Token không hợp lệ');
        redirect(SITE_URL . '/view/auth/forgot_password.php');
    }

    if ($password !== $confirmPassword) {
        setFlashMessage('error', 'Mật khẩu xác nhận không khớp');
        redirect(SITE_URL . '/view/auth/reset_password.php?token=' . urlencode($token));
    }

    $resetResult = resetPasswordWithToken($token, $password);
    if ($resetResult['success']) {
        setFlashMessage('success', $resetResult['message']);
        redirect(SITE_URL . '/view/auth/login.php');
    } else {
        setFlashMessage('error', $resetResult['message'] ?? 'Không thể đặt lại mật khẩu');
        redirect(SITE_URL . '/view/auth/reset_password.php?token=' . urlencode($token));
    }
} else {
    redirect(SITE_URL . '/view/auth/login.php');
}
?>

