# 操作Tab - 跨套餐选择品项优化计划

## 需求分析

### 当前问题
操作Tab中，用户只能选择**一个套餐**，然后在该套餐内选择多个品项进行操作。无法跨套餐选择品项。

### 当前代码结构

**模板部分**（第110-196行）：
1. 顶部：单个下拉选择器 `el-select` 绑定 `operationForm.packageId`，只能选一个套餐
2. 持卡明细表格：显示所选套餐的品项（`currentPackageItems`），支持多选
3. 持卡操作表格：选中的品项进入操作列表（`selectedOperationItems`）
4. 操作表单：操作时间、操作人、满意度等

**数据逻辑**：
- `packageList`：客户所有套餐列表
- `currentPackageItems`：当前选中套餐的品项（`handlePackageChange` 赋值）
- `selectedOperationItems`：勾选的操作品项
- `handlePackageChange(packageId)`：切换套餐时**清空**已选品项
- `submitOperation('0')`：提交时遍历 `selectedOperationItems`，每个品项单独调用 `addOperation`，共用同一个 `packageId`

### 核心难点
切换套餐时，`handlePackageChange` 会清空 `selectedOperationItems`，导致之前选的品项丢失。

---

## 推荐方案：展开所有套餐品项 + 套餐分组显示

### 设计思路
不再用下拉选择器选套餐，而是将客户所有套餐的品项**平铺展示**在一个表格中，按套餐分组，用户可以跨套餐自由勾选品项。

### 优势
- 用户可以一次性从多个套餐中选择品项
- 操作直观，所见即所得
- 每个品项自带 `packageId` 信息，提交时自动关联正确套餐
- 改动量适中，不需要修改后端API

---

## 实施步骤

### 步骤1：新增计算属性 - 合并所有套餐品项

在 script 中新增 `allPackageItems` 计算属性，将 `packageList` 中所有套餐的品项合并，并为每个品项附加套餐信息：

```js
const allPackageItems = computed(() => {
  const items = []
  packageList.value.forEach(pkg => {
    ;(pkg.items || []).forEach(item => {
      items.push({
        ...item,
        packageId: pkg.packageId,
        packageName: pkg.packageName,
        packageNo: pkg.packageNo
      })
    })
  })
  return items
})
```

### 步骤2：修改模板 - 替换单选套餐为分组表格

**删除**：顶部的套餐下拉选择器（第111-120行）

**替换为**：一个带套餐分组的品项表格，使用 `span-method` 或直接在表格中增加"套餐"列来标识品项归属。

推荐方案：在表格中增加"套餐名称"列，并用不同背景色或分隔行区分不同套餐：

```vue
<template v-if="allPackageItems.length > 0">
  <h4 style="margin: 0 0 8px 0">持卡明细（可跨套餐选择）</h4>
  <el-table ref="packageItemTableRef" :data="allPackageItems" border size="small"
    style="margin-bottom: 16px" @selection-change="handleOperationItemSelect"
    row-class-name="package-row">
    <el-table-column type="selection" width="45" :selectable="row => row.remainingQuantity > 0" />
    <el-table-column label="套餐" prop="packageName" width="140" />
    <el-table-column label="品项" prop="productName" />
    <el-table-column label="方案总价" prop="planPrice" width="90" align="center" />
    <el-table-column label="单次价" prop="unitPrice" width="80" align="center" />
    <el-table-column label="总次数" prop="totalQuantity" width="70" align="center" />
    <el-table-column label="已用" prop="usedQuantity" width="60" align="center" />
    <el-table-column label="剩余" prop="remainingQuantity" width="60" align="center" />
  </el-table>
</template>
```

### 步骤3：修改选中品项表格 - 增加套餐列

在"持卡操作"表格中增加"套餐"列，让用户清楚看到每个操作品项来自哪个套餐：

