<?php
/**
 * Admin Post Management
 */
require_once __DIR__ . '/../config.php';
require_once INCLUDE_PATH . 'function.common.php';
require_once INCLUDE_PATH . 'function.post.php';
require_once INCLUDE_PATH . 'function.category.php';

auth_check();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$total = get_total_posts();
$total_pages = max(1, ceil($total / $per_page));
$all_posts = get_posts($page, $per_page);

$message = '';
$edit_post = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'slug' => trim($_POST['slug'] ?? '') ?: generate_slug($_POST['title'] ?? ''),
        'category_id' => intval($_POST['category_id'] ?? 1),
        'template' => $_POST['template'] ?? 'flow',
        'status' => $_POST['status'] ?? 'published'
    ];

    // Sanitize rich-text content to prevent XSS
    $data['content'] = sanitize_html($data['content']);

    if (empty($data['title']) || empty($data['content'])) {
        $message = '<div class="error">标题和内容不能为空</div>';
    } else {
        // Check slug uniqueness (only if slug changed or new post)
        $existing = [];
        if (!empty($_POST['id'])) {
            $existing = get_post_by_id(intval($_POST['id'])) ?? [];
        }
        $slug_check = $data['slug'];
        $db = get_db();
        $exclude_id = $existing['id'] ?? 0;
        $stmt = $db->prepare("SELECT id FROM posts WHERE slug = ? AND deleted_at IS NULL AND id != ?");
        $stmt->bind_param('si', $slug_check, $exclude_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $data['slug'] = $data['slug'] . '-' . time();
        }

        if (!empty($_POST['id'])) {
            update_post(intval($_POST['id']), $data);
            $message = '<div class="success">文章已更新</div>';
        } else {
            create_post($data);
            $message = '<div class="success">文章已创建</div>';
        }
    }
}

if (!empty($_GET['edit'])) {
    $edit_post = get_post_by_id(intval($_GET['edit'])) ?? [];
}

if (!empty($_GET['delete'])) {
    csrf_verify('BOTH');
    delete_post(intval($_GET['delete']));
    $message = '<div class="success">文章已删除</div>';
}

