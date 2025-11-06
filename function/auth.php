<?php
// auth.php - Đăng nhập, đăng ký, xác thực người dùng
require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/helper.php';

// Đăng nhập
function login($username, $password) {
    try {
        // Tìm user theo username hoặc email
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $user = queryOne($sql, [$username, $username]);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Tên đăng nhập hoặc email không tồn tại'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Mật khẩu không chính xác'];
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['fullname'] = $user['fullname'];
        
        return ['success' => true, 'message' => 'Đăng nhập thành công'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Đăng ký
function register($username, $email, $password, $fullname, $pinCode = null) {
    try {
        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($fullname)) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }
        
        if (!isValidEmail($email)) {
            return ['success' => false, 'message' => 'Email không hợp lệ'];
        }
        
        if (!isValidPassword($password)) {
            return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
        }
        
        // Check existing user
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $existing = queryOne($sql, [$username, $email]);
        
        if ($existing) {
            return ['success' => false, 'message' => 'Username hoặc email đã tồn tại'];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user (without PIN for now)
        $sql = "INSERT INTO users (username, email, password, fullname) VALUES (?, ?, ?, ?)";
        $result = execute($sql, [$username, $email, $hashedPassword, $fullname]);
        
        if ($result['success']) {
            // Tạo ví mặc định (normal wallet)
            $sqlWallet = "INSERT INTO wallets (user_id, wallet_name, wallet_type, balance) VALUES (?, ?, 'normal', ?)";
            execute($sqlWallet, [$result['insert_id'], 'Ví chính', 0]);
            
            // Auto login after registration
            $_SESSION['user_id'] = $result['insert_id'];
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['fullname'] = $fullname;
            
            return ['success' => true, 'message' => 'Đăng ký thành công', 'user_id' => $result['insert_id']];
        } else {
            return ['success' => false, 'message' => 'Đăng ký thất bại'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Đăng xuất
function logout() {
    session_unset();
    session_destroy();
    redirect(SITE_URL . '/view/auth/login.php');
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $sql = "SELECT id, username, email, fullname, created_at FROM users WHERE id = ?";
        return queryOne($sql, [getCurrentUserId()]);
    } catch (Exception $e) {
        return null;
    }
}

// Check if user exists
function userExists($username, $email) {
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $user = queryOne($sql, [$username, $email]);
    return $user !== null;
}

// Set PIN code for user
function setPinCode($pinCode, $userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Chưa đăng nhập'];
    }
    
    // Validate PIN code
    if (!preg_match('/^[0-9]{6}$/', $pinCode)) {
        return ['success' => false, 'message' => 'Mã PIN phải là 6 số'];
    }
    
    try {
        // Hash PIN code
        $hashedPinCode = password_hash($pinCode, PASSWORD_DEFAULT);
        
        // Update PIN
        $sql = "UPDATE users SET pin_code = ? WHERE id = ?";
        $result = execute($sql, [$hashedPinCode, $userId]);
        
        if ($result['success']) {
            return ['success' => true, 'message' => 'Thiết lập mã PIN thành công'];
        } else {
            return ['success' => false, 'message' => 'Thiết lập mã PIN thất bại'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Check if user has PIN code
function hasPinCode($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return false;
    }
    
    try {
        $sql = "SELECT pin_code FROM users WHERE id = ?";
        $user = queryOne($sql, [$userId]);
        return !empty($user['pin_code']);
    } catch (Exception $e) {
        return false;
    }
}
?>

