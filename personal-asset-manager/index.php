<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="landing-page">
        <nav class="landing-navbar">
            <div class="logo">
                <i class="fas fa-wallet"></i> <?php echo SITE_NAME; ?>
            </div>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <a href="pages/dashboard.php"><i class="fas fa-tachometer-alt"></i> 进入后台</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> 退出</a>
                <?php else: ?>
                    <a href="pages/login.php"><i class="fas fa-sign-in-alt"></i> 登录</a>
                    <a href="pages/register.php"><i class="fas fa-user-plus"></i> 注册</a>
                <?php endif; ?>
            </div>
        </nav>

        <section class="landing-hero">
            <h1>轻松管理您的个人资产</h1>
            <p>一站式资产管理平台，帮助您清晰掌握财务状况，合理规划资产配置</p>
            <?php if (!isLoggedIn()): ?>
                <a href="pages/register.php" class="btn-landing">立即开始使用</a>
            <?php else: ?>
                <a href="pages/dashboard.php" class="btn-landing">进入管理后台</a>
            <?php endif; ?>
        </section>

        <section class="landing-features">
            <h2>系统功能特色</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-list-alt"></i>
                    <h3>资产记录</h3>
                    <p>详细记录各类资产信息，支持多种资产类型</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-pie"></i>
                    <h3>数据统计</h3>
                    <p>直观的图表展示，资产分布一目了然</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-tags"></i>
                    <h3>分类管理</h3>
                    <p>灵活的分类体系，让资产管理更有序</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-search"></i>
                    <h3>快速搜索</h3>
                    <p>支持关键词搜索，快速定位资产信息</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>安全可靠</h3>
                    <p>密码加密存储，数据安全有保障</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>响应式设计</h3>
                    <p>支持多种设备，随时随地管理资产</p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
