# 进销存管理系统实施计划

## 一、功能规划总览

根据美容院院线产品公司的业务特点，规划以下菜单结构和功能模块：

### 菜单结构

```
进销存管理 (wms)                          -- 一级目录菜单
├── 供货商管理 (supplier)                  -- 供货商CRUD
├── 货品管理 (product)                     -- 货品档案CRUD
├── 入库管理 (stockIn)                     -- 入库单主从表
├── 出库管理 (stockOut)                    -- 出库单主从表
├── 库存查看 (inventory)                   -- 实时库存查询
├── 库存盘点 (stockCheck)                  -- 盘点单主从表
└── 进销存报表 (report)                    -- 统计报表
```

---

## 二、数据库表设计

### 1. 供货商表 `biz_supplier`

| 字段 | 类型 | 说明 |
|------|------|------|
| supplier_id | bigint PK | 供货商ID |
| supplier_name | varchar(100) | 供货商名称 |
| contact_person | varchar(50) | 联系人 |
| contact_phone | varchar(20) | 联系电话 |
| address | varchar(255) | 地址 |
| cooperation_start_date | date | 合作起始日期 |
| status | char(1) | 状态(0正常 1停用) |
| remark | text | 备注 |
| create_by / create_time / update_by / update_time | - | 审计字段 |

### 2. 货品表 `biz_product`

| 字段 | 类型 | 说明 |
|------|------|------|
| product_id | bigint PK | 货品ID |
| product_name | varchar(100) | 品名 |
| product_code | varchar(50) | 货品编码（唯一） |
| supplier_id | bigint | 供货商ID |
| spec | varchar(100) | 规格 |
| category | char(1) | 类别(1院装-面部 2院装-身体 3仪器-面部 4仪器-身体 5家居-面部 6家居-身体) |
| unit | varchar(20) | 单位（如：盒、瓶、支、套） |
| purchase_price | decimal(10,2) | 进货价 |
| sale_price | decimal(10,2) | 出货价 |
| warn_qty | int | 库存预警数量（低于此值预警） |
| status | char(1) | 状态(0正常 1停用) |
| remark | text | 备注 |
| create_by / create_time / update_by / update_time | - | 审计字段 |

**字典类型**：`biz_product_category`（货品类别）
- 1 = 院装-面部 (primary)
- 2 = 院装-身体 (success)
- 3 = 仪器-面部 (warning)
- 4 = 仪器-身体 (danger)
- 5 = 家居-面部 (info)
- 6 = 家居-身体 (default)

### 3. 库存表 `biz_inventory`

| 字段 | 类型 | 说明 |
|------|------|------|
| inventory_id | bigint PK | 库存ID |
| product_id | bigint UK | 货品ID（唯一索引，一个货品一条库存记录） |
| quantity | int | 当前库存数量 |
| warn_qty | int | 预警数量 |
| last_stock_in_time | datetime | 最后入库时间 |
| last_stock_out_time | datetime | 最后出库时间 |
| create_time / update_time | - | 时间字段 |

### 4. 入库单主表 `biz_stock_in`

| 字段 | 类型 | 说明 |
|------|------|------|
| stock_in_id | bigint PK | 入库单ID |
| stock_in_no | varchar(30) | 入库单号（自动生成，如 RK20260428001） |
| stock_in_type | char(1) | 入库类型(1采购入库 2退货入库 3其他入库) |
| supplier_id | bigint | 供货商ID |
| total_quantity | int | 总数量 |
| total_amount | decimal(12,2) | 总金额 |
| stock_in_date | date | 入库日期 |
| operator_id | bigint | 操作人ID |
| operator_name | varchar(50) | 操作人姓名 |
| status | char(1) | 状态(0待确认 1已确认) |
| remark | text | 备注 |
| create_by / create_time / update_by / update_time | - | 审计字段 |

**字典类型**：`biz_stock_in_type`（入库类型）
- 1 = 采购入库 (primary)
- 2 = 退货入库 (warning)
- 3 = 其他入库 (info)

### 5. 入库单明细表 `biz_stock_in_item`

| 字段 | 类型 | 说明 |
|------|------|------|
| item_id | bigint PK | 明细ID |
| stock_in_id | bigint | 入库单ID |
| product_id | bigint | 货品ID |
| product_name | varchar(100) | 货品名称（冗余） |
| spec | varchar(100) | 规格（冗余） |
| unit | varchar(20) | 单位（冗余） |
| quantity | int | 入库数量 |
| purchase_price | decimal(10,2) | 进货单价 |
| amount | decimal(10,2) | 金额（数量×单价） |
| remark | varchar(500) | 备注 |

### 6. 出库单主表 `biz_stock_out`

