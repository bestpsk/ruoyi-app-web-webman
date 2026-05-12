# 管理后台顾客页面添加"欠款"Tab计划

## 需求说明

在管理后台 `front` 的单个顾客详情页面（`sales/index.vue`）中，在"操作"Tab右侧添加"欠款"Tab，用于：
1. 显示该客户所有欠款的套餐列表
2. 选择欠款套餐进行还款操作
3. 还款后生成还款记录

**注意：这是在顾客详情页面的Tab中添加，不是添加左侧菜单的子菜单。**

## 现有结构分析

### 页面文件
- `d:\fuchenpro\front\src\views\business\sales\index.vue`
- 现有Tab顺序：开单(order) → 操作(operation) → 开单记录(orderRecord) → 操作记录(operationRecord)
- 新增Tab位置：操作(operation)右侧 → **欠款(arrears)**

### 已有后端代码（上一轮已创建）
- `webman/app/model/BizRepaymentRecord.php` ✅
- `webman/app/service/BizRepaymentService.php` ✅
- `webman/app/controller/business/BizRepaymentController.php` ✅
- `webman/sql/biz_repayment.sql` ✅

### 需要新增
- `front/src/api/business/repayment.js` - 管理后台还款API
- 路由配置 - 在 `webman/config/route.php` 中添加还款接口路由
- 修改 `front/src/views/business/sales/index.vue` - 添加欠款Tab

## 实现步骤

### 步骤1：创建管理后台还款API文件
- 文件：`front/src/api/business/repayment.js`
- 接口方法：
  - `getOwedPackages(customerId)` - 获取客户欠款套餐列表
  - `addRepayment(data)` - 创建还款记录
  - `listRepayment(params)` - 获取还款记录列表
  - `auditRepayment(repaymentId)` - 审核还款

### 步骤2：添加后端路由
- 文件：`webman/config/route.php`
- 添加还款相关路由：
  - `GET /business/repayment/list`
  - `GET /business/repayment/owedPackages`
  - `POST /business/repayment/add`
  - `POST /business/repayment/audit`
  - `POST /business/repayment/cancel`
  - `GET /business/repayment/{repaymentId}`

### 步骤3：修改管理后台顾客页面
- 文件：`front/src/views/business/sales/index.vue`
- 修改内容：
  1. 在 `<el-tabs>` 中"操作"Tab后面添加 `<el-tab-pane label="欠款" name="arrears">`
  2. 欠款Tab内容：
     - 欠款套餐列表表格（套餐名称、成交金额、已付金额、欠款金额、操作时间）
     - 每行有"还款"按钮
  3. 还款弹窗（el-dialog）：
     - 显示套餐信息
     - 输入还款金额
     - 选择支付方式
     - 输入备注
     - 确认还款按钮
  4. 还款记录列表（可选，在欠款Tab下方显示历史还款记录）
  5. 添加相关数据变量和方法

## 文件清单

### 新建文件
| 文件路径 | 说明 |
|---------|------|
| `front/src/api/business/repayment.js` | 管理后台还款API |

### 修改文件
| 文件路径 | 说明 |
|---------|------|
| `front/src/views/business/sales/index.vue` | 添加欠款Tab和还款功能 |
| `webman/config/route.php` | 添加还款路由 |
