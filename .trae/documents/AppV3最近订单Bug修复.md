# AppV3 最近订单 - 3个Bug修复计划

## 🔍 问题分析

从截图和代码分析，存在以下3个问题：

### Bug 1: 标签显示"开单/操作/还款"
- **现象**: 订单状态标签显示的是 "已完成"、"待确认"、"确认中" 等，而非预期的简洁状态
- **原因**: 后端 `biz_sales_order.order_status` 字段存储的是中文描述（如"已确认"、"未确认"），不是简单的 '0'/'1'
- **修复**: 需要根据实际值映射为更友好的状态文本

### Bug 2: 企业和门店不显示
- **现象**: 客户名称下方没有显示企业和门店信息
- **原因**: 后端返回数据中 `enterpriseName`/`storeName` 可能为 null，或前端映射时字段名不匹配
- **修复**: 检查后端实际返回的字段，确保正确映射

### Bug 3: 点击订单报错 `[error] Object`
- **现象**: 控制台输出 `[error] Object`
- **原因**: 
  - `handleOrderClick` 使用 `uni.navigateTo` 跳转到 `/pages/work/index`
  - 但 `work/index` 是 **tabBar 页面**，tabBar 页面不能用 `navigateTo`，必须用 `switchTab`
  - 错误被 catch 后打印了 error 对象
- **修复**: 将 `navigateTo` 改为 `switchTab`

---

## 🛠️ 修复方案

### 第1步: 修复状态标签显示

修改 [index.vue](file:///d:/fuchenpro/AppV3/src/pages/index.vue) 中的 status 映射逻辑：

```javascript
// 当前代码（有问题）
status: (order.order_status === '1' || order.orderStatus === '1') ? '已完成' : '待确认'

// 修改为：根据实际 orderStatus 值做完整映射
status: getOrderStatusLabel(order.orderStatus)
```

添加映射函数：
```javascript
function getOrderStatusLabel(status) {
  const map = {
    '0': '待确认', '1': '已完成',
    '未确认': '待确认', '已确认': '已完成', '已取消': '已取消'
  }
  return map[status] || (status || '未知')
}
```

### 第2步: 修复企业门店显示

在 [index.vue](file:///d:/fuchenpro/AppV3/src/pages/index.vue) 中调试 store 字段映射，确保使用正确的驼峰字段名：

```javascript
store: [order.enterpriseName, order.storeName].filter(Boolean).join('·')
```

如果仍不显示，需要检查后端实际返回的数据结构。

### 第3步: 修复点击跳转报错

修改 [OrderList.vue](file:///d:/fuchenpro/AppV3/src/components/home/OrderList.vue) 中的跳转方式：

```javascript
// 当前代码（错误）
function handleOrderClick(item) {
  uni.navigateTo({ url: '/pages/work/index' })
}

// 修改为（正确）
function handleOrderClick(item) {
  uni.switchTab({ url: '/pages/work/index' })
}
```

同样修改 `handleMore` 函数。

---

## 📝 修改文件清单

| 文件 | Bug | 修改内容 |
|------|-----|----------|
| `AppV3/src/pages/index.vue` | #1 | 状态映射函数 |
| `AppV3/src/pages/index.vue` | #2 | 企业门店字段映射 |
| `AppV3/src/components/home/OrderList.vue` | #3 | navigateTo → switchTab |

---

## ✅ 预期效果

1. ✅ 状态标签正确显示：已确认→"已完成"、未确认→"待确认"
2. ✅ 企业·门店信息正常显示在客户姓名下方
3. ✅ 点击订单不再报错，正确跳转到工作台
