# 销售开单页面Card宽度和输入框样式修复计划

## 问题描述
销售开单页面（选择企业→选择门店→选择客户→开单）存在以下UI问题：
1. **Card宽度超出屏幕**：品项列表的item-card等卡片组件宽度太宽，导致水平方向溢出屏幕
2. **输入框宽窄不一**：品项表单中的各输入框宽度不一致，视觉上显得杂乱

## 问题根因分析

### 问题1：Card宽度超出屏幕
- [order.vue:483](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L483)：`.tab-content` 设置了 `padding: 0 14rpx`
- [order.vue:496](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L496)：`.item-card` 没有显式设置 `width: 100%` 和 `box-sizing: border-box`
- [order.vue:486](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L486)：`.package-name-section` 同样缺少宽度约束
- [order.vue:511](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L511)：`.summary-section` 也存在相同问题
- 导致卡片内容（含padding和border）实际渲染宽度超过容器可用宽度

### 问题2：输入框宽窄不一
- [order.vue:506](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L506)：`.form-label` 设置了 `min-width: 90rpx`，但未设置固定宽度
- 标签文字长度不同："名称"(2字)、"次数"(2字)、"成交金额"(4字)、"实付金额"(4字)、"欠款金额"(4字)
- 由于标签宽度不固定，导致右侧输入框的实际宽度各不相同

## 修复方案

### 修改文件
- `d:\fuchenpro\AppV3\src\pages\business\sales\order.vue` (样式部分)

### 具体修改步骤

#### 步骤1：修复所有卡片的宽度溢出问题
为以下CSS类添加 `width: 100%; box-sizing: border-box;`：
- `.package-name-section` (第486行)
- `.item-card` (第496行)
- `.summary-section` (第511行)
- `.remark-section` (第522行)

#### 步骤2：统一表单标签宽度
修改 `.form-label` 样式（第506行）：
- 将 `min-width: 90rpx` 改为固定 `width: 120rpx`（或根据最长标签"成交金额/实付金额/欠款金额"自适应）
- 确保所有标签宽度一致

#### 步骤3：验证修复效果
- 检查移动端屏幕下的显示效果
- 确认所有卡片不超出屏幕边界
- 确认所有输入框宽度一致且对齐

## 预期效果
✅ 所有卡片（套餐名称、品项列表、费用合计、备注）宽度适应屏幕，不溢出
✅ 品项表单中所有输入框宽度一致，左右对齐整齐
✅ 视觉上更加规范美观
