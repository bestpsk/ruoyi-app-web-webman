# 开单功能三个问题修复计划

## 问题分析

### 问题1：方案价格/成交价格是0的项目开单后不显示
**原因**：后端 `generatePackage` 方法（第129-131行）只处理 `is_deal=1` 的项目，未勾选"成交"的项目不会生成套餐品项。

**实际情况**：价格是0的项目（赠送项目）如果勾选了"成交"应该也能显示。后端代码没有过滤价格，问题可能是用户没有勾选"成交"复选框。

**解决方案**：无需修改，确保勾选"成交"即可。如果确实需要支持不勾选成交也能显示，需要修改后端逻辑。

### 问题2：套餐名称不显示（显示默认的"李玲 2026-05-01 持卡记录"）
**原因**：`biz_sales_order` 表没有 `package_name` 字段！前端传入的 `packageName` 无法保存。

**解决方案**：
1. 给 `biz_sales_order` 表添加 `package_name` 字段
2. 后端 `insertOrder` 方法会自动保存该字段（通过 `convert_to_snake_case` 转换）

### 问题3：开单后持卡明细不实时更新
**原因**：`loadPackageList()` 已调用，但可能需要确保数据刷新。

**解决方案**：检查 `loadPackageList` 是否正确刷新 `packageList`，确保 `allPackageItems` 计算属性能响应式更新。

---

## 实施步骤

### 步骤1：添加数据库字段
在 `biz_sales_order` 表添加 `package_name` 字段：

```sql
ALTER TABLE biz_sales_order 
ADD COLUMN package_name VARCHAR(200) DEFAULT '' COMMENT '套餐名称' 
AFTER order_status;
```

### 步骤2：验证后端保存逻辑
确认 `BizSalesOrderService::insertOrder` 方法通过 `convert_to_snake_case` 会自动将前端 `packageName` 转换为 `package_name` 并保存。

### 步骤3：确保前端刷新逻辑正确
检查 `loadPackageList()` 是否正确执行，以及 `allPackageItems` 计算属性是否响应式更新。

---

## 涉及文件
- `webman/sql/biz_sales.sql` - 添加字段（或直接执行SQL）
- `front/src/views/business/sales/index.vue` - 验证刷新逻辑
