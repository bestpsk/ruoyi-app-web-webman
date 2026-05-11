-- ========================================
-- 进销存数据清除脚本
-- 创建时间: 2026-04-30
-- 说明: 清除所有进销存业务数据，保留表结构
-- ========================================

-- 禁用外键检查（避免删除顺序问题）
SET FOREIGN_KEY_CHECKS = 0;

-- ========================================
-- 1. 清除入库相关数据
-- ========================================

-- 删除入库单明细
TRUNCATE TABLE `biz_stock_in_item`;

-- 删除入库单主表
TRUNCATE TABLE `biz_stock_in`;


-- ========================================
-- 2. 清除出库相关数据
-- ========================================

-- 删除出库单明细
TRUNCATE TABLE `biz_stock_out_item`;

-- 删除出库单主表
TRUNCATE TABLE `biz_stock_out`;


-- ========================================
-- 3. 清除盘点相关数据
-- ========================================

-- 删除盘点单明细
TRUNCATE TABLE `biz_stock_check_item`;

-- 删除盘点单主表
TRUNCATE TABLE `biz_stock_check`;


-- ========================================
-- 4. 清除库存数据
-- ========================================

-- 删除库存记录
TRUNCATE TABLE `biz_inventory`;


-- ========================================
-- 5. 清除基础数据（产品、供应商）
-- ========================================

-- 删除产品信息
TRUNCATE TABLE `biz_product`;

-- 删除供应商信息
TRUNCATE TABLE `biz_supplier`;


-- ========================================
-- 6. 重置自增ID（可选）
-- ========================================

-- 如果需要重置自增ID从1开始，取消以下注释
-- ALTER TABLE `biz_stock_in_item` AUTO_INCREMENT = 1;
-- ALTER TABLE `biz_stock_in` AUTO_INCREMENT = 1;
-- ALTER TABLE `biz_stock_out_item` AUTO_INCREMENT = 1;
-- ALTER TABLE `biz_stock_out` AUTO_INCREMENT = 1;
-- ALTER TABLE `biz_stock_check_item` AUTO_INCREMENT = 1;
-- ALTER TABLE `biz_stock_check` AUTO_INCREMENT = 1;
-- ALTER TABLE `biz_inventory` AUTO_INCREMENT = 1;
-- ALTER TABLE `biz_product` AUTO_INCREMENT = 1;
-- ALTER TABLE `biz_supplier` AUTO_INCREMENT = 1;


-- 启用外键检查
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- 执行完成提示
-- ========================================

SELECT '✅ 进销存数据已全部清除！' AS '执行结果',
       '共清空 9 张数据表' AS '说明',
       '表结构保持完整' AS '状态';
