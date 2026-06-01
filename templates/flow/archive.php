<?php
/**
 * Flow Template - Archive Page
 */
require_once __DIR__ . '/../../config.php';
require_once INCLUDE_PATH . 'function.common.php';
require_once INCLUDE_PATH . 'function.post.php';
require_once INCLUDE_PATH . 'function.category.php';

$base_url = '';
$page_title = '归档';

$db = get_db();
$result = $db->query("SELECT id, title, slug, created_at FROM posts WHERE status = 'published' AND deleted_at IS NULL ORDER BY created_at DESC");
$all_posts = $result->fetch_all(MYSQLI_ASSOC);

// Group by year and month
$archives = [];
foreach ($all_posts as $post) {
    $year = date('Y', strtotime($post['created_at']));
    $month = date('m', strtotime($post['created_at']));
    if (!isset($archives[$year])) {
        $archives[$year] = [];
    }
    if (!isset($archives[$year][$month])) {
        $archives[$year][$month] = [];
    }
    $archives[$year][$month][] = $post;
}

$categories = get_categories();
$recent_posts = get_recent_posts(5);

include __DIR__ . '/header.php';
?>

<div class="main-area">
    <div class="archive-header">
        <h1>📚 文章归档</h1>
        <p class="archive-stats">共 <?php echo count($all_posts); ?> 篇文章</p>
    </div>
    
    <?php if (!empty($archives)): ?>
    <?php foreach ($archives as $year => $months): ?>
    <div class="archive-year">
        <h2 class="year-title"><?php echo $year; ?> 年</h2>
        <?php foreach ($months as $month => $posts): ?>
        <div class="archive-month">
            <h3 class="month-title"><?php echo $month; ?> 月</h3>
            <ul class="archive-list">
                <?php foreach ($posts as $post): ?>
                <li>
                    <span class="archive-date"><?php echo date('m-d', strtotime($post['created_at'])); ?></span>
                    <a href="<?php echo $base_url; ?>post/<?php echo e($post['slug']); ?>" class="archive-title"><?php echo e($post['title']); ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="empty-state">
        <p>暂无文章</p>
    </div>
    <?php endif; ?>
</div>

<aside class="sidebar-area">
    <div class="sidebar-widget">
        <h3>📁 分类</h3>
        <ul class="category-list">
            <?php foreach ($categories as $cat): ?>
            <li>
                <a href="<?php echo $base_url; ?>category/<?php echo e($cat['slug']); ?>">
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
