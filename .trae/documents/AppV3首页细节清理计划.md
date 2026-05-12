# AppV3 首页细节清理计划

## 一、问题诊断

从截图可以看到：

1. **QuickMenu 组件重复显示** — 截图中菜单区域出现了两次（HeaderNav 内部 + index.vue 中的 QuickMenu），需要移除 index.vue 中的 QuickMenu
2. **HeaderNav 底部圆角和高度不协调** — 底部 `border-radius: 0 0 32rpx 32rpx` 使蓝色渐变区呈现一个圆弧形底部，但用户希望去掉圆角，同时底部边缘对齐到菜单图标的中心点位置（即菜单卡片的上半部分被蓝色背景覆盖）
3. **数据统计区的趋势箭头角标显得乱** — 每个数据项右侧的绿色/红色小圆圈（↑↓）视觉上过于杂乱，需要移除

---

## 二、修改方案

### 1. 移除 index.vue 中的 QuickMenu
- QuickMenu 已经嵌入到 HeaderNav 组件中
- 移除 `<QuickMenu />` 及其 import

### 2. 调整 HeaderNav 样式
- **去掉底部圆角**：移除 `border-radius: 0 0 32rpx 32rpx`
- **调整底部 padding**：从 `padding-bottom: 60rpx` 改为覆盖到菜单卡片的上半部分，让菜单图标看起来像是"嵌入"蓝色背景中
- 具体做法：将 `.header-section` 的 `padding-bottom` 设为约 `120rpx`（菜单卡片高度的一半），移除 border-radius

### 3. 移除 StatisticsCard 中的趋势箭头
- 移除 `.trend-icon` 相关 DOM 结构和样式
- `v-if="item.trend !== 0"` 判断逻辑一并移除

---

## 三、修改文件清单

| 文件 | 操作 | 说明 |
|------|------|------|
| `AppV3/src/pages/index.vue` | 修改 | 移除 QuickMenu 组件引用 |
| `AppV3/src/components/home/HeaderNav.vue` | 修改 | 去掉圆角，调整 padding-bottom |
| `AppV3/src/components/home/StatisticsCard.vue` | 修改 | 移除趋势箭头角标 |

---

## 四、具体改动

### index.vue
```diff
- import QuickMenu from '@/components/home/QuickMenu.vue'
- <QuickMenu />
```

### HeaderNav.vue
```diff
- border-radius: 0 0 32rpx 32rpx;
- padding-bottom: 60rpx;
+ padding-bottom: 120rpx;
```

### StatisticsCard.vue
```diff
- <view v-if="item.trend !== 0" :class="['trend-icon', item.trend > 0 ? 'up' : 'down']">
-   <u-icon :name="item.trend > 0 ? 'arrow-up' : 'arrow-down'" size="12" color="#FFFFFF" />
- </view>
- .trend-icon { ... }
```
