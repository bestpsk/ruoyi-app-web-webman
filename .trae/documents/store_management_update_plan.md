# 门店管理模块修改计划

## 需求概述
对现有门店管理模块进行以下修改：
1. 添加"年业绩"、"常来顾客数"和"创建人"字段
2. 修复企业选择问题：点击下拉框时默认加载企业列表
3. 营业时间改用时间范围选择器

---

## 修改步骤

### 步骤1：修改数据库表结构
**文件**: `webman/sql/biz_store.sql`

添加三个字段：
- `annual_performance` decimal(12,2) - 年业绩
- `regular_customers` int - 常来顾客数
- `creator_name` varchar(50) - 创建人姓名（默认取登录用户姓名）

### 步骤2：修改后端Model
**文件**: `webman/app/model/BizStore.php`

在 `$fillable` 数组中添加：
- `annual_performance`
- `regular_customers`
- `creator_name`

### 步骤3：修改前端页面
**文件**: `front/src/views/business/store/index.vue`

#### 3.1 修复企业选择默认加载问题
- 在 `handleAdd()` 和 `handleUpdate()` 函数中，打开对话框时自动加载企业列表
- 修改 `handleSearchEnterprise()` 函数，当 keyword 为空时也加载企业列表

#### 3.2 营业时间改用时间范围选择器
- 将 `el-input` 改为 `el-time-picker` 的 `is-range` 模式
- 格式化为 "HH:mm - HH:mm" 格式存储

#### 3.3 添加年业绩和常来顾客数字段
- 在表单中添加两个输入框
- 在列表中添加两列显示
- 在 form 数据中添加默认值

---

## 文件修改清单

| 文件路径 | 操作 |
|----------|------|
| `webman/sql/biz_store.sql` | 添加新字段SQL |
| `webman/app/model/BizStore.php` | 添加fillable字段 |
| `front/src/views/business/store/index.vue` | 修改页面逻辑和表单 |

---

## 详细修改内容

### 1. SQL新增字段
```sql
ALTER TABLE `biz_store` ADD COLUMN `annual_performance` decimal(12,2) DEFAULT 0.00 COMMENT '年业绩' AFTER `business_hours`;
ALTER TABLE `biz_store` ADD COLUMN `regular_customers` int DEFAULT 0 COMMENT '常来顾客数' AFTER `annual_performance`;
ALTER TABLE `biz_store` ADD COLUMN `creator_name` varchar(50) DEFAULT NULL COMMENT '创建人' AFTER `regular_customers`;
```

### 2. 前端表单新增字段
- 年业绩：`el-input-number` 数字输入框，精度2位小数
- 常来顾客数：`el-input-number` 数字输入框，整数
- 创建人：新增时自动取当前登录用户姓名，不可编辑，在列表中显示

### 3. 营业时间选择器
使用 `el-time-picker` 配合 `is-range` 属性实现时间范围选择：
```vue
<el-time-picker
  v-model="form.businessTimeRange"
  is-range
  range-separator="-"
  start-placeholder="开始时间"
  end-placeholder="结束时间"
  format="HH:mm"
  value-format="HH:mm"
/>
```

### 4. 企业选择默认加载
在打开新增/修改对话框时，自动调用企业搜索API加载企业列表：
```javascript
function handleAdd() {
  reset()
  handleSearchEnterprise('')  // 默认加载企业列表
  open.value = true
  title.value = "添加门店"
}
```
