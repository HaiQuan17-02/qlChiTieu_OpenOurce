<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/budget.php';
require_once __DIR__ . '/../../function/transaction.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Thêm ngân sách';

// Check login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Get expense categories
$categories = getCategories('expense');

// Check if edit mode
$editMode = false;
$budget = null;
if (isset($_GET['edit'])) {
    $budget = getBudgetById($_GET['edit']);
    if ($budget && $budget['user_id'] == getCurrentUserId()) {
        $editMode = true;
        $pageTitle = 'Sửa ngân sách';
    } else {
        setFlashMessage('error', 'Ngân sách không tồn tại hoặc không có quyền');
        redirect(SITE_URL . '/budgets.php');
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
                    <i class="bi bi-plus-circle"></i> <?= $editMode ? 'Sửa ngân sách' : 'Thêm ngân sách mới' ?>
                </h1>
                <a href="<?= SITE_URL ?>/budgets.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
            
            <?= displayFlashMessage() ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="<?= SITE_URL ?>/handler/budget_handler.php?action=<?= $editMode ? 'update' : 'add' ?>">
                                <?php if ($editMode): ?>
                                    <input type="hidden" name="budget_id" value="<?= $budget['id'] ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category_id" name="category_id" required <?= $editMode ? 'disabled' : '' ?>>
                                        <option value="">Chọn danh mục</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" 
                                                    <?= ($editMode && $budget['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                                <?= $category['icon'] ?> <?= e($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($editMode): ?>
                                        <input type="hidden" name="category_id" value="<?= $budget['category_id'] ?>">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Số tiền ngân sách <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="amount" name="amount" 
                                               value="<?= $editMode ? $budget['amount'] : '' ?>" 
                                               min="0" step="1000" required>
                                        <span class="input-group-text">VND</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="period" class="form-label">Chu kỳ <span class="text-danger">*</span></label>
                                    <select class="form-select" id="period" name="period" required>
                                        <option value="weekly" <?= ($editMode && $budget['period'] == 'weekly') ? 'selected' : '' ?>>
                                            Tuần
                                        </option>
                                        <option value="monthly" <?= (!$editMode || $budget['period'] == 'monthly') ? 'selected' : '' ?>>
                                            Tháng
                                        </option>
                                        <option value="yearly" <?= ($editMode && $budget['period'] == 'yearly') ? 'selected' : '' ?>>
                                            Năm
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="<?= $editMode ? $budget['start_date'] : date('Y-m-d') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="alert_threshold" class="form-label">Ngưỡng cảnh báo (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="alert_threshold" name="alert_threshold" 
                                               value="<?= $editMode ? $budget['alert_threshold'] : 80 ?>" 
                                               min="0" max="100" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text-muted">Cảnh báo khi chi tiêu đạt ngưỡng này</small>
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
                                <i class="bi bi-info-circle"></i> Thông tin
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="small">Ngân sách giúp bạn theo dõi và kiểm soát chi tiêu theo từng danh mục.</p>
                            
                            <h6 class="mt-3 mb-2">Ngưỡng cảnh báo:</h6>
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="bi bi-circle-fill text-success"></i> 
                                    < 80%: An toàn
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-circle-fill text-warning"></i> 
                                    80-99%: Cảnh báo
                                </li>
                                <li>
                                    <i class="bi bi-circle-fill text-danger"></i> 
                                    ≥ 100%: Vượt quá
                                </li>
                            </ul>
                            
                            <?php if ($editMode): ?>
                                <div class="alert alert-info mt-3">
                                    <small>
                                        <strong>Lưu ý:</strong> Không thể thay đổi danh mục sau khi tạo ngân sách.
                                    </small>
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

