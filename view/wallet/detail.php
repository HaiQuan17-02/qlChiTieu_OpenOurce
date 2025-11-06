<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/wallet.php';
require_once __DIR__ . '/../../function/transaction.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Chi tiết ví';

// Check login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Check PIN authentication (valid for 30 minutes)
if (!isset($_SESSION['wallet_authenticated']) || !$_SESSION['wallet_authenticated']) {
    setFlashMessage('error', 'Vui lòng xác thực mã PIN');
    redirect(SITE_URL . '/dashboard.php');
}

// Check if authentication is still valid (30 minutes)
if (isset($_SESSION['wallet_auth_time']) && (time() - $_SESSION['wallet_auth_time']) > 1800) {
    unset($_SESSION['wallet_authenticated']);
    unset($_SESSION['wallet_auth_time']);
    setFlashMessage('error', 'Phiên xác thực đã hết hạn');
    redirect(SITE_URL . '/dashboard.php');
}

// Get wallet ID
$walletId = $_GET['id'] ?? null;
if (!$walletId) {
    setFlashMessage('error', 'Không tìm thấy ví');
    redirect(SITE_URL . '/dashboard.php');
}

// Get wallet info
$wallet = getWalletById($walletId);
if (!$wallet || $wallet['user_id'] != getCurrentUserId()) {
    setFlashMessage('error', 'Không có quyền truy cập ví này');
    redirect(SITE_URL . '/dashboard.php');
}

// Get transactions for this wallet
$transactions = getUserTransactions($walletId);
$balance = calculateWalletBalance($walletId);
?>

<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-wallet2"></i> <?= e($wallet['wallet_name']) ?>
                    </h1>
                    <p class="text-muted mb-0">Số dư: <strong class="h4"><?= formatCurrency($balance, $wallet['currency']) ?></strong></p>
                </div>
                <a href="<?= SITE_URL ?>/dashboard.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
            
            <?= displayFlashMessage() ?>
            
            <!-- Transactions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Giao dịch
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3">Chưa có giao dịch nào</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Danh mục</th>
                                        <th>Loại</th>
                                        <th>Ghi chú</th>
                                        <th class="text-end">Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $t): ?>
                                        <tr>
                                            <td><?= formatDate($t['transaction_date']) ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= $t['category_icon'] ?> <?= e($t['category_name']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $t['type'] == 'income' ? 'success' : 'danger' ?>">
                                                    <?= $t['type'] == 'income' ? 'Thu nhập' : 'Chi tiêu' ?>
                                                </span>
                                            </td>
                                            <td><?= e($t['note']) ?></td>
                                            <td class="text-end">
                                                <span class="<?= $t['type'] == 'income' ? 'text-success' : 'text-danger' ?>">
                                                    <?= $t['type'] == 'income' ? '+' : '-' ?>
                                                    <?= formatCurrency($t['amount'], $wallet['currency']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

