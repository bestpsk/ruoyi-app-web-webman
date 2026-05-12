# 订单详情页UI细节优化计划

## 优化需求

### 需求1：门店显示完整信息 ⭐⭐
**当前位置**: detail.vue 第24-28行

**当前状态**:
```vue
<view class="info-row">
  <u-icon name="home" size="24" color="#86909C" />
  <text class="label">门店</text>
  <text class="value">{{ orderInfo.storeName || '-' }}</text>
</view>
```

**显示效果**: `○ 门店  肇嘉浜` （仅显示门店名）

**目标效果**: `○ 门店  馥田诗·肇嘉浜` （企业名·门店名）

**数据来源分析**:
- 后端返回字段：`enterprise_name` (企业名) + `store_name` (门店名)
- 前端接收字段：`orderInfo.enterpriseName` + `orderInfo.storeName`
- 显示格式：`{企业名} · {门店名}`

---

### 需求2：订单项目区关键字段靠右显示 ⭐⭐⭐
**当前位置**: detail.vue 第70-98行（item-body区域）

**当前状态**:
```
品项: 额电饭锅电饭锅 | 次数: 10次          ← 全部左对齐
方案价: ¥3980.00 | 单次价: ¥398.00
成交: ¥3980.00 | 实付: ¥3980.00
```

**目标效果**:
```
品项: 额电饭锅电饭锅          次数: 10次    ← 次数靠右
方案价: ¥3980.00            单次价: ¥398.00 ← 单次价靠右
成交: ¥3980.00              实付: ¥3980.00  ← 实付靠右
```

**布局要求**:
- 左侧固定：标签 + 值（品项、方案价、成交）
- 右侧对齐：标签 + 值（**次数、单次价、实付**）
- 中间：弹性空间填充
- 移除分隔符 `|`（改用空间分隔）

---

## 实施方案

### 步骤1：修改门店显示逻辑 ⭐⭐

**文件**: `d:\fuchenpro\AppV3\src\pages\business\order\detail.vue`
**位置**: 第24-28行

**修改内容**:

```vue
<!-- 修改前 -->
<view class="info-row">
  <u-icon name="home" size="24" color="#86909C" />
  <text class="label">门店</text>
  <text class="value">{{ orderInfo.storeName || '-' }}</text>
</view>

<!-- 修改后 -->
<view class="info-row">
  <u-icon name="home" size="24" color="#86909C" />
  <text class="label">门店</text>
  <text class="value">
    <template v-if="orderInfo.enterpriseName">
      {{ orderInfo.enterpriseName }} · {{ orderInfo.storeName || '-' }}
    </template>
    <template v-else>
      {{ orderInfo.storeName || '-' }}
    </template>
  </text>
</view>
```

**逻辑说明**:
1. 如果有 `enterpriseName`（企业名），显示：`企业名 · 门店名`
2. 如果没有企业名，仅显示：`门店名`（兼容旧数据）

---

### 步骤2：重构订单项目区布局为左右分布 ⭐⭐⭐

**文件**: `d:\fuchenpro\AppV3\src\pages\business\order\detail.vue`
**位置**: 第70-98行（item-body区域）

#### 2.1 修改模板结构

**完全替换为新的布局**:

```vue
<view class="item-body">
  <!-- 第一行：品项(左) | 次数(右) -->
  <view class="info-line">
    <view class="info-left">
      <text class="info-label">品项</text>
      <text class="info-value">{{ item.productName || item.product_name || '-' }}</text>
    </view>
    <view class="info-right">
      <text class="info-label">次数</text>
      <text class="info-value highlight">{{ item.quantity || item.count || 0 }}次</text>
    </view>
  </view>

  <!-- 第二行：方案价(左) | 单次价(右) -->
  <view class="info-line">
    <view class="info-left">
      <text class="info-label">方案价</text>
      <text class="info-value price">¥{{ item.planPrice || item.plan_price || item.price || '0.00' }}</text>
    </view>
    <view class="info-right">
      <text class="info-label">单次价</text>
      <text class="info-value">¥{{ getUnitPrice(item) }}</text>
    </view>
  </view>

  <!-- 第三行：成交(左) | 实付(右) [+欠款] -->
  <view class="info-line summary-line">
    <view class="info-left">
      <text class="info-label">成交</text>
      <text class="info-value amount">¥{{ item.dealAmount || item.deal_amount || '0.00' }}</text>
    </view>
    <view class="info-right">
      <template v-if="getOwedAmount(item) > 0">
        <text class="info-label">欠款</text>
        <text class="info-value owed">¥{{ getOwedAmount(item) }}</text>
        <text class="info-gap"></text>
      </template>
      <text class="info-label">实付</text>
      <text class="info-value paid">¥{{ item.paidAmount || item.paid_amount || '0.00' }}</text>
    </view>
  </view>
</view>
```

#### 2.2 新增/修改样式

