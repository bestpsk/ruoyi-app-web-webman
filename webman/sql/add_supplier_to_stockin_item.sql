-- 给入库明细表添加供货商字段
-- 用于存储每条入库明细对应的货品供货商信息

ALTER TABLE `biz_stock_in_item` 
ADD COLUMN `supplier_id` bigint DEFAULT NULL COMMENT '供货商ID' AFTER `product_name`,
ADD COLUMN `supplier_name` varchar(100) DEFAULT NULL COMMENT '供货商名称' AFTER `supplier_id`;
