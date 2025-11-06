<?php
// savings_handler.php - Xử lý tiết kiệm và chuyển tiền
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function/wallet.php';
require_once __DIR__ . '/../function/auth.php';
require_once __DIR__ . '/../function/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        // Create savings wallet
        $savingsName = sanitizeInput($_POST['savings_name'] ?? '');
        $targetAmount = $_POST['target_amount'] ?? 0;
        
        if (empty($savingsName)) {
            setFlashMessage('error', 'Vui lòng nhập tên mục tiêu');
            redirect(SITE_URL . '/wallets.php');
        }
        
        if ($targetAmount <= 0) {
            setFlashMessage('error', 'Số tiền mục tiêu phải lớn hơn 0');
            redirect(SITE_URL . '/wallets.php');
        }
        
        $result = createWallet($savingsName, 0, 'VND', 'savings', $targetAmount);
        
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
        } else {
            setFlashMessage('error', $result['message']);
        }
        
        redirect(SITE_URL . '/wallets.php');
        
    } elseif ($action === 'transfer') {
        // Transfer money between wallets
        if (!isset($_SESSION['wallet_authenticated']) || !$_SESSION['wallet_authenticated']) {
            setFlashMessage('error', 'Phiên xác thực đã hết hạn. Vui lòng thử lại.');
            redirect(SITE_URL . '/wallets.php');
        }
        
        $fromWalletId = $_POST['from_wallet_id'] ?? 0;
        $toWalletId = $_POST['to_wallet_id'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        $note = sanitizeInput($_POST['note'] ?? '');
        
        $result = transferBetweenWallets($fromWalletId, $toWalletId, $amount, $note);
        
        if ($result['success']) {
            // Unset authentication after transfer
            unset($_SESSION['wallet_authenticated']);
            unset($_SESSION['wallet_auth_time']);
            setFlashMessage('success', $result['message']);
        } else {
            setFlashMessage('error', $result['message']);
        }
        
        redirect(SITE_URL . '/wallets.php');
        
    } else {
        setFlashMessage('error', 'Hành động không hợp lệ');
        redirect(SITE_URL . '/wallets.php');
    }
} else {
    redirect(SITE_URL . '/wallets.php');
}
?>