| 字段 | 类型 | 说明 |
|------|------|------|
| stock_out_id | bigint PK | 出库单ID |
| stock_out_no | varchar(30) | 出库单号（自动生成，如 CK20260428001） |
| stock_out_type | char(1) | 出库类型(1销售出库 2调拨出库 3其他出库) |
| enterprise_id | bigint | 出库企业ID |
| enterprise_name | varchar(100) | 出库企业名称（冗余） |
| responsible_id | bigint | 负责员工ID |
| responsible_name | varchar(50) | 负责员工姓名（冗余） |
| total_quantity | int | 总数量 |
| total_amount | decimal(12,2) | 总金额 |
| stock_out_date | date | 出库日期 |
| status | char(1) | 状态(0待确认 1已确认) |
| remark | text | 备注 |
| create_by / create_time / update_by / update_time | - | 审计字段 |

**字典类型**：`biz_stock_out_type`（出库类型）
- 1 = 销售出库 (primary)
- 2 = 调拨出库 (warning)
- 3 = 其他出库 (info)

### 7. 出库单明细表 `biz_stock_out_item`

| 字段 | 类型 | 说明 |
|------|------|------|
| item_id | bigint PK | 明细ID |
| stock_out_id | bigint | 出库单ID |
| product_id | bigint | 货品ID |
| product_name | varchar(100) | 货品名称（冗余） |
| spec | varchar(100) | 规格（冗余） |
| unit | varchar(20) | 单位（冗余） |
| quantity | int | 出库数量 |
| sale_price | decimal(10,2) | 出货单价 |
| amount | decimal(10,2) | 金额（数量×单价） |
| remark | varchar(500) | 备注 |

### 8. 盘点单主表 `biz_stock_check`

| 字段 | 类型 | 说明 |
|------|------|------|
| stock_check_id | bigint PK | 盘点单ID |
| stock_check_no | varchar(30) | 盘点单号（自动生成，如 PD20260428001） |
| check_date | date | 盘点日期 |
| total_quantity | int | 盘点总数量 |
| total_diff_quantity | int | 差异数量合计 |
| operator_id | bigint | 操作人ID |
| operator_name | varchar(50) | 操作人姓名 |
| status | char(1) | 状态(0待确认 1已确认) |
| remark | text | 备注 |
| create_by / create_time / update_by / update_time | - | 审计字段 |

### 9. 盘点单明细表 `biz_stock_check_item`

| 字段 | 类型 | 说明 |
|------|------|------|
| item_id | bigint PK | 明细ID |
| stock_check_id | bigint | 盘点单ID |
| product_id | bigint | 货品ID |
| product_name | varchar(100) | 货品名称（冗余） |
| spec | varchar(100) | 规格（冗余） |
| unit | varchar(20) | 单位（冗余） |
| system_quantity | int | 系统库存数量 |
| actual_quantity | int | 实际盘点数量 |
| diff_quantity | int | 差异数量（实际-系统） |
| remark | varchar(500) | 备注 |

---

## 三、后端文件清单

### Controller（位于 `app/controller/wms/`）

| 文件 | 类名 | 说明 |
|------|------|------|
| BizSupplierController.php | BizSupplierController | 供货商管理 |
| BizProductController.php | BizProductController | 货品管理 |
| BizStockInController.php | BizStockInController | 入库管理（含明细CRUD） |
| BizStockOutController.php | BizStockOutController | 出库管理（含明细CRUD） |
| BizInventoryController.php | BizInventoryController | 库存查看 |
| BizStockCheckController.php | BizStockCheckController | 库存盘点（含明细CRUD） |
| BizWmsReportController.php | BizWmsReportController | 进销存报表 |

### Service（位于 `app/service/`）

| 文件 | 类名 | 说明 |
|------|------|------|
| BizSupplierService.php | BizSupplierService | 供货商业务逻辑 |
| BizProductService.php | BizProductService | 货品业务逻辑 |
| BizStockInService.php | BizStockInService | 入库业务逻辑（含库存更新） |
| BizStockOutService.php | BizStockOutService | 出库业务逻辑（含库存更新） |
| BizInventoryService.php | BizInventoryService | 库存查询逻辑 |
| BizStockCheckService.php | BizStockCheckService | 盘点业务逻辑（含库存调整） |
| BizWmsReportService.php | BizWmsReportService | 报表统计逻辑 |

### Model（位于 `app/model/`）

| 文件 | 类名 | 对应表 |
|------|------|--------|
| BizSupplier.php | BizSupplier | biz_supplier |
| BizProduct.php | BizProduct | biz_product |
| BizStockIn.php | BizStockIn | biz_stock_in |
| BizStockInItem.php | BizStockInItem | biz_stock_in_item |
| BizStockOut.php | BizStockOut | biz_stock_out |
| BizStockOutItem.php | BizStockOutItem | biz_stock_out_item |
| BizInventory.php | BizInventory | biz_inventory |
| BizStockCheck.php | BizStockCheck | biz_stock_check |
| BizStockCheckItem.php | BizStockCheckItem | biz_stock_check_item |

