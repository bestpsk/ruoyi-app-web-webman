# AppV3 业务管理模块完整实施计划

## 概述
在 AppV3 中实现业务管理下6个模块的移动端页面，参考 Web 端功能合理规划移动端交互。

## 已完成
- ✅ 企业管理（列表页 + 表单页 + API）

---

## 模块1: 门店管理

### Web端功能分析
- 搜索：门店名称、所属企业、负责人、电话、状态
- 列表字段：门店名称、所属企业、负责人、电话、微信、地址、营业时间、年业绩、常来顾客数、服务员工、创建人、状态
- 表单字段：所属企业*、门店名称*、负责人、联系电话、微信、营业时间(时间范围)、地址、年业绩、常来顾客数、服务员工、状态、备注
- 关联：企业下拉选择、员工下拉选择

### 移动端页面规划

#### 文件清单
| 文件 | 说明 |
|------|------|
| `api/business/store.js` | 门店API |
| `pages/business/store/index.vue` | 门店列表页 |
| `pages/business/store/form.vue` | 门店表单页 |

#### 列表页功能
- 搜索：门店名称/负责人/电话
- 筛选：所属企业、状态
- 卡片展示：门店名称、所属企业、负责人、电话、营业时间、状态
- 操作：新增、查看、编辑、删除
- 点击电话可拨打

#### 表单页功能
- 所属企业（选择器，从企业列表加载）
- 门店名称*、负责人、联系电话
- 微信、地址
- 营业时间（开始时间-结束时间）
- 年业绩、常来顾客数
- 服务员工（选择器）
- 状态（正常/停用）、备注

---

## 模块2: 行程安排

### Web端功能分析
- 三个Tab：员工行程、企业排班、员工配置
- 日历网格视图（员工×日期矩阵）
- 拖拽选择日期范围批量添加行程
- 下店目的：爆卡/启动销售/售后服务/洽谈业务
- 行程状态：已预约/服务中/已完成/已取消
- 员工配置：是否可排班、休息日期设置

### 移动端页面规划（简化适配）

#### 文件清单
| 文件 | 说明 |
|------|------|
| `api/business/schedule.js` | 行程API |
| `api/business/employeeConfig.js` | 员工配置API |
| `pages/business/schedule/index.vue` | 行程列表页 |
| `pages/business/schedule/form.vue` | 行程表单页 |

#### 列表页功能（移动端简化为列表模式）
- 顶部：年月选择器
- 筛选：员工姓名、企业名称、下店目的、状态
- 列表模式：按日期分组展示行程卡片
- 每条行程显示：日期、员工、企业、目的、状态
- 操作：新增行程、编辑、删除
- 下拉切换月份

#### 表单页功能
- 员工选择（选择器）
- 企业选择（选择器）
- 开始日期*、结束日期*
- 下店目的*（选择器：爆卡/启动销售/售后服务/洽谈业务）
- 状态（已预约/服务中/已完成/已取消）
- 备注

> 注：Web端的日历网格拖拽功能在移动端不适用，改为按日期分组的列表视图

---

## 模块3: 销售开单

### Web端功能分析
- 顶部：选择企业 → 选择门店
- 左侧：客户列表（搜索、新增）
- 右侧4个Tab：开单、操作、开单记录、操作记录
- 开单：选择套餐项目、填写数量/单价/备注 → 提交订单
- 操作：记录操作类型和内容
- 订单状态：待审核/企业审核/财务审核/已完成/已取消

### 移动端页面规划（分步流程）

#### 文件清单
| 文件 | 说明 |
|------|------|
| `api/business/salesOrder.js` | 销售订单API |
| `api/business/customer.js` | 客户API |
| `api/business/customerPackage.js` | 客户套餐API |
| `api/business/operationRecord.js` | 操作记录API |
| `pages/business/sales/index.vue` | 销售开单主页 |
| `pages/business/sales/order.vue` | 开单页面 |
| `pages/business/sales/operation.vue` | 添加操作页面 |

#### 主页功能
- 顶部：企业选择 → 门店选择（联动）
- 客户列表：搜索客户、新增客户
- 点击客户进入详情：开单/操作/记录三个Tab

#### 开单页面
- 显示客户已有套餐和项目
- 选择项目、填写数量/单价/备注
- 提交订单

#### 操作页面
- 选择操作类型
- 填写操作内容
- 提交操作记录

---

## 模块4: 项目操作

