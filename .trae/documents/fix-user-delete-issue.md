# 修复用户管理删除用户失败问题

## 问题分析

### 现象
系统管理 -> 用户管理 -> 删除用户功能失败

### 根因分析

**问题代码位置：** [SysUserController.php:147-157](file:///d:/fuchenpro/webman/app/controller/system/SysUserController.php#L147-L157)

```php
public function remove(Request $request)
{
    $userIds = explode(',', $request->input('userIds', ''));  // ❌ 错误：无法获取路径参数
    $userIds = array_map('intval', array_filter($userIds));
    ...
}
```

**问题原因：**
在 Webman 框架中，路径参数 `{userIds}` 不会自动注入到 `$request->input()` 方法中。`$request->input()` 只能获取查询参数或 POST body 中的数据。

**路由配置：** [route.php:30](file:///d:/fuchenpro/webman/config/route.php#L30)
```php
Route::delete('/system/user/{userIds}', [app\controller\system\SysUserController::class, 'remove']);
```

**前端请求：** [user.js:40-45](file:///d:/fuchenpro/front/src/api/system/user.js#L40-L45)
```javascript
export function delUser(userId) {
  return request({
    url: '/system/user/' + userId,  // DELETE /system/user/1,2,3
    method: 'delete'
  })
}
```

### 正确实现参考

其他控制器已正确实现，如 [SysDeptController.php:71-81](file:///d:/fuchenpro/webman/app/controller/system/SysDeptController.php#L71-L81)：

```php
public function remove(Request $request)
{
    $parts = explode('/', $request->path());
    $deptId = intval(end($parts));  // ✅ 正确：从路径解析参数
    ...
}
```

## 修复方案

### 修改文件
`d:\fuchenpro\webman\app\controller\system\SysUserController.php`

### 修改内容

将 `remove` 方法从：

```php
public function remove(Request $request)
{
    $userIds = explode(',', $request->input('userIds', ''));
    $userIds = array_map('intval', array_filter($userIds));
    if (in_array(1, $userIds)) {
        return AjaxResult::error('不允许删除超级管理员');
    }
    $service = new SysUserService();
    $result = $service->deleteUserByIds($userIds);
    return AjaxResult::toAjax($result ? 1 : 0);
}
```

修改为：

```php
public function remove(Request $request)
{
    $parts = explode('/', $request->path());
    $userIds = explode(',', end($parts));
    $userIds = array_map('intval', array_filter($userIds));
    if (in_array(1, $userIds)) {
        return AjaxResult::error('不允许删除超级管理员');
    }
    $service = new SysUserService();
    $result = $service->deleteUserByIds($userIds);
    return AjaxResult::toAjax($result ? 1 : 0);
}
```

## 验证步骤

1. 启动后端服务
2. 登录系统管理后台
3. 进入系统管理 -> 用户管理
4. 选择一个或多个用户（非超级管理员）
5. 点击删除按钮
6. 确认删除成功

## 影响范围

- 仅影响用户删除功能
- 不影响其他功能
- 修改量小，风险低
