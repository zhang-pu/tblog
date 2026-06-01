<?php
/**
 * Database Connection
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
