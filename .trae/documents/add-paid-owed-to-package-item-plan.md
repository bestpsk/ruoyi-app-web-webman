# 持卡明细增加实付和欠款数据列

## 问题
操作--持卡明细中，品项级别的表格缺少"实付金额"和"欠款金额"列。
目前只有套餐头部显示了汇总的实付/欠款，但每个品项的表格中没有。

## 修改计划

### 1. 数据库：biz_package_item 增加字段
```sql
ALTER TABLE `biz_package_item` ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '实付金额' AFTER `deal_price`;
ALTER TABLE `biz_package_item` ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;
```

### 2. 后端 Model：BizPackageItem 增加 fillable
文件：`d:\fuchenpro\webman\app\model\BizPackageItem.php`
- fillable 增加 `paid_amount`, `owed_amount`

### 3. 后端 Service：BizSalesOrderService.generatePackage
文件：`d:\fuchenpro\webman\app\service\BizSalesOrderService.php`
- 生成 package_item 时写入 `paid_amount` 和 `owed_amount`
- 从对应的 order_item 取 paid_amount
- owed_amount = deal_price - paid_amount

### 4. Web 前端：sales/index.vue 持卡明细表格
文件：`d:\fuchenpro\front\src\views\business\sales\index.vue`
- 品项表格中增加"实付金额"和"欠款金额"两列
- 位置：在"成交金额"列后面

### 5. App 前端：order.vue 持卡明细
文件：`d:\fuchenpro\AppV3\src\pages\business\sales\order.vue`
- 品项详情中增加实付和欠款显示

### 6. SQL 迁移脚本
文件：`d:\fuchenpro\webman\sql\biz_unify_status.sql`
- 追加 biz_package_item 的 ALTER 语句
