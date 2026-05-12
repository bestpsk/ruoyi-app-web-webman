-- =============================================
-- 统一订单状态 & 持卡明细重构 - 数据库变更
-- =============================================

-- 1. biz_customer_package 增加实付金额和欠款金额
ALTER TABLE `biz_customer_package` ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '实付金额' AFTER `total_amount`;
ALTER TABLE `biz_customer_package` ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;

-- 2. biz_order_item 增加实付金额
ALTER TABLE `biz_order_item` ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '实付金额' AFTER `deal_amount`;

-- 3. 更新订单状态字典：未成交(0)/已成交(1)/已用完(2)
DELETE FROM `sys_dict_data` WHERE `dict_type` = 'biz_order_status';
INSERT INTO `sys_dict_data` (`dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `create_by`, `create_time`) VALUES
('biz_order_status', '未成交', '0', 1, '0', 'admin', NOW()),
('biz_order_status', '已成交', '1', 2, '0', 'admin', NOW()),
('biz_order_status', '已用完', '2', 3, '0', 'admin', NOW());

-- 4. 更新套餐状态字典：未成交(0)/已成交(1)/已用完(2)
DELETE FROM `sys_dict_data` WHERE `dict_type` = 'biz_package_status';
INSERT INTO `sys_dict_data` (`dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `create_by`, `create_time`) VALUES
('biz_package_status', '未成交', '0', 1, '0', 'admin', NOW()),
('biz_package_status', '已成交', '1', 2, '0', 'admin', NOW()),
('biz_package_status', '已用完', '2', 3, '0', 'admin', NOW());

-- 5. 迁移现有数据：package status='0'(有效) 改为 '1'(已成交)
UPDATE `biz_customer_package` SET `status` = '1' WHERE `status` = '0';

-- 6. 迁移现有数据：order status='0'(待确认) 改为 '1'(已成交)，因为已有package的都是成交的
UPDATE `biz_sales_order` SET `order_status` = '1' WHERE `order_status` = '0' AND `deal_amount` > 0;

-- 7. 补充package的paid_amount和owed_amount（从关联订单取值）
UPDATE `biz_customer_package` p
INNER JOIN `biz_sales_order` o ON p.`order_id` = o.`order_id`
SET p.`paid_amount` = COALESCE(o.`paid_amount`, 0),
    p.`owed_amount` = COALESCE(o.`owed_amount`, 0)
WHERE p.`paid_amount` = 0 AND p.`owed_amount` = 0;

-- 8. biz_package_item 增加实付金额和欠款金额
ALTER TABLE `biz_package_item` ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '实付金额' AFTER `deal_price`;
ALTER TABLE `biz_package_item` ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;

-- 9. 补充package_item的paid_amount和owed_amount（按比例从package分配）
UPDATE `biz_package_item` pi
INNER JOIN `biz_customer_package` p ON pi.`package_id` = p.`package_id`
SET pi.`paid_amount` = CASE WHEN p.`total_amount` > 0 THEN ROUND(pi.`deal_price` / p.`total_amount` * p.`paid_amount`, 2) ELSE 0 END,
    pi.`owed_amount` = pi.`deal_price` - CASE WHEN p.`total_amount` > 0 THEN ROUND(pi.`deal_price` / p.`total_amount` * p.`paid_amount`, 2) ELSE 0 END
WHERE pi.`paid_amount` = 0 AND pi.`owed_amount` = 0 AND p.`total_amount` > 0;
