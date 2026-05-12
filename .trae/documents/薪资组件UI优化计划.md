# 薪资配置组件 UI 优化计划

## 问题分析

根据用户反馈和截图分析，存在以下 5 个问题：

### 问题1：薪资类型不显示
- **原因**：后端通过 `with(['salaryType'])` 返回关联数据，字段为 `salaryType.typeName`
- **当前代码**：`prop="typeName"` 直接取值，但实际数据在嵌套对象中
- **修复方案**：修改表格列模板，正确获取关联数据

### 问题2：提成比例 % 号位置
- **当前状态**：%号显示在输入框外部（右侧）
- **期望效果**：%号显示在输入框内部（后缀）
- **修复方案**：将 `el-input-number` 改为带 suffix 的 `el-input`，或使用 CSS 方式

### 问题3：日期格式异常
- **当前状态**：显示为 `2026-04-27T16:00:00.000000Z`（ISO 格式）
- **原因**：后端日期字段未做格式化处理
- **修复方案**：
  - 后端：在模型 cast 中确保日期格式
  - 前端：对列表中的日期进行格式化截取

### 问题4：非底薪类型的基础金额字段需要提示
- **场景**：销售业绩提成、回款业绩提成等类型，基础金额字段含义不明
- **修复方案**：在"基础金额"标签旁添加 `el-tooltip` 问号图标，鼠标悬停显示说明

### 问题5：阶梯配置三个输入框含义不清
- **当前状态**：只有 placeholder 提示（最小金额、最大金额、提成比例），不够直观
- **修复方案**：
  - 添加清晰的行内标签
  - 使用卡片/分组形式展示每级阶梯
  - 每个输入框上方或左侧标注含义

## 修改文件

仅修改一个文件：`front/src/views/system/user/components/UserSalary.vue`

## 具体修改内容

### 1. 表格列 - 薪资类型显示修复

```vue
<!-- 修改前 -->
<el-table-column label="薪资类型" prop="typeName" min-width="120" />

<!-- 修改后 -->
<el-table-column label="薪资类型" min-width="120">
  <template #default="scope">
    {{ scope.row.salaryType?.typeName || '-' }}
  </template>
</el-table-column>
```

### 2. 表格列 - 日期格式修复

```vue
<!-- 修改前 -->
<el-table-column label="生效日期" prop="effectiveDate" min-width="100" />
<el-table-column label="失效日期" prop="expireDate" min-width="100" />

<!-- 修改后 -->
<el-table-column label="生效日期" min-width="120">
  <template #default="scope">
    {{ formatDate(scope.row.effectiveDate) }}
  </template>
</el-table-column>
<el-table-column label="失效日期" min-width="120">
  <template #default="scope">
    {{ formatDate(scope.row.expireDate) }}
  </template>
</el-table-column>
```

添加日期格式化函数：
```js
const formatDate = (value) => {
  if (!value) return '-'
  return value.substring(0, 10)
}
```

### 3. 提成比例输入框 - %号内置

```vue
<!-- 修改前 -->
<el-form-item label="提成比例" prop="commissionRate">
  <el-input-number v-model="form.commissionRate" :min="0" :max="1" :step="0.01" :precision="4" style="width: 100%" />
  <span style="margin-left: 10px;">{{ form.commissionRate ? (form.commissionRate * 100).toFixed(2) + '%' : '0%' }}</span>
</el-form-item>

<!-- 修改后 -->
<el-form-item label="提成比例" prop="commissionRate">
  <el-input
    v-model="commissionRateDisplay"
    type="number"
    placeholder="请输入提成比例"
    @input="handleCommissionRateInput"
  >
    <template #suffix>%</template>
  </el-input>
</el-form-item>
```

### 4. 基础金额字段 - 添加提示图标

```vue
<!-- 修改前 -->
<el-form-item label="基础金额" prop="baseAmount">
  <el-input-number v-model="form.baseAmount" :min="0" :precision="2" style="width: 100%" />
</el-form-item>

<!-- 修改后 -->
<el-form-item prop="baseAmount">
  <template #label>
    <span>基础金额</span>
    <el-tooltip v-if="currentTypeCode !== 'BASE_SALARY'" content="底薪填固定月薪；提成类填保底金额；阶梯类填保底金额" placement="top">
      <el-icon class="tooltip-icon"><QuestionFilled /></el-icon>
    </el-tooltip>
  </template>
  <el-input-number v-model="form.baseAmount" :min="0" :precision="2" style="width: 100%" />
</el-form-item>
```

### 5. 阶梯配置 - 优化UI展示

```vue
<!-- 修改前：简单的三列数字输入 -->
<div class="tier-config">
  <div v-for="(tier, index) in form.tiers" :key="index" class="tier-item">
    <el-row :gutter="10">
      <el-col :span="8"><el-input-number ... placeholder="最小金额" /></el-col>
      <el-col :span="8"><el-input-number ... placeholder="最大金额" /></el-col>
      <el-col :span="6"><el-input-number ... placeholder="提成比例" /></el-col>
      <el-col :span="2"><el-button icon="Delete" /></el-col>
    </el-row>
  </div>
</div>

<!-- 修改后：卡片式布局 + 清晰标签 -->
<div class="tier-config">
  <div v-for="(tier, index) in form.tiers" :key="index" class="tier-card">
    <div class="tier-header">第 {{ index + 1 }} 级</div>
    <div class="tier-body">
      <div class="tier-field">
        <label>起始金额（≥）</label>
        <el-input-number v-model="tier.minAmount" :min="0" :precision="2" style="width: 100%" />
      </div>
      <div class="tier-field">
        <label>截止金额（&lt;）</label>
        <el-input-number v-model="tier.maxAmount" :min="0" :precision="2" style="width: 100%" />
        <span class="field-tip">留空表示无上限</span>
      </div>
      <div class="tier-field">
        <label>提成比例</label>
        <el-input v-model="tier.commissionRateDisplay" type="number">
          <template #suffix>%</template>
        </el-input>
      </div>
      <el-button type="danger" icon="Delete" circle @click="removeTier(index)" class="delete-btn" />
    </div>
  </div>
  <el-button type="primary" plain icon="Plus" @click="addTier">添加阶梯</el-button>
</div>
```

## 实现步骤

1. 修改表格列：修复薪资类型显示、日期格式
2. 修改表单：提成比例改为 % 内置输入框
3. 修改表单：基础金额添加 tooltip 提示
4. 修改表单：阶梯配置改为卡片式清晰布局
5. 添加相关样式和辅助函数
6. 添加 computed 属性 `currentTypeCode` 用于判断当前类型
