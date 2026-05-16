# 档案照片加载失败修复计划

## 问题分析

### 现象
web端 销售开单 > 档案 页面，照片显示"加载失败"，但 COS 存储桶中已有图片文件。

### 根本原因分析

#### 1. 数据库中 photos 字段存储格式
从 `sql-backup.sql` 中看到实际数据：
```json
["20260509/74e407483fabcd1997c631eab465649e.png","20260509/a4c36981c3f7fe74d9580c9133ab66a7.jpg"]
```
存储的是**相对路径**（日期+文件名），不是完整 URL。

#### 2. 前端 parseArchivePhotos 处理逻辑
[front/src/views/business/sales/index.vue:1254-1271](file:///f:/fuchen/front/src/views/business/sales/index.vue#L1254-L1271)：
- 以 `http(s)://` 开头 → 直接使用（COS 完整 URL）
- 其他情况 → 拼接 `/profile/upload/` 前缀（本地路径）

#### 3. 加载失败的直接原因
**COS 存储桶默认为"私有读取"权限**，浏览器无法直接通过以下 URL 访问图片：
```
https://mydream-1302682813.cos.ap-shanghai.myqcloud.com/upload/20260515/xxx.png
```
返回 403 Forbidden 或 CORS 错误。

### 当前 COS 配置
- Region: `ap-shanghai`
- Bucket: `mydream-1302682813`
- CDN Domain: 未配置（空）

## 修复方案

### 方案 A：修改 COS 存储桶权限为公有读（推荐，最简单）

在腾讯云 COS 控制台操作：
1. 登录腾讯云控制台 → 对象存储 → 存储桶列表
2. 找到 `mydream-1302682813` 存储桶
3. 权限管理 → 读写权限 → 改为 **公有读私有写**
4. （可选）跨域设置 → 添加规则允许前端域名跨域访问

**优点**：无需改代码，立即生效
**缺点**：所有文件公开可访问（但 URL 不易被猜测，风险可控）

### 方案 B：配置自定义 CDN 域名（推荐用于生产环境）

1. 在腾讯云控制台绑定自定义域名到 COS 存储桶
2. 配置 HTTPS 证书
3. 更新 `.env` 中 `COS_CDN_DOMAIN=cdn.yourdomain.com`

**优点**：可配置缓存、防盗链、HTTPS
**缺点**：需要已备案域名

### 方案 C：后端代理转发图片请求（安全但不推荐）

新增一个后端接口，代理获取 COS 图片并返回给前端：
- 新增 `/common/image-proxy?url=xxx` 接口
- 后端用 SDK 获取签名 URL 或直接读取返回
- 前端所有图片走此代理

**优点**：不暴露 COS URL，安全性高
**缺点**：增加服务器带宽消耗和延迟

## 实施步骤（推荐方案 A + 备选方案 C）

### 步骤 1：用户在腾讯云控制台操作（方案 A）
将存储桶权限改为"公有读私有写"

### 步骤 2：验证 COS URL 可访问性
在浏览器中直接访问一张 COS 图片 URL 测试

### 步骤 3：如方案 A 不可行，实施后端代理（方案 C）
修改文件：
1. `webman/app/controller/CommonController.php` - 新增 imageProxy 方法
2. `webman/config/route.php` - 新增路由
3. `front/src/views/business/sales/index.vue` - 修改 parseArchivePhotos 函数

## 风险评估

| 风险 | 影响 | 应对措施 |
|------|------|---------|
| 公有读取导致文件泄露 | 低 | URL 含随机 hash，不易被枚举 |
| CORS 跨域问题 | 中 | 在 COS 控制台添加跨域规则 |
| 旧数据兼容性 | 无 | 前端已做新旧 URL 兼容处理 |
