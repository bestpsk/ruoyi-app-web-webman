# 添加角色页面 - 状态和菜单权限数据不显示问题修复计划（续）

## 问题根因

**`AjaxResult::success()` 方法的参数顺序问题！**

当前方法定义：
```php
public static function success($msg = '操作成功', $data = null)
```

控制器调用方式：
```php
return AjaxResult::success($data);  // $data 被当作 $msg 处理了！
```

这导致：
- `$data` 被当作消息字符串处理
- 返回的 JSON 中没有 `data` 字段
- 前端收到的响应格式错误，无法显示数据

## 解决方案

修改 `AjaxResult::success()` 方法，使其能够智能判断第一个参数是消息还是数据：
- 如果第一个参数是数组或对象，则认为是数据
- 如果第一个参数是字符串，则认为是消息

## 修复步骤

### 步骤1：修复 AjaxResult::success() 方法

修改 `d:\fuchenpro\webman\app\common\AjaxResult.php`：

```php
public static function success($msg = '操作成功', $data = null)
{
    // 智能判断：如果第一个参数是数组或对象，则认为是数据
    if (is_array($msg) || is_object($msg)) {
        $data = $msg;
        $msg = '操作成功';
    }
    
    $result = ['code' => 200, 'msg' => $msg];
    if ($data !== null) {
        if (is_array($data)) {
            $data = self::convertToCamelCase($data);
            if (self::isAssociative($data)) {
                $result = array_merge($result, $data);
            } else {
                $result['data'] = $data;
            }
        } elseif (is_object($data)) {
            $result['data'] = self::convertToCamelCase($data->toArray());
        } else {
            $result['data'] = $data;
        }
    }
    return json($result);
}
```

### 步骤2：验证修复

修复后，API 返回的数据格式将正确：
- 字典数据：`{ code: 200, msg: '操作成功', data: [{ dictLabel: '正常', dictValue: '0', ... }] }`
- 菜单树：`{ code: 200, msg: '操作成功', data: [{ id: 1, label: '系统管理', children: [...] }] }`

## 预期结果

修复后，添加角色页面应该：
1. 状态单选框显示"正常"和"停用"两个选项
2. 菜单权限树显示完整的菜单树结构
