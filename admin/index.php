<?php
/**
 * Admin Dashboard
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDE_PATH . 'function.common.php';
require_once INCLUDE_PATH . 'function.post.php';
require_once INCLUDE_PATH . 'function.category.php';
require_once INCLUDE_PATH . 'function.comment.php';

auth_check();

$total_posts = get_total_posts();
$categories = get_categories();
$total_comments = get_comment_count();
$recent_posts = get_posts(1, 5);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - ZhangPu Blog</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">🌿 ZhangPu Blog</div>
            <nav>
                <a href="index.php" class="active">📊 概览</a>
                <a href="post.php">📝 文章管理</a>
                <a href="category.php">📁 分类管理</a>
                <a href="comment.php">💬 评论管理</a>
                <a href="settings.php">⚙️ 设置</a>
                <a href="logout.php">🚪 退出</a>
                <a href="../">👁️ 查看博客</a>
            </nav>
        </aside>
        <main class="content">
            <h1>管理后台</h1>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $total_posts; ?></div>
                        <div class="stat-label">文章总数</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📁</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo count($categories); ?></div>
                        <div class="stat-label">分类数量</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💬</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $total_comments; ?></div>
                        <div class="stat-label">评论总数</div>
                    </div>
                </div>
            </div>

            <h2>最新文章</h2>
            <div class="actions">
                <a href="post.php?new=1" class="btn btn-primary">写新文章</a>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>标题</th>
                        <th>分类</th>
                        <th>状态</th>
                        <th>发布时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_posts as $post): ?>
                    <tr>
                        <td><?php echo e($post['title']); ?></td>
                        <td><?php echo e($post['category_name']); ?></td>
                        <td><span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'draft'; ?>"><?php echo $post['status'] === 'published' ? '已发布' : '草稿'; ?></span></td>
                        <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                        <td>
                            <a href="post.php?edit=<?php echo $post['id']; ?>" class="btn btn-sm">编辑</a>
                            <a href="../post/<?php echo e($post['slug']); ?>" target="_blank" class="btn btn-sm btn-link">查看</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
