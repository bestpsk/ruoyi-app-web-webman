# AppV3 满意度显示 + 操作订单项目优化计划

## 问题描述

### 问题1：满意度星级不显示
**现象**：操作详情页有"满意度"图标和标签，但无星级评分显示

**根因分析**：

从 [u-rate.vue:88-90,115,127](file:///d:/fuchenpro/AppV3/node_modules/uview-plus/components/u-rate/u-rate.vue#L88-L115) 源码发现：

```javascript
// 第88行：文档说明
@property {String | Number} value 用于v-model双向绑定选中的星星数量 (默认 1)

// 第115行：Vue3 初始化
activeIndex: this.modelValue,  // ← Vue3 用 modelValue！

// 第127行：Vue3 watch
modelValue(val) {
    this.activeIndex = val;
}
```

**核心问题**：uView Plus 在 Vue3 中使用 **`modelValue`** 而不是 **`value`** 作为 prop 名称。

当前代码（错误）：
```html
<u-rate :value="Number(orderInfo.satisfaction)" ... />
```
`:value` 在 Vue3 中不会触发 `modelValue` 的 watcher，导致 `activeIndex` 始终为初始值（默认1或undefined），星星不渲染。

**修复方案**：改用 Vue3 标准的 `v-model` 或 `:modelValue`

### 问题2：操作模式订单项目缺少单价和总消耗
**现象**：当前操作模式下完全隐藏了所有价格信息

**用户需求**：
- 显示 **单价**（项目单价，即 consumeAmount/operationQuantity）
- 显示 **总消耗**（单价 × 次数 = consumeAmount）

**布局设计**：
```
1. 品项1                              1次
   单价    ¥398.00          总消耗    ¥398.00

2. 品项2                              1次
   单价    ¥398.00          总消耗    ¥398.00
```

---

## 修复方案

### 步骤1：修复满意度组件 - 改用 v-model

**文件**: [detail.vue:18-22](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue#L18-L22)

```html
<!-- 修复前（❌ Vue3 不识别 :value） -->
<u-rate :value="Number(orderInfo.satisfaction)" :count="5"
        :allowHalf="false" :disabled="true"
        active-color="#FFB800" inactive-color="#E5E6EB" :size="28"></u-rate>

<!-- 修复后（✅ 使用 v-model） -->
<u-rate v-model="satisfactionValue" :count="5"
        :readonly="true"
        active-color="#FFB800" inactive-color="#E5E6EB" :size="28"></u-rate>
```

同时在 script 中添加 computed：
```javascript
const satisfactionValue = computed(() => Number(orderInfo.value.satisfaction) || 0)
```

### 步骤2：修改订单项目 - 操作模式显示单价和总消耗

**文件**: [detail.vue:98-127](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue#L98-L127)

将当前完全隐藏的 `<template v-if="detailMode !== 'operation'">` 改为按模式显示不同内容：

```html
<view class="item-body">
  <!-- 开单模式：完整价格信息 -->
  <template v-if="detailMode !== 'operation'">
    <!-- 方案价 + 单价 -->
    <!-- 成交价 + 实付/欠款 -->
  </template>

  <!-- 操作模式：单价 + 总消耗 -->
  <template v-else>
    <view class="info-line">
      <view class="info-left">
        <text class="info-label">单价</text>
        <text class="info-value price">¥{{ getUnitPrice(item) }}</text>
      </view>
      <view class="info-right">
        <text class="info-label">总消耗</text>
        <text class="info-value amount">¥{{ item.planPrice || '0.00' }}</text>
      </view>
    </view>
  </template>
</view>
```

**数据映射**（已在 loadDetail 中定义）：
- `item.planPrice` = consumeAmount（每次操作的金额）= 总消耗
- `getUnitPrice(item)` = planPrice / quantity = 单价

---

## 实施步骤

| 序号 | 任务 | 文件 | 说明 |
|------|------|------|------|
| 1 | 修复 u-rate 组件 | detail.vue | 改用 v-model + readonly |
| 2 | 添加 satisfactionValue 计算属性 | detail.vue | 确保 number 类型 |
| 3 | 修改操作模式订单项目模板 | detail.vue | 添加单价+总消耗行 |
| 4 | 测试验证 | - | 确认效果 |

## 预期效果

| 页面区域 | 字段 | 修复前 | 修复后 |
|----------|------|--------|--------|
| 基础信息-满意度 | 星级评分 | 无显示 | ⭐⭐⭐⭐⭐ |
| 订单项目-操作模式 | 单价 | 隐藏 | ¥398.00 |
| 订单项目-操作模式 | 总消耗 | 隐藏 | ¥398.00 |

## 风险评估
- **风险等级**：低 🟢
- **影响范围**：仅 AppV3 操作详情页
- **回滚方案**：恢复原始代码即可
