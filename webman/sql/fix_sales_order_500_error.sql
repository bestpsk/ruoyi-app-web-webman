-- =============================================
-- 销售开单500错误修复脚本
-- 执行时间: 2026-05-12
-- 问题：数据库表缺少必要字段导致订单插入失败
-- 修复：为3张表添加缺失的9个字段
-- =============================================

-- ⚠️ 重要提示：
-- 1. 执行前请备份数据库
-- 2. 建议在测试环境先验证
-- 3. 建议在业务低峰期执行

-- =============================================
-- 1. 为 biz_customer_package 表添加缺失字段
-- =============================================

-- 添加已付金额字段
ALTER TABLE `biz_customer_package`
ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '已付金额' AFTER `total_amount`;

-- 添加欠款金额字段
ALTER TABLE `biz_customer_package`
ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;

-- =============================================
-- 2. 为 biz_order_item 表添加缺失字段
-- =============================================

-- 添加单次价字段（用于记录单价）
ALTER TABLE `biz_order_item`
ADD COLUMN `unit_price` decimal(12,2) DEFAULT 0.00 COMMENT '单次价格' AFTER `deal_amount`;

-- 添加欠款金额字段
ALTER TABLE `biz_order_item`
ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `unit_price`;

-- 注意：plan_price、is_deal、customer_feedback 字段在 optimize_sales_order.sql 中已被删除
-- 如果业务需要这些功能，可以取消以下注释重新添加：

-- ALTER TABLE `biz_order_item`
-- ADD COLUMN `plan_price` decimal(12,2) DEFAULT 0.00 COMMENT '方案总价' AFTER `quantity`;

-- ALTER TABLE `biz_order_item`
-- ADD COLUMN `is_deal` tinyint(1) DEFAULT 1 COMMENT '是否成交(0否1是)' AFTER `plan_price`;

-- ALTER TABLE `biz_order_item`
-- ADD COLUMN `customer_feedback` varchar(500) DEFAULT NULL COMMENT '客户反馈' AFTER `owed_amount`;

-- =============================================
-- 3. 为 biz_package_item 表添加缺失字段
-- =============================================

-- 添加已付金额字段
ALTER TABLE `biz_package_item`
ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '已付金额' AFTER `deal_price`;

-- 添加欠款金额字段
ALTER TABLE `biz_package_item`
ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;

-- =============================================
-- 验证语句（执行后可运行此查询确认字段已添加）
-- =============================================

-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_NAME = 'biz_customer_package'
-- AND COLUMN_NAME IN ('paid_amount', 'owed_amount');

-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_NAME = 'biz_order_item'
-- AND COLUMN_NAME IN ('unit_price', 'owed_amount');

-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_NAME = 'biz_package_item'
-- AND COLUMN_NAME IN ('paid_amount', 'owed_amount');
