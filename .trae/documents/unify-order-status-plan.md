# 统一订单状态 & 持卡明细显示逻辑重构

## 需求总结

1. **订单状态统一为3种**：未成交(0)、已成交(1)、已用完(2)
2. **未成交**：只创建记录登记方案，可写备注，不在持卡明细中显示
3. **已成交**：在持卡明细中显示，可被选择操作
4. **已用完**：持卡明细中默认不显示，点击"显示用完"才出现；如果所有卡项用完，客户卡片中在"已成交"左侧显示"已用完"tag
5. **持卡明细缺少实付和欠款数据列**
6. **企业审核和财务审核**：只展示，不参与实际逻辑

## 当前问题分析

### 状态定义混乱
- 数据库SQL：`biz_order_status` = 待确认(0)/已成交(1)/已取消(2)
- 数据库SQL：`biz_package_status` = 有效(0)/已用完(1)/已过期(2)/已退款(3)
- 代码中实际使用的状态值不一致，导致各种显示问题
- 企业审核会把 order_status 改为 '1'，package status 改为 '1'，但前端期望不同

### 核心矛盾
用户要求：**审核不参与逻辑**，成交/未成交由开单时决定。
当前代码：**审核参与逻辑**，审核后才变为"已成交"。

### 缺失数据
- `biz_customer_package` 表没有 `paid_amount` 和 `owed_amount` 字段
- `biz_order_item` 表没有 `paid_amount` 字段
- 持卡明细无法显示实付和欠款

---

## 修改计划

### 一、数据库变更

#### 1.1 修改字典数据
更新 `biz_order_status` 字典：
- 0 → 未成交
- 1 → 已成交
- 2 → 已用完（原"已取消"改为"已用完"）

更新 `biz_package_status` 字典：
- 0 → 未成交
- 1 → 已成交
- 2 → 已用完

#### 1.2 给 biz_customer_package 表增加字段
```sql
ALTER TABLE `biz_customer_package` ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '实付金额' AFTER `total_amount`;
ALTER TABLE `biz_customer_package` ADD COLUMN `owed_amount` decimal(12,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER `paid_amount`;
```

#### 1.3 给 biz_order_item 表增加字段
```sql
ALTER TABLE `biz_order_item` ADD COLUMN `paid_amount` decimal(12,2) DEFAULT 0.00 COMMENT '实付金额' AFTER `deal_amount`;
```

### 二、后端修改

#### 2.1 修改 Model - BizCustomerPackage
**文件：** `d:\fuchenpro\webman\app\model\BizCustomerPackage.php`
- 在 `$fillable` 中增加 `paid_amount`, `owed_amount`

#### 2.2 修改 Model - BizOrderItem
**文件：** `d:\fuchenpro\webman\app\model\BizOrderItem.php`
- 在 `$fillable` 中增加 `paid_amount`

#### 2.3 修改 Service - BizSalesOrderService
**文件：** `d:\fuchenpro\webman\app\service\BizSalesOrderService.php`

**insertOrder 方法：**
- 开单时根据是否有成交项目直接设置 orderStatus
- 有成交项目 → orderStatus='1'（已成交），同时生成 package（status='1'已成交）
- 无成交项目 → orderStatus='0'（未成交），不生成 package
- 生成 package 时写入 paid_amount 和 owed_amount

**enterpriseAudit 方法：**
- 只更新企业审核状态字段（enterprise_audit_status/enterprise_audit_by/enterprise_audit_time）
- **不再修改** order_status 和 package status
- 审核只做记录，不影响业务状态

**financeAudit 方法：**
- 保持不变，只更新财务审核状态字段

**generatePackage 方法：**
- 创建 package 时 status='1'（已成交），因为只有已成交才生成 package
- 写入 paid_amount 和 owed_amount 到 package
- 写入 paid_amount 到 order_item

#### 2.4 修改 Service - BizCustomerPackageService
**文件：** `d:\fuchenpro\webman\app\service\BizCustomerPackageService.php`

**selectPackagesByCustomer 方法：**
- 默认查询 status IN ('1', '2')（已成交 + 已用完），不显示未成交
- 支持传入 status 参数精确筛选

#### 2.5 修改 Controller - BizCustomerPackageController
**文件：** `d:\fuchenpro\webman\app\controller\business\BizCustomerPackageController.php`

**getByCustomer 方法：**
- 不传 status 时默认返回已成交和已用完的数据
- 使用 `$request->has('status')` 正确判断参数

#### 2.6 修改 Service - BizCustomerService
**文件：** `d:\fuchenpro\webman\app\service\BizCustomerService.php`

