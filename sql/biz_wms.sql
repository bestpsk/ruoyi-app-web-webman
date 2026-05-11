-- =============================================
-- 进销存管理模块数据库脚本
-- 执行顺序：1.建表 -> 2.字典类型 -> 3.字典数据 -> 4.菜单 -> 5.角色菜单权限
-- =============================================

-- =============================================
-- 1. 创建业务表
-- =============================================

-- 1.1 供货商表
DROP TABLE IF EXISTS `biz_supplier`;
CREATE TABLE `biz_supplier` (
  `supplier_id` bigint NOT NULL AUTO_INCREMENT COMMENT '供货商ID',
  `supplier_name` varchar(100) NOT NULL COMMENT '供货商名称',
  `contact_person` varchar(50) DEFAULT NULL COMMENT '联系人',
  `contact_phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `address` varchar(255) DEFAULT NULL COMMENT '地址',
  `cooperation_start_date` date DEFAULT NULL COMMENT '合作起始日期',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0正常 1停用)',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`supplier_id`),
  KEY `idx_supplier_name` (`supplier_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='供货商表';

-- 1.2 货品表
DROP TABLE IF EXISTS `biz_product`;
CREATE TABLE `biz_product` (
  `product_id` bigint NOT NULL AUTO_INCREMENT COMMENT '货品ID',
  `product_name` varchar(100) NOT NULL COMMENT '品名',
  `product_code` varchar(50) NOT NULL COMMENT '货品编码',
  `supplier_id` bigint DEFAULT NULL COMMENT '供货商ID',
  `spec` varchar(100) DEFAULT NULL COMMENT '规格',
  `category` char(1) NOT NULL DEFAULT '1' COMMENT '类别(1院装-面部 2院装-身体 3仪器-面部 4仪器-身体 5家居-面部 6家居-身体)',
  `unit` varchar(20) DEFAULT NULL COMMENT '单位',
  `purchase_price` decimal(10,2) DEFAULT 0.00 COMMENT '进货价',
  `sale_price` decimal(10,2) DEFAULT 0.00 COMMENT '出货价',
  `warn_qty` int DEFAULT 0 COMMENT '库存预警数量',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0正常 1停用)',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `uk_product_code` (`product_code`),
  KEY `idx_product_name` (`product_name`),
  KEY `idx_supplier_id` (`supplier_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='货品表';

-- 1.3 库存表
DROP TABLE IF EXISTS `biz_inventory`;
CREATE TABLE `biz_inventory` (
  `inventory_id` bigint NOT NULL AUTO_INCREMENT COMMENT '库存ID',
  `product_id` bigint NOT NULL COMMENT '货品ID',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '当前库存数量',
  `warn_qty` int DEFAULT 0 COMMENT '预警数量',
  `last_stock_in_time` datetime DEFAULT NULL COMMENT '最后入库时间',
  `last_stock_out_time` datetime DEFAULT NULL COMMENT '最后出库时间',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`inventory_id`),
  UNIQUE KEY `uk_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='库存表';

-- 1.4 入库单主表
DROP TABLE IF EXISTS `biz_stock_in`;
CREATE TABLE `biz_stock_in` (
  `stock_in_id` bigint NOT NULL AUTO_INCREMENT COMMENT '入库单ID',
  `stock_in_no` varchar(30) NOT NULL COMMENT '入库单号',
  `stock_in_type` char(1) NOT NULL DEFAULT '1' COMMENT '入库类型(1采购入库 2退货入库 3其他入库)',
  `supplier_id` bigint DEFAULT NULL COMMENT '供货商ID',
  `total_quantity` int DEFAULT 0 COMMENT '总数量',
  `total_amount` decimal(12,2) DEFAULT 0.00 COMMENT '总金额',
  `stock_in_date` date DEFAULT NULL COMMENT '入库日期',
  `operator_id` bigint DEFAULT NULL COMMENT '操作人ID',
  `operator_name` varchar(50) DEFAULT NULL COMMENT '操作人姓名',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0待确认 1已确认)',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`stock_in_id`),
  UNIQUE KEY `uk_stock_in_no` (`stock_in_no`),
  KEY `idx_supplier_id` (`supplier_id`),
  KEY `idx_stock_in_date` (`stock_in_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='入库单主表';

-- 1.5 入库单明细表
DROP TABLE IF EXISTS `biz_stock_in_item`;
CREATE TABLE `biz_stock_in_item` (
  `item_id` bigint NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `stock_in_id` bigint NOT NULL COMMENT '入库单ID',
  `product_id` bigint NOT NULL COMMENT '货品ID',
  `product_name` varchar(100) DEFAULT NULL COMMENT '货品名称',
  `spec` varchar(100) DEFAULT NULL COMMENT '规格',
  `unit` varchar(20) DEFAULT NULL COMMENT '单位',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '入库数量',
  `purchase_price` decimal(10,2) DEFAULT 0.00 COMMENT '进货单价',
  `amount` decimal(12,2) DEFAULT 0.00 COMMENT '金额',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`item_id`),
  KEY `idx_stock_in_id` (`stock_in_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='入库单明细表';

-- 1.6 出库单主表
DROP TABLE IF EXISTS `biz_stock_out`;
CREATE TABLE `biz_stock_out` (
  `stock_out_id` bigint NOT NULL AUTO_INCREMENT COMMENT '出库单ID',
  `stock_out_no` varchar(30) NOT NULL COMMENT '出库单号',
  `stock_out_type` char(1) NOT NULL DEFAULT '1' COMMENT '出库类型(1销售出库 2调拨出库 3其他出库)',
  `enterprise_id` bigint DEFAULT NULL COMMENT '出库企业ID',
  `enterprise_name` varchar(100) DEFAULT NULL COMMENT '出库企业名称',
  `responsible_id` bigint DEFAULT NULL COMMENT '负责员工ID',
  `responsible_name` varchar(50) DEFAULT NULL COMMENT '负责员工姓名',
  `total_quantity` int DEFAULT 0 COMMENT '总数量',
  `total_amount` decimal(12,2) DEFAULT 0.00 COMMENT '总金额',
  `stock_out_date` date DEFAULT NULL COMMENT '出库日期',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0待确认 1已确认)',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`stock_out_id`),
  UNIQUE KEY `uk_stock_out_no` (`stock_out_no`),
  KEY `idx_enterprise_id` (`enterprise_id`),
  KEY `idx_responsible_id` (`responsible_id`),
  KEY `idx_stock_out_date` (`stock_out_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='出库单主表';

-- 1.7 出库单明细表
DROP TABLE IF EXISTS `biz_stock_out_item`;
CREATE TABLE `biz_stock_out_item` (
  `item_id` bigint NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `stock_out_id` bigint NOT NULL COMMENT '出库单ID',
  `product_id` bigint NOT NULL COMMENT '货品ID',
  `product_name` varchar(100) DEFAULT NULL COMMENT '货品名称',
  `spec` varchar(100) DEFAULT NULL COMMENT '规格',
  `unit` varchar(20) DEFAULT NULL COMMENT '单位',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '出库数量',
  `sale_price` decimal(10,2) DEFAULT 0.00 COMMENT '出货单价',
  `amount` decimal(12,2) DEFAULT 0.00 COMMENT '金额',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`item_id`),
  KEY `idx_stock_out_id` (`stock_out_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='出库单明细表';

-- 1.8 盘点单主表
DROP TABLE IF EXISTS `biz_stock_check`;
CREATE TABLE `biz_stock_check` (
  `stock_check_id` bigint NOT NULL AUTO_INCREMENT COMMENT '盘点单ID',
  `stock_check_no` varchar(30) NOT NULL COMMENT '盘点单号',
  `check_date` date DEFAULT NULL COMMENT '盘点日期',
  `total_quantity` int DEFAULT 0 COMMENT '盘点总数量',
  `total_diff_quantity` int DEFAULT 0 COMMENT '差异数量合计',
  `operator_id` bigint DEFAULT NULL COMMENT '操作人ID',
  `operator_name` varchar(50) DEFAULT NULL COMMENT '操作人姓名',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态(0待确认 1已确认)',
  `remark` text DEFAULT NULL COMMENT '备注',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`stock_check_id`),
  UNIQUE KEY `uk_stock_check_no` (`stock_check_no`),
  KEY `idx_check_date` (`check_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='盘点单主表';

-- 1.9 盘点单明细表
DROP TABLE IF EXISTS `biz_stock_check_item`;
CREATE TABLE `biz_stock_check_item` (
  `item_id` bigint NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `stock_check_id` bigint NOT NULL COMMENT '盘点单ID',
  `product_id` bigint NOT NULL COMMENT '货品ID',
  `product_name` varchar(100) DEFAULT NULL COMMENT '货品名称',
  `spec` varchar(100) DEFAULT NULL COMMENT '规格',
  `unit` varchar(20) DEFAULT NULL COMMENT '单位',
  `system_quantity` int NOT NULL DEFAULT 0 COMMENT '系统库存数量',
  `actual_quantity` int NOT NULL DEFAULT 0 COMMENT '实际盘点数量',
  `diff_quantity` int NOT NULL DEFAULT 0 COMMENT '差异数量',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`item_id`),
  KEY `idx_stock_check_id` (`stock_check_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='盘点单明细表';

-- =============================================
-- 2. 插入字典类型
-- =============================================

INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`)
VALUES ('货品类别', 'biz_product_category', '0', 'admin', NOW(), '货品类别列表');

INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`)
VALUES ('入库类型', 'biz_stock_in_type', '0', 'admin', NOW(), '入库类型列表');

INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`)
VALUES ('出库类型', 'biz_stock_out_type', '0', 'admin', NOW(), '出库类型列表');

INSERT INTO `sys_dict_type` (`dict_name`, `dict_type`, `status`, `create_by`, `create_time`, `remark`)
VALUES ('单据确认状态', 'biz_doc_status', '0', 'admin', NOW(), '单据确认状态列表');

-- =============================================
-- 3. 插入字典数据
-- =============================================

-- 货品类别
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '院装-面部', '1', 'biz_product_category', '', 'primary', 'N', '0', 'admin', NOW()),
(2, '院装-身体', '2', 'biz_product_category', '', 'success', 'N', '0', 'admin', NOW()),
(3, '仪器-面部', '3', 'biz_product_category', '', 'warning', 'N', '0', 'admin', NOW()),
(4, '仪器-身体', '4', 'biz_product_category', '', 'danger', 'N', '0', 'admin', NOW()),
(5, '家居-面部', '5', 'biz_product_category', '', 'info', 'N', '0', 'admin', NOW()),
(6, '家居-身体', '6', 'biz_product_category', '', 'default', 'N', '0', 'admin', NOW());

-- 入库类型
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '采购入库', '1', 'biz_stock_in_type', '', 'primary', 'Y', '0', 'admin', NOW()),
(2, '退货入库', '2', 'biz_stock_in_type', '', 'warning', 'N', '0', 'admin', NOW()),
(3, '其他入库', '3', 'biz_stock_in_type', '', 'info', 'N', '0', 'admin', NOW());

-- 出库类型
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '销售出库', '1', 'biz_stock_out_type', '', 'primary', 'Y', '0', 'admin', NOW()),
(2, '调拨出库', '2', 'biz_stock_out_type', '', 'warning', 'N', '0', 'admin', NOW()),
(3, '其他出库', '3', 'biz_stock_out_type', '', 'info', 'N', '0', 'admin', NOW());

-- 单据确认状态
INSERT INTO `sys_dict_data` (`dict_sort`, `dict_label`, `dict_value`, `dict_type`, `css_class`, `list_class`, `is_default`, `status`, `create_by`, `create_time`) VALUES
(1, '待确认', '0', 'biz_doc_status', '', 'warning', 'Y', '0', 'admin', NOW()),
(2, '已确认', '1', 'biz_doc_status', '', 'success', 'N', '0', 'admin', NOW());

-- =============================================
-- 4. 插入菜单数据
-- =============================================

-- 4.1 一级目录：进销存管理
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`)
VALUES ('进销存管理', 0, 3, 'wms', NULL, NULL, NULL, 1, 0, 'M', '0', '0', '', 'shopping', 'admin', NOW());

-- 4.2 供货商管理菜单
SET @wms_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '进销存管理' AND parent_id = 0) t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`)
VALUES ('供货商管理', @wms_menu_id, 1, 'supplier', 'wms/supplier/index', NULL, 'WmsSupplier', 1, 0, 'C', '0', '0', 'wms:supplier:list', 'peoples', 'admin', NOW());

SET @supplier_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '供货商管理' AND path = 'supplier') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('供货商查询', @supplier_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:supplier:query', '#', 'admin', NOW()),
('供货商新增', @supplier_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:supplier:add', '#', 'admin', NOW()),
('供货商修改', @supplier_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:supplier:edit', '#', 'admin', NOW()),
('供货商删除', @supplier_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:supplier:remove', '#', 'admin', NOW()),
('供货商导出', @supplier_menu_id, 5, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:supplier:export', '#', 'admin', NOW());

-- 4.3 货品管理菜单
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`)
VALUES ('货品管理', @wms_menu_id, 2, 'product', 'wms/product/index', NULL, 'WmsProduct', 1, 0, 'C', '0', '0', 'wms:product:list', 'component', 'admin', NOW());

SET @product_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '货品管理' AND path = 'product') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('货品查询', @product_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:product:query', '#', 'admin', NOW()),
('货品新增', @product_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:product:add', '#', 'admin', NOW()),
('货品修改', @product_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:product:edit', '#', 'admin', NOW()),
('货品删除', @product_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:product:remove', '#', 'admin', NOW()),
('货品导出', @product_menu_id, 5, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:product:export', '#', 'admin', NOW());

-- 4.4 入库管理菜单
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`)
VALUES ('入库管理', @wms_menu_id, 3, 'stockIn', 'wms/stockIn/index', NULL, 'WmsStockIn', 1, 0, 'C', '0', '0', 'wms:stockIn:list', 'download', 'admin', NOW());

SET @stock_in_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '入库管理' AND path = 'stockIn') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('入库查询', @stock_in_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockIn:query', '#', 'admin', NOW()),
('入康新增', @stock_in_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockIn:add', '#', 'admin', NOW()),
('入库修改', @stock_in_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockIn:edit', '#', 'admin', NOW()),
('入库删除', @stock_in_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockIn:remove', '#', 'admin', NOW()),
('入库确认', @stock_in_menu_id, 5, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockIn:confirm', '#', 'admin', NOW()),
('入库导出', @stock_in_menu_id, 6, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockIn:export', '#', 'admin', NOW());

-- 4.5 出库管理菜单
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`)
VALUES ('出库管理', @wms_menu_id, 4, 'stockOut', 'wms/stockOut/index', NULL, 'WmsStockOut', 1, 0, 'C', '0', '0', 'wms:stockOut:list', 'upload', 'admin', NOW());

SET @stock_out_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '出库管理' AND path = 'stockOut') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('出库查询', @stock_out_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockOut:query', '#', 'admin', NOW()),
('出库新增', @stock_out_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockOut:add', '#', 'admin', NOW()),
('出库修改', @stock_out_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockOut:edit', '#', 'admin', NOW()),
('出库删除', @stock_out_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockOut:remove', '#', 'admin', NOW()),
('出库确认', @stock_out_menu_id, 5, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockOut:confirm', '#', 'admin', NOW()),
('出库导出', @stock_out_menu_id, 6, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockOut:export', '#', 'admin', NOW());

-- 4.6 库存查看菜单
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`)
VALUES ('库存查看', @wms_menu_id, 5, 'inventory', 'wms/inventory/index', NULL, 'WmsInventory', 1, 0, 'C', '0', '0', 'wms:inventory:list', 'list', 'admin', NOW());

SET @inventory_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '库存查看' AND path = 'inventory') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('库存导出', @inventory_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:inventory:export', '#', 'admin', NOW());

-- 4.7 库存盘点菜单
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`)
VALUES ('库存盘点', @wms_menu_id, 6, 'stockCheck', 'wms/stockCheck/index', NULL, 'WmsStockCheck', 1, 0, 'C', '0', '0', 'wms:stockCheck:list', 'clipboard', 'admin', NOW());

SET @stock_check_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '库存盘点' AND path = 'stockCheck') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('盘点查询', @stock_check_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockCheck:query', '#', 'admin', NOW()),
('盘点新增', @stock_check_menu_id, 2, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockCheck:add', '#', 'admin', NOW()),
('盘点修改', @stock_check_menu_id, 3, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockCheck:edit', '#', 'admin', NOW()),
('盘点删除', @stock_check_menu_id, 4, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockCheck:remove', '#', 'admin', NOW()),
('盘点确认', @stock_check_menu_id, 5, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockCheck:confirm', '#', 'admin', NOW()),
('盘点导出', @stock_check_menu_id, 6, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:stockCheck:export', '#', 'admin', NOW());

-- 4.8 进销存报表菜单
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`)
VALUES ('进销存报表', @wms_menu_id, 7, 'report', 'wms/report/index', NULL, 'WmsReport', 1, 0, 'C', '0', '0', 'wms:report:list', 'chart', 'admin', NOW());

SET @report_menu_id = (SELECT menu_id FROM (SELECT menu_id FROM sys_menu WHERE menu_name = '进销存报表' AND path = 'report') t);
INSERT INTO `sys_menu` (`menu_name`, `parent_id`, `order_num`, `path`, `component`, `query`, `route_name`, `is_frame`, `is_cache`, `menu_type`, `visible`, `status`, `perms`, `icon`, `create_by`, `create_time`) VALUES
('报表导出', @report_menu_id, 1, '', NULL, NULL, NULL, 1, 0, 'F', '0', '0', 'wms:report:export', '#', 'admin', NOW());

-- =============================================
-- 5. 为管理员角色分配菜单权限
-- =============================================

SET @admin_role_id = 1;
SET @wms_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '进销存管理' AND parent_id = 0);
SET @supplier_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '供货商管理' AND path = 'supplier');
SET @product_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '货品管理' AND path = 'product');
SET @stock_in_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '入库管理' AND path = 'stockIn');
SET @stock_out_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '出库管理' AND path = 'stockOut');
SET @inventory_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '库存查看' AND path = 'inventory');
SET @stock_check_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '库存盘点' AND path = 'stockCheck');
SET @report_menu = (SELECT menu_id FROM sys_menu WHERE menu_name = '进销存报表' AND path = 'report');

INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) VALUES
(@admin_role_id, @wms_menu),
(@admin_role_id, @supplier_menu),
(@admin_role_id, @product_menu),
(@admin_role_id, @stock_in_menu),
(@admin_role_id, @stock_out_menu),
(@admin_role_id, @inventory_menu),
(@admin_role_id, @stock_check_menu),
(@admin_role_id, @report_menu);

INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @supplier_menu;
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @product_menu;
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @stock_in_menu;
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @stock_out_menu;
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @inventory_menu;
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @stock_check_menu;
INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) SELECT @admin_role_id, menu_id FROM sys_menu WHERE parent_id = @report_menu;
