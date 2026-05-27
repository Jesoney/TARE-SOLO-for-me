<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (isLoggedIn()) {
    redirect('/personal-asset-manager/pages/dashboard.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');

    if (empty($username)) {
        $errors[] = '用户名不能为空';
    } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 20) {
        $errors[] = '用户名长度应在3-20个字符之间';
    }

    if (empty($password)) {
        $errors[] = '密码不能为空';
    } elseif (strlen($password) < 6) {
        $errors[] = '密码长度至少为6位';
    }

    if ($password !== $confirmPassword) {
        $errors[] = '两次输入的密码不一致';
    }

    if (empty($email)) {
        $errors[] = '邮箱不能为空';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '邮箱格式不正确';
    }

    if (empty($errors)) {
        $db = getDb();

        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = '用户名已存在';
        }

        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = '邮箱已被注册';
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $email, ROLE_USER]);

            showAlert('注册成功，请登录');
            redirect('/personal-asset-manager/pages/login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <h2><i class="fas fa-user-plus"></i> 用户注册</h2>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" class="form-control" required
                           value="<?php echo isset($_POST['username']) ? e($_POST['username']) : ''; ?>"
                           placeholder="3-20个字符">
                </div>
                <div class="form-group">
                    <label for="email">邮箱</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>"
                           placeholder="请输入邮箱">
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" class="form-control" required
                           placeholder="至少6位">
                </div>
                <div class="form-group">
                    <label for="confirm_password">确认密码</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                           placeholder="再次输入密码">
                </div>
                <button type="submit" class="btn-primary">注册</button>
            </form>
            <div class="auth-links">
                <p>已有账号？<a href="login.php">立即登录</a></p>
                <p><a href="../index.php"><i class="fas fa-arrow-left"></i> 返回首页</a></p>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        if (password !== confirm) {
            e.preventDefault();
            alert('两次输入的密码不一致');
        }
    });
    </script>
</body>
</html>
