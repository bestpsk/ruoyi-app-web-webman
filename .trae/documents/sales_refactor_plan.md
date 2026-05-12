# 销售开单模块重构计划

## 需求变更对比

### 开单Tab变更
| 项目 | 当前 | 重构后 |
|------|------|--------|
| 方案价格 | 单价，合计=价格×次数 | **总价**，单次价=方案价格÷次数 |
| 成交金额 | 手动输入 | **默认=方案价格**，可修改 |
| 对比照 | 有前后对比照 | **去掉**（只有操作才有对比照） |
| 是否我们操作 | 有 | **去掉**（通过操作Tab区分） |
| 生成套餐 | 生成"客户套餐" | 生成**"持卡记录"** |

### 操作Tab变更
| 项目 | 当前 | 重构后 |
|------|------|--------|
| 套餐来源 | 客户套餐表 | **持卡记录表**（同一张表，改名） |
| 操作时间 | 自动取当天 | **可选择操作时间** |
| 消耗金额 | 无 | **新增：消耗金额**（自动计算=成交金额÷次数×操作次数） |
| 操作人 | 自动取当前用户 | **可选择操作人** |
| 满意度 | 无 | **新增：满意度（星级1-5）** |
| 体验操作 | 无 | **新增：体验操作按钮** |

### 新增：体验操作
- 不从持卡记录中消耗次数和金额
- 字段：操作时间、操作项目、操作次数、体验价、操作人、满意度、顾客反馈、前后对比照、备注

---

## 数据库变更

### 1. 订单明细表 `biz_order_item` - 删除字段
```sql
ALTER TABLE `biz_order_item` DROP COLUMN `before_photo`;
ALTER TABLE `biz_order_item` DROP COLUMN `after_photo`;
ALTER TABLE `biz_order_item` DROP COLUMN `is_our_operation`;
```

### 2. 操作记录表 `biz_operation_record` - 新增字段
```sql
ALTER TABLE `biz_operation_record` ADD COLUMN `consume_amount` decimal(12,2) DEFAULT 0.00 COMMENT '消耗金额' AFTER `operation_quantity`;
ALTER TABLE `biz_operation_record` ADD COLUMN `satisfaction` tinyint DEFAULT NULL COMMENT '满意度(1-5星)' AFTER `customer_feedback`;
ALTER TABLE `biz_operation_record` ADD COLUMN `operation_type` char(1) NOT NULL DEFAULT '0' COMMENT '操作类型(0持卡操作1体验操作)' AFTER `record_id`;
ALTER TABLE `biz_operation_record` ADD COLUMN `trial_price` decimal(12,2) DEFAULT NULL COMMENT '体验价' AFTER `consume_amount`;
```

### 3. 客户套餐表改名（可选，建议保留表名，前端显示改名）
- 数据库表名保持 `biz_customer_package` 不变
- 前端显示改为"持卡记录"
- `package_name` 字段改为显示"持卡名称"

---

## 后端变更

### 1. Model变更
**BizOrderItem.php**: 从fillable中删除 `before_photo`, `after_photo`, `is_our_operation`

**BizOperationRecord.php**: fillable中添加 `consume_amount`, `satisfaction`, `operation_type`, `trial_price`

### 2. Service变更

**BizSalesOrderService.php**:
- `insertOrder()`: 方案总金额 = sum(plan_price)，不再乘以次数
- `generatePackage()`: 持卡记录名称改为 "客户姓名 + 日期 + 持卡记录"
- 持卡记录明细中增加 `unit_price`（单次价=方案价格÷次数）和 `plan_price`（方案总价）

**BizOperationRecordService.php**:
- `insertRecord()`: 
  - 持卡操作时自动计算消耗金额
  - 体验操作时不扣减持卡记录次数
  - 记录操作类型

**BizCustomerPackageService.php**: 无需变更

### 3. 套餐明细表 `biz_package_item` 新增字段
```sql
ALTER TABLE `biz_package_item` ADD COLUMN `unit_price` decimal(12,2) DEFAULT 0.00 COMMENT '单次价格' AFTER `product_name`;
ALTER TABLE `biz_package_item` ADD COLUMN `plan_price` decimal(12,2) DEFAULT 0.00 COMMENT '方案总价' AFTER `unit_price`;
```

---

## 前端变更

### 1. 开单Tab
- 删除"对比照"列
- 删除"我们操作"列
- 方案价格改为总价（不再乘以次数计算合计）
- 成交金额默认=方案价格（切换成交时自动填充）
- 合计行：方案合计 = sum(planPrice)，成交合计 = sum(dealAmount)

### 2. 操作Tab重构
布局改为：
```
[选择持卡记录下拉] [体验操作按钮]

--- 持卡操作（选择持卡记录后显示）---
持卡记录明细表格（品项/总次数/已用/剩余/单次价/选择）
选择品项后：
  操作时间 | 操作项目(只读)
  操作次数 | 消耗金额(自动计算)
  操作人(选择员工) | 满意度(星级)
  顾客反馈
  操作前对比照 | 操作后对比照
  备注
  [提交操作]

--- 体验操作（点击体验操作按钮后显示）---
  操作时间 | 操作项目(输入)
  操作次数 | 体验价(输入)
  操作人(选择员工) | 满意度(星级)
  顾客反馈
  操作前对比照 | 操作后对比照
  备注
  [提交体验操作]
```

### 3. 记录Tab
- 操作记录增加：消耗金额、满意度、操作类型（持卡/体验）

---

## 文件修改清单

### SQL
| 文件 | 修改 |
|------|------|
| `webman/sql/biz_sales.sql` | 更新建表语句 + ALTER语句 |

### 后端
| 文件 | 修改 |
|------|------|
| `webman/app/model/BizOrderItem.php` | 删除3个fillable字段 |
| `webman/app/model/BizOperationRecord.php` | 添加4个fillable字段 |
| `webman/app/model/BizPackageItem.php` | 添加2个fillable字段 |
| `webman/app/service/BizSalesOrderService.php` | 修改金额计算+持卡记录生成逻辑 |
| `webman/app/service/BizOperationRecordService.php` | 添加消耗金额计算+体验操作+满意度 |

### 前端
| 文件 | 修改 |
|------|------|
| `front/src/views/business/sales/index.vue` | 重构开单Tab+操作Tab |

---

## 实施步骤

1. 更新SQL脚本（ALTER语句）
2. 修改后端Model（3个文件）
3. 修改后端Service（2个文件）
4. 重构前端页面（1个文件）
