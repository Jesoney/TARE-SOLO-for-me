<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDb();
$userId = $_SESSION['user_id'];

$keyword = trim($_GET['keyword'] ?? '');
$categoryId = $_GET['category_id'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

$where = "WHERE a.user_id = ?";
$params = [$userId];

if (!empty($keyword)) {
    $where .= " AND (a.name LIKE ? OR a.description LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

if (!empty($categoryId)) {
    $where .= " AND a.category_id = ?";
    $params[] = $categoryId;
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM assets a $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$pagination = getPagination($total, $page);

$assetStmt = $db->prepare("
    SELECT a.*, c.name as category_name, c.color
    FROM assets a
    JOIN categories c ON a.category_id = c.id
    $where
    ORDER BY a.created_at DESC
    LIMIT ? OFFSET ?
");
$params[] = $pagination['per_page'];
$params[] = $pagination['offset'];
$assetStmt->execute($params);
$assets = $assetStmt->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $categories->fetchAll();

$pageTitle = '资产管理';
$currentPage = 'assets';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="top-bar">
                <h1><i class="fas fa-wallet"></i> <?php echo $pageTitle; ?></h1>
                <div class="user-menu">
                    <span><i class="fas fa-user"></i> <?php echo e($_SESSION['username']); ?></span>
                </div>
            </div>

            <?php $alert = getAlert(); if ($alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?>"><?php echo e($alert['message']); ?></div>
            <?php endif; ?>

            <div class="toolbar">
                <div class="search-box">
                    <form method="GET" action="">
                        <select name="category_id" class="form-control" style="width:auto;min-width:120px;display:inline-block;">
                            <option value="">全部分类</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="keyword" placeholder="搜索资产名称..." value="<?php echo e($keyword); ?>">
                        <button type="submit" class="btn-sm btn-edit"><i class="fas fa-search"></i> 搜索</button>
                        <?php if ($keyword || $categoryId): ?>
                            <a href="assets.php" class="btn-sm" style="background:#6c757d;color:white;"><i class="fas fa-times"></i> 清除</a>
                        <?php endif; ?>
                    </form>
                </div>
                <a href="asset_add.php" class="btn-success"><i class="fas fa-plus"></i> 添加资产</a>
            </div>

            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>资产名称</th>
                            <th>分类</th>
                            <th>金额</th>
                            <th>购买日期</th>
                            <th>描述</th>
                            <th>添加时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assets as $asset): ?>
                        <tr>
                            <td><?php echo $asset['id']; ?></td>
                            <td><?php echo e($asset['name']); ?></td>
                            <td>
                                <span style="color:<?php echo e($asset['color']); ?>">
                                    <i class="fas fa-circle" style="font-size:8px"></i>
                                    <?php echo e($asset['category_name']); ?>
                                </span>
                            </td>
                            <td><?php echo formatMoney($asset['amount']); ?></td>
                            <td><?php echo $asset['purchase_date'] ? formatDate($asset['purchase_date']) : '-'; ?></td>
                            <td><?php echo e($asset['description'] ?: '-'); ?></td>
                            <td><?php echo formatDateTime($asset['created_at']); ?></td>
                            <td>
                                <a href="asset_edit.php?id=<?php echo $asset['id']; ?>" class="btn-sm btn-edit"><i class="fas fa-edit"></i></a>
                                <a href="asset_delete.php?id=<?php echo $asset['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('确定要删除该资产吗？');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($assets)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:2rem;color:#888;">
                                <i class="fas fa-inbox" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>
                                暂无资产记录
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pagination['total_pages'] > 1): ?>
            <div class="pagination">
                <?php if ($pagination['has_prev']): ?>
                    <a href="?page=<?php echo $page - 1; ?>&keyword=<?php echo urlencode($keyword); ?>&category_id=<?php echo $categoryId; ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&keyword=<?php echo urlencode($keyword); ?>&category_id=<?php echo $categoryId; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['has_next']): ?>
                    <a href="?page=<?php echo $page + 1; ?>&keyword=<?php echo urlencode($keyword); ?>&category_id=<?php echo $categoryId; ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
