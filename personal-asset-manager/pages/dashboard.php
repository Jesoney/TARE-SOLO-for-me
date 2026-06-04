<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDb();
$userId = $_SESSION['user_id'];

$totalAssets = $db->prepare("SELECT COUNT(*) FROM assets WHERE user_id = ?");
$totalAssets->execute([$userId]);
$totalAssets = $totalAssets->fetchColumn();

$totalAmount = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM assets WHERE user_id = ?");
$totalAmount->execute([$userId]);
$totalAmount = $totalAmount->fetchColumn();

$categoryStats = $db->prepare("
    SELECT c.name, c.color, COUNT(a.id) as count, COALESCE(SUM(a.amount), 0) as amount
    FROM categories c
    LEFT JOIN assets a ON c.id = a.category_id AND a.user_id = ?
    GROUP BY c.id
    ORDER BY amount DESC
");
$categoryStats->execute([$userId]);
$categoryStats = $categoryStats->fetchAll();

$recentAssets = $db->prepare("
    SELECT a.*, c.name as category_name, c.color
    FROM assets a
    JOIN categories c ON a.category_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
    LIMIT 5
");
$recentAssets->execute([$userId]);
$recentAssets = $recentAssets->fetchAll();

$pageTitle = '后台首页';
$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="top-bar">
                <h1><i class="fas fa-tachometer-alt"></i> <?php echo $pageTitle; ?></h1>
                <div class="user-menu">
                    <span><i class="fas fa-user"></i> <?php echo e($_SESSION['username']); ?></span>
                </div>
            </div>

            <?php $alert = getAlert(); if ($alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?>"><?php echo e($alert['message']); ?></div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-wallet"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $totalAssets; ?></h3>
                        <p>资产总数</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-coins"></i></div>
                    <div class="stat-info">
                        <h3><?php echo formatMoney($totalAmount); ?></h3>
                        <p>资产总额</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-tags"></i></div>
                    <div class="stat-info">
                        <h3><?php echo count($categoryStats); ?></h3>
                        <p>分类数量</p>
                    </div>
                </div>
            </div>

            <div class="charts-row">
                <div class="chart-container">
                    <h3><i class="fas fa-chart-pie"></i> 资产分类分布</h3>
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
                <div class="chart-container">
                    <h3><i class="fas fa-list"></i> 最近添加的资产</h3>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>资产名称</th>
                                    <th>分类</th>
                                    <th>金额</th>
                                    <th>添加时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAssets as $asset): ?>
                                <tr>
                                    <td><?php echo e($asset['name']); ?></td>
                                    <td><span style="color:<?php echo e($asset['color']); ?>"><i class="fas fa-circle" style="font-size:8px"></i> <?php echo e($asset['category_name']); ?></span></td>
                                    <td><?php echo formatMoney($asset['amount']); ?></td>
                                    <td><?php echo formatDateTime($asset['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentAssets)): ?>
                                <tr><td colspan="4" style="text-align:center;color:#888;">暂无资产记录</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const categoryData = <?php echo json_encode($categoryStats); ?>;
    const labels = categoryData.map(c => c.name);
    const data = categoryData.map(c => parseFloat(c.amount));
    const colors = categoryData.map(c => c.color || '#1e3a5f');

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>
</body>
</html>
