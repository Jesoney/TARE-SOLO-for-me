<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDb();
$userId = $_SESSION['user_id'];
$errors = [];

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect('/personal-asset-manager/pages/assets.php');
}

$stmt = $db->prepare("SELECT * FROM assets WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);
$asset = $stmt->fetch();

if (!$asset) {
    showAlert('资产不存在或无权访问', 'danger');
    redirect('/personal-asset-manager/pages/assets.php');
}

$categories = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $categories->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $amount = $_POST['amount'] ?? '';
    $categoryId = $_POST['category_id'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $purchaseDate = $_POST['purchase_date'] ?? '';

    if (empty($name)) {
        $errors[] = '资产名称不能为空';
    }

    if (!is_numeric($amount) || $amount < 0) {
        $errors[] = '金额必须是非负数';
    }

    if (empty($categoryId)) {
        $errors[] = '请选择分类';
    }

    if (empty($errors)) {
        $stmt = $db->prepare("
            UPDATE assets
            SET category_id = ?, name = ?, amount = ?, description = ?, purchase_date = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            $categoryId,
            $name,
            $amount,
            $description,
            $purchaseDate ?: null,
            $id,
            $userId
        ]);

        showAlert('资产更新成功');
        redirect('/personal-asset-manager/pages/assets.php');
    }
}

$pageTitle = '编辑资产';
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
                <h1><i class="fas fa-edit"></i> <?php echo $pageTitle; ?></h1>
                <div class="user-menu">
                    <span><i class="fas fa-user"></i> <?php echo e($_SESSION['username']); ?></span>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">资产名称 *</label>
                            <input type="text" id="name" name="name" class="form-control" required
                                   value="<?php echo e($_POST['name'] ?? $asset['name']); ?>"
                                   placeholder="请输入资产名称">
                        </div>
                        <div class="form-group">
                            <label for="amount">金额 *</label>
                            <input type="number" id="amount" name="amount" class="form-control" required step="0.01"
                                   value="<?php echo e($_POST['amount'] ?? $asset['amount']); ?>"
                                   placeholder="请输入金额">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id">分类 *</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">请选择分类</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo (($asset['category_id'] == $cat['id']) || (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id'])) ? 'selected' : ''; ?>>
                                    <?php echo e($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="purchase_date">购买日期</label>
                            <input type="date" id="purchase_date" name="purchase_date" class="form-control"
                                   value="<?php echo e($_POST['purchase_date'] ?? $asset['purchase_date']); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">描述</label>
                        <textarea id="description" name="description" class="form-control" rows="3"
                                  placeholder="请输入资产描述（可选）"><?php echo e($_POST['description'] ?? $asset['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> 更新</button>
                        <a href="assets.php" class="btn-sm" style="background:#6c757d;color:white;margin-left:0.5rem;"><i class="fas fa-times"></i> 取消</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
