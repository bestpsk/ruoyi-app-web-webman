-- =============================================
-- 修复操作类型订单显示企业和门店名称
-- 日期: 2026-01-09
-- =============================================

-- 1. 为操作记录表添加企业名称和门店名称字段
SET @dbname = DATABASE();
SET @tablename = 'biz_operation_record';

-- 添加 enterprise_name 字段
SET @colname = 'enterprise_name';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
  'SELECT 1',
  'ALTER TABLE `biz_operation_record` ADD COLUMN `enterprise_name` varchar(100) DEFAULT NULL COMMENT ''企业名称'' AFTER `enterprise_id`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 添加 store_name 字段
SET @colname = 'store_name';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
  'SELECT 1',
  'ALTER TABLE `biz_operation_record` ADD COLUMN `store_name` varchar(100) DEFAULT NULL COMMENT ''门店名称'' AFTER `store_id`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. 从套餐表补充操作记录的企业和门店信息
UPDATE `biz_operation_record` or_
INNER JOIN `biz_customer_package` pkg ON or_.package_id = pkg.package_id
SET or_.enterprise_name = pkg.enterprise_name,
    or_.store_name = pkg.store_name
WHERE (or_.enterprise_name IS NULL OR or_.store_name IS NULL)
  AND or_.package_id IS NOT NULL;

-- 3. 从客户表补充剩余的操作记录信息
UPDATE `biz_operation_record` or_
INNER JOIN `biz_customer` c ON or_.customer_id = c.customer_id
SET or_.enterprise_name = c.enterprise_name,
    or_.store_name = c.store_name
WHERE (or_.enterprise_name IS NULL OR or_.store_name IS NULL);

-- 4. 补充操作类型档案（source_type='1'）的企业和门店名称
UPDATE `biz_customer_archive` ca
INNER JOIN `biz_operation_record` or_ ON ca.source_id = or_.record_id AND ca.source_type = '1'
SET ca.enterprise_name = or_.enterprise_name,
    ca.store_name = or_.store_name
WHERE ca.enterprise_name IS NULL OR ca.store_name IS NULL;

SELECT '✅ 操作记录和档案数据修复完成！' AS status;

-- 验证：查看操作记录数据
SELECT record_id, customer_name, enterprise_name, store_name, operation_date 
FROM biz_operation_record 
LIMIT 10;

-- 验证：查看操作类型档案数据
SELECT archive_id, customer_name, enterprise_name, store_name, source_type 
FROM biz_customer_archive 
WHERE source_type = '1'
LIMIT 10;
