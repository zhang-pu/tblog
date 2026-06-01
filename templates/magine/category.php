<?php
/**
 * Magine Template - Category Page (Dark Theme)
 */
require_once __DIR__ . '/../../config.php';
require_once INCLUDE_PATH . 'function.common.php';
require_once INCLUDE_PATH . 'function.post.php';
require_once INCLUDE_PATH . 'function.category.php';

$base_url = '';
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    http_response_code(404);
    echo '分类不存在';
    exit;
}

$category = get_category_by_slug($slug);

if (!$category) {
    http_response_code(404);
    echo '分类不存在';
    exit;
}

$page_title = $category['name'];
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = intval(get_setting('posts_per_page', '10'));

$posts = get_posts($page, $per_page, $category['id']);
$total_posts = get_category_count($category['id']);
$total_pages = ceil($total_posts / $per_page);

$recent_posts = get_recent_posts(5);
$categories = get_categories();

include __DIR__ . '/header.php';
?>

<div class="main-area">
    <div class="category-header">
        <h1>📁 <?php echo e($category['name']); ?></h1>
        <?php if ($category['description']): ?>
        <p class="category-desc"><?php echo e($category['description']); ?></p>
        <?php endif; ?>
    </div>
    
    <?php if (empty($posts)): ?>
    <div class="empty-state">
        <p>该分类下暂无文章</p>
    </div>
    <?php else: ?>
    <?php foreach ($posts as $post): ?>
    <article class="post-card">
        <div class="post-meta">
            <span class="post-date"><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></span>
            <span class="post-views">👁 <?php echo $post['views']; ?></span>
        </div>
        <h2 class="post-title"><a href="<?php echo $base_url; ?>post/<?php echo e($post['slug']); ?>"><?php echo e($post['title']); ?></a></h2>
        <?php if ($post['excerpt']): ?>
        <p class="post-excerpt"><?php echo e($post['excerpt']); ?></p>
        <?php endif; ?>
    </article>
    <?php endforeach; ?>
    
    <?php if ($total_pages > 1): ?>
    <nav class="pagination">
        <?php if ($page > 1): ?>
        <a href="?slug=<?php echo e($slug); ?>&page=<?php echo $page - 1; ?>" class="prev">← 上一页</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
        <a href="?slug=<?php echo e($slug); ?>&page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
        <a href="?slug=<?php echo e($slug); ?>&page=<?php echo $page + 1; ?>" class="next">下一页 →</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>

<aside class="sidebar-area">
    <div class="sidebar-widget">
        <h3>📁 分类</h3>
        <ul class="category-list">
            <?php foreach ($categories as $cat): ?>
            <li>
                <a href="<?php echo $base_url; ?>category/<?php echo e($cat['slug']); ?>" class="<?php echo $cat['id'] === $category['id'] ? 'active' : ''; ?>">
                    <span class="cat-name"><?php echo e($cat['name']); ?></span>
                    <span class="cat-count"><?php echo get_category_count($cat['id']); ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="sidebar-widget">
        <h3>📝 最新文章</h3>
        <ul class="recent-list">
            <?php foreach ($recent_posts as $recent): ?>
            <li><a href="<?php echo $base_url; ?>post/<?php echo e($recent['slug']); ?>"><?php echo e($recent['title']); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</aside>

<?php include __DIR__ . '/footer.php'; ?>
