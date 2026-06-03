# 更新日志

所有版本更新记录均在此文件更新。

---

## [v1.2.1] - 2026-06-03

### 🐛 Bug 修复

- ✅ 修复发布/编辑文章时 `mysqli_stmt::bind_param(): Argument #3 cannot be passed by reference` 致命错误
  - 原因：`bind_param()` 第三个参数传入了表达式 `$existing['id'] ?? 0`，PHP 7.4+ 不允许以引用方式传递表达式
  - 修复：先赋值到变量 `$exclude_id`，再传给 `bind_param()`
- ✅ 修复 `get_post_by_id()` 在 ID 不存在时返回 `null`，后续 `$post['xxx']` 触发 PHP 8 warning
  - 修复：找不到记录时返回空数组 `[]`
- ✅ 修复 `get_category_by_id()` 同上问题
- ✅ 修复 `admin/post.php` 编辑文章时 `$edit_post` 未做 null 防御
- ✅ 修复 `admin/category.php` 编辑分类时 `$edit_category` 未做 null 防御

### ✨ 新增

- ✅ 新增 `TBLOG_VERSION` 常量（`include/db.php`），统一版本号管理
- ✅ 管理后台「系统信息」页面新增 TBlog 版本显示

### 🔧 改进

- 防御性编程：`fetch_assoc()` 返回 null 时统一改为返回 `[]`，避免上层调用链产生 warning

---

## [v1.2.0] - 2026-05-31

### 定时任务 + 远程备份

- ⏰ 完整的定时任务管理（创建/删除/启用/禁用/立即执行）
- ✅ Cron 表达式格式验证（分/时/日/月/周五段校验）
- 💾 远程 rsync 备份（SSH 主机/端口/用户/密钥）
- 🔗 远程连接测试工具（部署前验证连通性）
- 📊 备份统计面板（总数/本地大小/远程大小/磁盘占用）
- 📁 文件管理：浏览/上传/编辑/删除/新建目录/权限显示
- 🔐 SSL 证书列表（到期天数/状态指示灯）
- 🔄 SSL 单个续期 + 批量续期
- 🛡️ 自动漏洞修复（每日凌晨3点执行）

---

## [v1.0.0] - 2024-05-31

### 首发版本

- 🚀 完整的博客系统，包含文章发布、分类管理、评论系统
- 🎨 双主题支持：Flow（浅色）和 Magine（深色）
- 🔐 安全特性：CSRF 防护、XSS 过滤、bcrypt 密码哈希、评论速率限制
- 📱 响应式设计，支持移动端访问
- ✏️ 富文本编辑器，支持 Markdown 快捷键
- 🛡️ 垃圾评论过滤（蜜罐 + 关键词检测）
- ⚙️ 安装向导，开箱即用

---

## 升级说明

### 从 v1.2.0 升级到 v1.2.1

无破坏性改动，直接覆盖 PHP 文件即可：

```bash
# 备份
cp -r /www/wwwroot/your-blog /path/to/backup/your-blog.v1.2.0

# 上传新文件覆盖（保留 config.php）
unzip -o TBlog_v1.2.1.zip -d /www/wwwroot/your-blog/

# 修复权限
chown -R www:www /www/wwwroot/your-blog
```

无需执行 SQL 迁移。

---

> 关注 GitHub 仓库获取最新更新：https://github.com/zhang-pu/tblog
