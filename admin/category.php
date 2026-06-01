<?php
/**
 * Admin Category Management
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDE_PATH . 'function.common.php';
require_once INCLUDE_PATH . 'function.category.php';

auth_check();

$message = '';
$edit_category = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'parent_id' => intval($_POST['parent_id'] ?? 0),
        'order_num' => intval($_POST['order_num'] ?? 0)
    ];
    
    if (empty($data['name']) || empty($data['slug'])) {
        $message = '<div class="error">名称和别名不能为空</div>';
    } else {
        if (!empty($_POST['id'])) {
            update_category(intval($_POST['id']), $data);
            $message = '<div class="success">分类已更新</div>';
        } else {
            create_category($data);
            $message = '<div class="success">分类已创建</div>';
        }
    }
}

// Load category for editing
if (!empty($_GET['edit'])) {
    $edit_category = get_category_by_id(intval($_GET['edit']));
}

// Handle delete
if (!empty($_GET['delete'])) {
    $count = get_category_count(intval($_GET['delete']));
    if ($count > 0) {
        $message = '<div class="error">该分类下有 ' . $count . ' 篇文章，无法删除</div>';
    } else {
        delete_category(intval($_GET['delete']));
        $message = '<div class="success">分类已删除</div>';
    }
}

// Get all categories
$categories = get_categories();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分类管理 - ZhangPu Blog</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">🌿 ZhangPu Blog</div>
            <nav>
                <a href="index.php">📊 概览</a>
                <a href="post.php">📝 文章管理</a>
                <a href="category.php" class="active">📁 分类管理</a>
                <a href="comment.php">💬 评论管理</a>
                <a href="settings.php">⚙️ 设置</a>
                <a href="logout.php">🚪 退出</a>
                <a href="../">👁️ 查看博客</a>
            </nav>
        </aside>
        <main class="content">
            <div class="back-link">
                <a href="index.php">← 返回概览</a>
                <a href="?new=1" style="margin-left: 15px;">+ 添加分类</a>
            </div>
            
            <?php echo $message; ?>
            
            <?php if (!empty($_GET['new']) || $edit_category): ?>
            <h1><?php echo $edit_category ? '编辑分类' : '添加分类'; ?></h1>
            <form method="POST">
                <?php if ($edit_category): ?>
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">名称</label>
                    <input type="text" name="name" id="name" value="<?php echo e($edit_category['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="slug">别名</label>
                    <input type="text" name="slug" id="slug" value="<?php echo e($edit_category['slug'] ?? ''); ?>" placeholder="如: tech" required>
                </div>
                
                <div class="form-group">
                    <label for="description">描述</label>
                    <textarea name="description" id="description" rows="3"><?php echo e($edit_category['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="parent_id">父分类</label>
                        <select name="parent_id" id="parent_id">
                            <option value="0">无</option>
                            <?php foreach ($categories as $cat): ?>
                            <?php if (!$edit_category || $cat['id'] != $edit_category['id']): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_category['parent_id'] ?? 0) == $cat['id'] ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="order_num">排序</label>
                        <input type="number" name="order_num" id="order_num" value="<?php echo e($edit_category['order_num'] ?? 0); ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="category.php" class="btn">取消</a>
                </div>
            </form>
            <?php else: ?>
            <h1>分类管理</h1>
            <table class="table">
                <thead>
                    <tr>
                        <th>名称</th>
                        <th>别名</th>
                        <th>描述</th>
                        <th>文章数</th>
                        <th>排序</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo e($cat['name']); ?></td>
                        <td><?php echo e($cat['slug']); ?></td>
                        <td><?php echo e($cat['description']); ?></td>
                        <td><?php echo get_category_count($cat['id']); ?></td>
                        <td><?php echo $cat['order_num']; ?></td>
                        <td>
                            <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-sm">编辑</a>
                            <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除吗?')">删除</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">暂无分类</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
