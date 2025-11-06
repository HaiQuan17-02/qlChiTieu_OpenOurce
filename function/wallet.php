<?php
// wallet.php - Logic ví và tính số dư
require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/helper.php';

// Get all wallets của user
function getUserWallets($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    try {
        $sql = "SELECT * FROM wallets WHERE user_id = ? ORDER BY created_at DESC";
        return queryAll($sql, [$userId]);
    } catch (Exception $e) {
        return [];
    }
}

// Get wallet by ID
function getWalletById($walletId) {
    try {
        $sql = "SELECT * FROM wallets WHERE id = ?";
        return queryOne($sql, [$walletId]);
    } catch (Exception $e) {
        return null;
    }
}

// Tạo ví mới
function createWallet($walletName, $initialBalance = 0, $currency = 'VND', $walletType = 'normal', $targetAmount = null) {
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Chưa đăng nhập'];
    }
    
    try {
        $sql = "INSERT INTO wallets (user_id, wallet_name, wallet_type, target_amount, balance, currency) VALUES (?, ?, ?, ?, ?, ?)";
        $result = execute($sql, [$userId, $walletName, $walletType, $targetAmount, $initialBalance, $currency]);
        
        if ($result['success']) {
            return ['success' => true, 'message' => 'Tạo ví thành công', 'wallet_id' => $result['insert_id']];
        }
        return ['success' => false, 'message' => 'Tạo ví thất bại'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Update số dư ví
function updateWalletBalance($walletId, $newBalance) {
    try {
        $sql = "UPDATE wallets SET balance = ? WHERE id = ?";
        $result = execute($sql, [$newBalance, $walletId]);
        return $result['success'];
    } catch (Exception $e) {
        return false;
    }
}

// Tính số dư ví dựa trên giao dịch và chuyển tiền
function calculateWalletBalance($walletId) {
    try {
        // Tính tổng thu nhập từ transactions
        $sqlIncome = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE wallet_id = ? AND type = 'income'";
        $income = queryOne($sqlIncome, [$walletId]);
        $totalIncome = $income['total'] ?? 0;
        
        // Tính tổng chi tiêu từ transactions
        $sqlExpense = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE wallet_id = ? AND type = 'expense'";
        $expense = queryOne($sqlExpense, [$walletId]);
        $totalExpense = $expense['total'] ?? 0;
        
        // Tính tổng tiền nhận được (to_wallet)
        $sqlReceived = "SELECT COALESCE(SUM(amount), 0) as total FROM wallet_transfers WHERE to_wallet_id = ?";
        $received = queryOne($sqlReceived, [$walletId]);
        $totalReceived = $received['total'] ?? 0;
        
        // Tính tổng tiền đã chuyển đi (from_wallet)
        $sqlSent = "SELECT COALESCE(SUM(amount), 0) as total FROM wallet_transfers WHERE from_wallet_id = ?";
        $sent = queryOne($sqlSent, [$walletId]);
        $totalSent = $sent['total'] ?? 0;
        
        // Số dư = tổng thu - tổng chi + tiền nhận - tiền chuyển
        $balance = ($totalIncome - $totalExpense) + ($totalReceived - $totalSent);
        
        // Update vào database
        updateWalletBalance($walletId, $balance);
        
        return $balance;
    } catch (Exception $e) {
        return 0;
    }
}

// Get tổng số dư của tất cả ví
function getTotalBalance($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return 0;
    }
    
    try {
        $wallets = getUserWallets($userId);
        $totalBalance = 0;
        
        foreach ($wallets as $wallet) {
            $totalBalance += calculateWalletBalance($wallet['id']);
        }
        
        return $totalBalance;
    } catch (Exception $e) {
        return 0;
    }
}

// Xóa ví
function deleteWallet($walletId) {
    try {
        // Check if user owns this wallet
        $wallet = getWalletById($walletId);
        if (!$wallet || $wallet['user_id'] != getCurrentUserId()) {
            return ['success' => false, 'message' => 'Không có quyền xóa ví này'];
        }
        
        $sql = "DELETE FROM wallets WHERE id = ?";
        $result = execute($sql, [$walletId]);
        
        if ($result['success']) {
            return ['success' => true, 'message' => 'Xóa ví thành công'];
        }
        return ['success' => false, 'message' => 'Xóa ví thất bại'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Get savings wallets only
function getSavingsWallets($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    try {
        $sql = "SELECT * FROM wallets WHERE user_id = ? AND wallet_type = 'savings' ORDER BY created_at DESC";
        return queryAll($sql, [$userId]);
    } catch (Exception $e) {
        return [];
    }
}

// Get normal wallets only
function getNormalWallets($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    try {
        $sql = "SELECT * FROM wallets WHERE user_id = ? AND wallet_type = 'normal' ORDER BY created_at DESC";
        return queryAll($sql, [$userId]);
    } catch (Exception $e) {
        return [];
    }
}

// Chuyển tiền giữa các ví
function transferBetweenWallets($fromWalletId, $toWalletId, $amount, $note = '', $userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Chưa đăng nhập'];
    }
    
    if ($amount <= 0) {
        return ['success' => false, 'message' => 'Số tiền phải lớn hơn 0'];
    }
    
    if ($fromWalletId == $toWalletId) {
        return ['success' => false, 'message' => 'Không thể chuyển tiền vào cùng một ví'];
    }
    
    try {
        // Check wallets exist and belong to user
        $fromWallet = getWalletById($fromWalletId);
        $toWallet = getWalletById($toWalletId);
        
        if (!$fromWallet || $fromWallet['user_id'] != $userId) {
            return ['success' => false, 'message' => 'Ví nguồn không hợp lệ'];
        }
        
        if (!$toWallet || $toWallet['user_id'] != $userId) {
            return ['success' => false, 'message' => 'Ví đích không hợp lệ'];
        }
        
        // Calculate actual balances from transactions and transfers
        $fromBalance = calculateWalletBalance($fromWalletId);
        if ($fromBalance < $amount) {
            return ['success' => false, 'message' => 'Số dư không đủ'];
        }
        
        // Save transfer record
        $sqlTransfer = "INSERT INTO wallet_transfers (user_id, from_wallet_id, to_wallet_id, amount, note) VALUES (?, ?, ?, ?, ?)";
        $result = execute($sqlTransfer, [$userId, $fromWalletId, $toWalletId, $amount, $note]);
        
        if (!$result['success']) {
            return ['success' => false, 'message' => 'Không thể lưu thông tin chuyển tiền'];
        }
        
        // Recalculate balances for both wallets
        calculateWalletBalance($fromWalletId);
        calculateWalletBalance($toWalletId);
        
        return ['success' => true, 'message' => 'Chuyển tiền thành công'];
        
    } catch (Exception $e) {
        error_log("Transfer error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Get transfer history
function getWalletTransfers($walletId = null, $userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    try {
        if ($walletId) {
            $sql = "SELECT wt.*, 
                    fw.wallet_name as from_wallet_name, 
                    tw.wallet_name as to_wallet_name
                    FROM wallet_transfers wt
                    LEFT JOIN wallets fw ON wt.from_wallet_id = fw.id
                    LEFT JOIN wallets tw ON wt.to_wallet_id = tw.id
                    WHERE wt.user_id = ? AND (wt.from_wallet_id = ? OR wt.to_wallet_id = ?)
                    ORDER BY wt.transfer_date DESC";
            return queryAll($sql, [$userId, $walletId, $walletId]);
        } else {
            $sql = "SELECT wt.*, 
                    fw.wallet_name as from_wallet_name, 
                    tw.wallet_name as to_wallet_name
                    FROM wallet_transfers wt
                    LEFT JOIN wallets fw ON wt.from_wallet_id = fw.id
                    LEFT JOIN wallets tw ON wt.to_wallet_id = tw.id
                    WHERE wt.user_id = ?
                    ORDER BY wt.transfer_date DESC";
            return queryAll($sql, [$userId]);
        }
    } catch (Exception $e) {
        return [];
    }
}
?>

