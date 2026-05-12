# 行程安排日历图显示休息日计划

## 问题分析

当前代码已有休息日显示的基础设施，但**未实际生效**：

| 已有组件 | 状态 | 说明 |
|---------|------|------|
| `isRestDayForUser(userId, day)` | ✅ 存在（L803-808） | 检查 `restDateMap[userId]` |
| `.rest-day` CSS样式 | ✅ 存在（L952-962） | 斜纹条纹背景 + opacity |
| 模板绑定 | ✅ 存在（L63） | `'rest-day': isRestDayForUser(row.userId, day)` |
| `restDateMap` 数据填充 | ❌ **缺失** | 初始化为空对象 `{}`，从未被赋值 |

**根本原因**：`getList()` 加载日历数据时，没有同时加载员工的休息日数据到 `restDateMap`。

## 数据流

```
员工配置表 biz_employee_config
  └─ rest_dates 字段（JSON数组，如 ["2026-05-03", "2026-05-10"]）
      └─ listEmployeeConfig API 返回 restDates 字段
          └─ 构建 restDateMap = { userId: ["2026-05-03", ...] }
              └─ isRestDayForUser() 匹配 → .rest-day 样式生效
```

## 实施方案

### 改动文件：`front/src/views/business/schedule/index.vue`

### 步骤1：在 getList() 中加载休息日数据

当 `activeTab === 'employee'` 时，加载完日程数据后，额外调用 `listEmployeeConfig` 获取所有员工的休息日：

```javascript
function getList() {
  // ... 现有逻辑 ...

  if (activeTab.value === 'employee') {
    getEmployeeSchedule(params).then(response => {
      scheduleData.value = response.data || []
      getScheduleList(params)
    })
    // 新增：加载员工休息日数据
    loadRestDateMap()
  }
}
```

### 步骤2：新增 loadRestDateMap 函数

```javascript
async function loadRestDateMap() {
  try {
    const res = await listEmployeeConfig({ pageNum: 1, pageSize: 1000 })
    const rows = res.rows || []
    const map = {}
    rows.forEach(row => {
      if (row.restDates?.length && row.userId) {
        map[row.userId] = row.restDates
      }
    })
    restDateMap.value = map
  } catch (e) {
    console.error('加载休息日数据失败:', e)
  }
}
```

### 步骤3：优化 - 仅显示当前月份的休息日

当前 `isRestDayForUser` 会匹配所有日期的休息日。可以保持不变（因为只影响当前月视图），或加一层过滤仅匹配当前年月的日期。

## 视觉效果

休息日的单元格将显示为：
- **斜纹条纹背景**（灰白相间 45°）
- **透明度降低**（opacity: 0.7）
- **鼠标变为禁用状态**（cursor: not-allowed）
- 与行程块不冲突（休息日标记在底层 `.day-cell` 上）

## 实施步骤

1. 在 `getList()` 的 `employee` 分支末尾添加 `loadRestDateMap()` 调用
2. 新增 `loadRestDateMap()` 异步函数
3. 验证效果：切换到"员工行程"Tab时，配置了休息日的日期应显示斜纹背景
