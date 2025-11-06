<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/wallet.php';
require_once __DIR__ . '/../../function/transaction.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Giao dịch';

// Check login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Get parameters
$walletFilter = $_GET['wallet'] ?? 'all';
$typeFilter = $_GET['type'] ?? 'all';
$monthFilter = $_GET['month'] ?? date('Y-m');

// Get data
$wallets = getUserWallets();
$categoriesIncome = getCategories('income');
$categoriesExpense = getCategories('expense');

// Build date range
list($year, $month) = explode('-', $monthFilter);
$dateRange = getMonthRange($year, $month);

// Get transactions
$transactions = getUserTransactions(null, $walletFilter != 'all' ? $walletFilter : null, $dateRange['start'], $dateRange['end']);

// Filter by type
if ($typeFilter != 'all') {
    $transactions = array_filter($transactions, function($t) use ($typeFilter) {
        return $t['type'] == $typeFilter;
    });
}

// Get totals
$totalIncome = 0;
$totalExpense = 0;
foreach ($transactions as $t) {
    if ($t['type'] == 'income') {
        $totalIncome += $t['amount'];
    } else {
        $totalExpense += $t['amount'];
    }
}
?>

<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-list-ul"></i> Giao dịch
                </h1>
                <a href="<?= SITE_URL ?>/view/transaction/add.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Thêm giao dịch
                </a>
            </div>
            
            <?= displayFlashMessage() ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Ví</label>
                            <select name="wallet" class="form-select">
                                <option value="all" <?= $walletFilter == 'all' ? 'selected' : '' ?>>Tất cả ví</option>
                                <?php foreach ($wallets as $wallet): ?>
                                    <option value="<?= $wallet['id'] ?>" <?= $walletFilter == $wallet['id'] ? 'selected' : '' ?>>
                                        <?= e($wallet['wallet_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Loại</label>
                            <select name="type" class="form-select">
                                <option value="all" <?= $typeFilter == 'all' ? 'selected' : '' ?>>Tất cả</option>
                                <option value="income" <?= $typeFilter == 'income' ? 'selected' : '' ?>>Thu nhập</option>
                                <option value="expense" <?= $typeFilter == 'expense' ? 'selected' : '' ?>>Chi tiêu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tháng</label>
                            <input type="month" name="month" class="form-control" value="<?= $monthFilter ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Summary -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2">Tổng thu nhập</h6>
                            <h3 class="card-title"><?= formatCurrency($totalIncome) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2">Tổng chi tiêu</h6>
                            <h3 class="card-title"><?= formatCurrency($totalExpense) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Transactions List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list"></i> Danh sách giao dịch (<?= count($transactions) ?>)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center p-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3">Chưa có giao dịch nào trong tháng này</p>
                            <a href="<?= SITE_URL ?>/view/transaction/add.php" class="btn btn-primary">
                                Thêm giao dịch đầu tiên
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Danh mục</th>
                                        <th>Loại</th>
                                        <th>Ví</th>
                                        <th>Số tiền</th>
                                        <th>Ghi chú</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td><?= formatDate($transaction['transaction_date']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $transaction['category_color'] ?? 'secondary' ?>">
                                                    <?= $transaction['category_icon'] ?> <?= e($transaction['category_name']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($transaction['type'] == 'income'): ?>
                                                    <span class="badge bg-success">Thu nhập</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Chi tiêu</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= e($transaction['wallet_name']) ?></td>
                                            <td class="<?= $transaction['type'] == 'income' ? 'text-success' : 'text-danger' ?> fw-bold">
                                                <?= $transaction['type'] == 'income' ? '+' : '-' ?><?= formatCurrency($transaction['amount']) ?>
                                            </td>
                                            <td><?= e($transaction['note']) ?></td>
                                            <td>
                                                <a href="<?= SITE_URL ?>/view/transaction/add.php?edit=<?= $transaction['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $transaction['id'] ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="<?= SITE_URL ?>/handler/transaction_handler.php?action=delete" style="display:none;">
    <input type="hidden" name="transaction_id" id="deleteTransactionId">
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
function confirmDelete(id) {
    if (confirm('Bạn có chắc chắn muốn xóa giao dịch này?')) {
        document.getElementById('deleteTransactionId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

