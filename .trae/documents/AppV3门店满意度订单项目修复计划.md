# AppV3 最近订单门店+详情满意度+订单项目简化 修复计划

## 问题描述
从截图分析发现3个问题：

### 问题1：最近订单列表 - 操作类型门店不显示
**现象**：
- 开单类型订单：✅ 显示 "逆龄奢·宜川店"
- 操作类型订单：❌ 只显示 "客户1 持卡"，无门店信息

**根因**：
```
biz_customer_archive 表数据：
┌────────────┬──────────────┬────────────────┬───────────┬────────────┐
│ archive_id │ customer_name│ enterprise_name│ store_name│ source_type│
├────────────┼──────────────┼────────────────┼───────────┼────────────┤
│          5 │ 客户1        │ (NULL)         │ (NULL)    │ 1 (操作)   │ ❌
│          1 │ 客户1        │ 逆龄奢         │ 宜川店    │ 0 (开单)   │ ✅
└────────────┴──────────────┴────────────────┴───────────┴────────────┘
```

操作类型的档案记录中 `enterprise_name` 和 `store_name` 为 NULL，因为：
- 操作记录表中的这两个字段也是 NULL
- 创建档案时直接复制了操作记录的字段值

### 问题2：操作详情页 - 满意度星级不显示
**现象**：有星标图标但无星级评分显示
**可能原因**：uView 2.x 的 u-rate 组件参数或数据类型问题

### 问题3：操作详情页 - 订单项目需要简化
**需求**：操作类型订单不需要显示"方案价、成交价、实付"（因为不涉及成交）
**保留**：品项名 + 次数

---

## 修复方案

### 步骤1：修复后端 - 操作档案创建时补充企业名和门店名

**文件**: [BizCustomerArchiveService.php:89-169](file:///d:/fuchenpro/webman/app/service/BizCustomerArchiveService.php#L89-L169)

在 `insertArchiveFromOperation()` 方法中，当 `$recordModel->enterprise_name` 或 `$recordModel->store_name` 为空时，通过 `package_id` 从套餐/订单中获取：

```php
// 第130行附近，修改 enterprise_name 和 store_name 的获取逻辑
$enterpriseName = $recordModel->enterprise_name ?? null;
$storeName = $recordModel->store_name ?? null;

if (!$enterpriseName || !$storeName) {
    if (!empty($recordModel->package_id)) {
        $pkg = \app\model\BizCustomerPackage::where('package_id', $recordModel->package_id)->first();
        if ($pkg) {
            if (!$enterpriseName) $enterpriseName = $pkg->enterprise_name;
            if (!$storeName) $storeName = $pkg->store_name;
            if (!$storeName && !empty($pkg->order_id)) {
                $order = \app\model\BizSalesOrder::find($pkg->order_id);
                if ($order) {
                    if (!$enterpriseName) $enterpriseName = $order->enterprise_name;
                    $storeName = $order->store_name;
                }
            }
        }
    }
}

// 然后在 $data 数组中使用 $enterpriseName 和 $storeName
$data = [
    // ...
    'enterprise_name' => $enterpriseName,
    'store_name' => $storeName,
    // ...
];
```

### 步骤2：修复前端 - 满意度组件优化

**文件**: [detail.vue:18-22](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue#L18-L22)

uView 2.x 的 `u-rate` 组件可能需要使用不同的属性名。检查并修复：

```html
<!-- 方案A：使用 v-model 替代 :value -->
<u-rate v-model="Number(orderInfo.satisfaction) || 0" :count="5" disabled />

<!-- 方案B：确保 value 是数字类型 -->
<u-rate :value="Number(orderInfo.satisfaction)" :count="5" :disabled="true" active-color="#FFB800" />
```

### 步骤3：修改前端 - 操作模式订单项目简化

**文件**: [detail.vue:98-125](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue#L98-L125)

当 `detailMode === 'operation'` 时，隐藏"方案价、成交价、实付"，只显示品项名和次数：

```html
<!-- 订单项目区域 -->
<view class="item-body">
  <!-- 开单模式：显示完整价格信息 -->
  <template v-if="detailMode !== 'operation'">
    <view class="info-line">...</view>  <!-- 方案价 + 单价 -->
    <view class="info-line summary-line">...</view>  <!-- 成交价 + 实付 -->
  </template>
  
  <!-- 操作模式：只显示必要信息或完全省略价格 -->
</view>
```

或者更简洁的方式：操作模式下整个 item-body 都不显示（只有品项名+次数）。

### 步骤4：补充历史档案数据

更新已有的操作类型档案记录的企业名和门店名：

```sql
UPDATE biz_customer_archive a
INNER JOIN biz_operation_record r ON a.source_id = r.record_id AND a.source_type = '1'
LEFT JOIN biz_customer_package p ON r.package_id = p.package_id
SET a.enterprise_name = COALESCE(a.enterprise_name, p.enterprise_name),
    a.store_name = COALESCE(a.store_name, p.store_name)
WHERE a.source_type = '1'
AND (a.enterprise_name IS NULL OR a.store_name IS NULL);
```

---

## 实施步骤清单

| 序号 | 任务 | 文件 | 优先级 |
|------|------|------|--------|
| 1 | 修改 insertArchiveFromOperation 补充企业名门店 | BizCustomerArchiveService.php | 🔴 高 |
| 2 | 修复 u-rate 满意度组件 | detail.vue | 🔴 高 |
| 3 | 操作模式隐藏价格信息 | detail.vue | 🟡 中 |
| 4 | 补充历史档案数据 | SQL | 🟢 低 |
| 5 | 重启服务测试 | - | 🔴 高 |

## 预期效果

| 页面 | 字段 | 修复前 | 修复后 |
|------|------|--------|--------|
| 首页-最近订单 | 操作类型门店 | 无 | **逆龄奢·宜川店** |
| 操作详情-基础信息 | 满意度 | 无星级 | **⭐⭐⭐⭐⭐** |
| 操作详情-订单项目 | 价格信息 | 方案价/成交价/实付 | **仅品项名+次数** |

## 风险评估
- **风险等级**：低 🟢
- **影响范围**：AppV3 首页列表 + 操作详情页
- **回滚方案**：恢复原始代码即可