### SQL脚本（位于 `sql/`）

| 文件 | 说明 |
|------|------|
| biz_wms.sql | 完整建表+字典+菜单+权限SQL |

### 路由（追加到 `config/route.php`）

所有路由前缀：`/wms/xxx`

---

## 四、核心业务逻辑

### 4.1 入库流程

```
1. 创建入库单（状态=待确认） + 入库明细
2. 确认入库：
   - 校验明细中货品是否存在
   - 更新 biz_inventory 表：quantity += 入库数量
   - 更新 last_stock_in_time
   - 更新入库单状态为已确认
   - 计算并更新 total_quantity / total_amount
```

### 4.2 出库流程

```
1. 创建出库单（状态=待确认） + 出库明细
2. 确认出库：
   - 校验库存是否充足（逐项检查 biz_inventory.quantity >= 出库数量）
   - 库存不足时返回错误提示
   - 更新 biz_inventory 表：quantity -= 出库数量
   - 更新 last_stock_out_time
   - 更新出库单状态为已确认
   - 计算并更新 total_quantity / total_amount
```

### 4.3 盘点流程

```
1. 创建盘点单，自动加载所有在售货品的系统库存数量
2. 填写实际盘点数量
3. 自动计算差异数量（diff_quantity = actual_quantity - system_quantity）
4. 确认盘点：
   - 根据 diff_quantity 调整 biz_inventory.quantity
   - 更新盘点单状态为已确认
```

### 4.4 库存预警

```
- 库存查看页面标识预警状态：quantity <= warn_qty 时显示预警
- 可提供库存预警列表接口
```

### 4.5 进销存报表

```
- 入库汇总：按时间范围、供货商、货品类别统计入库数量和金额
- 出库汇总：按时间范围、出库企业、货品类别统计出库数量和金额
- 库存周转：期初库存 + 入库 - 出库 = 期末库存
- 货品收发存明细：单个货品的出入库流水
```

---

## 五、路由设计

```
# 供货商管理
GET    /wms/supplier/list              供货商列表
GET    /wms/supplier/search            供货商搜索下拉
GET    /wms/supplier/{id}              供货商详情
POST   /wms/supplier                   新增供货商
PUT    /wms/supplier                   修改供货商
DELETE /wms/supplier/{ids}             删除供货商

# 货品管理
GET    /wms/product/list               货品列表
GET    /wms/product/search             货品搜索下拉
GET    /wms/product/{id}               货品详情
POST   /wms/product                    新增货品
PUT    /wms/product                    修改货品
DELETE /wms/product/{ids}              删除货品

# 入库管理
GET    /wms/stockIn/list               入库单列表
GET    /wms/stockIn/{id}               入库单详情（含明细）
POST   /wms/stockIn                    新增入库单（含明细）
PUT    /wms/stockIn                    修改入库单（含明细）
DELETE /wms/stockIn/{ids}              删除入库单
PUT    /wms/stockIn/confirm/{id}       确认入库

# 出库管理
GET    /wms/stockOut/list              出库单列表
GET    /wms/stockOut/{id}              出库单详情（含明细）
POST   /wms/stockOut                   新增出库单（含明细）
PUT    /wms/stockOut                   修改出库单（含明细）
DELETE /wms/stockOut/{ids}             删除出库单
PUT    /wms/stockOut/confirm/{id}      确认出库

# 库存查看
GET    /wms/inventory/list             库存列表
GET    /wms/inventory/warn             库存预警列表
GET    /wms/inventory/{productId}      单个货品库存详情

# 库存盘点
GET    /wms/stockCheck/list            盘点单列表
GET    /wms/stockCheck/{id}            盘点单详情（含明细）
POST   /wms/stockCheck                 新增盘点单（含明细）
PUT    /wms/stockCheck                 修改盘点单（含明细）
DELETE /wms/stockCheck/{ids}           删除盘点单
PUT    /wms/stockCheck/confirm/{id}    确认盘点

# 进销存报表
GET    /wms/report/stockInSummary      入库汇总
GET    /wms/report/stockOutSummary     出库汇总
GET    /wms/report/inventoryTurnover   库存周转
GET    /wms/report/productFlow         货品收发存明细
```

---

## 六、菜单与权限SQL设计

### 菜单层级

