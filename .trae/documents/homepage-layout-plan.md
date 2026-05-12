# 首页页面布局规划

## 目标
根据设计图重新规划 `pages/index.vue` 首页布局，不修改 tabbar 配置。

## 设计图分析

### 页面结构（从上到下）

```
┌─────────────────────────────────────┐
│  [头像] 大涵 | 产品销售部  [扫码][设置] │  ← 顶部用户信息栏
├─────────────────────────────────────┤
│  ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐│
│  │考勤│ │会议│ │审批│ │工资│ │更多││  ← 功能菜单网格(5列)
│  └────┘ └────┘ └────┘ └────┘ └────┘│
├─────────────────────────────────────┤
│  🔵 最新消息：您有一条新的系统消息... > │  ← 消息通知条
├─────────────────────────────────────┤
│  进度管理                            │  ← 标题
│  ┌─────────────────────────────┐    │
│  │      ╭────╮                 │    │
│  │     ╱ 69% ╲   本月进度      │    │  ← 圆环进度图
│  │    ╰──────╯                │    │
│  │ ●目标销售额     ○当前销售额  │    │
│  │ ¥189,565,65    ¥171,565,35  │    │  ← 销售数据
│  └─────────────────────────────┘    │
├─────────────────────────────────────┤
│  ┌──────────┐  ┌──────────┐        │
│  │📋 规章制度│  │📅 计划管理│        │  ← 快捷入口卡片
│  │了解企业..│  │合理利用..│        │
│  └──────────┘  └──────────┘        │
├─────────────────────────────────────┤
│  [待处理信息]        [今日日程]      │  ← 标签切换
├─────────────────────────────────────┤
│  员工工资明细对              [紧急]  │  ← 列表项
├─────────────────────────────────────┤
│  🏠首页  📋项目  ➕  📄事件  👤我的  │  ← TabBar(不改)
└─────────────────────────────────────┘
```

## 实现步骤

### Step 1: 重写 index.vue 模板结构

修改 `d:\fuchenpro\AppV3\src\pages\index.vue`，按以下模块组织：

#### 1.1 顶部用户信息栏 (header)
- 左侧：圆形头像 + 用户名 + 部门名称（竖线分隔）
- 右侧：扫码图标(u-icon: scan) + 设置图标(u-icon: setting)
- 背景色：浅蓝渐变或白色
- 高度：约 120rpx

```vue
<view class="header">
  <view class="user-info">
    <image class="avatar" :src="userInfo.avatar" />
    <view class="user-text">
      <text class="username">{{ userInfo.nickName }}</text>
      <text class="dept">{{ userInfo.deptName }}</text>
    </view>
  </view>
  <view class="header-actions">
    <u-icon name="scan" size="24" color="#333" />
    <u-icon name="setting" size="24" color="#333" />
  </view>
</view>
```

#### 1.2 功能菜单网格 (menu-grid)
- 5 列等宽网格布局
- 每项：图标(圆角方形背景) + 文字
- 图标使用 u-view-plus 的 u-icon 或自定义颜色背景

| 菜单项 | 图标名称 | 背景色 |
|--------|----------|--------|
| 考勤 | calendar | #e8f5e9 绿 |
| 会议 | chat | #e3f2fd 蓝 |
| 审批 | account-fill | #fff3e0 橙 |
| 工资 | red-packet | #fce4ec 红 |
| 更多 | grid | #f3e5f5 紫 |

```vue
<view class="menu-grid">
  <view class="menu-item" v-for="item in menuList" :key="item.name">
    <view class="menu-icon" :style="{ background: item.color }">
      <u-icon :name="item.icon" size="24" color="#fff" />
    </view>
    <text class="menu-text">{{ item.label }}</text>
  </view>
</view>
```

#### 1.3 最新消息通知条 (notice-bar)
- 左侧蓝色圆点 + "最新消息："前缀
- 中间消息内容（单行省略）
- 右侧箭头 >
- 背景：白色，带轻微阴影

```vue
<view class="notice-bar">
  <u-icon name="bell" size="18" color="#3c96f3" />
  <text class="notice-text">最新消息：{{ latestMessage }}</text>
  <u-icon name="arrow-right" size="16" color="#999" />
</view>
```

#### 1.4 进度管理卡片 (progress-card)
- 标题："进度管理"
- 圆环进度图（使用 CSS 实现 or canvas）
- 进度百分比 + "本月进度"
- 两行数据：目标销售额 / 当前销售额

```vue
<view class="progress-card">
  <view class="card-title">进度管理</view>
  <view class="progress-content">
    <view class="progress-circle">
      <!-- SVG/CSS 圆环 -->
      <text class="progress-num">69%</text>
      <text class="progress-label">本月进度</text>
    </view>
    <view class="progress-data">
      <view class="data-item">
        <text class="data-label">● 目标销售额</text>
        <text class="data-value">¥189,565,65</text>
      </view>
      <view class="data-item">
        <text class="data-label">○ 当前销售额</text>
        <text class="data-value">¥171,565,35</text>
      </view>
    </view>
  </view>
</view>
```

#### 1.5 快捷入口卡片 (quick-entry)
- 两个并排卡片：规章制度 / 计划管理
- 每个卡片：左侧图标 + 右侧标题+描述

