<?php
/**
 * Category Functions
 */

function get_categories() {
    $db = get_db();
    $result = $db->query("SELECT * FROM categories ORDER BY order_num ASC, id ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_category_by_slug($slug) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_category_by_id($id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function create_category($data) {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO categories (name, slug, description, parent_id, order_num) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssii', $data['name'], $data['slug'], $data['description'], $data['parent_id'], $data['order_num']);
    return $stmt->execute();
}

function update_category($id, $data) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, parent_id = ?, order_num = ? WHERE id = ?");
    $stmt->bind_param('sssiii', $data['name'], $data['slug'], $data['description'], $data['parent_id'], $data['order_num'], $id);
    return $stmt->execute();
}

function delete_category($id) {
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}

function get_category_count($category_id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ? AND status = 'published' AND deleted_at IS NULL");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_row()[0];
}
