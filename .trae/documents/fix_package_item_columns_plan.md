# 修复：biz_package_item 表缺少 unit_price 和 plan_price 字段

## 问题分析

开单报错：`Unknown column 'unit_price' in 'field list'`

**根本原因**：数据库中 `biz_package_item` 表缺少 `unit_price` 和 `plan_price` 两个字段。

- SQL建表脚本 `biz_sales.sql` 第113-125行的 CREATE TABLE 语句已包含这两个字段
- 但实际数据库中的表是在添加这两个字段之前创建的，SQL脚本底部的 ALTER 语句（第214-215行）被注释掉了，未执行
- 模型 `BizPackageItem.php` 的 `$fillable` 数组中已包含 `unit_price` 和 `plan_price`
- 服务层 `BizSalesOrderService::generatePackage` 方法在创建套餐明细时使用了这两个字段

## 修复步骤

### 步骤1：执行 ALTER 语句添加缺失字段

在数据库中执行以下 SQL：

```sql
ALTER TABLE `biz_package_item` ADD COLUMN `unit_price` decimal(12,2) DEFAULT 0.00 COMMENT '单次价格' AFTER `product_name`;
ALTER TABLE `biz_package_item` ADD COLUMN `plan_price` decimal(12,2) DEFAULT 0.00 COMMENT '方案总价' AFTER `unit_price`;
```

这是唯一的修复操作，无需修改任何代码文件。
