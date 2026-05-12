-- =============================================
-- 企业管理模块数据库脚本
-- 执行顺序：1.建表 -> 2.字典类型 -> 3.字典数据 -> 4.菜单
-- =============================================

-- 1. 创建企业表
DROP TABLE IF EXISTS `biz_enterprise`;
CREATE TABLE `biz_enterprise` (
  `enterprise_id` bigint NOT NULL AUTO_INCREMENT COMMENT '企业ID',
  `enterprise_name` varchar(100) NOT NULL COMMENT '企业名称',
  `boss_name` varchar(50) NOT NULL COMMENT '老板姓名',
  `phone` varchar(20) NOT NULL COMMENT '联系电话',
  `address` varchar(255) DEFAULT NULL COMMENT '地址',
  `enterprise_type` char(1) NOT NULL DEFAULT '1' COMMENT '企业类型(1直营 2加盟 3合作)',
  `store_count` int DEFAULT 0 COMMENT '门店数量',
  `annual_performance` decimal(12,2) DEFAULT 0.00 COMMENT '年业绩',
  `enterprise_level` char(1) NOT NULL DEFAULT '3' COMMENT '企业级别(1A级 2B级 3C级)',
  `server_user_id` bigint DEFAULT NULL COMMENT '服务人ID',
  `server_user_name` varchar(50) DEFAULT NULL COMMENT '服务人姓名',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0正常 1停用)',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`enterprise_id`),
  KEY `idx_enterprise_name` (`enterprise_name`),
  KEY `idx_server_user_id` (`server_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='企业管理表';

-- 2. 插入字典类型
INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`) 
VALUES ('企业类型', 'biz_enterprise_type', '0', 'admin', NOW(), '企业类型列表');

INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`) 
VALUES ('企业级别', 'biz_enterprise_level', '0', 'admin', NOW(), '企业级别列表');

-- 3. 插入字典数据
-- 企业类型字典数据
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '直营', '1', 'biz_enterprise_type', '', 'primary', 'N', '0', 'admin', NOW()),
(2, '加盟', '2', 'biz_enterprise_type', '', 'success', 'N', '0', 'admin', NOW()),
(3, '合作', '3', 'biz_enterprise_type', '', 'info', 'N', '0', 'admin', NOW());

-- 企业级别字典数据
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, 'A级', '1', 'biz_enterprise_level', '', 'danger', 'N', '0', 'admin', NOW()),
(2, 'B级', '2', 'biz_enterprise_level', '', 'warning', 'N', '0', 'admin', NOW()),
(3, 'C级', '3', 'biz_enterprise_level', '', 'info', 'Y', '0', 'admin', NOW());

-- 4. 插入菜单数据
-- 业务管理目录
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) 
VALUES ('业务管理', 0, 2, 'business', NULL, NULL, NULL, 1, 0, 'M', '0', '0', '', 'peoples', 'admin', NOW());

-- 企业管理菜单
SET @business_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '业务管理' AND parent_id = 0) t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) 
VALUES ('企业管理', @business_menu_id, 1, 'enterprise', 'business/enterprise/index', NULL, 'Enterprise', 1, 0, 'C', '0', '0', 'business:enterprise:list', 'company', 'admin', NOW());

-- 企业管理按钮权限
SET @enterprise_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '企业管理' AND path = 'enterprise') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('企业查询', @enterprise_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:enterprise:query', '#', 'admin', NOW()),
('企业新增', @enterprise_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:enterprise:add', '#', 'admin', NOW()),
('企业修改', @enterprise_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:enterprise:edit', '#', 'admin', NOW()),
('企业删除', @enterprise_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:enterprise:remove', '#', 'admin', NOW()),
('企业导出', @enterprise_menu_id, 5, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:enterprise:export', '#', 'admin', NOW());

-- 5. 为管理员角色分配企业管理菜单权限
SET @admin_role_id = 1;
SET @business_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '业务管理' AND parent_id = 0);
SET @enterprise_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '企业管理' AND path = 'enterprise');
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) VALUES
(@admin_role_id, @business_menu),
(@admin_role_id, @enterprise_menu);

INSERT INTO `sys_role_menu` (`role_id`, `menu_id`)
SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @enterprise_menu;
