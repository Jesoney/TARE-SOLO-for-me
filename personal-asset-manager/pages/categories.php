<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDb();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $color = trim($_POST['color'] ?? '#1e3a5f');
    $icon = trim($_POST['icon'] ?? 'fa-folder');

    if (empty($name)) {
        $errors[] = '分类名称不能为空';
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("INSERT INTO categories (name, color, icon) VALUES (?, ?, ?)");
            $stmt->execute([$name, $color, $icon]);
            showAlert('分类添加成功');
            redirect('/personal-asset-manager/pages/categories.php');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = '分类名称已存在';
            } else {
                $errors[] = '添加失败：' . $e->getMessage();
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        showAlert('分类删除成功');
    } catch (PDOException $e) {
        showAlert('该分类下存在资产，无法删除', 'danger');
    }
    redirect('/personal-asset-manager/pages/categories.php');
}

$categories = $db->query("
    SELECT c.*, COUNT(a.id) as asset_count
    FROM categories c
    LEFT JOIN assets a ON c.id = a.category_id
    GROUP BY c.id
    ORDER BY c.name
");
$categories = $categories->fetchAll();

$pageTitle = '分类管理';
$currentPage = 'categories';
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
                <h1><i class="fas fa-tags"></i> <?php echo $pageTitle; ?></h1>
                <div class="user-menu">
                    <span><i class="fas fa-user"></i> <?php echo e($_SESSION['username']); ?></span>
                </div>
            </div>

            <?php $alert = getAlert(); if ($alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?>"><?php echo e($alert['message']); ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-card" style="margin-bottom:2rem;">
                <h3 style="margin-bottom:1rem;color:var(--primary-color);"><i class="fas fa-plus"></i> 添加分类</h3>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">分类名称 *</label>
                            <input type="text" id="name" name="name" class="form-control" required
                                   placeholder="请输入分类名称">
                        </div>
                        <div class="form-group">
                            <label for="color">颜色</label>
                            <input type="color" id="color" name="color" class="form-control" value="#1e3a5f">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="icon">图标类名 (Font Awesome)</label>
                        <input type="text" id="icon" name="icon" class="form-control" value="fa-folder"
                               placeholder="例如: fa-wallet, fa-car, fa-home">
                    </div>
                    <button type="submit" class="btn-primary" style="width:auto;padding:0.5rem 1.5rem;"><i class="fas fa-save"></i> 添加</button>
                </form>
            </div>

            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>图标</th>
                            <th>分类名称</th>
                            <th>颜色</th>
                            <th>资产数量</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td><i class="fas <?php echo e($cat['icon']); ?>" style="color:<?php echo e($cat['color']); ?>"></i></td>
                            <td><?php echo e($cat['name']); ?></td>
                            <td>
                                <span style="display:inline-block;width:20px;height:20px;border-radius:4px;background:<?php echo e($cat['color']); ?>"></span>
                                <?php echo e($cat['color']); ?>
                            </td>
                            <td><?php echo $cat['asset_count']; ?></td>
                            <td>
                                <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('确定要删除该分类吗？如果分类下存在资产将无法删除。');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:2rem;color:#888;">暂无分类</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
