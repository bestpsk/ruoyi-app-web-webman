-- =============================================
-- 销售测试数据清空脚本
-- 执行时间：2026-01-09
-- 注意：此操作不可回滚，请先确认已备份！
-- =============================================

-- 1. 禁用外键检查
SET FOREIGN_KEY_CHECKS = 0;

-- 2. 清空所有业务数据表（按依赖顺序）
TRUNCATE TABLE `biz_repayment_record`;      -- 还款记录（欠款）
TRUNCATE TABLE `biz_operation_record`;      -- 操作记录
TRUNCATE TABLE `biz_customer_archive`;      -- 客户档案
TRUNCATE TABLE `biz_package_item`;          -- 套餐明细
TRUNCATE TABLE `biz_customer_package`;      -- 客户套餐
TRUNCATE TABLE `biz_order_item`;            -- 订单明细
TRUNCATE TABLE `biz_sales_order`;           -- 销售订单（开单）
TRUNCATE TABLE `biz_customer`;              -- 客户表

-- 3. 重新启用外键检查
SET FOREIGN_KEY_CHECKS = 1;

-- 4. 验证清空结果
SELECT '✅ 数据清空完成' AS result;
