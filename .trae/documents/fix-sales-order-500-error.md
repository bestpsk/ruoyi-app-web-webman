# 销售开单500错误修复计划

## 问题描述
在销售开单流程中，提交订单时返回 `开单失败: 500` 错误。

## 根本原因分析
经过代码审查，发现**数据库表结构与ORM模型定义不一致**，导致数据插入时出现字段缺失错误：

### 🔴 关键问题：数据库表缺少必要字段

#### 1. `biz_customer_package` 表缺少2个字段
- ❌ 缺少 `paid_amount` (已付金额)
- ❌ 缺少 `owed_amount` (欠款金额)
- 📍 影响位置：[BizSalesOrderService.php:184-185](webman/app/service/BizSalesOrderService.php#L184-L185)

#### 2. `biz_order_item` 表缺少5个字段
- ❌ 缺少 `unit_price` (单次价)
- ❌ 缺少 `owed_amount` (欠款金额)
- ❌ 缺少 `plan_price` (方案总价)
- ❌ 缺少 `is_deal` (是否成交)
- ❌ 缺少 `customer_feedback` (客户反馈)
- 📍 影响位置：[BizSalesOrderService.php:63-70](webman/app/service/BizSalesOrderService.php#L63-L70)

#### 3. `biz_package_item` 表缺少2个字段
- ❌ 缺少 `paid_amount` (已付金额)
- ❌ 缺少 `owed_amount` (欠款金额)
- 📍 影响位置：[BizSalesOrderService.php:203-204](webman/app/service/BizSalesOrderService.php#L203-L204)

## 修复方案

### 步骤1: 备份现有数据库
```sql
-- 在执行ALTER之前，建议先备份相关表
CREATE TABLE biz_customer_package_backup AS SELECT * FROM biz_customer_package;
CREATE TABLE biz_order_item_backup AS SELECT * FROM biz_order_item;
CREATE TABLE biz_package_item_backup AS SELECT * FROM biz_package_item;
```

### 步骤2: 为 `biz_customer_package` 表添加缺失字段
```sql
-- 添加已付金额字段
ALTER TABLE `biz_customer_package`
ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '已付金额' AFTER `total_amount`;

-- 添加欠款金额字段
ALTER TABLE `biz_customer_package`
ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;
```

### 步骤3: 为 `biz_order_item` 表添加缺失字段
```sql
-- 添加方案总价字段
ALTER TABLE `biz_order_item`
ADD COLUMN `plan_price` decimal(12,2) DEFAULT 0.00 COMMENT '方案总价' AFTER `quantity`;

-- 添加是否成交字段
ALTER TABLE `biz_order_item`
ADD COLUMN `is_deal` tinyint(1) DEFAULT 1 COMMENT '是否成交(0否1是)' AFTER `plan_price`;

-- 添加单次价字段
ALTER TABLE `biz_order_item`
ADD COLUMN `unit_price` decimal(12,2) DEFAULT 0.00 COMMENT '单次价格' AFTER `deal_amount`;

-- 添加欠款金额字段
ALTER TABLE `biz_order_item`
ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `unit_price`;

-- 添加客户反馈字段
ALTER TABLE `biz_order_item`
ADD COLUMN `customer_feedback` varchar(500) DEFAULT NULL COMMENT '客户反馈' AFTER `owed_amount`;
```

### 步骤4: 为 `biz_package_item` 表添加缺失字段
```sql
-- 添加已付金额字段
ALTER TABLE `biz_package_item`
ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '已付金额' AFTER `deal_price`;

-- 添加欠款金额字段
ALTER TABLE `biz_package_item`
ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;
```

### 步骤5: 验证修复结果
- ✅ 测试销售开单功能，确认可以正常提交订单
- ✅ 验证订单明细、套餐信息正确保存
- ✅ 检查金额计算是否准确（成交金额、实付金额、欠款金额）
- ✅ 测试开单记录查询功能
- ✅ 测试还欠款功能

## 影响范围
- ✅ 仅涉及数据库DDL操作，不影响现有业务逻辑代码
- ✅ 所有新增字段都有默认值，不会影响现有数据
- ✅ 字段类型与ORM模型定义完全匹配

## 注意事项
⚠️ 执行ALTER语句前务必备份数据库
⚠️ 建议在测试环境先验证SQL语句的正确性
⚠️ 如果数据量较大，建议在业务低峰期执行
