# 修复连续行程编辑只更新第一条记录的问题

## 问题分析

### 现象
选择一个连续的行程（如5-8日）修改下店目的时，只有第一个格子（被点击的）被修改了，导致原本连续的同类型行程变成了两个不同类型的行程块。

### 根因分析

**问题代码位置：** [index.vue:647-655](file:///d:/fuchenpro/front/src/views/business/schedule/index.vue#L647-L655)

```javascript
function handleEdit() {
  detailOpen.value = false
  reset()
  getSchedule(currentSchedule.value.scheduleId).then(response => {
    form.value = { 
      ...response.data, 
      userIds: [response.data.userId], 
      startDate: response.data.scheduleDate,  // ❌ 只设置了单天日期
      endDate: response.data.scheduleDate,    // ❌ 只设置了单天日期
      status: String(response.data.status) 
    }
    open.value = true
    title.value = "修改行程"
  })
}
```

**问题原因：**
1. `getSchedule` 只获取了**单条记录**（被点击的那一天）
2. `startDate` 和 `endDate` 都设置为同一天
3. 提交时因为 `scheduleId` 存在，走的是 `updateSchedule` **只更新一条记录**
4. 其他日期的行程未被更新，导致出现两个不同的行程块

**数据流程：**
```
用户点击5-8日的行程块 (purpose=1)
    ↓
handleScheduleClick → currentSchedule = {scheduleId: 27, date: '2026-04-05', ...}
    ↓
handleEdit → getSchedule(27) → 只获取5号那天的数据
    ↓
form = {startDate: '2026-04-05', endDate: '2026-04-05', ...}
    ↓
updateSchedule → 只更新 scheduleId=27 的记录
    ↓
结果：5号变成新目的，6-8号还是旧目的 → 分裂成两个行程块
```

## 修复方案

### 方案：修改前端编辑逻辑

**核心思路：** 编辑时找到同一用户+企业+目的的所有连续行程记录，批量更新。

### 修改文件
`d:\fuchenpro\front\src\views\business\schedule\index.vue`

### 修改内容

#### 1. 修改 handleEdit 函数
```javascript
function handleEdit() {
  detailOpen.value = false
  reset()
  
  const schedule = currentSchedule.value
  
  // 从 scheduleListData 中找出同一用户+企业+目的的所有记录
  const relatedSchedules = scheduleListData.value.filter(item =>
    item.userId === schedule.userId &&
    item.enterpriseId === schedule.enterpriseId &&
    item.purpose === schedule.purpose
  )
  
  // 按日期排序，获取日期范围
  const dates = relatedSchedules.map(item => item.scheduleDate).sort()
  
  form.value = {
    ...schedule,
    userIds: [schedule.userId],
    startDate: dates[0],
    endDate: dates[dates.length - 1],
    status: String(schedule.status),
    // 保存所有需要更新的ID
    updateIds: relatedSchedules.map(item => item.scheduleId)
  }
  open.value = true
  title.value = "修改行程"
}
```

#### 2. 修改 submitForm 函数
当存在多个 ID 时，使用批量删除+新增的方式更新：
```javascript
if (form.value.updateIds?.length > 1) {
  // 批量更新：先删除旧记录，再插入新记录
  delSchedule(form.value.updateIds.join(',')).then(() => {
    return addScheduleBatch(scheduleList)
  }).then(() => { ... })
} else if (form.value.scheduleId != undefined) {
  // 单条更新
  updateSchedule(updateData).then(() => { ... })
}
```

## 验证步骤

1. 刷新页面
2. 在日历上找到一个连续的行程块（多天的）
3. 点击该行程块，打开详情
4. 点击"编辑"，修改下店目的或状态
5. 确认保存后，整个行程块都被更新为新的目的

## 影响范围

- 仅影响行程编辑功能
- 不影响新增、删除功能
- 修改量适中
