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
