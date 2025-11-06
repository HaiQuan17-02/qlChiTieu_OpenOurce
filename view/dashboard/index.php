<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/wallet.php';
require_once __DIR__ . '/../../function/transaction.php';
require_once __DIR__ . '/../../function/budget.php';
require_once __DIR__ . '/../../function/report.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Dashboard';

// Check login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Get data
$wallets = getUserWallets();
$totalBalance = getTotalBalance();
$currentMonth = getMonthlySummary();
$weeklyStats = getWeeklyStats();
$last6Months = getLast6MonthsStats();
$expenseByCategory = getExpenseByCategory(getFirstDayOfMonth(), getLastDayOfMonth());
$incomeByCategory = getIncomeByCategory(getFirstDayOfMonth(), getLastDayOfMonth());
$budgets = getUserBudgets();
$budgetAlerts = getBudgetAlerts();
$recentTransactions = getUserTransactions(null, null, getFirstDayOfMonth(), getLastDayOfMonth());
$recentTransactions = array_slice($recentTransactions, 0, 10);
?>

<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </h1>
                <small class="text-muted">Tháng <?= date('m/Y') ?></small>
            </div>
            
            <?= displayFlashMessage() ?>
            
            <!-- Alerts -->
            <?php if (!empty($budgetAlerts)): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>Cảnh báo ngân sách:</strong>
                    <?php foreach ($budgetAlerts as $alert): ?>
                        <div><?= e($alert['message']) ?></div>
                    <?php endforeach; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2">Tổng số dư</h6>
                                    <h3 class="card-title mb-0 balance-display" data-wallet-id="total"><?= formatCurrency($totalBalance) ?></h3>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button class="btn btn-sm btn-link toggle-balance-btn p-0 text-white" data-wallet-id="total" title="Ẩn/Hiện số dư">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2">Thu nhập tháng này</h6>
                                    <h3 class="card-title mb-0"><?= formatCurrency($currentMonth['income']) ?></h3>
                                </div>
                                <i class="bi bi-arrow-up-circle fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2">Chi tiêu tháng này</h6>
                                    <h3 class="card-title mb-0"><?= formatCurrency($currentMonth['expense']) ?></h3>
                                </div>
                                <i class="bi bi-arrow-down-circle fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2">Còn lại</h6>
                                    <h3 class="card-title mb-0 <?= $currentMonth['balance'] < 0 ? 'text-warning' : '' ?>">
                                        <?= formatCurrency($currentMonth['balance']) ?>
                                    </h3>
                                </div>
                                <i class="bi bi-pie-chart fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bar-chart"></i> Thống kê 6 tháng gần nhất
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pie-chart"></i> Chi tiêu theo danh mục
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="expenseChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Wallets and Recent Transactions -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-wallet2"></i> Ví tiền
                            </h5>
                            <span class="badge bg-primary"><?= count($wallets) ?></span>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($wallets as $wallet): ?>
                                <?php 
                                $balance = calculateWalletBalance($wallet['id']);
                                ?>
                                <div class="list-group-item wallet-item" style="cursor: pointer;" data-wallet-id="<?= $wallet['id'] ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= e($wallet['wallet_name']) ?></h6>
                                            <small class="text-muted balance-display" data-wallet-id="<?= $wallet['id'] ?>"><?= formatCurrency($balance, $wallet['currency']) ?></small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <button class="btn btn-sm btn-link toggle-balance-btn p-0" data-wallet-id="<?= $wallet['id'] ?>" title="Ẩn/Hiện số dư">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <i class="bi bi-wallet fs-4 text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history"></i> Giao dịch gần đây
                            </h5>
                            <a href="<?= SITE_URL ?>/transaction.php" class="btn btn-sm btn-outline-primary">
                                Xem tất cả
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recentTransactions)): ?>
                                <p class="text-center text-muted p-3">Chưa có giao dịch nào</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Ngày</th>
                                                <th>Danh mục</th>
                                                <th>Loại</th>
                                                <th>Số tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentTransactions as $transaction): ?>
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
                                                    <td class="<?= $transaction['type'] == 'income' ? 'text-success' : 'text-danger' ?>">
                                                        <?= $transaction['type'] == 'income' ? '+' : '-' ?><?= formatCurrency($transaction['amount']) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
