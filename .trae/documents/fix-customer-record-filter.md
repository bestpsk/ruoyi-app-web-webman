# 修复记录tab页面顾客过滤问题

## 问题分析

### 当前问题
记录tab页面中的"订单记录"表格显示的是所有订单记录，而不是当前选中顾客的订单记录。

### 根本原因
在 `BizSalesOrderService.php` 的 `selectOrderList` 方法中，缺少对 `customer_id` 参数的过滤处理。

**对比分析：**
- `BizOperationRecordService.php` 第14行已正确处理 `customer_id` 过滤：
  ```php
  if (!empty($params['customer_id'])) $query->where('customer_id', $params['customer_id']);
  ```

- `BizSalesOrderService.php` 的 `selectOrderList` 方法缺少此过滤条件

### 前端调用情况
前端 `sales/index.vue` 第717-719行已正确传递 `customerId` 参数：
```javascript
function loadOrderRecords() {
  if (!currentCustomerId.value) return
  listSalesOrder({ customerId: currentCustomer.value.customerId, pageSize: 20 }).then(res => { orderRecordList.value = res.rows || [] })
}
```

## 修复方案

### 修改文件
`d:\fuchenpro\webman\app\service\BizSalesOrderService.php`

### 修改内容
在 `selectOrderList` 方法中添加 `customer_id` 过滤条件：

```php
public function selectOrderList($params = [])
{
    $query = BizSalesOrder::query();
    if (!empty($params['customer_id'])) $query->where('customer_id', $params['customer_id']);  // 新增此行
    if (!empty($params['order_no'])) $query->where('order_no', 'like', '%' . $params['order_no'] . '%');
    if (!empty($params['customer_name'])) $query->where('customer_name', 'like', '%' . $params['customer_name'] . '%');
    // ... 其他过滤条件保持不变
}
```

## 实施步骤

1. 在 `BizSalesOrderService.php` 的 `selectOrderList` 方法中，在现有过滤条件之前添加 `customer_id` 过滤条件
2. 验证修改后的效果

## 预期结果
修复后，记录tab页面的"订单记录"表格将只显示当前选中顾客的订单记录，而不是所有订单记录。
