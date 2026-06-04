<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (isLoggedIn()) {
    redirect('/personal-asset-manager/pages/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = '请输入用户名和密码';
    } else {
        $db = getDb();
        $stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            redirect('/personal-asset-manager/pages/dashboard.php');
        } else {
            $error = '用户名或密码错误';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <h2><i class="fas fa-wallet"></i> 用户登录</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" class="form-control" required
                           value="<?php echo isset($_POST['username']) ? e($_POST['username']) : ''; ?>"
                           placeholder="请输入用户名">
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" class="form-control" required
                           placeholder="请输入密码">
                </div>
                <button type="submit" class="btn-primary">登录</button>
            </form>
            <div class="auth-links">
                <p>还没有账号？<a href="register.php">立即注册</a></p>
                <p><a href="../index.php"><i class="fas fa-arrow-left"></i> 返回首页</a></p>
            </div>
        </div>
    </div>
</body>
</html>
