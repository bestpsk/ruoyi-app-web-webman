# 行程列表显示优化计划

## 需求分析

### 当前问题
1. **重复显示**：同一个行程（同一员工+企业+目的）有3天会显示3个卡片
2. **布局不合理**：日期显示在头部，企业单独一行

### 目标效果
1. **合并显示**：同一行程只显示一个卡片
2. **头部显示员工姓名**（原日期位置）
3. **企业 + 目的并排**：企业在左，目的在右
4. **日期以Tag形式显示**：在底部一行显示多个日期标签
5. **标签省略**：超出3个标签显示"+N"，点击进入查看详情

---

## 实施方案

### 步骤1：添加数据分组函数

在 `<script setup>` 中添加分组逻辑：

```javascript
function groupScheduleList(list) {
  const groupMap = new Map()

  list.forEach(item => {
    const key = `${item.userId}_${item.enterpriseId}_${item.purpose}_${item.status}`

    if (!groupMap.has(key)) {
      groupMap.set(key, {
        ...item,
        scheduleIds: [item.scheduleId],
        scheduleDates: [item.scheduleDate]
      })
    } else {
      const group = groupMap.get(key)
      group.scheduleIds.push(item.scheduleId)
      group.scheduleDates.push(item.scheduleDate)
    }
  })

  return Array.from(groupMap.values())
    .map(group => ({
      ...group,
      scheduleDates: group.scheduleDates.sort()
    }))
    .sort((a, b) => new Date(a.scheduleDates[0]) - new Date(b.scheduleDates[0]))
}
```

### 步骤2：修改 getList 函数

在获取数据后调用分组函数：

```javascript
async function getList(isRefresh = false) {
  // ... 原有逻辑 ...

  const response = await listSchedule(params)
  const data = response.data || response
  const list = data.rows || []

  // 分组处理
  const grouped = groupScheduleList(list)

  scheduleList.value = isRefresh ? grouped : [...scheduleList.value, ...grouped]

  // ... 原有逻辑 ...
}
```

### 步骤3：重构卡片模板

**修改前：**
```vue
<view class="card-header">
  <view class="date-badge">{{ formatDay(item.scheduleDate) }}</view>
  <view class="status-tag">{{ getStatusName(item.status) }}</view>
</view>
<view class="card-body">
  <view class="info-row">
    <view class="info-item">
      <text class="label">员工</text>
      <text class="value">{{ item.userName }}</text>
    </view>
    <view class="info-item">
      <text class="label">目的</text>
      <text class="value">{{ getPurposeName(item.purpose) }}</text>
    </view>
  </view>
  <view class="info-row">
    <view class="info-item full">
      <text class="label">企业</text>
      <text class="value">{{ item.enterpriseName }}</text>
    </view>
  </view>
</view>
<view class="card-footer">
  <view class="time-text">{{ item.scheduleDate }}</view>
  ...
</view>
```

**修改后：**
```vue
<view class="card-header">
  <view class="user-badge">{{ item.userName || '-' }}</view>
  <view class="status-tag" :class="'status-' + item.status">{{ getStatusName(item.status) }}</view>
</view>
<view class="card-body">
  <view class="info-row">
    <view class="info-item">
      <text class="label">企业</text>
      <text class="value">{{ item.enterpriseName || '-' }}</text>
    </view>
    <view class="info-item">
      <text class="label">目的</text>
      <text class="value purpose-text">{{ getPurposeName(item.purpose) }}</text>
    </view>
  </view>
  <view class="date-tags-row">
    <view class="date-tag" v-for="(date, idx) in getDisplayDates(item.scheduleDates)" :key="idx">
      {{ formatDay(date) }}
    </view>
    <view class="date-tag more" v-if="item.scheduleDates.length > 3">
      +{{ item.scheduleDates.length - 3 }}
    </view>
  </view>
</view>
<view class="card-footer">
  <view class="time-text">共{{ item.scheduleDates.length }}天</view>
  ...
</view>
```

### 步骤4：添加辅助函数

```javascript
function getDisplayDates(dates) {
  return dates.slice(0, 3)
}
```

### 步骤5：更新样式

```scss
.user-badge {
  background: #E8F0FE;
  color: #3D6DF7;
  padding: 8rpx 20rpx;
  border-radius: 8rpx;
  font-size: 28rpx;
  font-weight: 600;
}

.date-tags-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12rpx;
  margin-top: 16rpx;
}

.date-tag {
  padding: 6rpx 16rpx;
  background: #F7F8FA;
  border-radius: 6rpx;
  font-size: 22rpx;
  color: #4E5969;

  &.more {
    background: #E8F0FE;
    color: #3D6DF7;
  }
}
```

### 步骤6：修改点击详情逻辑

```javascript
function goDetail(item) {
  uni.navigateTo({
    url: `/pages/business/schedule/form?id=${item.scheduleIds[0]}&mode=view`
  })
}

function goEdit(item) {
  uni.navigateTo({
    url: `/pages/business/schedule/form?id=${item.scheduleIds[0]}&mode=edit`
  })
}

function handleDelete(item) {
  uni.showModal({
    title: '提示',
    content: `是否确认删除该行程（共${item.scheduleIds.length}天）?`,
    success: async (res) => {
      if (res.confirm) {
        try {
          await delSchedule(item.scheduleIds.join(','))
          uni.showToast({ title: '删除成功', icon: 'success' })
          getList(true)
        } catch (e) {
          console.error('删除失败:', e)
        }
      }
    }
  })
}
```

---

## 效果对比

| 项目 | 修改前 | 修改后 |
|-----|--------|--------|
| 显示数量 | 3天显示3个卡片 | 3天显示1个卡片 |
| 头部显示 | 日期（05-02） | 员工姓名 |
| 企业/目的 | 上下两行 | 左右并排 |
| 日期显示 | 底部一行文字 | Tag标签形式 |
| 标签省略 | 无 | 超过3个显示+N |

---

## 验证方法
1. Vite 热更新自动生效
2. 手机访问 http://192.168.31.126:9091/
3. 进入 **行程安排** → 验证同一行程合并显示
4. 验证日期标签显示正确
5. 点击卡片进入详情验证功能正常
