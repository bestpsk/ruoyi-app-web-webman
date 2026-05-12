-- =============================================
-- 订单管理模块 - 数据库脚本
-- 包含：字段扩展 + 菜单配置
-- =============================================

-- =============================================
-- 1. 数据库字段扩展
-- =============================================

-- 添加实付金额字段
ALTER TABLE `biz_sales_order` ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '实付金额' AFTER `deal_amount`;

-- 添加欠款金额字段
ALTER TABLE `biz_sales_order` ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;

-- 更新现有数据：欠款金额 = 成交金额 - 实付金额（实付金额默认为0，所以欠款金额 = 成交金额）
UPDATE `biz_sales_order` SET `owed_amount` = `deal_amount` WHERE `owed_amount` = 0 AND `deal_amount` > 0;

-- =============================================
-- 2. 菜单配置
-- =============================================

-- 获取业务管理菜单ID
SET @business_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '业务管理' AND parent_id = 0) t);

-- 插入订单管理菜单
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) 
VALUES ('订单管理', @business_menu_id, 4, 'order', 'business/order/index', NULL, 'Order', 1, 0, 'C', '0', '0', 'business:order:list', 'list', 'admin', NOW());

-- 获取订单管理菜单ID
SET @order_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '订单管理' AND path = 'order') t);

-- 订单管理按钮权限
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('订单查询', @order_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:order:query', '#', 'admin', NOW()),
('企业审核', @order_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:order:enterpriseAudit', '#', 'admin', NOW()),
('财务审核', @order_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:order:financeAudit', '#', 'admin', NOW());

-- =============================================
-- 3. 为管理员角色分配菜单权限
-- =============================================
SET @admin_role_id = 1;
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) VALUES (@admin_role_id, @order_menu_id);
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`)
SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @order_menu_id;
