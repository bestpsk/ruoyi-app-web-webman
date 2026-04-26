# 图标库统一使用 uView Plus 修复计划

## 问题描述

将项目中所有图标统一使用 uView Plus 的 u-icon 组件。

## 当前图标使用情况

### 1. main.js
- 当前未引入 uview-plus
- 需要添加 uview-plus 引入

### 2. login.vue
使用 uni-icons：
- `person` → 用户图标
- `locked` → 密码图标
- `image` → 验证码图标

### 3. register.vue
使用 uni-icons：
- `person` → 用户图标
- `locked` → 密码图标
- `image` → 验证码图标

### 4. mine/index.vue
使用 text 字母作为图标：
- `C` → 在线客服
- `F` → 反馈社区
- `L` → 点赞我们
- `A` → 关于我们
- `E` → 编辑资料
- `H` → 常见问题
- `I` → 关于我们
- `S` → 应用设置

### 5. mine/setting/index.vue
使用 uni-icons：
- `locked` → 修改密码
- `loop` → 检查更新
- `trash` → 清理缓存

## 图标映射对照表

| 原 uni-icons | uView Plus u-icon |
|-------------|-------------------|
| person | account |
| locked | lock |
| image | photo |
| loop | reload |
| trash | trash |

| 原 text 字母 | 功能 | uView Plus u-icon |
|------------|------|-------------------|
| C | 在线客服 | chat |
| F | 反馈社区 | edit-pen |
| L | 点赞我们 | thumb-up |
| A | 关于我们 | info-circle |
| E | 编辑资料 | edit-pen |
| H | 常见问题 | question-circle |
| I | 关于我们 | info-circle |
| S | 应用设置 | setting |

## 修复步骤

### 步骤1：修改 main.js
引入 uview-plus：
```js
import uviewPlus from 'uview-plus'
// ...
app.use(uviewPlus)
```

### 步骤2：修改 login.vue
将 uni-icons 改为 u-icon：
- `<uni-icons type="person" size="20" color="#999" />` → `<u-icon name="account" size="20" color="#999" />`
- `<uni-icons type="locked" size="20" color="#999" />` → `<u-icon name="lock" size="20" color="#999" />`
- `<uni-icons type="image" size="20" color="#999" />` → `<u-icon name="photo" size="20" color="#999" />`

### 步骤3：修改 register.vue
同 login.vue 的修改

### 步骤4：修改 mine/index.vue
将 text 字母图标改为 u-icon，并调整样式

### 步骤5：修改 mine/setting/index.vue
将 uni-icons 改为 u-icon

## 预期结果

修复后，所有页面图标统一使用 uView Plus 的 u-icon 组件，视觉效果更统一美观。
