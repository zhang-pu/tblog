<?php
/**
 * Magine Template - Single Post (Dark Theme)
 */
require_once __DIR__ . '/../../config.php';
require_once INCLUDE_PATH . 'function.common.php';
require_once INCLUDE_PATH . 'function.post.php';
require_once INCLUDE_PATH . 'function.comment.php';

$base_url = '';
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    http_response_code(404);
    echo '文章不存在';
    exit;
}

$post = get_post_by_slug($slug);

if (!$post) {
    http_response_code(404);
    echo '文章不存在';
    exit;
}

$page_title = $post['title'];
$comments = get_comments($post['id']);

$comment_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && $_POST['action'] === 'add_comment') {
    $data = [
        'post_id' => $post['id'],
        'author' => trim($_POST['author'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'url' => trim($_POST['url'] ?? ''),
        'content' => trim($_POST['content'] ?? '')
    ];
    
    if (empty($data['author']) || empty($data['content'])) {
        $comment_message = '<div class="comment-error">请填写昵称和评论内容</div>';
    } else {
        if (create_comment($data)) {
            $comment_message = '<div class="comment-success">评论已提交，等待审核</div>';
            $data = ['post_id' => $post['id'], 'author' => '', 'email' => '', 'url' => '', 'content' => ''];
        } else {
            $comment_message = '<div class="comment-error">评论提交失败</div>';
        }
    }
} else {
    $data = ['post_id' => $post['id'], 'author' => '', 'email' => '', 'url' => '', 'content' => ''];
}

include __DIR__ . '/header.php';
?>

<article class="post-single">
    <header class="post-header">
        <div class="post-meta">
            <a href="<?php echo $base_url; ?>category/<?php echo e($post['category_slug']); ?>" class="category-tag"><?php echo e($post['category_name']); ?></a>
            <span class="post-date"><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></span>
            <span class="post-views">👁 <?php echo $post['views']; ?> 次阅读</span>
        </div>
        <h1 class="post-title"><?php echo e($post['title']); ?></h1>
    </header>
    
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
</article>

<section class="comments-section">
    <h3>💬 评论 (<?php echo count($comments); ?>)</h3>
    
    <?php if (!empty($comment_message)): ?>
    <?php echo $comment_message; ?>
    <?php endif; ?>
    
    <div class="comment-list">
        <?php if (!empty($comments)): ?>
        <?php foreach ($comments as $comment): ?>
        <div class="comment-item">
            <div class="comment-author"><?php echo e($comment['author']); ?></div>
            <div class="comment-date"><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></div>
            <div class="comment-content"><?php echo e($comment['content']); ?></div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p class="no-comments">暂无评论，来说两句吧~</p>
        <?php endif; ?>
    </div>
    
    <form method="POST" class="comment-form">
        <input type="hidden" name="action" value="add_comment">
        <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
        <h4>发表评论</h4>
        <div class="form-row">
            <input type="text" name="author" placeholder="昵称 *" value="<?php echo e($data['author']); ?>" required>
            <input type="email" name="email" placeholder="邮箱" value="<?php echo e($data['email']); ?>">
        </div>
        <input type="url" name="url" placeholder="网站" value="<?php echo e($data['url']); ?>">
        <textarea name="content" rows="4" placeholder="评论内容 *" required><?php echo e($data['content']); ?></textarea>
        <button type="submit" class="btn-submit">提交评论</button>
    </form>
</section>

<?php include __DIR__ . '/footer.php'; ?>
