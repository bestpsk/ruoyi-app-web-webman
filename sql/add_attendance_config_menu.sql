-- 添加考勤配置菜单
-- 获取考勤管理的菜单ID
SET @attendance_parent_id = (SELECT menu_id FROM sys_menu WHERE menu_name = '考勤管理' AND parent_id = 0);

-- 插入考勤配置菜单
INSERT INTO sys_menu (menu_name, parent_id, order_num, path, component, query, route_name, is_frame, is_cache, menu_type, visible, status, perms, icon, create_by, create_time, update_by, update_time, remark)
VALUES ('考勤配置', @attendance_parent_id, 3, 'config', 'business/attendance/config', NULL, 'AttendanceConfig', 1, 0, 'C', '0', '0', 'business:attendance:config:list', 'setting', 'admin', NOW(), '', NULL, '考勤配置菜单');

-- 获取刚插入的考勤配置菜单ID
SET @config_menu_id = (SELECT menu_id FROM sys_menu WHERE menu_name = '考勤配置' AND path = 'config');

-- 插入考勤配置按钮权限
INSERT INTO sys_menu (menu_name, parent_id, order_num, path, component, query, route_name, is_frame, is_cache, menu_type, visible, status, perms, icon, create_by, create_time, update_by, update_time, remark)
VALUES
('配置查询', @config_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:attendance:config:query', '#', 'admin', NOW(), '', NULL, ''),
('配置新增', @config_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:attendance:config:add', '#', 'admin', NOW(), '', NULL, ''),
('配置修改', @config_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:attendance:config:edit', '#', 'admin', NOW(), '', NULL, ''),
('配置删除', @config_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'business:attendance:config:remove', '#', 'admin', NOW(), '', NULL, '');

-- 为管理员角色分配考勤配置菜单权限
SET @admin_role_id = (SELECT role_id FROM sys_role WHERE role_key = 'admin');
INSERT INTO sys_role_menu (role_id, menu_id)
SELECT @admin_role_id, menu_id FROM sys_menu WHERE menu_name IN ('考勤配置', '配置查询', '配置新增', '配置修改', '配置删除') AND parent_id = @config_menu_id OR menu_id = @config_menu_id;
