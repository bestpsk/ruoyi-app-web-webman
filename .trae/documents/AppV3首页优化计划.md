# AppV3 首页精细化设计优化计划

## 一、项目背景

目标项目：`d:\fuchenpro\AppV3`（Vue3 + Vite + uview-plus 的 uni-app 项目）

当前首页由 4 个组件构成：
- `HeaderNav.vue` — 顶部导航栏
- `QuickMenu.vue` — 常用菜单区
- `StatisticsCard.vue` — 数据统计区
- `OrderList.vue` — 订单明细列表

参考图片（图1：蓝色清新风格参考，图2：当前页面截图），优化首页设计。

---

## 二、问题诊断（基于浏览器检查元素）

### 问题1：菜单图标尺寸和颜色不对
- 实际渲染：`font-size: 28px; color: rgb(60, 150, 243)` — 使用了**旧颜色 #3C96F3**
- 图标尺寸 28px 在 96rpx 的 icon-wrapper 中偏大，视觉不协调
- 应为：size=20（约20px），color=#3D6DF7（新主色）

### 问题2：数据统计区设计单调
- 当前用 Tab 切换今日/本月，只能看一组数据
- 用户要求：**同时展示两组数据**，今日数据醒目（蓝色大字），本月数据在其下方以浅灰色小字显示
- 参考图1的设计感：数据层次分明，信息密度高但不拥挤

### 问题3：订单列表格式需调整
- 当前显示：phone（手机号）+ points（积分）
- 要求：
  - 电话改为 **XX美容（XX店）** 格式
  - 积分改为 **余3次/5次** 格式

### 问题4：缺少通知消息滚动区域
- 当前首页没有通知/公告展示区域
- 需要新增一个可滚动的通知消息卡片

### 问题5：菜单区缺少"更多"入口
- 当前只有4个固定菜单项
- 需要右侧增加"更多"入口（点击跳转所有菜单页，先不开发具体页面）

---

## 三、优化方案

### 模块1：常用菜单区（QuickMenu.vue）

**改动点：**
| 属性 | 改前 | 改后 |
|------|------|------|
| u-icon size | 28 | **20** |
| u-icon color | #3c96f3（旧色） | **#3D6DF7**（新主色） |
| icon-wrapper 尺寸 | 96rpx | **88rpx** |
| icon-wrapper 与文字间距 | gap: 16rpx | **gap: 12rpx** |
| menu-text 字号 | 26rpx | **24rpx** |
| 布局 | 4列等宽 | **4列+右侧更多入口** |

**新增"更多"入口：**
- 右侧垂直分割线 + "更多"文字 + 右箭头图标
- 点击提示"更多菜单功能开发中"

### 模块2：通知消息滚动区域（新增组件 NoticeBar.vue）

**设计：**
- 白色卡片背景，圆角 20rpx
- 左侧蓝色喇叭图标（圆形背景）
- 中间使用 swiper 垂直自动轮播通知内容
- 右侧右箭头可查看更多
- 消息格式：`[类型] 内容`

**数据：**
```js
noticeList: [
  { type: '通知', content: '您有一条新的订单待处理，请及时查看' },
  { type: '公告', content: '系统将于今晚22:00进行维护升级' },
  { type: '提醒', content: '今日有3位顾客预约，请提前做好准备' }
]
```

### 模块3：数据概览区（StatisticsCard.vue 重构）

**新布局设计：**

```
┌─────────────────────────────────────────────┐
│  数据概览                              🔄   │
│  ────────────────────────────────────────  │
│                                             │
│  咨询客数    成交客数    成交金额    成交项次 │
│                                             │
│   128 ↗      36 ↗     ¥12.8k ↗    89 ↘   │  ← 今日（蓝色40rpx粗体 + 趋势箭头）
│ 本月 1,256   本月 386  本月 ¥128.5k 本月 892 │  ← 本月（灰色23rpx常规字）
│                                             │
└─────────────────────────────────────────────┘
```

**样式规范：**
| 元素 | 今日数据 | 本月数据 |
|------|----------|----------|
| 字号 | 40rpx | 23rpx |
| 字重 | 700 | 400 |
| 颜色 | #3D6DF7（蓝色） | #86909C（浅灰） |
| 趋势箭头 | 有（绿色↑/红色↓小圆点） | 无 |

**移除：** Tab 切换（今日/本月标签）、tab-indicator 指示器
**保留：** 刷新按钮、4列网格布局、分割线

