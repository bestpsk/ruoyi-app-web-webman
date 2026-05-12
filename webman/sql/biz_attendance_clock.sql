-- =============================================
-- 打卡明细表（支持多次打卡）
-- 执行顺序：1.建表 -> 2.主表新增字段 -> 3.数据迁移
-- =============================================

-- 1. 创建打卡明细表
DROP TABLE IF EXISTS `biz_attendance_clock`;
CREATE TABLE `biz_attendance_clock` (
  `clock_id` bigint NOT NULL AUTO_INCREMENT COMMENT '打卡ID',
  `record_id` bigint NOT NULL COMMENT '关联考勤记录ID',
  `user_id` bigint NOT NULL COMMENT '用户ID',
  `user_name` varchar(30) DEFAULT '' COMMENT '用户姓名',
  `clock_time` datetime NOT NULL COMMENT '打卡时间',
  `clock_type` char(1) NOT NULL DEFAULT '0' COMMENT '打卡类型(0上班 1下班)',
  `work_type` char(1) NOT NULL DEFAULT '0' COMMENT '工作类型(0坐班 1外勤)',
  `latitude` decimal(10,7) DEFAULT NULL COMMENT '打卡纬度',
  `longitude` decimal(10,7) DEFAULT NULL COMMENT '打卡经度',
  `address` varchar(255) DEFAULT '' COMMENT '打卡地址',
  `photo` varchar(500) DEFAULT '' COMMENT '打卡照片',
  `outside_reason` varchar(500) DEFAULT '' COMMENT '外勤事由',
  `remark` varchar(500) DEFAULT '' COMMENT '备注',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`clock_id`),
  KEY `idx_record_id` (`record_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_clock_time` (`clock_time`),
  KEY `idx_user_date` (`user_id`, `clock_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='打卡明细表';

-- 2. 为主表添加新字段
ALTER TABLE `biz_attendance_record` 
ADD COLUMN `clock_count` int NOT NULL DEFAULT 0 COMMENT '打卡次数' AFTER `attendance_status`,
ADD COLUMN `first_clock_time` datetime DEFAULT NULL COMMENT '首次打卡时间' AFTER `clock_count`,
ADD COLUMN `last_clock_time` datetime DEFAULT NULL COMMENT '最后打卡时间' AFTER `first_clock_time`;

-- 3. 数据迁移：为已有记录生成打卡明细
-- 迁移上班打卡记录
INSERT INTO `biz_attendance_clock` (`record_id`, `user_id`, `user_name`, `clock_time`, `clock_type`, `work_type`, `latitude`, `longitude`, `address`, `photo`, `outside_reason`, `create_time`)
SELECT 
    record_id,
    user_id,
    user_name,
    clock_in_time,
    '0',
    clock_type,
    clock_in_latitude,
    clock_in_longitude,
    clock_in_address,
    clock_in_photo,
    outside_reason,
    create_time
FROM `biz_attendance_record`
WHERE clock_in_time IS NOT NULL;

-- 迁移下班打卡记录
INSERT INTO `biz_attendance_clock` (`record_id`, `user_id`, `user_name`, `clock_time`, `clock_type`, `work_type`, `latitude`, `longitude`, `address`, `photo`, `create_time`)
SELECT 
    record_id,
    user_id,
    user_name,
    clock_out_time,
    '1',
    clock_type,
    clock_out_latitude,
    clock_out_longitude,
    clock_out_address,
    clock_out_photo,
    update_time
FROM `biz_attendance_record`
WHERE clock_out_time IS NOT NULL;

-- 4. 更新主表汇总字段
UPDATE `biz_attendance_record` r
SET 
    clock_count = (SELECT COUNT(*) FROM biz_attendance_clock WHERE record_id = r.record_id),
    first_clock_time = clock_in_time,
    last_clock_time = clock_out_time;
