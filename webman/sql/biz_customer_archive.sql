-- =============================================
-- 客户档案表
-- =============================================
CREATE TABLE IF NOT EXISTS `biz_customer_archive` (
  `archive_id` bigint NOT NULL AUTO_INCREMENT COMMENT '档案ID',
  `customer_id` bigint NOT NULL COMMENT '客户ID',
  `customer_name` varchar(50) DEFAULT NULL COMMENT '客户姓名',
  `enterprise_id` bigint DEFAULT NULL COMMENT '企业ID',
  `store_id` bigint DEFAULT NULL COMMENT '门店ID',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户档案表';
