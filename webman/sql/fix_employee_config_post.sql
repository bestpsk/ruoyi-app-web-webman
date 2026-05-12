-- =============================================
-- 修复员工配置表岗位信息
-- =============================================

-- 更新员工配置表的岗位信息（通过 sys_user_post 关联表获取）
UPDATE biz_employee_config ec
LEFT JOIN sys_user_post up ON ec.user_id = up.user_id
LEFT JOIN sys_post p ON up.post_id = p.post_id
SET ec.post_id = p.post_id, ec.post_name = p.post_name
WHERE ec.post_id IS NULL OR ec.post_name IS NULL;
