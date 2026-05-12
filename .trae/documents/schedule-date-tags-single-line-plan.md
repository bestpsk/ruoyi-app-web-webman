# 行程日期标签单行显示优化计划

## 需求分析

**当前问题：**
- 日期标签使用了 `flex-wrap: wrap`，会换行显示
- 用户希望只显示一行，不换行，超出部分省略

**当前样式：**
```scss
.date-tags-row {
  display: flex;
  flex-wrap: wrap;  // 允许换行
  gap: 12rpx;
  margin-top: 16rpx;
}
```

---

## 优化方案

### 方案：强制单行 + 溢出隐藏

**修改样式：**
```scss
.date-tags-row {
  display: flex;
  flex-wrap: nowrap;     // 禁止换行
  gap: 12rpx;
  margin-top: 16rpx;
  overflow: hidden;      // 隐藏超出部分
}

.date-tag {
  padding: 6rpx 16rpx;
  background: #F7F8FA;
  border-radius: 6rpx;
  font-size: 22rpx;
  color: #4E5969;
  flex-shrink: 0;        // 防止标签被压缩

  &.more {
    background: #E8F0FE;
    color: #3D6DF7;
  }
}
```

### 效果说明
- 标签强制在一行显示
- 超出容器宽度的标签会被隐藏
- "+N" 标签会显示在最后（如果有的话）

---

## 验证方法
1. Vite 热更新自动生效
2. 刷新行程列表页面
3. 验证日期标签只显示一行
4. 验证超出部分被隐藏
