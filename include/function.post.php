<?php
/**
 * Post Functions
 */

function get_posts($page = 1, $per_page = 10, $category_id = null) {
    $db = get_db();
    $offset = ($page - 1) * $per_page;
    
    $where = "WHERE p.status = 'published' AND p.deleted_at IS NULL";
    $params = [];
    $types = '';
    
    if ($category_id) {
        $where .= " AND p.category_id = ?";
        $params[] = $category_id;
        $types .= 'i';
    }
    
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id 
            $where 
            ORDER BY p.created_at DESC 
            LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $db->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_total_posts($category_id = null) {
    $db = get_db();
    $where = "WHERE status = 'published' AND deleted_at IS NULL";
    $params = [];
    $types = '';
    
    if ($category_id) {
        $where .= " AND category_id = ?";
        $params[] = $category_id;
        $types .= 'i';
    }
    
    $sql = "SELECT COUNT(*) FROM posts $where";
    $stmt = $db->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_row()[0];
}

function get_post_by_slug($slug) {
    $db = get_db();
    $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                          FROM posts p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.slug = ? AND p.status = 'published' AND p.deleted_at IS NULL");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    
    if ($post) {
        $stmt2 = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
        $stmt2->bind_param('i', $post['id']);
        $stmt2->execute();
    }
    
    return $post;
}

function get_post_by_id($id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function create_post($data) {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO posts (title, content, excerpt, slug, category_id, template, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssss', 
        $data['title'], 
        $data['content'], 
        $data['excerpt'], 
        $data['slug'], 
        $data['category_id'], 
        $data['template'], 
        $data['status']
    );
    return $stmt->execute();
}

function update_post($id, $data) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE posts SET title = ?, content = ?, excerpt = ?, slug = ?, category_id = ?, template = ?, status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('sssssssi', 
        $data['title'], 
        $data['content'], 
        $data['excerpt'], 
        $data['slug'], 
        $data['category_id'], 
        $data['template'], 
        $data['status'],
        $id
    );
    return $stmt->execute();
}

function delete_post($id) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE posts SET deleted_at = NOW() WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}

function generate_slug($title) {
    $slug = preg_replace('/[^\x{4e00}-\x{9fa5}a-zA-Z0-9]+/u', '-', $title);
    $slug = trim($slug, '-');
    $slug = strtolower($slug);
    return $slug ?: 'post-' . time();
}

function get_recent_posts($limit = 5) {
    $db = get_db();
    $stmt = $db->prepare("SELECT id, title, slug, created_at FROM posts WHERE status = 'published' AND deleted_at IS NULL ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
