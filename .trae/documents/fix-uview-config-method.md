# uview-plus 配置方式错误修复计划

## 问题描述

```
TypeError: Cannot set properties of undefined (setting 'iconUrl')
```

## 问题根因

`uviewPlus` 导出的默认对象只有 `install` 方法，没有 `config` 属性：

```js
// uview-plus/index.js
export default {
    install
}
```

当前错误代码：
```js
uviewPlus.config.iconUrl = '/static/uview-plus/uicon-iconfont.ttf'  // ❌ config 不存在
```

## 正确的配置方式

根据 uview-plus 源码，有两种正确方式：

### 方式1：使用 setConfig 函数（推荐）
```js
import uviewPlus, { setConfig } from 'uview-plus'

setConfig({
  config: {
    iconUrl: '/static/uview-plus/uicon-iconfont.ttf'
  }
})
```

### 方式2：直接导入 config 对象
```js
import uviewPlus from 'uview-plus'
import config from 'uview-plus/libs/config/config.js'

config.iconUrl = '/static/uview-plus/uicon-iconfont.ttf'
```

## 修复步骤

修改 `d:\fuchenpro\AppV3\src\main.js`，使用 `setConfig` 函数配置 iconUrl。
