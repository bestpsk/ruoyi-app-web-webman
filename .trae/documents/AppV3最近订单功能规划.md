# AppV3 - 最近订单功能规划

## 📋 需求分析

将AppV3首页"最近订单"从**硬编码假数据**改为**真实订单数据**，显示当前登录用户最近成交的销售订单。

---

## 📱 参考主流APP设计

### 显示数量
| APP | 首页订单展示数量 |
|-----|----------------|
| 美团/外卖 | 2-3条（横向滚动卡片） |
| 滴滴出行 | 3条（纵向列表） |
| 饿了么 | 3-5条（纵向列表） |
| 美团到店 | 4-5条（纵向列表） |

**建议：显示 5 条**，足够展示近期动态又不占用过多屏幕空间。

### 卡片信息设计
参考美团/滴滴风格，每条订单卡片应包含：

```
┌─────────────────────────────────────┐
│ [头像] 客户姓名        [状态标签]    │
│        企业名称·门店名称             │
│                                     │
│                    ¥成交金额       │
│                    订单编号 日期   │
└─────────────────────────────────────┘
```

---

## 🔧 实施步骤

### 第1步：前端 - 创建订单API

新建 `d:\fuchenpro\AppV3\src\api\business\sales.js`：
```javascript
import request from '@/utils/request'

export function listMyRecentOrders(params) {
  return request({ url: '/business/sales/list', method: 'get', params })
}
```

参数：`creatorUserId` + `pageNum=1` + `pageSize=5` + `orderByColumn=create_time` + `isAsc=desc`

### 第2步：修改 OrderList.vue 组件

**改动点：**
1. 移除硬编码假数据，改为 props 接收或内部请求API
2. 调用 `listMyRecentOrders` 获取当前用户的最近5条订单
3. 字段映射：

| 后端字段 | 前端显示 | 说明 |
|---------|---------|------|
| `customer_name` | 客户姓名 | 左侧大字 |
| `enterprise_name` | 企业名 | 副标题 |
| `store_name` | 门店名 | 副标题（企业·门店格式） |
| `deal_amount` | 成交金额 | 右侧金额 |
| `order_status` | 订单状态 | 标签（已确认/未确认） |
| `order_no` | 订单编号 | 底部小字 |
| `create_time` | 下单时间 | 底部小字 |
| `customer_avatar` | 客户头像 | 左侧圆形头像 |

4. 状态映射：
   - `0`(未确认) → `warning` 类型 "待确认"
   - `1`(已确认) → `success` 类型 "已完成"

5. 点击跳转到订单详情或工作台

### 第3步：修改 index.vue 首页

在 `loadHomeData()` 中加载订单数据并传递给 OrderList 组件：
```javascript
async function loadHomeData() {
  // ... 其他数据
  
  // 加载最近订单
  const orderRes = await listMyRecentOrders({
    creatorUserId: userStore.getId,
    pageNum: 1,
    pageSize: 5,
    orderByColumn: 'create_time',
    isAsc: 'desc'
  })
  orderList.value = (orderRes.rows || []).map(order => ({
    id: order.orderId,
    name: order.customerName,
    store: `${order.enterpriseName || ''}·${order.storeName || ''}`,
    avatar: order.customerAvatar || '/static/images/profile.jpg',
    amount: Number(order.dealAmount || 0).toFixed(2),
    remainCount: 0,
    totalCount: 0,
    status: order.orderStatus === '1' ? '已完成' : '进行中',
    orderNo: order.orderNo,
    createTime: order.createTime
  }))
}
```

### 第4步：后端 - 确认API支持

现有 `/business/sales/list` 已支持 `creator_user_id` 参数筛选，无需修改后端。

---

## 📝 修改文件清单

| 文件 | 操作 | 说明 |
|------|------|------|
| `AppV3/src/api/business/sales.js` | **新建** | 订单API接口 |
| `AppV3/src/components/home/OrderList.vue` | **修改** | 接入真实数据，优化UI |
| `AppV3/src/pages/index.vue` | **修改** | 加载订单数据 |

---

## ✅ 预期效果

1. 首页"最近订单"显示当前用户最近 **5条** 成交订单
2. 每条订单显示：客户姓名、企业门店、成交金额、状态、时间
3. 点击订单可跳转查看详情
4. 无订单时显示空状态提示
5. "查看全部"跳转到工作台订单列表
