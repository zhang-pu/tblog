CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT '分类名称',
  `slug` VARCHAR(100) NOT NULL COMMENT 'URL别名',
  `description` TEXT,
  `parent_id` INT DEFAULT 0,
  `order_num` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL COMMENT '标题',
  `content` LONGTEXT NOT NULL COMMENT '正文',
  `excerpt` TEXT COMMENT '摘要',
  `slug` VARCHAR(200) NOT NULL COMMENT 'URL别名',
  `category_id` INT DEFAULT 1,
  `template` VARCHAR(50) DEFAULT 'flow',
  `status` ENUM('draft','published') DEFAULT 'published',
  `views` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME DEFAULT NULL,
  KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT NOT NULL,
  `author` VARCHAR(100) NOT NULL COMMENT '评论者',
  `email` VARCHAR(200) DEFAULT '',
  `url` VARCHAR(500) DEFAULT '',
  `content` TEXT NOT NULL,
  `ip` VARCHAR(50) DEFAULT '',
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `settings` (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default data
INSERT INTO `categories` (`name`, `slug`, `description`, `order_num`) VALUES
('技术', 'tech', '技术文章', 1),
('生活', 'life', '生活随笔', 2),
('摄影', 'photo', '摄影作品', 3);

INSERT INTO `settings` (`key`, `value`) VALUES
('site_name', '张璞博客'),
('site_description', '一个简洁优雅的博客'),
('template', 'flow'),
('posts_per_page', '10');