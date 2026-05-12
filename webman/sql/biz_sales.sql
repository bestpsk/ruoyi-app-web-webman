-- =============================================
-- 销售开单模块数据库脚本
-- =============================================

-- 1. 客户表
DROP TABLE IF EXISTS `biz_customer`;
CREATE TABLE `biz_customer` (
  `customer_id` bigint NOT NULL AUTO_INCREMENT COMMENT '客户ID',
  `enterprise_id` bigint NOT NULL COMMENT '所属企业ID',
  `enterprise_name` varchar(100) DEFAULT NULL COMMENT '所属企业名称',
  `store_id` bigint DEFAULT NULL COMMENT '所属门店ID',
  `store_name` varchar(100) DEFAULT NULL COMMENT '所属门店名称',
  `customer_name` varchar(50) NOT NULL COMMENT '客户姓名',
  `phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `wechat` varchar(50) DEFAULT NULL COMMENT '微信',
  `gender` char(1) DEFAULT '2' COMMENT '性别(0男1女2未知)',
  `age` int DEFAULT NULL COMMENT '年龄',
  `tag` varchar(100) DEFAULT NULL COMMENT '客户标签(字典biz_customer_tag)',
  `remark` text DEFAULT NULL COMMENT '备注',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0正常1停用)',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`customer_id`),
  KEY `idx_enterprise_id` (`enterprise_id`),
  KEY `idx_store_id` (`store_id`),
  KEY `idx_customer_name` (`customer_name`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户表';

-- 2. 销售订单表
DROP TABLE IF EXISTS `biz_sales_order`;
CREATE TABLE `biz_sales_order` (
  `order_id` bigint NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_no` varchar(30) NOT NULL COMMENT '订单编号',
  `customer_id` bigint NOT NULL COMMENT '客户ID',
  `customer_name` varchar(50) DEFAULT NULL COMMENT '客户姓名',
  `enterprise_id` bigint NOT NULL COMMENT '企业ID',
  `enterprise_name` varchar(100) DEFAULT NULL COMMENT '企业名称',
  `store_id` bigint DEFAULT NULL COMMENT '门店ID',
  `store_name` varchar(100) DEFAULT NULL COMMENT '门店名称',
  `deal_amount` decimal(12,2) DEFAULT 0.00 COMMENT '成交总金额',
  `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '实付总金额',
  `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款总金额',
  `order_status` char(1) NOT NULL DEFAULT '1' COMMENT '订单状态(1已成交2已取消)',
  `package_name` varchar(200) DEFAULT '' COMMENT '套餐名称',
  `enterprise_audit_status` char(1) NOT NULL DEFAULT '0' COMMENT '企业审核(0未审核1已审核)',
  `finance_audit_status` char(1) NOT NULL DEFAULT '0' COMMENT '财务审核(0未审核1已审核)',
  `enterprise_audit_by` varchar(64) DEFAULT NULL COMMENT '企业审核人',
  `enterprise_audit_time` datetime DEFAULT NULL COMMENT '企业审核时间',
  `finance_audit_by` varchar(64) DEFAULT NULL COMMENT '财务审核人',
  `finance_audit_time` datetime DEFAULT NULL COMMENT '财务审核时间',
  `creator_user_id` bigint DEFAULT NULL COMMENT '开单员工ID',
  `creator_user_name` varchar(50) DEFAULT NULL COMMENT '开单员工姓名',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_enterprise_id` (`enterprise_id`),
  KEY `idx_store_id` (`store_id`),
  KEY `idx_order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='销售订单表';

-- 3. 订单明细表
DROP TABLE IF EXISTS `biz_order_item`;
CREATE TABLE `biz_order_item` (
  `item_id` bigint NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `order_id` bigint NOT NULL COMMENT '订单ID',
  `product_name` varchar(100) NOT NULL COMMENT '品项名称',
  `quantity` int NOT NULL DEFAULT 1 COMMENT '次数',
  `deal_amount` decimal(12,2) DEFAULT 0.00 COMMENT '成交金额',
  `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '实付金额',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`item_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单明细表';

-- 4. 客户套餐表
DROP TABLE IF EXISTS `biz_customer_package`;
CREATE TABLE `biz_customer_package` (
  `package_id` bigint NOT NULL AUTO_INCREMENT COMMENT '套餐ID',
  `package_no` varchar(30) NOT NULL COMMENT '套餐编号',
  `customer_id` bigint NOT NULL COMMENT '客户ID',
  `customer_name` varchar(50) DEFAULT NULL COMMENT '客户姓名',
  `order_id` bigint DEFAULT NULL COMMENT '来源订单ID',
  `order_no` varchar(30) DEFAULT NULL COMMENT '来源订单编号',
  `enterprise_id` bigint DEFAULT NULL COMMENT '企业ID',
  `store_id` bigint DEFAULT NULL COMMENT '门店ID',
  `package_name` varchar(100) DEFAULT NULL COMMENT '套餐名称',
  `total_amount` decimal(12,2) DEFAULT 0.00 COMMENT '套餐总金额',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0有效1已用完2已过期3已退款)',
  `expire_date` date DEFAULT NULL COMMENT '过期日期',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`package_id`),
  UNIQUE KEY `uk_package_no` (`package_no`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户套餐表';

-- 5. 套餐明细表
DROP TABLE IF EXISTS `biz_package_item`;
CREATE TABLE `biz_package_item` (
  `package_item_id` bigint NOT NULL AUTO_INCREMENT COMMENT '持卡明细ID',
  `package_id` bigint NOT NULL COMMENT '持卡记录ID',
  `product_name` varchar(100) NOT NULL COMMENT '品项名称',
  `unit_price` decimal(12,2) DEFAULT 0.00 COMMENT '单次价格',
  `plan_price` decimal(12,2) DEFAULT 0.00 COMMENT '方案总价',
  `deal_price` decimal(12,2) DEFAULT 0.00 COMMENT '成交金额',
  `total_quantity` int NOT NULL DEFAULT 0 COMMENT '总次数',
  `used_quantity` int NOT NULL DEFAULT 0 COMMENT '已用次数',
  `remaining_quantity` int NOT NULL DEFAULT 0 COMMENT '剩余次数',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`package_item_id`),
  KEY `idx_package_id` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='套餐明细表';

-- 6. 操作记录表
DROP TABLE IF EXISTS `biz_operation_record`;
CREATE TABLE `biz_operation_record` (
  `record_id` bigint NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `operation_type` char(1) NOT NULL DEFAULT '0' COMMENT '操作类型(0持卡操作1体验操作)',
  `customer_id` bigint NOT NULL COMMENT '客户ID',
  `customer_name` varchar(50) DEFAULT NULL COMMENT '客户姓名',
  `package_id` bigint DEFAULT NULL COMMENT '持卡记录ID',
  `package_no` varchar(30) DEFAULT NULL COMMENT '持卡记录编号',
  `package_item_id` bigint DEFAULT NULL COMMENT '持卡明细ID',
  `product_name` varchar(100) DEFAULT NULL COMMENT '品项名称',
  `operation_quantity` int NOT NULL DEFAULT 1 COMMENT '操作次数',
  `consume_amount` decimal(12,2) DEFAULT 0.00 COMMENT '消耗金额',
  `trial_price` decimal(12,2) DEFAULT NULL COMMENT '体验价',
  `customer_feedback` varchar(500) DEFAULT NULL COMMENT '顾客反馈',
  `satisfaction` tinyint DEFAULT NULL COMMENT '满意度(1-5星)',
  `before_photo` varchar(500) DEFAULT NULL COMMENT '操作前对比照',
  `after_photo` varchar(500) DEFAULT NULL COMMENT '操作后对比照',
  `operator_user_id` bigint DEFAULT NULL COMMENT '操作员工ID',
  `operator_user_name` varchar(50) DEFAULT NULL COMMENT '操作员工姓名',
  `operation_date` date DEFAULT NULL COMMENT '操作日期',
  `enterprise_id` bigint DEFAULT NULL COMMENT '企业ID',
  `store_id` bigint DEFAULT NULL COMMENT '门店ID',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`record_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_package_id` (`package_id`),
  KEY `idx_operation_date` (`operation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作记录表';

-- 7. 插入菜单数据
SET @business_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '业务管理' AND parent_id = 0) t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) 
VALUES ('销售开单', @business_menu_id, 3, 'sales', 'business/sales/index', NULL, 'Sales', 1, 0, 'C', '0', '0', 'business:sales:list', 'shopping', 'admin', NOW());

-- 销售开单按钮权限
SET @sales_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '销售开单' AND path = 'sales') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('销售开单查询', @sales_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:sales:query', '#', 'admin', NOW()),
('销售开单新增', @sales_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:sales:add', '#', 'admin', NOW()),
('销售开单修改', @sales_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:sales:edit', '#', 'admin', NOW()),
('销售开单删除', @sales_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:sales:remove', '#', 'admin', NOW()),
('企业审核', @sales_menu_id, 5, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:sales:enterpriseAudit', '#', 'admin', NOW()),
('财务审核', @sales_menu_id, 6, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:sales:financeAudit', '#', 'admin', NOW());

-- 8. 为管理员角色分配菜单权限
SET @admin_role_id = 1;
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) VALUES (@admin_role_id, @sales_menu_id);
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`)
SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @sales_menu_id;

-- 9. 插入字典类型
INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`) VALUES
('客户标签', 'biz_customer_tag', '0', 'admin', NOW(), '客户标签列表'),
('订单状态', 'biz_order_status', '0', 'admin', NOW(), '销售订单状态'),
('套餐状态', 'biz_package_status', '0', 'admin', NOW(), '客户套餐状态');

-- 10. 插入字典数据
INSERT INTO `sys_dict_data` (`dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `create_by`, `create_time`, `remark`) VALUES
('biz_customer_tag', 'VIP', 'vip', 1, '0', 'admin', NOW(), NULL),
('biz_customer_tag', '普通', 'normal', 2, '0', 'admin', NOW(), NULL),
('biz_customer_tag', '重点客户', 'important', 3, '0', 'admin', NOW(), NULL),
('biz_customer_tag', '新客户', 'new', 4, '0', 'admin', NOW(), NULL),
('biz_customer_tag', '待跟进', 'follow', 5, '0', 'admin', NOW(), NULL),
('biz_order_status', '待确认', '0', 1, '0', 'admin', NOW(), NULL),
('biz_order_status', '已成交', '1', 2, '0', 'admin', NOW(), NULL),
('biz_order_status', '已取消', '2', 3, '0', 'admin', NOW(), NULL),
('biz_package_status', '有效', '0', 1, '0', 'admin', NOW(), NULL),
('biz_package_status', '已用完', '1', 2, '0', 'admin', NOW(), NULL),
('biz_package_status', '已过期', '2', 3, '0', 'admin', NOW(), NULL),
('biz_package_status', '已退款', '3', 4, '0', 'admin', NOW(), NULL);

-- 11. 重构ALTER语句（如已建表，执行以下语句）
-- 订单明细表：删除对比照和是否我们操作字段
-- ALTER TABLE `biz_order_item` DROP COLUMN `before_photo`;
-- ALTER TABLE `biz_order_item` DROP COLUMN `after_photo`;
-- ALTER TABLE `biz_order_item` DROP COLUMN `is_our_operation`;

-- 操作记录表：新增消耗金额、满意度、操作类型、体验价
-- ALTER TABLE `biz_operation_record` ADD COLUMN `operation_type` char(1) NOT NULL DEFAULT '0' COMMENT '操作类型(0持卡操作1体验操作)' AFTER `record_id`;
-- ALTER TABLE `biz_operation_record` ADD COLUMN `consume_amount` decimal(12,2) DEFAULT 0.00 COMMENT '消耗金额' AFTER `operation_quantity`;
-- ALTER TABLE `biz_operation_record` ADD COLUMN `trial_price` decimal(12,2) DEFAULT NULL COMMENT '体验价' AFTER `consume_amount`;
-- ALTER TABLE `biz_operation_record` ADD COLUMN `satisfaction` tinyint DEFAULT NULL COMMENT '满意度(1-5星)' AFTER `customer_feedback`;

-- 套餐明细表：新增单次价和方案总价
-- ALTER TABLE `biz_package_item` ADD COLUMN `unit_price` decimal(12,2) DEFAULT 0.00 COMMENT '单次价格' AFTER `product_name`;
-- ALTER TABLE `biz_package_item` ADD COLUMN `plan_price` decimal(12,2) DEFAULT 0.00 COMMENT '方案总价' AFTER `unit_price`;

-- 12. 修复销售开单500错误（2026-05-12）
-- 问题原因：数据库表缺少必要字段导致订单插入失败
-- 详见: fix_sales_order_500_error.sql

-- 客户套餐表：新增已付金额和欠款金额字段
-- ALTER TABLE `biz_customer_package` ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '已付金额' AFTER `total_amount`;
-- ALTER TABLE `biz_customer_package` ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;

-- 订单明细表：新增单次价和欠款金额字段
-- ALTER TABLE `biz_order_item` ADD COLUMN `unit_price` decimal(12,2) DEFAULT 0.00 COMMENT '单次价格' AFTER `deal_amount`;
-- ALTER TABLE `biz_order_item` ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `unit_price`;

-- 套餐明细表：新增已付金额和欠款金额字段
-- ALTER TABLE `biz_package_item` ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '已付金额' AFTER `deal_price`;
-- ALTER TABLE `biz_package_item` ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;
