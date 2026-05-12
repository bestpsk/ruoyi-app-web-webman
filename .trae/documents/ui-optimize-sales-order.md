# 客户开单页面 UI 优化计划

## 问题分析（基于截图）

| 问题 | 当前状态 | 优化目标 |
|------|---------|---------|
| 页面宽度过宽 | 品项表单行内容溢出屏幕 | 缩小标签宽度、间距和内边距 |
| 输入框过高 | `height: 72rpx` | 缩小到 `60rpx` |
| 页面不满屏 | scrollHeight 计算减去过多 | 动态计算精确高度 |
| 缺少图标装饰 | 仅文字+简单图标 | 添加 u-icon 装饰图标 |
| 整体风格不够扁平 | 圆角12rpx + box-shadow | 减小圆角、去掉阴影 |

---

## 优化方案

### 一、布局与尺寸调整

**1.1 页面容器**
- `.order-container`: 改为 `100vh` 占满屏幕
- `.tab-content padding`: 从 `24rpx` → `16rpx`
- `.tab-panel padding`: 从 `20rpx 0` → `16rpx 0`

**1.2 客户信息区**
- 紧凑化：`padding: 20rpx 24rpx` → `16rpx 24rpx`
- 添加头像/图标装饰（u-icon）
- 字号微调：客户名 `32rpx` → `30rpx`

**1.3 套餐名称区**
- `padding: 24rpx` → `18rpx 20rpx`
- 输入框高度: `72rpx` → `60rpx`
- 标签字号: `28rpx` → `26rpx`

**1.4 品项卡片（核心改动）**

表单行改为紧凑双列布局：
```
┌─────────────────────────────────────┐
│ 📦 品项 1                    [删除] │
├──────┬──────────────┬──────────────┤
│ 名称 │ [___________]│              │
├──────┼──────────────┼──────────────┤
│ 次数 │ [____]       │ 成交金额 [__]│
├──────┼──────────────┼──────────────┤
│ 单次价 ¥XXX.XX (自动)              │
├──────┼──────────────┼──────────────┤
│ 实付 │ [____]       │ 欠款   ¥XXX  │
└──────┴──────────────┴──────────────┘
```

关键参数变更：
- 卡片 `padding`: `24rpx` → `18rpx`
- 卡片 `border-radius`: `12rpx` → `10rpx`
- 表单行 `min-height`: `72rpx` → `56rpx`
- 输入框 `height`: `72rpx` → `56rpx`
- 标签 `min-width`: `120rpx` → `90rpx`
- 标签 `font-size`: `26rpx` → `24rpx`
- 行间距 `gap`: `12rpx` → `8rpx`

**1.5 合计区域**
- 紧凑化：`padding: 24rpx` → `16rpx 20rpx`
- 合计值字号: `32rpx` → `28rpx`

**1.6 提交按钮**
- 固定在底部悬浮（fixed bottom）

### 二、图标装饰

| 位置 | 图标 | 说明 |
|------|------|------|
| 套餐名称标签前 | `shopping-bag` / `gift` | 装饰 |
| 品项列表标题前 | `list` / `grid` | 装饰 |
| 品项序号旁 | `edit-pen` / `file-text` | 装饰 |
| 删除按钮 | 已有 `trash` | 保持 |
| 提交按钮 | 已有 | 保持 |
| 备注标签前 | `chat-dot` | 装饰 |
| 合计区域前 | `wallet` / `red-packet` | 装饰 |
| 客户信息旁 | `account` | 装饰 |

### 三、扁平化风格调整

| 属性 | 修改前 | 修改后 |
|------|--------|--------|
| 卡片 border-radius | `12rpx` | `8rpx ~ 10rpx` |
| 卡片 box-shadow | `0 2rpx 8rpx rgba(0,0,0,0.04)` | 移除，改用 `#F2F3F5` 浅边框 |
| 输入框背景色 | `#F5F7FA` | `#F7F8FA` 更浅 |
| 分割线颜色 | `#F2F3F5` | 保持 |
| 主色调 | `#3D6DF7` | 保持 |
| 强调色 | `#FF6B35` | 保持 |

### 四、满屏高度计算

```javascript
function calcScrollHeight() {
  const systemInfo = uni.getSystemInfoSync()
  // 导航栏 + tabs + 客户信息 + 底部安全区
  const navBarHeight = 44 * (systemInfo.windowWidth / 375)
  const tabBarHeight = 44
  const customerInfoHeight = 80
  const safeBottom = systemInfo.safeAreaInsets?.bottom || 0
  scrollHeight.value = systemInfo.windowHeight - navBarHeight - tabBarHeight - customerInfoHeight - safeBottom - 40
}
```

### 五、具体样式改动清单

#### 全局
```
.order-container: min-height: 100vh → height: 100vh; display: flex; flex-direction: column;
.tab-content: flex: 1; overflow-y: auto; padding: 0 16rpx;
.tab-panel: padding: 14rpx 0;
```

#### 客户信息
```
.customer-info: padding 16rpx 24rpx; 添加 u-icon account 图标
```

#### 套餐名称
```
.package-name-section: padding 18rpx 20rpx; border-radius 10rpx; 无阴影
.package-name-input: height 60rpx; font-size 27rpx
.section-label: font-size 26rpx; margin-bottom 8rpx
```

#### 品项列表
```
.items-section: margin-bottom 14rpx
.add-item-btn: padding 8rpx 16rpx
.item-card: padding 18rpx; border-radius 10rpx; border: 1rpx solid #EDEEF2; 无阴影
.item-card-header: margin-bottom 12rpx; padding-bottom 10rpx
.item-form: gap 8rpx
.form-row: min-height 56rpx; gap 12rpx
.form-row.readonly: padding 0 16rpx
.form-label: font-size 24rpx; min-width 90rpx
.form-input: height 56rpx; font-size 26rpx; padding 0 16rpx
.form-value: font-size 26rpx
```

#### 合计区域
```
.summary-section: padding 16rpx 20rpx; border-radius 10rpx; 无阴影
.summary-row: padding 10rpx 0
.summary-value: font-size 28rpx
```

#### 备注 & 提交
```
.remark-section: padding 18rpx 20rpx; border-radius 10rpx; 无阴影
.submit-bar: position fixed; bottom: 0; left: 0; right: 0; padding 16rpx 32rpx; background: #fff; z-index: 10
```

---

## 实施步骤

1. 修改 `<template>` 部分：添加图标装饰元素
2. 修改 `<style>` 部分：全面调整尺寸、间距、扁平化处理
3. 修改 `calcScrollHeight()` 函数：优化满屏高度计算
4. 验证效果
