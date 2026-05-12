-- =============================================
-- 考勤管理模块数据库脚本
-- 执行顺序：1.建表 -> 2.字典类型 -> 3.字典数据 -> 4.菜单 -> 5.角色权限 -> 6.默认数据
-- =============================================

-- 1. 创建考勤规则表
DROP TABLE IF EXISTS `biz_attendance_rule`;
CREATE TABLE `biz_attendance_rule` (
  `rule_id` bigint NOT NULL AUTO_INCREMENT COMMENT '规则ID',
  `rule_name` varchar(100) NOT NULL COMMENT '规则名称',
  `work_start_time` time NOT NULL COMMENT '上班时间',
  `work_end_time` time NOT NULL COMMENT '下班时间',
  `late_threshold` int NOT NULL DEFAULT 0 COMMENT '迟到容忍分钟数',
  `early_leave_threshold` int NOT NULL DEFAULT 0 COMMENT '早退容忍分钟数',
  `work_latitude` decimal(10,7) DEFAULT NULL COMMENT '考勤点纬度',
  `work_longitude` decimal(10,7) DEFAULT NULL COMMENT '考勤点经度',
  `work_address` varchar(255) DEFAULT '' COMMENT '考勤点地址',
  `allowed_distance` int DEFAULT 500 COMMENT '允许打卡距离(米)',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0正常 1停用)',
  `remark` varchar(500) DEFAULT '' COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考勤规则表';

