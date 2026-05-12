# 定位"一直加载中"深度诊断与修复计划

## 当前状态分析

从截图看，页面仍然卡在"**loading 定位中...**"，说明 `locationLoading.value = true` 被设置后**从未被重置为 false**。

虽然已添加了 8 秒超时 + 浏览器原生 Geolocation 降级，但降级逻辑**没有生效**。

---

## 可能原因排查（按可能性排序）

### 原因1：开发服务器未重启（最可能 ⭐⭐⭐⭐⭐）
- `manifest.json` 的修改需要**重启 Vite 开发服务器**才能生效
- 如果只做了热更新（HMR），manifest.json 的变更不会被识别
- **验证方法**：检查终端是否还在运行旧的 dev server 进程，需要 Ctrl+C 停止后重新 `npm run dev:h5`

### 原因2：`#ifdef H5` 条件编译在当前运行环境不生效（⭐⭐⭐⭐）
- 如果运行的是微信小程序开发者工具模拟器（而非浏览器），则 `// #ifdef H5` 代码块会被编译器**剔除**
- 此时只有 `// #ifdef MP-WEIXIN` 的代码会执行，但小程序的 `appid` 为空字符串，可能导致异常
- **验证方法**：确认是在浏览器中访问（如 `http://localhost:9091`）

### 原因3：`sdkKey.securityJsCode` 字段名不被 uni-app 识别（⭐⭐⭐）
- uni-app 官方文档中 `h5.sdkKey` 支持的字段可能是有限的
- `securityJsCode` 可能不是 uni-app 支持的标准字段名
- **解决方案**：改用全局方式配置高德安全密钥

### 原因4：JavaScript 执行错误导致后续代码中断（⭐⭐⭐）
- 某处代码抛出异常，导致 `doGetLocation()` 或 `fallbackGetLocation()` 根本没执行到
- **验证方法**：打开浏览器 F12 控制台查看是否有红色报错信息

---

## 修复方案

### 方案：移除条件编译依赖 + 确保降级一定执行

核心思路：**不依赖 `#ifdef` 条件编译**，让所有平台的定位逻辑都统一走同一路径，确保降级方案一定能触发。

#### 改造后的 `getLocation()` 逻辑：

```javascript
function getLocation() {
  locationLoading.value = true
  locationError.value = false
  
  // 统一调用，不再用 #ifdef 区分平台
  doGetLocation()
}

function doGetLocation() {
  // 8秒超时保护
  clearTimeout(locationTimer)
  locationTimer = setTimeout(() => {
    console.warn('定位超时，使用降级方案')
    locationLoading.value = false  // 先停止 loading
    fallbackGetLocation()
  }, 8000)

  uni.getLocation({
    type: 'gcj02',
    success: (res) => {
      clearTimeout(locationTimer)
      location.value.latitude = res.latitude
      location.value.longitude = res.longitude
      location.value.address = res.address || `${res.latitude.toFixed(6)}, ${res.longitude.toFixed(6)}`
      locationLoading.value = false
    },
    fail: (err) => {
      clearTimeout(locationTimer)
      console.warn('uni.getLocation 失败:', err?.errMsg)
      locationLoading.value = false  // 先停止 loading
      fallbackGetLocation()
    }
  })
}
```

关键改动：
1. **移除 `#ifdef MP-WEIXIN` / `#ifdef H5` 条件编译** — 所有平台统一走 `doGetLocation()`
2. **在调用 `fallbackGetLocation()` 前，先设置 `locationLoading = false`** — 确保UI不会卡在加载状态
3. **保留 8 秒超时** — 即使 uni.getLocation 静默挂起，8秒后也会自动切换到降级方案

---

## 文件变更

| 文件 | 修改内容 |
|------|----------|
| `AppV3/src/pages/attendance/index.vue` | 重写 `getLocation()` 和 `doGetLocation()`，移除条件编译，确保 loading 一定会被重置 |

## 操作步骤

1. **先重启开发服务器**（Ctrl+C → `npm run dev:h5`）
2. 如果仍不行 → 应用上述代码修改
3. 打开浏览器 F12 控制台观察日志输出
