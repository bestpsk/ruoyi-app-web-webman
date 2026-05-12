# 修复 DB 类找不到错误

## 问题分析

### 错误信息
```
Error: Class "DB" not found
```

### 错误原因
在 [BizStockInService.php](webman/app/service/BizStockInService.php) 第36行使用了错误的类引用：

```php
// ❌ 错误：Laravel 风格
\DB::raw('...')

// ✅ 正确：webman 风格
use support\Db;
Db::raw('...')
```

**webman 框架使用 `support\Db`，不是 Laravel 的 `\DB` facade**

---

## 修复方案

### 方案：改用 webman 标准的 Db 类

**修改文件**：`webman/app/service/BizStockInService.php`

#### 修改1：添加正确的 use 语句
```php
// 在文件顶部添加
use support\Db;
```

#### 修改2：修复列表查询中的 raw 表达式
```php
// 第36行改为：
->paginate($pageSize, ['*', Db::raw('(SELECT MIN(expiry_date) FROM biz_stock_in_item WHERE biz_stock_in_item.stock_in_id = biz_stock_in.stock_in_id) as earliest_expiry')], 'page', $pageNum);
```

---

## 关于"用模型访问数据库"

您说得对！统一用模型访问数据库更好：
- **类型安全**：IDE 有代码提示
- **可维护性**：逻辑集中在 Model/Service 层
- **一致性**：避免散落的 SQL

当前项目已经在使用 Eloquent Model（BizStockIn、BizStockInItem 等），只需要修复这个 DB 引用错误即可。
