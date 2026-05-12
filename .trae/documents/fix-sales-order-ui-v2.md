# 销售开单页面UI优化计划（第二版）

## 问题描述
销售开单页面存在以下UI问题：
1. **卡片宽度太宽**：卡片几乎占满整个屏幕宽度，左右没有留白
2. **单次价和欠款金额样式与其他字段不一致**：这两个只读字段有灰色背景，而其他输入框没有背景色差异
3. **输入框宽窄不一**：虽然已设置标签宽度，但视觉效果仍不够整齐

## 问题根因分析

### 问题1：卡片缺少左右留白
- [order.vue:483](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L483)：`.tab-content` 的 `padding: 0 14rpx` 太小
- 导致内部卡片（package-name-section, item-card, summary-section, remark-section）几乎占满屏幕宽度
- 从截图对比可见，理想状态应该有约 20-24rpx 的左右边距

### 问题2：只读字段样式不统一
- [order.vue:63-66](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L63-L66)：`单次价` 使用 `.form-row.readonly` 类
- [order.vue:71-74](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L71-L74)：`欠款金额` 也使用 `.form-row.readonly` 类
- [order.vue:505](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L505)：`.form-row.readonly` 设置了 `background: #F7F8FA; padding: 0 14rpx`
- 而其他可编辑字段（名称、次数、成交金额、实付金额）使用普通 `.form-row`，无特殊背景
- 这导致视觉上"单次价"和"欠款金额"看起来像是在一个灰色的盒子里，与其他输入框风格不同

## 修复方案

### 修改文件
- `d:\fuchenpro\AppV3\src\pages\business\sales\order.vue`

### 具体修改步骤

#### 步骤1：增加卡片的左右留白（优化padding）
**修改位置**：第483行 `.tab-content`
```scss
/* 修改前 */
.tab-content { flex: 1; padding: 0 14rpx; }

/* 修改后 */
.tab-content { flex: 1; padding: 0 24rpx; }
```
**目的**：增加左右边距从14rpx到24rpx，让卡片与屏幕边缘有适度的留白空间

#### 步骤2：统一所有表单行的样式（移除readonly的特殊背景）
**修改位置**：第505行 `.form-row.readonly`
```scss
/* 修改前 */
.form-row.readonly { background: #F7F8FA; border-radius: 6rpx; padding: 0 14rpx; }

/* 修改后 */
.form-row.readonly { }
```
或者完全删除这个样式规则，让所有表单行保持一致的视觉效果

**目的**：移除"单次价"和"欠款金额"的灰色背景，使其与其他输入框在视觉上保持一致

#### 步骤3：优化表单整体的视觉一致性（可选增强）
如果步骤2移除背景后觉得太单调，可以考虑给所有表单行添加统一的轻量背景：
```scss
.form-row {
  display: flex;
  align-items: center;
  gap: 12rpx;
  min-height: 56rpx;
  background: #FAFBFC; /* 极浅的灰色背景 */
  border-radius: 6rpx;
  padding: 0 12rpx;
}
```
这样所有字段（包括可编辑和只读）都会有统一的视觉表现

#### 步骤4：验证效果
- 对比修改前后的截图
- 确认卡片左右有适度留白（约24rpx）
- 确认所有表单字段（名称、次数、成交金额、单次价、实付金额、欠款金额）视觉风格统一
- 确认在不同屏幕尺寸下的显示效果

## 预期效果对比

### 修改前的问题
❌ 卡片紧贴屏幕边缘，几乎无留白
❌ "单次价"和"欠款金额"有灰色背景框，与其他输入框视觉割裂
❌ 整体看起来杂乱不整齐

### 修改后的效果
✅ 卡片左右各有24rpx的优雅留白，呼吸感更好
✅ 所有6个表单字段视觉风格完全统一（或都有统一的轻量背景）
✅ 整体布局更加规范美观，符合移动端设计规范
✅ 用户视觉体验更舒适，操作更流畅

## 备选方案
如果用户希望保留只读字段的区分度，可以采用更 subtle 的方式：
- 只改变文字颜色（如只读字段用稍浅的颜色）
- 或只在底部加一条细线而非整块背景
- 或使用左边框/图标来标识只读状态
