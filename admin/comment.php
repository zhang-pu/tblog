<?php
/**
 * Admin Comment Management
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDE_PATH . 'function.common.php';
require_once INCLUDE_PATH . 'function.comment.php';

auth_check();

$message = '';

// Handle status update
if (!empty($_POST['action'])) {
    csrf_verify();
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        if ($_POST['action'] === 'approve') {
            update_comment_status($id, 'approved');
            $message = '<div class="success">评论已通过</div>';
        } elseif ($_POST['action'] === 'reject') {
            update_comment_status($id, 'rejected');
            $message = '<div class="success">评论已拒绝</div>';
        } elseif ($_POST['action'] === 'delete') {
            delete_comment($id);
            $message = '<div class="success">评论已删除</div>';
        }
    }
}

// Filter
$status_filter = $_GET['status'] ?? null;
$comments = get_all_comments($status_filter);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>评论管理 - ZhangPu Blog</title>
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
                <a href="comment.php" class="active">💬 评论管理</a>
                <a href="settings.php">⚙️ 设置</a>
                <a href="logout.php">🚪 退出</a>
                <a href="../">👁️ 查看博客</a>
            </nav>
        </aside>
        <main class="content">
            <div class="back-link">
                <a href="index.php">← 返回概览</a>
            </div>

            <?php echo $message; ?>

            <h1>评论管理</h1>

            <div class="filter-tabs">
                <a href="comment.php" class="<?php echo !$status_filter ? 'active' : ''; ?>">全部</a>
                <a href="comment.php?status=pending" class="<?php echo $status_filter === 'pending' ? 'active' : ''; ?>">待审核</a>
                <a href="comment.php?status=approved" class="<?php echo $status_filter === 'approved' ? 'active' : ''; ?>">已通过</a>
                <a href="comment.php?status=rejected" class="<?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">已拒绝</a>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>文章</th>
                        <th>作者</th>
                        <th>内容</th>
                        <th>状态</th>
                        <th>时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><a href="../post/<?php echo e($comment['post_slug']); ?>" target="_blank"><?php echo e($comment['post_title']); ?></a></td>
                        <td>
                            <?php echo e($comment['author']); ?>
                            <?php if ($comment['email']): ?>
                            <br><small style="color:#999;"><?php echo e($comment['email']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="max-width: 200px;">
                            <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($comment['content']); ?></div>
                        </td>
                        <td><span class="badge badge-<?php echo $comment['status']; ?>"><?php
                            $status_map = ['pending'=>'待审核','approved'=>'已通过','rejected'=>'已拒绝'];
                            echo $status_map[$comment['status']];
                        ?></span></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></td>
                        <td>
                            <?php if ($comment['status'] === 'pending'): ?>
                            <form method="POST" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-sm">通过</button>
                            </form>
                            <?php endif; ?>
                            <?php if ($comment['status'] !== 'rejected'): ?>
                            <form method="POST" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-sm">拒绝</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('确定要删除吗?')">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-sm btn-danger">删除</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($comments)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">暂无评论</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
    <style>
        .filter-tabs {
            margin-bottom: 20px;
        }
        .filter-tabs a {
            display: inline-block;
            padding: 8px 16px;
            margin-right: 10px;
            background: #e5e5e5;
            color: #666;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }
        .filter-tabs a:hover,
        .filter-tabs a.active {
            background: #22c55e;
            color: #fff;
        }
    </style>
</body>
</html>