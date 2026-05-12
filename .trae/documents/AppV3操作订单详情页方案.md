# AppV3 操作订单详情页方案

## 📋 需求

AppV3端点击"操作"类型订单时，显示操作记录详情页：
- **上面**：基础信息（借鉴订单详情页风格）
- **下面**：操作项目列表

---

## 🔍 数据来源

### biz_operation_record 表字段

| 字段 | 说明 | 显示 |
|------|------|------|
| record_id | 记录ID | 编号 |
| operation_type | 类型(0持卡/1体验) | 类型标签 |
| customer_name | 客户名称 | 客户 |
| product_name | 品项名称 | 项目 |
| operation_quantity | 操作次数 | 次数 |
| consume_amount | 消耗金额 | 金额 |
| trial_price | 体验价 | 金额(体验) |
| package_no | 持卡编号 | 编号 |
| satisfaction | 满意度(1-5) | 星级 |
| customer_feedback | 顾客反馈 | 反馈 |
| before_photo/after_photo | 操作前后照片 | 照片 |
| operator_user_name | 操作人 | 操作人 |
| operation_date | 操作日期 | 时间 |
| enterprise_name/store_name | 企业/门店 | 门店 |
| remark | 备注 | 备注 |

---

## ✅ 实现方案

### 方案：复用 detail.vue，支持双模式

