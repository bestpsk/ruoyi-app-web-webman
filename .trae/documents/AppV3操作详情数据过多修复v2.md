# AppV3 操作详情数据过多问题修复计划（最终版）

## 问题描述
AppV3 操作详情页面显示8个订单项目，但Web端操作记录只显示2个项目。数据不一致。

## 根因分析

### 数据流程
```
AppV3首页 → biz_customer_archive(档案表) → source_id → biz_operation_record(操作记录)
         → 点击操作 → detail.vue?id=source_id&type=operation
         → GET /business/operation/{id}
         → getRecordDetailById($id)
         → 按 package_no 查询同批次 → 返回 items 数组
```

### 核心问题

**后端查询逻辑**：[BizOperationRecordService.php:116-121](file:///d:/fuchenpro/webman/app/service/BizOperationRecordService.php#L116-L121)

```php
$batchRecords = BizOperationRecord::where('package_no', $record->package_no)
    ->where('customer_id', $record->customer_id)
    ->orderBy('record_id', 'asc')
    ->get();
```

`package_no` 是持卡编号（如 `PK202605100001`），对应的是**同一个套餐**。如果用户在不同时间对同一个套餐进行了4次操作（每次2个品项），查询会返回**所有8条**操作记录，而不是**当前这一次**的2条。

### Web端为什么只显示2个
Web端操作记录表格是**每条记录一行**，不按批次分组，所以看起来是正确的数量。

### 档案表的合并逻辑
[BizCustomerArchiveService.php:118-122](file:///d:/fuchenpro/webman/app/service/BizCustomerArchiveService.php#L118-L122)：同一客户、同一天、同一操作人的操作会合并到同一条档案记录中，`plan_items` 字段存储了合并后的项目列表。

但档案合并逻辑也有问题：如果同一客户、同一天、同一操作人对同一套餐进行了多次操作，这些操作会被合并到同一条档案中，导致 `plan_items` 包含了所有操作的项目，而不是只包含当前这一次操作的项目。

## 修复方案

### 方案：添加 operation_batch_id 字段（推荐✅）

这是最可靠的方案，为同一次操作提交的所有记录分配相同的批次ID。

#### 修改清单

**1. 数据库添加字段**
```sql
ALTER TABLE `biz_operation_record` ADD COLUMN `operation_batch_id` varchar(32) DEFAULT NULL COMMENT '操作批次ID' AFTER `package_no`;
CREATE INDEX idx_operation_batch_id ON `biz_operation_record`(`operation_batch_id`);
```

**2. 修改 BizOperationRecord 模型** - 添加 `operation_batch_id` 到 `$fillable`

**3. 修改 BizOperationRecordService::insertRecord()** - 生成并设置 batch_id

**4. 修改前端提交逻辑** - `sales/index.vue` 的 `submitOperation()` 方法，为同一次操作的所有品项生成相同的 batch_id

**5. 修改 BizOperationRecordService::getRecordDetailById()** - 按 `operation_batch_id` 查询同批次记录

**6. 补充历史数据** - 为已有操作记录按 `create_time` + `customer_id` + `operator_user_id` 分组补充 batch_id

## 实施步骤

### 步骤1：数据库添加字段
执行 SQL 添加 `operation_batch_id` 字段和索引

### 步骤2：修改后端模型
在 `BizOperationRecord.php` 的 `$fillable` 数组中添加 `'operation_batch_id'`

### 步骤3：修改后端 Service - insertRecord
在 `insertRecord()` 方法中，如果传入数据没有 `operation_batch_id`，则自动生成一个

### 步骤4：修改前端提交逻辑
在 `sales/index.vue` 的 `submitOperation('0')` 中，为所有品项生成相同的 batch_id

### 步骤5：修改后端 Service - getRecordDetailById
使用 `operation_batch_id` 查询同批次记录，替代按 `package_no` 查询

### 步骤6：补充历史数据
为已有操作记录补充 `operation_batch_id`

### 步骤7：重启服务并测试

## 风险评估
- **风险等级**：中 🟡（涉及数据库表结构变更）
- **影响范围**：操作记录相关功能
- **回滚方案**：删除 `operation_batch_id` 字段，恢复原始查询逻辑
