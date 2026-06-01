/**
 * Lightweight HTML sanitizer using DOMDocument + blacklist
 * For production use, consider installing HTMLPurifier via Composer.
 */
function sanitize_html($html) {
    if (empty($html)) return '';

    // Step 1: Remove script, style, and dangerous tags via regex pre-clean
    $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button)[^>]*>.*?</\1>#is', '', $html);
    $html = preg_replace('/<[^>]+\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html); // remove on* attributes
    $html = preg_replace('/<[^>]+\s+style\s*=\s*["\'][^"\']*["\']/i', '', $html); // remove style attrs

    // Step 2: Use DOMDocument to re-serialize (normalizes malformed HTML)
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . "\n" . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    // Step 3: Walk all elements and enforce whitelist
    $allowed_tags = [
        'h1','h2','h3','h4','h5','h6',
        'p','br','strong','em','u','s','del','sup','sub',
        'blockquote','pre','code',
        'ul','ol','li',
        'a','img',
        'table','thead','tbody','tr','th','td',
        'hr','span',
        'div','section','article','header','footer',
    ];
    $allowed_attrs = [
        'href', 'target', 'src', 'alt', 'width', 'height', 'class', 'id',
    ];

    $xpath = new DOMXPath($dom);
    $all_elements = $xpath->query('//*');
    $to_remove = [];

    foreach ($all_elements as $el) {
        $tag = strtolower($el->nodeName);
        // Remove disallowed tags entirely
        if (!in_array($tag, $allowed_tags)) {
            // Keep children, remove tag wrapper
            foreach ($el->childNodes as $child) {
                $el->parentNode->insertBefore($child->cloneNode(true), $el);
            }
            $to_remove[] = $el;
            continue;
        }
        // Remove disallowed attributes
        $attrs_to_remove = [];
        if ($el->attributes) {
            foreach ($el->attributes as $attr) {
                if (!in_array(strtolower($attr->nodeName), $allowed_attrs)) {
                    $attrs_to_remove[] = $attr;
                }
            }
            foreach ($attrs_to_remove as $attr) {
                $el->removeAttributeNode($attr);
            }
        }
        // Sanitize href/src URLs (allow only http/https/data)
        if ($el->hasAttribute('href')) {
            $href = $el->getAttribute('href');
            if (!preg_match('#^https?://|mailto:|tel:|data:#i', $href)) {
                $el->removeAttribute('href');
            }
        }
        if ($el->hasAttribute('src')) {
            $src = $el->getAttribute('src');
            if (!preg_match('#^https?://|data:#i', $src)) {
                $el->removeAttribute('src');
            }
        }
    }

    foreach ($to_remove as $el) {
        $el->parentNode->removeChild($el);
    }

    $body = $dom->getElementsByTagName('body')->item(0);
    $output = '';
    if ($body) {
        foreach ($body->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }
    }

    return trim($output);
}

/**
 * Auto-detect base URL from request
 */
function get_site_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
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
    // Remove expired entries
    $data = array_filter($data, function($ts) use ($now, $window_seconds) {
        return ($now - $ts) < $window_seconds;
    });
    if (count($data) >= $max_requests) {
        return false;
    }
    $data[] = $now;
    file_put_contents($cache_file, json_encode($data));
    return true;
}