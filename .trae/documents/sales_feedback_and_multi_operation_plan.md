# 销售开单 - 顾客反馈移至订单级别 & 操作支持多品项

## 需求分析

### 需求1：顾客反馈跟着订单走，不跟着每个品项

**当前情况**：
- 前端：开单表格中每个品项行都有"顾客反馈"列（第85-89行）
- 后端：`biz_order_item` 表有 `customer_feedback` 字段
- 模型：`BizOrderItem` 的 `$fillable` 包含 `customer_feedback`

**目标**：
- 将"顾客反馈"从品项行移到订单级别（品项表格下方）
- 后端：`biz_sales_order` 表增加 `customer_feedback` 字段
- `biz_order_item` 表的 `customer_feedback` 字段保留（历史数据兼容），但前端不再使用

### 需求2：操作支持选择多个不同品项一起操作

**当前情况**：
- 前端：持卡明细表格中每行有"选择"按钮，点击后只能选择一个品项
- `operationForm` 只有一个 `packageItemId`，只能记录一个品项的操作
- 提交时只生成一条操作记录

**目标**：
- 持卡明细表格中每行增加复选框，支持多选
- 选中多个品项后，显示操作表单（操作时间、操作人、满意度、顾客反馈、对比照、备注为共享字段）
- 每个选中的品项可以设置操作次数
- 提交时为每个选中的品项生成一条操作记录

---

## 修改步骤

### 步骤1：数据库 - 给 biz_sales_order 表增加 customer_feedback 字段

```sql
ALTER TABLE `biz_sales_order` ADD COLUMN `customer_feedback` varchar(500) DEFAULT NULL COMMENT '顾客反馈' AFTER `remark`;
```

### 步骤2：后端模型 - BizSalesOrder 增加 customer_feedback

**文件**: `d:\fuchenpro\webman\app\model\BizSalesOrder.php`

在 `$fillable` 数组中增加 `customer_feedback`。

### 步骤3：前端 - 开单TAB页改造

**文件**: `d:\fuchenpro\front\src\views\business\sales\index.vue`

1. **移除品项表格中的"顾客反馈"列**（第85-89行）
2. **在品项表格下方、合计行上方，增加订单级别的"顾客反馈"输入框**
3. **修改 `addOrderItemRow` 函数**：移除 `customerFeedback` 字段
4. **修改 `submitOrder` 函数**：将 `customerFeedback` 放到订单级别数据中，不再放到 items 中

### 步骤4：前端 - 操作TAB页改造（支持多品项选择）

**文件**: `d:\fuchenpro\front\src\views\business\sales\index.vue`

1. **持卡明细表格增加多选功能**：使用 `el-table` 的 `@selection-change` 事件
2. **将 `operationForm` 改为支持多品项**：
   - 保留共享字段：操作时间、操作人、满意度、顾客反馈、对比照、备注
   - 选中的品项列表单独管理，每个品项可设置操作次数
3. **操作表单改造**：
   - 显示已选品项列表，每个品项可设置操作次数
   - 共享字段（操作时间、操作人等）只需填一次
4. **修改 `submitOperation` 函数**：循环为每个选中的品项调用 `addOperation` 接口

### 步骤5：后端 - 操作记录服务无需修改

`BizOperationRecordService::insertRecord` 已经支持单条记录插入，前端循环调用即可。

---

## 详细代码设计

### 开单TAB页 - 模板改造

