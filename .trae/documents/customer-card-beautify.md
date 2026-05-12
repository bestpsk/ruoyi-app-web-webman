# 客户卡片美化与统计信息展示计划

## 需求分析

### 当前状态
客户列表中的每个客户卡片（`.customer-item`）仅显示：
- 客户姓名
- 电话号码
- 标签（VIP/普通等）

样式简陋，无边界分隔，缺少业务统计信息。

### 需要新增的信息
1. **是否成交** - 客户是否有已成交订单
2. **已消费金额** - 客户累计消费金额（操作记录中的消耗金额总和）
3. **满意度平均分** - 客户所有操作记录的满意度平均值

### 数据来源分析
- `biz_sales_order` 表：`order_status='1'` 表示已成交，`deal_amount` 为成交金额
- `biz_operation_record` 表：`consume_amount` 为消耗金额，`satisfaction` 为满意度评分
- 当前 `searchCustomer` API 只返回 `biz_customer` 表的基本字段，**不包含统计信息**

---

## 实施步骤

### 步骤1：后端 - 修改 searchCustomer API 附加统计信息

**文件**: `d:/fuchenpro/webman/app/service/BizCustomerService.php`

在 `searchCustomer` 方法中，对返回的每个客户追加3个统计字段：

```php
public function searchCustomer($keyword, $enterpriseId, $storeId = null)
{
    $query = BizCustomer::query();
    $query->where('enterprise_id', $enterpriseId);
    if ($storeId) $query->where('store_id', $storeId);
    if ($keyword) {
        $query->where(function ($q) use ($keyword) {
            $q->where('customer_name', 'like', '%' . $keyword . '%')
              ->orWhere('phone', 'like', '%' . $keyword . '%');
        });
    }
    $customers = $query->where('status', '0')->orderBy('customer_name', 'asc')->limit(50)->get();

    // 附加统计信息
    foreach ($customers as $customer) {
        $customerId = $customer->customer_id;

        // 是否有已成交订单
        $hasDeal = BizSalesOrder::where('customer_id', $customerId)
            ->where('order_status', '1')->exists();
        $customer->has_deal = $hasDeal;

        // 已消费金额（操作记录消耗金额总和）
        $totalConsumed = BizOperationRecord::where('customer_id', $customerId)
            ->sum('consume_amount');
        $customer->total_consumed = round(floatval($totalConsumed), 2);

        // 满意度平均分
        $avgSatisfaction = BizOperationRecord::where('customer_id', $customerId)
            ->whereNotNull('satisfaction')
            ->avg('satisfaction');
        $customer->avg_satisfaction = $avgSatisfaction ? round(floatval($avgSatisfaction), 1) : null;
    }

    return $customers;
}
```

**需要引入的模型**:
```php
use app\model\BizSalesOrder;
use app\model\BizOperationRecord;
```

### 步骤2：前端 - 美化客户卡片模板

**文件**: `d:/fuchenpro/front/src/views/business/sales/index.vue`

将当前简单的客户卡片（第32-37行）：
```vue
<div v-for="item in customerList" :key="item.customerId"
  :class="['customer-item', currentCustomerId === item.customerId ? 'active' : '']"
  @click="handleSelectCustomer(item)">
  <div class="customer-name">{{ item.customerName }}</div>
  <div class="customer-info">
    <span>{{ item.phone }}</span>
    <dict-tag v-if="item.tag" :options="biz_customer_tag" :value="item.tag" size="small" />
  </div>
</div>
```

改为增强版卡片：
```vue
<div v-for="item in customerList" :key="item.customerId"
  :class="['customer-item', currentCustomerId === item.customerId ? 'active' : '']"
  @click="handleSelectCustomer(item)">
  <div class="customer-header">
    <span class="customer-name">{{ item.customerName }}</span>
    <dict-tag v-if="item.tag" :options="biz_customer_tag" :value="item.tag" size="small" />
  </div>
  <div class="customer-info">
    <span>{{ item.phone }}</span>
  </div>
  <div class="customer-stats">
    <span class="stat-item">
      <el-tag :type="item.hasDeal ? 'success' : 'info'" size="small" effect="plain">
        {{ item.hasDeal ? '已成交' : '未成交' }}
      </el-tag>
    </span>
    <span class="stat-item" v-if="item.totalConsumed > 0">
      <span class="stat-label">消费</span>
      <span class="stat-value">¥{{ item.totalConsumed }}</span>
    </span>
    <span class="stat-item" v-if="item.avgSatisfaction">
      <span class="stat-label">满意度</span>
      <el-rate :model-value="item.avgSatisfaction" disabled size="small" />
    </span>
  </div>
</div>
```

### 步骤3：前端 - 美化CSS样式

**文件**: `d:/fuchenpro/front/src/views/business/sales/index.vue` style部分

修改现有样式并新增：

```css
.customer-item {
  padding: 10px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid #ebeef5;  /* 新增边框 */
  background: #fff;
}
.customer-item:hover {
  background: #f5f7fa;
  border-color: #c0c4cc;  /* 悬停时边框加深 */
}
.customer-item.active {
  background: #ecf5ff;
  border-color: #409eff;  /* 选中时蓝色边框 */
}
.customer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.customer-name {
  font-weight: 500;
  font-size: 14px;
}
.customer-info {
  display: flex;
  align-items: center;
  margin-top: 2px;
  font-size: 12px;
  color: #909399;
}
.customer-stats {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 6px;
  font-size: 12px;
  color: #606266;
}
.stat-item {
  display: flex;
  align-items: center;
  gap: 2px;
}
.stat-label {
  color: #909399;
  font-size: 11px;
}
.stat-value {
  color: #409eff;
  font-weight: 500;
}
```

---

## 修改文件清单

| 文件 | 修改内容 |
|------|----------|
| `webman/app/service/BizCustomerService.php` | searchCustomer 方法追加统计查询 |
| `front/src/views/business/sales/index.vue` | 客户卡片模板+CSS样式 |

## 注意事项

1. 后端统计查询在客户列表加载时执行，需注意性能（limit 50，每个客户3次子查询）
2. 如果性能有问题，可后续优化为一条SQL的子查询方式
3. `hasDeal`、`totalConsumed`、`avgSatisfaction` 字段为后端动态追加，非数据库字段
4. 前端使用 `item.hasDeal`（驼峰）对应后端 `has_deal`（蛇形），框架自动转换
