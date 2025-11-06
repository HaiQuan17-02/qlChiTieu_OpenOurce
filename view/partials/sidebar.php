<?php if (isLoggedIn()): ?>
<div class="col-md-3 col-lg-2 px-0">
<div class="sidebar-modern">
    <!-- Brand -->
    <div class="sidebar-brand">
        <i class="bi bi-wallet2"></i>
        <span>Quản Lý Chi Tiêu</span>
    </div>
    
    <!-- Main Menu -->
    <div class="sidebar-menu-section">
        <div class="sidebar-section-title">TRANG CHỦ</div>
        <a class="sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/dashboard.php">
            <div class="sidebar-icon">
                <i class="bi bi-grid-3x3-gap"></i>
            </div>
            <span class="sidebar-text">Bảng điều khiển</span>
        </a>
    </div>
    
    <div class="sidebar-menu-section">
        <div class="sidebar-section-title">QUẢN LÝ</div>
        <a class="sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'wallets.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/wallets.php">
            <div class="sidebar-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <span class="sidebar-text">Ví của tôi</span>
        </a>
        <a class="sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'transaction.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/transaction.php">
            <div class="sidebar-icon">
                <i class="bi bi-list-check"></i>
            </div>
            <span class="sidebar-text">Giao dịch</span>
        </a>
        <a class="sidebar-item <?= basename($_SERVER['PHP_SELF']) == 'budgets.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/budgets.php">
            <div class="sidebar-icon">
                <i class="bi bi-piggy-bank"></i>
            </div>
            <span class="sidebar-text">Ngân sách</span>
        </a>
    </div>
    
    <div class="sidebar-menu-section">
        <div class="sidebar-section-title">BÁO CÁO</div>
        <a class="sidebar-item <?= (basename($_SERVER['PHP_SELF']) == 'report.php' || strpos($_SERVER['REQUEST_URI'], '/view/report/') !== false) ? 'active' : '' ?>" href="<?= SITE_URL ?>/report.php">
            <div class="sidebar-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <span class="sidebar-text">Thống kê</span>
        </a>
    </div>
    
    <!-- Footer -->
    <div class="sidebar-footer">
        <a href="<?= SITE_URL ?>/profile.php" class="user-info-sidebar" style="text-decoration: none; color: inherit; cursor: pointer;">
            <i class="bi bi-person-circle"></i>
            <div>
                <div class="user-name"><?= e($_SESSION['fullname'] ?? 'User') ?></div>
                <small class="user-role">Người dùng</small>
            </div>
        </a>
        <a href="<?= SITE_URL ?>/handler/logout_handler.php" class="logout-btn">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</div>
</div>
<?php endif; ?>

