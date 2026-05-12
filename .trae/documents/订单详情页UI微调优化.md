# 订单详情页UI微调优化方案

## 需求清单

根据用户反馈，需要优化以下内容：

### 1️⃣ 去除分隔线 ⭐⭐
- **金额行**: 删除上方虚线 (`border-top: 1rpx dashed`)
- **汇总行**: 删除上方实线 (`border-top: 1rpx solid`)

### 2️⃣ 紧凑行间距 ⭐⭐
- 减小各处 margin、padding、gap 值
- 让整体布局更紧凑

### 3️⃣ 标签文字修改 ⭐⭐⭐ (新增)
| 当前标签 | 修改后 |
|---------|--------|
| 品项 | **品项名** |
| 成交 | **成交价** |
| 单次价 | **单价** |

---

## 具体实施步骤

### 步骤1：修改标签文字（模板层）

**文件**: `d:\fuchenpro\AppV3\src\pages\business\order\detail.vue`
**位置**: 第77-115行（item-body区域）

**修改点**:

```vue
<!-- 第1行：品项 → 品项名 -->
<view class="info-line">
  <view class="info-left">
    <text class="info-label">品项名</text>  <!-- 修改 -->
    <text class="info-value">...</text>
  </view>
  <view class="info-right">
    <text class="info-label">次数</text>
    ...
  </view>
</view>

<!-- 第2行：单次价 → 单价 -->
<view class="info-line">
  <view class="info-left">
    <text class="info-label">方案价</text>
    ...
  </view>
  <view class="info-right">
    <text class="info-label">单价</text>  <!-- 修改 -->
    ...
  </view>
</view>

<!-- 第3行：成交 → 成交价 -->
<view class="info-line summary-line">
  <view class="info-left">
    <text class="info-label">成交价</text>  <!-- 修改 -->
    ...
  </view>
  <view class="info-right">
    ...
  </view>
</view>
```

---

### 步骤2：去除分隔线 + 紧凑间距（样式层）

#### 2.1 修改金额行样式
**位置**: 第263-267行

```scss
// 修改前
&.amount-row {
  margin-top: 8rpx;
  padding-top: 16rpx;
  border-top: 1rpx dashed #E5E6EB;  // ❌ 删除虚线
}

// 修改后
&.amount-row {
  margin-top: 4rpx;       // ✅ 减小间距
  padding-top: 8rpx;     // ✅ 减半
}
```

#### 2.2 修改汇总行样式
**位置**: 第437-441行

```scss
// 修改前
&.summary-line {
  margin-top: 8rpx;
  padding-top: 12rpx;
  border-top: 1rpx solid #F2F3F5;  // ❌ 删除实线
}

// 修改后
&.summary-line {
  margin-top: 4rpx;       // ✅ 减小间距
  padding-top: 6rpx;     // ✅ 减半
}
```

#### 2.3 紧凑整体行间距
**位置**: 第248-252行

```scss
// 修改前
.info-body {
  gap: 16rpx;           // ❌ 偏大
}

// 修改后
.info-body {
  gap: 10rpx;           // ✅ 更紧凑
}
```

---

## 视觉效果对比

### 标签文字变化

```
修改前:
品项: 额电饭锅电饭锅          次数: 10次
方案价: ¥3980.00            单次价: ¥398.00
成交: ¥3980.00              实付: ¥3980.00

修改后:
品项名: 额电饭锅电饭锅        次数: 10次     ← 更明确
方案价: ¥3980.00              单价: ¥398.00  ← 更简洁
成交价: ¥3980.00              实付: ¥3980.00 ← 更清晰
```

### 分隔线和间距变化

```
修改前:
○ 金额   ¥3980.00
──────────────────────  ← 虚线分隔，间距大
○ 时间   2026-05-08

品项名: ...          次数: 10次      ← 行间距16rpx
方案价: ...          单价: ¥398
──────────────────────  ← 实线分隔，间距大
成交价: ...          实付: ...

修改后:
○ 金额   ¥3980.00
○ 时间   2026-05-08               ← 无虚线，紧凑

品项名: ...          次数: 10次      ← 行间距10rpx
方案价: ...          单价: ¥398
成交价: ...          实付: ...        ← 无实线，紧贴
```

---

## 涉及文件

| 文件 | 操作 | 修改范围 |
|------|------|---------|
| `detail.vue` | 修改模板+CSS | 3处标签 + 3处样式 |

---

## 预计工时

- 标签文字修改: 2分钟
- CSS样式调整: 3分钟
- 测试验证: 5分钟
- **总计**: 约10分钟

---

## 总结

✅ **标签优化**: 品项→品项名, 成交→成交价, 单次价→单价  
✅ **去除装饰**: 删除2条分隔线（虚线+实线）  
✅ **紧凑布局**: 整体间距减少约40%  
✅ **保持清晰**: 通过字重和颜色维持层级
