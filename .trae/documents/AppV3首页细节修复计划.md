# AppV3 首页细节修复计划

## 一、问题诊断（基于用户截图和浏览器元素检查）

### 问题1：HeaderNav 和 QuickMenu 之间有缝隙
**现象：** 从截图可以看到，蓝色渐变导航栏底部和白色菜单卡片顶部之间有一条明显的灰色缝隙。

**原因分析：**
- HeaderNav 底部有圆角 `border-radius: 0 0 32rpx 32rpx`
- QuickMenu 使用 `margin: -30rpx 24rpx 0` 向上偏移试图覆盖 HeaderNav 底部
- 但 `-30rpx` 的负 margin 不够大，没有完全覆盖住圆角下方的区域
- 加上 HeaderNav 底部 padding 是 `40rpx`，圆角从内容区边缘开始，实际覆盖需要更大的负 margin

**修复方案：**
- 将 QuickMenu 的 `margin-top` 从 `-30rpx` 改为 **`-60rpx`**，确保完全覆盖 HeaderNav 的圆角过渡区域
- 同时给 QuickMenu 增加 `position: relative; z-index: 10`，确保层级在 HeaderNav 之上

### 问题2："更多"按钮样式不协调
**现象：** 当前"更多"是纯文字+小箭头，和前面4个圆形图标+文字的菜单项风格不一致。

**用户要求：** "更多"也以导航图标的样式显示（即圆形图标背景 + 文字）。

**修复方案：**
- 将"更多"改为和菜单项一样的结构：圆形浅蓝背景图标 + "更多"文字
- 图标使用 `apps` 或 `grid`（九宫格/更多图标）
- 移除左侧垂直分割线

### 问题3：次数格式错误
**现象：** 当前显示为 `余3次/5`（缺少最后的"次"字）。

**修复方案：**
- 将 `余{{ item.remainCount }}次/{{ item.totalCount }}` 改为 `余{{ item.remainCount }}次/{{ item.totalCount }}次`

### 问题4：状态标签（已完成/进行中）没有上下居中
**现象：** 从截图可以看到，"已完成"绿色标签和"王雪"名字不在同一水平线上，标签偏上或偏下。

**原因分析：**
- `customer-name` 使用了 `display: flex; align-items: center`，但 `u-tag` 组件可能有默认的 margin 或 padding 导致不对齐
- 需要给标签增加 `vertical-align: middle` 或调整对齐方式

**修复方案：**
- 给 `u-tag` 增加 `style="margin-left: 10rpx; vertical-align: middle; align-self: center;"`
- 或者将标签和名字放在同一 flex 容器中并设置 `align-items: center`

---

## 二、具体修改文件

### 1. QuickMenu.vue
- margin-top: -30rpx → **-60rpx**
- 增加 `position: relative; z-index: 10`
- "更多"改为图标+文字样式（使用 `apps` 图标）
- 移除左侧分割线

### 2. OrderList.vue
- 次数格式：`余{{ item.remainCount }}次/{{ item.totalCount }}` → `余{{ item.remainCount }}次/{{ item.totalCount }}次`
- 标签对齐：给 `u-tag` 增加对齐样式

---

## 三、实施步骤

### 步骤1：修复 QuickMenu.vue
1. 调整 margin-top 为 -60rpx
2. 增加 position 和 z-index
3. 将"更多"改为图标+文字样式

### 步骤2：修复 OrderList.vue
1. 修正次数显示格式
2. 修复状态标签对齐

---

## 四、预期效果

1. 菜单卡片完全覆盖 HeaderNav 底部圆角，无缝衔接
2. "更多"按钮和前面4个菜单项风格一致（圆形图标+文字）
3. 次数显示为 `余3次/5次`
4. 状态标签和顾客名字在同一水平线上，视觉对齐