```vue
<el-table :data="selectedOperationItems" border size="small" style="margin-bottom: 12px">
  <el-table-column label="套餐" prop="packageName" width="140" />
  <el-table-column label="品项" prop="productName" />
  <el-table-column label="单次价" prop="unitPrice" width="80" align="center" />
  <el-table-column label="剩余次数" prop="remainingQuantity" width="80" align="center" />
  <el-table-column label="操作次数" width="120">...</el-table-column>
  <el-table-column label="消耗金额" width="120">...</el-table-column>
</el-table>
```

### 步骤4：修改 handleOperationItemSelect - 保留套餐信息

当前函数已使用展开运算符 `...item`，只需确保品项携带 `packageId`、`packageName`、`packageNo` 即可（步骤1已处理）：

```js
function handleOperationItemSelect(selection) {
  selectedOperationItems.value = selection.map(item => ({
    ...item,
    operationQuantity: 1,
    consumeAmount: item.unitPrice
  }))
  showTrialForm.value = false
}
```

此函数无需修改，因为 `allPackageItems` 中的每个 item 已包含套餐信息。

### 步骤5：修改 submitOperation - 按品项的 packageId 提交

当前提交逻辑中 `packageId` 和 `packageNo` 取自 `operationForm.value.packageId`，需要改为从每个品项自身获取：

```js
// 修改前
packageId: operationForm.value.packageId,
packageNo: pkg?.packageNo,

// 修改后
packageId: item.packageId,
packageNo: item.packageNo,
```

同时移除对 `operationForm.value.packageId` 的校验（因为不再需要先选套餐）。

### 步骤6：清理不再需要的代码

1. **删除** `operationForm` 中的 `packageId` 字段
2. **删除** `currentPackageItems` 变量
3. **删除** `handlePackageChange` 函数
4. **修改** `resetOperationForm` 函数，移除 `packageId` 保留逻辑
5. **修改** `handleSelectCustomer` 中的 `resetOperationForm()` 调用后的清理逻辑

### 步骤7：调整体验操作按钮位置

原来体验操作按钮在套餐选择器旁边，删除选择器后需要将按钮移到合适位置（如持卡明细标题行右侧）。

---

## 修改文件清单

| 文件 | 修改内容 |
|------|----------|
| `front/src/views/business/sales/index.vue` | 模板+脚本全面调整 |

## 最终布局预览

```
[体验操作] 按钮                          ← 右上角

持卡明细（可跨套餐选择）
┌──────────┬────────┬────────┬──────┬──────┬──────┬──────┬──────┐
│ □ │ 套餐 │ 品项   │方案总价│单次价│总次数│ 已用 │ 剩余 │
├──────────┼────────┼────────┼──────┼──────┼──────┼──────┼──────┤
│ ☑ │ 套餐A │ 品项1  │  500  │  50  │  10  │  3   │  7   │
│ ☐ │ 套餐A │ 品项2  │  300  │  30  │  10  │  2   │  8   │
│ ☑ │ 套餐B │ 品项3  │  800  │  80  │  10  │  1   │  9   │
│ ☐ │ 套餐B │ 品项4  │  200  │  20  │  10  │  5   │  5   │
└──────────┴────────┴────────┴──────┴──────┴──────┴──────┴──────┘

持卡操作
┌──────────┬────────┬──────┬──────────┬──────────┐
│ 套餐     │ 品项   │单次价│操作次数  │消耗金额  │
├──────────┼────────┼──────┼──────────┼──────────┤
│ 套餐A    │ 品项1  │  50  │    1     │   50     │
│ 套餐B    │ 品项3  │  80  │    1     │   80     │
└──────────┴────────┴──────┴──────────┴──────────┘

操作时间 / 操作人 / 满意度 / 顾客反馈 / 操作前后照片 / 备注
[提交持卡操作]
```

## 注意事项

1. 后端 `addOperation` API 不需要修改，因为每个品项提交时已携带正确的 `packageId` 和 `packageNo`
2. `operationForm` 中移除 `packageId` 后，公共字段（操作时间、操作人等）仍然共用
3. 表格 `selectable` 条件保持不变：`remainingQuantity > 0` 才可勾选
