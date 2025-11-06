<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/budget.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Ngân sách';

// Check login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Get budgets
$budgets = getUserBudgets();

// Get actual spending for each budget
foreach ($budgets as &$budget) {
    $budget['actual_spending'] = getActualSpending($budget['id']);
    $budget['percentage'] = $budget['amount'] > 0 ? ($budget['actual_spending'] / $budget['amount']) * 100 : 0;
    $budget['remaining'] = $budget['amount'] - $budget['actual_spending'];
}

// Get alerts
$alerts = getBudgetAlerts();
?>

<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-piggy-bank"></i> Ngân sách
                </h1>
                <a href="<?= SITE_URL ?>/view/budgets/add.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Thêm ngân sách
                </a>
            </div>
            
            <?= displayFlashMessage() ?>
            
            <?php if (!empty($alerts)): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Cảnh báo:</strong>
                    <ul class="mb-0">
                        <?php foreach ($alerts as $alert): ?>
                            <li><?= e($alert['message']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Budgets List -->
            <?php if (empty($budgets)): ?>
                <div class="card text-center py-5">
                    <i class="bi bi-piggy-bank fs-1 text-muted"></i>
                    <p class="text-muted mt-3 mb-4">Chưa có ngân sách nào được tạo</p>
                    <a href="<?= SITE_URL ?>/view/budgets/add.php" class="btn btn-primary">
                        Tạo ngân sách đầu tiên
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($budgets as $budget): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title">
                                                <span class="badge bg-<?= $budget['category_color'] ?? 'secondary' ?>">
                                                    <?= $budget['category_icon'] ?> <?= e($budget['category_name']) ?>
                                                </span>
                                            </h5>
                                            <p class="text-muted small mb-0">
                                                <?= ucfirst($budget['period']) ?> budget
                                            </p>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="<?= SITE_URL ?>/view/budgets/add.php?edit=<?= $budget['id'] ?>">
                                                        <i class="bi bi-pencil"></i> Sửa
                                                    </a>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger" onclick="confirmDelete(<?= $budget['id'] ?>)">
                                                        <i class="bi bi-trash"></i> Xóa
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small">Đã chi</span>
                                            <strong><?= formatCurrency($budget['actual_spending']) ?> / <?= formatCurrency($budget['amount']) ?></strong>
                                        </div>
                                        <div class="progress" style="height: 20px;">
                                            <?php
                                            $percentage = min($budget['percentage'], 100);
                                            $bgColor = $budget['percentage'] >= 100 ? 'bg-danger' : ($budget['percentage'] >= 80 ? 'bg-warning' : 'bg-success');
                                            ?>
                                            <div class="progress-bar <?= $bgColor ?>" role="progressbar" 
                                                 style="width: <?= $percentage ?>%" 
                                                 aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?= number_format($percentage, 1) ?>%
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small">
                                            Còn lại: 
                                            <strong class="<?= $budget['remaining'] < 0 ? 'text-danger' : 'text-success' ?>">
                                                <?= formatCurrency($budget['remaining']) ?>
                                            </strong>
                                        </span>
                                        <small class="text-muted">
                                            <?= formatDate($budget['start_date']) ?> - <?= formatDate($budget['end_date']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="<?= SITE_URL ?>/handler/budget_handler.php?action=delete" style="display:none;">
    <input type="hidden" name="budget_id" id="deleteBudgetId">
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
function confirmDelete(id) {
    if (confirm('Bạn có chắc chắn muốn xóa ngân sách này?')) {
        document.getElementById('deleteBudgetId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

