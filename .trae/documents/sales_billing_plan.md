# 销售开单模块 - 完整功能设计方案（修订版）

## 业务背景

我们是**美容院线产品公司**，服务对象是美容院企业。我们会到美容院企业去给他们的顾客进行销售和操作。部分操作由我们完成，部分不是我们操作，因此业务上不能完全实现闭环（有些顾客不需要我们操作）。

---

## 一、数据库设计

### 1.1 客户表 `biz_customer`

| 字段 | 类型 | 说明 |
|------|------|------|
| customer_id | bigint PK | 客户ID |
| enterprise_id | bigint | 所属企业ID |
| enterprise_name | varchar(100) | 所属企业名称 |
| store_id | bigint | 所属门店ID |
| store_name | varchar(100) | 所属门店名称 |
| customer_name | varchar(50) | 客户姓名 |
| phone | varchar(20) | 联系电话 |
| wechat | varchar(50) | 微信 |
| gender | char(1) | 性别(0男1女2未知) |
| age | int | 年龄 |
| tag | varchar(100) | 客户标签(字典: biz_customer_tag) |
| remark | text | 备注 |
| status | char(1) | 状态(0正常1停用) |
| create_by | varchar(64) | 创建者 |
| create_time | datetime | 创建时间 |
| update_by | varchar(64) | 更新者 |
| update_time | datetime | 更新时间 |

> **变更**: 去掉`source`(客户来源)和`birthday`(生日)，增加`age`(年龄)，`tag`改为字典

### 1.2 销售订单表 `biz_sales_order`

| 字段 | 类型 | 说明 |
|------|------|------|
| order_id | bigint PK | 订单ID |
| order_no | varchar(30) | 订单编号(自动生成: SO+年月日+4位序号) |
| customer_id | bigint | 客户ID |
| customer_name | varchar(50) | 客户姓名 |
| enterprise_id | bigint | 企业ID |
| enterprise_name | varchar(100) | 企业名称 |
| store_id | bigint | 门店ID |
| store_name | varchar(100) | 门店名称 |
| total_amount | decimal(12,2) | 方案总金额 |
| deal_amount | decimal(12,2) | 成交总金额 |
| order_status | char(1) | 订单状态(0待确认1已成交2已取消) |
| enterprise_audit_status | char(1) | 企业审核(0未审核1已审核) |
| finance_audit_status | char(1) | 财务审核(0未审核1已审核) |
| enterprise_audit_by | varchar(64) | 企业审核人 |
| enterprise_audit_time | datetime | 企业审核时间 |
| finance_audit_by | varchar(64) | 财务审核人 |
| finance_audit_time | datetime | 财务审核时间 |
| creator_user_id | bigint | 开单员工ID |
| creator_user_name | varchar(50) | 开单员工姓名 |
| remark | text | 备注 |
| create_by | varchar(64) | 创建者 |
| create_time | datetime | 创建时间 |
| update_by | varchar(64) | 更新者 |
| update_time | datetime | 更新时间 |

> **变更**: 增加`enterprise_audit_status`/`finance_audit_status`及对应审核人和审核时间字段

### 1.3 订单明细表 `biz_order_item`

| 字段 | 类型 | 说明 |
|------|------|------|
| item_id | bigint PK | 明细ID |
| order_id | bigint | 订单ID |
| product_name | varchar(100) | 品项名称 |
| quantity | int | 次数 |
| plan_price | decimal(12,2) | 方案价格 |
| is_deal | tinyint | 是否成交(0否1是) |
| deal_amount | decimal(12,2) | 成交金额 |
| is_our_operation | tinyint | 是否我们操作(0否1是) |
| customer_feedback | varchar(500) | 顾客反馈 |
| before_photo | varchar(500) | 操作前对比照(多图逗号分隔) |
| after_photo | varchar(500) | 操作后对比照(多图逗号分隔) |
| remark | text | 备注 |
| create_time | datetime | 创建时间 |

> **变更**: 增加`is_our_operation`(是否我们操作)，因为有些顾客不需要我们操作

### 1.4 客户套餐表 `biz_customer_package`

| 字段 | 类型 | 说明 |
|------|------|------|
| package_id | bigint PK | 套餐ID |
| package_no | varchar(30) | 套餐编号(自动生成: PK+年月日+4位序号) |
| customer_id | bigint | 客户ID |
| customer_name | varchar(50) | 客户姓名 |
| order_id | bigint | 来源订单ID |
| order_no | varchar(30) | 来源订单编号 |
| enterprise_id | bigint | 企业ID |
| store_id | bigint | 门店ID |
| package_name | varchar(100) | 套餐名称 |
| total_amount | decimal(12,2) | 套餐总金额 |
| status | char(1) | 状态(0有效1已用完2已过期3已退款) |
| expire_date | date | 过期日期 |
| remark | text | 备注 |
| create_by | varchar(64) | 创建者 |
| create_time | datetime | 创建时间 |
| update_by | varchar(64) | 更新者 |
| update_time | datetime | 更新时间 |

