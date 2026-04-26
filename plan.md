# RuoYi Spring Boot → Webman PHP 迁移计划

## 项目概述

将 RuoYi-Vue3 前端配套的 Spring Boot 后端功能完整迁移到 Webman PHP 框架中。

- **前端**: `d:\fuchenpro\front` (RuoYi-Vue3，已有，不修改)
- **目标后端**: `d:\fuchenpro\webman` (Webman PHP)
- **参考后端**: `d:\fuchenpro\RuoYi-Vue-fast-master` (Spring Boot)
- **数据库**: fuchenpro (已建表和数据)

## 前端期望的 API 端点总计约 120 个

---

## 阶段一：基础设施搭建

### 1.1 安装 Composer 依赖
- `illuminate/database` — Eloquent ORM
- `illuminate/pagination` — 分页
- `illuminate/events` — 事件系统
- `predis/predis` — Redis 客户端
- `vlucas/phpdotenv` — 环境变量
- `webman/database` — webman 数据库插件
- `webman/redis` — webman Redis 插件
- `firebase/php-jwt` — JWT 生成与解析
- `guzzlehttp/guzzle` — HTTP 客户端（IP定位等）
- `intervention/image` — 图片处理（头像裁剪）

### 1.2 配置文件
- 创建 `.env` 文件（数据库、Redis、JWT 密钥等配置）
- 创建 `config/database.php`（MySQL 连接配置，指向 fuchenpro 库）
- 创建 `config/redis.php`（Redis 连接配置）
- 修改 `config/middleware.php`（注册全局中间件）
- 修改 `config/route.php`（路由配置）

### 1.3 修改 Vite 代理
- 前端 `vite.config.js` 代理目标改为 `http://localhost:8787`（webman 默认端口）

---

## 阶段二：核心框架层

### 2.1 统一响应类 `app/common/AjaxResult.php`
- 对应 Java 的 `AjaxResult`，格式：`{code, msg, data}`
- 静态方法：`success()`, `success(data)`, `error()`, `error(msg)`, `warn(msg)`

### 2.2 分页响应类 `app/common/TableDataInfo.php`
- 格式：`{total, rows, code, msg}`
- 从请求参数中提取 `pageNum`, `pageSize`, `orderByColumn`, `isAsc`

### 2.3 常量定义 `app/common/Constants.php`
- JWT 密钥、Token 前缀、过期时间
- Redis Key 前缀（login_tokens:, captcha_codes:, sys_config:, sys_dict:, pwd_err_cnt: 等）
- 超级管理员角色ID、全部权限标识 `*:*:*`
- 菜单类型（M目录/C菜单/F按钮）

### 2.4 JWT 服务 `app/service/TokenService.php`
- `createToken(LoginUser $loginUser)` — 生成 JWT + 存储 LoginUser 到 Redis
- `getLoginUser(Request $request)` — 从请求中解析 Token，从 Redis 取用户
- `verifyToken(LoginUser $loginUser)` — 验证并自动续期（不足20分钟刷新）
- `refreshToken(LoginUser $loginUser)` — 刷新 Redis 缓存
- `removeToken(String $uuid)` — 删除 Token（登出）
- `refreshPermissionByRoleId(Long $roleId)` — 角色权限热刷新

### 2.5 登录用户实体 `app/common/LoginUser.php`
- 属性：userId, deptId, token(uuid), loginTime, expireTime, ipaddr, loginLocation, browser, os, permissions, user(SysUser)
- 序列化到 Redis 时排除 password 字段

### 2.6 认证中间件 `app/middleware/AuthMiddleware.php`
- 从 Header 提取 `Authorization: Bearer <token>`
- 解析 JWT 获取 UUID
- 从 Redis 获取 LoginUser
- 验证 Token 有效期并自动续期
- 将 LoginUser 注入到 Request 属性中
- 白名单路径跳过认证：`/login`, `/register`, `/captchaImage`

### 2.7 权限校验服务 `app/service/PermissionService.php`
- `hasPermi(permission)` — 验证是否具备某权限
- `hasRole(role)` — 判断是否拥有某角色
- `hasAnyPermi(permissions)` — 验证是否具有任一权限
- `lacksPermi(permission)` — 验证是否不具备某权限
- 超级管理员（admin）自动通过所有权限校验

### 2.8 操作日志中间件/注解 `app/middleware/LogMiddleware.php`
- 记录操作日志到 `sys_oper_log` 表
- 记录：模块标题、业务类型、请求方法、操作人、IP、请求参数、返回参数、耗时

