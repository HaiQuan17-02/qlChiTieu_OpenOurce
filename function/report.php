<?php
// report.php - Tính tổng thu/chi, thống kê theo tháng
require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/helper.php';

// Get tổng thu nhập
function getTotalIncome($userId = null, $startDate = null, $endDate = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return 0;
    }
    
    try {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'income'";
        $params = [$userId];
        
        if ($startDate) {
            $sql .= " AND transaction_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND transaction_date <= ?";
            $params[] = $endDate;
        }
        
        $result = queryOne($sql, $params);
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Get tổng chi tiêu
function getTotalExpense($userId = null, $startDate = null, $endDate = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return 0;
    }
    
    try {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND type = 'expense'";
        $params = [$userId];
        
        if ($startDate) {
            $sql .= " AND transaction_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND transaction_date <= ?";
            $params[] = $endDate;
        }
        
        $result = queryOne($sql, $params);
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Get tổng thu chi theo tháng
function getMonthlySummary($year = null, $month = null, $userId = null) {
    $userId = $userId ?? getCurrentUserId();
    $year = $year ?? date('Y');
    $month = $month ?? date('m');
    
    if (!$userId) {
        return ['income' => 0, 'expense' => 0, 'balance' => 0];
    }
    
    $dateRange = getMonthRange($year, $month);
    
    $income = getTotalIncome($userId, $dateRange['start'], $dateRange['end']);
    $expense = getTotalExpense($userId, $dateRange['start'], $dateRange['end']);
    $balance = $income - $expense;
    
    return [
        'income' => $income,
        'expense' => $expense,
        'balance' => $balance
    ];
}

// Get thống kê theo danh mục
function getExpenseByCategory($startDate = null, $endDate = null, $userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    try {
        $sql = "SELECT c.id, c.name, c.icon, c.color, COALESCE(SUM(t.amount), 0) as total 
                FROM categories c 
                LEFT JOIN transactions t ON c.id = t.category_id AND t.user_id = ? AND t.type = 'expense'";
        
        $params = [$userId];
        
        if ($startDate) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " WHERE c.type = 'expense' GROUP BY c.id, c.name ORDER BY total DESC";
        
        return queryAll($sql, $params);
    } catch (Exception $e) {
        return [];
    }
}

// Get thống kê thu nhập theo danh mục
function getIncomeByCategory($startDate = null, $endDate = null, $userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    try {
        $sql = "SELECT c.id, c.name, c.icon, c.color, COALESCE(SUM(t.amount), 0) as total 
                FROM categories c 
                LEFT JOIN transactions t ON c.id = t.category_id AND t.user_id = ? AND t.type = 'income'";
        
        $params = [$userId];
        
        if ($startDate) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " WHERE c.type = 'income' GROUP BY c.id, c.name ORDER BY total DESC";
        
        return queryAll($sql, $params);
    } catch (Exception $e) {
        return [];
    }
}

// Get thống kê 6 tháng gần nhất
function getLast6MonthsStats($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    $stats = [];
    
    for ($i = 5; $i >= 0; $i--) {
        $date = new DateTime();
        $date->modify("-$i months");
        $year = $date->format('Y');
        $month = $date->format('m');
        
        $summary = getMonthlySummary($year, $month, $userId);
        $stats[] = [
            'year' => $year,
            'month' => $month,
            'month_name' => $date->format('M Y'),
            'income' => $summary['income'],
            'expense' => $summary['expense'],
            'balance' => $summary['balance']
        ];
    }
    
    return $stats;
}

// Get thống kê theo tuần
function getWeeklyStats($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return ['income' => 0, 'expense' => 0];
    }
    
    $today = date('Y-m-d');
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    
    return [
        'income' => getTotalIncome($userId, $weekStart, $today),
        'expense' => getTotalExpense($userId, $weekStart, $today)
    ];
}

// Get thống kê theo năm
function getYearlyStats($year = null, $userId = null) {
    $userId = $userId ?? getCurrentUserId();
    $year = $year ?? date('Y');
    
    if (!$userId) {
        return ['income' => 0, 'expense' => 0];
    }
    
    $yearStart = "$year-01-01";
    $yearEnd = "$year-12-31";
    
    return [
        'income' => getTotalIncome($userId, $yearStart, $yearEnd),
        'expense' => getTotalExpense($userId, $yearStart, $yearEnd)
    ];
}

// Get thống kê xu hướng chi tiêu 6-12 tháng
function getExpenseTrend($months = 12, $categoryId = null, $userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    $stats = [];
    
    for ($i = $months - 1; $i >= 0; $i--) {
        $date = new DateTime();
        $date->modify("-$i months");
        $year = $date->format('Y');
        $month = $date->format('m');
        
        $dateRange = getMonthRange($year, $month);
        
        if ($categoryId) {
            // Chi tiêu theo danh mục cụ thể
            try {
                $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions 
                        WHERE user_id = ? AND type = 'expense' AND category_id = ?
                        AND transaction_date >= ? AND transaction_date <= ?";
                $result = queryOne($sql, [$userId, $categoryId, $dateRange['start'], $dateRange['end']]);
                $expense = $result['total'] ?? 0;
            } catch (Exception $e) {
                $expense = 0;
            }
        } else {
            // Tổng chi tiêu
            $expense = getTotalExpense($userId, $dateRange['start'], $dateRange['end']);
        }
        
        $stats[] = [
            'year' => $year,
            'month' => $month,
            'month_name' => $date->format('M Y'),
            'expense' => $expense
        ];
    }
    
    return $stats;
}

// So sánh tháng này với tháng trước
function getMonthComparison($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return null;
    }
    
    // Tháng hiện tại
    $currentMonth = date('m');
    $currentYear = date('Y');
    $currentDateRange = getMonthRange($currentYear, $currentMonth);
    $currentExpense = getTotalExpense($userId, $currentDateRange['start'], $currentDateRange['end']);
    $currentIncome = getTotalIncome($userId, $currentDateRange['start'], $currentDateRange['end']);
    
    // Tháng trước
    $lastMonthDate = new DateTime();
    $lastMonthDate->modify('-1 month');
    $lastMonth = $lastMonthDate->format('m');
    $lastYear = $lastMonthDate->format('Y');
    $lastDateRange = getMonthRange($lastYear, $lastMonth);
    $lastExpense = getTotalExpense($userId, $lastDateRange['start'], $lastDateRange['end']);
    $lastIncome = getTotalIncome($userId, $lastDateRange['start'], $lastDateRange['end']);
    
    // Tính phần trăm thay đổi
    $expenseChange = $lastExpense > 0 ? (($currentExpense - $lastExpense) / $lastExpense) * 100 : 0;
    $incomeChange = $lastIncome > 0 ? (($currentIncome - $lastIncome) / $lastIncome) * 100 : 0;
    
    return [
        'current' => [
            'expense' => $currentExpense,
            'income' => $currentIncome
        ],
        'last' => [
            'expense' => $lastExpense,
            'income' => $lastIncome
        ],
        'change' => [
            'expense' => round($expenseChange, 1),
            'income' => round($incomeChange, 1)
        ]
    ];
}

