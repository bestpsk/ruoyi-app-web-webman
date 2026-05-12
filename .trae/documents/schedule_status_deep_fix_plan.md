# 行程安排 - 状态显示过多问题深度诊断与修复

## 问题现象
排班列表的"状态"列显示多个"已预约"标签堆叠在一起，修改分组key后仍未解决。

## 深度分析

### 已完成的修改
✅ 已将 `status` 加入分组 key：`${item.userId}_${item.enterpriseId}_${item.purpose}_${item.status}`

### 可能的根本原因

#### 原因1：数据库存在大量重复数据
用户可能多次执行了"新增排班"操作，每次操作都会向 `biz_schedule` 表插入多条记录（每个日期一条）。
- 例如：用户为同一员工+企业+目的添加了4次排班
- 数据库中就会有4组重复记录
- 虽然分组逻辑正确合并了它们，但如果数据量过大可能导致显示异常

#### 原因2：status字段类型不一致
部分记录的 `status` 可能是：
- 数字类型：`1`
- 字符串类型：`"1"`
- 这会导致分组 key 不同，无法正确合并

#### 原因3：浏览器缓存
前端代码已修改，但浏览器仍在使用缓存的旧JS文件。

---

## 解决方案

### 步骤1：强制刷新浏览器清除缓存
- 按 `Ctrl + Shift + R` 或 `Ctrl + F5` 强制刷新
- 或打开开发者工具 → Network → 勾选 "Disable cache" 后刷新

### 步骤2：检查后端返回的原始数据
在浏览器控制台查看API返回的数据，确认是否有重复记录。

### 步骤3：优化 processScheduleListGroup 函数
增加数据去重和类型标准化处理：

```javascript
function processScheduleListGroup() {
  if (!scheduleListData.value.length) { processedScheduleList.value = []; return }
  
  // 先按 scheduleDate 去重
  const uniqueList = []
  const dateMap = new Map()
  scheduleListData.value.forEach(item => {
    const dedupeKey = `${item.userId}_${item.enterpriseId}_${item.scheduleDate}_${item.purpose}`
    if (!dateMap.has(dedupeKey)) {
      dateMap.set(dedupeKey, item)
      uniqueList.push(item)
    }
  })
  
  const grouped = {}
  uniqueList.forEach(item => {
    // 标准化status为字符串
    const status = String(item.status || '1')
    const key = `${item.userId}_${item.enterpriseId}_${item.purpose}_${status}`
    
    if (!grouped[key]) {
      grouped[key] = {
        userId: item.userId,
        userName: item.userName,
        postName: item.postName || '',
        enterpriseId: item.enterpriseId,
        enterpriseName: item.enterpriseName,
        purpose: item.purpose,
        status: status,
        dates: []
      }
    }
    // dates也去重
    if (!grouped[key].dates.includes(item.scheduleDate)) {
      grouped[key].dates.push(item.scheduleDate)
    }
  })
  
  processedScheduleList.value = Object.values(grouped)
}
```

---

## 修改文件
| 文件 | 修改内容 |
|------|----------|
| `front/src/views/business/schedule/index.vue` | 增强 `processScheduleListGroup()` 函数 |

## 验证步骤
1. 强制刷新浏览器
2. 打开行程安排页面
3. 检查排班列表的状态列是否只显示单个标签
