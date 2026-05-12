# 行程安排 - 状态显示过多问题修复计划

## 问题分析
从截图中看到，**排班列表**的"状态"列显示了多个"已预约"标签堆叠在一起。

### 根本原因
查看 [processScheduleListGroup()](file:///d:/fuchenpro/front/src/views/business/schedule/index.vue#L467-L482) 函数：

```javascript
const key = `${item.userId}_${item.enterpriseId}_${item.purpose}`
if (!grouped[key]) {
  grouped[key] = {
    ...
    status: String(item.status),  // 只取第一条记录的状态
    dates: []
  }
}
grouped[key].dates.push(item.scheduleDate)
```

**问题**：分组key只包含 `userId + enterpriseId + purpose`，没有包含 `status`。
- 当同一员工在同一企业有**多条不同状态的排班记录**时（如多次添加），它们会被合并到同一组
- 但每组只显示一个 `status` 字段
- 如果数据库中存在重复/冗余的排班数据，可能导致显示异常

### 可能的场景
1. 用户多次为同一员工+企业+目的添加排班，产生了多条记录
2. 每条记录状态可能不同，但被强制合并为一组
3. 前端显示时可能出现状态混乱

---

## 解决方案

### 方案：优化分组逻辑和状态显示

#### 修改1：将 status 加入分组 key
确保不同状态的排班不会被错误合并：

```javascript
// 修改前
const key = `${item.userId}_${item.enterpriseId}_${item.purpose}`

// 修改后
const key = `${item.userId}_${item.enterpriseId}_${item.purpose}_${item.status}`
```

#### 修改2：状态列显示优化
如果同组内存在多种状态，可以显示主要状态或全部状态。

---

## 修改文件
| 文件 | 修改内容 |
|------|----------|
| `front/src/views/business/schedule/index.vue` | 修改 `processScheduleListGroup()` 分组逻辑 |

## 具体修改位置
**文件**: `front/src/views/business/schedule/index.vue`
**函数**: `processScheduleListGroup()` (第467-482行)
**修改**: 将 `status` 加入分组 key
