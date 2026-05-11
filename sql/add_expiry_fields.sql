-- =============================================
-- 货品有效期管理功能 - 数据库升级脚本
-- =============================================

-- 1. 货品表添加有效期相关字段
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'biz_product' AND COLUMN_NAME = 'shelf_life_days');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `biz_product` ADD COLUMN `shelf_life_days` int DEFAULT NULL COMMENT \'保质期(天)\' AFTER `sale_price_spec`', 'SELECT \'shelf_life_days already exists\' as result');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'biz_product' AND COLUMN_NAME = 'has_expiry');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `biz_product` ADD COLUMN `has_expiry` char(1) DEFAULT \'0\' COMMENT \'是否有有效期(0否 1是)\' AFTER `shelf_life_days`', 'SELECT \'has_expiry already exists\' as result');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. 入库明细表添加生产日期和有效期字段
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'biz_stock_in_item' AND COLUMN_NAME = 'production_date');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `biz_stock_in_item` ADD COLUMN `production_date` date DEFAULT NULL COMMENT \'生产日期\' AFTER `remark`', 'SELECT \'production_date already exists\' as result');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'biz_stock_in_item' AND COLUMN_NAME = 'expiry_date');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `biz_stock_in_item` ADD COLUMN `expiry_date` date DEFAULT NULL COMMENT \'有效期至\' AFTER `production_date`', 'SELECT \'expiry_date already exists\' as result');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. 库存表添加最早到期时间字段
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'biz_inventory' AND COLUMN_NAME = 'earliest_expiry');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `biz_inventory` ADD COLUMN `earliest_expiry` date DEFAULT NULL COMMENT \'最早批次有效期至\' AFTER `quantity`', 'SELECT \'earliest_expiry already exists\' as result');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. 验证
SELECT '✅ 有效期字段添加完成！' AS message;
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN ('biz_product', 'biz_stock_in_item', 'biz_inventory')
AND COLUMN_NAME IN ('shelf_life_days', 'has_expiry', 'production_date', 'expiry_date', 'earliest_expiry')
ORDER BY TABLE_NAME, COLUMN_NAME;