**searchCustomer 方法：**
- `has_deal` 判断逻辑改为：存在 order_status='1' 的订单即为已成交
- 增加 `all_exhausted` 判断：所有已成交 package 的 items 的 remaining_quantity 都为 0
- 增加 `deal_amount` 统计：客户总成交金额

### 三、Web端前端修改

#### 3.1 修改销售开单页面
**文件：** `d:\fuchenpro\front\src\views\business\sales\index.vue`

**客户卡片区域（约38-61行）：**
- `hasDeal` tag 逻辑改为：has_deal=true 显示"已成交"
- 如果 `all_exhausted`=true，在"已成交"左侧显示"已用完" tag
- 显示成交金额数据

**开单提交逻辑（约622-650行）：**
- submitOrder 中 orderStatus 根据是否有成交项目动态设置
- 有成交项目 orderStatus='1'，无成交项目 orderStatus='0'
- items 中需要传递 paidAmount

**持卡明细表格（约155-167行）：**
- 增加"实付金额"和"欠款金额"列
- 默认只显示 status='1'（已成交）的 package
- 已用完(status='2')的 package 默认隐藏，点击"显示用完"才出现
- allPackageItems computed 中加入 status 过滤逻辑

**loadPackageList 方法（约652-655行）：**
- 修改为不传 status 或传 status='1'，获取已成交的 package
- 需要同时获取已用完的数据用于"显示用完"功能

**开单记录 tab（约305-356行）：**
- 订单状态显示改为：未成交/已成交/已用完
- 企业审核和财务审核只展示，不影响订单状态

#### 3.2 修改订单管理页面
**文件：** `d:\fuchenpro\front\src\views\business\order\index.vue`

- 企业审核 switch 只更新审核状态，不改 order_status
- 订单状态字典改为：未成交/已成交/已用完

### 四、App端前端修改

#### 4.1 修改开单页面
**文件：** `d:\fuchenpro\AppV3\src\pages\business\sales\order.vue`

- 统一状态显示：getOrderStatusName 改为 { '0': '未成交', '1': '已成交', '2': '已用完' }
- getPackageStatusName 改为 { '0': '未成交', '1': '已成交', '2': '已用完' }
- 持卡明细中增加实付和欠款显示
- 已用完的套餐默认隐藏

### 五、字典数据更新SQL

```sql
-- 更新订单状态字典
DELETE FROM `sys_dict_data` WHERE `dict_type` = 'biz_order_status';
INSERT INTO `sys_dict_data` (`dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `create_by`, `create_time`) VALUES
('biz_order_status', '未成交', '0', 1, '0', 'admin', NOW(), NULL),
('biz_order_status', '已成交', '1', 2, '0', 'admin', NOW(), NULL),
('biz_order_status', '已用完', '2', 3, '0', 'admin', NOW(), NULL);

-- 更新套餐状态字典
DELETE FROM `sys_dict_data` WHERE `dict_type` = 'biz_package_status';
INSERT INTO `sys_dict_data` (`dict_type`, `dict_label`, `dict_value`, `dict_sort`, `status`, `create_by`, `create_time`) VALUES
('biz_package_status', '未成交', '0', 1, '0', 'admin', NOW(), NULL),
('biz_package_status', '已成交', '1', 2, '0', 'admin', NOW(), NULL),
('biz_package_status', '已用完', '2', 3, '0', 'admin', NOW(), NULL);
```

---

## 实施步骤

1. 执行数据库变更 SQL（增加字段 + 更新字典）
2. 修改后端 Model（增加 fillable 字段）
3. 修改后端 Service（BizSalesOrderService 核心逻辑重构）
4. 修改后端 Service（BizCustomerPackageService 查询逻辑）
5. 修改后端 Service（BizCustomerService 增加 all_exhausted 等字段）
6. 修改后端 Controller（BizCustomerPackageController 参数处理）
7. 修改 Web 端前端（sales/index.vue 持卡明细+客户卡片+状态逻辑）
8. 修改 Web 端前端（order/index.vue 审核逻辑调整）
9. 修改 App 端前端（order.vue 状态统一+持卡明细增强）

## 状态流转图（新逻辑）

```
开单时选择成交 → order_status='1'(已成交) + 生成package(status='1')
                  ↓
              可在持卡明细中操作
                  ↓
         所有items用完 → package.status='2'(已用完)
                  ↓
         持卡明细中默认隐藏，点"显示用完"可见

开单时未选成交 → order_status='0'(未成交) + 不生成package
                  ↓
              只做记录，持卡明细不显示
```

## 审核逻辑（新）

```
企业审核 → 只记录审核状态，不改 order_status 和 package.status
财务审核 → 只记录审核状态，不改 order_status 和 package.status
```
审核仅作为内部管理流程的记录，不影响客户可见的订单状态。
