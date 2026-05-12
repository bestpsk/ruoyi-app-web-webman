# 开单/操作Tab微调计划

## 需求

### 1. 开单Tab - 工具栏调整
- **当前**：`[+ 添加品项]` 按钮在左侧
- **目标**：左边新增"套餐名称"输入框，按钮靠右显示
- 布局：`[套餐名称输入框] ...........................  [+ 添加品项]`

### 2. 操作Tab - 消耗金额只读
- **当前**：消耗金额使用 `el-input-number` 可编辑输入框
- **目标**：改为纯文本显示（不可编辑），值由操作次数自动计算

## 实施步骤

### 步骤1：开单Tab工具栏改造 (第71行)
将单个按钮改为 flex 布局行：
```vue
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px">
  <el-input v-model="orderPackageName" placeholder="套餐名称" style="width: 200px" size="small" clearable />
  <el-button v-if="canAddOrderItem" type="primary" plain icon="Plus" @click="addOrderItemRow">添加品项</el-button>
</div>
```
同时在 script 中新增 `orderPackageName` ref 变量。

### 步骤2：操作Tab消耗金额改只读 (第167-170行)
将 `el-input-number` 改为 `<span>` 文本显示：
```vue
<el-table-column label="消耗金额" width="120" align="right">
  <template #default="scope">
    <span style="color: #409eff; font-weight: 500">{{ scope.row.consumeAmount?.toFixed(2) || '0.00' }}</span>
  </template>
</el-table-column>
```

## 涉及文件
- `front/src/views/business/sales/index.vue`
  - 第71行：开单Tab工具栏
  - 第167-170行：操作Tab消耗金额列
  - script：新增 `orderPackageName` 变量
