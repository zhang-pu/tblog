<?php
/**
 * URL Router
 */

function route_request($route) {
    $route = trim($route, '/');
    
    if (empty($route) || $route === 'index.php') {
        include TEMPLATE_PATH . 'flow/index.php';
        return;
    }
    
    // Admin routes
    if (strpos($route, 'admin/') === 0) {
        $admin_route = substr($route, 6);
        
        if (empty($admin_route) || $admin_route === 'index.php') {
            include ROOT_PATH . 'admin/index.php';
        } elseif ($admin_route === 'login') {
            include ROOT_PATH . 'admin/login.php';
        } elseif ($admin_route === 'logout') {
            include ROOT_PATH . 'admin/logout.php';
        } elseif ($admin_route === 'post') {
            include ROOT_PATH . 'admin/post.php';
        } elseif ($admin_route === 'category') {
            include ROOT_PATH . 'admin/category.php';
        } elseif ($admin_route === 'comment') {
            include ROOT_PATH . 'admin/comment.php';
        } elseif ($admin_route === 'settings') {
            include ROOT_PATH . 'admin/settings.php';
        } else {
            http_response_code(404);
            echo '管理员页面不存在';
        }
        return;
    }
    
    // Post routes
    if (strpos($route, 'post/') === 0) {
        $slug = substr($route, 5);
        $_GET['slug'] = $slug;
        include TEMPLATE_PATH . 'flow/post.php';
        return;
    }
    
    // Category routes
    if (strpos($route, 'category/') === 0) {
        $slug = substr($route, 9);
        $_GET['slug'] = $slug;
        include TEMPLATE_PATH . 'flow/category.php';
        return;
    }
    
    // Archive route
    if ($route === 'archive') {
        include TEMPLATE_PATH . 'flow/archive.php';
        return;
    }
    
    // Check for static files
    if (file_exists(ROOT_PATH . $route)) {
        return false; // Let Apache serve it
    }
    
    // 404
    http_response_code(404);
    echo '页面不存在';
    return true;
}
