<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDb();
$userId = $_SESSION['user_id'];
$errors = [];
$success = '';

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($email)) {
            $errors[] = '邮箱不能为空';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = '邮箱格式不正确';
        }

        if (empty($errors)) {
            try {
                $stmt = $db->prepare("UPDATE users SET email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$email, $phone, $userId]);
                showAlert('个人信息更新成功');
                redirect('/personal-asset-manager/pages/profile.php');
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errors[] = '邮箱已被使用';
                } else {
                    $errors[] = '更新失败';
                }
            }
        }
    } elseif ($action === 'change_password') {
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($oldPassword)) {
            $errors[] = '请输入原密码';
        } elseif (!password_verify($oldPassword, $user['password'])) {
            $errors[] = '原密码不正确';
        }

        if (empty($newPassword)) {
            $errors[] = '请输入新密码';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = '新密码长度至少为6位';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = '两次输入的新密码不一致';
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            showAlert('密码修改成功');
            redirect('/personal-asset-manager/pages/profile.php');
        }
    }
}

$pageTitle = '个人中心';
$currentPage = 'profile';
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
                <h1><i class="fas fa-user"></i> <?php echo $pageTitle; ?></h1>
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

            <div class="charts-row">
                <div class="form-card">
                    <h3 style="margin-bottom:1rem;color:var(--primary-color);"><i class="fas fa-id-card"></i> 基本信息</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label>用户名</label>
                            <input type="text" class="form-control" value="<?php echo e($user['username']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="email">邮箱 *</label>
                            <input type="email" id="email" name="email" class="form-control" required
                                   value="<?php echo e($_POST['email'] ?? $user['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">手机号</label>
                            <input type="text" id="phone" name="phone" class="form-control"
                                   value="<?php echo e($_POST['phone'] ?? $user['phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label>注册时间</label>
                            <input type="text" class="form-control" value="<?php echo formatDateTime($user['created_at']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>角色</label>
                            <input type="text" class="form-control" value="<?php echo $user['role'] == ROLE_ADMIN ? '管理员' : '普通用户'; ?>" disabled>
                        </div>
                        <button type="submit" class="btn-primary" style="width:auto;padding:0.5rem 1.5rem;"><i class="fas fa-save"></i> 更新信息</button>
                    </form>
                </div>

                <div class="form-card">
                    <h3 style="margin-bottom:1rem;color:var(--primary-color);"><i class="fas fa-lock"></i> 修改密码</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label for="old_password">原密码 *</label>
                            <input type="password" id="old_password" name="old_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">新密码 *</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="至少6位">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">确认新密码 *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn-primary" style="width:auto;padding:0.5rem 1.5rem;"><i class="fas fa-key"></i> 修改密码</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
