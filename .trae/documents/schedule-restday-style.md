# 行程安排日历图休息日样式优化计划

## 当前状态

上一轮已实现 `loadRestDateMap()` 数据加载，休息日已在日历中**以斜纹条纹背景显示**（如截图2所示，部分日期有浅色斜纹）。

## 用户需求

1. **颜色更明显** — 当前斜纹太淡（opacity:0.7 + 浅灰），不够醒目
2. **显示"休息"文字** — 休息日单元格上要看到"休息"二字

## 改动文件

仅修改：`front/src/views/business/schedule/index.vue`

### 改动1：template - 休息日单元格添加"休息"文字

在 `day-cell` 内部添加条件渲染的"休息"标签：

```html
<div v-for="day in daysInMonth"
     :key="'d-' + rowIndex + '-' + day"
     :class="['day-cell', { selected: isSelectedDay(rowIndex, day), 'rest-day': isRestDayForUser(row.userId, day) }]"
     :data-day="day"
     @mouseenter="handleCellEnter(rowIndex, day)"
     @click.stop="handleCellClick(row, day)">
  <span v-if="isRestDayForUser(row.userId, day)" class="rest-label">休息</span>
</div>
```

### 改动2：style - 增强 .rest-day 和新增 .rest-label 样式

```css
/* 休息日单元格 - 更明显的配色 */
.day-cell.rest-day {
  background: linear-gradient(135deg, #fff0f0 0%, #ffe8e8 100%);
  border-color: #fbc4c4;
  cursor: not-allowed;
  position: relative;
}

/* "休息"文字标签 */
.rest-label {
  display: inline-block;
  font-size: 11px;
  color: #F56C6C;
  font-weight: 600;
  background: rgba(245, 108, 108, 0.08);
  border: 1px solid rgba(245, 108, 108, 0.25);
  border-radius: 3px;
  padding: 1px 5px;
  line-height: 16px;
}
```

## 视觉效果预览

```
┌─────┬─────┬─────┬─────┬─────┐
│ 1日 │ 2日 │ 3日 │ 4日 │ 5日 │
│     │     │[休息]│     │     │  ← 粉红底色 + 红色边框
│     │     │     │     │     │    + 居中"休息"文字
└─────┴─────┴─────┴─────┴─────┘
```

## 实施步骤

1. template 中每个 `day-cell` 内部添加 `<span class="rest-label">休息</span>` 条件渲染
2. style 中修改 `.rest-day` 为粉红色渐变背景 + 红色边框
3. style 中新增 `.rest-label` 样式