```vue
<!-- 品项表格：移除顾客反馈列 -->
<el-table :data="orderItems" border style="width: 100%" size="small">
  <el-table-column label="品项" min-width="140">...</el-table-column>
  <el-table-column label="次数" width="80">...</el-table-column>
  <el-table-column label="方案价格" width="120">...</el-table-column>
  <el-table-column label="单次价" width="90" align="center">...</el-table-column>
  <el-table-column label="成交" width="55" align="center">...</el-table-column>
  <el-table-column label="成交金额" width="120">...</el-table-column>
  <el-table-column label="备注" min-width="100">...</el-table-column>
  <el-table-column label="操作" width="50" align="center">...</el-table-column>
</el-table>

<!-- 订单级别：顾客反馈 -->
<el-row :gutter="20" style="margin-top: 12px">
  <el-col :span="12">
    <el-input v-model="orderCustomerFeedback" type="textarea" :rows="2" placeholder="顾客反馈" size="small" />
  </el-col>
  <el-col :span="12" style="text-align: right; padding-top: 6px">
    方案合计: <b>{{ totalPlanAmount }}</b> 元 | 成交合计: <b style="color: #409eff">{{ totalDealAmount }}</b> 元
    <el-button type="primary" @click="submitOrder" style="margin-left: 12px">提交订单</el-button>
  </el-col>
</el-row>
```

### 操作TAB页 - 多选改造

```vue
<!-- 持卡明细表格：增加多选 -->
<el-table :data="currentPackageItems" border size="small" style="margin-bottom: 16px" @selection-change="handleOperationItemSelect">
  <el-table-column type="selection" width="45" :selectable="row => row.remainingQuantity > 0" />
  <el-table-column label="品项" prop="productName" />
  <el-table-column label="方案总价" prop="planPrice" width="90" align="center" />
  <el-table-column label="单次价" prop="unitPrice" width="80" align="center" />
  <el-table-column label="总次数" prop="totalQuantity" width="70" align="center" />
  <el-table-column label="已用" prop="usedQuantity" width="60" align="center" />
  <el-table-column label="剩余" prop="remainingQuantity" width="60" align="center" />
</el-table>

<!-- 操作表单：选中品项后显示 -->
<template v-if="selectedOperationItems.length > 0">
  <h4 style="margin: 0 0 8px 0">持卡操作</h4>
  <!-- 已选品项列表，每个可设置操作次数 -->
  <el-table :data="selectedOperationItems" border size="small" style="margin-bottom: 12px">
    <el-table-column label="品项" prop="productName" />
    <el-table-column label="单次价" prop="unitPrice" width="80" align="center" />
    <el-table-column label="剩余次数" prop="remainingQuantity" width="80" align="center" />
    <el-table-column label="操作次数" width="120">
      <template #default="scope">
        <el-input-number v-model="scope.row.operationQuantity" :min="1" :max="scope.row.remainingQuantity" controls-position="right" size="small" style="width: 100%" />
      </template>
    </el-table-column>
    <el-table-column label="消耗金额" width="120">
      <template #default="scope">
        <el-input-number v-model="scope.row.consumeAmount" :min="0" :precision="2" controls-position="right" size="small" style="width: 100%" />
      </template>
    </el-table-column>
  </el-table>
  <!-- 共享字段 -->
  <el-form :model="operationForm" label-width="80px" size="small" style="max-width: 700px">
    <el-row :gutter="16">
      <el-col :span="12">
        <el-form-item label="操作时间">
          <el-date-picker v-model="operationForm.operationDate" type="date" value-format="YYYY-MM-DD" placeholder="选择日期" style="width: 100%" />
        </el-form-item>
      </el-col>
      <el-col :span="12">
        <el-form-item label="操作人">
          <el-select v-model="operationForm.operatorUserId" filterable @change="handleOperatorChange" style="width: 100%">
            <el-option v-for="u in userOptions" :key="u.userId" :label="u.realName || u.nickName || u.userName" :value="u.userId" />
          </el-select>
        </el-form-item>
      </el-col>
    </el-row>
    <el-row :gutter="16">
      <el-col :span="12">
        <el-form-item label="满意度">
          <el-rate v-model="operationForm.satisfaction" :colors="['#99A9BF', '#F7BA2A', '#FF9900']" />
        </el-form-item>
      </el-col>
    </el-row>
    <el-form-item label="顾客反馈">
      <el-input v-model="operationForm.customerFeedback" type="textarea" :rows="2" />
    </el-form-item>
    <el-row :gutter="16">
      <el-col :span="12">
        <el-form-item label="操作前">
          <image-upload v-model="operationForm.beforePhoto" :limit="2" :fileSize="5" width="60" height="60" />
        </el-form-item>
      </el-col>
      <el-col :span="12">
        <el-form-item label="操作后">
          <image-upload v-model="operationForm.afterPhoto" :limit="2" :fileSize="5" width="60" height="60" />
        </el-form-item>
      </el-col>
    </el-row>
    <el-form-item label="备注">
      <el-input v-model="operationForm.remark" type="textarea" :rows="2" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="submitOperation('0')">提交持卡操作</el-button>
    </el-form-item>
  </el-form>
</template>
```

