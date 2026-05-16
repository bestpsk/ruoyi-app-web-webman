# 验证码不显示问题修复计划

## 问题分析

### 错误现象

验证码图片无法显示，控制台报错：`[tcp://127.0.0.1:6379] 由于目标计算机积极拒绝，无法连接`

### 根本原因

验证码服务 (`CaptchaService.php`) 依赖 Redis 存储验证码数据：

* `CaptchaService::getCaptcha()` 方法在第 26-28 行使用 Redis 存储验证码

* Redis 配置指向 `127.0.0.1:6379`

* **Redis 已安装但未启动**（位于 `D:\phpstudy_pro\Extensions\redis3.0.504`）

## 修复方案

**方案：启动 Redis 服务**

### 相关文件

1. `webman/app/service/CaptchaService.php` - 验证码服务实现
2. `webman/config/redis.php` - Redis 配置
3. `D:\phpstudy_pro\Extensions\redis3.0.504\` - Redis 安装目录

### 实施步骤

#### 步骤 1：启动 Redis 服务

在终端中执行以下命令：

```powershell
cd D:\phpstudy_pro\Extensions\redis3.0.504
.\redis-server.exe redis.windows.conf
```

#### 步骤 2：验证 Redis 连接

测试 Redis 是否正常运行：

```powershell
.\redis-cli.exe ping
```

预期返回：`PONG`

#### 步骤 3：刷新前端页面

重新访问前端登录页面，验证码应该正常显示。

## 风险评估

* **无风险**：仅启动已安装的 Redis 服务，不修改任何代码

* **兼容性**：Redis 版本 3.0.504 与项目完全兼容

## 预期结果

启动 Redis 后：

1. Redis 监听端口 `127.0.0.1:6379`
2. 验证码服务正常存储和读取验证码数据
3. 前端登录页面的验证码图片正常显示

