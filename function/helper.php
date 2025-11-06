<?php
// helper.php - Các hàm tiện ích chung

// Format tiền tệ
function formatCurrency($amount, $currency = 'VND') {
    if ($currency === 'VND') {
        return number_format($amount, 0, ',', '.') . ' đ';
    }
    return number_format($amount, 2, '.', ',') . ' ' . $currency;
}

// Format ngày tháng
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// Format datetime
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate password (ít nhất 6 ký tự)
function isValidPassword($password) {
    return strlen($password) >= 6;
}

// Escape HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Display flash message HTML
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type'];
        $message = e($flash['message']);
        $alertClass = 'alert-' . ($type === 'error' ? 'danger' : $type);
        
        echo "<div class='alert {$alertClass} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}

// Get first day of month
function getFirstDayOfMonth($date = null) {
    $date = $date ?: date('Y-m-d');
    return date('Y-m-01', strtotime($date));
}

// Get last day of month
function getLastDayOfMonth($date = null) {
    $date = $date ?: date('Y-m-d');
    return date('Y-m-t', strtotime($date));
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Get date range for current month
function getCurrentMonthRange() {
    return [
        'start' => date('Y-m-01'),
        'end' => date('Y-m-t')
    ];
}

// Get date range for specific month
function getMonthRange($year, $month) {
    return [
        'start' => sprintf('%04d-%02d-01', $year, $month),
        'end' => sprintf('%04d-%02d-%d', $year, $month, date('t', mktime(0, 0, 0, $month, 1, $year)))
    ];
}
?>

