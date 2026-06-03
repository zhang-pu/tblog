<?php
/**
 * Flow Template - Header
 */
$base_url = '/';
require_once __DIR__ . '/../../config.php';
require_once INCLUDE_PATH . 'function.common.php';
require_once INCLUDE_PATH . 'function.category.php';

$site_name = get_setting('site_name', '张璞博客');
$site_description = get_setting('site_description', '一个简洁优雅的博客');
$categories = get_categories();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? e($page_title) . ' - ' : ''; ?><?php echo e($site_name); ?></title>
    <meta name="description" content="<?php echo e($site_description); ?>">
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>static/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>templates/flow/static/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="<?php echo $base_url ?? ''; ?>" class="logo">🌿 <?php echo e($site_name); ?></a>
                <nav class="main-nav">
                    <a href="<?php echo $base_url ?? ''; ?>">首页</a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="<?php echo ($base_url ?? '') . 'category/' . e($cat['slug']); ?>"><?php echo e($cat['name']); ?></a>
                    <?php endforeach; ?>
                    <a href="<?php echo ($base_url ?? '') . 'admin/'; ?>" class="admin-link">管理</a>
                </nav>
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
            </div>
        </div>
    </header>
    <main class="main-content">
        <div class="container">
            <div class="content-wrapper">
