# AppV3 最近订单 - 标签和点击详情修复

## 📋 需求分析

用户提出两个问题：
1. **标签应显示"开单/操作/还款"**：当前显示的是订单状态（已完成/待确认），但应该显示记录类型（开单/操作/还款）
2. **点击单个订单看详情**：当前跳转到工作台，应该跳转到订单详情页面

---

## 🔍 方案设计

### 核心思路：改用档案API

将数据源从 `/business/sales/list`（仅订单）改为 `/business/archive/list`（包含开单、操作、还款的统一档案表）。

### 数据映射

| 档案字段 | 显示内容 | 说明 |
|---------|---------|------|
| `customer_name` | 客户姓名 | 左侧名称 |
| `enterprise_name` + `store_name` | 企业·门店 | 副标题 |
| `amount` | 金额 | 右侧金额 |
| `source_type` | **标签文本** | 0→开单, 1→操作, 2→还款, 3→手动 |
| `archive_date` / `create_time` | 时间 | 底部时间 |
| `source_id` + `source_type` | 跳转参数 | 用于跳转到对应详情 |

### 标签样式映射

| source_type | 文本 | u-tag type | 颜色 |
|-------------|------|------------|------|
| 0 (开单) | 开单 | primary | 蓝色 |
| 1 (操作) | 操作 | success | 绿色 |
| 2 (还款) | 还款 | warning | 橙色 |
| 3 (手动) | 手动 | info | 灰色 |

### 点击跳转逻辑

```
switch(source_type):
  case '0' (开单) → navigateTo('/pages/business/order/detail?id=' + sourceId)
  case '1' (操作) → navigateTo('/pages/business/sales/operation?id=' + sourceId) 或提示
  case '2' (还款) → 提示或跳转工作台
  case '3' (手动) → 提示或无操作
```

---

## 🛠️ 实施步骤

### 第1步：新建档案API（AppV3）

创建 `d:\fuchenpro\AppV3\src\api\business\archive.js`：
```javascript
import request from '@/utils/request'

export function listArchive(params) {
  return request({ url: '/business/archive/list', method: 'get', params })
}
```

### 第2步：修改 index.vue - 改用档案API

- 引入 `listArchive` API
- 修改 `loadHomeData()` 中调用：
```javascript
const archiveRes = await listArchive({
  operatorUserId: userStore.getId,
  pageNum: 1,
  pageSize: 5,
  orderByColumn: 'archive_date',
  isAsc: 'desc'
})
orderList.value = (archiveRes.rows || []).map(item => ({
  id: item.archiveId || item.archive_id,
  name: item.customerName || item.customer_name || '',
  store: [item.enterpriseName || item.enterprise_name, item.storeName || item.store_name].filter(Boolean).join('·'),
  avatar: '/static/images/profile.jpg',
  amount: Number(item.amount || 0).toFixed(2),
  sourceType: item.sourceType || item.source_type,    // 来源类型
  sourceId: item.sourceId || item.source_id,            // 原始记录ID
  status: getSourceTypeLabel(item.sourceType || item.source_type),  // 标签文字
  createTime: item.archiveDate || item.archive_date || item.createTime
}))
```

添加标签映射函数：
```javascript
function getSourceTypeLabel(type) {
  const map = { '0': '开单', '1': '操作', '2': '还款', '3': '手动' }
  return map[type] || '未知'
}
```

### 第3步：修改 OrderList.vue - 标签和点击

#### 3.1 修改标签颜色映射
```javascript
function getStatusType(status) {
  const map = { '开单': 'primary', '操作': 'success', '还款': 'warning', '手动': 'info' }
  return map[status] || 'info'
}
```

#### 3.2 修改点击事件 - 跳转详情
```javascript
function handleOrderClick(item) {
  if (!item.sourceId) return
  
  switch (item.sourceType) {
    case '0': // 开单 → 订单详情
      uni.navigateTo({ url: `/pages/business/order/detail?id=${item.sourceId}` })
      break
    case '1': // 操作 → 操作详情（如果有）
      uni.navigateTo({ url: `/pages/business/sales/operation?recordId=${item.sourceId}` })
      break
    case '2': // 还款
      uni.showToast({ title: '查看还款记录', icon: 'none' })
      break
    default:
      uni.showToast({ title: '暂无详情', icon: 'none' })
  }
}
```

---

## 📝 修改文件清单

| 文件 | 操作 | 说明 |
|------|------|------|
| `AppV3/src/api/business/archive.js` | **新建** | 档案列表API |
| `AppV3/src/pages/index.vue` | **修改** | 改用档案API，修改数据映射和标签函数 |
| `AppV3/src/components/home/OrderList.vue` | **修改** | 修改标签颜色映射、点击跳转详情 |

---

## ✅ 预期效果

1. ✅ 标签正确显示：**开单**(蓝)、**操作**(绿)、**还款**(橙)、**手动**(灰)
2. ✅ 点击开单类型 → 跳转到订单详情页
3. ✅ 点击操作类型 → 跳转到操作详情页
4. ✅ "查看全部" → 跳转到工作台
