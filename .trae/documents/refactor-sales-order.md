# 销售开单重构计划（v2 - 参考PC端实现）

## 需求概述

重构移动端销售开单页面，参考PC端 `front/src/views/business/sales/index.vue` 的"开单"Tab实现方式：
1. **客户列表页** (`sales/index.vue`)：增加显示客户性别、年龄、标签
2. **开单页** (`sales/order.vue`)：将"开单"Tab从"选择已有套餐品项"改为"自由创建套餐+品项"表单模式

---

## 一、客户列表页改造 (`AppV3/src/pages/business/sales/index.vue`)

### 1.1 客户卡片信息调整

**当前显示**：客户名字、电话、套餐数量

**改为显示**：
- 客户名字
- 性别（gender字段，映射：0=男, 1=女, 2=未知）
- 年龄（age字段，显示"XX岁"）
- 客户标签（tag字段，逗号分隔显示为标签徽章）

**数据库已有字段**（`biz_customer`表）：
- `gender` char(1) DEFAULT '2' COMMENT '性别(0男1女2未知)'
- `age` int(11) DEFAULT NULL COMMENT '年龄'
- `tag` varchar(100) DEFAULT NULL COMMENT '客户标签(字典biz_customer_tag)'

**具体改动**：
- 修改 `card-body` 区域，将"电话+套餐"替换为"性别+年龄"
- 将标签从 `card-header` 移到 `card-body` 中展示
- 性别用文字+颜色标识（男=蓝色, 女=粉色, 未知=灰色）
- 年龄显示为"XX岁"

---

## 二、开单页改造 (`AppV3/src/pages/business/sales/order.vue`)

### 2.1 参考PC端"开单"Tab实现

PC端开单核心代码（`front/src/views/business/sales/index.vue` 第85-139行）：

```
- 套餐名称输入框 (orderPackageName)
- 添加品项按钮 (addOrderItemRow)
- 品项表格 (orderItems)，每行包含：
  - 品项名称 (productName)
  - 次数 (quantity, 最小1)
  - 成交金额 (dealAmount)
  - 单次价 (自动计算 dealAmount/quantity)
  - 实付金额 (paidAmount)
  - 欠款金额 (自动计算 dealAmount-paidAmount)
  - 删除按钮
- 备注 (orderCustomerFeedback)
- 合计金额 (totalDealAmount)
- 提交订单按钮
```

PC端提交数据结构：
```javascript
{
  customerId, customerName, enterpriseId, enterpriseName,
  storeId, storeName, orderStatus: '1',
  packageName: orderPackageName.value,
  customerFeedback: '',
  remark: orderCustomerFeedback.value,
  items: orderItems.value  // [{ productName, quantity, dealAmount, paidAmount }]
}
```

### 2.2 移动端适配设计

将PC端的表格形式改为移动端卡片列表形式：

```
┌─────────────────────────────┐
│ 套餐名称: [输入框]           │
├─────────────────────────────┤
│ 品项 1                      │
│ 名称: [输入框]    [删除按钮] │
│ 次数: [数字]  成交金额: [金额]│
│ 单次价: ¥XX.XX (自动计算)    │
│ 实付金额: [金额]             │
│ 欠款金额: ¥XX.XX (自动计算)  │
├─────────────────────────────┤
│ 品项 2                      │
│ ...                         │
├─────────────────────────────┤
│ [+ 添加品项]                 │
├─────────────────────────────┤
│ 合计: ¥XXX.XX               │
├─────────────────────────────┤
│ 备注: [textarea]            │
├─────────────────────────────┤
│ [提交订单]                   │
└─────────────────────────────┘
```

### 2.3 数据结构

```javascript
const orderPackageName = ref('')
const orderItems = ref([
  { productName: '', quantity: 1, dealAmount: 0, paidAmount: 0 }
])
const orderRemark = ref('')

// 自动计算
const totalDealAmount = computed(() => orderItems.value.reduce((sum, item) => sum + (item.dealAmount || 0), 0))
const totalPaidAmount = computed(() => orderItems.value.reduce((sum, item) => sum + (item.paidAmount || 0), 0))
const totalOwedAmount = computed(() => totalDealAmount.value - totalPaidAmount.value)
```

