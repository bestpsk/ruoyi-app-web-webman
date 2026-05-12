-- =============================================
-- 还款管理模块 - 数据库脚本
-- 包含：还款记录表
-- =============================================

CREATE TABLE IF NOT EXISTS `biz_repayment_record` (
  `repayment_id` bigint NOT NULL AUTO_INCREMENT COMMENT '还款ID',
  `repayment_no` varchar(50) DEFAULT NULL COMMENT '还款编号',
  `customer_id` bigint DEFAULT NULL COMMENT '客户ID',
  `customer_name` varchar(100) DEFAULT NULL COMMENT '客户名称',
  `package_id` bigint DEFAULT NULL COMMENT '套餐ID（还款来源）',
  `package_no` varchar(50) DEFAULT NULL COMMENT '套餐编号',
  `package_name` varchar(200) DEFAULT NULL COMMENT '套餐名称',
  `order_id` bigint DEFAULT NULL COMMENT '原订单ID',
  `order_no` varchar(50) DEFAULT NULL COMMENT '原订单编号',
  `repayment_order_id` bigint DEFAULT NULL COMMENT '还款订单ID',
  `repayment_order_no` varchar(50) DEFAULT NULL COMMENT '还款订单编号',
  `repayment_amount` decimal(12,2) DEFAULT 0.00 COMMENT '还款金额',
  `repayment_type` char(1) DEFAULT '1' COMMENT '还款类型：1-套餐还款 2-订单还款',
  `payment_method` varchar(50) DEFAULT NULL COMMENT '支付方式',
  `status` char(1) DEFAULT '0' COMMENT '状态：0-待审核 1-已审核 2-已取消',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `enterprise_id` bigint DEFAULT NULL COMMENT '企业ID',
  `enterprise_name` varchar(100) DEFAULT NULL COMMENT '企业名称',
  `store_id` bigint DEFAULT NULL COMMENT '门店ID',
  `store_name` varchar(100) DEFAULT NULL COMMENT '门店名称',
  `create_by` varchar(64) DEFAULT NULL COMMENT '创建者',
  `creator_user_id` bigint DEFAULT NULL COMMENT '创建用户ID',
  `creator_user_name` varchar(64) DEFAULT NULL COMMENT '创建用户名称',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT NULL COMMENT '更新者',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `audit_by` varchar(64) DEFAULT NULL COMMENT '审核人',
  `audit_time` datetime DEFAULT NULL COMMENT '审核时间',
  PRIMARY KEY (`repayment_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_package_id` (`package_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='还款记录表';

-- =============================================
-- 如果表已存在，添加新字段
-- =============================================
-- ALTER TABLE `biz_repayment_record` ADD COLUMN `repayment_order_id` bigint DEFAULT NULL COMMENT '还款订单ID' AFTER `order_no`;
-- ALTER TABLE `biz_repayment_record` ADD COLUMN `repayment_order_no` varchar(50) DEFAULT NULL COMMENT '还款订单编号' AFTER `repayment_order_id`;
