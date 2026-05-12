# 货品字典显示数字问题 - 深度修复计划

## 问题根因分析

### 发现的问题
通过深入排查代码，发现问题的真正原因：

**后端使用了 Redis 缓存字典数据！**

文件：[SysDictTypeService.php](webman/app/service/SysDictTypeService.php) 第69-86行：

```php
public static function selectDictDataByType($dictType)
{
    try {
        $redis = Redis::connection();
        $cacheKey = Constants::SYS_DICT_KEY . $dictType;
        $cached = $redis->get($cacheKey);  // ⚠️ 先查Redis缓存
        if ($cached) {
            return json_decode($cached, true);  // ⚠️ 有缓存就直接返回，不查数据库
        }
        
        // 只有缓存没有才查数据库
        $data = SysDictData::where('dict_type', $dictType)...
        $redis->set($cacheKey, json_encode($data));  // 存入缓存
        return $data;
    } catch (\Exception $e) {
        // ...
    }
}
```

### 问题链路
```
前端 useDict("biz_product_unit")
    ↓ 调用API
后端 /system/dict/data/type/biz_product_unit
    ↓ 查询方法
selectDictDataByType("biz_product_unit")
    ↓ ⚠️ 命中Redis缓存（旧数据为空）
返回 [] 空数组
    ↓
前端 el-select 显示 value 本身（数字5、1）
```

### 为什么之前修复没生效？
1. ✅ SQL脚本执行成功 → 数据库数据正确
2. ❌ 但 Redis 缓存未清除 → 后端仍返回旧缓存
3. 结果：前端拿到的还是空的字典数据

---

## 解决方案

### 方案：清除 Redis 缓存 + 确保数据库数据完整

#### 步骤1：创建PHP脚本清除缓存并验证数据

```php
<?php
// fix_dict_cache.php
require_once __DIR__ . '/vendor/autoload.php';

// 1. 连接数据库
$config = require __DIR__ . '/config/database.php';
$pdo = new PDO(...);

// 2. 清除Redis缓存
$redis = Redis::connection();
$keys = $redis->keys('sys:dict:*');
foreach ($keys as $key) {
    $redis->del($key);
}

// 3. 确保字典类型存在
INSERT IGNORE INTO sys_dict_type ...

// 4. 删除并重新插入字典数据（确保最新）
DELETE FROM sys_dict_data WHERE dict_type IN ('biz_product_unit', 'biz_product_spec');
INSERT INTO sys_dict_data ...;

// 5. 验证数据
SELECT * FROM sys_dict_data WHERE dict_type = 'biz_product_unit';
SELECT * FROM sys_dict_data WHERE dict_type = 'biz_product_spec';
```

#### 步骤2：执行脚本

#### 步骤3：刷新前端页面（可能需要硬刷新 Ctrl+Shift+R）

---

## 实施步骤清单

- [ ] **步骤1**: 创建修复脚本 `fix_dict_cache.php`
  - 包含Redis缓存清除逻辑
  - 包含字典数据重新插入逻辑
  - 包含数据验证逻辑

- [ ] **步骤2**: 执行修复脚本
  - 运行 PHP 脚本
  - 确认输出显示数据已正确插入

- [ ] **步骤3**: 验证修复效果
  - 用户刷新页面
  - 确认单位下拉框显示：箱/件/套/罐/盒/袋/包
  - 确认规格下拉框显示：支/瓶/件/套/片/个

---

## 预期结果

修复后的效果：
- 单位下拉框：显示 "箱、件、套、罐、盒、袋、包" （默认选中"盒"）
- 规格下拉框：显示 "支、瓶、件、套、片、个" （默认选中"支"）
- 包装数量提示：换算 1盒 = 10支
