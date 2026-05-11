-- =============================================
-- 订单明细表结构修复
-- 为 biz_order_item 表添加缺失的 paid_amount 字段
-- 日期: 2026-01-09
-- =============================================

-- 检查并添加 paid_amount 字段（实付金额）
ALTER TABLE `biz_order_item`
ADD COLUMN IF NOT EXISTS `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '实付金额' AFTER `deal_amount`;