### 1.5 套餐明细表 `biz_package_item`

| 字段 | 类型 | 说明 |
|------|------|------|
| package_item_id | bigint PK | 套餐明细ID |
| package_id | bigint | 套餐ID |
| product_name | varchar(100) | 品项名称 |
| total_quantity | int | 总次数 |
| used_quantity | int | 已用次数(默认0) |
| remaining_quantity | int | 剩余次数 |
| remark | varchar(500) | 备注 |

> **说明**: 一个套餐由多个品项+次数组成打包套餐，如"面部护理3次+身体按摩2次"

### 1.6 操作记录表 `biz_operation_record`

| 字段 | 类型 | 说明 |
|------|------|------|
| record_id | bigint PK | 记录ID |
| customer_id | bigint | 客户ID |
| customer_name | varchar(50) | 客户姓名 |
| package_id | bigint | 套餐ID |
| package_no | varchar(30) | 套餐编号 |
| package_item_id | bigint | 套餐明细ID |
| product_name | varchar(100) | 品项名称 |
| operation_quantity | int | 操作次数 |
| customer_feedback | varchar(500) | 顾客反馈 |
| before_photo | varchar(500) | 操作前对比照 |
| after_photo | varchar(500) | 操作后对比照 |
| operator_user_id | bigint | 操作员工ID |
| operator_user_name | varchar(50) | 操作员工姓名 |
| operation_date | date | 操作日期 |
| enterprise_id | bigint | 企业ID |
| store_id | bigint | 门店ID |
| remark | text | 备注 |
| create_by | varchar(64) | 创建者 |
| create_time | datetime | 创建时间 |

### 1.7 字典数据

| 字典类型 | 字典标签 | 字典值 |
|----------|----------|--------|
| biz_customer_tag | VIP | vip |
| biz_customer_tag | 普通 | normal |
| biz_customer_tag | 重点客户 | important |
| biz_customer_tag | 新客户 | new |
| biz_customer_tag | 待跟进 | follow |

---

## 二、页面设计

### 2.1 页面结构

```
销售开单 (business/sales/index.vue)
├── 顶部: 企业+门店选择栏(始终可见)
│   ├── 企业下拉选择
│   ├── 门店下拉选择(根据企业联动)
│   └── 快速创建门店按钮(无门店时显示)
│
├── 工作区(选择门店后显示)
│   ├── 左侧: 客户面板
│   │   ├── 搜索客户(姓名/电话)
│   │   ├── 客户列表(点击选中)
│   │   │   └── 显示: 姓名/电话/标签/年龄/有效套餐数
│   │   └── 新增客户按钮
│   │
│   └── 右侧: 操作面板(选中客户后显示)
│       ├── 客户信息栏(姓名/电话/标签/累计消费/有效套餐)
│       │
│       ├── Tab1: 开单
│       │   ├── 品项明细表格(可增加多行)
│       │   │   ├── 品项名称
│       │   │   ├── 次数
│       │   │   ├── 方案价格
│       │   │   ├── 是否成交(开关)
│       │   │   ├── 成交金额(成交时填写)
│       │   │   ├── 是否我们操作(开关)
│       │   │   ├── 顾客反馈
│       │   │   ├── 前后对比照(图片上传)
│       │   │   └── 备注
│       │   ├── 合计行(方案总金额、成交总金额)
│       │   └── 提交订单按钮
│       │
│       ├── Tab2: 操作(核销套餐)
│       │   ├── 选择套餐(下拉)
│       │   ├── 套餐明细(显示各品项剩余次数)
│       │   ├── 操作表单
│       │   │   ├── 选择品项
│       │   │   ├── 操作次数
│       │   │   ├── 顾客反馈
│       │   │   ├── 前后对比照(图片上传)
│       │   │   └── 备注
│       │   └── 提交操作按钮
│       │
│       └── Tab3: 记录
│           ├── 订单记录列表(含审核状态)
│           ├── 操作记录列表
│           └── 套餐信息
```

### 2.2 审核流程

```
开单提交 → 订单状态: 已成交
  → 企业审核: 未审核 → 已审核(合作企业确认)
  → 财务审核: 未审核 → 已审核(我方财务确认)

审核权限:
- 企业审核: 分配给企业相关角色的用户
- 财务审核: 分配给我方财务角色的用户
```

### 2.3 交互流程

