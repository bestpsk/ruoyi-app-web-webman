# AppV3 操作详情数据渲染问题修复计划

## 问题描述
新建订单后，AppV3 操作详情页面数据显示异常：
1. **基础信息全部为空**：操作人-、客户-、门店-、金额¥0.00、时间空
2. **订单编号异常**：`OPRundefined`（说明 `record_id` 为 undefined）
3. **项目数据异常**：品项名显示"-"，所有金额都是¥0.00
4. **但项目数量正确**：显示2项（说明 items 数组有数据）

## 根因分析

### 数据流追踪
```
后端返回: { code:200, record:{recordId:10, packageName:"品项1", ...}, items:[...] }
                    ↓ AjaxResult::convertToCamelCase() 自动转换
前端接收: response = { code:200, record:{recordId:10, packageName:"品项1", ...} }
                    ↓
前端访问: record.record_id  → undefined! (应该是 record.recordId)
```

### 问题代码位置
**文件**: [detail.vue:220-248](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue#L220-L248)

当前使用 snake_case 字段名，但后端已转换为 camelCase：

| 前端当前代码 | 后端实际返回 | 结果 |
|-------------|-------------|------|
| `record.record_id` | `record.recordId` | ❌ undefined |
| `record.package_no` | `record.packageNo` | ❌ undefined → OPRundefined |
| `record.customer_name` | `record.customerName` | ❌ undefined → - |
| `record.operator_user_name` | `record.operatorUserName` | ❌ undefined → - |
| `item.product_name` | `item.productName` | ❌ undefined → - |
| `item.operation_quantity` | `item.operationQuantity` | ❌ undefined → 0 |
| `item.consume_amount` | `item.consumeAmount` | ❌ undefined → 0 |

## 修复方案

### 修改文件
[d:\fuchenpro\AppV3\src\pages\business\order\detail.vue](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue)

### 具体改动

#### 1. 修改 loadDetail() 函数中的 orderInfo 映射（第223-238行）

```javascript
// ✅ 修复后（camelCase）
orderInfo.value = {
  recordId: record.recordId || record.record_id,
  orderNo: record.packageNo || ('OPR' + (record.recordId || record.record_id)),
  operationType: record.operationType || record.operation_type,
  customerName: record.customerName || record.customer_name,
  enterpriseName: data.enterpriseName || data.enterprise_name || record.enterpriseName,
  storeName: data.storeName || data.store_name || record.storeName,
  dealAmount: data.totalAmount || data.total_amount || record.consumeAmount || record.consumeAmount || 0,
  totalAmount: data.totalAmount || data.total_amount || record.consumeAmount || record.consumeAmount || 0,
  createTime: record.operationDate || record.operation_date || record.createTime || record.create_time,
  remark: record.remark,
  operatorName: record.operatorUserName || record.operator_user_name,
  satisfaction: record.satisfaction,
  feedback: record.customerFeedback || record.customer_feedback,
  photos: [record.beforePhoto || record.before_photo, record.afterPhoto || record.after_photo].filter(Boolean)
}
```

#### 2. 修改 orderItems 映射（第240-248行）

```javascript
// ✅ 修复后（camelCase）
orderItems.value = items.map(item => ({
  product_name: item.productName || item.product_name,
  productName: item.productName || item.product_name,
  quantity: item.operationQuantity || item.operation_quantity,
  count: item.operationQuantity || item.operation_quantity,
  planPrice: Number(item.consumeAmount || item.consume_amount || item.trialPrice || item.trial_price || 0),
  dealAmount: Number(item.consumeAmount || item.consume_amount || item.trialPrice || item.trial_price || 0),
  paidAmount: Number(item.consumeAmount || item.consume_amount || item.trialPrice || item.trial_price || 0)
}))
```

## 实施步骤

### 步骤1：修改 detail.vue 的 loadDetail() 函数
将所有 snake_case 字段名改为 camelCase，同时保留 snake_case 作为兼容性回退

### 步骤2：测试验证
- 在 AppV3 中点击新建的操作订单
- 确认基础信息正确显示（操作人、客户、门店、金额、时间）
- 确认订单项目正确显示（品项名、次数、金额）

## 预期效果

| 字段 | 修复前 | 修复后 |
|------|--------|--------|
| 订单编号 | OPRundefined | OPR9 或 PK202605090001 |
| 操作人 | - | admin |
| 客户 | - | 客户1 |
| 门店 | - | 逆龄奢·宜川店（或从套餐获取） |
| 金额 | ¥0.00 | ¥796.00（2个品项 × 398） |
| 时间 | 空 | 2026-05-10 20:01 |
| 品项名 | - | 品项1 / 品项2 |
| 方案价 | ¥0.00 | ¥398.00 |

## 风险评估
- **风险等级**：低 🟢（仅修改前端字段名映射）
- **影响范围**：仅 AppV3 操作详情页面
- **兼容性**：保留 snake_case 回退，确保向后兼容
