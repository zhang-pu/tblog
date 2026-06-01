<?php
/**
 * Admin Settings
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDE_PATH . 'function.common.php';

auth_check();

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // Admin credentials change
    if (!empty($_POST['new_username']) || !empty($_POST['new_password'])) {
        $new_username = trim($_POST['new_username'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $current_pass = $_POST['current_password'] ?? '';

        // Verify current password first
        if (!auth_verify($_SESSION['admin_user'] ?? '', $current_pass)) {
            $message = '<div class="error">当前密码错误</div>';
        } else {
            if (!empty($new_username)) {
                update_setting('admin_user', $new_username);
            }
            if (!empty($new_password)) {
                set_admin_password($new_password);
            }
            $message = '<div class="success">账号信息已更新（下次登录生效）</div>';
        }
    } else {
        // Site settings
        update_setting('site_name', trim($_POST['site_name'] ?? ''));
        update_setting('site_description', trim($_POST['site_description'] ?? ''));
        update_setting('template', $_POST['template'] ?? 'flow');
        update_setting('posts_per_page', max(1, intval($_POST['posts_per_page'] ?? 10)));
        $message = '<div class="success">设置已保存</div>';
    }
}

// Load settings
$site_name = get_setting('site_name', '张璞博客');
$site_description = get_setting('site_description', '一个简洁优雅的博客');
$template = get_setting('template', 'flow');
$posts_per_page = get_setting('posts_per_page', '10');
$admin_user = get_admin_user();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>设置 - ZhangPu Blog</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">🌿 ZhangPu Blog</div>
            <nav>
                <a href="index.php">📊 概览</a>
                <a href="post.php">📝 文章管理</a>
                <a href="category.php">📁 分类管理</a>
                <a href="comment.php">💬 评论管理</a>
                <a href="settings.php" class="active">⚙️ 设置</a>
                <a href="logout.php">🚪 退出</a>
                <a href="../">👁️ 查看博客</a>
            </nav>
        </aside>
        <main class="content">
            <div class="back-link">
                <a href="index.php">← 返回概览</a>
            </div>

            <?php echo $message; ?>

            <h1>修改登录账号</h1>
            <form method="POST" class="settings-form" style="margin-bottom: 40px; max-width: 600px;">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="current_password">当前密码（验证身份）</label>
                    <input type="password" name="current_password" id="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_username">用户名</label>
                    <input type="text" name="new_username" id="new_username" value="<?php echo e($admin_user); ?>" placeholder="留空则不修改">
                </div>
                <div class="form-group">
                    <label for="new_password">新密码</label>
                    <input type="password" name="new_password" id="new_password" placeholder="留空则不修改，建议8位以上">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">修改账号</button>
                </div>
            </form>

            <h1>网站设置</h1>
            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="site_name">网站名称</label>
                    <input type="text" name="site_name" id="site_name" value="<?php echo e($site_name); ?>">
                </div>

                <div class="form-group">
                    <label for="site_description">网站描述</label>
                    <textarea name="site_description" id="site_description" rows="3"><?php echo e($site_description); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="template">默认模板</label>
                    <select name="template" id="template">
                        <option value="flow" <?php echo $template === 'flow' ? 'selected' : ''; ?>>Flow (浅色主题)</option>
                        <option value="magine" <?php echo $template === 'magine' ? 'selected' : ''; ?>>Magine (深色主题)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="posts_per_page">每页文章数</label>
                    <input type="number" name="posts_per_page" id="posts_per_page" value="<?php echo e($posts_per_page); ?>" min="1" max="50">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存设置</button>
                </div>
            </form>

            <h2 style="margin-top: 40px;">系统信息</h2>
            <table class="table">
                <tr>
                    <td>PHP 版本</td>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <td>MySQL 客户端版本</td>
                    <td><?php echo mysqli_get_client_info(); ?></td>
                </tr>
                <tr>
                    <td>服务器软件</td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                </tr>
                <tr>
                    <td>当前登录用户</td>
                    <td><?php echo e($admin_user); ?></td>
                </tr>
            </table>
        </main>
    </div>
</body>
</html>