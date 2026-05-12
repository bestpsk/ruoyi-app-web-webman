# 修复员工配置职位不显示问题

## 问题分析

### 现象
员工配置列表中，部分员工的职位（postName）显示为空。

### 根因分析

**问题代码位置：** [BizEmployeeConfigService.php:10-45](file:///d:/fuchenpro/webman/app/service/BizEmployeeConfigService.php#L10-L45)

1. `biz_employee_config` 表的 `post_id` 和 `post_name` 字段在初始化时未填充
2. `sys_user` 表没有 `post_id` 字段，岗位是通过 `sys_user_post` 关联表获取的
3. 当前 `selectConfigList` 方法在循环中逐个查询岗位信息，效率低且可能遗漏

**数据库关系：**
```
sys_user (用户表)
    ↓ user_id
sys_user_post (用户岗位关联表)
    ↓ post_id  
sys_post (岗位表)
```

**当前初始化 SQL 问题：** [biz_schedule_upgrade.sql:47-64](file:///d:/fuchenpro/webman/sql/biz_schedule_upgrade.sql#L47-L64)
- 移除了 `post_id` 和 `post_name` 字段的初始化
- 导致员工配置表中岗位信息为空

## 修复方案

### 方案一：修改 Service 层查询（推荐）

修改 `selectConfigList` 方法，使用 JOIN 查询直接关联获取岗位信息：

```php
public function selectConfigList($params = [])
{
    $query = BizEmployeeConfig::query()
        ->leftJoin('sys_user_post as up', 'biz_employee_config.user_id', '=', 'up.user_id')
        ->leftJoin('sys_post as p', 'up.post_id', '=', 'p.post_id')
        ->select('biz_employee_config.*', 'p.post_id', 'p.post_name');

    // ... 查询条件

    return $query->orderBy('config_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
}
```

### 方案二：补充初始化 SQL

添加 SQL 脚本更新现有数据的岗位信息：

```sql
UPDATE biz_employee_config ec
JOIN sys_user_post up ON ec.user_id = up.user_id
JOIN sys_post p ON up.post_id = p.post_id
SET ec.post_id = p.post_id, ec.post_name = p.post_name
WHERE ec.post_id IS NULL OR ec.post_name IS NULL;
```

## 实施步骤

### 步骤 1：修改 BizEmployeeConfigService.php
- 修改 `selectConfigList` 方法，使用 JOIN 关联查询岗位信息
- 移除循环中逐个查询的低效代码

### 步骤 2：添加数据修复 SQL
- 创建 SQL 脚本更新现有员工配置的岗位信息

### 步骤 3：验证
- 刷新员工配置页面，确认职位正常显示

## 影响范围

- 仅影响员工配置列表查询
- 不影响其他功能
- 修改量小，风险低
