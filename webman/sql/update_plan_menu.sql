-- 方案管理菜单调整
-- 将方案管理菜单的组件路径从 business/plan/index 改为 business/planList/index
-- 执行时间: 2026-05-07

UPDATE sys_menu 
SET component = 'business/planList/index',
    update_time = NOW()
WHERE menu_id = 2076;

-- 验证更新结果
SELECT menu_id, menu_name, path, component, perms 
FROM sys_menu 
WHERE menu_id = 2076;
