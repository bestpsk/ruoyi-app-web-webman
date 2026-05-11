-- =============================================
-- 完整修复脚本：首页最近订单显示企业和门店名称
-- 日期: 2026-01-09
-- 兼容MySQL 5.x/8.x
-- =============================================

-- 1. 创建客户档案表（如果不存在）
CREATE TABLE IF NOT EXISTS `biz_customer_archive` (
  `archive_id` bigint NOT NULL AUTO_INCREMENT COMMENT '档案ID',
  `customer_id` bigint NOT NULL COMMENT '客户ID',
  `customer_name` varchar(50) DEFAULT NULL COMMENT '客户姓名',
  `enterprise_id` bigint DEFAULT NULL COMMENT '企业ID',
  `enterprise_name` varchar(100) DEFAULT NULL COMMENT '企业名称',
  `store_id` bigint DEFAULT NULL COMMENT '门店ID',
  `store_name` varchar(100) DEFAULT NULL COMMENT '门店名称',
  `archive_date` date DEFAULT NULL COMMENT '档案日期',
  `source_type` char(1) NOT NULL DEFAULT '3' COMMENT '来源类型(0开单 1操作 2还款 3手动新增)',
  `source_id` bigint DEFAULT NULL COMMENT '来源记录ID',
  `plan_items` text DEFAULT NULL COMMENT '方案项目JSON:[{name,quantity}]',
  `amount` decimal(12,2) DEFAULT 0.00 COMMENT '金额',
  `satisfaction` tinyint DEFAULT NULL COMMENT '满意度(1-5星)',
  `photos` text DEFAULT NULL COMMENT '照片JSON数组',
  `customer_feedback` varchar(500) DEFAULT NULL COMMENT '顾客反馈',
  `operator_user_id` bigint DEFAULT NULL COMMENT '操作人ID',
  `operator_user_name` varchar(50) DEFAULT NULL COMMENT '操作人姓名',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`archive_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_archive_date` (`archive_date`),
  KEY `idx_source_type` (`source_type`),
  KEY `idx_enterprise_id` (`enterprise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='客户档案表';

-- 2. 添加缺失字段（兼容处理，忽略已存在的字段错误）
SET @dbname = DATABASE();
SET @tablename = 'biz_customer_archive';

-- 检查并添加 enterprise_name 字段
SET @colname = 'enterprise_name';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
  'SELECT 1',
  'ALTER TABLE `biz_customer_archive` ADD COLUMN `enterprise_name` varchar(100) DEFAULT NULL COMMENT ''企业名称'' AFTER `enterprise_id`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 检查并添加 store_name 字段
SET @colname = 'store_name';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
  'SELECT 1',
  'ALTER TABLE `biz_customer_archive` ADD COLUMN `store_name` varchar(100) DEFAULT NULL COMMENT ''门店名称'' AFTER `store_id`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. 从现有订单数据补充历史档案的企业和门店名称
UPDATE `biz_customer_archive` ca
INNER JOIN `biz_sales_order` so ON ca.source_id = so.order_id AND ca.source_type = '0'
SET ca.enterprise_name = so.enterprise_name,
    ca.store_name = so.store_name
WHERE ca.enterprise_name IS NULL OR ca.store_name IS NULL;

SELECT '✅ 数据库修复完成！' AS status;
