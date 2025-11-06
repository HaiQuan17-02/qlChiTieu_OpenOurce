<?php
// verify_pin_handler.php - Xác thực mã PIN
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function/auth.php';
require_once __DIR__ . '/../function/helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'] ?? '';
    
    // Validate PIN
    if (!preg_match('/^[0-9]{6}$/', $pin)) {
        echo json_encode(['success' => false, 'message' => 'Mã PIN không hợp lệ']);
        exit;
    }
    
    // Get user's stored PIN
    try {
        $userId = getCurrentUserId();
        $sql = "SELECT pin_code FROM users WHERE id = ?";
        $user = queryOne($sql, [$userId]);
        
        if (!$user || !$user['pin_code']) {
            echo json_encode(['success' => false, 'message' => 'Chưa thiết lập mã PIN']);
            exit;
        }
        
        // Verify PIN
        if (password_verify($pin, $user['pin_code'])) {
            // Set session flag for wallet access
            $_SESSION['wallet_authenticated'] = true;
            $_SESSION['wallet_auth_time'] = time();
            
            echo json_encode(['success' => true, 'message' => 'Xác thực thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mã PIN không chính xác']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>

