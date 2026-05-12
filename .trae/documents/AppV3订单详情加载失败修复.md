# AppV3 最近订单点击加载失败修复方案

## 🔍 根因分析

### 数据流追踪

```
首页 (index.vue)
  ↓ 调用 listArchive API
  ↓ 返回 biz_customer_archive 表数据
  ↓ 映射为 orderList
     ├── id: archive_id
     ├── sourceType: '0'开单 / '1'操作 / '2'还款
     └── sourceId: 
         ├── 开单(0) → sales_order.order_id ✅ 正确
         ├── 操作(1) → operation_record.record_id ❌ 问题！
         └── 还款(2) → repayment_record.id

用户点击订单
  ↓ handleOrderClick(item)
  ↓ uni.navigateTo(`/pages/business/order/detail?id=${item.sourceId}`)

订单详情页 (detail.vue)
  ↓ getSalesOrder(orderId)
  ↓ 调用后端 API: GET /business/sales/{orderId}
  ↓ 后端查询 biz_sales_order 表
  ↓ ❌ 用 operation_record.record_id 查 sales_order → 找不到！
```

### 核心问题

| 类型 | sourceId 实际值 | 详情页查询 | 结果 |
|------|---------------|-----------|------|
| 开单(0) | sales_order.order_id | 查 sales_order | ✅ 成功 |
| **操作(1)** | **operation_record.record_id** | 查 sales_order | **❌ 找不到！** |
| 还款(2) | repayment_record.id | 查 sales_order | ❌ 找不到 |

**截图中的 "ORDundefined"** 说明 orderId 是 undefined 或查询返回了空数据。

---

## ✅ 解决方案

### 方案：根据 sourceType 智能跳转

修改 [OrderList.vue](file:///d:\fuchenpro\AppV3\src\components\home\OrderList.vue) 的 `handleOrderClick` 方法：

```javascript
function handleOrderClick(item) {
  if (!item.sourceId) return
  
  const type = item.sourceType || item.source_type
  
  switch (type) {
    case '0': // 开单 → 订单详情
      uni.navigateTo({
        url: `/pages/business/order/detail?id=${item.sourceId}`
      })
      break
      
    case '1': // 操作 → 需要先找到关联的订单ID
      // 通过操作记录找到关联的套餐→订单
      uni.navigateTo({
        url: `/pages/business/order/detail?id=${item.sourceId}&type=operation`
      })
      break
      
    case '2': // 还款 → 暂不处理或提示
      uni.showToast({ title: '还款记录暂无详情', icon: 'none' })
      break
      
    default:
      uni.showToast({ title: '暂无详情', icon: 'none' })
  }
}
```

### 更优方案：修改订单详情页支持多种数据源

修改 [detail.vue](file:///d:\fuchenpro\AppV3\src\pages\business\order\detail.vue)，增加对"操作类型"的支持：

```javascript
onMounted(() => {
  const pages = getCurrentPages()
  const options = pages[pages.length - 1].options || {}
  orderId.value = options.id ? parseInt(options.id) : null
  const queryType = options.type || 'order' // order 或 operation
  uni.setNavigationBarTitle({ title: '订单详情' })
  loadDetail(queryType)
})

async function loadDetail(type = 'order') {
  if (!orderId.value) return
  try {
    uni.showLoading({ title: '加载中...' })
    
    let data
    if (type === 'operation') {
      // 操作类型：先获取操作记录，再找关联的订单
      const opRes = await getOperationRecord(orderId.value)
      const opData = opRes.data || opRes
      // 从操作记录中获取关联的订单ID
      if (opData.order_id || opData.orderId) {
        orderId.value = opData.order_id || opData.orderId
      }
    }
    
    const response = await getSalesOrder(orderId.value)
    data = response.data || response
    orderInfo.value = data
    orderItems.value = data.items || []
  } catch (e) { 
    console.error('加载订单详情失败:', e)
    uni.showToast({ title: '加载失败', icon: 'none' }) 
  } finally { 
    uni.hideLoading() 
  }
}
```

---

## 📝 推荐的最简方案

考虑到复杂度，推荐**最小改动方案**：

### 修改 OrderList.vue 的跳转逻辑

对于"操作"类型的记录，不跳转到订单详情页，而是显示提示或跳转到其他页面。

或者更实用的做法：**在首页数据映射时，将操作的 sourceId 改为关联的订单ID**

修改 [index.vue 第72行](file:///d:\fuchenpro\AppV3\src\pages\index.vue#L72)：

```javascript
// 当前：直接使用 source_id（可能是操作记录ID）
sourceId: item.sourceId || item.source_id,

// 优化后：对于操作类型，暂时不设置sourceId或做特殊处理
sourceId: (item.sourceType === '1') ? null : (item.sourceId || item.source_id),
```

但这会导致操作类型无法点击。

### 最终建议方案

**最佳平衡方案**：修改 `handleOrderClick`，对操作类型做特殊处理——如果有关联订单则跳转，否则提示。

---

## 📌 修改文件清单

| 文件 | 修改内容 |
|------|---------|
| `AppV3/src/components/home/OrderList.vue` | 修改 handleOrderClick 方法，区分不同类型 |

**总计：修改1个文件，约10行代码**
