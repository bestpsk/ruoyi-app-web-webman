# 修复定位一直显示"定位中"问题

## 问题分析

从截图看，页面卡在"loading 定位中..."状态，`locationLoading` 始终为 `true`，说明 `uni.getLocation` 的 **success 和 fail 回调都没有触发**。

**根因**：H5 端 `uni.getLocation({ type: 'gcj02' })` 底层依赖高德地图 JS SDK。如果：
- SDK Key 无效或未正确配置
- SDK 加载失败（网络问题、CDN 被墙等）
- 浏览器 Geolocation API 本身被阻止

则 `uni.getLocation` 会**静默挂起**，既不触发 success 也不触发 fail，导致永远停在"定位中"。

## 解决方案

### 方案：添加超时机制 + 多重降级策略

1. **添加超时保护**：`doGetLocation()` 增加 8 秒超时，超时后自动停止 loading 并显示手动输入
2. **增加原生 Geolocation 降级**：先尝试 `uni.getLocation`，超时或失败后回退到浏览器原生 `navigator.geolocation`
3. **始终确保 loading 状态能被重置**：无论什么情况，loading 必须在有限时间内结束

## 实施步骤

### 步骤1：改造 `attendance/index.vue` 的定位逻辑
- `doGetLocation()` 添加 setTimeout 超时（8秒）
- 超时后设置 `locationLoading = false` + 显示手动输入框
- 新增 `fallbackGetLocation()` 使用浏览器原生 Geolocation API
- `uni.getLocation` 失败/超时时自动调用降级方案

## 文件变更

| 文件 | 修改内容 |
|------|----------|
| `AppV3/src/pages/attendance/index.vue` | 改造 `doGetLocation()` 添加超时+降级逻辑 |

## 改造后的定位流程

```
点击定位/进入页面
    ↓
uni.getLocation({ type: 'gcj02' })
    ↓ (同时启动 8 秒定时器)
    ├─ 成功 → 更新位置信息 ✅
    ├─ 失败 → 尝试降级方案 ↓
    └─ 超时(8s) → 取消 uni.getLocation，尝试降级方案 ↓
            ↓
        navigator.geolocation.getCurrentPosition()
            ├─ 成功 → 更新位置信息 ✅
            └─ 失败 → 显示"定位失败"+ 手动输入地址 ✅
```
