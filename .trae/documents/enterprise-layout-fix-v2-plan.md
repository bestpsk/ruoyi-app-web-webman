# 企业管理页面布局修复计划 v2

## 问题分析（基于截图对比）

### 截图1（修改前）问题：
- 搜索框右侧有**白色间隙/边框**露出
- 筛选按钮与搜索框之间有视觉断层
- 页面整体偏左，右侧空间不足

### 截图2（修改后）问题：
- 搜索框变宽了，但**右侧仍然有白边**
- 企业卡片右侧的"编辑""删除"按钮**被截断或紧贴边缘**
- 整体布局右侧间距依然不够

## 根本原因

1. **搜索框白边**：`.search-section` 的 `padding: 20rpx 16rpx` 太小，而 `.search-box` 使用 `width: 100%` 导致溢出或与边缘对齐不正确
2. **右侧内容遮盖**：页面没有统一的左右 padding，各区域 padding 不一致

## 修复方案

### 方案：统一容器 padding + 搜索框自适应

#### 修改1: 统一页面容器左右边距

```scss
.enterprise-container {
  min-height: 100vh;
  padding: 0 24rpx;          // 统一左右24rpx间距
  padding-bottom: 120rpx;
}
```

#### 修改2: 搜索区域使用标准padding

```scss
.search-section {
  padding: 20rpx 0;           // 左右由父容器控制，不再单独设置
  background: linear-gradient(180deg, #3D6DF7 0%, #4A7AEF 100%);
}

.search-box {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 36rpx;
  margin: 0 24rpx;            // 与父容器padding配合，实际左右24rpx
  padding: 0 8rpx 0 28rpx;   // 内部左侧稍大，给图标留空间
  height: 72rpx;
  gap: 12rpx;                // 减小gap让输入区更大
  box-sizing: border-box;
}

.filter-btn {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4rpx;
  height: 56rpx;              // 固定高度比search-box小一点
  padding: 0 22rpx;
  background: #E8F0FE;
  border-radius: 28rpx;

  text {
    font-size: 26rpx;
    color: #3D6DF7;
    font-weight: 500;
    white-space: nowrap;
  }
}
```

#### 修改3: 列表区域去掉多余padding

```scss
.list-scroll {
  padding: 20rpx 0;           // 由父容器控制左右间距
}
```

#### 修改4: 已筛选标签区域

```scss
.active-filters {
  padding: 12rpx 24rpx 16rpx;
  background: linear-gradient(180deg, #4A7AEF 0%, #F5F7FA 100%);
}
```

## 预期效果

```
┌──────────────────────────────┐ ← 容器 padding: 0 24rpx
│ [蓝色渐变背景]               │
│ ┌──────────────────────────┐ │
│ │🔍 输入框...        筛选 ▼│ │ ← search-box margin: 0 24rpx
│ └──────────────────────────┘ │
├──────────────────────────────┤
│                              │
│ ┌──────────────────────────┐ │
│ │ 🏢 企业名称      正常    │ │
│ │ ...            [编辑][删除]│ │ ← 右侧有足够空间显示
│ └──────────────────────────┘ │
│                              │
│                    [+新增]   │
└──────────────────────────────┘
```

## 关键改动点总结

| 元素 | 当前值 | 修改后 |
|------|--------|--------|
| `.enterprise-container` | `padding-bottom: 120rpx` | `padding: 0 24rpx 120rpx` |
| `.search-section` | `padding: 20rpx 16rpx` | `padding: 20rpx 0` |
| `.search-box` | 无margin | `margin: 0 24rpx` |
| `.list-scroll` | `padding: 20rpx 28rpx 20rpx 24rpx` | `padding: 20rpx 0` |
