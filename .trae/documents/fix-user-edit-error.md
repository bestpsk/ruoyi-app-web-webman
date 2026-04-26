# 修复用户管理修改用户报错问题

## 问题分析

**错误信息**: `Error: Undefined array key "user_name"`

**根本原因**: 
- 前端发送的数据使用驼峰命名（如 `userName`, `nickName`, `userId`）
- 后端 `edit` 方法期望的是蛇形命名（如 `user_name`, `nick_name`, `user_id`）

**数据流程**:
1. `getInfo` 返回用户数据，`AjaxResult::success` 已将数据转换为驼峰命名
2. 前端接收数据后直接赋值给表单 `form.value = response.data`
3. 前端提交修改时，发送的是驼峰命名的数据
4. 后端 `edit` 方法中 `$data['user_name']` 获取不到值，因为实际键名是 `userName`

## 解决方案

在后端控制器中添加一个辅助方法，将驼峰命名转换为蛇形命名，然后在 `add` 和 `edit` 方法中使用。

## 实现步骤

### 步骤 1: 在 SysUserController 中添加数据转换方法

```php
private function convertToSnakeCase($data)
{
    $result = [];
    foreach ($data as $key => $value) {
        $newKey = is_string($key) ? $this->toSnakeCase($key) : $key;
        $result[$newKey] = $value;
    }
    return $result;
}

private function toSnakeCase($key)
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
}
```

### 步骤 2: 修改 add 方法

```php
public function add(Request $request)
{
    $data = $this->convertToSnakeCase($request->post());
    // ... 其余代码不变
}
```

### 步骤 3: 修改 edit 方法

```php
public function edit(Request $request)
{
    $data = $this->convertToSnakeCase($request->post());
    // ... 其余代码不变
}
```

## 文件修改清单

| 文件 | 修改内容 |
|------|----------|
| `app/controller/system/SysUserController.php` | 添加 `convertToSnakeCase` 方法，修改 `add` 和 `edit` 方法 |

## 测试验证

1. 打开用户管理页面
2. 点击修改按钮
3. 修改用户信息
4. 点击确定，验证修改成功
