<?php
/**
 * ZhangPu Blog - Main Entry Point
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Load functions
require_once INCLUDE_PATH . 'function.common.php';
require_once INCLUDE_PATH . 'router.php';

// Get the route from URL
$route = isset($_GET['route']) ? $_GET['route'] : '';
$route = trim($route, '/');

// Route the request
route_request($route);