-- 创建考勤记录表
DROP TABLE IF EXISTS `biz_attendance_record`;
CREATE TABLE `biz_attendance_record` (
  `record_id` bigint NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `user_id` bigint NOT NULL COMMENT '用户ID',
  `user_name` varchar(30) DEFAULT '' COMMENT '用户姓名',
  `attendance_date` date NOT NULL COMMENT '考勤日期',
  `clock_in_time` datetime DEFAULT NULL COMMENT '上班打卡时间',
  `clock_out_time` datetime DEFAULT NULL COMMENT '下班打卡时间',
  `clock_in_latitude` decimal(10,7) DEFAULT NULL COMMENT '上班打卡纬度',
  `clock_in_longitude` decimal(10,7) DEFAULT NULL COMMENT '上班打卡经度',
  `clock_in_address` varchar(255) DEFAULT '' COMMENT '上班打卡地址',
  `clock_in_photo` varchar(500) DEFAULT '' COMMENT '上班打卡照片',
  `clock_out_latitude` decimal(10,7) DEFAULT NULL COMMENT '下班打卡纬度',
  `clock_out_longitude` decimal(10,7) DEFAULT NULL COMMENT '下班打卡经度',
  `clock_out_address` varchar(255) DEFAULT '' COMMENT '下班打卡地址',
  `clock_out_photo` varchar(500) DEFAULT '' COMMENT '下班打卡照片',
  `attendance_status` char(1) NOT NULL DEFAULT '0' COMMENT '考勤状态(0正常 1迟到 2早退 3迟到+早退 4缺勤)',
  `clock_type` char(1) NOT NULL DEFAULT '0' COMMENT '打卡类型(0坐班 1外勤)',
  `outside_reason` varchar(500) DEFAULT '' COMMENT '外勤事由',
  `rule_id` bigint DEFAULT NULL COMMENT '关联考勤规则ID',
  `remark` varchar(500) DEFAULT '' COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `uk_user_date` (`user_id`, `attendance_date`),
  KEY `idx_attendance_date` (`attendance_date`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_attendance_status` (`attendance_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考勤记录表';

-- 2. 插入字典类型
INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`) 
VALUES ('考勤状态', 'biz_attendance_status', '0', 'admin', NOW(), '考勤状态列表');

-- 3. 插入字典数据
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '正常', '0', 'biz_attendance_status', '', 'success', 'N', '0', 'admin', NOW()),
(2, '迟到', '1', 'biz_attendance_status', '', 'warning', 'N', '0', 'admin', NOW()),
(3, '早退', '2', 'biz_attendance_status', '', 'warning', 'N', '0', 'admin', NOW()),
(4, '迟到+早退', '3', 'biz_attendance_status', '', 'danger', 'N', '0', 'admin', NOW()),
(5, '缺勤', '4', 'biz_attendance_status', '', 'danger', 'N', '0', 'admin', NOW());

-- 4. 插入菜单数据
-- 考勤管理一级菜单
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) 
VALUES ('考勤管理', 0, 5, 'attendance', NULL, NULL, NULL, 1, 0, 'M', '0', '0', '', 'time', 'admin', NOW());

-- 考勤记录菜单
SET @attendance_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '考勤管理' AND parent_id = 0) t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) 
VALUES ('考勤记录', @attendance_menu_id, 1, 'record', 'business/attendance/record', NULL, 'AttendanceRecord', 1, 0, 'C', '0', '0', 'business:attendance:record:list', 'log', 'admin', NOW());

-- 考勤规则菜单
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) 
VALUES ('考勤规则', @attendance_menu_id, 2, 'rule', 'business/attendance/rule', NULL, 'AttendanceRule', 1, 0, 'C', '0', '0', 'business:attendance:rule:list', 'edit', 'admin', NOW());

-- 考勤记录按钮权限
SET @record_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '考勤记录' AND path = 'record') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('记录查询', @record_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:attendance:record:query', '#', 'admin', NOW()),
('记录详情', @record_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:attendance:record:detail', '#', 'admin', NOW());

-- 考勤规则按钮权限
SET @rule_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '考勤规则' AND path = 'rule') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('规则查询', @rule_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:attendance:rule:query', '#', 'admin', NOW()),
('规则新增', @rule_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:attendance:rule:add', '#', 'admin', NOW()),
('规则修改', @rule_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:attendance:rule:edit', '#', 'admin', NOW()),
('规则删除', @rule_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:attendance:rule:remove', '#', 'admin', NOW());

-- 5. 为管理员角色分配考勤管理菜单权限
SET @admin_role_id = 1;
SET @attendance_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '考勤管理' AND parent_id = 0);
SET @record_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '考勤记录' AND path = 'record');
SET @rule_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '考勤规则' AND path = 'rule');

INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) VALUES
(@admin_role_id, @attendance_menu),
(@admin_role_id, @record_menu),
(@admin_role_id, @rule_menu);

INSERT INTO `sys_role_menu` (`role_id`, `menu_id`)
SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @record_menu;
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`)
SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @rule_menu;

-- 6. 插入默认考勤规则
INSERT INTO `biz_attendance_rule` (`rule_name`, `work_start_time`, `work_end_time`, `late_threshold`, `early_leave_threshold`, `work_latitude`, `work_longitude`, `allowed_distance`, `status`, `remark`, `create_by`) 
VALUES ('标准班', '09:00:00', '18:00:00', 0, 0, NULL, NULL, 500, '0', '默认考勤规则', 'admin');

-- 7. 增量变更：为已有表添加新字段（如果表已存在则执行此段）
-- ALTER TABLE `biz_attendance_record` ADD COLUMN `clock_type` char(1) NOT NULL DEFAULT '0' COMMENT '打卡类型(0坐班 1外勤)' AFTER `attendance_status`;
-- ALTER TABLE `biz_attendance_record` ADD COLUMN `outside_reason` varchar(500) DEFAULT '' COMMENT '外勤事由' AFTER `clock_type`;

-- 8. 打卡类型字典
INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`) 
VALUES ('打卡类型', 'biz_clock_type', '0', 'admin', NOW(), '打卡类型列表');

INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '坐班', '0', 'biz_clock_type', '', 'primary', 'Y', '0', 'admin', NOW()),
(2, '外勤', '1', 'biz_clock_type', '', 'warning', 'N', '0', 'admin', NOW());
