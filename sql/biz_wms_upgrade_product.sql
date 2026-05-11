-- =============================================
-- 货品管理字段优化升级脚本
-- 执行顺序：1.新增字典类型 -> 2.新增字典数据 -> 3.修改表结构
-- =============================================

-- 1. 新增字典类型
INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`)
VALUES ('货品单位', 'biz_product_unit', '0', 'admin', NOW(), '货品单位列表');

INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`)
VALUES ('货品规格', 'biz_product_spec', '0', 'admin', NOW(), '货品规格列表');

-- 2. 新增字典数据
-- 货品单位
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '箱', '1', 'biz_product_unit', '', 'primary', 'N', '0', 'admin', NOW()),
(2, '件', '2', 'biz_product_unit', '', 'success', 'N', '0', 'admin', NOW()),
(3, '套', '3', 'biz_product_unit', '', 'warning', 'N', '0', 'admin', NOW()),
(4, '罐', '4', 'biz_product_unit', '', 'info', 'N', '0', 'admin', NOW()),
(5, '盒', '5', 'biz_product_unit', '', 'primary', 'Y', '0', 'admin', NOW()),
(6, '袋', '6', 'biz_product_unit', '', 'success', 'N', '0', 'admin', NOW()),
(7, '包', '7', 'biz_product_unit', '', 'warning', 'N', '0', 'admin', NOW());

-- 货品规格
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '支', '1', 'biz_product_spec', '', 'primary', 'Y', '0', 'admin', NOW()),
(2, '瓶', '2', 'biz_product_spec', '', 'success', 'N', '0', 'admin', NOW()),
(3, '件', '3', 'biz_product_spec', '', 'warning', 'N', '0', 'admin', NOW()),
(4, '套', '4', 'biz_product_spec', '', 'info', 'N', '0', 'admin', NOW()),
(5, '片', '5', 'biz_product_spec', '', 'primary', 'N', '0', 'admin', NOW()),
(6, '个', '6', 'biz_product_spec', '', 'success', 'N', '0', 'admin', NOW());

-- 3. 修改货品表结构
-- 新增字段
ALTER TABLE `biz_product` ADD COLUMN `pack_qty` int DEFAULT 1 COMMENT '换算比例(1主单位=多少副单位)' AFTER `spec`;
ALTER TABLE `biz_product` ADD COLUMN `sale_price_spec` decimal(10,2) DEFAULT 0.00 COMMENT '出货价(按副单位)' AFTER `sale_price`;

-- 修改spec字段类型为字典
ALTER TABLE `biz_product` MODIFY COLUMN `spec` char(1) DEFAULT NULL COMMENT '副单位/规格(字典biz_product_spec)';

-- 修改unit字段类型为字典
ALTER TABLE `biz_product` MODIFY COLUMN `unit` char(1) DEFAULT NULL COMMENT '主单位(字典biz_product_unit)';

-- 调整字段顺序：将category移到supplier_id后面
ALTER TABLE `biz_product` MODIFY COLUMN `category` char(1) NOT NULL DEFAULT '1' COMMENT '类别(1院装-面部 2院装-身体 3仪器-面部 4仪器-身体 5家居-面部 6家居-身体)' AFTER `supplier_id`;

-- 4. 修改入库明细表结构
-- 新增单位类型字段(1主单位 2副单位)
ALTER TABLE `biz_stock_in_item` ADD COLUMN `unit_type` char(1) DEFAULT '1' COMMENT '单位类型(1主单位 2副单位)' AFTER `unit`;

-- 5. 修改出库明细表结构
-- 新增单位类型字段(1主单位 2副单位)
ALTER TABLE `biz_stock_out_item` ADD COLUMN `unit_type` char(1) DEFAULT '1' COMMENT '单位类型(1主单位 2副单位)' AFTER `unit`;
