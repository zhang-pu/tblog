<?php
/**
 * TBlog - Installation Wizard
 */
session_start();

// Load common functions
require_once __DIR__ . '/include/function.common.php';

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

// Handle form submission for step 2
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $_SESSION['db_host'] = trim($_POST['db_host'] ?? 'localhost');
    $_SESSION['db_port'] = intval($_POST['db_port'] ?? 3306);
    $_SESSION['db_user'] = trim($_POST['db_user'] ?? '');
    $_SESSION['db_pass'] = trim($_POST['db_pass'] ?? '');
    $_SESSION['db_name'] = trim($_POST['db_name'] ?? '');

    // Test connection
    $mysqli = @new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], '', $_SESSION['db_port']);
    if ($mysqli->connect_error) {
        $error = '数据库连接失败: ' . $mysqli->connect_error;
    } else {
        // Create database if not exists
        $mysqli->query("CREATE DATABASE IF NOT EXISTS `{$_SESSION['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $mysqli->select_db($_SESSION['db_name']);

        // Read and execute install.sql
        $sql_file = file_get_contents(__DIR__ . '/install.sql');
        $statements = array_filter(array_map('trim', explode(';', $sql_file)));

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                if (!$mysqli->query($statement)) {
                    $error = '数据库初始化失败: ' . $mysqli->error;
                    break;
                }
            }
        }

        if (!$error) {
            $success = '数据库连接成功，配置已保存';
            header('Location: ?step=3');
            exit;
        }
        $mysqli->close();
    }
}

