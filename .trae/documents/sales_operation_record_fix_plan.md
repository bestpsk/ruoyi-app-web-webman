# 销售开单 - 操作TAB页和记录TAB页问题修复计划

## 问题分析

### 问题1：操作TAB页选择持卡记录不显示数据

**原因分析：**
1. 查询接口 `getPackageByCustomer` 调用正常，后端 `BizCustomerPackageController::getByCustomer` 实现正确
2. 前端 `loadPackageList` 函数使用 `res.data` 获取数据，符合 AjaxResult 返回格式
3. **根本原因**：只有当订单 `order_status == '1'` 且 `dealAmount > 0` 时才会生成持卡记录（见 `BizSalesOrderService::insertOrder` 第66-68行）
4. 如果客户没有已成交的订单，或成交金额为0，则不会产生持卡记录，下拉框自然无数据

**需要确认/修复的点：**
- 确保订单提交时 `isDeal=1` 和 `dealAmount` 正确传递到后端
- 检查 `convert_to_snake_case` 函数是否正确转换了字段名（如 `isDeal` -> `is_deal`, `dealAmount` -> `deal_amount`）

### 问题2：记录TAB页 - 订单记录显示问题

**子问题2a：数据列表宽度未占满整行宽度**
- 当前 `el-table-column` 使用固定 `width` 而非 `min-width`，导致总宽度可能小于容器宽度

**子问题2b：缺少品项名称、次数字段**
- 订单记录表格只显示了订单主表字段（orderNo, totalAmount, dealAmount等）
- 虽然后端 `selectOrderList` 使用了 `with('items')` 加载了订单明细，但前端表格未展示 items 数据
- 需要在表格中增加品项名称和次数的展示

**子问题2c：方案金额和成交金额都是0**
- 后端 `insertOrder` 方法第47-56行有计算逻辑，但需要确认：
  - 前端传递的字段名是否正确（planPrice -> plan_price, dealAmount -> deal_amount）
  - `convert_to_snake_case` 是否正确处理了嵌套的 items 数组

---

## 修复步骤

### 步骤1：修复订单记录表格 - 调整列宽和增加品项信息

**文件**: `d:\fuchenpro\front\src\views\business\sales\index.vue`

修改订单记录表格部分（约273-289行）：

1. 将固定 `width` 改为 `min-width` 使表格自适应宽度
2. 增加"品项"列展示订单明细的品项名称（使用模板展示多个品项）
3. 增加"次数"列展示订单明细的数量
4. 确保金额字段正确显示

### 步骤2：检查并修复数据传递问题

**文件**: `d:\fuchenpro\webman\app\service\BizSalesOrderService.php`

确认 `insertOrder` 方法中的数据处理：
- 确保 items 数组中的字段名正确映射
- 添加调试日志或确保数据完整性

### 步骤3：验证持卡记录生成逻辑

**文件**: `d:\fuchenpro\webman\app\service\BizSalesOrderService.php`

确认 `generatePackage` 方法的触发条件：
- 只有 `order_status == '1' && $dealAmount > 0` 才会生成套餐
- 如果用户勾选了"成交"但 dealAmount 为0，不会生成套餐

---

## 具体代码修改

### 修改1：订单记录表格（index.vue 第273-289行）

```vue
<!-- 修改前 -->
<el-table :data="orderRecordList" border size="small" style="margin-bottom: 20px">
  <el-table-column label="订单编号" prop="orderNo" width="160" />
  <el-table-column label="方案金额" prop="totalAmount" width="100" />
  <el-table-column label="成交金额" prop="dealAmount" width="100" />
  ...
</el-table>

<!-- 修改后 -->
<el-table :data="orderRecordList" border size="small" style="margin-bottom: 20px; width: 100%">
  <el-table-column label="订单编号" prop="orderNo" min-width="160" />
  <el-table-column label="品项" min-width="140">
    <template #default="scope">
      <span v-for="(item, idx) in (scope.row.items || [])" :key="idx">
        {{ item.productName }}<template v-if="idx < (scope.row.items || []).length - 1">、</template>
      </span>
    </template>
  </el-table-column>
  <el-table-column label="次数" min-width="80" align="center">
    <template #default="scope">
      {{ (scope.row.items || []).reduce((sum, item) => sum + (item.quantity || 0), 0) }}
    </template>
  </el-table-column>
  <el-table-column label="方案金额" prop="totalAmount" min-width="100" align="right">
    <template #default="scope">{{ scope.row.totalAmount ? scope.row.totalAmount.toFixed(2) : '0.00' }}</template>
  </el-table-column>
  <el-table-column label="成交金额" prop="dealAmount" min-width="100" align="right">
    <template #default="scope">{{ scope.row.dealAmount ? scope.row.dealAmount.toFixed(2) : '0.00' }}</template>
  </el-table-column>
  <!-- 其他列也改为 min-width -->
</el-table>
```

### 修改2：确保金额计算正确（如需要）

检查 `convert_to_snake_case` 函数是否能正确处理嵌套对象。如果不能，需要在控制器层手动处理 items 数组的字段名转换。

---

## 验证清单

- [ ] 选择客户后，操作TAB页的下拉框能显示该客户的持卡记录（前提是该客户有已成交订单）
- [ ] 订单记录表格占满整行宽度
- [ ] 订单记录表格显示品项名称
- [ ] 订单记录表格显示次数
- [ ] 方案金额和成交金额正确显示（非0）
- [ ] 提交新订单后，上述功能正常
