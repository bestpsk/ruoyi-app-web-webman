# AppV3 订单详情 - 信息补全修复计划

## 🔍 问题分析

从截图和代码分析，订单详情页面存在以下缺失：

### 缺失信息清单

| 区域 | 当前状态 | 缺失内容 |
|------|---------|----------|
| **订单基本信息** | 部分显示 | ❌ 套餐名称、❌ 成交金额、❌ 实付金额、❌ 欠款 |
| **订单项目** | 数据异常 | ❌ 品项名称(字段不对)、❌ 成交金额、❌ 单次价 |

### 根本原因

1. **字段名不匹配**：后端返回驼峰/下划线格式，前端取的字段名可能不正确
2. **模板未渲染关键字段**：`package_name` 等字段没有在模板中展示
3. **items 字段映射错误**：后端 items 中是 `product_name`/`plan_price`/`deal_amount`/`quantity`，前端用了 `itemName`/`price`/`count`

---

## 📊 后端数据结构参考

### biz_sales_order (订单主表)
```
order_no, customer_name, store_name,
package_name,           ← 套餐名称 (缺失)
total_amount,            ← 方案总金额 (缺失)
deal_amount,             ← 成交总金额 (缺失)
order_status, create_time, remark
```

### biz_order_item (订单明细)
```
product_name,            ← 品项名称 (字段映射错)
quantity,                ← 次数 (字段映射错)
plan_price,              ← 方案价格/总价 (字段映射错)
deal_amount,             ← 成交金额 (缺失)
is_deal                  ← 是否成交
```

---

## 🛠️ 修复方案

### 第1步：修改订单基本信息区域

在 `.info-body` 中添加缺失字段：

```vue
<view class="info-row" v-if="orderInfo.packageName">
  <text class="label">套餐</text>
  <text class="value">{{ orderInfo.packageName }}</text>
</view>
<view class="info-row">
  <text class="label">金额</text>
  <text class="value amount">¥{{ orderInfo.dealAmount || orderInfo.totalAmount || '0.00' }}</text>
</view>
<view class="info-row" v-if="orderInfo.totalAmount && orderInfo.dealAmount != orderInfo.totalAmount">
  <text class="label">方案总额</text>
  <text class="value" style="text-decoration: line-through; color: #86909C;">¥{{ orderInfo.totalAmount }}</text>
</view>
```

### 第2步：修改订单项目区域

重写 item-card 的字段映射：

```vue
<view v-for="(item, idx) in orderItems" :key="idx" class="item-card">
  <view class="item-header">
    <text class="item-name">{{ item.productName || item.product_name || item.itemName || '-' }}</text>
    <text v-if="item.isDeal === '1' || item.is_deal === '1'" class="deal-tag">已成交</text>
  </view>
  <view class="item-detail">
    <text>数量: {{ item.quantity || item.count || 0 }}次</text>
    <text>单价: ¥{{ ((item.planPrice || item.plan_price || 0) / (item.quantity || item.count || 1)).toFixed(2) }}</text>
  </view>
  <view class="item-detail item-prices">
    <text>方案价: ¥{{ item.planPrice || item.plan_price || '0.00' }}</text>
    <text class="deal-amount">成交: ¥{{ item.dealAmount || item.deal_amount || '0.00' }}</text>
  </view>
</view>
```

### 第3步：添加样式补充

```scss
.item-header { display: flex; align-items: center; gap: 12rpx; margin-bottom: 12rpx; }
.deal-tag { font-size: 20rpx; color: #00B42A; background: #E8FFEA; padding: 4rpx 12rpx; border-radius: 6rpx; }
.item-prices { margin-top: 8rpx; justify-content: space-between; }
.deal-amount { color: #FF6B35; font-weight: 600; }
```

---

## 📝 修改文件清单

| 文件 | 修改内容 |
|------|----------|
| `AppV3/src/pages/business/order/detail.vue` | 补全订单信息和项目明细的模板和样式 |

---

## ✅ 预期效果

### 修复前（当前）
```
SO2026050800005          未知
客户   找间距
门店   肇嘉浜
金额   ¥3980.00
时间   2026-05-08 21:37
备注   复古风格电饭锅电饭锅

订单项目
数量: 10  单价: ¥0.00  小计: ¥0.00     ← 数据全错
```

### 修复后（目标）
```
SO2026050800005          已确认
客户   找间距
门店   肇嘉浜
套餐   11111                    ← 新增
方案总额 ¥12850 (划掉)         ← 新增
金额   ¥3980.00                 ← 改为成交金额
时间   2026-05-08 21:37
备注   复古风格电饭锅电饭锅

订单项目
┌─────────────────────────────────┐
│ 11111                    [已成交] │ ← 品项名称+成交标签
│ 数量: 10次  单次价: ¥398.00     │ ← 单次价计算
│ 方案价: ¥3980.00  成交: ¥3980.00│ ← 双价格显示
└─────────────────────────────────┘
```
