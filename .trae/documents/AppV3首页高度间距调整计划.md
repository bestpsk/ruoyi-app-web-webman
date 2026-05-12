# AppV3 首页高度与间距调整计划

## 需求
1. **header-section（蓝色区域）高度减少到当前的一半** — 当前 `padding-bottom: 120rpx` 覆盖了整个菜单卡片，需要减半到约 **60rpx**（蓝色背景只覆盖到菜单图标中心位置）
2. **quick-menu 和 notice-card 之间保持适度间距**

## 修改文件
仅修改 2 个文件：

### 1. HeaderNav.vue — 减少蓝色区域高度
```diff
- padding-bottom: 120rpx;
+ padding-bottom: 60rpx;
```

### 2. NoticeBar.vue — 增加顶部间距
```diff
- margin: 16rpx 24rpx 0;
+ margin: 24rpx 24rpx 0;
```
