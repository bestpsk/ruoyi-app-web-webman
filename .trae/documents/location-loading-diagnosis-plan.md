# 定位"一直加载中"问题诊断与修复

## 一、配置检查结果

从截图分析您的配置：

| 检查项 | 状态 | 说明 |
|--------|------|------|
| manifest.json `sdkKey.map` | ✅ 正确 | `fa588d6bc9fbc9dce1f0c379e40f9faa` |
| 高德控制台 Key | ✅ 匹配 | 与 manifest.json 一致 |
| 服务平台 | ✅ 正确 | 已选"Web端" |
| 安全密钥 | ⚠️ 未配置 | 高德控制台有安全密钥但未在代码中使用 |

## 二、根因分析

**最可能的原因**：高德地图 JS API 2.0 版本**强制要求配置安全密钥**（securityJsCode），否则 SDK 会静默失败，不触发 success 也不触发 fail 回调。

当前 `manifest.json` 只配置了 `sdkKey.map`（即 Key），但**没有配置安全密钥**。这导致：
1. 高德 SDK 加载后校验安全密钥 → 校验失败
2. `uni.getLocation` 内部调用高德定位API → 静默挂起
3. success/fail 都不触发 → 永远停在"定位中"

## 三、修复方案

### 方案A：在 manifest.json 中添加安全密钥（推荐）

uni-app H5 端支持通过 `sdkKey.securityJsCode` 配置高德安全密钥：

```json
"h5": {
  "sdkKey": {
    "map": "fa588d6bc9fbc9dce1f0c379e40f9faa",
    "securityJsCode": "19ef226bdd6e4a6276d45ed1e5cb9a475"
  }
}
```

### 方案B：添加超时+降级兜底（双重保险）

即使配置正确，网络波动也可能导致SDK加载慢或失败。需要给 `doGetLocation()` 加上超时保护 + 浏览器原生 Geolocation 降级方案：

1. `doGetLocation()` 启动 8 秒定时器
2. 超时/失败时自动降级到 `navigator.geolocation.getCurrentPosition()`
3. 原生 API 也失败则显示手动输入框

## 四、实施步骤

### 步骤1：修改 manifest.json 添加 securityJsCode
### 步骤2：改造 attendance/index.vue 的 doGetLocation() 添加超时+降级逻辑

## 文件变更

| 文件 | 修改内容 |
|------|----------|
| `AppV3/src/manifest.json` | h5.sdkKey 新增 `securityJsCode` 字段 |
| `AppV3/src/pages/attendance/index.vue` | doGetLocation() 添加超时+浏览器原生Geolocation降级 |
