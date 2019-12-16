-- shortId 计数
-- 通过 62 进制生成的 shortId 依靠自增数保证唯一性，时间戳太长，只能考虑保存此
-- 自增数。持久化存储只有文件与数据库，使用文件形式需要考虑迁移的问题，需要额外的
-- 操作处理，用数据库备份可以统一

CREATE TABLE `count` (
    `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (`id`)
)
ENGINE=InnoDB
AUTO_INCREMENT=4000
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
COMMENT='shortId 计数表';

-- 全文检索，使用 FULLTEXT 索引
-- 目前只检索文章表
CREATE FULLTEXT INDEX `ftx_article`
ON `article` (`title`, `summary`, `content`)
-- 使用内置的分词器，ngram 支持中日韩
WITH PARSER ngram;


-- 添加纯文本列
ALTER TABLE `article`
ADD COLUMN `text_content` text DEFAULT NULL COMMENT '纯文本内容，不是 md 格式'
AFTER `content`;

-- 修改索引。只能先删掉再创建
DROP INDEX `ftx_article` on `article`;

CREATE FULLTEXT INDEX `ftx_article`
ON `article` (`title`, `summary`, `text_content`)
WITH PARSER ngram;

-- 文章名增长到 25 个字
ALTER TABLE `article`
MODIFY COLUMN `title` varchar(25) NOT NULL COMMENT '标题';

-- 留言表添加 user_agent、read 字段
ALTER TABLE `msg`
ADD COLUMN `user_agent` char(140) DEFAULT NULL COMMENT '浏览器标识'
AFTER `platform`;

ALTER TABLE `msg`
ADD COLUMN `read` boolean DEFAULT false COMMENT '博主是否已读'
AFTER `user_agent`;

ALTER TABLE `msg`
ADD COLUMN `site` varchar(64) DEFAULT NULL COMMENT '留言人网站，友链备用'
AFTER `read`;

-- 评论表添加用户标签
ALTER TABLE `comment`
ADD COLUMN `label` tinyint(1) DEFAULT 0 COMMENT '用户标签：0-普通用户、1-博主'
AFTER `parent_id`;

ALTER TABLE `comment`
MODIFY COLUMN `name` varchar(20) DEFAULT NULL COMMENT '评论人姓名';

-- 添加用户表，API 鉴权支持
CREATE TABLE `user` (
    `id` int(16) unsigned AUTO_INCREMENT,
    `account` varchar(32) NULL COMMENT '账号，可以是英文、邮箱、手机号等等',
    `display_name` varchar(64) NULL COMMENT '用户昵称',
    `avatar` varchar(128) NULL COMMENT '用户头像',
    `pwd` varchar(256) NULL COMMENT '加密后的密码',

    `user_ip` char(15) NULL COMMENT '注册时 IP 地址',
    `user_ip_address` varchar(256) NULL COMMENT '注册时 IP 所在地址',
    `user_origin` varchar(128) NULL COMMENT '注册时域名',
    `user_agent` varchar(150) NULL COMMENT '注册时浏览器标识',

    `user_id` varchar(10) NOT NULL COLLATE utf8mb4_bin COMMENT '唯一短链型 id',
    `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `modify_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `delete_at` datetime NULL,
    `_d` tinyint(1) DEFAULT 0 COMMENT '逻辑删除',

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user` (`account`, `_d`),
    KEY `idx_user` (`user_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE utf8mb4_general_ci
COMMENT '用户表，目前用来支撑 API 鉴权';

INSERT INTO `user` (`account`, `display_name`, `pwd`, `user_id`)
VALUES ('tao', '诸葛林', '$2y$10$4iq1gyL6nPkm3Tbw8Lb0ie3Z5QiUVhoL509q/yVI0C9C1zcZqdDkW', '__ADMIN__');

ALTER TABLE `user`
ADD COLUMN `user_ip_address` varchar(256) NULL COMMENT '注册时 IP 所在地址'
AFTER `user_ip`;