```
进销存管理 (M, parent_id=0, path=wms, icon=shopping)
├── 供货商管理 (C, path=supplier, component=wms/supplier/index, perms=wms:supplier:list)
│   ├── 供货商查询 (F, perms=wms:supplier:query)
│   ├── 供货商新增 (F, perms=wms:supplier:add)
│   ├── 供货商修改 (F, perms=wms:supplier:edit)
│   ├── 供货商删除 (F, perms=wms:supplier:remove)
│   └── 供货商导出 (F, perms=wms:supplier:export)
├── 货品管理 (C, path=product, component=wms/product/index, perms=wms:product:list)
│   ├── 货品查询 (F, perms=wms:product:query)
│   ├── 货品新增 (F, perms=wms:product:add)
│   ├── 货品修改 (F, perms=wms:product:edit)
│   ├── 货品删除 (F, perms=wms:product:remove)
│   └── 货品导出 (F, perms=wms:product:export)
├── 入库管理 (C, path=stockIn, component=wms/stockIn/index, perms=wms:stockIn:list)
│   ├── 入库查询 (F, perms=wms:stockIn:query)
│   ├── 入康新增 (F, perms=wms:stockIn:add)
│   ├── 入库修改 (F, perms=wms:stockIn:edit)
│   ├── 入库删除 (F, perms=wms:stockIn:remove)
│   ├── 入库确认 (F, perms=wms:stockIn:confirm)
│   └── 入库导出 (F, perms=wms:stockIn:export)
├── 出库管理 (C, path=stockOut, component=wms/stockOut/index, perms=wms:stockOut:list)
│   ├── 出库查询 (F, perms=wms:stockOut:query)
│   ├── 出库新增 (F, perms=wms:stockOut:add)
│   ├── 出库修改 (F, perms=wms:stockOut:edit)
│   ├── 出库删除 (F, perms=wms:stockOut:remove)
│   ├── 出库确认 (F, perms=wms:stockOut:confirm)
│   └── 出库导出 (F, perms=wms:stockOut:export)
├── 库存查看 (C, path=inventory, component=wms/inventory/index, perms=wms:inventory:list)
│   └── 库存导出 (F, perms=wms:inventory:export)
├── 库存盘点 (C, path=stockCheck, component=wms/stockCheck/index, perms=wms:stockCheck:list)
│   ├── 盘点查询 (F, perms=wms:stockCheck:query)
│   ├── 盘点新增 (F, perms=wms:stockCheck:add)
│   ├── 盘点修改 (F, perms=wms:stockCheck:edit)
│   ├── 盘点删除 (F, perms=wms:stockCheck:remove)
│   ├── 盘点确认 (F, perms=wms:stockCheck:confirm)
│   └── 盘点导出 (F, perms=wms:stockCheck:export)
└── 进销存报表 (C, path=report, component=wms/report/index, perms=wms:report:list)
    └── 报表导出 (F, perms=wms:report:export)
```

---

## 七、实施步骤

### 第1步：创建SQL脚本 `sql/biz_wms.sql`
- 9张业务表建表语句
- 4组字典类型+字典数据（货品类别、入库类型、出库类型、单据状态）
- 菜单数据（1个目录 + 7个页面菜单 + 按钮权限）
- 管理员角色菜单权限分配

### 第2步：创建9个Model文件
- 按照项目规范：继承 `support\Model`，指定 `$table`、`$primaryKey`、`$timestamps=false`、`$fillable`

### 第3步：创建7个Service文件
- 供货商/货品/库存查看：标准CRUD Service
- 入库Service：CRUD + 确认入库逻辑（更新库存）
- 出库Service：CRUD + 确认出库逻辑（校验库存+更新库存）
- 盘点Service：CRUD + 确认盘点逻辑（调整库存）
- 报表Service：各类统计查询

### 第4步：创建7个Controller文件
- 位于 `app/controller/wms/` 目录
- 遵循项目Controller规范

### 第5步：注册路由
- 在 `config/route.php` 中添加所有 `/wms/*` 路由

### 第6步：执行SQL脚本
- 运行 `biz_wms.sql` 创建表和菜单数据

---

## 八、关键设计说明

1. **主从表设计**：入库/出库/盘点均采用主表+明细表设计，一个单据可包含多个货品明细，符合实际业务场景
2. **库存实时表**：`biz_inventory` 独立维护实时库存，避免每次查询都汇总出入库记录
3. **冗余字段**：明细表中冗余货品名称、规格、单位等，避免货品信息变更后历史单据数据不一致
4. **出库关联企业和员工**：出库单关联 `enterprise_id`（对应已有的 `biz_enterprise`）和 `responsible_id`（负责员工）
5. **单据编号自动生成**：入库单 RK+日期+序号，出库单 CK+日期+序号，盘点单 PD+日期+序号
6. **确认机制**：入库/出库/盘点均有"待确认→已确认"流程，确认时才更新库存，待确认状态可修改/删除
7. **库存预警**：货品设置 `warn_qty`，库存查看页面可筛选预警货品