### Web端功能分析
- 操作记录API：list/add/del
- 关联销售开单中的操作Tab

### 移动端页面规划

#### 文件清单
| 文件 | 说明 |
|------|------|
| `pages/business/operation/index.vue` | 操作记录列表页 |

#### 列表页功能
- 筛选：客户名称、操作类型、日期范围
- 卡片展示：客户、操作类型、操作内容、操作人、时间
- 新增操作（跳转到销售开单的操作页面）

> 注：项目操作与销售开单强关联，独立页面主要展示操作记录列表

---

## 模块5: 订单管理

### Web端功能分析
- 订单列表：企业审核、财务审核流程
- 订单字段：订单编号、客户、门店、金额、状态、创建时间
- 状态流转：待审核 → 企业审核 → 财务审核 → 已完成

### 移动端页面规划

#### 文件清单
| 文件 | 说明 |
|------|------|
| `pages/business/order/index.vue` | 订单列表页 |
| `pages/business/order/detail.vue` | 订单详情页 |

#### 列表页功能
- 搜索：订单编号/客户名称
- 筛选：订单状态、日期范围
- 卡片展示：订单编号、客户、门店、金额、状态标签、创建时间
- 状态标签颜色区分

#### 详情页功能
- 订单基本信息
- 订单项目列表
- 审核操作（企业审核/财务审核按钮）
- 操作记录列表

---

## 实施步骤（按优先级排序）

### 第1步: 创建所有API文件
1. `api/business/store.js`
2. `api/business/schedule.js`
3. `api/business/employeeConfig.js`
4. `api/business/salesOrder.js`
5. `api/business/customer.js`
6. `api/business/customerPackage.js`
7. `api/business/operationRecord.js`

### 第2步: 门店管理页面
1. `pages/business/store/index.vue` - 列表页
2. `pages/business/store/form.vue` - 表单页

### 第3步: 行程安排页面
1. `pages/business/schedule/index.vue` - 列表页
2. `pages/business/schedule/form.vue` - 表单页

### 第4步: 销售开单页面
1. `pages/business/sales/index.vue` - 主页
2. `pages/business/sales/order.vue` - 开单页
3. `pages/business/sales/operation.vue` - 操作页

### 第5步: 项目操作页面
1. `pages/business/operation/index.vue` - 列表页

### 第6步: 订单管理页面
1. `pages/business/order/index.vue` - 列表页
2. `pages/business/order/detail.vue` - 详情页

### 第7步: 路由配置和导航更新
1. 更新 `pages.json` 添加所有新页面路由
2. 更新 `pages/work/index.vue` 中 businessList 的 path

---

## 文件总清单

| 序号 | 文件路径 | 操作 |
|------|----------|------|
| 1 | `api/business/store.js` | 新建 |
| 2 | `api/business/schedule.js` | 新建 |
| 3 | `api/business/employeeConfig.js` | 新建 |
| 4 | `api/business/salesOrder.js` | 新建 |
| 5 | `api/business/customer.js` | 新建 |
| 6 | `api/business/customerPackage.js` | 新建 |
| 7 | `api/business/operationRecord.js` | 新建 |
| 8 | `pages/business/store/index.vue` | 新建 |
| 9 | `pages/business/store/form.vue` | 新建 |
| 10 | `pages/business/schedule/index.vue` | 新建 |
| 11 | `pages/business/schedule/form.vue` | 新建 |
| 12 | `pages/business/sales/index.vue` | 新建 |
| 13 | `pages/business/sales/order.vue` | 新建 |
| 14 | `pages/business/sales/operation.vue` | 新建 |
| 15 | `pages/business/operation/index.vue` | 新建 |
| 16 | `pages/business/order/index.vue` | 新建 |
| 17 | `pages/business/order/detail.vue` | 新建 |
| 18 | `pages.json` | 修改 |
| 19 | `pages/work/index.vue` | 修改 |

## UI设计规范

所有业务页面统一风格：
- 导航栏：蓝色背景 `#3D6DF7`，白色文字
- 搜索区：蓝色渐变背景 + 白色搜索框 + 筛选按钮
- 列表区：卡片式布局，灰色背景 `#F5F7FA`
- 浮动按钮：蓝色渐变圆形 + 号
- 卡片样式：白色圆角卡片 + 阴影
- 状态标签：绿色正常/红色停用/蓝色进行中/灰色已取消
