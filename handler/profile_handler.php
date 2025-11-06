<?php
// profile_handler.php - Xử lý cập nhật profile
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function/auth.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = getCurrentUserId();
    
    if (!$userId) {
        setFlashMessage('error', 'Chưa đăng nhập');
        redirect(SITE_URL . '/profile.php');
    }
    
    if ($action === 'update_info') {
        // Update profile info
        $fullname = sanitizeInput($_POST['fullname'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        
        if (empty($fullname) || empty($email)) {
            setFlashMessage('error', 'Vui lòng điền đầy đủ thông tin');
            redirect(SITE_URL . '/profile.php');
        }
        
        // Check email exists
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $existing = queryOne($sql, [$email, $userId]);
        
        if ($existing) {
            setFlashMessage('error', 'Email đã được sử dụng bởi người khác');
            redirect(SITE_URL . '/profile.php');
        }
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('error', 'Email không hợp lệ');
            redirect(SITE_URL . '/profile.php');
        }
        
        // Update
        $sql = "UPDATE users SET fullname = ?, email = ? WHERE id = ?";
        $result = execute($sql, [$fullname, $email, $userId]);
        
        if ($result['success']) {
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            setFlashMessage('success', 'Cập nhật thông tin thành công');
        } else {
            setFlashMessage('error', 'Cập nhật thông tin thất bại');
        }
        
        redirect(SITE_URL . '/profile.php');
        
    } elseif ($action === 'change_password') {
        // Change password
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_new_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            setFlashMessage('error', 'Vui lòng điền đầy đủ thông tin');
            redirect(SITE_URL . '/profile.php');
        }
        
        if ($newPassword !== $confirmPassword) {
            setFlashMessage('error', 'Mật khẩu xác nhận không khớp');
            redirect(SITE_URL . '/profile.php');
        }
        
        if (strlen($newPassword) < 6) {
            setFlashMessage('error', 'Mật khẩu phải có ít nhất 6 ký tự');
            redirect(SITE_URL . '/profile.php');
        }
        
        // Get current password
        $sql = "SELECT password FROM users WHERE id = ?";
        $user = queryOne($sql, [$userId]);
        
        if (!password_verify($currentPassword, $user['password'])) {
            setFlashMessage('error', 'Mật khẩu hiện tại không đúng');
            redirect(SITE_URL . '/profile.php');
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $result = execute($sql, [$hashedPassword, $userId]);
        
        if ($result['success']) {
            setFlashMessage('success', 'Đổi mật khẩu thành công');
        } else {
            setFlashMessage('error', 'Đổi mật khẩu thất bại');
        }
        
        redirect(SITE_URL . '/profile.php');
        
    } elseif ($action === 'update_security') {
        // Update security question
        $securityQuestion = sanitizeInput($_POST['security_question'] ?? '');
        $securityAnswer = sanitizeInput($_POST['security_answer'] ?? '');
        
        if (empty($securityQuestion) || empty($securityAnswer)) {
            setFlashMessage('error', 'Vui lòng điền đầy đủ thông tin');
            redirect(SITE_URL . '/profile.php');
        }
        
        // Hash answer
        $hashedAnswer = password_hash($securityAnswer, PASSWORD_DEFAULT);
        
        // Update
        $sql = "UPDATE users SET security_question = ?, security_answer = ? WHERE id = ?";
        $result = execute($sql, [$securityQuestion, $hashedAnswer, $userId]);
        
        if ($result['success']) {
            setFlashMessage('success', 'Lưu câu hỏi bảo mật thành công');
        } else {
            setFlashMessage('error', 'Lưu câu hỏi bảo mật thất bại');
        }
        
        redirect(SITE_URL . '/profile.php');
        
    } elseif ($action === 'update_avatar') {
        // Upload avatar - return JSON for AJAX
        header('Content-Type: application/json');
        
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            
            // Validate file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file JPG, PNG, GIF, WEBP']);
                exit;
            }
            
            if ($file['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'Kích thước file tối đa là 5MB']);
                exit;
            }
            
            // Create upload directory
            $uploadDir = __DIR__ . '/../uploads/avatars/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old avatar if exists
                $sql = "SELECT avatar FROM users WHERE id = ?";
                $user = queryOne($sql, [$userId]);
                
                if (!empty($user['avatar'])) {
                    $oldAvatar = __DIR__ . '/../' . $user['avatar'];
                    if (file_exists($oldAvatar)) {
                        unlink($oldAvatar);
                    }
                }
                
                // Save to database
                $avatarPath = 'uploads/avatars/' . $filename;
                $sql = "UPDATE users SET avatar = ? WHERE id = ?";
                $result = execute($sql, [$avatarPath, $userId]);
                
                if ($result['success']) {
                    setFlashMessage('success', 'Cập nhật ảnh đại diện thành công');
                    echo json_encode(['success' => true, 'message' => 'Cập nhật ảnh đại diện thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Cập nhật ảnh đại diện thất bại']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload ảnh thất bại. Kiểm tra quyền thư mục.']);
            }
        } else {
            $errorMsg = 'Vui lòng chọn file ảnh';
            if (isset($_FILES['avatar'])) {
                switch($_FILES['avatar']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMsg = 'File quá lớn';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMsg = 'Upload không hoàn tất';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errorMsg = 'Không có file được chọn';
                        break;
                }
            }
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        }
        exit;
    }
    
} else {
    redirect(SITE_URL . '/profile.php');
}
?>

