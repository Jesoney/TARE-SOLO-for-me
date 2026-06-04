<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

requireAdmin();

$db = getDb();

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id != $_SESSION['user_id']) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role != ?");
        $stmt->execute([$id, ROLE_ADMIN]);
        if ($stmt->rowCount() > 0) {
            showAlert('用户删除成功');
        } else {
            showAlert('无法删除管理员用户', 'danger');
        }
    } else {
        showAlert('不能删除当前登录用户', 'danger');
    }
    redirect('/personal-asset-manager/pages/users.php');
}

$page = max(1, intval($_GET['page'] ?? 1));
$keyword = trim($_GET['keyword'] ?? '');

$where = "WHERE 1=1";
$params = [];

if (!empty($keyword)) {
    $where .= " AND (username LIKE ? OR email LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM users $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$pagination = getPagination($total, $page);

$userStmt = $db->prepare("
    SELECT u.*, COUNT(a.id) as asset_count
    FROM users u
    LEFT JOIN assets a ON u.id = a.user_id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
");
$params[] = $pagination['per_page'];
$params[] = $pagination['offset'];
$userStmt->execute($params);
$users = $userStmt->fetchAll();

$pageTitle = '用户管理';
$currentPage = 'users';
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
                <h1><i class="fas fa-users"></i> <?php echo $pageTitle; ?></h1>
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
                        <input type="text" name="keyword" placeholder="搜索用户名或邮箱..." value="<?php echo e($keyword); ?>">
                        <button type="submit" class="btn-sm btn-edit"><i class="fas fa-search"></i> 搜索</button>
                        <?php if ($keyword): ?>
                            <a href="users.php" class="btn-sm" style="background:#6c757d;color:white;"><i class="fas fa-times"></i> 清除</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户名</th>
                            <th>邮箱</th>
                            <th>手机号</th>
                            <th>角色</th>
                            <th>资产数量</th>
                            <th>注册时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo e($u['username']); ?></td>
                            <td><?php echo e($u['email']); ?></td>
                            <td><?php echo e($u['phone'] ?: '-'); ?></td>
                            <td>
                                <?php if ($u['role'] == ROLE_ADMIN): ?>
                                    <span style="color:var(--secondary-color);"><i class="fas fa-crown"></i> 管理员</span>
                                <?php else: ?>
                                    <span style="color:#888;">普通用户</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $u['asset_count']; ?></td>
                            <td><?php echo formatDateTime($u['created_at']); ?></td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['user_id'] && $u['role'] != ROLE_ADMIN): ?>
                                    <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('确定要删除该用户吗？此操作不可恢复。');"><i class="fas fa-trash"></i></a>
                                <?php else: ?>
                                    <span style="color:#ccc;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:2rem;color:#888;">暂无用户</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pagination['total_pages'] > 1): ?>
            <div class="pagination">
                <?php if ($pagination['has_prev']): ?>
                    <a href="?page=<?php echo $page - 1; ?>&keyword=<?php echo urlencode($keyword); ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&keyword=<?php echo urlencode($keyword); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['has_next']): ?>
                    <a href="?page=<?php echo $page + 1; ?>&keyword=<?php echo urlencode($keyword); ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
