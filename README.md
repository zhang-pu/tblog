# TBlog

一个简洁、优雅的开源博客系统，基于 PHP + MySQL 开发。

🌐 网址：https://tblog.cn

---

## 特性

- ✏️ 富文本编辑器，写作体验流畅
- 🎨 两套主题可选（Flow 浅色 / Magine 深色）
- 💬 评论系统，支持垃圾评论过滤
- 📁 分类管理，文章归档
- 🔐 安全防护：CSRF 防护、XSS 过滤、密码 bcrypt 哈希、评论速率限制
- 📱 响应式布局，适配移动端
- 🛠 轻量高效，无需复杂依赖

---

## 环境要求

- PHP >= 7.0
- MySQL >= 5.6
- mysqli 扩展
- PDO 扩展（可选）

---

## 安装

### 方式一：向导安装

1. 解压源码，上传至网站目录
2. 访问 `http://你的域名/install.php`
3. 按提示填写数据库信息，完成安装
4. 初始管理员账号：`admin` / `admin123`（安装后请立即修改）

### 方式二：手动配置

1. 创建数据库（例如 `tblog`）
2. 导入 `install.sql` 建表
3. 复制并重命名 `config.php.new` 为 `config.php`，填入数据库信息
4. 通过 SQL 设置管理员密码哈希：

```sql
INSERT INTO settings (`key`, value) VALUES ('admin_user', 'admin');
INSERT INTO settings (`key`, value) VALUES ('admin_hash', '$2y$10$YOUR_HASH_HERE');
```

生成哈希：PHP 执行 `echo password_hash('你的密码', PASSWORD_DEFAULT);`

---

## 目录结构

```
tblog/
├── admin/             管理后台
│   ├── assets/        样式资源
│   ├── category.php   分类管理
│   ├── comment.php    评论管理
│   ├── index.php      管理概览
│   ├── login.php      登录页
│   ├── logout.php     退出
│   ├── post.php       文章管理
│   └── settings.php   系统设置
├── include/            核心类库
│   ├── db.php         数据库连接
│   ├── router.php     URL 路由
│   ├── function.common.php
│   ├── function.post.php
│   ├── function.category.php
│   ├── function.comment.php
│   └── HTMLPurifier.simple.php   HTML 净化
├── static/            静态资源
├── templates/         主题模板
│   ├── flow/          Flow 主题（浅色）
│   └── magine/        Magine 主题（深色）
├── index.php          入口文件
├── config.php.new     配置文件模板
├── install.php        安装向导
└── install.sql        数据库建表脚本
```

---

## 管理后台

访问 `http://你的域名/admin/`，使用安装时设置的管理员账号登录。

---

## 主题切换

文章可独立选择模板（Flow / Magine），全站默认模板可在后台「设置」中修改。

---

## 开源协议

MIT License

---

## 更新日志

详见 [CHANGELOG.md](./CHANGELOG.md)

---

## 问题反馈

如有 Bug 或建议，欢迎提交 Issue。

---

**TBlog** — 简洁而不简单。