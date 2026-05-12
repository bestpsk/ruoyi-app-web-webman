# 修复销售开单页面备注图标不显示问题

## 问题描述
销售开单页面的"备注"标题前面的 `chat-dot` 图标无法正常显示。

## 问题分析

### 当前代码
**位置**：[order.vue 第104行](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L104)

```html
<view class="section-title-row">
  <u-icon name="chat-dot" size="16" color="#86909C"></u-icon>  <!-- 图标不显示 -->
  <text class="section-label">备注</text>
</view>
```

### 可能的原因

1. **图标名称错误**：`chat-dot` 可能不是 uView Plus 支持的有效图标名称
2. **图标库版本差异**：不同版本的 uView 图标集可能不同
3. **图标未正确引入**：可能需要额外配置或使用其他方式引用

### 其他正常显示的图标对比
查看同一文件中其他正常工作的图标：
- `account-fill` ✅ 客户信息
- `map` ✅ 门店信息
- `gift-fill` ✅ 套餐名称
- `list` ✅ 品项列表
- `edit-pen` ✅ 品项编辑
- `trash` ✅ 删除按钮
- `info-circle` ✅ 单次价提示
- `red-packet-fill` ✅ 费用合计

这些图标都能正常显示，说明 u-icon 组件本身没问题，问题出在 `chat-dot` 这个特定的图标名称上。

## 解决方案

### 方案一：更换为正确的图标名称（推荐）✅

根据 uView Plus 的图标库，建议使用以下替代方案：

#### 选项A：使用 `chat` 或 `chat-fill`
```html
<u-icon name="chat" size="16" color="#86909C"></u-icon>
```
或
```html
<u-icon name="chat-fill" size="16" color="#86909C"></u-icon>
```

#### 选项B：使用 `edit-pen`（与备注/编辑相关）
```html
<u-icon name="edit-pen" size="16" color="#86909C"></u-icon>
```

#### 选项C：使用 `file-text` 或 `note`
```html
<u-icon name="file-text" size="16" color="#86909C"></u-icon>
```

### 方案二：使用 Unicode 字符或图片（备选）

如果 uView 中确实没有合适的聊天/备注图标：

```html
<!-- 使用文本符号 -->
<text style="margin-right: 8rpx; color: #86909C;">💬</text>

<!-- 或使用自定义图片 -->
<image src="/static/icons/chat.png" style="width: 32rpx; height: 32rpx;"></image>
```

## 推荐实施步骤

### 步骤1：修改图标名称
**位置**：[第104行](d:/fuchenpro/AppV3/src/pages/business/sales/order.vue#L104)

```html
<!-- 修改前 -->
<u-icon name="chat-dot" size="16" color="#86909C"></u-icon>

<!-- 修改后（推荐使用 chat 或 chat-fill）-->
<u-icon name="chat" size="16" color="#86909C"></u-icon>
```

### 步骤2：验证效果
- 确认图标能正常显示
- 确认颜色和大小合适
- 确保与其他区域的图标风格一致

## 预期效果

**修改前**：
```
□ 备注    ← 图标不显示，只有文字
```

**修改后**：
```
💬 备注   ← 图标正常显示
```

## 注意事项

1. **测试多个图标**：如果推荐的图标也不显示，可以尝试其他常见图标名
2. **保持一致性**：确保图标的颜色（#86909C 灰色）和大小（size=16）与其他 section 一致
3. **跨平台测试**：在不同平台（微信小程序、H5、App）下验证图标显示

## 常用 uView 图标参考

以下是一些可能适合"备注"场景的图标：
- `chat` - 聊天气泡
- `chat-fill` - 实心聊天气泡
- `edit-pen` - 编辑笔（已在其他地方使用）
- `file-text` - 文件文本
- `note` - 笔记
- `message` - 消息
- `comment` - 评论

建议优先尝试 `chat` 或 `chat-fill`，这两个最符合"备注"的语义。