**数据结构调整：**
```js
// 父组件传入合并数据
const combinedStats = [
  { label: '咨询客数', todayValue: '128', monthValue: '1,256', trend: 12 },
  { label: '成交客数', todayValue: '36', monthValue: '386', trend: 8 },
  { label: '成交金额', todayValue: '¥12.8k', monthValue: '¥128.5k', trend: 15 },
  { label: '成交项次', todayValue: '89', monthValue: '892', trend: -3 }
]
```

### 模块4：订单明细列表（OrderList.vue）

**改动：**
- `phone` 字段改为 `store` 字段，格式：`美丽佳人美容院(天河店)`
- `points` 字段改为 `remainCount` + `totalCount`，显示格式：`余3次/5次`

**数据结构调整：**
```js
orderList: [
  {
    id: 1,
    name: '王雪',
    store: '美丽佳人美容院(天河店)',
    avatar: '/static/images/profile.jpg',
    amount: '88',
    remainCount: 3,
    totalCount: 5,
    status: '已完成'
  }
]
```

### 模块5：顶部导航栏（HeaderNav.vue）微调

**改动：**
- 渐变背景色从 `#4a9ff5 → #5b8def` 调整为更明亮的 `#5B8FF9 → #3D6DF7`
- 底部增加圆角 `border-radius: 0 0 32rpx 32rpx`
- 问候语根据时间动态变化（早上好/下午好/晚上好）

### 模块6：整体细节打磨

| 优化项 | 说明 |
|--------|------|
| 卡片间距 | 统一 16rpx（原 24rpx），更紧凑 |
| 卡片内边距 | 统一 24rpx |
| 圆角 | 统一 20rpx（卡片）、16rpx（内部元素） |
| 阴影 | 更轻柔 `0 2rpx 12rpx rgba(61,109,247,0.06)` |
| 分割线颜色 | #E5E6EB |

---

## 四、uview-plus 图标映射

| 用途 | 图标名称 |
|------|----------|
| 打卡 | clock |
| 开单 | file-text |
| 行程 | calendar |
| 订单 | list |
| 消息 | bell |
| 设置 | setting |
| 刷新 | reload |
| 更多 | arrow-right |
| 上升 | arrow-up |
| 下降 | arrow-down |
| 通知 | volume |

---

## 五、文件修改清单

### 修改文件
| 文件 | 操作 | 说明 |
|------|------|------|
| `AppV3/src/pages/index.vue` | 修改 | 引入 NoticeBar 组件，传入合并后的统计数据 |
| `AppV3/src/components/home/HeaderNav.vue` | 修改 | 调整渐变背景色，增加底部圆角 |
| `AppV3/src/components/home/QuickMenu.vue` | 修改 | 修复图标尺寸/颜色，增加"更多"入口 |
| `AppV3/src/components/home/StatisticsCard.vue` | 重写 | 双行数据展示（今日+本月） |
| `AppV3/src/components/home/OrderList.vue` | 修改 | 电话→门店，积分→剩余次数 |

### 新增文件
| 文件 | 说明 |
|------|------|
| `AppV3/src/components/home/NoticeBar.vue` | 通知消息滚动组件 |

---

## 六、实施步骤

### 步骤1：修改 HeaderNav.vue
- 调整渐变背景色为 `#5B8FF9 → #3D6DF7`
- 增加底部圆角 `border-radius: 0 0 32rpx 32rpx`

### 步骤2：修改 QuickMenu.vue
- icon size 28→20
- icon color #3c96f3→#3D6DF7
- icon-wrapper 96rpx→88rpx
- 调整间距
- 增加"更多"入口

### 步骤3：新增 NoticeBar.vue
- 创建通知消息滚动组件
- 使用 swiper 垂直轮播

### 步骤4：重写 StatisticsCard.vue
- 移除 Tab 切换
- 每个 stat-item 内显示两行数据
- 今日数值（蓝色大字 + 趋势箭头）
- 本月数值（灰色小字）

### 步骤5：修改 OrderList.vue
- phone 字段改为 store 字段
- points 字段改为 remainCount/totalCount
- 显示格式改为 `余X次/Y`

### 步骤6：修改 index.vue
- 引入 NoticeBar 组件
- 调整统计数据结构传入 StatisticsCard

---

## 七、颜色体系

```scss
$primary-color: #3D6DF7;
$primary-light: #E8F0FE;
$gradient-start: #5B8FF9;
$gradient-end: #3D6DF7;

$bg-page: #F5F7FA;
$bg-white: #FFFFFF;

$text-primary: #1D2129;
$text-secondary: #4E5969;
$text-auxiliary: #86909C;
$text-light: #C9CDD4;

$success: #00B42A;
$info: #165DFF;
$danger: #F53F3F;

$divider: #E5E6EB;
$icon-bg: #E8F0FE;
```
