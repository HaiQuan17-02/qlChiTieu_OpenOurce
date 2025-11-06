<?php
// transaction.php - Logic xử lý giao dịch
require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/wallet.php';
require_once __DIR__ . '/helper.php';

// Get all transactions của user
function getUserTransactions($userId = null, $walletId = null, $startDate = null, $endDate = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    try {
        $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color, 
                w.wallet_name 
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                JOIN wallets w ON t.wallet_id = w.id 
                WHERE t.user_id = ?";
        
        $params = [$userId];
        
        if ($walletId) {
            $sql .= " AND t.wallet_id = ?";
            $params[] = $walletId;
        }
        
        if ($startDate) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY t.transaction_date DESC, t.created_at DESC";
        
        return queryAll($sql, $params);
    } catch (Exception $e) {
        return [];
    }
}

// Get transaction by ID
function getTransactionById($transactionId) {
    try {
        $sql = "SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color, 
                w.wallet_name 
                FROM transactions t 
                JOIN categories c ON t.category_id = c.id 
                JOIN wallets w ON t.wallet_id = w.id 
                WHERE t.id = ?";
        return queryOne($sql, [$transactionId]);
    } catch (Exception $e) {
        return null;
    }
}

// Thêm giao dịch
function addTransaction($walletId, $categoryId, $type, $amount, $note, $transactionDate) {
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Chưa đăng nhập'];
    }
    
    // Validate
    if (empty($walletId) || empty($categoryId) || empty($type) || empty($amount) || empty($transactionDate)) {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
    }
    
    // Check wallet ownership
    $wallet = getWalletById($walletId);
    if (!$wallet || $wallet['user_id'] != $userId) {
        return ['success' => false, 'message' => 'Ví không hợp lệ'];
    }
    
    try {
        $sql = "INSERT INTO transactions (user_id, wallet_id, category_id, type, amount, note, transaction_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $result = execute($sql, [$userId, $walletId, $categoryId, $type, $amount, $note, $transactionDate]);
        
        if ($result['success']) {
            // Cập nhật số dư ví
            calculateWalletBalance($walletId);
            
            return ['success' => true, 'message' => 'Thêm giao dịch thành công'];
        }
        return ['success' => false, 'message' => 'Thêm giao dịch thất bại'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Sửa giao dịch
function updateTransaction($transactionId, $walletId, $categoryId, $type, $amount, $note, $transactionDate) {
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Chưa đăng nhập'];
    }
    
    // Check ownership
    $transaction = getTransactionById($transactionId);
    if (!$transaction || $transaction['user_id'] != $userId) {
        return ['success' => false, 'message' => 'Giao dịch không tồn tại hoặc không có quyền'];
    }
    
    $oldWalletId = $transaction['wallet_id'];
    
    try {
        $sql = "UPDATE transactions SET wallet_id = ?, category_id = ?, type = ?, amount = ?, note = ?, transaction_date = ? 
                WHERE id = ?";
        $result = execute($sql, [$walletId, $categoryId, $type, $amount, $note, $transactionDate, $transactionId]);
        
        if ($result['success']) {
            // Recalculate balances
            calculateWalletBalance($oldWalletId);
            if ($oldWalletId != $walletId) {
                calculateWalletBalance($walletId);
            }
            
            return ['success' => true, 'message' => 'Cập nhật giao dịch thành công'];
        }
        return ['success' => false, 'message' => 'Cập nhật giao dịch thất bại'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Xóa giao dịch
function deleteTransaction($transactionId) {
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Chưa đăng nhập'];
    }
    
    // Check ownership
    $transaction = getTransactionById($transactionId);
    if (!$transaction || $transaction['user_id'] != $userId) {
        return ['success' => false, 'message' => 'Giao dịch không tồn tại hoặc không có quyền'];
    }
    
    $walletId = $transaction['wallet_id'];
    
    try {
        $sql = "DELETE FROM transactions WHERE id = ?";
        $result = execute($sql, [$transactionId]);
        
        if ($result['success']) {
            // Recalculate balance
            calculateWalletBalance($walletId);
            
            return ['success' => true, 'message' => 'Xóa giao dịch thành công'];
        }
        return ['success' => false, 'message' => 'Xóa giao dịch thất bại'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Get categories
function getCategories($type = null) {
    try {
        $sql = "SELECT * FROM categories WHERE 1=1";
        $params = [];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY type, name";
        
        return queryAll($sql, $params);
    } catch (Exception $e) {
        return [];
    }
}

// Get category by ID
function getCategoryById($categoryId) {
    try {
        $sql = "SELECT * FROM categories WHERE id = ?";
        return queryOne($sql, [$categoryId]);
    } catch (Exception $e) {
        return null;
    }
}
?>

