-- =============================================
-- 门店管理模块数据库脚本
-- 执行顺序：1.建表 -> 2.菜单 -> 3.权限
-- =============================================

-- 1. 创建门店表
DROP TABLE IF EXISTS `biz_store`;
CREATE TABLE `biz_store` (
  `store_id` bigint NOT NULL AUTO_INCREMENT COMMENT '门店ID',
  `enterprise_id` bigint NOT NULL COMMENT '所属企业ID',
  `enterprise_name` varchar(100) DEFAULT NULL COMMENT '所属企业名称',
  `store_name` varchar(100) NOT NULL COMMENT '门店名称',
  `manager_name` varchar(50) DEFAULT NULL COMMENT '门店负责人',
  `phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `wechat` varchar(50) DEFAULT NULL COMMENT '微信',
  `address` varchar(255) DEFAULT NULL COMMENT '地址',
  `business_hours` varchar(100) DEFAULT NULL COMMENT '营业时间',
  `annual_performance` decimal(12,2) DEFAULT 0.00 COMMENT '年业绩',
  `regular_customers` int DEFAULT 0 COMMENT '常来顾客数',
  `creator_name` varchar(50) DEFAULT NULL COMMENT '创建人',
  `server_user_id` bigint DEFAULT NULL COMMENT '服务员工ID',
  `server_user_name` varchar(50) DEFAULT NULL COMMENT '服务员工姓名',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0正常 1停用)',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`store_id`),
  KEY `idx_enterprise_id` (`enterprise_id`),
  KEY `idx_store_name` (`store_name`),
  KEY `idx_server_user_id` (`server_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='门店管理表';

-- 2. 插入菜单数据
-- 门店管理菜单（挂在业务管理下）
SET @business_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '业务管理' AND parent_id = 0) t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) 
VALUES ('门店管理', @business_menu_id, 2, 'store', 'business/store/index', NULL, 'Store', 1, 0, 'C', '0', '0', 'business:store:list', 'shopping', 'admin', NOW());

-- 门店管理按钮权限
SET @store_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '门店管理' AND path = 'store') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('门店查询', @store_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:store:query', '#', 'admin', NOW()),
('门店新增', @store_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:store:add', '#', 'admin', NOW()),
('门店修改', @store_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:store:edit', '#', 'admin', NOW()),
('门店删除', @store_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:store:remove', '#', 'admin', NOW()),
('门店导出', @store_menu_id, 5, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:store:export', '#', 'admin', NOW());

-- 3. 为管理员角色分配门店管理菜单权限
SET @admin_role_id = 1;
SET @store_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '门店管理' AND path = 'store');
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) VALUES (@admin_role_id, @store_menu);

INSERT INTO `sys_role_menu` (`role_id`, `menu_id`)
SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @store_menu;

-- 4. 如表已存在，执行以下ALTER语句添加新字段
-- ALTER TABLE `biz_store` ADD COLUMN `annual_performance` decimal(12,2) DEFAULT 0.00 COMMENT '年业绩' AFTER `business_hours`;
-- ALTER TABLE `biz_store` ADD COLUMN `regular_customers` int DEFAULT 0 COMMENT '常来顾客数' AFTER `annual_performance`;
-- ALTER TABLE `biz_store` ADD COLUMN `creator_name` varchar(50) DEFAULT NULL COMMENT '创建人' AFTER `regular_customers`;

-- =============================================
-- ⚠️ 重要：请确保在MySQL中执行以下SQL语句！
-- =============================================

-- 5. 为已存在的biz_store表添加新字段（如果表已创建但缺少字段）
ALTER TABLE IF EXISTS `biz_store` ADD COLUMN IF NOT EXISTS `annual_performance` decimal(12,2) DEFAULT 0.00 COMMENT '年业绩' AFTER `business_hours`;
ALTER TABLE IF EXISTS `biz_store` ADD COLUMN IF NOT EXISTS `regular_customers` int DEFAULT 0 COMMENT '常来顾客数' AFTER `annual_performance`;
ALTER TABLE IF EXISTS `biz_store` ADD COLUMN IF NOT EXISTS `creator_name` varchar(50) DEFAULT NULL COMMENT '创建人' AFTER `regular_customers`;

-- 6. 将sys_user表的nick_name字段重命名为real_name
ALTER TABLE `sys_user` CHANGE COLUMN `nick_name` `real_name` varchar(30) DEFAULT '' COMMENT '用户姓名';
