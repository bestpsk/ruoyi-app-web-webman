# 休息日标签文字居中修复计划

## 问题

截图3显示"休息"标签已生效，但**文字位置偏右上角**，没有在单元格内居中。

## 原因

`.day-cell` 默认没有设置 flex 布局，`.rest-label` 作为 `inline-block` 元素跟随文档流，未实现居中。

## 修改方案

仅修改 `front/src/views/business/schedule/index.vue` 的 style 部分：

### 修改 `.day-cell.rest-day` 样式

添加 flex 居中布局：

```css
.day-cell.rest-day {
  background: linear-gradient(135deg, #fff0f0 0%, #ffe8e8 100%);
  border-color: #fbc4c4;
  cursor: not-allowed;
  display: flex;
  align-items: center;
  justify-content: center;
}
```

### 修改 `.rest-label` 样式

去掉 `display: inline-block`，改为 block/inline 都可（由父容器 flex 控制）：

```css
.rest-label {
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

## 效果

```
┌─────┐
│     │
│ 休息│  ← 完全居中
│     │
└─────┘
```