// Gợi ý tiết kiệm dựa trên phân tích chi tiêu
function getSavingsSuggestions($userId = null) {
    $userId = $userId ?? getCurrentUserId();
    
    if (!$userId) {
        return [];
    }
    
    $suggestions = [];
    
    // So sánh tháng này với tháng trước
    $comparison = getMonthComparison($userId);
    if ($comparison) {
        // Chi tiêu tăng quá nhiều
        if ($comparison['change']['expense'] > 20) {
            $suggestions[] = [
                'type' => 'warning',
                'icon' => '⚠️',
                'title' => 'Chi tiêu tăng đáng kể',
                'message' => "Bạn chi tiêu tháng này tăng " . abs($comparison['change']['expense']) . "% so với tháng trước. Hãy xem lại các khoản chi để tiết kiệm hơn."
            ];
        }
    }
    
    // Phân tích chi tiêu theo danh mục tháng này
    $currentMonth = date('m');
    $currentYear = date('Y');
    $dateRange = getMonthRange($currentYear, $currentMonth);
    $expenseByCategory = getExpenseByCategory($dateRange['start'], $dateRange['end'], $userId);
    
    $totalExpense = array_sum(array_column($expenseByCategory, 'total'));
    
    // Kiểm tra từng danh mục
    foreach ($expenseByCategory as $category) {
        if ($category['total'] > 0 && $totalExpense > 0) {
            $percentage = ($category['total'] / $totalExpense) * 100;
            
            // Cảnh báo nếu một danh mục chiếm quá nhiều
            if ($percentage > 40) {
                $suggestions[] = [
                    'type' => 'info',
                    'icon' => '💰',
                    'title' => 'Chi tiêu tập trung một danh mục',
                    'message' => "Bạn đang chi " . formatCurrency($category['total']) . " cho " . $category['name'] . " (" . round($percentage) . "% tổng chi). Cân nhắc phân bổ lại ngân sách."
                ];
            }
        }
    }
    
    // So sánh với tháng trước cho từng danh mục
    if ($comparison) {
        $lastMonthDate = new DateTime();
        $lastMonthDate->modify('-1 month');
        $lastMonth = $lastMonthDate->format('m');
        $lastYear = $lastMonthDate->format('Y');
        $lastDateRange = getMonthRange($lastYear, $lastMonth);
        $lastExpenseByCategory = getExpenseByCategory($lastDateRange['start'], $lastDateRange['end'], $userId);
        
        foreach ($expenseByCategory as $currentCat) {
            $lastCat = null;
            foreach ($lastExpenseByCategory as $cat) {
                if ($cat['id'] == $currentCat['id']) {
                    $lastCat = $cat;
                    break;
                }
            }
            
            if ($lastCat && $lastCat['total'] > 0) {
                $change = (($currentCat['total'] - $lastCat['total']) / $lastCat['total']) * 100;
                
                // Gợi ý nếu tăng hơn 30%
                if ($change > 30) {
                    $suggestions[] = [
                        'type' => 'suggestion',
                        'icon' => '💡',
                        'title' => 'Gợi ý cắt giảm',
                        'message' => "Bạn chi " . formatCurrency($currentCat['total']) . " cho " . $currentCat['name'] . ", tăng " . round($change) . "% so với tháng trước. Xem xét giảm chi tiêu ở mục này."
                    ];
                }
            }
        }
    }
    
    // Kiểm tra tiết kiệm (thu nhập - chi tiêu)
    if ($comparison) {
        $savings = $comparison['current']['income'] - $comparison['current']['expense'];
        $savingsRate = $comparison['current']['income'] > 0 ? ($savings / $comparison['current']['income']) * 100 : 0;
        
        if ($savingsRate < 10 && $savingsRate >= 0) {
            $suggestions[] = [
                'type' => 'warning',
                'icon' => '🏦',
                'title' => 'Tỷ lệ tiết kiệm thấp',
                'message' => "Bạn đang tiết kiệm " . round($savingsRate) . "% thu nhập. Nên tiết kiệm ít nhất 10-20% để có tài chính tốt hơn."
            ];
        } elseif ($savings < 0) {
            $suggestions[] = [
                'type' => 'danger',
                'icon' => '🚨',
                'title' => 'Chi tiêu vượt quá thu nhập',
                'message' => "Bạn đang chi tiêu nhiều hơn thu nhập " . formatCurrency(abs($savings)) . ". Cần cắt giảm chi tiêu ngay."
            ];
        }
    }
    
    return $suggestions;
}
?>

