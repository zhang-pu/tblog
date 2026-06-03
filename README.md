# TBlog

一个简洁、优雅的开源博客系统，基于 PHP + MySQL 开发。

🌐 官网：https://tblog.cn
💻 源码：https://github.com/zhang-pu/tblog
📦 Releases：https://github.com/zhang-pu/tblog/releases
📝 更新日志：[CHANGELOG.md](./CHANGELOG.md)

---

## ✨ 特性

- ✏️ 富文本编辑器，写作体验流畅
- 🎨 两套主题可选（Flow 浅色 / Magine 深色）
- 💬 评论系统，支持垃圾评论过滤
- 📁 分类管理，文章归档
- 🔐 安全防护：CSRF 防护、XSS 过滤、密码 bcrypt 哈希、评论速率限制
- 📱 响应式布局，适配移动端
- 🛠 轻量高效，无需复杂依赖

---

## 📋 环境要求

- PHP >= 7.0（推荐 7.4 / 8.0+）
- MySQL >= 5.6（推荐 5.7 / 8.0）
- mysqli 扩展
- PDO 扩展（可选）

---

## 🚀 快速安装

### 方式一：向导安装（推荐）

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

## 📁 目录结构

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
│   ├── db.php         数据库连接 + 版本常量
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
├── install.sql        数据库建表脚本
├── CHANGELOG.md       更新日志
├── README.md          本文件
└── LICENSE            MIT 协议
```

---

## 🔐 管理后台

访问 `http://你的域名/admin/`，使用安装时设置的管理员账号登录。

> 忘记密码？使用 [reset.zip](https://tblog.cn/reset.zip) 工具一键重置。

---

## 🎨 主题切换

文章可独立选择模板（Flow / Magine），全站默认模板可在后台「设置」中修改。

---

## 🔧 系统设置

登录后台 → 「设置」可配置：

- 站点名称 / 站点描述
- 默认主题（flow / magine）
- 每页文章数

---

## 🐛 常见问题

### 500 Internal Server Error
- 检查 `config.php` 数据库配置
- 确认 `config.php` 可写（安装时）
- 查看 PHP 错误日志：`/var/log/php*-fpm.log`

### 主题不生效
- 硬刷新浏览器：`Ctrl+Shift+R` / `Cmd+Shift+R`
- 清除 CDN/浏览器缓存

### 性能优化
- 启用 PHP OPcache
- 用 CDN 缓存静态资源
- 文章数 > 1000 时考虑分页

详见 [FAQ 文档](https://tblog.cn/docs/faq.html)。

---

## 📦 升级

```bash
# 1. 备份
cp -r /www/wwwroot/your-blog /path/to/backup/

# 2. 覆盖新文件（保留 config.php 和 uploads/）
rsync -av --exclude='config.php' --exclude='uploads/' \
      TBlog_v1.2.1/ /www/wwwroot/your-blog/

# 3. 修复权限
chown -R www:www /www/wwwroot/your-blog
```

完整更新日志：[CHANGELOG.md](./CHANGELOG.md)

---

## 🛡 安全建议

- 安装后立即删除 `install.php` 和 `install.sql`
- 修改默认管理员密码为强密码（12 位以上）
- 不要将 `config.php` 设为 777 权限（推荐 644）
- 定期升级到最新版本
- 启用 HTTPS（Let's Encrypt 免费）

---

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

- Bug 报告：https://github.com/zhang-pu/tblog/issues
- 功能建议：https://github.com/zhang-pu/tblog/issues
- 代码贡献：Fork → 修改 → Pull Request

---

## 📄 开源协议

MIT License - 详见 [LICENSE](./LICENSE)

---

## 👨‍💻 作者

**Zhang Pu** - [https://zhangpu.dev](https://zhangpu.dev)

---

**TBlog** — 简洁而不简单。
