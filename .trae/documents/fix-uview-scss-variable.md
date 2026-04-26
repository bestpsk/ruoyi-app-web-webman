# uView Plus SCSS 变量未定义错误修复计划

## 问题描述

```
[plugin:vite:css] [sass] Undefined variable.
    ╷
 40 │     border-color: $u-border-color!important;
    │                   ^^^^^^^^^^^^^^^
    ╵
   node_modules\uview-plus\libs\css\common.scss 40:16
```

## 问题根因

**SCSS 导入顺序错误！**

`index.scss` 的内部导入顺序是：
```scss
// index.scss 第2行 - 先导入 common.scss（使用了 $u-border-color）
@import "./libs/css/common.scss";
// ...
// 第5行 - 才导入 color.scss（但这里只是使用变量）
@import "./libs/css/color.scss";
```

而 `$u-border-color` 变量定义在 **`theme.scss`** 中。

当前配置：
- `uni.scss`: 引入 `theme.scss` ✓
- `App.vue`: 直接引入 `index.scss` ✗

**问题**：`App.vue` 中直接 `@import 'uview-plus/index.scss'` 时，`common.scss` 先于 `theme.scss` 被加载，导致变量未定义。

## 解决方案

修改 `App.vue` 中的导入顺序，确保 `theme.scss` 在 `index.scss` **之前**加载：

```scss
<style lang="scss">
  @use '@/static/scss/common.scss';
  @import 'uview-plus/theme.scss';     // 先加载变量定义
  @import 'uview-plus/index.scss';      // 再加载组件样式
</style>
```

## 修复步骤

### 步骤1：修改 App.vue 样式导入顺序

将 `@import 'uview-plus/theme.scss'` 移到 `@import 'uview-plus/index.scss'` **之前**

### 步骤2：验证修复

重新运行项目，确认编译错误消失，图标正常显示

## 预期结果

修复后：
1. SCSS 编译错误消失
2. uView Plus 图标正常显示
3. 所有页面样式正常
