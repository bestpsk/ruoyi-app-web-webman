-- =============================================
-- 行程安排模块数据库脚本
-- 执行顺序：1.建表 -> 2.字典类型 -> 3.字典数据 -> 4.菜单
-- =============================================

-- 1. 创建行程安排表
DROP TABLE IF EXISTS `biz_schedule`;
CREATE TABLE `biz_schedule` (
  `schedule_id` bigint NOT NULL AUTO_INCREMENT COMMENT '行程ID',
  `user_id` bigint NOT NULL COMMENT '员工ID',
  `user_name` varchar(50) DEFAULT NULL COMMENT '员工姓名',
  `enterprise_id` bigint NOT NULL COMMENT '企业ID',
  `enterprise_name` varchar(100) DEFAULT NULL COMMENT '企业名称',
  `schedule_date` date NOT NULL COMMENT '行程日期',
  `purpose` char(1) NOT NULL COMMENT '下店目的(1爆卡 2启动销售 3售后服务 4洽谈业务)',
  `remark` text DEFAULT NULL COMMENT '备注',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0正常 1取消)',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`schedule_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_enterprise_id` (`enterprise_id`),
  KEY `idx_schedule_date` (`schedule_date`),
  KEY `idx_user_date` (`user_id`, `schedule_date`),
  KEY `idx_enterprise_date` (`enterprise_id`, `schedule_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='行程安排表';

-- 2. 插入字典类型
INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`) 
VALUES ('下店目的', 'biz_schedule_purpose', '0', 'admin', NOW(), '下店目的列表');

-- 3. 插入字典数据
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '爆卡', '1', 'biz_schedule_purpose', '', 'danger', 'N', '0', 'admin', NOW()),
(2, '启动销售', '2', 'biz_schedule_purpose', '', 'success', 'N', '0', 'admin', NOW()),
(3, '售后服务', '3', 'biz_schedule_purpose', '', 'warning', 'N', '0', 'admin', NOW()),
(4, '洽谈业务', '4', 'biz_schedule_purpose', '', 'primary', 'N', '0', 'admin', NOW());

-- 4. 插入菜单数据
-- 行程安排菜单
SET @business_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '业务管理' AND parent_id = 0) t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) 
VALUES ('行程安排', @business_menu_id, 2, 'schedule', 'business/schedule/index', NULL, 'Schedule', 1, 0, 'C', '0', '0', 'business:schedule:list', 'date', 'admin', NOW());

-- 行程安排按钮权限
SET @schedule_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '行程安排' AND path = 'schedule') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('行程查询', @schedule_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:schedule:query', '#', 'admin', NOW()),
('行程新增', @schedule_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:schedule:add', '#', 'admin', NOW()),
('行程修改', @schedule_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:schedule:edit', '#', 'admin', NOW()),
('行程删除', @schedule_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:schedule:remove', '#', 'admin', NOW());

-- 5. 为管理员角色分配行程安排菜单权限
SET @admin_role_id = 1;
SET @schedule_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '行程安排' AND path = 'schedule');
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) VALUES
(@admin_role_id, @schedule_menu);

INSERT INTO `sys_role_menu` (`role_id`, `menu_id`)
SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @schedule_menu;