### 2.9 数据权限服务 `app/service/DataScopeService.php`
- 根据角色的 dataScope 拼接 SQL 条件
- 支持5种数据范围：全部数据、自定义部门、本部门、本部门及以下、仅本人

### 2.10 Redis 服务 `app/service/RedisService.php`
- 封装 Redis 操作：set/get/delete/expire/keys/hSet/hGet 等
- 对应 Java 的 RedisCache 工具类

### 2.11 验证码服务 `app/service/CaptchaService.php`
- 生成数学运算验证码
- 存储到 Redis（2分钟过期）
- 返回 Base64 图片 + UUID

### 2.12 密码服务 `app/service/PasswordService.php`
- BCrypt 密码加密与验证
- 密码错误次数限制（Redis 计数，5次锁定10分钟）

### 2.13 IP 地址服务 `app/service/IpService.php`
- 通过 IP 获取地理位置（使用离线库或在线API）

### 2.14 浏览器/OS 解析服务 `app/service/UserAgentService.php`
- 解析 User-Agent 获取浏览器类型和操作系统

---

## 阶段三：数据模型层（Eloquent Model）

所有模型放在 `app/model/` 目录下：

| 模型名 | 对应表 | 说明 |
|--------|--------|------|
| SysUser | sys_user | 用户 |
| SysRole | sys_role | 角色 |
| SysMenu | sys_menu | 菜单权限 |
| SysDept | sys_dept | 部门 |
| SysPost | sys_post | 岗位 |
| SysDictType | sys_dict_type | 字典类型 |
| SysDictData | sys_dict_data | 字典数据 |
| SysConfig | sys_config | 参数配置 |
| SysNotice | sys_notice | 通知公告 |
| SysNoticeRead | sys_notice_read | 公告已读 |
| SysOperLog | sys_oper_log | 操作日志 |
| SysLogininfor | sys_logininfor | 登录日志 |
| SysJob | sys_job | 定时任务 |
| SysJobLog | sys_job_log | 任务日志 |
| SysUserRole | sys_user_role | 用户角色关联 |
| SysUserPost | sys_user_post | 用户岗位关联 |
| SysRoleMenu | sys_role_menu | 角色菜单关联 |
| SysRoleDept | sys_role_dept | 角色部门关联 |
| GenTable | gen_table | 代码生成表 |
| GenTableColumn | gen_table_column | 代码生成字段 |

---

## 阶段四：业务服务层

所有服务放在 `app/service/` 目录下：

### 4.1 系统管理模块

| 服务 | 关键方法 |
|------|---------|
| SysUserService | selectUserList, selectUserById, selectUserByUserName, insertUser, updateUser, deleteUserByIds, resetPwd, checkUserNameUnique, checkPhoneUnique, checkEmailUnique |
| SysRoleService | selectRoleList, selectRoleById, insertRole, updateRole, deleteRoleByIds, authDataScope, selectRolePermissionByUserId, checkRoleNameUnique |
| SysMenuService | selectMenuList, selectMenuTreeByUserId, buildMenus(构建前端路由), selectMenuPermsByUserId, selectMenuPermsByRoleId, insertMenu, updateMenu, deleteMenuById |
| SysDeptService | selectDeptList, selectDeptById, insertDept, updateDept, deleteDeptById, selectDeptTreeList, buildDeptTree |
| SysPostService | selectPostList, selectPostById, insertPost, updatePost, deletePostByIds |
| SysDictTypeService | selectDictTypeList, insertDictType, updateDictType, deleteDictTypeByIds, resetDictCache, selectDictDataByType |
| SysDictDataService | selectDictDataList, insertDictData, updateDictData, deleteDictDataByIds |
| SysConfigService | selectConfigList, selectConfigById, selectConfigByKey, insertConfig, updateConfig, deleteConfigByIds, resetConfigCache, selectCaptchaEnabled |
| SysNoticeService | selectNoticeList, selectNoticeById, insertNotice, updateNotice, deleteNoticeByIds |
| SysNoticeReadService | selectNoticeListWithReadStatus, markRead, markReadBatch, selectReadUsersByNoticeId |

### 4.2 监控模块

| 服务 | 关键方法 |
|------|---------|
| SysJobService | selectJobList, selectJobById, insertJob, updateJob, deleteJobByIds, changeStatus, run |
| SysJobLogService | selectJobLogList, selectJobLogById, deleteJobLogByIds, cleanJobLog |
| SysLogininforService | selectLogininforList, insertLogininfor, deleteLogininforByIds, cleanLogininfor |
| SysOperLogService | selectOperLogList, insertOperLog, deleteOperLogByIds, cleanOperLog |
| SysUserOnlineService | selectOnlineList, forceLogout |

