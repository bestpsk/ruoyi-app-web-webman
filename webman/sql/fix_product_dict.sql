-- =============================================
-- 货品字典数据修复脚本
-- 解决：单位和规格显示数字而非中文的问题
-- =============================================

-- 1. 确保字典类型存在（忽略重复错误）
INSERT IGNORE INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`)
VALUES ('货品单位', 'biz_product_unit', '0', 'admin', NOW(), '货品单位列表');

INSERT IGNORE INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`)
VALUES ('货品规格', 'biz_product_spec', '0', 'admin', NOW(), '货品规格列表');

-- 2. 删除旧数据重新插入（确保数据完整）
DELETE FROM `sys_dict_data` WHERE `dict_type` = 'biz_product_unit';
DELETE FROM `sys_dict_data` WHERE `dict_type` = 'biz_product_spec';

-- 3. 插入单位字典数据 (biz_product_unit)
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '箱', '1', 'biz_product_unit', '', 'primary', 'N', '0', 'admin', NOW()),
(2, '件', '2', 'biz_product_unit', '', 'success', 'N', '0', 'admin', NOW()),
(3, '套', '3', 'biz_product_unit', '', 'warning', 'N', '0', 'admin', NOW()),
(4, '罐', '4', 'biz_product_unit', '', 'info', 'N', '0', 'admin', NOW()),
(5, '盒', '5', 'biz_product_unit', '', 'primary', 'Y', '0', 'admin', NOW()),
(6, '袋', '6', 'biz_product_unit', '', 'success', 'N', '0', 'admin', NOW()),
(7, '包', '7', 'biz_product_unit', '', 'warning', 'N', '0', 'admin', NOW());

-- 4. 插入规格字典数据 (biz_product_spec)
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '支', '1', 'biz_product_spec', '', 'primary', 'Y', '0', 'admin', NOW()),
(2, '瓶', '2', 'biz_product_spec', '', 'success', 'N', '0', 'admin', NOW()),
(3, '件', '3', 'biz_product_spec', '', 'warning', 'N', '0', 'admin', NOW()),
(4, '套', '4', 'biz_product_spec', '', 'info', 'N', '0', 'admin', NOW()),
(5, '片', '5', 'biz_product_spec', '', 'primary', 'N', '0', 'admin', NOW()),
(6, '个', '6', 'biz_product_spec', '', 'success', 'N', '0', 'admin', NOW());

-- 完成
SELECT '字典数据修复完成！' AS message;
SELECT * FROM sys_dict_data WHERE dict_type IN ('biz_product_unit', 'biz_product_spec') ORDER BY dict_type, dict_sort;