修改 [detail.vue](file:///d:\fuchenpro\AppV3\src\pages\business\order\detail.vue)，使其同时支持：

1. **订单详情模式** (`type=order`) - 原有逻辑
2. **操作详情模式** (`type=operation`) - 新增逻辑

### 修改文件清单

| 文件 | 修改内容 |
|------|---------|
| `AppV3/src/api/business/operationRecord.js` | 添加 getOperationRecord API |
| `AppV3/src/pages/business/order/detail.vue` | 支持操作详情展示 |
| `AppV3/src/components/home/OrderList.vue` | 跳转时传递 type 参数 |

---

## 📝 具体实现

### 步骤1：添加操作记录API

**文件**：[operationRecord.js](file:///d:\fuchenpro\AppV3\src\api\business\operationRecord.js)

```javascript
export function getOperationRecord(id) {
  return request({ url: '/business/operation/' + id, method: 'get' })
}
```

### 步骤2：修改 OrderList.vue 跳转逻辑

**文件**：[OrderList.vue](file:///d:\fuchenpro\AppV3\src\components\home\OrderList.vue)

```javascript
function handleOrderClick(item) {
  if (!item.sourceId) return
  
  const sourceType = item.sourceType || item.source_type
  
  // 根据类型跳转到不同详情
  const typeMap = { '0': 'order', '1': 'operation', '2': 'repayment' }
  const type = typeMap[sourceType] || 'order'
  
  uni.navigateTo({
    url: `/pages/business/order/detail?id=${item.sourceId}&type=${type}`
  })
}
```

### 步骤3：修改 detail.vue 支持操作详情

**文件**：[detail.vue](file:///d:\fuchenpro\AppV3\src\pages\business\order\detail.vue)

#### 3.1 导入新API
```javascript
import { getSalesOrder, enterpriseAudit, financeAudit } from '@/api/business/salesOrder'
import { getOperationRecord } from '@/api/business/operationRecord'
```

#### 3.2 添加 mode 状态
```javascript
const detailMode = ref('order') // 'order' 或 'operation'
```

#### 3.3 修改 onMounted 和 loadDetail
```javascript
onMounted(() => {
  const pages = getCurrentPages()
  const options = pages[pages.length - 1].options || {}
  orderId.value = options.id ? parseInt(options.id) : null
  detailMode.value = options.type || 'order'
  
  uni.setNavigationBarTitle({ 
    title: detailMode.value === 'operation' ? '操作详情' : '订单详情'
  })
  loadDetail()
})

async function loadDetail() {
  if (!orderId.value) return
  try {
    uni.showLoading({ title: '加载中...' })
    
    let data
    if (detailMode.value === 'operation') {
      // 操作详情模式
      const response = await getOperationRecord(orderId.value)
      data = response.data || response
      // 将操作记录数据映射为统一的 orderInfo 格式
      orderInfo.value = {
        orderNo: data.package_no || ('OPR' + data.record_id),
        orderStatus: data.operation_type === '1' ? '4' : '1',
        customerName: data.customer_name,
        enterpriseName: data.enterprise_name,
        storeName: data.store_name,
        dealAmount: data.consume_amount || data.trial_price || 0,
        totalAmount: data.consume_amount || data.trial_price || 0,
        createTime: data.operation_date || data.create_time,
        remark: data.remark,
        operatorName: data.operator_user_name,
        satisfaction: data.satisfaction,
        feedback: data.customer_feedback,
        photos: [data.before_photo, data.after_photo].filter(Boolean)
      }
      // 构建项目列表
      orderItems.value = [{
        product_name: data.product_name,
        productName: data.product_name,
        quantity: data.operation_quantity,
        count: data.operation_quantity,
        planPrice: data.consume_amount || data.trial_price || 0,
        dealAmount: data.consume_amount || data.trial_price || 0,
        paidAmount: data.consume_amount || data.trial_price || 0
      }]
    } else {
      // 订单详情模式（原有逻辑）
      const response = await getSalesOrder(orderId.value)
      data = response.data || response
      orderInfo.value = data
      orderItems.value = data.items || []
    }
  } catch (e) { 
    console.error('加载详情失败:', e)
    uni.showToast({ title: '加载失败', icon: 'none' }) 
  } finally { 
    uni.hideLoading() 
  }
}
```

#### 3.4 修改模板显示

在模板中根据 `detailMode` 条件显示不同内容：

```vue
<template>
  <view class="detail-container">
    <!-- 基础信息区域 -->
    <view class="order-info">
      <view class="info-header">
        <text class="order-no">{{ orderInfo.orderNo }}</text>
        <view class="status-tag" :class="'status-' + getStatusClass()">
          {{ getStatusText() }}
        </view>
      </view>

      <view class="info-body">
        <!-- 操作详情特有：操作人、满意度等 -->
        <view v-if="detailMode === 'operation'" class="info-row">
          <u-icon name="account-fill" size="20" color="#86909C" />
          <text class="label">操作人</text>
          <text class="value">{{ orderInfo.operatorName || '-' }}</text>
        </view>

        <view v-if="detailMode === 'operation' && orderInfo.satisfaction" class="info-row">
          <u-icon name="star" size="20" color="#86909C" />
          <text class="label">满意度</text>
          <u-rate :value="orderInfo.satisfaction" disabled />
        </view>

        <!-- 公共字段：客户、门店、金额、时间 -->
        ...
        
        <!-- 操作详情：照片 -->
        <view v-if="detailMode === 'operation' && orderInfo.photos?.length" class="info-row photo-row">
          <text class="label">照片</text>
          <view class="photo-list">
            <image v-for="(url, idx) in orderInfo.photos" :key="idx" :src="getImgUrl(url)" mode="aspectFill" class="photo-img" />
          </view>
        </view>

        <!-- 操作详情：反馈 -->
        <view v-if="detailMode === 'operation' && orderInfo.feedback" class="info-row">
          <u-icon name="chat" size="20" color="#86909C" />
          <text class="label">反馈</text>
          <text class="value">{{ orderInfo.feedback }}</text>
        </view>
      </view>
    </view>

    <!-- 项目列表区域（共用） -->
    <view class="items-section" v-if="orderItems.length > 0">
      ...
    </view>
  </view>
</template>
```

#### 3.5 添加辅助方法
```javascript
function getStatusText() {
  if (detailMode.value === 'operation') {
    const typeMap = { '0': '持卡操作', '1': '体验操作' }
    return typeMap[orderInfo.value.operation_type] || '操作'
  }
  return getOrderStatusName(orderInfo.value.orderStatus || orderInfo.value.status)
}

function getStatusClass() {
  if (detailMode.value === 'operation') {
    return orderInfo.value.operation_type === '1' ? '4' : '1'
  }
  return orderInfo.value.orderStatus || orderInfo.value.status || '0'
}

function getImgUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return '/profile/upload/' + path
}
```

---

## 📊 效果预览

### 操作详情页布局
```
┌─────────────────────────────────────┐
│ ← 订单详情                          │
├─────────────────────────────────────┤
│ OPR202605090001         [持卡操作]   │
│ ─────────────────────────────────── │
│ 👤 操作人     admin                  │
│ 👤 客户       客户1                  │
│ 🏠 门店       迭龄荟·宜川店           │
│ 💰 金额       ¥796.00               │
│ ⭐ 满意度     ⭐⭐⭐⭐⭐              │
│ 🕐 时间       2026-05-09             │
│ 📷 照片       [前] [后]              │
│ 💬 反馈       1111                   │
├─────────────────────────────────────┤
│ 📋 订单项目                    1项   │
│ ─────────────────────────────────── │
│ 1. 品项2×1                    [已成交] │
│   方案价: ¥398.00   单价: ¥398.00   │
│   成交价: ¥398.00   实付: ¥398.00   │
└─────────────────────────────────────┘
```

---

## 📌 修改清单

| 序号 | 文件 | 操作 |
|------|------|------|
| 1 | `api/business/operationRecord.js` | 添加 getOperationRecord API |
| 2 | `components/home/OrderList.vue` | 跳转传递 type 参数 |
| 3 | `pages/business/order/detail.vue` | 支持操作详情模式 |

**总计：修改3个文件**
