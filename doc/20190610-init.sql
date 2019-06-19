-- 初始化
-- 创建数据库、表

-- 指定此次连接编码
SET NAMES 'utf8mb4';

-- 创建数据库
-- 这一步直接在 shell 里做
-- CREATE DATABASE blog DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 创建文章表 article
CREATE TABLE `article` (
    `id` int(8) NOT NULL AUTO_INCREMENT,
    `title` varchar(20) NOT NULL COMMENT '标题',
    `summary` varchar(200) DEFAULT NULL COMMENT '简介',
    `content` text DEFAULT NULL COMMENT '文章内容',
    `tag` varchar(20) DEFAULT 'none' COMMENT '所属标签',
    `status` tinyint(1) DEFAULT 0 COMMENT '状态：0-草稿、1-上线、2-下线、3-垃圾箱',
    `category` enum('note', 'life') NOT NULL COMMENT '所属分类：笔记、生活',
    `bg` varchar(128) DEFAULT NULL COMMENT '配图',

    -- 统一型字段
    `article_id` varchar(10) NOT NULL COMMENT '唯一短链型 id',
    `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `modify_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `delete_at` datetime NULL,
    `_d` tinyint(1) DEFAULT 0 COMMENT '逻辑删除',

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_article` (`article_id`),
    -- 组合索引更高效
    KEY `idx_article` (`status`, `category`, `_d`)
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
    `platform` enum('pc', 'mobile') NOT NULL COMMENT '留言平台',

    `msg_id` varchar(10) NOT NULL COMMENT '唯一短链型 id',
    `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `modify_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `delete_at` datetime NULL,
    `_d` tinyint(1) DEFAULT 0 COMMENT '逻辑删除',

    PRIMARY KEY (`id`),
    -- 同人只能留言一次
    UNIQUE KEY `uq_msg` (`msg_id`, `name`),
    KEY `idx_msg` (`_d`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
COMMENT='留言表';

-- 创建评论表 comment
CREATE TABLE `comment` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `name` varchar(20) NOT NULL COMMENT '评论人姓名',
    `content` varchar(150) NOT NULL COMMENT '评论',
    `type` tinyint(1) NOT NULL COMMENT '评论类型：0-文章、1-留言',
    `parent_id` varchar(10) NOT NULL COMMENT '关联的文章、留言等 id',

    `comment_id` varchar(10) NOT NULL COMMENT '唯一短链型 id',
    `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `modify_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `delete_at` datetime NULL,
    `_d` tinyint(1) DEFAULT 0 COMMENT '逻辑删除',

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_comment` (`comment_id`),
    KEY `idx_comment` (`type`, `parent_id`, `_d`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci
COMMENT='评论表';

-- 创建标签表 tag
CREATE TABLE `tag` (
    `id` int(4) NOT NULL AUTO_INCREMENT,
    `name` varchar(16) NOT NULL COMMENT '标签名',
    `display_name` varchar(64) NOT NULL COMMENT '展示标签名',
    `click` int(8) DEFAULT 0 COMMENT '点击量',

    `tag_id` varchar(10) NOT NULL COMMENT '唯一短链型 id',
    `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `modify_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `delete_at` datetime NULL,
    `_d` tinyint(1) DEFAULT 0 COMMENT '逻辑删除',

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tag` (`tag_id`, `name`)
)
