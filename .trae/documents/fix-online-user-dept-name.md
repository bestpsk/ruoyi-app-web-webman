# 在线用户列表不显示问题修复计划

## 问题描述

点击在线用户提示：`Attempt to read property "dept_name" on array`，数据列表不显示。

## 问题根因

在 `SysUserOnlineService.php` 第 33 行：

```php
'deptName' => $loginUser->user && $loginUser->user->dept ? $loginUser->user->dept->dept_name : '',
```

这里尝试以对象方式访问 `$loginUser->user->dept->dept_name`，但实际上：

1. 用户登录时，`LoginUser` 对象被序列化为 JSON 存储到 Redis
2. `LoginUser::toArray()` 方法将 `user` 转换为数组，包括 `dept` 关联
3. 当从 Redis 读取时，`LoginUser::fromArray()` 将 `user` 数组转换为 `SysUser` 模型
4. **但 `user` 中的 `dept` 属性仍然是数组，没有被转换为 `SysDept` 模型**

所以 `$loginUser->user->dept` 是数组，不是对象，访问 `->dept_name` 会报错。

## 解决方案

修改 `SysUserOnlineService.php`，使用数组方式访问 `dept_name`：

```php
'deptName' => $loginUser->user && $loginUser->user->dept 
    ? (is_array($loginUser->user->dept) 
        ? ($loginUser->user->dept['dept_name'] ?? '') 
        : $loginUser->user->dept->dept_name) 
    : '',
```

## 修复步骤

### 步骤1：修改 SysUserOnlineService::selectOnlineList 方法

修改 `d:\fuchenpro\webman\app\service\SysUserOnlineService.php` 第 33 行，兼容数组和对象两种访问方式。

### 步骤2：验证修复

- 刷新在线用户页面
- 确认列表正常显示
- 确认部门名称正确显示

## 预期结果

修复后，在线用户列表应该正常显示，包括用户名、部门名称、IP地址等信息。
