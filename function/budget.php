<?php
// budget.php - Logic xử lý ngân sách
require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/transaction.php';
require_once __DIR__ . '/helper.php';

// Get all budgets của user
function getUserBudgets($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    try {
        $sql = "SELECT b.*, c.name as category_name, c.icon as category_icon, c.color as category_color 
                FROM budgets b 
                JOIN categories c ON b.category_id = c.id 
                WHERE b.user_id = ? 
                ORDER BY b.created_at DESC";
        return queryAll($sql, [$userId]);
    } catch (Exception $e) {
        return [];
    }
}

// Get budget by ID
function getBudgetById($budgetId) {
    try {
        $sql = "SELECT b.*, c.name as category_name, c.icon as category_icon, c.color as category_color 
                FROM budgets b 
                JOIN categories c ON b.category_id = c.id 
                WHERE b.id = ?";
        return queryOne($sql, [$budgetId]);
    } catch (Exception $e) {
        return null;
    }
}

// Tạo ngân sách mới
function createBudget($categoryId, $amount, $period, $startDate, $alertThreshold = 80) {
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Chưa đăng nhập'];
    }
    
    // Validate
    if (empty($categoryId) || empty($amount) || empty($period) || empty($startDate)) {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
    }
    
    // Calculate end date
    $endDate = calculateEndDate($startDate, $period);
    
    try {
        $sql = "INSERT INTO budgets (user_id, category_id, amount, period, start_date, end_date, alert_threshold) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $result = execute($sql, [$userId, $categoryId, $amount, $period, $startDate, $endDate, $alertThreshold]);
        
        if ($result['success']) {
            return ['success' => true, 'message' => 'Tạo ngân sách thành công'];
        }
        return ['success' => false, 'message' => 'Tạo ngân sách thất bại'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Update ngân sách
function updateBudget($budgetId, $amount, $period, $startDate, $alertThreshold = 80) {
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Chưa đăng nhập'];
    }
    
    // Check ownership
    $budget = getBudgetById($budgetId);
    if (!$budget || $budget['user_id'] != $userId) {
        return ['success' => false, 'message' => 'Ngân sách không tồn tại hoặc không có quyền'];
    }
    
    // Calculate end date
    $endDate = calculateEndDate($startDate, $period);
    
    try {
        $sql = "UPDATE budgets SET amount = ?, period = ?, start_date = ?, end_date = ?, alert_threshold = ? 
                WHERE id = ?";
        $result = execute($sql, [$amount, $period, $startDate, $endDate, $alertThreshold, $budgetId]);
        
        if ($result['success']) {
            return ['success' => true, 'message' => 'Cập nhật ngân sách thành công'];
        }
        return ['success' => false, 'message' => 'Cập nhật ngân sách thất bại'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Xóa ngân sách
function deleteBudget($budgetId) {
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Chưa đăng nhập'];
    }
    
    // Check ownership
    $budget = getBudgetById($budgetId);
    if (!$budget || $budget['user_id'] != $userId) {
        return ['success' => false, 'message' => 'Ngân sách không tồn tại hoặc không có quyền'];
    }
    
    try {
        $sql = "DELETE FROM budgets WHERE id = ?";
        $result = execute($sql, [$budgetId]);
        
        if ($result['success']) {
            return ['success' => true, 'message' => 'Xóa ngân sách thành công'];
        }
        return ['success' => false, 'message' => 'Xóa ngân sách thất bại'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Tính ngày kết thúc theo period
function calculateEndDate($startDate, $period) {
    $start = new DateTime($startDate);
    
    switch ($period) {
        case 'weekly':
            $start->modify('+6 days');
            break;
        case 'monthly':
            $start->modify('last day of this month');
            break;
        case 'yearly':
            $start->modify('last day of December this year');
            break;
    }
    
    return $start->format('Y-m-d');
}

// Get actual spending for a budget
function getActualSpending($budgetId, $userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return 0;
    }
    
    try {
        $budget = getBudgetById($budgetId);
        if (!$budget) return 0;
        
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM transactions 
                WHERE user_id = ? 
                AND category_id = ? 
                AND type = 'expense'
                AND transaction_date >= ? 
                AND transaction_date <= ?";
        
        $result = queryOne($sql, [
            $userId,
            $budget['category_id'],
            $budget['start_date'],
            $budget['end_date']
        ]);
        
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Check budget alerts
function getBudgetAlerts($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    $budgets = getUserBudgets($userId);
    $alerts = [];
    
    foreach ($budgets as $budget) {
        $actual = getActualSpending($budget['id'], $userId);
        $budgetAmount = $budget['amount'];
        $percentage = ($budgetAmount > 0) ? ($actual / $budgetAmount) * 100 : 0;
        $threshold = $budget['alert_threshold'];
        
        if ($percentage >= 100) {
            $alerts[] = [
                'budget' => $budget,
                'type' => 'danger',
                'message' => "Đã vượt quá ngân sách {$budget['category_name']}"
            ];
        } elseif ($percentage >= $threshold) {
            $alerts[] = [
                'budget' => $budget,
                'type' => 'warning',
                'message' => "Sắp hết ngân sách {$budget['category_name']} ({$percentage}%)"
            ];
        }
    }
    
    return $alerts;
}
?>

