# 品项表单布局优化计划

## 问题

当前品项卡片内使用了 `form-grid` 双列布局（次数/成交金额并排、实付/欠款并排），导致视觉上不整齐、拥挤。

## 目标

改为**每行一个字段**的标准表单布局：
```
┌─────────────────────────────────────┐
│ 🖊 品项 1                      [删除]│
├─────────────────────────────────────┤
│ 名称              [________________] │
│ 次数              [____]             │
│ 成交金额          [____]             │
│ 单次价            ¥0.00        自动   │
│ 实付金额          [____]             │
│ 欠款金额          ¥0.00              │
└─────────────────────────────────────┘
```

## 改动内容

### template 改动

将 `form-grid`（双列）改回为 `form-row`（单列），每个字段独占一行：

```html
<!-- 删除 form-grid 结构 -->
<view class="form-row">
  <text class="form-label">名称</text>
  <input class="form-input" ... />
</view>
<view class="form-row">
  <text class="form-label">次数</text>
  <input class="form-input" ... />
</view>
<view class="form-row">
  <text class="form-label">成交金额</text>
  <input class="form-input" ... />
</view>
<view class="form-row readonly">
  <text class="form-label">单次价</text>
  <text class="form-value">...</text>
</view>
<view class="form-row">
  <text class="form-label">实付金额</text>
  <input class="form-input" ... />
</view>
<view class="form-row readonly">
  <text class="form-label">欠款金额</text>
  <text class="form-value owed">...</text>
</view>
```

### style 调整

- 移除 `.form-grid` / `.form-col` / `.form-input-sm` / `.form-label-sm` / `.owed-display` 样式
- 保持 `.form-row` 单行布局：左边标签固定宽度 + 右边输入框 flex:1 填满
- 行高统一 `56rpx`，间距 `8rpx`
- 标签宽度统一 `90rpx`

## 实施步骤

1. 替换 template 中品项卡片的 form-grid 为 form-row 单列结构
2. 清理不再使用的 CSS 类（form-grid 相关）
3. 验证无诊断错误
