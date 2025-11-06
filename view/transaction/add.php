<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/wallet.php';
require_once __DIR__ . '/../../function/transaction.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Thêm giao dịch';

// Check login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Get data
$wallets = getUserWallets();
$categoriesIncome = getCategories('income');
$categoriesExpense = getCategories('expense');

// Check if edit mode
$editMode = false;
$transaction = null;
if (isset($_GET['edit'])) {
    $transaction = getTransactionById($_GET['edit']);
    if ($transaction && $transaction['user_id'] == getCurrentUserId()) {
        $editMode = true;
        $pageTitle = 'Sửa giao dịch';
    } else {
        setFlashMessage('error', 'Giao dịch không tồn tại hoặc không có quyền');
        redirect(SITE_URL . '/transaction.php');
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
                    <i class="bi bi-plus-circle"></i> <?= $editMode ? 'Sửa giao dịch' : 'Thêm giao dịch mới' ?>
                </h1>
                <a href="<?= SITE_URL ?>/transaction.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
            
            <?= displayFlashMessage() ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="<?= SITE_URL ?>/handler/transaction_handler.php?action=<?= $editMode ? 'update' : 'add' ?>">
                                <?php if ($editMode): ?>
                                    <input type="hidden" name="transaction_id" value="<?= $transaction['id'] ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label class="form-label">Loại giao dịch <span class="text-danger">*</span></label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="type" id="typeIncome" value="income" 
                                                   <?= (!$editMode || $transaction['type'] == 'income') ? 'checked' : '' ?> required>
                                            <label class="form-check-label" for="typeIncome">
                                                <i class="bi bi-arrow-up-circle text-success"></i> Thu nhập
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="type" id="typeExpense" value="expense" 
                                                   <?= ($editMode && $transaction['type'] == 'expense') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="typeExpense">
                                                <i class="bi bi-arrow-down-circle text-danger"></i> Chi tiêu
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="wallet_id" class="form-label">Ví <span class="text-danger">*</span></label>
                                    <select class="form-select" id="wallet_id" name="wallet_id" required>
                                        <option value="">Chọn ví</option>
                                        <?php foreach ($wallets as $wallet): ?>
                                            <option value="<?= $wallet['id'] ?>" 
                                                    <?= ($editMode && $transaction['wallet_id'] == $wallet['id']) ? 'selected' : '' ?>>
                                                <?= e($wallet['wallet_name']) ?> - <?= formatCurrency($wallet['balance'], $wallet['currency']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Chọn danh mục</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Số tiền <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="amount" name="amount" 
                                               value="<?= $editMode ? $transaction['amount'] : '' ?>" 
                                               min="0" step="1000" required>
                                        <span class="input-group-text">VND</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="transaction_date" class="form-label">Ngày giao dịch <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="transaction_date" name="transaction_date" 
                                           value="<?= $editMode ? $transaction['transaction_date'] : date('Y-m-d') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="note" class="form-label">Ghi chú</label>
                                    <textarea class="form-control" id="note" name="note" rows="3"><?= $editMode ? e($transaction['note']) : '' ?></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> <?= $editMode ? 'Cập nhật' : 'Thêm mới' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-info-circle"></i> Hướng dẫn
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <strong>Thu nhập:</strong> Tiền bạn nhận được
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-x-circle text-danger"></i>
                                    <strong>Chi tiêu:</strong> Tiền bạn đã sử dụng
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-wallet2 text-primary"></i>
                                    <strong>Ví:</strong> Nơi lưu trữ tiền
                                </li>
                                <li>
                                    <i class="bi bi-tag text-info"></i>
                                    <strong>Danh mục:</strong> Phân loại giao dịch
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
const categoriesIncome = <?= json_encode($categoriesIncome) ?>;
const categoriesExpense = <?= json_encode($categoriesExpense) ?>;
const currentType = '<?= $editMode ? $transaction['type'] : 'income' ?>';
const currentCategoryId = <?= $editMode ? $transaction['category_id'] : 'null' ?>;

// Populate categories based on type
function updateCategories(type) {
    const categorySelect = document.getElementById('category_id');
    const categories = type === 'income' ? categoriesIncome : categoriesExpense;
    
    categorySelect.innerHTML = '<option value="">Chọn danh mục</option>';
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.icon + ' ' + category.name;
        if (currentCategoryId && category.id == currentCategoryId) {
            option.selected = true;
        }
        categorySelect.appendChild(option);
    });
}

// Listen for type changes
document.querySelectorAll('input[name="type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        updateCategories(this.value);
    });
});

// Initialize categories on load
updateCategories(currentType);
</script>

