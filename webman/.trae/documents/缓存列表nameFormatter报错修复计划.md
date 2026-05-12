# 缓存列表 nameFormatter 报错修复计划

## 一、问题现象

### 错误信息
```
TypeError: Cannot read properties of undefined (reading 'replace')
at Object.nameFormatter [as formatter] (list.vue:79:24)
```

### 页面状态
- 缓存列表：空白
- 键名列表：显示"暂无数据"
- 缓存内容：表单为空

## 二、根本原因分析

### 错误位置

[list.vue:222-224](file:///d:\fuchenpro\front\src\views\monitor\cache\list.vue#L222-L224)

```javascript
function nameFormatter(row) {
  return row.cacheName.replace(":", "")  // ❌ row.cacheName 是 undefined
}
```

### 数据结构不匹配

#### 前端表格配置（第30-36行）
```html
<el-table-column
  label="缓存名称"
  prop="cacheName"              <!-- 期望每行是一个对象，有 cacheName 属性 -->
  :formatter="nameFormatter"     <!-- formatter 接收整个 row 对象 -->
></el-table-column>
```

前端期望的数据格式：
```javascript
[
  { cacheName: "user:info", remark: "" },
  { cacheName: "system:config", remark: "" },
]
```

#### 后端实际返回的数据

[CacheController.php:91-107](file:///d:\fuchenpro\webman\app\controller\monitor\CacheController.php#L91-L107)

```php
public function getNames(Request $request)
{
    $redis = Redis::connection();
    $keys = $redis->keys('*');
    $names = [];
    foreach ($keys as $key) {
        $parts = explode(':', $key);
        if (count($parts) > 1) {
            $name = $parts[0] . ':' . $parts[1];
            if (!in_array($name, $names)) {
                $names[] = $name;  // ← 返回的是字符串数组！
            }
        }
    }
    return AjaxResult::success('', [
        'data' => $names  // ["user:info", "system:config"]
    ]);
}
```

后端返回的实际数据格式：
```json
{
  "code": 200,
  "msg": "",
  "data": ["user:info", "system:config"]  // 字符串数组！
}
```

### 问题链路

```
后端返回：["user:info", "system:config"]  （字符串数组）
         ↓
前端接收：response.data = ["user:info", "system:config"]
         ↓
赋值给 cacheNames.value = ["user:info", "system:config"]
         ↓
el-table 渲染每一行时：
  - 第1行：row = "user:info"  （字符串，不是对象）
  - 调用 nameFormatter("user:info")
  - 尝试访问 row.cacheName → undefined
  - 调用 undefined.replace() → 💥 TypeError!
```

## 三、解决方案

### 方案A：修改后端返回对象数组（推荐）

将后端的字符串数组改为对象数组，符合前端 el-table 的 prop 配置要求。

**修改 CacheController.php 的 getNames() 方法：**

```php
public function getNames(Request $request)
{
    $redis = Redis::connection();
    $keys = $redis->keys('*');
    $names = [];
    foreach ($keys as $key) {
        $parts = explode(':', $key);
        if (count($parts) > 1) {
            $name = $parts[0] . ':' . $parts[1];
            $exists = false;
            foreach ($names as $item) {
                if ($item['cacheName'] === $name) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $names[] = ['cacheName' => $name, 'remark' => ''];  // 对象格式
            }
        }
    }
    return AjaxResult::success('', [
        'data' => $names
    ]);
}
```

**返回格式变为：**
```json
{
  "code": 200,
  "msg": "",
  "data": [
    {"cacheName": "user:info", "remark": ""},
    {"cacheName": "system:config", "remark": ""}
  ]
}
```

### 方案B：修改前端适配字符串数组

如果不想改后端，可以修改前端的 el-table-column 配置和 formatter 函数。

但这样需要改动较多地方，且不符合 RuoYi 原版设计意图。

## 四、实施步骤

### 步骤1：修改CacheController.php的getNames方法

文件：`d:\fuchenpro\webman\app\controller\monitor\CacheController.php`
行号：91-107

将 `$names[] = $name;` 改为 `$names[] = ['cacheName' => $name, 'remark' => ''];`

同时修改去重逻辑以适配新格式。

### 步骤2：验证语法

运行 PHP 语法检查确保无错误。

### 步骤3：测试验证

重启Webman服务后刷新缓存列表页面，验证：
- 左侧缓存列表正常显示缓存名称
- 点击缓存名称后中间键名列表加载
- 点击键名后右侧缓存内容显示

## 五、预期效果

修复完成后：

✅ **左侧缓存列表**
- 显示所有Redis缓存名称（如 user:info、system:config）
- nameFormatter 正常工作，去除冒号显示（userinfo、systemconfig）
- 点击行可选中并触发键名列表加载

✅ **中间键名列表**
- 显示选中缓存下的所有键名
- keyFormatter 正常工作，去除缓存名称前缀

✅ **右侧缓存内容**
- 显示选中键的完整内容

## 六、技术说明

### 为什么会出现这个问题？

这是RuoYi-Vue原版代码与当前后端实现的**数据格式不匹配**导致的：

1. **RuoYi原版设计**：Java后端通常返回对象数组，因为Java是强类型语言，更倾向于使用实体类/DTO
2. **当前PHP实现**：PHP是弱类型语言，开发者倾向于直接返回简单的字符串数组
3. **el-table的要求**：当使用 `prop="cacheName"` 时，el-table 期望每行数据是一个对象

### 最佳实践建议

为了保持前后端一致性，建议：
- 后端API返回的数据应该符合前端组件的数据契约
- 使用明确的DTO/VO对象而非原始类型数组
- 保持与RuoYi框架的设计风格一致