```vue
<view class="quick-entry">
  <view class="entry-card" v-for="item in quickEntries" :key="item.title">
    <view class="entry-icon" :style="{ background: item.bgColor }">
      <u-icon :name="item.icon" size="28" :color="item.iconColor" />
    </view>
    <view class="entry-info">
      <text class="entry-title">{{ item.title }}</text>
      <text class="entry-desc">{{ item.desc }}</text>
    </view>
  </view>
</view>
```

#### 1.6 标签切换区 (tab-switch)
- 两个标签：待处理信息 / 今日日程
- 选中状态高亮显示

```vue
<view class="tab-switch">
  <text 
    class="tab-item" 
    :class="{ active: activeTab === 'pending' }"
    @click="activeTab = 'pending'"
  >待处理信息</text>
  <text 
    class="tab-item" 
    :class="{ active: activeTab === 'schedule' }"
    @click="activeTab = 'schedule'"
  >今日日程</text>
</view>
```

#### 1.7 待办列表 (todo-list)
- 列表项：标题 + 紧急标签（可选）
- 点击跳转详情

```vue
<view class="todo-list">
  <view class="todo-item" v-for="todo in todoList" :key="todo.id">
    <text class="todo-title">{{ todo.title }}</text>
    <view class="tag-urgent" v-if="todo.urgent">紧急</view>
  </view>
</view>
```

### Step 2: 编写样式 (SCSS)

在 `<style lang="scss" scoped>` 中编写：

```scss
// 全局
page { background-color: #f5f6fa; }

// 头部
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20rpx 30rpx;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  // 或纯白背景
}

// 用户信息
.user-info { display: flex; align-items: center; }
.avatar { width: 80rpx; height: 80rpx; border-radius: 50%; }

// 菜单网格
.menu-grid {
  display: flex;
  background: #fff;
  border-radius: 16rpx;
  padding: 20rpx 10rpx;
  margin: 20rpx;
}
.menu-item { flex: 1; display: flex; flex-direction: column; align-items: center; }
.menu-icon { width: 90rpx; height: 90rpx; border-radius: 20rpx; display: flex; align-items: center; justify-content: center; }

// 进度卡片
.progress-card {
  background: #fff;
  margin: 20rpx;
  border-radius: 16rpx;
  padding: 30rpx;
}
.progress-circle {
  width: 240rpx; height: 240rpx;
  position: relative;
  // 使用 conic-gradient 实现圆环
}

// 快捷入口
.quick-entry {
  display: flex;
  gap: 20rpx;
  padding: 0 20rpx;
}
.entry-card {
  flex: 1;
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
  display: flex;
  align-items: center;
}

// 标签切换
.tab-switch {
  display: flex;
  background: #fff;
  margin: 20rpx;
  border-radius: 12rpx;
  padding: 6rpx;
}
.tab-item {
  flex: 1;
  text-align: center;
  padding: 16rpx 0;
  border-radius: 8rpx;
}

// 待办列表
.todo-list { padding: 0 20rpx; }
.todo-item {
  background: #fff;
  padding: 24rpx;
  border-radius: 12rpx;
  margin-bottom: 16rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.tag-urgent {
  background: #ff4757;
  color: #fff;
  font-size: 22rpx;
  padding: 4rpx 12rpx;
  border-radius: 6rpx;
}
```

### Step 3: 编写脚本逻辑

```vue
<script setup>
import { ref, computed } from 'vue'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()

// 用户信息
const userInfo = ref({
  nickName: '大涵',
  deptName: '产品销售部',
  avatar: '/static/logo.png'
})

// 功能菜单
const menuList = ref([
  { name: 'attendance', label: '考勤', icon: 'calendar', color: '#4caf50' },
  { name: 'meeting', label: '会议', icon: 'chat', color: '#2196f3' },
  { name: 'approval', label: '审批', icon: 'account-fill', color: '#ff9800' },
  { name: 'salary', label: '工资', icon: 'red-packet', color: '#e91e63' },
  { name: 'more', label: '更多', icon: 'grid', color: '#9c27b0' }
])

// 最新消息
const latestMessage = ref('您有一条新的系统消息...')

// 进度数据
const progressData = ref({
  percent: 69,
  targetAmount: 189565.65,
  currentAmount: 171565.35
})

// 快捷入口
const quickEntries = ref([
  { title: '规章制度', desc: '了解企业核心理念', icon: 'file-text', bgColor: '#e3f2fd', iconColor: '#1976d2' },
  { title: '计划管理', desc: '合理利用高效工作', icon: 'calendar', bgColor: '#e8f5e9', iconColor: '#388e3c' }
])

// 标签切换
const activeTab = ref('pending')

// 待办列表
const todoList = ref([
  { id: 1, title: '员工工资明细核对', urgent: true },
  { id: 2, title: '项目进度报告提交', urgent: false }
])
</script>
```

## 不修改的内容
- `pages.json` 中的 tabBar 配置保持不变
- 底部 TabBar（首页、工作台、我的）保持原样

## 文件修改清单
| 文件 | 操作 |
|------|------|
| `d:\fuchenpro\AppV3\src\pages\index.vue` | 重写模板、脚本、样式 |

## 技术要点
1. 使用 uView Plus 组件库的 `u-icon` 图标
2. 圆环进度使用 CSS `conic-gradient` 实现
3. 整体采用卡片式设计，圆角 16rpx
4. 主色调：蓝色系 (#3c96f3) + 渐变背景
5. 响应式布局使用 Flexbox
