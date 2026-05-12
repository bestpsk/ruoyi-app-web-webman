-- =============================================
-- 行程安排功能升级SQL脚本
-- 执行顺序：1.修改表结构 -> 2.新增字典 -> 3.新建表 -> 4.更新菜单权限
-- =============================================

-- 1. 修改行程安排表状态字段
ALTER TABLE `biz_schedule` MODIFY COLUMN `status` char(1) NOT NULL DEFAULT '1' COMMENT '状态(1已预约 2服务中 3已完成 4已取消)';

-- 2. 新增行程状态字典类型（如果不存在）
INSERT IGNORE INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`) 
VALUES ('行程状态', 'biz_schedule_status', '0', 'admin', NOW(), '行程状态列表');

-- 3. 新增行程状态字典数据（如果不存在）
INSERT IGNORE INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '已预约', '1', 'biz_schedule_status', '', 'primary', 'Y', '0', 'admin', NOW()),
(2, '服务中', '2', 'biz_schedule_status', '', 'warning', 'N', '0', 'admin', NOW()),
(3, '已完成', '3', 'biz_schedule_status', '', 'success', 'N', '0', 'admin', NOW()),
(4, '已取消', '4', 'biz_schedule_status', '', 'danger', 'N', '0', 'admin', NOW());

-- 4. 为企业表增加拼音首字母字段
ALTER TABLE `biz_enterprise` ADD COLUMN `pinyin` varchar(50) DEFAULT NULL COMMENT '拼音首字母' AFTER `enterprise_name`;

-- 5. 更新现有企业的拼音首字母（需要手动执行或通过应用自动生成）
-- UPDATE `biz_enterprise` SET `pinyin` = CONVERT(enterprise_name USING gbk);

-- 6. 创建员工配置表
CREATE TABLE IF NOT EXISTS `biz_employee_config` (
  `config_id` bigint NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `user_id` bigint NOT NULL COMMENT '员工ID',
  `user_name` varchar(50) DEFAULT NULL COMMENT '员工姓名',
  `post_id` bigint DEFAULT NULL COMMENT '岗位ID',
  `post_name` varchar(50) DEFAULT NULL COMMENT '岗位名称',
  `dept_id` bigint DEFAULT NULL COMMENT '部门ID',
  `dept_name` varchar(50) DEFAULT NULL COMMENT '部门名称',
  `is_schedulable` char(1) NOT NULL DEFAULT '1' COMMENT '是否可排班(0否 1是)',
  `rest_dates` text DEFAULT NULL COMMENT '休息日期(JSON格式)',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0正常 1停用)',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `uk_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工配置表';

-- 7. 初始化员工配置数据（从现有员工数据同步）
INSERT INTO `biz_employee_config` (`user_id`, `user_name`, `dept_id`, `dept_name`, `is_schedulable`, `status`, `create_by`, `create_time`)
SELECT
  u.user_id,
  u.user_name,
  u.dept_id,
  d.dept_name,
  '1',
  u.status,
  'admin',
  NOW()
FROM sys_user u
LEFT JOIN sys_dept d ON u.dept_id = d.dept_id
WHERE u.status = '0'
AND NOT EXISTS (SELECT 1 FROM biz_employee_config c WHERE c.user_id = u.user_id);

-- 8. 同步员工职位信息到配置表（修复职位不显示问题）
UPDATE `biz_employee_config` c
INNER JOIN sys_user_post up ON c.user_id = up.user_id
INNER JOIN sys_post p ON up.post_id = p.post_id
SET c.post_id = p.post_id,
    c.post_name = p.post_name
WHERE c.post_name IS NULL OR c.post_name = '';

-- 9. 同步行程表的企业名称（修复企业不显示问题）
UPDATE `biz_schedule` s
INNER JOIN `biz_enterprise` e ON s.enterprise_id = e.enterprise_id
SET s.enterprise_name = e.enterprise_name
WHERE s.enterprise_name IS NULL OR s.enterprise_name = '';