### 2.4 自动计算逻辑

- **单次价** = 成交金额 ÷ 次数（次数为0时显示0.00）
- **欠款金额** = 成交金额 - 实付金额（每个品项独立计算）
- **合计成交金额** = 所有品项成交金额之和
- **合计实付金额** = 所有品项实付金额之和
- **合计欠款金额** = 合计成交金额 - 合计实付金额

### 2.5 提交订单逻辑

与PC端一致，提交数据：
```javascript
{
  customerId, customerName, enterpriseId, enterpriseName,
  storeId, storeName, orderStatus: '1',
  packageName: orderPackageName.value,
  remark: orderRemark.value,
  items: orderItems.value
}
```

### 2.6 移除的内容

- 移除"客户套餐"展示区域（packageList、filteredPackageList、showExhaustedItems）
- 移除"付款信息"区域（actualPaidAmount、selectedPaymentMethod、paymentMethods）
- 移除"支付方式"选择
- 移除 `loadPackages()`、`getPackageByCustomer` 导入
- 移除品项的 `orderCount` 数字选择器

### 2.7 保留的内容

- 客户信息头部展示
- Tab切换（开单、开单记录、操作记录、还欠款）
- 开单记录Tab
- 操作记录Tab
- 还欠款Tab
- 还款弹窗

---

## 三、后端改造

### 3.1 数据库迁移

当前 `biz_order_item` 表结构（来自 sql-backup.sql）：
```sql
item_id, order_id, product_name, quantity, deal_amount, paid_amount,
is_our_operation, customer_feedback, before_photo, after_photo, remark, create_time
```

需要添加两个字段：
```sql
ALTER TABLE biz_order_item ADD COLUMN unit_price DECIMAL(10,2) DEFAULT 0.00 COMMENT '单次价' AFTER paid_amount;
ALTER TABLE biz_order_item ADD COLUMN owed_amount DECIMAL(10,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER unit_price;
```

### 3.2 后端模型更新

`BizOrderItem.php` 的 `$fillable` 添加 `unit_price`、`owed_amount`

### 3.3 后端服务层更新

`BizSalesOrderService::insertOrder` 方法调整：
- `convertedItem` 中增加 `unit_price` 和 `owed_amount` 字段提取
- `unit_price` = `deal_amount / quantity`（与PC端逻辑一致）
- `owed_amount` = `deal_amount - paid_amount`
- `generatePackage` 方法中使用品项的 `unit_price` 和 `owed_amount`

---

## 四、实施步骤

### 步骤1：数据库迁移
- 创建 `webman/sql/add_order_item_fields.sql`
- 为 `biz_order_item` 添加 `unit_price` 和 `owed_amount` 字段
- 执行迁移

### 步骤2：后端模型更新
- 更新 `BizOrderItem` 模型 `$fillable`，添加 `unit_price`、`owed_amount`

### 步骤3：后端服务层更新
- 修改 `BizSalesOrderService::insertOrder`，支持品项的 `unit_price`、`owed_amount`
- 修改 `generatePackage` 方法，使用品项的 `unit_price` 和 `owed_amount`

### 步骤4：客户列表页前端改造
- 修改 `sales/index.vue` 客户卡片，显示性别、年龄、标签
- 移除电话和套餐数量显示

### 步骤5：开单页前端改造
- 重构 `sales/order.vue` 的"开单"Tab
- 实现套餐名称输入
- 实现品项动态添加/删除（参考PC端 addOrderItemRow）
- 实现自动计算逻辑（单次价、欠款金额）
- 实现新的提交订单逻辑（参考PC端 submitOrder）
- 移除旧的套餐选择和付款信息区域

### 步骤6：验证
- 检查代码正确性
