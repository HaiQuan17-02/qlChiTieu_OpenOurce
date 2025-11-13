<?php
// transaction.php - Logic xử lý giao dịch
require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/wallet.php';
require_once __DIR__ . '/helper.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/auth.php';

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
                w.wallet_name, w.currency as wallet_currency 
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
            $transactionId = $result['insert_id'] ?? null;

            // Cập nhật số dư ví
            calculateWalletBalance($walletId);

            if ($transactionId) {
                notifyTransactionCreated($transactionId);
            }
            
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

// Gửi email thông báo khi có giao dịch mới
function notifyTransactionCreated($transactionId) {
    if (empty($transactionId)) {
        return;
    }

    $transaction = getTransactionById($transactionId);
    if (!$transaction) {
        return;
    }

    $user = getUserById($transaction['user_id']);
    if (!$user || empty($user['email']) || !isValidEmail($user['email'])) {
        return;
    }

    $wallet = getWalletById($transaction['wallet_id']);
    $currency = $wallet['currency'] ?? $transaction['wallet_currency'] ?? 'VND';

    $amountFormatted = formatCurrency($transaction['amount'], $currency);
    $dateFormatted = formatDate($transaction['transaction_date'], 'd/m/Y');
    $createdAtFormatted = formatDateTime($transaction['created_at'] ?? date('Y-m-d H:i:s'), 'd/m/Y H:i');
    $typeLabel = $transaction['type'] === 'income' ? 'thu nhập' : 'chi tiêu';
    $categoryName = $transaction['category_name'] ?? 'Không xác định';
    $walletName = $wallet['wallet_name'] ?? $transaction['wallet_name'] ?? 'Ví';
    $note = $transaction['note'] ?? '';

    $subject = sprintf('[%s] Giao dịch %s mới', SITE_NAME, $typeLabel);

    $body = '<h2>' . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . '</h2>';
    $body .= '<p>Chào ' . htmlspecialchars($user['fullname'] ?? $user['username'], ENT_QUOTES, 'UTF-8') . ',</p>';
    $body .= '<p>Bạn vừa ghi nhận một giao dịch ' . htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') . ' mới.</p>';
    $body .= '<table style="border-collapse: collapse; width: 100%;">';
    $body .= '<tr><td style="padding: 6px; border: 1px solid #ddd;">Số tiền</td><td style="padding: 6px; border: 1px solid #ddd;"><strong>' . htmlspecialchars($amountFormatted, ENT_QUOTES, 'UTF-8') . '</strong></td></tr>';
    $body .= '<tr><td style="padding: 6px; border: 1px solid #ddd;">Danh mục</td><td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') . '</td></tr>';
    $body .= '<tr><td style="padding: 6px; border: 1px solid #ddd;">Ví</td><td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($walletName, ENT_QUOTES, 'UTF-8') . '</td></tr>';
    $body .= '<tr><td style="padding: 6px; border: 1px solid #ddd;">Ngày giao dịch</td><td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($dateFormatted, ENT_QUOTES, 'UTF-8') . '</td></tr>';
    $body .= '<tr><td style="padding: 6px; border: 1px solid #ddd;">Thời điểm ghi</td><td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($createdAtFormatted, ENT_QUOTES, 'UTF-8') . '</td></tr>';
    if (!empty($note)) {
        $body .= '<tr><td style="padding: 6px; border: 1px solid #ddd;">Ghi chú</td><td style="padding: 6px; border: 1px solid #ddd;">' . nl2br(htmlspecialchars($note, ENT_QUOTES, 'UTF-8')) . '</td></tr>';
    }
    $body .= '</table>';
    $body .= '<p>Bạn có thể xem chi tiết tại ứng dụng: <a href="' . SITE_URL . '/transaction.php">Quản lý giao dịch</a></p>';
    $body .= '<p>Cảm ơn bạn đã sử dụng ' . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . '.</p>';

    sendEmail($user['email'], $subject, $body, ['is_html' => true]);
}
?>

