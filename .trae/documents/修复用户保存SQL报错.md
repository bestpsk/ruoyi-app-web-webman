# 修复用户保存 SQL 报错

## 问题分析

### 错误信息
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'dept' in 'field list'
```

### 根本原因
后端 `getUser` 接口通过 `with(['dept', 'roles', 'posts'])` 返回用户数据时，会将关联对象一起返回。前端在修改用户后提交表单时，`form.value` 中包含这些关联对象：

- `dept` - 部门关联对象（JSON）
- `roles` - 角色关联数组（JSON）
- `posts` - 岗位关联数组（JSON）

这些不是 `sys_user` 表的字段，直接提交会导致 SQL 报错。

## 当前代码状态

文件：`front/src/views/system/user/index.vue` 第 511-537 行

当前已删除的字段：
- ✅ `dept`
- ✅ `roles`
- ✅ `postIds`
- ✅ `roleIds`

**遗漏的字段**：
- ❌ `posts` - 岗位关联对象

## 修复方案

### 方法1：明确列出需要删除的关联字段（推荐）

```js
function submitForm() {
  proxy.$refs["userRef"].validate(valid => {
    if (valid) {
      const submitData = { ...form.value }
      // 删除关联对象和非数据库字段
      delete submitData.dept       // 部门关联
      delete submitData.roles      // 角色关联
      delete submitData.posts       // 岗位关联
      delete submitData.postIds     // 岗位ID数组
      delete submitData.roleIds     // 角色ID数组
      // ... 提交逻辑
    }
  })
}
```

### 方法2：白名单方式（只保留数据库字段）

更安全的方式是只提取数据库表中的实际字段：

```js
// sys_user 表的实际字段
const USER_FIELDS = [
  'userId', 'deptId', 'userName', 'nickName', 'userType', 'email',
  'phonenumber', 'sex', 'avatar', 'password', 'status', 'delFlag',
  'loginIp', 'loginDate', 'pwdUpdateDate', 'createBy', 'createTime',
  'updateBy', 'updateTime', 'remark'
]

function submitForm() {
  proxy.$refs["userRef"].validate(valid => {
    if (valid) {
      // 只提取数据库字段，忽略所有关联对象
      const submitData = {}
      USER_FIELDS.forEach(field => {
        const camelKey = field.charAt(0).toLowerCase() + field.slice(1)
        if (form.value[camelKey] !== undefined) {
          submitData[camelKey] = form.value[camelKey]
        }
      })
      // ... 提交逻辑
    }
  })
}
```

## 修改文件

仅修改一个文件：
- `front/src/views/system/user/index.vue` - `submitForm()` 函数

## 实施步骤

1. 在 `submitForm()` 函数中添加 `delete submitData.posts`
2. 可选：使用白名单方式替代黑名单方式，更加安全
