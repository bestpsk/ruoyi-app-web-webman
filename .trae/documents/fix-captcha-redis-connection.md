# 验证码不显示 - Redis 连接问题修复方案

## 问题分析

### 错误信息
```
由于目标计算机积极拒绝，无法连接。 [tcp://127.0.0.1:6379]
```

### 问题根源
**Redis 服务未启动或无法连接**

验证码功能依赖 Redis 存储验证码答案，当前 Redis 服务无法连接导致验证码生成失败。

### 相关代码分析

#### 1. Redis 配置文件
**文件**: [config/redis.php](file:///f:/fuchen/webman/config/redis.php)

```php
return [
    'client' => 'predis',
    'default' => [
        'password' => getenv('REDIS_PASSWORD') ?: '',
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',  // 默认 127.0.0.1
        'port' => getenv('REDIS_PORT') ?: 6379,         // 默认 6379
        'database' => getenv('REDIS_DB') ?: 0,
        // ...
    ]
];
```

#### 2. 验证码服务
**文件**: [app/service/CaptchaService.php](file:///f:/fuchen/webman/app/service/CaptchaService.php)

**关键代码**（第26-28行）：
```php
$redis = Redis::connection();  // 连接 Redis
$verifyKey = Constants::CAPTCHA_CODE_KEY . $uuid;
$redis->setex($verifyKey, Constants::CAPTCHA_EXPIRE * 60, $answer);  // 存储验证码答案
```

**问题点**：
- 第26行尝试连接 Redis
- 如果 Redis 服务未启动，连接失败抛出异常
- 导致验证码无法生成

## 解决方案

### 方案一：启动 Redis 服务（推荐）

#### Windows 系统操作步骤

**步骤 1：检查 Redis 是否已安装**
```powershell
# 方法1：检查 Redis 服务
Get-Service *redis*

# 方法2：检查 Redis 可执行文件
where redis-server
where redis-cli
```

**步骤 2：如果已安装，启动 Redis 服务**

**方式A：作为 Windows 服务启动**
```powershell
# 启动 Redis 服务
net start Redis

# 或者使用服务管理器
Start-Service Redis
```

**方式B：命令行启动**
```powershell
# 直接启动 Redis 服务器
redis-server

# 或指定配置文件启动
redis-server C:\redis\redis.windows.conf
```

**步骤 3：验证 Redis 是否启动成功**
```powershell
# 使用 Redis 客户端测试连接
redis-cli ping

# 预期输出：PONG
```

**步骤 4：测试验证码功能**
- 刷新登录页面
- 检查验证码是否正常显示

---

#### 如果 Redis 未安装

**安装方式A：使用 Chocolatey（推荐）**
```powershell
# 安装 Chocolatey（如果未安装）
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.WebRequest]::DefaultWebProxy.Credentials = [System.Net.CredentialCache]::DefaultCredentials; iex ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))

# 安装 Redis
choco install redis-64 -y

# 启动 Redis 服务
redis-server
```

**安装方式B：手动下载安装**
1. 下载 Redis for Windows：
   - GitHub: https://github.com/microsoftarchive/redis/releases
   - 下载 `Redis-x64-3.2.100.zip`（或最新版本）

2. 解压到指定目录：
   ```
   C:\Redis\
   ├── redis-server.exe
   ├── redis-cli.exe
   ├── redis.windows.conf
   └── ...
   ```

3. 启动 Redis：
   ```powershell
   cd C:\Redis
   redis-server redis.windows.conf
   ```

4. （可选）注册为 Windows 服务：
   ```powershell
   redis-server --service-install redis.windows.conf --loglevel verbose
   redis-server --service-start
   ```

---

### 方案二：修改验证码存储方式（临时方案）

如果无法启动 Redis，可以临时修改验证码服务使用文件缓存或 Session 存储。

#### 修改 CaptchaService.php

**修改文件**: [app/service/CaptchaService.php](file:///f:/fuchen/webman/app/service/CaptchaService.php)

**方案2A：使用文件缓存**

```php
<?php

namespace app\service;

use support\Redis;
use app\common\Constants;

class CaptchaService
{
    private static $cacheDir = runtime_path() . '/captcha/';

    public static function getCaptcha()
    {
        $captchaEnabled = SysConfigService::selectCaptchaEnabled();
        if (!$captchaEnabled) {
            return [
                'captchaEnabled' => false,
                'img' => '',
                'uuid' => '',
            ];
        }

        $uuid = self::fastUUID();
        $result = self::generateMathCaptcha();
        $answer = $result['answer'];
        $image = $result['image'];

        // 使用文件缓存代替 Redis
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
        $cacheFile = self::$cacheDir . $uuid . '.txt';
        file_put_contents($cacheFile, $answer);

        return [
            'captchaEnabled' => true,
            'img' => $image,
            'uuid' => $uuid,
        ];
    }

    public static function validateCaptcha($code, $uuid)
    {
        // 从文件读取验证码
        $cacheFile = self::$cacheDir . ($uuid ?? '') . '.txt';
        if (!file_exists($cacheFile)) {
            return false;
        }
        $captcha = file_get_contents($cacheFile);
        unlink($cacheFile); // 删除缓存文件
        return strcasecmp($code, $captcha) === 0;
    }

    // ... 其他方法保持不变
}
```

**方案2B：使用 Session 存储**

```php
public static function getCaptcha()
{
    $captchaEnabled = SysConfigService::selectCaptchaEnabled();
    if (!$captchaEnabled) {
        return [
            'captchaEnabled' => false,
            'img' => '',
            'uuid' => '',
        ];
    }

    $uuid = self::fastUUID();
    $result = self::generateMathCaptcha();
    $answer = $result['answer'];
    $image = $result['image'];

    // 使用 Session 存储验证码
    $session = request()->session();
    $verifyKey = Constants::CAPTCHA_CODE_KEY . $uuid;
    $session->set($verifyKey, $answer);

    return [
        'captchaEnabled' => true,
        'img' => $image,
        'uuid' => $uuid,
    ];
}

public static function validateCaptcha($code, $uuid)
{
    $verifyKey = Constants::CAPTCHA_CODE_KEY . ($uuid ?? '');
    $session = request()->session();
    $captcha = $session->get($verifyKey);
    if ($captcha === null) {
        return false;
    }
    $session->delete($verifyKey);
    return strcasecmp($code, $captcha) === 0;
}
```

---

### 方案三：禁用验证码功能（临时方案）

如果暂时不需要验证码功能，可以临时禁用。

#### 修改系统配置

在数据库 `sys_config` 表中，找到 `captcha_enabled` 配置项，设置为 `false` 或 `0`。

**SQL 语句**：
```sql
UPDATE sys_config 
SET config_value = 'false' 
WHERE config_key = 'captcha_enabled';
```

或通过系统配置管理界面关闭验证码功能。

---

## 推荐执行流程

### 优先级排序

1. **P0 - 立即执行**：启动 Redis 服务（方案一）
   - 最彻底的解决方案
   - 不影响其他功能

2. **P1 - 备选方案**：修改为文件缓存（方案二）
   - 如果无法启动 Redis
   - 需要修改代码

3. **P2 - 临时方案**：禁用验证码（方案三）
   - 仅用于紧急情况
   - 降低安全性

---

## 详细实施步骤（推荐方案一）

### 步骤 1：检查 Redis 安装状态
```powershell
# 检查 Redis 服务
Get-Service *redis*

# 检查 Redis 可执行文件
where redis-server
```

### 步骤 2：根据检查结果执行

**情况A：Redis 已安装但未启动**
```powershell
# 启动 Redis 服务
net start Redis

# 或
Start-Service Redis
```

**情况B：Redis 未安装**
```powershell
# 使用 Chocolatey 安装
choco install redis-64 -y

# 启动 Redis
redis-server
```

**情况C：手动安装**
1. 下载 Redis for Windows
2. 解压到 C:\Redis
3. 运行 `redis-server.exe`

### 步骤 3：验证 Redis 连接
```powershell
# 测试连接
redis-cli ping

# 预期输出：PONG
```

### 步骤 4：重启 webman 服务
```powershell
# 停止 webman
# Ctrl+C 或关闭运行窗口

# 重新启动 webman
cd f:\fuchen\webman
php start.php start
```

### 步骤 5：测试验证码功能
- 访问 Front 登录页面：http://localhost:端口/
- 访问 AppV3 登录页面
- 检查验证码是否正常显示

---

## 环境变量配置（可选）

如果 Redis 不在默认地址或需要密码，可以创建环境变量配置。

### 创建 .env 文件

**文件路径**: `f:\fuchen\webman\.env`

```env
# Redis 配置
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0
```

### 修改 Redis 配置（如果需要）

如果 Redis 在其他地址，修改 `.env` 文件：
```env
REDIS_HOST=192.168.1.100  # Redis 服务器地址
REDIS_PORT=6379           # Redis 端口
REDIS_PASSWORD=your_password  # Redis 密码（如果有）
REDIS_DB=0                # 数据库编号
```

---

## 常见问题排查

### 问题1：Redis 服务启动失败

**可能原因**：
- 端口被占用
- 配置文件错误
- 权限不足

**解决方法**：
```powershell
# 检查端口占用
netstat -ano | findstr :6379

# 如果端口被占用，结束占用进程
taskkill /PID <进程ID> /F

# 使用其他端口启动
redis-server --port 6380
```

### 问题2：Redis 连接超时

**可能原因**：
- 防火墙阻止
- Redis 未监听正确地址

**解决方法**：
```powershell
# 检查防火墙规则
netsh advfirewall firewall show rule name=all | findstr 6379

# 添加防火墙规则
netsh advfirewall firewall add rule name="Redis" dir=in action=allow protocol=tcp localport=6379
```

### 问题3：验证码仍不显示

**检查清单**：
1. ✅ Redis 服务已启动
2. ✅ Redis 连接测试成功（redis-cli ping）
3. ✅ webman 服务已重启
4. ✅ 浏览器缓存已清除
5. ✅ 检查 webman 日志是否有错误

**查看日志**：
```powershell
# 查看 webman 日志
cd f:\fuchen\webman
type runtime\logs\webman.log
```

---

## 验证成功标志

### Redis 启动成功
```powershell
redis-cli ping
# 输出：PONG
```

### 验证码显示成功
- Front 登录页面显示验证码图片
- AppV3 登录页面显示验证码图片
- 验证码可以正常刷新
- 输入正确验证码可以登录

---

## 总结

### 问题原因
Redis 服务未启动，导致验证码无法存储和验证。

### 推荐解决方案
**启动 Redis 服务**（方案一）

### 执行步骤
1. 检查 Redis 安装状态
2. 启动 Redis 服务
3. 验证 Redis 连接
4. 重启 webman 服务
5. 测试验证码功能

### 备选方案
- 方案二：修改为文件缓存存储
- 方案三：临时禁用验证码功能

### 预期结果
✅ Redis 服务正常运行
✅ 验证码正常显示
✅ 登录功能正常使用
