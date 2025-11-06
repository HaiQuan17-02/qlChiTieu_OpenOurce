<?php
// transaction_handler.php - Xử lý CRUD giao dịch
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function/transaction.php';
require_once __DIR__ . '/../function/helper.php';

if (!isLoggedIn()) {
    setFlashMessage('error', 'Vui lòng đăng nhập');
    redirect(SITE_URL . '/view/auth/login.php');
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $walletId = $_POST['wallet_id'] ?? '';
            $categoryId = $_POST['category_id'] ?? '';
            $type = $_POST['type'] ?? '';
            $amount = $_POST['amount'] ?? '';
            $note = sanitizeInput($_POST['note'] ?? '');
            $transactionDate = $_POST['transaction_date'] ?? date('Y-m-d');
            
            $result = addTransaction($walletId, $categoryId, $type, $amount, $note, $transactionDate);
            setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            redirect(SITE_URL . '/transaction.php');
        }
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transactionId = $_POST['transaction_id'] ?? '';
            $walletId = $_POST['wallet_id'] ?? '';
            $categoryId = $_POST['category_id'] ?? '';
            $type = $_POST['type'] ?? '';
            $amount = $_POST['amount'] ?? '';
            $note = sanitizeInput($_POST['note'] ?? '');
            $transactionDate = $_POST['transaction_date'] ?? date('Y-m-d');
            
            $result = updateTransaction($transactionId, $walletId, $categoryId, $type, $amount, $note, $transactionDate);
            setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            redirect(SITE_URL . '/transaction.php');
        }
        break;
        
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transactionId = $_POST['transaction_id'] ?? '';
            $result = deleteTransaction($transactionId);
            setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            redirect(SITE_URL . '/transaction.php');
        }
        break;
        
    default:
        redirect(SITE_URL . '/transaction.php');
        break;
}
?>

