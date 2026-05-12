# 销售开单功能优化计划 - 去掉方案价格和成交开关

## 📋 需求概述
优化web端销售开单功能，简化开单流程：
- ✅ 去掉"方案价格"字段（前端+后端+数据库）
- ✅ 去掉"成交"开关（默认所有订单都是已成交状态）
- ✅ 清理数据库中相关的冗余字段
- ✅ 简化UI界面，提升用户体验

---

## 🔍 当前实现分析

### 涉及的文件
1. **前端页面**: [sales/index.vue](file:///d:/fuchenpro/front/src/views/business/sales/index.vue)
2. **后端服务**: [BizSalesOrderService.php](file:///d:/fuchenpro/webman/app/service/BizSalesOrderService.php)
3. **数据库模型**: [BizSalesOrder.php](file:///d:/fuchenpro/webman/app/model/BizSalesOrder.php)
4. **SQL脚本**: [biz_sales.sql](file:///d:/fuchenpro/webman/sql/biz_sales.sql) 和 [sql-backup.sql](file:///d:/fuchenpro/webman/sql-backup.sql)

### 当前数据结构
#### biz_order_item 表（订单明细）
```sql
plan_price      -- 方案价格 ❌ 需删除
is_deal         -- 是否成交(0否1是) ❌ 需删除
deal_amount     -- 成交金额 ✅ 保留
paid_amount     -- 实付金额 ✅ 保留
```

#### biz_sales_order 表（销售订单）
```sql
total_amount    -- 方案总金额 ❌ 需删除（与deal_amount重复）
deal_amount     -- 成交总金额 ✅ 保留（作为唯一金额字段）
```

#### biz_package_item 表（套餐明细）
```sql
plan_price      -- 方案总价 ❌ 可选删除（看业务需要）
deal_price      -- 成交金额 ✅ 保留
```

---

## 📝 详细实施步骤

### 步骤 1: 前端页面优化 (sales/index.vue)

#### 1.1 删除"成交状态"筛选器
**位置**: 第23-26行
```vue
<!-- 删除这段代码 -->
<el-select v-model="filterHasDeal" placeholder="成交状态" clearable style="width: 100px" @change="loadCustomerList">
  <el-option label="已成交" value="1" />
  <el-option label="未成交" value="0" />
</el-select>
```
**同时删除相关变量**: `filterHasDeal` (第733行)

#### 1.2 删除客户列表中的"已成交/未成交"标签
**位置**: 第59行
```vue
<!-- 删除这个标签 -->
<el-tag :type="item.hasDeal ? 'success' : 'info'" size="small">{{ item.hasDeal ? '已成交' : '未成交' }}</el-tag>
```

#### 1.3 删除表格中的"方案价格"列
**位置**: 第109-113行
```vue
<!-- 删除整个列 -->
<el-table-column label="方案价格" width="140">
  <template #default="scope">
    <el-input-number v-model="scope.row.planPrice" :min="0" :precision="2" controls-position="right" style="width: 100%" />
  </template>
</el-table-column>
```

#### 1.4 删除表格中的"成交"开关列
**位置**: 第114-118行
```vue
<!-- 删除整个列 -->
<el-table-column label="成交" width="65" align="center">
  <template #default="scope">
    <el-switch v-model="scope.row.isDeal" :active-value="1" :inactive-value="0" @change="val => handleDealChange(scope.$index, val)" />
  </template>
</el-table-column>
```

#### 1.5 修改"成交金额"列（移除禁用逻辑）
**位置**: 第119-123行
**修改前**:
```vue
<el-input-number v-model="scope.row.dealAmount" :min="0" :precision="2" :disabled="!scope.row.isDeal" controls-position="right" style="width: 100%" />
```
**修改后**:
```vue
<el-input-number v-model="scope.row.dealAmount" :min="0" :precision="2" controls-position="right" style="width: 100%" />
```

#### 1.6 修改"实付金额"列（移除禁用逻辑）
**位置**: 第129-133行
**修改前**:
```vue
<el-input-number v-model="scope.row.paidAmount" :min="0" :precision="2" :disabled="!scope.row.isDeal" controls-position="right" style="width: 100%" />
```
**修改后**:
```vue
<el-input-number v-model="scope.row.paidAmount" :min="0" :precision="2" controls-position="right" style="width: 100%" />
```

#### 1.7 修改"单次价"列（移除isDeal判断）
**位置**: 第124-128行
**修改前**:
```vue
<span>{{ scope.row.quantity > 0 && scope.row.isDeal ? (scope.row.dealAmount / scope.row.quantity).toFixed(2) : '0.00' }}</span>
```
**修改后**:
```vue
<span>{{ scope.row.quantity > 0 ? (scope.row.dealAmount / scope.row.quantity).toFixed(2) : '0.00' }}</span>
```

#### 1.8 修改"欠款金额"列（移除isDeal判断）
**位置**: 第134-138行
**修改前**:
```vue
<span>{{ scope.row.isDeal ? (scope.row.dealAmount - scope.row.paidAmount).toFixed(2) : '0.00' }}</span>
```
**修改后**:
```vue
<span>{{ (scope.row.dealAmount - scope.row.paidAmount).toFixed(2) }}</span>
```

#### 1.9 修改底部统计区域
**位置**: 第149-155行
**修改前**:
```vue
<span style="margin-right: 12px">方案合计: <b>{{ totalPlanAmount }}</b> 元</span>
<span style="margin-right: 12px">成交合计: <b style="color: #409eff">{{ totalDealAmount }}</b> 元</span>
```
**修改后**:
```vue
<span style="margin-right: 12px">合计: <b style="color: #409eff">{{ totalDealAmount }}</b> 元</span>
```

#### 1.10 删除totalPlanAmount计算属性
**位置**: 第817行
```javascript
// 删除这行
const totalPlanAmount = computed(() => orderItems.value.reduce((sum, item) => sum + (item.planPrice || 0), 0))
```

#### 1.11 修改addOrderItemRow函数
**位置**: 第886-890行
**修改前**:
```javascript
function addOrderItemRow() {
  orderItems.value.push({
    productName: '', quantity: 1, planPrice: 0, isDeal: 0, dealAmount: 0, paidAmount: 0
  })
}
```
**修改后**:
```javascript
function addOrderItemRow() {
  orderItems.value.push({
    productName: '', quantity: 1, dealAmount: 0, paidAmount: 0
  })
}
```

#### 1.12 删除handleDealChange函数
**位置**: 第892-900行
```javascript
// 整个函数删除
function handleDealChange(index, val) { ... }
```

#### 1.13 修改submitOrder函数
**位置**: 第902-932行
**关键改动**:
- 移除`hasDeal`判断
- 固定`orderStatus: '1'`（已成交）

**修改后的核心代码**:
```javascript
function submitOrder() {
  if (orderItems.value.length === 0) return proxy.$modal.msgWarning('请添加品项')
  const hasEmpty = orderItems.value.some(i => !i.productName)
  if (hasEmpty) return proxy.$modal.msgWarning('请填写品项名称')

  const ent = enterpriseOptions.value.find(e => e.enterpriseId === currentEnterpriseId.value)
  const store = storeOptions.value.find(s => s.storeId === currentStoreId.value)
  const data = {
    customerId: currentCustomer.value.customerId,
    customerName: currentCustomer.value.customerName,
    enterpriseId: currentEnterpriseId.value,
    enterpriseName: ent?.enterpriseName,
    storeId: currentStoreId.value,
    storeName: store?.storeName,
    orderStatus: '1', // 固定为已成交
    packageName: orderPackageName.value,
    customerFeedback: '',
    remark: orderCustomerFeedback.value,
    items: orderItems.value
  }
  addSalesOrder(data).then(() => {
    proxy.$modal.msgSuccess('开单成功')
    orderItems.value = []
    orderCustomerFeedback.value = ''
    orderPackageName.value = ''
    loadPackageList()
    loadOrderRecords()
    loadCustomerList()
  })
}
```

#### 1.14 删除loadCustomerList中的filterHasDeal参数
**位置**: 第860行
**修改前**:
```javascript
searchCustomer(customerKeyword.value, currentEnterpriseId.value, currentStoreId.value, filterHasDeal.value, filterSatisfaction.value)
```
**修改后**:
```javascript
searchCustomer(customerKeyword.value, currentEnterpriseId.value, currentStoreId.value, '', filterSatisfaction.value)
```

#### 1.15 开单记录Tab页筛选器调整（可选）
**位置**: 第456-459行
可以考虑删除"是否成交"筛选器，或者保留但默认不显示（因为现在都是已成交）

---

### 步骤 2: 后端服务层优化 (BizSalesOrderService.php)

#### 2.1 修改insertOrder方法
**位置**: 第45-145行
**主要改动**:

1. **简化item转换逻辑**（第64-83行）:
```php
$convertedItems = [];
foreach ($items as $item) {
    $convertedItem = [
        'product_name' => $item['item_name'] ?? $item['product_name'] ?? $item['productName'] ?? '',
        'quantity' => intval($item['count'] ?? $item['quantity'] ?? 1),
        // 删除 plan_price 和 is_deal 字段
        'deal_amount' => floatval($item['price'] ?? $item['deal_amount'] ?? $item['dealPrice'] ?? 0),
        'paid_amount' => floatval($item['paid_amount'] ?? $item['paidAmount'] ?? 0),
        'remark' => $item['remark'] ?? null,
        'create_time' => date('Y-m-d H:i:s')
    ];
    $convertedItems[] = $convertedItem;

    // 所有项目都视为成交
    $dealAmount += $convertedItem['deal_amount'];
    $paidAmount += $convertedItem['paid_amount'];
}
```

2. **简化金额计算逻辑**（第86-108行）:
```php
// 直接使用dealAmount作为总金额
$data['deal_amount'] = $dealAmount;
$data['paid_amount'] = $paidAmount;
$data['owed_amount'] = $dealAmount - $paidAmount;
// 删除 total_amount 的设置
```

3. **固定订单状态为已成交**（第110-112行）:
```php
if (!isset($data['order_status'])) {
    $data['order_status'] = '1'; // 固定已成交
}
```

4. **修改generatePackage调用**（第134行）:
```php
$this->generatePackage($order, $convertedItems);
```

5. **简化generatePackage方法**（第205-257行）:
   - 删除planPrice相关逻辑
   - 所有items都生成套餐

#### 2.2 修改updateOrder方法
**位置**: 第147-179行
**类似简化**:
- 删除plan_price和is_deal处理
- 简化金额计算

---

### 步骤 3: 数据库优化

#### 3.1 生成数据库迁移SQL

**文件**: 新建 `webman/sql/optimize_sales_order.sql`

```sql
-- =============================================
-- 销售开单优化：删除冗余字段
-- 执行时间: 2026-05-10
-- =============================================

-- 1. 订单明细表：删除方案价格和是否成交字段
ALTER TABLE `biz_order_item` DROP COLUMN `plan_price`;
ALTER TABLE `biz_order_item` DROP COLUMN `is_deal`;

-- 2. 销售订单表：删除方案总金额字段（使用deal_amount代替）
ALTER TABLE `biz_sales_order` DROP COLUMN `total_amount`;

-- 3. 套餐明细表：删除方案总价字段（可选，如果业务不需要）
-- ALTER TABLE `biz_package_item` DROP COLUMN `plan_price`;

-- 4. 更新订单状态字典（删除"待确认"状态，如果不需要）
-- DELETE FROM sys_dict_data WHERE dict_type = 'biz_order_status' AND dict_value = '0';

-- 5. 更新备注说明
-- ALTER TABLE `biz_sales_order` MODIFY COLUMN `deal_amount` decimal(12,2) DEFAULT 0.00 COMMENT '订单总金额';
```

#### 3.2 更新biz_sales.sql建表脚本
同步更新原始建表脚本，保持一致性

---

### 步骤 4: 模型层调整 (可选)

**文件**: BizSalesOrder.php

如果需要在模型层面做调整：
- 从`fillable`数组中移除`total_amount`
- 确保没有其他地方引用这些字段

---

## 🎯 优化效果对比

### 优化前
| 品项 | 次数 | 方案价格 | 成交✅ | 成交金额 | 单次价 | 实付金额 | 欠款金额 |
|------|------|---------|-------|---------|--------|---------|---------|
| 品项1 | 10  | 3980    | ✓     | 3980    | 398.00 | 3980    | 0       |

### 优化后
| 品项 | 次数 | 成交金额 | 单次价 | 实付金额 | 欠款金额 |
|------|------|---------|--------|---------|---------|
| 品项1 | 10  | 3980    | 398.00 | 3980    | 0       |

**改进点**:
- ✅ 减少2列，界面更简洁
- ✅ 减少用户操作步骤（无需切换成交开关）
- ✅ 减少数据录入错误（不会忘记点成交）
- ✅ 数据库字段更精简，减少存储空间
- ✅ 业务逻辑更清晰（开单=成交）

---

## ⚠️ 注意事项

### 数据兼容性
1. **历史数据处理**: 已有数据的`plan_price`和`is_deal`字段在删除前需确认不影响历史查询
2. **报表统计**: 如果有报表依赖`total_amount`字段，需要改用`deal_amount`
3. **API接口**: 检查是否有其他系统/移动端依赖这些字段

### 测试要点
1. ✅ 新增订单：验证默认已成交、无方案价格字段
2. ✅ 金额计算：验证成交金额、实付金额、欠款金额正确
3. ✅ 套餐生成：验证持卡记录正常创建
4. ✅ 历史查询：验证历史订单显示正常
5. ✅ 操作扣减：验证套餐操作正常扣减

---

## 📊 执行顺序建议

1. **Phase 1**: 前端修改（立即可见效果）
2. **Phase 2**: 后端服务层修改（配合前端）
3. **Phase 3**: 数据库字段清理（最后执行，需备份数据）
4. **Phase 4**: 全面测试验证

---

## ✅ 完成标准

- [ ] 前端页面无"方案价格"列和"成交"开关
- [ ] 开单时自动标记为已成交状态
- [ ] 底部只显示一个"合计"金额
- [ ] 后端不再处理plan_price和is_deal字段
- [ ] 数据库已删除冗余字段
- [ ] 所有测试用例通过
- [ ] 历史数据显示正常
