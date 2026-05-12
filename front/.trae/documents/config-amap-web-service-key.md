# 配置高德地图 Web 服务 Key

## 背景
- 当前已配置 Web端(JS API) 类型的 Key，用于前端地图组件
- APP 端使用的是 Web 服务类型的 Key，需要在前端也配置一份

## 实现步骤

### 1. 更新 `.env.development` 文件
添加 Web 服务类型的 Key 配置：
```bash
# 高德地图 Web服务 Key（用于 APP 端）
VITE_AMAP_WEB_SERVICE_KEY = d184e115457658cbcf3f92ed8e3a1772
```

### 2. 同步更新其他环境配置文件
- `.env.production`
- `.env.staging`

## 最终配置示例
```bash
# 高德地图配置（Web端 JS API）
VITE_AMAP_KEY = fa588d6bc9fbc9dce1f0c379e40f9faa
VITE_AMAP_SECURITY_CODE = f9ef226bd6e4a6276d45ed1e5cb9a475

# 高德地图配置（Web服务，用于 APP 端）
VITE_AMAP_WEB_SERVICE_KEY = d184e115457658cbcf3f92ed8e3a1772
```
