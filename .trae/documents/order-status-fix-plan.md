# Web端订单状态显示问题修复计划

## 问题分析

### 当前状态字段说明

| 字段 | 含义 | 值 |
|-----|------|---|
| `order_status` | 订单状态 | '0'=待确认, '1'=已成交, '2'=已取消 |
| `enterprise_audit_status` | 企业审核状态 | '0'=未审核, '1'=已审核 |
| `finance_audit_status` | 财务审核状态 | '0'=未审核, '1'=已审核 |

### 问题所在

**当前逻辑：**
- 企业审核只更新 `enterprise_audit_status` 为 '1'
- `order_status` 保持 '0'（待确认）
- 前端显示的是 `order_status`，所以显示"待确认"

**用户期望：**
- 企业审核后，订单状态应变为"已成交"

---

## 解决方案

### 修改企业审核方法

**文件：** `webman/app/service/BizSalesOrderService.php`

```php
public function enterpriseAudit($orderId, $auditBy)
{
    $result = BizSalesOrder::where('order_id', $orderId)->update([
        'enterprise_audit_status' => '1',
        'enterprise_audit_by' => $auditBy,
        'enterprise_audit_time' => date('Y-m-d H:i:s'),
        'order_status' => '1'  // 同时更新订单状态为已成交
    ]);

    // 同时更新套餐状态为可用
    BizCustomerPackage::where('order_id', $orderId)->update([
        'status' => '1'
    ]);

    return $result;
}
```

---

## 业务流程

```
开单 → order_status='0'(待确认), enterprise_audit_status='0'(未审核)
  ↓
企业审核 → order_status='1'(已成交), enterprise_audit_status='1'(已审核)
  ↓
套餐可用
```

---

## 验证方法

1. 创建订单 → 订单状态显示"待确认"
2. 企业审核 → 订单状态变为"已成交"
3. 开单记录中显示"已成交"
