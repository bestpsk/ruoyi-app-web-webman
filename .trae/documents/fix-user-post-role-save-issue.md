# 修复用户岗位和角色保存失败问题

## 问题分析

### 现象
修改用户的岗位和角色后，点击确定没有保存成功。

### 根因分析

**问题代码位置：** [index.vue:511-537](file:///d:/fuchenpro/front/src/views/system/user/index.vue#L511-L537)

```javascript
function submitForm() {
  proxy.$refs["userRef"].validate(valid => {
    if (valid) {
      const submitData = { ...form.value }
      delete submitData.dept
      delete submitData.roles
      delete submitData.posts
      delete submitData.postIds    // ❌ 错误：删除了岗位ID
      delete submitData.roleIds    // ❌ 错误：删除了角色ID
      // ...
    }
  })
}
```

**问题原因：**
前端在提交表单时，错误地删除了 `postIds` 和 `roleIds` 字段，导致后端无法接收到岗位和角色信息。

**后端期望的字段：** [SysUserService.php:94-117](file:///d:/fuchenpro/webman/app/service/SysUserService.php#L94-L117)
```php
public function updateUser($data)
{
    // ...
    if (!empty($data['role_ids'])) {   // 需要接收 role_ids
        $this->insertUserRole($userId, $data['role_ids']);
    }
    if (!empty($data['post_ids'])) {   // 需要接收 post_ids
        $this->insertUserPost($userId, $data['post_ids']);
    }
    // ...
}
```

## 修复方案

### 修改文件
`d:\fuchenpro\front\src\views\system\user\index.vue`

### 修改内容

将 `submitForm` 函数中的删除语句：

```javascript
delete submitData.dept
delete submitData.roles
delete submitData.posts
delete submitData.postIds    // 删除这行
delete submitData.roleIds    // 删除这行
```

修改为：

```javascript
delete submitData.dept
delete submitData.roles
delete submitData.posts
// 保留 postIds 和 roleIds，用于后端保存岗位和角色
```

## 验证步骤

1. 修改前端代码
2. 刷新页面
3. 进入系统管理 -> 用户管理
4. 编辑一个用户，修改岗位和角色
5. 点击确定，确认保存成功

## 影响范围

- 仅影响用户编辑功能
- 不影响其他功能
- 修改量小，风险低
