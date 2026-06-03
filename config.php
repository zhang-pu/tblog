<?php
/**
 * ZhangPu Blog - Configuration
 */
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'zhangpu_blog');
define('DB_PASS', 'rHzj9EWStT6xCcMD');
define('DB_NAME', 'zhangpu_blog');
define('DB_PREFIX', '');

// Site settings
define('SITE_NAME', 'ZhangPu Blog');

// Path constants
define('ROOT_PATH', __DIR__ . '/');
define('INCLUDE_PATH', ROOT_PATH . 'include/');
define('TEMPLATE_PATH', ROOT_PATH . 'templates/');

// Admin credentials stored hashed in DB; fall back to env/local config if needed
// To set initial password: go to /admin/settings and change it there
session_start();