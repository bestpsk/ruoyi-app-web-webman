# 行程日期标签显示数量优化计划

## 需求分析

**当前问题：** 日期标签只显示3个，用户希望尽量多显示

**当前代码：**
```javascript
function getDisplayDates(dates) {
  return dates.slice(0, 3)  // 只显示前3个
}
```

---

## 优化方案

### 方案说明
考虑到移动端屏幕宽度限制：
- 屏幕宽度：约 750rpx
- 每个日期标签：约 90-100rpx（含间距）
- 可显示数量：约 6-7 个标签

**建议显示数量：6个**

### 修改内容

**修改 getDisplayDates 函数：**
```javascript
function getDisplayDates(dates) {
  return dates.slice(0, 6)  // 显示前6个
}
```

---

## 验证方法
1. Vite 热更新自动生效
2. 刷新行程列表页面
3. 验证日期标签显示6个，超过6个显示"+N"
