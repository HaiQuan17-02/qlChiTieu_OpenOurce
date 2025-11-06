<?php
// budget_handler.php - Xử lý CRUD ngân sách
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function/budget.php';
require_once __DIR__ . '/../function/helper.php';

if (!isLoggedIn()) {
    setFlashMessage('error', 'Vui lòng đăng nhập');
    redirect(SITE_URL . '/view/auth/login.php');
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoryId = $_POST['category_id'] ?? '';
            $amount = $_POST['amount'] ?? '';
            $period = $_POST['period'] ?? 'monthly';
            $startDate = $_POST['start_date'] ?? date('Y-m-d');
            $alertThreshold = $_POST['alert_threshold'] ?? 80;
            
            $result = createBudget($categoryId, $amount, $period, $startDate, $alertThreshold);
            setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            redirect(SITE_URL . '/budgets.php');
        }
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $budgetId = $_POST['budget_id'] ?? '';
            $amount = $_POST['amount'] ?? '';
            $period = $_POST['period'] ?? 'monthly';
            $startDate = $_POST['start_date'] ?? date('Y-m-d');
            $alertThreshold = $_POST['alert_threshold'] ?? 80;
            
            $result = updateBudget($budgetId, $amount, $period, $startDate, $alertThreshold);
            setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            redirect(SITE_URL . '/budgets.php');
        }
        break;
        
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $budgetId = $_POST['budget_id'] ?? '';
            $result = deleteBudget($budgetId);
            setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            redirect(SITE_URL . '/budgets.php');
        }
        break;
        
    default:
        redirect(SITE_URL . '/budgets.php');
        break;
}
?>

