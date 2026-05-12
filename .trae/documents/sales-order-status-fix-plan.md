# 开单功能问题分析与修复计划

## 问题分析

### 问题1：成交后的项目在操作-持卡明细中不显示

**根因分析：**

1. **后端逻辑**：`BizSalesOrderService.php`
   - 订单成交时调用 `generatePackage()` 生成套餐
   - 新建套餐默认状态为 `'0'`（待确认）

2. **AppV3端逻辑**：`AppV3/src/pages/business/sales/order.vue:114-124`
   ```javascript
   async function loadPackages() {
     const response = await getPackageByCustomer(customerId, '1')  // 只查询状态为 '1' 的套餐
   }
   ```

3. **问题所在**：
   - AppV3端查询状态为 '1' 的套餐
   - 新成交的套餐状态是 '0'，所以不显示

---

### 问题2：企业审核后套餐应变为可用状态

**当前逻辑问题：**

`enterpriseAudit()` 方法只更新订单审核状态，没有更新套餐状态：

```php
public function enterpriseAudit($orderId, $auditBy)
{
    return BizSalesOrder::where('order_id', $orderId)->update([
        'enterprise_audit_status' => '1',
        'enterprise_audit_by' => $auditBy,
        'enterprise_audit_time' => date('Y-m-d H:i:s')
    ]);
    // 缺少：更新套餐状态为 '1'
}
```

---

## 解决方案

### 修复1：AppV3持卡明细查询

**修改文件：** `AppV3/src/pages/business/sales/order.vue`

```javascript
// 修改前
async function loadPackages() {
  const response = await getPackageByCustomer(customerId, '1')
}

// 修改后
async function loadPackages() {
  const response = await getPackageByCustomer(customerId, null)  // 查询所有套餐
}
```

---

### 修复2：企业审核时更新套餐状态

**修改文件：** `webman/app/service/BizSalesOrderService.php`

```php
public function enterpriseAudit($orderId, $auditBy)
{
    $result = BizSalesOrder::where('order_id', $orderId)->update([
        'enterprise_audit_status' => '1',
        'enterprise_audit_by' => $auditBy,
        'enterprise_audit_time' => date('Y-m-d H:i:s')
    ]);

    // 同时更新套餐状态为可用
    BizCustomerPackage::where('order_id', $orderId)->update([
        'status' => '1'
    ]);

    return $result;
}
```

---

## 业务流程图

```
┌─────────────────────────────────────────────────────────────────┐
│                        开单审核流程                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. 开单员工创建订单                                             │
│     ├── 订单状态: order_status = '0' (待审核)                   │
│     ├── 企业审核: enterprise_audit_status = '0' (未审核)        │
│     └── 生成套餐: status = '0' (待确认)                         │
│                                                                 │
│  2. 企业管理员审核 (订单管理页面)                                 │
│     ├── 企业审核: enterprise_audit_status = '1' (已审核)        │
│     └── 套餐状态: status = '1' (使用中) ← 自动更新               │
│                                                                 │
│  3. 套餐可用                                                    │
│     └── 可在"操作"中进行持卡操作                                 │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 验证方法

1. **创建订单**：
   - 开单成交 → 生成套餐（状态 '0'）

2. **企业审核**：
   - 订单管理 → 打开"企业审核"开关
   - 套餐状态自动变为 '1'

3. **持卡操作**：
   - 选择客户 → 持卡明细显示套餐
   - 可以进行持卡操作
