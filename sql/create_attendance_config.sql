-- 考勤配置表
CREATE TABLE IF NOT EXISTS `biz_attendance_config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `config_name` varchar(100) DEFAULT NULL COMMENT '配置名称',
  `rule_id` int(11) NOT NULL COMMENT '考勤规则ID',
  `config_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '配置类型：1=用户级，2=部门级',
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID（用户级配置）',
  `dept_id` int(11) DEFAULT NULL COMMENT '部门ID（部门级配置）',
  `status` char(1) DEFAULT '0' COMMENT '状态：0=正常，1=停用',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT NULL COMMENT '创建者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT NULL COMMENT '更新者',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`config_id`),
  KEY `idx_rule_id` (`rule_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_dept_id` (`dept_id`),
  KEY `idx_config_type` (`config_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考勤配置表';
