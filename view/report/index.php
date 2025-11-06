<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../function/report.php';
require_once __DIR__ . '/../../function/transaction.php';
require_once __DIR__ . '/../../function/helper.php';

$pageTitle = 'Thống kê & Phân tích';

// Check login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/view/auth/login.php');
}

// Get data
$expenseTrend = getExpenseTrend(12); // 12 tháng
$monthComparison = getMonthComparison();
$savingsSuggestions = getSavingsSuggestions();
$expenseByCategory = getExpenseByCategory(getFirstDayOfMonth(), getLastDayOfMonth());
$categories = getCategories('expense');

// Get selected category for filter
$selectedCategory = $_GET['category'] ?? null;
$filteredTrend = $selectedCategory ? getExpenseTrend(12, $selectedCategory) : $expenseTrend;
?>

<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-graph-up"></i> Thống kê & Phân tích
                </h1>
            </div>
            
            <?= displayFlashMessage() ?>
            
            <!-- Biểu đồ xu hướng chi tiêu -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up-arrow"></i> Biểu đồ xu hướng chi tiêu (12 tháng)
                        </h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="categoryFilter" style="width: 200px;">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="expenseTrendChart" height="100"></canvas>
                </div>
            </div>
            
            <!-- So sánh tháng này với tháng trước -->
            <?php if ($monthComparison): ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-calculator"></i> So sánh tháng này
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="border-end pe-3">
                                        <div class="small text-muted mb-2">Thu nhập</div>
                                        <div class="h5 mb-1"><?= formatCurrency($monthComparison['current']['income']) ?></div>
                                        <?php 
                                        $incomeChange = $monthComparison['change']['income'];
                                        $incomeClass = $incomeChange >= 0 ? 'text-success' : 'text-danger';
                                        $incomeIcon = $incomeChange >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                                        ?>
                                        <div class="small <?= $incomeClass ?>">
                                            <i class="bi <?= $incomeIcon ?>"></i>
                                            <?= abs($incomeChange) ?>% so với tháng trước
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ps-3">
                                        <div class="small text-muted mb-2">Chi tiêu</div>
                                        <div class="h5 mb-1"><?= formatCurrency($monthComparison['current']['expense']) ?></div>
                                        <?php 
                                        $expenseChange = $monthComparison['change']['expense'];
                                        $expenseClass = $expenseChange <= 0 ? 'text-success' : 'text-danger';
                                        $expenseIcon = $expenseChange <= 0 ? 'bi-arrow-down' : 'bi-arrow-up';
                                        ?>
                                        <div class="small <?= $expenseClass ?>">
                                            <i class="bi <?= $expenseIcon ?>"></i>
                                            <?= abs($expenseChange) ?>% so với tháng trước
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="row">
                                <div class="col-12">
                                    <div class="small text-muted mb-2">Tiết kiệm</div>
                                    <?php 
                                    $savings = $monthComparison['current']['income'] - $monthComparison['current']['expense'];
                                    $savingsClass = $savings >= 0 ? 'text-success' : 'text-danger';
                                    ?>
                                    <div class="h4 mb-0 <?= $savingsClass ?>">
                                        <?= formatCurrency(abs($savings)) ?>
                                    </div>
                                    <?php if ($monthComparison['current']['income'] > 0): ?>
                                        <?php $savingsRate = ($savings / $monthComparison['current']['income']) * 100; ?>
                                        <div class="small text-muted">
                                            Tỷ lệ tiết kiệm: <?= round($savingsRate, 1) ?>%
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar2-check"></i> Chi tiết tháng trước
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="small text-muted mb-1">Thu nhập</div>
                                <div class="h6 mb-0"><?= formatCurrency($monthComparison['last']['income']) ?></div>
                            </div>
                            <div class="mb-3">
                                <div class="small text-muted mb-1">Chi tiêu</div>
                                <div class="h6 mb-0"><?= formatCurrency($monthComparison['last']['expense']) ?></div>
                            </div>
                            <div>
                                <div class="small text-muted mb-1">Tiết kiệm</div>
                                <?php 
                                $lastSavings = $monthComparison['last']['income'] - $monthComparison['last']['expense'];
                                $lastSavingsClass = $lastSavings >= 0 ? 'text-success' : 'text-danger';
                                ?>
                                <div class="h6 mb-0 <?= $lastSavingsClass ?>">
                                    <?= formatCurrency(abs($lastSavings)) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Gợi ý tiết kiệm -->
            <?php if (!empty($savingsSuggestions)): ?>
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb"></i> Gợi ý tiết kiệm thông minh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($savingsSuggestions as $index => $suggestion): ?>
                            <div class="col-md-6 mb-3">
                                <div class="alert alert-<?= $suggestion['type'] === 'danger' ? 'danger' : ($suggestion['type'] === 'warning' ? 'warning' : ($suggestion['type'] === 'info' ? 'info' : 'secondary')) ?> d-flex align-items-start" role="alert">
                                    <span class="me-2" style="font-size: 1.5rem;"><?= e($suggestion['icon']) ?></span>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold mb-1"><?= e($suggestion['title']) ?></div>
                                        <div class="small"><?= e($suggestion['message']) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card mb-4 border-success">
                <div class="card-body text-center py-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-success">Tài chính của bạn đang tốt!</h5>
                    <p class="text-muted mb-0">Không có gợi ý cắt giảm nào lúc này. Hãy duy trì thói quen chi tiêu lành mạnh.</p>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    const categoryId = this.value;
    window.location.href = '<?= SITE_URL ?>/view/report/index.php?category=' + categoryId;
});

// Expense Trend Chart
const ctx = document.getElementById('expenseTrendChart').getContext('2d');
const expenseTrendData = <?= json_encode($filteredTrend) ?>;

const expenseTrendChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: expenseTrendData.map(item => item.month_name),
        datasets: [{
            label: 'Chi tiêu',
            data: expenseTrendData.map(item => item.expense),
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Chi tiêu: ' + new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND',
                            notation: 'compact'
                        }).format(value);
                    }
                }
            }
        }
    }
});
</script>

