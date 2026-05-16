# Service 目录添加注释计划

## 概述

为 `f:\fuchen\webman\app\service` 目录下的服务类添加 PHPDoc 注释。

## 当前状态

- **总文件数**: 48 个
- **已有注释**: 34 个文件
- **待添加注释**: 15 个文件

## 待添加注释的文件清单

### 业务管理服务（15个文件）

| 序号 | 文件名 | 功能说明 |
|------|--------|----------|
| 1 | `BizAttendanceConfigService.php` | 考勤配置服务 - 提供考勤配置的增删改查功能 |
| 2 | `BizAttendanceRecordService.php` | 考勤记录服务 - 提供考勤打卡记录的查询和统计功能 |
| 3 | `BizAttendanceRuleService.php` | 考勤规则服务 - 提供考勤规则的增删改查功能 |
| 4 | `BizInventoryService.php` | 库存服务 - 提供库存查询、调拨、预警等功能 |
| 5 | `BizOperationRecordService.php` | 操作记录服务 - 提供客户操作记录的查询功能 |
| 6 | `BizPlanService.php` | 方案计划服务 - 提供方案计划的增删改查功能 |
| 7 | `BizProductService.php` | 产品服务 - 提供产品的增删改查功能 |
| 8 | `BizRepaymentService.php` | 还款服务 - 提供还款记录的增删改查功能 |
| 9 | `BizScheduleService.php` | 日程服务 - 提供日程安排的增删改查功能 |
| 10 | `BizShipmentService.php` | 出货服务 - 提供出货单的增删改查、库存扣减等功能 |
| 11 | `BizStockCheckService.php` | 库存盘点服务 - 提供库存盘点的增删改查功能 |
| 12 | `BizStockInService.php` | 入库服务 - 提供入库单的增删改查、库存增加等功能 |
| 13 | `BizStockOutService.php` | 出库服务 - 提供出库单的增删改查、库存减少等功能 |
| 14 | `BizSupplierService.php` | 供应商服务 - 提供供应商的增删改查功能 |
| 15 | `BizWmsReportService.php` | 仓储报表服务 - 提供入库、出库、库存统计报表功能 |

## 注释规范

### 类注释格式

```php
/**
 * [服务名称]
 *
 * [功能描述]
 *
 * @package app\service
 * @author  system
 * @since   1.0.0
 */
```

### 方法注释格式

```php
/**
 * [方法描述]
 *
 * @param  array  $params  查询参数
 * @return mixed           查询结果
 */
```

## 实施步骤

### 步骤 1: 考勤模块服务（3个文件）
- [ ] `BizAttendanceConfigService.php` - 考勤配置服务
- [ ] `BizAttendanceRecordService.php` - 考勤记录服务
- [ ] `BizAttendanceRuleService.php` - 考勤规则服务

### 步骤 2: 方案与出货服务（2个文件）
- [ ] `BizPlanService.php` - 方案计划服务
- [ ] `BizShipmentService.php` - 出货服务

### 步骤 3: 库存管理服务（4个文件）
- [ ] `BizInventoryService.php` - 库存服务
- [ ] `BizStockCheckService.php` - 库存盘点服务
- [ ] `BizStockInService.php` - 入库服务
- [ ] `BizStockOutService.php` - 出库服务

### 步骤 4: 产品与供应商服务（2个文件）
- [ ] `BizProductService.php` - 产品服务
- [ ] `BizSupplierService.php` - 供应商服务

### 步骤 5: 其他业务服务（4个文件）
- [ ] `BizOperationRecordService.php` - 操作记录服务
- [ ] `BizRepaymentService.php` - 还款服务
- [ ] `BizScheduleService.php` - 日程服务
- [ ] `BizWmsReportService.php` - 仓储报表服务

## 预期结果

完成后，所有 48 个服务类文件都将包含规范的 PHPDoc 注释，包括：
- 类级别的功能说明
- `@package` 标签
- `@author` 标签
- `@since` 标签
