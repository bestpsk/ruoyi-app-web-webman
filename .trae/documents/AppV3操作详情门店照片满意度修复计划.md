# AppV3 操作详情 - 门店/照片/满意度显示问题修复计划

## 问题描述
AppV3 操作详情页面存在3个数据显示问题：
1. ❌ **门店不显示**：显示 "-" （应该显示 企业名·门店名）
2. ❌ **照片不显示**：有空框但没有图片加载
3. ❌ **满意度不显示**：有星标图标但无星级评分

## 根因分析

### 问题1：门店不显示

**文件**: [BizOperationRecordService.php:129-144](file:///d:/fuchenpro/webman/app/service/BizOperationRecordService.php#L129-L144)

**当前代码**:
```php
if (!$enterpriseName || !$storeName) {
    if ($record->customer_package_id) {  // ❌ 此字段不存在于数据库！
        $pkg = \app\model\BizCustomerPackage::find($record->customer_package_id);
    }
}
```

**数据库实际情况**:
```sql
-- customer_package_id 字段不存在！
-- 但 package_id 字段存在且值为 1
SELECT record_id, package_id, enterprise_name, store_name 
FROM biz_operation_record WHERE record_id = 9;
-- 结果: package_id=1, enterprise_name=NULL, store_name=NULL
```

**修复方案**: 将查询条件从 `customer_package_id` 改为 `package_id`

### 问题2：照片不显示

**文件**: [vite.config.js:10-16](file:///d:/fuchenpro/AppV3/vite.config.js#L10-L16)

**当前代理配置**:
```javascript
proxy: {
  '/prod-api': {  // 只代理 API 请求
    target: 'http://localhost:8787',
    changeOrigin: true,
    rewrite: (path) => path.replace(/^\/prod-api/, '')
  }
  // ❌ 缺少 /profile 的代理配置！
}
```

**前端图片URL**: `/profile/upload/20260510/xxx.jpg`
**请求路径**: 直接请求 AppV3 开发服务器 (localhost:5174)
**预期路径**: 应该代理到 Webman (localhost:8787) 的静态文件服务

**修复方案**: 在 Vite 配置中添加 `/profile` 代理

### 问题3：满意度不显示

**文件**: [detail.vue:18-22](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue#L18-L22)

**模板代码**:
```html
<view v-if="detailMode === 'operation' && orderInfo.satisfaction" class="info-row">
  <u-icon name="star" size="20" color="#86909C" />
  <text class="label">满意度</text>
  <u-rate :value="Number(orderInfo.satisfaction)" :count="5" disabled />
</view>
```

**数据流**:
```
数据库: satisfaction = 5
后端转换: satisfaction → satisfaction (无变化，无下划线)
前端映射: satisfaction: record.satisfaction ✅
模板条件: orderInfo.satisfaction = 5 (truthy) ✅
u-rate: :value="Number(5)" = 5 ✅
```

**可能原因**:
1. uView 2.x 的 `u-rate` 组件在某些情况下可能不渲染
2. 或者 satisfaction 值在某个环节变成了字符串/undefined

**修复方案**: 确保 satisfaction 字段的类型正确性，并检查 u-rate 组件使用方式

## 实施步骤

### 步骤1：修改后端 Service - 修复门店查询逻辑

**文件**: `d:\fuchenpro\webman\app\service\BizOperationRecordService.php`

将第129-144行改为通过 `package_id` 查找套餐信息:

```php
$enterpriseName = $record->enterprise_name;
$storeName = $record->store_name;

if (!$enterpriseName || !$storeName) {
    // 优先通过 package_id 查找客户套餐
    if (!empty($record->package_id)) {
        $pkg = \app\model\BizCustomerPackage::where('package_id', $record->package_id)->first();
        if ($pkg) {
            if (!$enterpriseName) $enterpriseName = $pkg->enterprise_name;
            if (!$storeName) $storeName = $pkg->store_name;
            // 如果套餐中没有门店信息，尝试从关联订单获取
            if (!$storeName && !empty($pkg->order_id)) {
                $order = \app\model\BizSalesOrder::find($pkg->order_id);
                if ($order) {
                    if (!$enterpriseName) $enterpriseName = $order->enterprise_name;
                    $storeName = $order->store_name;
                }
            }
        }
    }
    // 如果还没有，尝试从客户表获取
    if ((!$enterpriseName || !$storeName) && !empty($record->customer_id)) {
        $customer = \app\model\BizCustomer::find($record->customer_id);
        if ($customer) {
            if (!$enterpriseName) $enterpriseName = $customer->enterprise_name;
            if (!$storeName) $storeName = $customer->store_name;
        }
    }
}
```

### 步骤2：修改 Vite 配置 - 添加图片代理

**文件**: `d:\fuchenpro\AppV3\vite.config.js`

```javascript
proxy: {
  '/prod-api': {
    target: 'http://localhost:8787',
    changeOrigin: true,
    rewrite: (path) => path.replace(/^\/prod-api/, '')
  },
  '/profile': {  // ✅ 新增：代理静态资源（图片等）
    target: 'http://localhost:8787',
    changeOrigin: true
  }
}
```

### 步骤3：修改前端 detail.vue - 确保满意度正确显示

**文件**: `d:\fuchenpro\AppV3\src\pages\business\order\detail.vue`

确保 satisfaction 字段映射正确（已在上次修复中处理，验证即可）:

```javascript
// 第235行 - 已修复为 camelCase
satisfaction: record.satisfaction,  // satisfaction 无下划线，保持原样
```

同时确保模板中的 u-rate 组件参数正确:

```html
<!-- 第18-22行 -->
<view v-if="detailMode === 'operation' && orderInfo.satisfaction != null" class="info-row">
  <u-icon name="star" size="20" color="#86909C" />
  <text class="label">满意度</text>
  <u-rate :value="Number(orderInfo.satisfaction) || 0" :count="5" :allowHalf="false" disabled activeColor="#FFB800" inactiveColor="#E5E6EB" />
</view>
```

### 步骤4：重启服务并测试

1. **重启 Webman**: `php d:\fuchenpro\webman\windows.php restart`
2. **重启 AppV3 开发服务器**（Vite 配置变更后需要重启）

## 预期效果

| 字段 | 修复前 | 修复后 |
|------|--------|--------|
| 门店 | - | **逆龄奢·宜川店** |
| 照片 | 空框 | **显示操作前/操作后照片** |
| 满意度 | 无星级 | **⭐⭐⭐⭐⭐ (5星)** |

## 风险评估
- **风险等级**：低 🟢
- **影响范围**：AppV3 操作详情页面 + Vite 开发服务器配置
- **回滚方案**：
  - 后端：恢复原始 `customer_package_id` 查询逻辑
  - 前端：移除 Vite 代理配置中的 `/profile` 条目
