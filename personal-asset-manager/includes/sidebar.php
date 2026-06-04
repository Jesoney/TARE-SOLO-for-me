<?php
$currentPage = $currentPage ?? '';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-wallet"></i> <?php echo SITE_NAME; ?></h3>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> 后台首页
        </a>
        <a href="assets.php" class="<?php echo $currentPage === 'assets' ? 'active' : ''; ?>">
            <i class="fas fa-wallet"></i> 资产管理
        </a>
        <a href="categories.php" class="<?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i> 分类管理
        </a>
        <a href="profile.php" class="<?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i> 个人中心
        </a>
        <?php if (isAdmin()): ?>
        <a href="users.php" class="<?php echo $currentPage === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> 用户管理
        </a>
        <?php endif; ?>
        <a href="../logout.php">
            <i class="fas fa-sign-out-alt"></i> 退出登录
        </a>
    </nav>
</aside>