// Monthly Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: [<?= implode(',', array_map(function($m) { return "'" . $m['month_name'] . "'"; }, $last6Months)) ?>],
        datasets: [
            {
                label: 'Thu nhập',
                data: [<?= implode(',', array_column($last6Months, 'income')) ?>],
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            },
            {
                label: 'Chi tiêu',
                data: [<?= implode(',', array_column($last6Months, 'expense')) ?>],
                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Expense Pie Chart
const expenseCtx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(expenseCtx, {
    type: 'pie',
    data: {
        labels: [<?= implode(',', array_map(function($c) { return "'" . e($c['name']) . "'"; }, $expenseByCategory)) ?>],
        datasets: [{
            data: [<?= implode(',', array_column($expenseByCategory, 'total')) ?>],
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)',
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});
</script>

<!-- PIN Modal -->
<div class="modal fade" id="pinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-shield-lock"></i> Xác thực mã PIN
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Vui lòng nhập mã PIN 6 số để xem chi tiết ví</p>
                <form id="pinForm">
                    <div class="mb-3">
                        <label for="pinInput" class="form-label">Mã PIN</label>
                        <input type="password" class="form-control form-control-lg text-center" id="pinInput" 
                               maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required autofocus>
                        <small class="text-danger d-none" id="pinError">Mã PIN không chính xác</small>
                    </div>
                    <input type="hidden" id="walletId" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="verifyPinBtn">
                    <i class="bi bi-check-circle"></i> Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Wallet click handler
document.querySelectorAll('.wallet-item').forEach(item => {
    item.addEventListener('click', function() {
        const walletId = this.dataset.walletId;
        document.getElementById('walletId').value = walletId;
        const modal = new bootstrap.Modal(document.getElementById('pinModal'));
        modal.show();
        document.getElementById('pinInput').value = '';
        document.getElementById('pinInput').focus();
        document.getElementById('pinError').classList.add('d-none');
    });
});

// Verify PIN
document.getElementById('verifyPinBtn').addEventListener('click', function() {
    const pin = document.getElementById('pinInput').value;
    const walletId = document.getElementById('walletId').value;
    
    // Validate
    if (!/^[0-9]{6}$/.test(pin)) {
        document.getElementById('pinError').textContent = 'Mã PIN phải là 6 số';
        document.getElementById('pinError').classList.remove('d-none');
        return;
    }
    
    // Send AJAX request
    fetch('<?= SITE_URL ?>/handler/verify_pin_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'pin=' + encodeURIComponent(pin)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and redirect to wallet detail
            bootstrap.Modal.getInstance(document.getElementById('pinModal')).hide();
            window.location.href = '<?= SITE_URL ?>/view/wallet/detail.php?id=' + walletId;
        } else {
            document.getElementById('pinError').textContent = data.message;
            document.getElementById('pinError').classList.remove('d-none');
            document.getElementById('pinInput').value = '';
            document.getElementById('pinInput').focus();
        }
    })
    .catch(error => {
        document.getElementById('pinError').textContent = 'Có lỗi xảy ra. Vui lòng thử lại.';
        document.getElementById('pinError').classList.remove('d-none');
    });
});

// Enter key to submit
document.getElementById('pinInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('verifyPinBtn').click();
    }
});

// Toggle balance visibility
document.querySelectorAll('.toggle-balance-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent card click
        
        const walletId = this.dataset.walletId;
        const balanceDisplay = document.querySelector(`.balance-display[data-wallet-id="${walletId}"]`);
        const icon = this.querySelector('i');
        
        if (balanceDisplay) {
            if (balanceDisplay.style.filter === 'blur(8px)') {
                // Show balance
                balanceDisplay.style.filter = 'none';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                // Hide balance
                balanceDisplay.style.filter = 'blur(8px)';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        }
    });
});
</script>

<style>
.wallet-item:hover {
    background-color: #f8f9fa;
}

.balance-display {
    transition: filter 0.3s ease;
}

.toggle-balance-btn {
    transition: all 0.2s;
}

.toggle-balance-btn:not(.text-white) {
    color: #6c757d !important;
}

.toggle-balance-btn.text-white {
    color: rgba(255, 255, 255, 0.8) !important;
}

.toggle-balance-btn:not(.text-white):hover {
    color: #495057 !important;
}

.toggle-balance-btn.text-white:hover {
    color: rgba(255, 255, 255, 1) !important;
}

.toggle-balance-btn:hover {
    transform: scale(1.1);
}

.toggle-balance-btn i {
    font-size: 1.1rem;
}
</style>

