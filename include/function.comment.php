<?php
/**
 * Comment Functions
 */

function get_comments($post_id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC");
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_all_comments($status = null) {
    $db = get_db();
    if ($status) {
        $stmt = $db->prepare("SELECT c.*, p.title as post_title, p.slug as post_slug
                              FROM comments c
                              LEFT JOIN posts p ON c.post_id = p.id
                              WHERE c.status = ?
                              ORDER BY c.created_at DESC");
        $stmt->bind_param('s', $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $result = $db->query("SELECT c.*, p.title as post_title, p.slug as post_slug
                            FROM comments c
                            LEFT JOIN posts p ON c.post_id = p.id
                            ORDER BY c.created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Spam keywords (case-insensitive)
function is_spam_content($content, $author = '', $url = '') {
    $spam_keywords = [
        'casino', 'viagra', 'cialis', 'loan', 'mortgage', 'bitcoin',
        'cheap jerseys', 'nfl jerseys', 'michael kors outlet',
        'louis vuitton', 'ugg boots', 'pandora', 'tiffany',
        'viagra', 'cialis', 'levitra', 'cialis',
        'adult', 'porn', 'nude',
        'essay', 'dissertation', 'thesis',
        'backlink', 'sexxx', 'fuck',
    ];

    $check = strtolower($author . ' ' . $content . ' ' . $url);
    foreach ($spam_keywords as $keyword) {
        if (strpos($check, strtolower($keyword)) !== false) {
            return true;
        }
    }
    return false;
}

function create_comment($data) {
    // Rate limiting: max 5 comments per minute per IP
    if (!rate_limit('comment', 5, 60)) {
        return false;
    }

    // Honeypot check
    if (!empty($data['website'])) {
        return false; // Bot detected
    }

    // Keyword spam check
    if (is_spam_content($data['content'], $data['author'], $data['url'])) {
        return false;
    }

    $db = get_db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $db->prepare("INSERT INTO comments (post_id, author, email, url, content, ip, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param('isssss', $data['post_id'], $data['author'], $data['email'], $data['url'], $data['content'], $ip);
    return $stmt->execute();
}

function update_comment_status($id, $status) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE comments SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    return $stmt->execute();
}

function delete_comment($id) {
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}

function get_comment_count($status = null) {
    $db = get_db();
    if ($status) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE status = ?");
        $stmt->bind_param('s', $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0];
    } else {
        $result = $db->query("SELECT COUNT(*) FROM comments");
        return $result->fetch_row()[0];
    }
}