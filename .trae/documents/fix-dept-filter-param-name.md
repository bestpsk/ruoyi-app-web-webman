# 选择部门后用户列表未筛选问题修复计划

## 问题描述

在用户管理页面，选择左侧部门树后，右侧用户列表没有根据部门进行筛选。

## 问题根因

**参数名称不匹配！**

前端发送的参数是驼峰命名 `deptId`，但后端检查的是下划线命名 `dept_id`。

### 前端代码（index.vue 第 277-279 行）
```javascript
function handleNodeClick(data) {
  queryParams.value.deptId = data.id  // 使用驼峰命名 deptId
  handleQuery()
}
```

### 后端代码（SysUserService.php 第 26 行）
```php
if (!empty($params['dept_id'])) {  // 检查下划线命名 dept_id
    // ...
}
```

### 参数传递流程
1. 前端发送 GET 请求：`/system/user/list?deptId=103`
2. 后端控制器接收：`$params = $request->all()` → `$params['deptId'] = 103`
3. 后端服务检查：`$params['dept_id']` → **不存在！**

## 解决方案

在控制器的 `list` 方法中，对 GET 参数进行命名转换（驼峰转下划线）。

## 修复步骤

### 步骤1：修改 SysUserController::list 方法

修改 `d:\fuchenpro\webman\app\controller\system\SysUserController.php`：

```php
public function list(Request $request)
{
    $params = convert_to_snake_case($request->all());
    $params['login_user'] = $request->loginUser;
    $service = new SysUserService();
    $result = $service->selectUserList($params);
    return TableDataInfo::result($result->items(), $result->total());
}
```

### 步骤2：验证修复

- 选择部门树中的某个部门
- 确认用户列表正确显示该部门及其子部门的用户

## 预期结果

修复后，选择部门时：
1. 前端发送 `deptId=103`
2. 后端转换为 `dept_id=103`
3. 用户列表正确筛选显示
