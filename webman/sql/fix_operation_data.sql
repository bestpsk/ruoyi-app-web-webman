-- =============================================
-- 修复操作类型订单显示企业和门店名称 - 数据补充
-- 日期: 2026-01-09
-- =============================================

-- 1. 从客户表补充操作记录的企业和门店信息
UPDATE `biz_operation_record` or_
INNER JOIN `biz_customer` c ON or_.customer_id = c.customer_id
SET or_.enterprise_name = c.enterprise_name,
    or_.store_name = c.store_name
WHERE (or_.enterprise_name IS NULL OR or_.store_name IS NULL);

-- 2. 如果客户表没有，则从订单表通过套餐关联获取
UPDATE `biz_operation_record` or_
INNER JOIN `biz_customer_package` pkg ON or_.package_id = pkg.package_id
INNER JOIN `biz_sales_order` so ON pkg.order_id = so.order_id
SET or_.enterprise_name = so.enterprise_name,
    or_.store_name = so.store_name
WHERE (or_.enterprise_name IS NULL OR or_.store_name IS NULL)
  AND or_.package_id IS NOT NULL;

-- 3. 为套餐表添加企业名称和门店名称字段（如果不存在）
SET @dbname = DATABASE();
SET @tablename = 'biz_customer_package';
SET @colname = 'enterprise_name';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
  'SELECT 1',
  'ALTER TABLE `biz_customer_package` ADD COLUMN `enterprise_name` varchar(100) DEFAULT NULL COMMENT ''企业名称'' AFTER `enterprise_id`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @colname = 'store_name';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
  'SELECT 1',
  'ALTER TABLE `biz_customer_package` ADD COLUMN `store_name` varchar(100) DEFAULT NULL COMMENT ''门店名称'' AFTER `store_id`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 4. 从订单表补充套餐表的企业和门店信息
UPDATE `biz_customer_package` pkg
INNER JOIN `biz_sales_order` so ON pkg.order_id = so.order_id
SET pkg.enterprise_name = so.enterprise_name,
    pkg.store_name = so.store_name
WHERE (pkg.enterprise_name IS NULL OR pkg.store_name IS NULL)
  AND pkg.order_id IS NOT NULL;

-- 5. 补充操作类型档案（source_type='1'）的企业和门店名称
UPDATE `biz_customer_archive` ca
INNER JOIN `biz_operation_record` or_ ON ca.source_id = or_.record_id AND ca.source_type = '1'
SET ca.enterprise_name = or_.enterprise_name,
    ca.store_name = or_.store_name
WHERE ca.enterprise_name IS NULL OR ca.store_name IS NULL;

-- 6. 如果还没有，从客户表直接补充档案数据
UPDATE `biz_customer_archive` ca
INNER JOIN `biz_customer` c ON ca.customer_id = c.customer_id
SET ca.enterprise_name = c.enterprise_name,
    ca.store_name = c.store_name
WHERE (ca.enterprise_name IS NULL OR ca.store_name IS NULL);

SELECT '✅ 所有数据修复完成！' AS status;