// Handle step 3 - save config and set admin credentials
if ($step === 3) {
    $config_content = "<?php
/**
 * TBlog - Configuration
 */
define('DB_HOST', '{$_SESSION['db_host']}');
define('DB_PORT', {$_SESSION['db_port']});
define('DB_USER', '" . addslashes($_SESSION['db_user']) . "');
define('DB_PASS', '" . addslashes($_SESSION['db_pass']) . "');
define('DB_NAME', '{$_SESSION['db_name']}');
define('DB_PREFIX', '');

// Path constants
define('ROOT_PATH', __DIR__ . '/');
define('INCLUDE_PATH', ROOT_PATH . 'include/');
define('TEMPLATE_PATH', ROOT_PATH . 'templates/');

// Admin credentials stored in database (settings table)
// Initial setup below; change via admin panel after install
session_start();
";

    if (!file_put_contents(__DIR__ . '/config.php', $config_content)) {
        $error = '配置文件写入失败，请检查目录权限';
    } else {
        // Set initial admin credentials in database
        $mysqli = @new mysqli($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name'], $_SESSION['db_port']);
        if (!$mysqli->connect_error) {
            $admin_user = 'admin';
            $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $mysqli->query("INSERT INTO settings (`key`, value) VALUES ('admin_user', 'admin') ON DUPLICATE KEY UPDATE value = 'admin'");
            $mysqli->query("INSERT INTO settings (`key`, value) VALUES ('admin_hash', '$admin_hash') ON DUPLICATE KEY UPDATE value = '$admin_hash'");
            $mysqli->close();
        }
        header('Location: ?step=4');
        exit;
    }
}

// Step 5 - completion
if ($step === 5) {
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装向导 - TBlog</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #22c55e 0%, #0ea5e9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .installer {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
        }
        .installer-header {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #fff;
            padding: 30px;
            text-align: center;
        }
        .installer-header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .installer-header p {
            opacity: 0.9;
        }
        .progress-bar {
            display: flex;
            padding: 0 30px;
            margin-top: 20px;
        }
        .progress-step {
            flex: 1;
            height: 4px;
            background: #e5e5e5;
            position: relative;
        }
        .progress-step.completed {
            background: #22c55e;
        }
        .progress-step.active {
            background: #22c55e;
        }
        .progress-step::before {
            content: attr(data-step);
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 24px;
            height: 24px;
            background: #e5e5e5;
            border-radius: 50%;
            font-size: 12px;
            line-height: 24px;
            color: #666;
        }
        .progress-step.completed::before,
        .progress-step.active::before {
            background: #22c55e;
            color: #fff;
        }
        .installer-body {
            padding: 40px 30px;
        }
        h2 {
            color: #1a1a2e;
            margin-bottom: 20px;
            font-size: 22px;
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
        input[type="password"],
        input[type="number"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #22c55e;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: #22c55e;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
        }
        .btn:hover {
            background: #16a34a;
        }
        .btn-secondary {
            background: #e5e5e5;
            color: #333;
        }
        .btn-secondary:hover {
            background: #d5d5d5;
        }
        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success {
            background: #dcfce7;
            color: #16a34a;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .requirements {
            list-style: none;
        }
        .requirements li {
            padding: 12px 0;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .requirements li:last-child {
            border-bottom: none;
        }
        .requirements .ok { color: #22c55e; }
        .requirements .fail { color: #dc2626; }
        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }
        .features {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .features ul {
            list-style: none;
        }
        .features li {
            padding: 8px 0;
            padding-left: 24px;
            position: relative;
        }
        .features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #22c55e;
        }
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            color: #1e40af;
        }
        .text-center { text-align: center; }
        .mt-4 { margin-top: 24px; }
    </style>
</head>
<body>
    <div class="installer">
        <div class="installer-header">
            <h1>🌿 TBlog 安装向导</h1>
            <p>一个简洁优雅的博客系统</p>
            <div class="progress-bar">
                <div class="progress-step <?php echo $step >= 1 ? 'completed' : ''; ?>" data-step="1"></div>
                <div class="progress-step <?php echo $step >= 2 ? 'completed' : ''; ?> <?php echo $step == 2 ? 'active' : ''; ?>" data-step="2"></div>
                <div class="progress-step <?php echo $step >= 3 ? 'completed' : ''; ?> <?php echo $step == 3 ? 'active' : ''; ?>" data-step="3"></div>
                <div class="progress-step <?php echo $step >= 4 ? 'completed' : ''; ?> <?php echo $step == 4 ? 'active' : ''; ?>" data-step="4"></div>
                <div class="progress-step <?php echo $step >= 5 ? 'completed' : ''; ?> <?php echo $step == 5 ? 'active' : ''; ?>" data-step="5"></div>
            </div>
        </div>
        <div class="installer-body">
            <?php if ($step === 1): ?>
            <h2>第一步：环境检测</h2>
            <ul class="requirements">
                <li class="<?php echo PHP_VERSION_ID >= 70000 ? 'ok' : 'fail'; ?>">
                    PHP 版本 >= 7.0 (当前: <?php echo PHP_VERSION; ?>)
                    <?php echo PHP_VERSION_ID >= 70000 ? '✓' : '✗'; ?>
                </li>
                <li class="<?php echo extension_loaded('mysqli') ? 'ok' : 'fail'; ?>">
                    MySQLi 扩展 <?php echo extension_loaded('mysqli') ? '✓ 已启用' : '✗ 未启用'; ?>
                </li>
                <li class="<?php echo is_writable(__DIR__) ? 'ok' : 'fail'; ?>">
                    目录可写权限 <?php echo is_writable(__DIR__) ? '✓' : '✗'; ?>
                </li>
                <li class="<?php echo extension_loaded('pdo') ? 'ok' : 'fail'; ?>">
                    PDO 扩展 <?php echo extension_loaded('pdo') ? '✓ 已启用' : '✗ 未启用'; ?>
                </li>
            </ul>

            <?php
            $requirements_ok = PHP_VERSION_ID >= 70000 && extension_loaded('mysqli') && is_writable(__DIR__);
            ?>

            <?php if ($requirements_ok): ?>
            <div class="success">环境检测通过，所有要求均已满足</div>
            <?php else: ?>
            <div class="error">环境检测未通过，请解决上述问题后继续</div>
            <?php endif; ?>

            <div class="buttons">
                <?php if ($requirements_ok): ?>
                <a href="?step=2" class="btn">下一步 →</a>
                <?php else: ?>
                <button class="btn" disabled>下一步 →</button>
                <?php endif; ?>
            </div>

            <?php elseif ($step === 2): ?>
            <h2>第二步：数据库配置</h2>

            <?php if ($error): ?>
            <div class="error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="db_host">数据库主机</label>
                    <input type="text" name="db_host" id="db_host" value="<?php echo e($_SESSION['db_host'] ?? 'localhost'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="db_port">数据库端口</label>
                    <input type="number" name="db_port" id="db_port" value="<?php echo e($_SESSION['db_port'] ?? 3306); ?>" required>
                </div>

                <div class="form-group">
                    <label for="db_user">数据库用户名</label>
                    <input type="text" name="db_user" id="db_user" value="<?php echo e($_SESSION['db_user'] ?? 'root'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="db_pass">数据库密码</label>
                    <input type="password" name="db_pass" id="db_pass" value="<?php echo e($_SESSION['db_pass'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="db_name">数据库名称</label>
                    <input type="text" name="db_name" id="db_name" value="<?php echo e($_SESSION['db_name'] ?? 'tblog'); ?>" required>
                </div>

                <div class="buttons">
                    <a href="?step=1" class="btn btn-secondary">← 上一步</a>
                    <button type="submit" class="btn">测试连接并继续 →</button>
                </div>
            </form>

            <?php elseif ($step === 3): ?>
            <h2>第三步：创建配置文件</h2>

            <?php if ($error): ?>
            <div class="error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <p>正在创建 <code>config.php</code> 配置文件...</p>

            <div class="buttons">
                <a href="?step=2" class="btn btn-secondary">← 上一步</a>
                <a href="?step=4" class="btn">继续 →</a>
            </div>

            <?php elseif ($step === 4): ?>
            <h2>第四步：安装完成</h2>

            <div class="success">🎉 恭喜！TBlog 已安装成功！</div>

            <div class="features">
                <strong>默认管理员信息：</strong>
                <ul>
                    <li>用户名：admin</li>
                    <li>密码：admin123</li>
                    <li style="color:#dc2626;">密码已使用 bcrypt 哈希加密存储</li>
                </ul>
            </div>

            <div class="info-box">
                <strong>⚠️ 安全提示：</strong>安装完成后请前往「设置」页面修改默认密码。
            </div>

            <div class="buttons">
                <a href="?step=5" class="btn">完成安装</a>
            </div>

            <?php elseif ($step === 5): ?>
            <div class="text-center">
                <h2>🎉 安装完成！</h2>
                <p class="mt-4">感谢使用 TBlog</p>
            </div>

            <div class="buttons" style="justify-content: center; margin-top: 30px;">
                <a href="index.php" class="btn">访问博客</a>
                <a href="admin/" class="btn btn-secondary">管理后台</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>