### 4.3 工具模块

| 服务 | 关键方法 |
|------|---------|
| GenTableService | selectGenTableList, selectGenTableById, selectDbTableList, importGenTable, updateGenTable, deleteGenTableByIds, previewCode, synchDb |

---

## 阶段五：控制器层

所有控制器放在 `app/controller/` 目录下，按模块组织：

### 5.1 公共控制器

| 控制器 | 路径前缀 | 端点数 |
|--------|---------|--------|
| CaptchaController | / | 1 (GET /captchaImage) |
| CommonController | /common | 4 (上传/下载) |

### 5.2 认证控制器

| 控制器 | 路径前缀 | 端点数 |
|--------|---------|--------|
| SysLoginController | / | 4 (POST /login, POST /logout, GET /getInfo, GET /getRouters) |
| SysRegisterController | / | 1 (POST /register) |
| SysIndexController | / | 2 (POST /unlockscreen, GET /) |

### 5.3 系统管理控制器

| 控制器 | 路径前缀 | 端点数 |
|--------|---------|--------|
| SysUserController | /system/user | 14 |
| SysRoleController | /system/role | 13 |
| SysMenuController | /system/menu | 8 |
| SysDeptController | /system/dept | 7 |
| SysPostController | /system/post | 5 |
| SysDictTypeController | /system/dict/type | 8 |
| SysDictDataController | /system/dict/data | 7 |
| SysConfigController | /system/config | 7 |
| SysNoticeController | /system/notice | 9 |
| SysProfileController | /system/user/profile | 4 |

### 5.4 监控模块控制器

| 控制器 | 路径前缀 | 端点数 |
|--------|---------|--------|
| SysUserOnlineController | /monitor/online | 2 |
| SysJobController | /monitor/job | 7 |
| SysJobLogController | /monitor/jobLog | 3 |
| SysLogininforController | /monitor/logininfor | 4 |
| SysOperlogController | /monitor/operlog | 3 |
| ServerController | /monitor/server | 1 |
| CacheController | /monitor/cache | 7 |

### 5.5 工具模块控制器

| 控制器 | 路径前缀 | 端点数 |
|--------|---------|--------|
| GenController | /tool/gen | 10 |

---

## 阶段六：路由注册

在 `config/route.php` 中注册所有路由，按模块分组：
- 公共路由（无需认证）：/login, /register, /captchaImage
- 认证路由组（需要 JWT 中间件）
- 系统管理路由组
- 监控模块路由组
- 工具模块路由组

---

## 阶段七：功能验证与调试

### 7.1 核心流程验证
1. 验证码获取与登录流程
2. Token 生成与验证
3. 动态菜单路由加载
4. 权限校验（按钮级别）
5. 数据权限过滤

### 7.2 CRUD 功能验证
1. 用户管理（含关联角色/岗位）
2. 角色管理（含菜单权限分配、数据权限）
3. 菜单管理（含树结构）
4. 部门管理（含树结构）
5. 字典管理（含缓存刷新）
6. 参数配置（含缓存刷新）
7. 通知公告（含已读功能）

### 7.3 监控功能验证
1. 在线用户列表与强退
2. 操作日志/登录日志查询与清空
3. 缓存监控（Redis 信息查询）
4. 服务监控（PHP 进程信息）

---

## 实施优先级

1. **P0 - 必须先完成**：基础设施 + 核心框架 + 登录认证 + 路由菜单
2. **P1 - 核心功能**：用户/角色/菜单/部门管理
3. **P2 - 辅助功能**：字典/参数/岗位/通知公告管理
4. **P3 - 监控功能**：日志/在线用户/缓存监控/服务监控
5. **P4 - 高级功能**：定时任务/代码生成（可后续迭代）

---

## 关键技术决策

| Java 技术 | PHP 替代方案 |
|-----------|-------------|
| Spring Security | 自定义 JWT 中间件 |
| JWT (jjwt) | firebase/php-jwt |
| BCrypt | password_hash/password_verify |
| Redis (Lettuce) | predis/predis |
| MyBatis + PageHelper | Eloquent ORM + illuminate/pagination |
| @DataScope AOP | DataScopeService 在 Service 层手动调用 |
| @Log AOP | LogMiddleware 中间件 |
| Quartz | webman 定时任务（Crontab 进程） |
| Kaptcha 验证码 | 自定义 GD 库生成数学验证码 |
| FastJson2 | PHP json_encode/json_decode |
| Druid 连接池 | Eloquent 内置连接管理 |
