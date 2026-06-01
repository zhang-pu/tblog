<?php
/**
 * Admin Login
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDE_PATH . 'function.common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (auth_verify($username, $password)) {
        $_SESSION['admin'] = true;
        $_SESSION['admin_user'] = $username;
        // Rotate CSRF token on login
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        redirect('index.php');
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - ZhangPu Blog 管理后台</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #22c55e 0%, #0ea5e9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #22c55e;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #22c55e;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #22c55e;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #16a34a;
        }
        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #666;
            text-decoration: none;
        }
        .back-link a:hover {
            color: #22c55e;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🌿 ZhangPu Blog 管理后台</h1>
        <?php if (!empty($error)): ?>
        <div class="error"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" name="username" id="username" placeholder="请输入用户名" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">管理密码</label>
                <input type="password" name="password" id="password" placeholder="请输入管理密码" required>
            </div>
            <button type="submit">登 录</button>
        </form>
        <div class="back-link">
            <a href="../">← 返回博客</a>
        </div>
    </div>
</body>
</html>