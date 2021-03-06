-- 关于逻辑删除破坏唯一性约束的思考
-- 1. 另建一个表
-- 2. 删除前进行查询
-- -----------------------------------------------------------------------------

-- 2020/12/17
-- 以前参考 sql 文档规范是公司业务项目的，每次版本都会改动很多数据库，
-- 但是自己的项目只是偶尔增删改几个字段，因此只直接维护一份完整的 sql 文档，
-- 修改的语句写到 drafts.sql 中，线上手动复制执行。
-- -----------------------------------------------------------------------------

-- 初始化
-- 创建数据库、表

-- 指定此次连接编码
SET NAMES 'utf8mb4';

-- 创建数据库
-- 这一步直接在 shell 里做
CREATE DATABASE `blog` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- -----------------------------------------------------------------------------
-- 字符串区分大小写，只有转化为二进制存储，指定 utf8mb4_bin

-- 创建文章表 article
CREATE TABLE `article` (
    `id` int(8) NOT NULL AUTO_INCREMENT,
    `title` varchar(25) NOT NULL COMMENT '标题',
    `summary` varchar(200) DEFAULT NULL COMMENT '简介',
    `content` text DEFAULT NULL COMMENT '文章内容',
    `text_content` text DEFAULT NULL COMMENT '纯文本内容，不是 md 格式',
    `tag` varchar(20) DEFAULT 'none' COMMENT '所属标签',
    `status` tinyint(1) DEFAULT 0 COMMENT '状态：0-草稿、1-上线、2-下线、3-垃圾箱',
    `category` enum('note', 'life') NOT NULL COMMENT '所属分类：笔记、生活',
    `bg` varchar(128) DEFAULT NULL COMMENT '配图',

    -- 统一型字段
    `article_id` varchar(10) NOT NULL COLLATE utf8mb4_bin COMMENT '唯一短链型 id',
    `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `modify_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `publish_at` datetime NULL COMMENT '发布时间',
    `delete_at` datetime NULL,
    `_d` tinyint(1) DEFAULT 0 COMMENT '逻辑删除',

    PRIMARY KEY (`id`),
    -- 插入前查询
    -- UNIQUE KEY `uq_article` (`title`, `status`, `_d`),
    -- 组合索引更高效
    KEY `idx_article` (`article_id`, `_d`),
    -- 全文检索，使用 FULLTEXT 索引。目前只检索文章表
    -- 使用内置的分词器，ngram 支持中日韩
    FULLTEXT KEY `ftx_article` (`title`, `summary`, `text_content`) WITH PARSER ngram
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
COMMENT='文章表';

-- 创建留言表 msg
CREATE TABLE `msg` (
    `id` int(6) NOT NULL AUTO_INCREMENT,
    `name` varchar(20) NOT NULL COMMENT '留言人姓名',
    `content` varchar(250) NOT NULL COMMENT '留言内容',
    `avatar` varchar(128) DEFAULT NULL COMMENT '头像',
    `read` boolean DEFAULT false COMMENT '博主是否已读',
    `site` varchar(64) DEFAULT NULL COMMENT '留言人网站，友链备用',

    `msg_id` varchar(10) NOT NULL COLLATE utf8mb4_bin COMMENT '唯一短链型 id',
    `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `create_by` varchar(10) NOT NULL COLLATE utf8mb4_bin COMMENT '用户 id',
    `modify_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `delete_at` datetime NULL,
    `_d` tinyint(1) DEFAULT 0 COMMENT '逻辑删除',

    PRIMARY KEY (`id`),
    KEY `idx_msg` (`msg_id`, `_d`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
COMMENT='留言表';

-- 创建评论表 comment
CREATE TABLE `comment` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `name` varchar(20) DEFAULT NULL COMMENT '评论人姓名',
    `content` varchar(150) NOT NULL COMMENT '评论',
    `type` tinyint(1) NOT NULL COMMENT '评论类型：0-文章、1-留言',
    `parent_id` varchar(10) NOT NULL COMMENT '关联的文章、留言等 id',
    `label` tinyint(1) DEFAULT 0 COMMENT '用户标签：0-普通用户、1-博主',

    `comment_id` varchar(10) NOT NULL COLLATE utf8mb4_bin COMMENT '唯一短链型 id',
    `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `modify_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `delete_at` datetime NULL,
    `_d` tinyint(1) DEFAULT 0 COMMENT '逻辑删除',

    PRIMARY KEY (`id`),
    KEY `idx_comment` (`comment_id`, `parent_id`, `_d`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
COMMENT='评论表';

-- 创建标签表 tag
CREATE TABLE `tag` (
    `id` int(4) NOT NULL AUTO_INCREMENT,
    -- 大小写敏感
    `name` varchar(16) NOT NULL COLLATE utf8mb4_bin COMMENT '标签名',
    `display_name` varchar(64) NOT NULL COMMENT '展示标签名',
    `click` int(8) DEFAULT 0 COMMENT '点击量',
    `status` tinyint(1) DEFAULT 1 COMMENT '标签状态：1-可用、2-不可用',

    `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `modify_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `delete_at` datetime NULL,
    `_d` tinyint(1) DEFAULT 0 COMMENT '逻辑删除',

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tag` (`name`, `_d`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
COMMENT '标签表';

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

-- 用户表，API 鉴权支撑
-- 手动插入管理员记录，之后有需求再做配置
CREATE TABLE `user` (
    `id` int(16) unsigned AUTO_INCREMENT,
    `account` varchar(32) NULL COMMENT '账号，可以是英文、邮箱、手机号等等',
    `display_name` varchar(64) NULL COMMENT '用户昵称',
    `avatar` varchar(128) NULL COMMENT '用户头像',
    `pwd` varchar(256) NULL COMMENT '加密后的密码',

    `user_ip` char(15) NULL COMMENT '注册时 IP 地址',
    `user_ip_address` varchar(256) NULL COMMENT '注册时 IP 所在地址',
    `user_origin` varchar(128) NULL COMMENT '注册时域名',
    `user_agent` varchar(512) NULL COMMENT '注册时浏览器标识',

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
