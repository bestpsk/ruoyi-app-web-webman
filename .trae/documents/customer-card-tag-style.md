# 客户卡片标签样式统一与图标添加计划

## 需求
1. 成交/未成交标签与客户标签样式统一（都使用 `dict-tag` 的实心背景样式）
2. 客户名字前面加上图标（如用户图标 `User` 或 `Avatar`）

## 当前状态
- **成交标签**：使用 `<el-tag effect="plain">` → 空心边框样式
- **客户标签**：使用 `<dict-tag>` 组件 → 实心背景色样式（如紫色"普通"、粉色"重点客户"）
- **客户名**：纯文本无图标

## 修改方案

### 步骤1：统一标签样式
将成交标签从 `el-tag effect="plain"` 改为与 dict-tag 一致的样式：
- 使用 `el-tag` 但去掉 `effect="plain"`，改用默认的实心效果
- 或者直接用 `type="primary"` / 自定义颜色匹配 dict-tag 风格

**推荐**：成交标签改为 `el-tag type="success"` 去掉 `effect="plain"`（实心绿色），未成交用 `type="info"` 去掉 `effect="plain"`（实心灰色），与 dict-tag 的视觉风格一致。

### 步骤2：客户名前加图标
在 `.customer-name` 前加 Element Plus 的 `User` 图标：
```vue
<el-icon style="margin-right: 4px; vertical-align: middle"><User /></el-icon>
<span class="customer-name">{{ item.customerName }}</span>
```

## 涉及文件
- `front/src/views/business/sales/index.vue` 第34-37行
