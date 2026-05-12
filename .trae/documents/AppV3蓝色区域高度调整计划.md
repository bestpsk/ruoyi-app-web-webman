# AppV3 蓝色区域高度调整计划

## 问题根因

**为什么改了 `padding-bottom: 60rpx` 没效果？**

当前结构：`.header-section`（蓝色渐变背景）内部同时包含了：
- `header-nav`（头像+问候语）
- `quick-menu-wrapper`（菜单卡片）

由于 QuickMenu 在 header-section **内部**，整个 header-section 区域都是蓝色背景的。`padding-bottom` 只是控制蓝色区域在 QuickMenu **下方**延伸多少，但 QuickMenu 本身始终被蓝色覆盖。

## 解决方案

**将 QuickMenu 从 header-section 中移出，改用负 margin 方式叠加。**

### 新结构
```
header-section (蓝色渐变，仅包含导航栏)
├── header-nav (头像+问候语)

QuickMenu (独立白色卡片，负margin向上偏移)
├── margin-top: -50rpx (让上半部分叠加在蓝色区域上)
```

### 效果
- 蓝色区域 = 仅导航栏高度（约 200rpx）
- 菜单卡片上半部分（约 86rpx = 图标中心位置）叠加在蓝色区域上
- 菜单卡片下半部分露出白色

---

## 修改文件

### 1. HeaderNav.vue — 移除 QuickMenu
- 删除 `<view class="quick-menu-wrapper">` 及其内部的 `<QuickMenu />`
- 删除 import QuickMenu
- `padding-bottom` 改为默认值或移除（不再需要）

### 2. index.vue — 重新引入 QuickMenu + 负 margin
- 重新添加 `<QuickMenu />`
- 给 QuickMenu 添加 class 或直接用 style 设置 `margin-top: -50rpx`
- NoticeBar 保持 `margin-top: 24rpx`

### 3. QuickMenu.vue — 增加 z-index
- 确保 QuickMenu 层级在 HeaderNav 之上
