-- 修复入库明细表和出库明细表缺失字段
-- 执行此脚本前请先备份数据

-- 1. 修复入库明细表 biz_stock_in_item
-- 添加 pack_qty 字段（换算比例）
ALTER TABLE `biz_stock_in_item` ADD COLUMN `pack_qty` int DEFAULT 1 COMMENT '换算比例' AFTER `unit`;

-- 添加 unit_type 字段（单位类型）
ALTER TABLE `biz_stock_in_item` ADD COLUMN `unit_type` char(1) DEFAULT '1' COMMENT '单位类型(1主单位 2副单位)' AFTER `unit`;

-- 添加 original_quantity 字段（原始数量）
ALTER TABLE `biz_stock_in_item` ADD COLUMN `original_quantity` int DEFAULT NULL COMMENT '原始数量(换算前)' AFTER `unit_type`;

-- 2. 修复出库明细表 biz_stock_out_item
-- 添加 pack_qty 字段（换算比例）
ALTER TABLE `biz_stock_out_item` ADD COLUMN `pack_qty` int DEFAULT 1 COMMENT '换算比例' AFTER `unit`;

-- 添加 unit_type 字段（单位类型）
ALTER TABLE `biz_stock_out_item` ADD COLUMN `unit_type` char(1) DEFAULT '1' COMMENT '单位类型(1主单位 2副单位)' AFTER `unit`;

-- 添加 original_quantity 字段（原始数量）
ALTER TABLE `biz_stock_out_item` ADD COLUMN `original_quantity` int DEFAULT NULL COMMENT '原始数量(换算前)' AFTER `unit_type`;
