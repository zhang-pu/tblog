<?php
/**
 * Common Functions
 */

function get_db() {
    static $db = null;
    if ($db === null) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($db->connect_error) {
            die('数据库连接失败: ' . $db->connect_error);
        }
        $db->set_charset('utf8mb4');
    }
    return $db;
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * CSRF Protection
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify($method = 'POST') {
    if ($method === 'POST' || $method === 'BOTH') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo 'CSRF token mismatch';
            exit;
        }
    }
}

/**
 * Authentication
 */
function get_admin_user() {
    return get_setting('admin_user', 'admin');
}

function get_admin_hash() {
    return get_setting('admin_hash', '');
}

function verify_admin_password($password) {
    $hash = get_admin_hash();
    if (empty($hash)) {
        // Fallback: no hash set yet, use legacy plain-text check
        // After first settings save this branch is never reached
        return false;
    }
    return password_verify($password, $hash);
}

function set_admin_password($password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    update_setting('admin_hash', $hash);
}

function auth_check() {
    if (empty($_SESSION['admin'])) {
        redirect('login.php');
    }
}

function auth_verify($username, $password) {
    if ($username !== get_admin_user()) {
        return false;
    }
    return verify_admin_password($password);
}

function get_setting($key, $default = '') {
    $db = get_db();
    $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['value'];
    }
    return $default;
}

function update_setting($key, $value) {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
    $stmt->bind_param('ss', $key, $value);
    return $stmt->execute();
}

function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) return '刚刚';
    if ($diff < 3600) return floor($diff / 60) . '分钟前';
    if ($diff < 86400) return floor($diff / 3600) . '小时前';
    if ($diff < 2592000) return floor($diff / 86400) . '天前';
    return date('Y-m-d', $timestamp);
}

/**
 * HTML sanitizer - prevent XSS in rich-text content
 * Uses DOMDocument + whitelist approach (no external dependencies needed)
 */
function sanitize_html($html) {
    if (empty($html)) return '';

    // Step 1: Remove dangerous tags via regex pre-clean (before DOM parsing)
    $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|select|textarea)[^>]*>.*?</\1>#is', '', $html);

    // Step 2: Remove event-handler attributes (onclick, onerror, etc.)
    $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);

    // Step 3: Use DOMDocument to normalize and re-serialize
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    // Wrap in a div so we can use loadHTML on fragment
    $dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . "\n" . '<div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    // Whitelist of allowed tags and attributes
    $allowed_tags = [
        'h1','h2','h3','h4','h5','h6',
        'p','br','hr',
        'strong','b','em','i','u','s','del','sup','sub','mark',
        'blockquote','pre','code',
        'ul','ol','li',
        'a','img',
        'table','thead','tbody','tfoot','tr','th','td',
        'div','span','section','article','header','footer','aside',
    ];
    $allowed_attrs = [
        'href', 'src', 'alt', 'title', 'width', 'height',
        'class', 'id', 'style',
    ];

    $xpath = new DOMXPath($dom);
    $all = $xpath->query('//*');
    $remove = [];

    foreach ($all as $el) {
        $tag = strtolower($el->nodeName);
        if (!in_array($tag, $allowed_tags)) {
            // Replace tag with its children
            foreach ($el->childNodes as $child) {
                $el->parentNode->insertBefore($child->cloneNode(true), $el);
            }
            $remove[] = $el;
            continue;
        }
        // Filter attributes
        $bad = [];
        if ($el->attributes) {
            foreach ($el->attributes as $attr) {
                $name = strtolower($attr->nodeName);
                if (!in_array($name, $allowed_attrs)) {
                    $bad[] = $attr;
                    continue;
                }
                // Sanitize URL attributes
                if (in_array($name, ['href', 'src'])) {
                    $val = $attr->nodeValue;
                    if (!preg_match('#^(https?://|mailto:|tel:|data:)#i', $val)) {
                        // Remove javascript: and other dangerous protocols
                        if (preg_match('#^javascript:#i', $val)) {
                            $bad[] = $attr;
                            continue;
                        }
                        // For src/href, if it doesn't match safe schemes, clear it
                        if (!preg_match('#^https?://|data:#i', $val)) {
                            $bad[] = $attr;
                        }
                    }
                }
            }
            foreach ($bad as $attr) {
                $el->removeAttributeNode($attr);
            }
        }
    }

    foreach ($remove as $el) {
        $el->parentNode->removeChild($el);
    }

    // Extract content from the wrapper div
    $out = '';
    foreach ($dom->getElementsByTagName('div')->item(0)->childNodes as $node) {
        $out .= $dom->saveHTML($node);
    }
    return trim($out);
}

/**
 * Rate limiting for comment submissions
 */
function rate_limit($key, $max_requests = 5, $window_seconds = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cache_file = '/tmp/rate_limit_' . md5($key . '_' . $ip) . '.json';
    $now = time();
    $data = [];
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true) ?: [];
    }
    $data = array_values(array_filter($data, function($ts) use ($now, $window_seconds) {
        return ($now - $ts) < $window_seconds;
    }));
    if (count($data) >= $max_requests) {
        return false;
    }
    $data[] = $now;
    @file_put_contents($cache_file, json_encode($data));
    return true;
}

/**
 * Auto-detect site URL from current request
 */
function get_site_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = $scheme . '://' . $host;
    $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    return rtrim($base . ($script === '/' || $script === '\\' ? '' : $script), '/');
}