```scss
/* 项目内容区 */
.item-body {
  display: flex;
  flex-direction: column;
  gap: 12rpx;
}

/* 信息行 - 改为flex布局支持左右分布 */
.info-line {
  display: flex;
  align-items: center;
  justify-content: space-between;  /* 关键：两端对齐 */
  font-size: 25rpx;
  line-height: 1.6;

  /* 左侧容器 */
  .info-left {
    display: flex;
    align-items: center;
    gap: 8rpx;
    flex: 1;  /* 占据剩余空间 */
  }

  /* 右侧容器 */
  .info-right {
    display: flex;
    align-items: center;
    gap: 8rpx;
    flex-shrink: 0;  /* 不压缩 */
    margin-left: auto;  /* 推到右侧 */
  }

  /* 标签统一样式 */
  .info-label {
    color: #86909C;
    white-space: nowrap;
    font-size: 24rpx;
  }

  /* 数值统一样式 */
  .info-value {
    color: #4E5969;
    font-size: 25rpx;

    &.highlight {  /* 次数 */
      color: #3D6DF7;
      font-weight: 500;
    }

    &.price {  /* 方案价 */
      color: #1D2129;
      font-weight: 500;
    }

    &.amount {  /* 成交金额 */
      color: #FF6B35;
      font-weight: 600;
    }

    &.paid {  /* 实付金额 */
      color: #00B42A;
      font-weight: 500;
    }

    &.owed {  /* 欠款金额 */
      color: #F56C6C;
      font-weight: 500;
    }
  }

  /* 右侧元素间距（用于欠款和实付之间）*/
  .info-gap {
    width: 16rpx;
  }

  /* 汇总行特殊样式 */
  &.summary-line {
    margin-top: 8rpx;
    padding-top: 12rpx;
    border-top: 1rpx solid #F2F3F5;
  }
}
```

---

## 布局原理说明

### Flexbox 两端对齐布局

```
┌─────────────────────────────────────────────┐
│ [info-line: justify-content: space-between] │
│                                             │
│  ┌─ info-left (flex: 1) ─┐┌─ info-right ─┐│
│  │ 品项: 产品名称        ││ 次数: 10次   ││
│  └────────────────────────┘└───────────────┘│
│                                             │
│  ←──── 弹性占据空间 ────→ ←─ 固定宽度 ──→ │
│         (自动扩展)           (不压缩)       │
└─────────────────────────────────────────────┘
```

**关键CSS属性**:
- `.info-line`: `display: flex` + `justify-content: space-between`
- `.info-left`: `flex: 1` (占据剩余空间)
- `.info-right`: `flex-shrink: 0` + `margin-left: auto` (靠右且不被压缩)

---

## 视觉效果对比

### 修改前（当前状态）
```
┌─────────────────────────────────┐
│ ○ 门店  肇嘉浜                 │  ← 仅门店名
│                                 │
│ 1. 额电饭锅电饭锅        已成交  │
│                                 │
│ 品项: 额电饭锅电饭锅 | 次数: 10次│  ← 紧凑左对齐
│ 方案价: ¥3980.00 | 单次价: ¥398│     有分隔符
│ 成交: ¥3980.00 | 实付: ¥3980.00│
└─────────────────────────────────┘
```

### 修改后（目标状态）
```
┌─────────────────────────────────┐
│ ○ 门店  馥田诗·肇嘉浜           │  ← 完整地址
│                                 │
│ 1. 额电饭锅电饭锅        已成交  │
│                                 │
│ 品项: 额电饭锅电饭锅    次数: 10次│  ← 次数靠右
│ 方案价: ¥3980.00      单次价: ¥398│  ← 单次价靠右
│ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ │
│ 成交: ¥3980.00        实付: ¥3980│  ← 实付靠右
└─────────────────────────────────┘
```

---

## 涉及文件清单

| 文件路径 | 操作类型 | 修改范围 |
|---------|---------|---------|
| `d:\fuchenpro\AppV3\src\pages\business\order\detail.vue` | **修改** | 第24-28行（门店）+ 第70-98行（项目布局）|

**无需修改其他文件**

---

## 测试验证场景

### 场景1：有企业名的订单
**前置条件**: `enterpriseName = "馥田诗"`, `storeName = "肇嘉浜"`

**验证点**:
- ✅ 门店行显示: `馥田诗·肇嘉浜`
- ✅ 格式正确，中间有 `·` 分隔

### 场景2：无企业名的历史订单
**前置条件**: `enterpriseName = null`, `storeName = "肇嘉浜"`

**验证点**:
- ✅ 门店行仅显示: `肇嘉浜`
- ✅ 不显示多余的 `·` 或空值

### 场景3：订单项目区布局
**验证点**:
- ✅ 第一行: "品项+产品名" 在左侧，"次数+N次"在右侧
- ✅ 第二行: "方案价+金额" 在左侧，"单次价+金额"在右侧
- ✅ 第三行: "成交+金额" 在左侧，"实付+金额"在右侧
- ✅ 如果有欠款: "欠款" 和 "实付" 都在右侧，中间有间距
- ✅ 左右两侧对齐整齐
- ✅ 不同长度的产品名称不影响右侧对齐

### 场景4：响应式测试
**验证点**:
- ✅ 产品名称很长时，左侧自适应，右侧仍靠右
- ✅ 屏幕宽度变化时，布局保持稳定
- ✅ 无文字重叠或溢出

---

## 风险评估

- **风险等级**: 🟢 极低风险
- **影响范围**: 仅UI展示层，不影响业务逻辑
- **向后兼容**: 完全兼容（企业名为空时降级显示）
- **技术难度**: 🟢 简单（标准Flexbox布局）

---

## 预计工时

- 步骤1（门店显示）: 5分钟
- 步骤2（项目布局）: 15分钟
- 测试验证: 10分钟
- **总计**: 约30分钟

---

## 总结

本次优化将实现：

✅ **门店信息完整化**: 显示"企业名·门店名"完整地址  
✅ **项目布局合理化**: 关键数值（次数、单次价、实付）靠右对齐  
✅ **视觉层次清晰化**: 左侧基础信息 vs 右侧关键数据  
✅ **阅读体验提升**: 信息分组更符合视觉习惯  

**核心改进**: 通过Flexbox的`space-between`实现优雅的两端对齐布局。
