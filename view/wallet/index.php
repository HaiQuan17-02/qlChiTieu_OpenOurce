<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/wallet.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Ví của tôi';

// Check login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Get all wallets
$wallets = getUserWallets();
$normalWallets = getNormalWallets();
$savingsWallets = getSavingsWallets();
$totalBalance = getTotalBalance();
?>

<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-wallet2"></i> Ví của tôi
                </h1>
            </div>
            
            <?= displayFlashMessage() ?>
            
            <!-- Total Balance Card -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Tổng số dư</h6>
                                    <h2 class="mb-0 balance-display" data-wallet-id="total"><?= formatCurrency($totalBalance) ?></h2>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <button class="btn btn-sm btn-link toggle-balance-btn p-0" data-wallet-id="total" title="Ẩn/Hiện số dư">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <i class="bi bi-wallet2 fs-1 text-primary opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tiết kiệm Section -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="h5 mb-0">
                        <i class="bi bi-piggy-bank"></i> Tiết kiệm
                    </h4>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSavingsModal">
                        <i class="bi bi-plus-circle"></i> Thêm mục tiêu tiết kiệm
                    </button>
                </div>
                
                <?php if (empty($savingsWallets)): ?>
                    <div class="card border-dashed">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-piggy-bank fs-1 text-muted"></i>
                            <p class="text-muted mt-3 mb-4">Chưa có mục tiêu tiết kiệm nào</p>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSavingsModal">
                                <i class="bi bi-plus-circle"></i> Tạo mục tiêu đầu tiên
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($savingsWallets as $wallet): ?>
                            <?php 
                            $balance = calculateWalletBalance($wallet['id']);
                            $targetAmount = $wallet['target_amount'] ?? 0;
                            $percentage = $targetAmount > 0 ? ($balance / $targetAmount) * 100 : 0;
                            $percentage = min($percentage, 100);
                            ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card savings-wallet-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0"><?= e($wallet['wallet_name']) ?></h6>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-link toggle-balance-btn p-0" data-wallet-id="<?= $wallet['id'] ?>" title="Ẩn/Hiện số dư">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger transfer-btn" data-from-wallet-id="<?= $wallet['id'] ?>">
                                                    <i class="bi bi-arrow-up-right"></i> Chuyển tiền
                                                </button>
                                            </div>
                                        </div>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?= $percentage ?>%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-baseline">
                                            <div class="balance-display" data-wallet-id="<?= $wallet['id'] ?>">
                                                <div class="h5 mb-0"><?= formatCurrency($balance) ?></div>
                                                <small class="text-muted">/ <?= formatCurrency($targetAmount) ?></small>
                                            </div>
                                            <span class="badge bg-success"><?= round($percentage) ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Normal Wallets Section -->
            <div class="mb-4">
                <h4 class="h5 mb-3">
                    <i class="bi bi-wallet"></i> Ví thường
                </h4>
                
                <?php if (empty($normalWallets)): ?>
                    <div class="card text-center py-3">
                        <p class="text-muted mb-0">Chưa có ví thường</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($normalWallets as $wallet): ?>
                            <?php 
                            $balance = calculateWalletBalance($wallet['id']);
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card wallet-card h-100" style="cursor: pointer;" data-wallet-id="<?= $wallet['id'] ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1"><?= e($wallet['wallet_name']) ?></h5>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar"></i> 
                                                    <?= formatDate($wallet['created_at']) ?>
                                                </small>
                                            </div>
                                            <button class="btn btn-sm btn-link toggle-balance-btn p-0" data-wallet-id="<?= $wallet['id'] ?>" title="Ẩn/Hiện số dư">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <h3 class="mb-0 balance-display" data-wallet-id="<?= $wallet['id'] ?>">
                                            <?= formatCurrency($balance, $wallet['currency']) ?>
                                        </h3>
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="bi bi-wallet"></i> Click để xem chi tiết
                                                </small>
                                                <i class="bi bi-arrow-right-circle"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

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
document.querySelectorAll('.wallet-card').forEach(card => {
    card.addEventListener('click', function() {
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
</script>

<!-- Add Savings Modal -->
<div class="modal fade" id="addSavingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-piggy-bank"></i> Thêm mục tiêu tiết kiệm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSavingsForm" method="POST" action="<?= SITE_URL ?>/handler/savings_handler.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="savings_name" class="form-label">Tên mục tiêu</label>
                        <input type="text" class="form-control" id="savings_name" name="savings_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="target_amount" class="form-label">Số tiền mục tiêu</label>
                        <input type="number" class="form-control" id="target_amount" name="target_amount" min="0" required>
                    </div>
                    <input type="hidden" name="action" value="create">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Tạo mục tiêu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transfer Money Modal (PIN required) -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-left-right"></i> Chuyển tiền
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="transferPinSection">
                    <p class="text-muted">Vui lòng nhập mã PIN để chuyển tiền</p>
                    <div class="mb-3">
                        <label for="transferPinInput" class="form-label">Mã PIN</label>
                        <input type="password" class="form-control form-control-lg text-center" id="transferPinInput" 
                               maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required autofocus>
                        <small class="text-danger d-none" id="transferPinError">Mã PIN không chính xác</small>
                    </div>
                </div>
                
                <div id="transferFormSection" style="display: none;">
                    <form id="transferForm">
                        <div class="mb-3">
                            <label class="form-label">Từ ví</label>
                            <select class="form-select" id="fromWalletId" required>
                                <option value="">Chọn ví nguồn</option>
                                <?php foreach ($normalWallets as $wallet): ?>
                                    <option value="<?= $wallet['id'] ?>"><?= e($wallet['wallet_name']) ?> 
                                        (<?= formatCurrency(calculateWalletBalance($wallet['id'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="transferAmount" class="form-label">Số tiền</label>
                            <input type="number" class="form-control" id="transferAmount" name="amount" min="1" required>
                            <input type="hidden" id="toWalletId">
                        </div>
                        <div class="mb-3">
                            <label for="transferNote" class="form-label">Ghi chú (tùy chọn)</label>
                            <textarea class="form-control" id="transferNote" name="note" rows="2"></textarea>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="verifyTransferPinBtn" style="display: none;">
                    <i class="bi bi-check-circle"></i> Tiếp tục
                </button>
                <button type="button" class="btn btn-primary" id="confirmTransferBtn" style="display: none;">
                    <i class="bi bi-check-circle"></i> Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Wallet click handler
document.querySelectorAll('.wallet-card').forEach(card => {
    card.addEventListener('click', function() {
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

// Enter key to submit PIN
document.getElementById('pinInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('verifyPinBtn').click();
    }
});

// Transfer button handler
document.querySelectorAll('.transfer-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const toWalletId = this.dataset.fromWalletId;
        document.getElementById('toWalletId').value = toWalletId;
        
        // Show transfer modal with PIN first
        const modal = new bootstrap.Modal(document.getElementById('transferModal'));
        modal.show();
        document.getElementById('transferPinInput').value = '';
        document.getElementById('transferPinInput').focus();
        document.getElementById('transferPinError').classList.add('d-none');
        document.getElementById('transferPinSection').style.display = 'block';
        document.getElementById('transferFormSection').style.display = 'none';
        document.getElementById('verifyTransferPinBtn').style.display = 'inline-block';
        document.getElementById('confirmTransferBtn').style.display = 'none';
    });
});

// Verify transfer PIN
document.getElementById('verifyTransferPinBtn').addEventListener('click', function() {
    const pin = document.getElementById('transferPinInput').value;
    
    if (!/^[0-9]{6}$/.test(pin)) {
        document.getElementById('transferPinError').textContent = 'Mã PIN phải là 6 số';
        document.getElementById('transferPinError').classList.remove('d-none');
        return;
    }
    
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
            // Show transfer form
            document.getElementById('transferPinSection').style.display = 'none';
            document.getElementById('transferFormSection').style.display = 'block';
            document.getElementById('verifyTransferPinBtn').style.display = 'none';
            document.getElementById('confirmTransferBtn').style.display = 'inline-block';
        } else {
            document.getElementById('transferPinError').textContent = data.message;
            document.getElementById('transferPinError').classList.remove('d-none');
            document.getElementById('transferPinInput').value = '';
            document.getElementById('transferPinInput').focus();
        }
    });
});

// Confirm transfer
document.getElementById('confirmTransferBtn').addEventListener('click', function() {
    const fromWalletId = document.getElementById('fromWalletId').value;
    const toWalletId = document.getElementById('toWalletId').value;
    const amount = document.getElementById('transferAmount').value;
    const note = document.getElementById('transferNote').value;
    
    if (!fromWalletId || !amount || amount <= 0) {
        alert('Vui lòng điền đầy đủ thông tin');
        return;
    }
    
    // Send transfer request
    fetch('<?= SITE_URL ?>/handler/savings_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=transfer&from_wallet_id=' + fromWalletId + '&to_wallet_id=' + toWalletId + '&amount=' + amount + '&note=' + encodeURIComponent(note)
    })
    .then(response => response.text())
    .then(data => {
        // Reload page
        window.location.reload();
    });
});

// Enter key for transfer PIN
document.getElementById('transferPinInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('verifyTransferPinBtn').click();
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
.wallet-card {
    transition: all 0.3s;
}
.wallet-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.savings-wallet-card {
    border-left: 4px solid #28a745;
    transition: all 0.3s;
}
.savings-wallet-card:hover {
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
}

.border-dashed {
    border: 2px dashed #dee2e6 !important;
}

.balance-display {
    transition: filter 0.3s ease;
}

.toggle-balance-btn {
    color: #6c757d !important;
    transition: all 0.2s;
}

.toggle-balance-btn:hover {
    color: #495057 !important;
    transform: scale(1.1);
}

.toggle-balance-btn i {
    font-size: 1.1rem;
}
</style>

