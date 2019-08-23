# Blog Api

为博客提供服务。用原生 PHP 实现，以后再了解一些框架使用

[API 访问地址](https://doc.ningtaostudy.cn)

## 几个概念

+ API 鉴权
+ 路由
+ 中间件
+ 几大逻辑模块，文章、评论、留言、标签、搜索
+ 日志管理

## 调试（XDebug）

[文档](https://xdebug.org/)，在 VS Code 中调试

1. 安装插件：PHP Debug
2. 机器上安装 XDebug
3. 配置`php.ini`

## Windows 上碰到的问题记录

1. **PDO 驱动未安装**。编辑 `php.ini` 文件，添加 `php_pdo_mysql.dll` 扩展
2. **MySQL 连接认证方式问题**。修改认证为 `mysql_native_password`，可以修改 `my.cnf` 或连接数据库 `ALTER USER`
3. **`$_GET` 变量为空**。Nginx 配置的问题，在 `try_files` 加上 `?$query_string`，只有 `index.php` 的话导致空数据

## 生产环境注意事项

关于服务一些配置，尽量少暴露开发信息造成危险

### PHP 配置

```ini
; 将错误输出到浏览器。生产环境应为 Off
display_errors = Off
display_startup_errors = Off

; 暴露 PHP 版本信息。在 header 里的 X-Powerd-By: PHP/7.2
expose_php = Off
```
