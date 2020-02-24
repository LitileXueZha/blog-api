-- 删除留言表中作用不大的字段
ALTER TABLE `msg`
DROP `user_agent`;
ALTER TABLE `msg`
DROP `platform`;

-- 增加用户表代理字符串长度
ALTER TABLE `user`
MODIFY COLUMN `user_agent` varchar(512) NULL COMMENT '注册时浏览器标识';
