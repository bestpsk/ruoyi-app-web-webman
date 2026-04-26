# 选择部门后用户列表未筛选问题修复计划

## 问题描述

在用户管理页面，选择左侧部门树后，右侧用户列表没有根据部门进行筛选。

## 问题分析

### 当前实现

在 `SysUserService::selectUserList` 方法中（第 26-28 行）：

```php
if (!empty($params['dept_id'])) {
    $query->where('dept_id', $params['dept_id']);
}
```

这里只查询了指定部门ID的用户，**没有包含子部门**。

### 若依原始项目实现

在若依原始项目的 `SysUserMapper.xml` 中：

```xml
<if test="deptId != null and deptId != 0">
    AND (u.dept_id = #{deptId} OR u.dept_id IN ( 
        SELECT t.dept_id FROM sys_dept t WHERE find_in_set(#{deptId}, ancestors) 
    ))
</if>
```

这里使用了 `find_in_set` 函数来查询该部门及其所有子部门的用户。

### 数据库结构

`sys_dept` 表有 `ancestors` 字段，存储祖级列表：
- 部门 100（若依科技）：ancestors = '0'
- 部门 101（深圳总公司）：ancestors = '0,100'
- 部门 103（研发部门）：ancestors = '0,100,101'

## 解决方案

修改 `SysUserService::selectUserList` 方法，使其能够查询该部门及其所有子部门的用户。

## 修复步骤

### 步骤1：修改 SysUserService::selectUserList 方法

修改 `d:\fuchenpro\webman\app\service\SysUserService.php`：

```php
if (!empty($params['dept_id'])) {
    // 查询该部门及其所有子部门的用户
    $deptIds = \app\model\SysDept::where('dept_id', $params['dept_id'])
        ->orWhereRaw("FIND_IN_SET(?, ancestors)", [$params['dept_id']])
        ->pluck('dept_id')
        ->toArray();
    $query->whereIn('dept_id', $deptIds);
}
```

### 步骤2：验证修复

- 选择一个有子部门的部门，确认用户列表显示该部门及其子部门的所有用户
- 选择一个没有子部门的部门，确认用户列表只显示该部门的用户

## 预期结果

修复后，选择部门时：
1. 用户列表应该显示该部门及其所有子部门的用户
2. 筛选功能正常工作
