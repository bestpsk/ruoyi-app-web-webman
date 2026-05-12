# AppV3 企业管理页面完善计划

## 目标
借鉴 Web 端 (`front/src/views/business/enterprise/index.vue`) 的企业管理功能，在 AppV3 (uni-app) 中实现完整的移动端企业管理页面。

## Web端功能分析

### 数据字段
| 字段 | 说明 | 类型 |
|------|------|------|
| enterpriseId | 企业ID | Long |
| enterpriseName | 企业名称 | String |
| bossName | 老板姓名 | String |
| phone | 联系电话 | String |
| address | 地址 | String |
| enterpriseType | 企业类型 | String(字典) |
| storeCount | 门店数量 | Integer |
| annualPerformance | 年业绩 | BigDecimal |
| enterpriseLevel | 企业级别 | String(字典) |
| serverUserId | 服务人ID | Long |
| serverUserName | 服务人姓名 | String |
| status | 状态 | String(字典: 0正常 1停用) |
| remark | 备注 | String |
| createTime | 创建时间 | DateTime |

### 搜索条件
- 企业名称、老板姓名、联系电话、企业类型、企业级别、状态

### 列表展示列
- 企业名称、老板姓名、联系电话、企业类型、门店数量、年业绩、企业级别、服务人、状态、创建时间

### 操作功能
- 新增、修改、删除、搜索、重置、分页

---

## 实施步骤

### 步骤1: 创建API文件
**文件**: `AppV3/src/api/business/enterprise.js`

- `listEnterprise(params)` - 获取企业列表（分页+筛选）
- `getEnterprise(id)` - 获取企业详情
- `addEnterprise(data)` - 新增企业
- `updateEnterprise(data)` - 修改企业
- `delEnterprise(ids)` - 删除企业

### 步骤2: 创建企业管理列表页
**文件**: `AppV3/src/pages/business/enterprise/index.vue`

#### 页面结构
```
┌─────────────────────────────┐
│  企业管理                    │ ← 导航栏标题
├─────────────────────────────┤
│  [搜索框] 🔍                │ ← 搜索区域
│  ┌───┬───┬───┐              │
│  │筛选│排序│新增│            │ ← 操作按钮栏
│  └───┴───┴───┘              │
├─────────────────────────────┤
│  ┌─────────────────────┐    │
│  │ 🏢 企业卡片1         │    │ ← 企业列表（卡片式）
│  │ 名称 | 类型 | 级别    │    │
│  │ 老板 | 电话          │    │
│  │ 门店数 | 年业绩      │    │
│  │ 服务人 | 状态        │    │
│  │        [编辑][删除]  │    │
│  └─────────────────────┘    │
│  ┌─────────────────────┐    │
│  │ 🏢 企业卡片2         │    │
│  └─────────────────────┘    │
│  ...                        │
├─────────────────────────────┤
│     加载更多 / 没有更多了    │ ← 分页加载
├─────────────────────────────┤
│           [+新增]           │ ← 浮动按钮
└─────────────────────────────┘
```

#### 功能点
1. **顶部搜索栏**
   - 搜索输入框（支持按企业名称/老板姓名/电话模糊搜索）

2. **筛选器**
   - 下拉选择：企业类型、企业级别、状态
   - 使用 uni-popup 或下拉面板实现

3. **企业卡片列表**
   - 卡片式布局展示企业信息
   - 显示关键字段：企业名称、老板姓名、电话、类型、级别、状态等
   - 点击卡片进入详情/编辑页

4. **操作功能**
   - 新增：浮动按钮或顶部按钮触发
   - 编辑：点击卡片或操作按钮
   - 删除：滑动删除或长按菜单

5. **分页加载**
   - 上拉加载更多（onReachBottom）
   - 下拉刷新（onPullDownRefresh）

6. **空状态处理**
   - 无数据时显示空状态提示

### 步骤3: 创建企业表单页
**文件**: `AppV3/src/pages/business/enterprise/form.vue`

#### 表单字段
- 企业名称 * (必填)
- 老板姓名 * (必填)
- 联系电话 * (必填)
- 企业类型 * (必填, 选择器)
- 地址
- 门店数量 (数字输入)
- 年业绩 (数字输入, 保留两位小数)
- 企业级别 * (必填, 选择器)
- 服务人
- 状态 (单选: 正常/停用)
- 备注 (多行文本)

#### 验证规则
- 必填项校验
- 电话格式校验
- 数字范围校验

### 步骤4: 配置路由
**文件**: `AppV3/src/pages.json`

添加页面路由:
```json
{
  "path": "pages/business/enterprise/index",
  "style": { "navigationBarTitleText": "企业管理" }
},
{
  "path": "pages/business/enterprise/form",
  "style": { "navigationBarTitleText": "企业信息" }
}
```

### 步骤5: 更新工作台导航
**文件**: `AppV3/src/pages/work/index.vue`

更新 businessList 中"企业管理"的 path:
```js
{ icon: 'home-fill', title: '企业管理', path: '/pages/business/enterprise/index' }
```

---

## 文件清单

| 序号 | 文件路径 | 操作 |
|------|----------|------|
| 1 | `AppV3/src/api/business/enterprise.js` | 新建 |
| 2 | `AppV3/src/pages/business/enterprise/index.vue` | 新建 |
| 3 | `AppV3/src/pages/business/enterprise/form.vue` | 新建 |
| 4 | `AppV3/src/pages.json` | 修改 |
| 5 | `AppV3/src/pages/work/index.vue` | 修改 |

## 技术要点

1. **UI组件使用 uview-plus**
   - `u-search` 搜索组件
   - `u-card` 卡片组件
   - `u-input` 输入框
   - `u-picker` 选择器
   - `u-popup` 弹出层
   - `u-empty` 空状态
   - `u-loadmore` 加载更多

2. **响应数据处理**
   - 前端需要 response.data (根据用户规则)

3. **字典数据**
   - biz_enterprise_type (企业类型)
   - biz_enterprise_level (企业级别)
   - sys_normal_disable (状态)

4. **交互优化**
   - 下拉刷新 + 上拉加载
   - 卡片点击反馈
   - 删除确认弹窗
   - Toast 提示