$categories = get_categories();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章管理 - ZhangPu Blog</title>
    <link rel="stylesheet" href="assets/admin.css">
    <style>
    .editor-wrap { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; margin-bottom: 20px; }
    .toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 10px 14px;
        border-bottom: 2px solid #e5e5e5;
        background: #fafafa;
    }
    .toolbar button {
        width: 34px; height: 34px;
        border: 1px solid #e0e0e0;
        background: #fff;
        border-radius: 6px;
        cursor: pointer;
        font-size: 15px;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .toolbar button:hover { background: #22c55e; color: #fff; border-color: #22c55e; }
    .toolbar .sep { width: 1px; height: 24px; background: #e0e0e0; margin: 5px 4px; }
    .toolbar select { height: 34px; padding: 0 8px; border: 1px solid #e0e0e0; border-radius: 6px; background: #fff; font-size: 13px; cursor: pointer; }
    .color-wrap { position: relative; }
    .color-picker {
        width: 34px; height: 34px; border: 1px solid #e0e0e0; border-radius: 6px; cursor: pointer; padding: 2px; background: #fff;
    }
    .color-picker::-webkit-color-swatch-wrapper { padding: 2px; }
    .color-picker::-webkit-color-swatch { border: none; border-radius: 4px; }
    #editor {
        min-height: 400px;
        padding: 16px 20px;
        outline: none;
        font-size: 15px;
        line-height: 1.8;
    }
    #editor img { max-width: 100%; height: auto; border-radius: 8px; margin: 10px 0; }
    #editor blockquote { border-left: 4px solid #22c55e; padding-left: 16px; margin: 10px 0; color: #555; }
    #editor pre { background: #1e1e2e; color: #e0e0e8; padding: 16px; border-radius: 8px; overflow-x: auto; font-family: monospace; }
    #editor ul, #editor ol { padding-left: 28px; margin: 8px 0; }
    .footer-bar { padding: 10px 14px; border-top: 1px solid #e5e5e5; background: #fafafa; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #888; }
    .footer-bar .word-count span { color: #22c55e; font-weight: bold; }
    .pagination { margin-top: 20px; display: flex; gap: 4px; align-items: center; }
    .pagination a, .pagination span { padding: 6px 12px; border: 1px solid #e5e5e5; border-radius: 4px; text-decoration: none; color: #333; }
    .pagination a:hover { background: #22c55e; color: #fff; border-color: #22c55e; }
    .pagination .active { background: #22c55e; color: #fff; border-color: #22c55e; }
    .pagination .disabled { color: #ccc; pointer-events: none; }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">🌿 ZhangPu Blog</div>
            <nav>
                <a href="index.php">📊 概览</a>
                <a href="post.php" class="active">📝 文章管理</a>
                <a href="category.php">📁 分类管理</a>
                <a href="comment.php">💬 评论管理</a>
                <a href="settings.php">⚙️ 设置</a>
                <a href="logout.php">🚪 退出</a>
                <a href="../">👁️ 查看博客</a>
            </nav>
        </aside>
        <main class="content">
            <div class="back-link">
                <a href="index.php">← 返回概览</a>
                <a href="?new=1" style="margin-left: 15px;">+ 写新文章</a>
            </div>

            <?php echo $message; ?>

            <?php if (!empty($_GET['new']) || $edit_post): ?>
            <h1><?php echo $edit_post ? '编辑文章' : '写新文章'; ?></h1>
            <form method="POST" class="post-form" onsubmit="return submitForm()">
                <?php echo csrf_field(); ?>
                <?php if ($edit_post): ?>
                <input type="hidden" name="id" value="<?php echo $edit_post['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">标题</label>
                    <input type="text" name="title" id="title" value="<?php echo e($edit_post['title'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="slug">URL别名</label>
                        <input type="text" name="slug" id="slug" value="<?php echo e($edit_post['slug'] ?? ''); ?>" placeholder="auto-generated">
                    </div>
                    <div class="form-group">
                        <label for="category_id">分类</label>
                        <select name="category_id" id="category_id">
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_post['category_id'] ?? 1) == $cat['id'] ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="template">模板</label>
                        <select name="template" id="template">
                            <option value="flow" <?php echo ($edit_post['template'] ?? 'flow') === 'flow' ? 'selected' : ''; ?>>Flow (浅色)</option>
                            <option value="magine" <?php echo ($edit_post['template'] ?? '') === 'magine' ? 'selected' : ''; ?>>Magine (深色)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="excerpt">摘要</label>
                    <textarea name="excerpt" id="excerpt" rows="2"><?php echo e($edit_post['excerpt'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>正文</label>
                    <div class="editor-wrap">
                        <div class="toolbar">
                            <select onchange="formatBlock(this.value)" title="标题">
                                <option value="p">正文</option>
                                <option value="h1">标题1</option>
                                <option value="h2">标题2</option>
                                <option value="h3">标题3</option>
                            </select>
                            <div class="sep"></div>
                            <button type="button" onclick="execCmd('bold')" title="加粗"><b>B</b></button>
                            <button type="button" onclick="execCmd('italic')" title="斜体"><i>I</i></button>
                            <button type="button" onclick="execCmd('underline')" title="下划线"><u>U</u></button>
                            <button type="button" onclick="execCmd('strikeThrough')" title="删除线"><s>S</s></button>
                            <div class="sep"></div>
                            <button type="button" onclick="execCmd('insertUnorderedList')" title="无序列表">•</button>
                            <button type="button" onclick="execCmd('insertOrderedList')" title="有序列表">1.</button>
                            <div class="sep"></div>
                            <div class="color-wrap">
                                <input type="color" class="color-picker" value="#333333" onchange="execForeColor(this.value)" title="文字颜色">
                            </div>
                            <div class="sep"></div>
                            <button type="button" onclick="execCmd('formatBlock', 'blockquote')" title="引用">❝</button>
                            <button type="button" onclick="formatBlock('pre')" title="代码块">&lt;/&gt;</button>
                            <button type="button" onclick="insertLink()" title="链接">🔗</button>
                            <button type="button" onclick="insertImage()" title="图片">🖼️</button>
                            <div class="sep"></div>
                            <button type="button" onclick="execCmd('justifyLeft')" title="左对齐">⬅</button>
                            <button type="button" onclick="execCmd('justifyCenter')" title="居中">⬛</button>
                            <button type="button" onclick="execCmd('justifyRight')" title="右对齐">➡</button>
                            <div class="sep"></div>
                            <button type="button" onclick="execCmd('removeFormat')" title="清除格式">🧹</button>
                        </div>

                        <div id="editor" contenteditable="true" oninput="updateCount()"><?php echo $edit_post['content'] ?? ''; ?></div>

                        <div class="footer-bar">
                            <span>字数：<span class="word-count"><span id="charCount">0</span> 字</span></span>
                        </div>
                    </div>
                    <input type="hidden" name="content" id="content" value="<?php echo e($edit_post['content'] ?? ''); ?>">
                </div>

                <div class="status-toggle">
                    <label>
                        <input type="radio" name="status" value="draft" <?php echo ($edit_post['status'] ?? 'published') === 'draft' ? 'checked' : ''; ?>>
                        草稿
                    </label>
                    <label>
                        <input type="radio" name="status" value="published" <?php echo ($edit_post['status'] ?? 'published') === 'published' ? 'checked' : ''; ?>>
                        发布
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="post.php" class="btn">取消</a>
                </div>
            </form>
            <?php else: ?>
            <h1>文章管理 <small style="font-size:14px;color:#888;">(共 <?php echo $total; ?> 篇)</small></h1>
            <table class="table">
                <thead>
                    <tr>
                        <th>标题</th>
                        <th>分类</th>
                        <th>模板</th>
                        <th>状态</th>
                        <th>浏览</th>
                        <th>发布时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_posts as $post): ?>
                    <tr>
                        <td><?php echo e($post['title']); ?></td>
                        <td><?php echo e($post['category_name']); ?></td>
                        <td><?php echo e($post['template']); ?></td>
                        <td><span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'draft'; ?>"><?php echo $post['status'] === 'published' ? '已发布' : '草稿'; ?></span></td>
                        <td><?php echo $post['views']; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                        <td>
                            <a href="?edit=<?php echo $post['id']; ?>" class="btn btn-sm">编辑</a>
                            <a href="../post/<?php echo e($post['slug']); ?>" target="_blank" class="btn btn-sm btn-link">查看</a>
                            <a href="?delete=<?php echo $post['id']; ?>&csrf_token=<?php echo csrf_token(); ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除吗?')">删除</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($all_posts)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">暂无文章</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">← 上一页</a>
                <?php else: ?>
                <span class="disabled">← 上一页</span>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                if ($start > 1) echo '<a href="?page=1">1</a>';
                if ($start > 2) echo '<span>...</span>';
                for ($i = $start; $i <= $end; $i++) {
                    if ($i == $page) {
                        echo '<span class="active">' . $i . '</span>';
                    } else {
                        echo '<a href="?page=' . $i . '">' . $i . '</a>';
                    }
                }
                if ($end < $total_pages) echo '<span>...</span>';
                if ($end < $total_pages) echo '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>';
                ?>

                <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">下一页 →</a>
                <?php else: ?>
                <span class="disabled">下一页 →</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </main>
    </div>
    <script>
    const editor = document.getElementById('editor');
    const contentInput = document.getElementById('content');

    function execCmd(cmd, val) {
        document.execCommand(cmd, false, val || null);
        editor.focus();
    }

    function execForeColor(color) {
        document.execCommand('foreColor', false, color);
    }

    function formatBlock(tag) {
        if (tag === 'blockquote' || tag === 'pre') {
            execCmd('formatBlock', '<' + tag + '>');
        } else {
            execCmd('formatBlock', '<' + tag + '>');
        }
    }

    function insertLink() {
        const url = prompt('输入链接地址：', 'https://');
        if (url) execCmd('createLink', url);
    }

    function insertImage() {
        const url = prompt('输入图片链接地址：', 'https://');
        if (url) execCmd('insertImage', url);
    }

    function updateCount() {
        const text = editor.innerText.replace(/\s/g, '');
        document.getElementById('charCount').textContent = text.length;
    }

    function submitForm() {
        contentInput.value = editor.innerHTML;
        return true;
    }

    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'b') { e.preventDefault(); execCmd('bold'); }
        if (e.ctrlKey && e.key === 'i') { e.preventDefault(); execCmd('italic'); }
        if (e.ctrlKey && e.key === 'u') { e.preventDefault(); execCmd('underline'); }
    });

    updateCount();
    </script>
</body>
</html>