```
用户进入页面
  → 选择企业(必选)
  → 选择门店(联动，必选)
    → 如果无门店 → 显示"创建门店"按钮 → 弹窗创建
  → 左侧显示客户列表
    → 搜索客户(姓名/电话)
    → 点击客户 → 右侧显示操作面板
    → 如果客户不存在 → 点击"新增客户" → 弹窗创建
  → 右侧操作
    → 开单Tab: 填写品项明细 → 标记是否成交/是否我们操作 → 提交
      → 已成交品项自动生成套餐(多个品项组成一个打包套餐)
    → 操作Tab: 选择套餐 → 选择品项 → 填写操作信息 → 提交 → 扣减套餐次数
    → 记录Tab: 查看历史订单(含审核状态)和操作记录
```

---

## 三、业务规则

### 3.1 套餐自动生成规则
- 订单中**已成交**的品项自动生成一个客户套餐
- 一个订单的已成交品项组成**一个打包套餐**（多个品项+次数）
- 套餐名称自动组合: 客户姓名 + 日期 + "套餐"
- 套餐明细中各品项的次数 = 订单明细中的次数
- 套餐总金额 = 订单成交总金额

### 3.2 套餐次数扣减规则
- 操作记录提交后，自动扣减对应套餐明细的剩余次数
- 剩余次数为0时，该品项标记为"已用完"
- 所有品项用完时，套餐状态改为"已用完"

### 3.3 审核规则
- 订单提交后默认: 企业未审核 + 财务未审核
- 企业审核通过后才能进行财务审核
- 审核通过后不可修改订单
- 审核状态在订单列表和记录Tab中显示

### 3.4 是否我们操作
- 订单明细中"是否我们操作"标记
- 标记为"我们操作"的品项才会在操作Tab中显示可核销
- 标记为"非我们操作"的品项只记录在套餐中，不产生操作记录

---

## 四、文件清单

### 4.1 数据库脚本
| 文件 | 说明 |
|------|------|
| `webman/sql/biz_sales.sql` | 建表+菜单+权限+字典SQL |

### 4.2 后端文件
| 文件 | 说明 |
|------|------|
| `webman/app/model/BizCustomer.php` | 客户模型 |
| `webman/app/model/BizSalesOrder.php` | 销售订单模型 |
| `webman/app/model/BizOrderItem.php` | 订单明细模型 |
| `webman/app/model/BizCustomerPackage.php` | 客户套餐模型 |
| `webman/app/model/BizPackageItem.php` | 套餐明细模型 |
| `webman/app/model/BizOperationRecord.php` | 操作记录模型 |
| `webman/app/service/BizCustomerService.php` | 客户服务 |
| `webman/app/service/BizSalesOrderService.php` | 销售订单服务(含套餐自动生成) |
| `webman/app/service/BizCustomerPackageService.php` | 客户套餐服务 |
| `webman/app/service/BizOperationRecordService.php` | 操作记录服务(含次数扣减) |
| `webman/app/controller/business/BizCustomerController.php` | 客户控制器 |
| `webman/app/controller/business/BizSalesOrderController.php` | 销售订单控制器(含审核) |
| `webman/app/controller/business/BizCustomerPackageController.php` | 客户套餐控制器 |
| `webman/app/controller/business/BizOperationRecordController.php` | 操作记录控制器 |
| `webman/config/route.php` | 添加路由(修改) |

### 4.3 前端文件
| 文件 | 说明 |
|------|------|
| `front/src/api/business/customer.js` | 客户API |
| `front/src/api/business/salesOrder.js` | 销售订单API(含审核接口) |
| `front/src/api/business/customerPackage.js` | 客户套餐API |
| `front/src/api/business/operationRecord.js` | 操作记录API |
| `front/src/views/business/sales/index.vue` | 销售开单主页面 |

---

## 五、实施步骤

### 第一阶段: 数据库和后端基础
1. 创建SQL脚本(6张表+菜单+权限+字典)
2. 创建6个Model
3. 创建4个Service
4. 创建4个Controller
5. 配置路由

### 第二阶段: 前端页面
6. 创建4个API文件
7. 创建销售开单主页面
   - 企业/门店选择区(联动+快速创建门店)
   - 客户面板(搜索+列表+新增弹窗)
   - 开单Tab(品项明细表格+是否我们操作+提交)
   - 操作Tab(套餐选择+操作表单+提交)
   - 记录Tab(订单历史含审核状态+操作历史+套餐信息)

### 第三阶段: 业务逻辑
8. 开单提交 → 自动生成打包套餐(已成交品项)
9. 操作提交 → 自动扣减套餐次数
10. 订单编号/套餐编号自动生成
11. 企业审核/财务审核功能
