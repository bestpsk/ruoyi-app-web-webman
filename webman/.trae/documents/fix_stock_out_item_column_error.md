# 修复出库单新增报错计划

## 问题分析

### 错误信息
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'pack_qty' in 'field list'
```

### 根本原因
数据库表 `biz_stock_out_item` 缺少以下字段：
- `pack_qty` - 换算比例字段（从未添加过）
- `unit_type` - 单位类型字段（可能在升级脚本中定义但未执行）

### 代码与数据库不一致
1. **模型定义** ([BizStockOutItem.php:14](file:///d:/fuchenpro/webman/app/model/BizStockOutItem.php#L14))：
   - `$fillable` 包含 `pack_qty`, `unit_type`, `original_quantity`

2. **服务代码** ([BizStockOutService.php:94-95](file:///d:/fuchenpro/webman/app/service/BizStockOutService.php#L94))：
   - 使用 `unit_type` 和 `pack_qty` 进行单位换算逻辑

3. **数据库表结构**：
   - 原始表 `biz_stock_out_item` 只有基础字段
   - `biz_wms_upgrade_product.sql` 只添加了 `unit_type`，没有添加 `pack_qty`
   - `add_original_quantity.sql` 添加了 `original_quantity`

## 解决方案

### 步骤1：创建数据库修复脚本
创建新的SQL脚本 `sql/fix_stock_out_item_columns.sql`，添加缺失的字段：

```sql
-- 修复出库明细表缺失字段
-- 添加 pack_qty 字段
ALTER TABLE `biz_stock_out_item` ADD COLUMN `pack_qty` int DEFAULT 1 COMMENT '换算比例' AFTER `unit`;

-- 添加 unit_type 字段（如果不存在）
ALTER TABLE `biz_stock_out_item` ADD COLUMN `unit_type` char(1) DEFAULT '1' COMMENT '单位类型(1主单位 2副单位)' AFTER `unit`;
```

### 步骤2：执行SQL脚本
在数据库中执行修复脚本，添加缺失字段。

### 步骤3：验证修复
重新测试出库单新增功能，确认错误已解决。

## 文件变更清单

| 文件 | 操作 |
|------|------|
| `sql/fix_stock_out_item_columns.sql` | 新建 |
