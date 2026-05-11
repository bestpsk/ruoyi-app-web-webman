-- =============================================
-- 客户档案表结构修复
-- 添加 enterprise_name 和 store_name 字段
-- 日期: 2026-01-09
-- =============================================

-- 为 biz_customer_archive 表添加企业名称字段
ALTER TABLE `biz_customer_archive`
ADD COLUMN IF NOT EXISTS `enterprise_name` varchar(100) DEFAULT NULL COMMENT '企业名称' AFTER `store_id`;

-- 为 biz_customer_archive 表添加门店名称字段
ALTER TABLE `biz_customer_archive`
ADD COLUMN IF NOT EXISTS `store_name` varchar(100) DEFAULT NULL COMMENT '门店名称' AFTER `enterprise_name`;
