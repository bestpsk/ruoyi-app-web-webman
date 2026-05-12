-- 为出库单表添加对象类型字段
-- 用于区分：企业出库(1) / 员工领用(2)
-- 创建时间: 2026-05-05

ALTER TABLE `biz_stock_out`
ADD COLUMN `out_target_type` VARCHAR(1) NOT NULL DEFAULT '1' COMMENT '出库对象类型（1-企业出库 2-员工领用）'
AFTER `stock_out_type`;