### JS 逻辑改造

```javascript
// 新增：订单级别的顾客反馈
const orderCustomerFeedback = ref('')

// 新增：操作多选品项
const selectedOperationItems = ref([])

// 修改：addOrderItemRow - 移除 customerFeedback
function addOrderItemRow() {
  orderItems.value.push({
    productName: '', quantity: 1, planPrice: 0, isDeal: 0, dealAmount: 0, remark: ''
  })
}

// 修改：submitOrder - 顾客反馈放到订单级别
function submitOrder() {
  // ...验证逻辑不变
  const data = {
    customerId: currentCustomer.value.customerId,
    customerName: currentCustomer.value.customerName,
    enterpriseId: currentEnterpriseId.value,
    enterpriseName: ent?.enterpriseName,
    storeId: currentStoreId.value,
    storeName: store?.storeName,
    orderStatus: '1',
    customerFeedback: orderCustomerFeedback.value,
    remark: '',
    items: orderItems.value
  }
  // ...
}

// 新增：操作品项多选处理
function handleOperationItemSelect(selection) {
  selectedOperationItems.value = selection.map(item => ({
    ...item,
    operationQuantity: 1,
    consumeAmount: item.unitPrice
  }))
}

// 修改：submitOperation - 循环提交多个品项
function submitOperation(operationType) {
  if (operationType === '0') {
    if (!operationForm.value.packageId) return proxy.$modal.msgWarning('请选择持卡记录')
    if (selectedOperationItems.value.length === 0) return proxy.$modal.msgWarning('请选择操作品项')

    const pkg = packageList.value.find(p => p.packageId === operationForm.value.packageId)
    const promises = selectedOperationItems.value.map(item => {
      const data = {
        operationType: '0',
        customerId: currentCustomer.value.customerId,
        customerName: currentCustomer.value.customerName,
        packageId: operationForm.value.packageId,
        packageNo: pkg?.packageNo,
        packageItemId: item.packageItemId,
        productName: item.productName,
        operationQuantity: item.operationQuantity,
        consumeAmount: item.consumeAmount,
        customerFeedback: operationForm.value.customerFeedback,
        satisfaction: operationForm.value.satisfaction,
        beforePhoto: operationForm.value.beforePhoto,
        afterPhoto: operationForm.value.afterPhoto,
        operatorUserId: operationForm.value.operatorUserId,
        operatorUserName: operationForm.value.operatorUserName,
        operationDate: operationForm.value.operationDate,
        enterpriseId: currentEnterpriseId.value,
        storeId: currentStoreId.value,
        remark: operationForm.value.remark
      }
      return addOperation(data)
    })
    Promise.all(promises).then(() => {
      proxy.$modal.msgSuccess('持卡操作提交成功')
      resetOperationForm()
      selectedOperationItems.value = []
      loadPackageList()
      loadOperationRecords()
    })
  }
  // 体验操作逻辑不变
}
```

---

## 修改文件清单

| 文件 | 修改内容 |
|------|----------|
| `webman/app/model/BizSalesOrder.php` | `$fillable` 增加 `customer_feedback` |
| `front/src/views/business/sales/index.vue` | 1. 移除品项表格"顾客反馈"列<br>2. 增加订单级别"顾客反馈"输入框<br>3. 操作TAB页改为多选品项<br>4. 操作表单支持多品项操作次数设置<br>5. 提交逻辑循环调用接口 |
| 数据库 | `biz_sales_order` 表增加 `customer_feedback` 字段 |
