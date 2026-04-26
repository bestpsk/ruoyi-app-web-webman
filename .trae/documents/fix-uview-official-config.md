# 按官方方式修复 uview-plus 图标配置

## 问题描述

图标仍然不显示，之前的 `setConfig` 方式没有生效。

## 正确的官方方式（3.4.0+）

```js
// main.js
import uviewPlus from 'uview-plus'

export function createApp() {
  const app = createSSRApp(App)
  const pinia = createPinia()
  app.use(pinia)
  app.use(uviewPlus, () => {
    return {
      options: {
        config: {
          iconUrl: '/static/uview-plus/uicon-iconfont.ttf'
        }
      }
    }
  })
  return { app }
}
```

**关键点：**
1. 配置作为第二个参数传递给 `app.use(uviewPlus, callback)`
2. callback 返回 `{ options: { config: {...} } }` 格式

## 修复步骤

修改 `d:\fuchenpro\AppV3\src\main.js`，移除 `setConfig` 调用，改为在 `app.use(uviewPlus)` 中传入配置。
