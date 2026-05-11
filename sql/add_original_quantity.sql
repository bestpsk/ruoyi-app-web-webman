-- 添加原始数量字段，用于记录用户输入的原始数量（换算前）
-- 入库明细表
ALTER TABLE `biz_stock_in_item` ADD COLUMN `original_quantity` int DEFAULT NULL COMMENT '原始数量(换算前)' AFTER `unit_type`;

-- 出库明细表  
ALTER TABLE `biz_stock_out_item` ADD COLUMN `original_quantity` int DEFAULT NULL COMMENT '原始数量(换算前)' AFTER `unit_type`;
