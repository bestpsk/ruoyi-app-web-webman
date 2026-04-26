# 前后端数据接口命名统一修复计划

## 问题分析

### 核心问题
- **前端**：所有 POST/PUT 请求发送的数据字段名统一使用**驼峰命名**（如 `userName`, `deptId`, `roleId`）
- **后端**：大部分控制器直接使用**蛇形命名**访问请求数据（如 `$data['user_name']`, `$data['dept_id']`）
- **请求工具层**：没有做任何命名风格转换，数据原样发送

### 现状
- `SysUserController` 的 `add` 和 `edit` 方法已通过 `convertToSnakeCase` 修复
- 其他所有控制器均未处理命名转换，存在大量不匹配问题

### 新增按钮"访问资源不存在"问题
前端新增用户时调用 `getUser()` 不带参数，请求 URL 为 `/system/user/`，可能匹配不到路由。

## 需要修复的控制器清单

### 1. SysUserController（部分已修复，需补充）
- ✅ `add()` - 已添加 convertToSnakeCase
- ✅ `edit()` - 已添加 convertToSnakeCase
- ❌ `updateProfile()` - 第202行 `$data = $request->post()` 未转换，访问 `$data['phonenumber']`
- ❌ `insertAuthRole()` - 第285行 `$request->post('userId')` 驼峰，但传给 `updateUser` 用蛇形 `user_id`

### 2. SysRoleController
- ❌ `add()` - 访问 `$data['role_name']`, `$data['role_key']`, `$data['create_by']`
- ❌ `edit()` - 访问 `$data['role_name']`, `$data['role_key']`, `$data['role_id']`, `$data['update_by']`
- ❌ `dataScope()` - 访问 `$data['role_id']`, `$data['data_scope']`, `$data['dept_ids']`

### 3. SysDeptController
- ❌ `add()` - 访问 `$data['create_by']`
- ❌ `edit()` - 访问 `$data['update_by']`
- ❌ `updateSort()` - 访问 `$dept['dept_id']`, `$dept['order_num']`

### 4. SysMenuController
- ❌ `add()` - 访问 `$data['create_by']`
- ❌ `edit()` - 访问 `$data['update_by']`
- ❌ `updateSort()` - 访问 `$menu['menu_id']`, `$menu['order_num']`

### 5. SysPostController
- ❌ `add()` - 访问 `$data['create_by']`
- ❌ `edit()` - 访问 `$data['update_by']`

### 6. SysDictTypeController
- ❌ `add()` - 访问 `$data['create_by']`
- ❌ `edit()` - 访问 `$data['update_by']`

### 7. SysDictDataController
- ❌ `add()` - 访问 `$data['create_by']`
- ❌ `edit()` - 访问 `$data['update_by']`

### 8. SysConfigController
- ❌ `add()` - 访问 `$data['create_by']`
- ❌ `edit()` - 访问 `$data['update_by']`

### 9. SysNoticeController
- ❌ `add()` - 访问 `$data['create_by']`
- ❌ `edit()` - 访问 `$data['update_by']`

### 10. SysJobController
- ❌ `add()` - 访问 `$data['create_by']`
- ❌ `edit()` - 访问 `$data['update_by']`

## 解决方案

### 方案：创建全局驼峰转蛇形中间件/辅助函数

在 `app/common` 中创建一个全局辅助函数，所有控制器统一使用。

### 步骤 1: 在 Helpers.php 中添加全局转换函数

在 `app/common/Helpers.php`（如果不存在则创建）中添加：

```php
if (!function_exists('convert_to_snake_case')) {
    function convert_to_snake_case($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $newKey = is_string($key) ? to_snake_case($key) : $key;
            if (is_array($value) && !empty($value) && array_values($value) !== $value) {
                $result[$newKey] = convert_to_snake_case($value);
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }
}

if (!function_exists('to_snake_case')) {
    function to_snake_case($key)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
    }
}
```

### 步骤 2: 修改所有控制器的 add/edit/updateSort/dataScope/updateProfile 等方法

在每个控制器中，将 `$data = $request->post()` 改为 `$data = convert_to_snake_case($request->post())`。

### 步骤 3: 修复新增用户路由问题

检查前端新增用户时的请求路径，确保 `/system/user/`（空 userId）能正确路由到 `getInfo` 方法。

### 步骤 4: 删除 SysUserController 中的私有转换方法

因为已改为使用全局函数，删除 `convertToSnakeCase` 和 `toSnakeCase` 私有方法。

## 文件修改清单

| 文件 | 修改内容 |
|------|----------|
| `app/common/Helpers.php` | 添加 `convert_to_snake_case` 和 `to_snake_case` 全局函数 |
| `app/functions.php` | 确保 Helpers.php 被加载 |
| `app/controller/system/SysUserController.php` | 修改 `updateProfile`、`insertAuthRole`，删除私有转换方法改用全局函数 |
| `app/controller/system/SysRoleController.php` | 修改 `add`、`edit`、`dataScope` |
| `app/controller/system/SysDeptController.php` | 修改 `add`、`edit`、`updateSort` |
| `app/controller/system/SysMenuController.php` | 修改 `add`、`edit`、`updateSort` |
| `app/controller/system/SysPostController.php` | 修改 `add`、`edit` |
| `app/controller/system/SysDictTypeController.php` | 修改 `add`、`edit` |
| `app/controller/system/SysDictDataController.php` | 修改 `add`、`edit` |
| `app/controller/system/SysConfigController.php` | 修改 `add`、`edit` |
| `app/controller/system/SysNoticeController.php` | 修改 `add`、`edit` |
| `app/controller/monitor/SysJobController.php` | 修改 `add`、`edit` |
| `config/route.php` | 修复新增用户路由 |
