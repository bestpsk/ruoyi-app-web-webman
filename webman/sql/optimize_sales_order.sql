-- =============================================
-- 销售开单优化：删除冗余字段
-- 执行时间: 2026-05-10
-- 说明：去掉方案价格和成交开关功能，简化开单流程
-- =============================================

-- 1. 订单明细表：删除方案价格和是否成交字段
ALTER TABLE `biz_order_item` DROP COLUMN `plan_price`;
ALTER TABLE `biz_order_item` DROP COLUMN `is_deal`;

-- 2. 销售订单表：删除方案总金额字段（使用deal_amount代替）
ALTER TABLE `biz_sales_order` DROP COLUMN `total_amount`;

-- 3. 套餐明细表：删除方案总价字段（可选，如果业务不需要保留历史数据）
-- ALTER TABLE `biz_package_item` DROP COLUMN `plan_price`;

-- 4. 更新订单状态字典（删除"待确认"状态，因为现在开单即成交）
-- 注意：此步骤可选，根据业务需求决定是否执行
-- DELETE FROM sys_dict_data WHERE dict_type = 'biz_order_status' AND dict_value = '0';

-- 5. 更新备注说明（可选）
-- ALTER TABLE `biz_sales_order` MODIFY COLUMN `deal_amount` decimal(12,2) DEFAULT 0.00 COMMENT '订单总金额';
