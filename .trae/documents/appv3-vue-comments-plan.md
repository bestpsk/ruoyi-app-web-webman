# AppV3 Vue 页面注释完善计划

## 项目概况

AppV3 项目共有 **35 个 Vue 文件**需要添加注释，当前所有文件均无注释（之前的注释因git回滚丢失）。

## 注释规范

### Vue 文件头注释格式（放在 `<script setup>` 或 `<script>` 开头）
```javascript
/**
 * @description 页面名称 - 页面简要说明
 * @description 详细描述页面的业务功能、核心交互和数据流
 */
```

### 函数注释格式
```javascript
/** 函数功能的具体描述（不要重复函数名） */
```

### 计算属性注释格式
```javascript
/** 计算属性的具体业务含义 */
```

### 注释原则
1. **描述具体作用**，不重复函数名（❌ `// 登录` → ✅ `// 验证表单后调用store登录接口，成功后获取用户信息并跳转首页`）
2. **说明业务含义**，不仅描述技术操作
3. **复杂逻辑加步骤说明**（如定位降级策略、金额计算公式、日期冲突检测）
4. **不修改任何功能代码**，仅添加注释
5. **Vue文件注释重点**：页面用途、核心函数、计算属性、事件处理函数

## 分批实施计划

### 第一批：核心页面（8个文件）

| 序号 | 文件路径 | 需要添加的注释 |
|------|---------|--------------|
| 1 | `App.vue` | 文件头 + initApp/checkLogin |
| 2 | `pages/index.vue` | 文件头 + loadHomeData/getSourceTypeLabel |
| 3 | `pages/login.vue` | 文件头 + handleLogin/pwdLogin/getCode |
| 4 | `pages/register.vue` | 文件头 + handleRegister/doRegister/getCode |
| 5 | `pages/work/index.vue` | 文件头 + 计算属性 + handleGridClick/goToPage |
| 6 | `pages/mine/index.vue` | 文件头 + 各跳转函数 |
| 7 | `pages/mine/setting/index.vue` | 文件头 + handleLogout |
| 8 | `pages/mine/pwd/index.vue` | 文件头 + submit校验逻辑 |

### 第二批：个人中心与公共页面（7个文件）

| 序号 | 文件路径 | 需要添加的注释 |
|------|---------|--------------|
| 9 | `pages/mine/info/index.vue` | 文件头 + getUser |
| 10 | `pages/mine/info/edit.vue` | 文件头 + getUser/submit |
| 11 | `pages/mine/help/index.vue` | 文件头 + handleText |
| 12 | `pages/mine/avatar/index.vue` | 文件头 + chooseImage/uploadImage |
| 13 | `pages/mine/about/index.vue` | 文件头 |
| 14 | `pages/common/webview/index.vue` | 文件头 + onLoad |
| 15 | `pages/common/textview/index.vue` | 文件头 + onLoad |

### 第三批：业务模块 - 销售管理（4个文件，逻辑最复杂）

| 序号 | 文件路径 | 需要添加的注释 |
|------|---------|--------------|
| 16 | `pages/business/sales/index.vue` | 文件头 + 持久化函数 + 选择函数 + 新增客户弹窗 |
| 17 | `pages/business/sales/operation.vue` | 文件头 + toggleItem/qtyChange/submitOperation/照片上传 |
| 18 | `pages/business/sales/order.vue` | 文件头 + 金额计算 + 订单提交 + 还款弹窗 |
| 19 | `pages/business/sales/archive.vue` | 文件头 + JSON解析 + 档案CRUD + 图片预览 |

### 第四批：业务模块 - 企业/门店/订单/操作（6个文件）

| 序号 | 文件路径 | 需要添加的注释 |
|------|---------|--------------|
| 20 | `pages/business/enterprise/index.vue` | 文件头 + getList/类型级别映射/CRUD跳转 |
| 21 | `pages/business/enterprise/form.vue` | 文件头 + loadDetail/submitForm/手机号校验 |
| 22 | `pages/business/store/index.vue` | 文件头 + getList/搜索防抖/CRUD跳转 |
| 23 | `pages/business/store/form.vue` | 文件头 + loadDetail/submitForm/企业选择 |
| 24 | `pages/business/order/index.vue` | 文件头 + getList/状态映射 |
| 25 | `pages/business/order/detail.vue` | 文件头 + loadDetail/审核操作/金额计算/图片预览 |

### 第五批：业务模块 - 排班/操作记录 + 考勤（5个文件）

| 序号 | 文件路径 | 需要添加的注释 |
|------|---------|--------------|
| 26 | `pages/business/schedule/index.vue` | 文件头 + groupScheduleList/月份选择/删除 |
| 27 | `pages/business/schedule/form.vue` | 文件头 + 日历格式化/日期冲突检测/表单提交 |
| 28 | `pages/business/operation/index.vue` | 文件头 + getList/搜索防抖 |
| 29 | `pages/attendance/index.vue` | 文件头 + 定位降级策略/逆地理编码/距离计算/打卡流程 |
| 30 | `pages/attendance/record.vue` | 文件头 + 日历计算/状态映射/地址格式化 |

### 第六批：首页组件（5个文件）

| 序号 | 文件路径 | 需要添加的注释 |
|------|---------|--------------|
| 31 | `components/home/HeaderNav.vue` | 文件头 + 计算属性 + 事件处理 |
| 32 | `components/home/StatisticsCard.vue` | 文件头 + handleRefresh |
| 33 | `components/home/QuickMenu.vue` | 文件头 + handleMenuClick |
| 34 | `components/home/OrderList.vue` | 文件头 + 状态映射/点击跳转 |
| 35 | `components/home/NoticeBar.vue` | 文件头 + handleMoreNotice |

## 执行策略

### Vue 文件注释步骤
1. 读取文件内容，理解页面业务逻辑
2. 在 `<script setup>` 或 `<script>` 开头添加页面级注释
3. 为每个函数/方法添加单行注释（`/** ... */`）
4. 为计算属性添加单行注释
5. 为复杂逻辑添加行内注释
6. 不修改任何功能代码、模板和样式

### 注释示例

**页面级注释**：
```javascript
<script setup>
/**
 * @description 登录页 - 用户身份认证入口
 * @description 支持账号密码+验证码登录，登录成功后获取用户信息并跳转首页。
 * 包含验证码获取、表单校验、隐私协议确认等交互流程
 */
```

**函数注释**：
```javascript
/** 验证表单字段后调用store登录接口，成功后获取用户信息并跳转首页 */
async function pwdLogin() {
```

**计算属性注释**：
```javascript
/** 根据当前时间自动生成问候语（早上好/下午好/晚上好） */
const greeting = computed(() => {
```

## 注意事项

1. **不修改功能代码** - 仅添加注释，不改变任何逻辑
2. **注释要具体** - 描述业务含义和具体作用
3. **保持代码风格一致** - 使用统一的注释格式
4. **分批执行** - 按优先级分批实施
5. **中文注释** - 所有注释使用中文
6. **重点注释复杂逻辑** - 考勤定位降级、金额计算、日期冲突检测等复杂逻辑需要详细注释
