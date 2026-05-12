# AppV3 满意度显示 + 字段顺序调整计划

## 问题描述

### 问题1：满意度星级仍然不显示
**现象**：已尝试 `:value`、`:disabled`、`v-model`、`:readonly` 等多种方式，uView Plus 的 u-rate 组件在当前环境下始终无法渲染星级

**根因推测**：uView Plus 的 u-rate 组件在 uni-app Vue3 环境下可能存在兼容性问题，`modelValue` prop 的 watcher 未正确触发 `activeIndex` 更新

**最终方案**：放弃使用 u-rate 组件，改用**自定义星级显示**（纯文本/图标方式），简单可靠

### 问题2：字段顺序调整
**当前顺序**：操作人 → 满意度 → 客户 → 门店 → 金额 → 时间 → 照片 → 反馈
**目标顺序**：客户 → 门店 → **操作人** → **满意度** → 金额 → 时间 → 照片 → 反馈

---

## 修复方案

### 步骤1：替换满意度组件 - 使用自定义星级

**文件**: [detail.vue:18-22](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue#L18-L22)

将 `<u-rate>` 替换为自定义星级显示：

```html
<!-- 方案：用图标循环替代 u-rate 组件 -->
<view v-if="detailMode === 'operation' && orderInfo.satisfaction != null && orderInfo.satisfaction !== ''" class="info-row">
  <u-icon name="star" size="20" color="#86909C" />
  <text class="label">满意度</text>
  <view class="star-list">
    <u-icon v-for="i in 5" :key="i"
      :name="i <= Number(orderInfo.satisfaction) ? 'star-fill' : 'star'"
      :size="28"
      :color="i <= Number(orderInfo.satisfaction) ? '#FFB800' : '#E5E6EB'">
    </u-icon>
  </view>
</view>
```

同时删除不再需要的 `satisfactionValue` computed 属性。

### 步骤2：调整字段顺序

**文件**: [detail.vue:11-76](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue#L11-L76)

重新排列 info-body 内的 view 顺序：

```
1. 客户      （原第3位 → 第1位）
2. 门店      （原第4位 → 第2位）
3. 操作人    （原第1位 → 第3位）
4. 满意度    （原第2位 → 第4位）
5. 金额      （保持）
6. 时间      （保持）
7. 照片      （保持）
8. 反馈      （保持）
```

---

## 实施步骤

| 序号 | 任务 | 文件 | 说明 |
|------|------|------|------|
| 1 | 替换 u-rate 为自定义星级 | detail.vue | 用 u-icon 循环渲染5颗星 |
| 2 | 删除 satisfactionValue computed | detail.vue | 不再需要 |
| 3 | 调整字段顺序 | detail.vue | 客户→门店→操作人→满意度 |

## 预期效果

```
基础信息区域（调整后）：

客户     客户1                          ← 第1
门店     逆龄奢·宜川店                   ← 第2
操作人   admin                           ← 第3
满意度   ⭐⭐⭐⭐⭐                       ← 第4（自定义星级）
金额     ¥796
时间     2026-05-10
照片     [图片]
反馈     惹我热污染
```

## 风险评估
- **风险等级**：极低 🟢
- **影响范围**：仅 AppV3 操作详情页模板
- **回滚方案**：恢复原始代码即可
