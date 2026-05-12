# 开单记录和操作记录查询条件优化计划

## 需求分析

### 1. 开单记录查询条件优化
- **日期选择器宽度调整**：当前240px太宽，缩小至200px
- **订单状态bug修复**：提交订单时直接设置orderStatus为'1'（已成交）是错误的，应该设置为'0'（待确认），每个品项单独判断是否成交（品项已有isDeal字段）

### 2. 操作记录查询条件添加
- **日期选择器宽度调整**：当前240px太宽，缩小至200px
- **新增查询条件**：
  - 操作品项（模糊查询）
  - 操作人（下拉选择）
  - 满意度（1-5星选择）

## 实施步骤

### 步骤1：修复开单状态设置
**文件**：`d:/fuchenpro/front/src/views/business/sales/index.vue`

修改 `submitOrder` 函数，将订单状态改为待确认：
- 订单状态统一设置为'0'（待确认）
- 每个品项有自己的isDeal字段，单独判断是否成交

```javascript
orderStatus: '0',  // 改为待确认，而不是已成交
```

### 步骤2：调整开单记录日期选择器宽度
**文件**：`d:/fuchenpro/front/src/views/business/sales/index.vue`

将日期选择器宽度从 `width: 240px` 改为 `width: 200px`

### 步骤3：调整操作记录日期选择器宽度
**文件**：`d:/fuchenpro/front/src/views/business/sales/index.vue`

将日期选择器宽度从 `width: 240px` 改为 `width: 200px`

### 步骤4：添加操作记录查询条件变量
**文件**：`d:/fuchenpro/front/src/views/business/sales/index.vue`

添加响应式变量：
```javascript
const operationRecordProductName = ref('')
const operationRecordOperatorUserId = ref(null)
const operationRecordSatisfaction = ref(null)
```

### 步骤5：添加操作记录查询条件UI
**文件**：`d:/fuchenpro/front/src/views/business/sales/index.vue`

在操作记录标签页添加三个查询条件：
- 操作品项：输入框，支持模糊查询
- 操作人：下拉选择，使用userOptions
- 满意度：下拉选择，1-5星

### 步骤6：修改操作记录查询函数
**文件**：`d:/fuchenpro/front/src/views/business/sales/index.vue`

修改 `loadOperationRecords` 函数，添加新的查询参数

### 步骤7：后端添加操作记录查询条件支持
**文件**：`d:/fuchenpro/webman/app/service/BizOperationRecordService.php`

在 `selectRecordList` 方法中添加：
- product_name：模糊查询
- operator_user_id：精确查询
- satisfaction：精确查询

## 涉及文件

| 文件 | 修改内容 |
|------|----------|
| front/src/views/business/sales/index.vue | 前端UI和逻辑修改 |
| webman/app/service/BizOperationRecordService.php | 后端查询条件支持 |
