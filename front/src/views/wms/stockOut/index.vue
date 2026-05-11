<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch">
      <el-form-item label="出库单号" prop="stockOutNo">
        <el-input v-model="queryParams.stockOutNo" placeholder="请输入出库单号" clearable style="width: 160px" @keyup.enter="handleQuery" />
      </el-form-item>
      <el-form-item label="出库类型" prop="stockOutType">
        <el-select v-model="queryParams.stockOutType" placeholder="请选择" clearable style="width: 140px">
          <el-option v-for="dict in biz_stock_out_type" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-select v-model="queryParams.status" placeholder="请选择" clearable style="width: 120px">
          <el-option v-for="dict in biz_doc_status" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="出库日期">
        <el-date-picker v-model="dateRange" type="daterange" range-separator="-" start-placeholder="开始" end-placeholder="结束" value-format="YYYY-MM-DD" style="width: 240px" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
        <el-button icon="Refresh" @click="resetQuery">重置</el-button>
      </el-form-item>
    </el-form>

    <el-row :gutter="10" class="mb8">
      <el-col :span="1.5">
        <el-button type="primary" plain icon="Plus" @click="handleAdd" v-hasPermi="['wms:stockOut:add']">新增</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="danger" plain icon="Delete" :disabled="multiple" @click="handleDelete" v-hasPermi="['wms:stockOut:remove']">删除</el-button>
      </el-col>
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList" />
    </el-row>

    <el-table v-loading="loading" :data="stockOutList" @selection-change="handleSelectionChange" style="width: 100%">
      <el-table-column type="selection" width="55" align="center" />
      <el-table-column label="出库单号" prop="stockOutNo" min-width="140" />
      <el-table-column label="出库类型" prop="stockOutType" width="90" align="center">
        <template #default="scope">
          <dict-tag :options="biz_stock_out_type" :value="scope.row.stockOutType" />
        </template>
      </el-table-column>
      <el-table-column label="出库企业" prop="enterpriseName" min-width="90" show-overflow-tooltip />
      <el-table-column label="对接员工" prop="contactEmployeeName" min-width="80" align="center" />
      <el-table-column label="出库员工" prop="responsibleName" min-width="80" align="center" />
      <el-table-column label="总数量" min-width="160" align="center">
        <template #default="scope">
          {{ formatQuantity(scope.row) }}
        </template>
      </el-table-column>
      <el-table-column label="总金额" prop="totalAmount" min-width="100" align="right" />
      <el-table-column label="出库日期" prop="stockOutDate" min-width="100" align="center" />
      <el-table-column label="状态" prop="status" min-width="80" align="center">
        <template #default="scope">
          <el-switch v-model="scope.row.status" active-value="1" inactive-value="0" @change="(val) => handleStatusChange(scope.row, val)" />
        </template>
      </el-table-column>
      <el-table-column label="操作" min-width="200" align="center">
        <template #default="scope">
          <el-button link type="primary" icon="View" @click="handleView(scope.row)">查看</el-button>
          <el-button link type="primary" icon="Edit" @click="handleUpdate(scope.row)" v-if="scope.row.status === '0'" v-hasPermi="['wms:stockOut:edit']">修改</el-button>
          <el-button link type="danger" icon="Delete" @click="handleDelete(scope.row)" v-if="scope.row.status === '0'" v-hasPermi="['wms:stockOut:remove']">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" v-model:page="queryParams.pageNum" v-model:limit="queryParams.pageSize" @pagination="getList" />

    <el-dialog :title="dialogTitle" v-model="open" width="80%" append-to-body>
      <el-form ref="stockOutRef" :model="form" :rules="rules" label-width="100px">
        <el-row>
          <el-col :span="6">
            <el-form-item label="出库类型" prop="stockOutType">
              <el-select v-model="form.stockOutType" placeholder="请选择" :disabled="isView" style="width: 100%">
                <el-option v-for="dict in biz_stock_out_type" :key="dict.value" :label="dict.label" :value="dict.value" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="对象类型" prop="outTargetType">
              <el-select v-model="form.outTargetType" placeholder="请选择" :disabled="isView" style="width: 100%" @change="onTargetTypeChange">
                <el-option label="企业出库" value="1" />
                <el-option label="员工领用" value="2" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="6" v-if="form.outTargetType === '1'">
            <el-form-item label="出库企业" prop="enterpriseId">
              <template v-if="!isView">
                <el-select v-model="form.enterpriseId" placeholder="搜索企业" filterable remote :remote-method="searchEnterpriseList" :loading="enterpriseLoading" style="width: 100%" @change="onEnterpriseSelect">
                  <el-option v-for="item in enterpriseOptions" :key="item.enterpriseId" :label="item.enterpriseName" :value="item.enterpriseId" />
                </el-select>
              </template>
              <span v-else style="line-height: 32px">{{ form.enterpriseName || '-' }}</span>
            </el-form-item>
          </el-col>
          <el-col :span="6" v-if="form.outTargetType === '1'">
            <el-form-item label="对接员工">
              <template v-if="!isView">
                <el-select v-model="form.contactEmployeeId" placeholder="选择对接员工(可选)" filterable remote :remote-method="searchEmployeeList" :loading="employeeLoading" style="width: 100%" @change="onContactEmployeeSelect">
                  <el-option v-for="item in employeeOptions" :key="item.userId" :label="item.userName + (item.deptName ? '(' + item.deptName + ')' : '')" :value="item.userId" />
                </el-select>
              </template>
              <span v-else style="line-height: 32px">{{ form.contactEmployeeName || '-' }}</span>
            </el-form-item>
          </el-col>
          <el-col :span="6" v-if="form.outTargetType === '2'">
            <el-form-item label="领用员工" prop="responsibleId">
              <template v-if="!isView">
                <el-select v-model="form.responsibleId" placeholder="搜索员工" filterable remote :remote-method="searchEmployeeList" :loading="employeeLoading" style="width: 100%" @change="onEmployeeSelect">
                  <el-option v-for="item in employeeOptions" :key="item.userId" :label="item.userName + (item.deptName ? '(' + item.deptName + ')' : '')" :value="item.userId" />
                </el-select>
              </template>
              <span v-else style="line-height: 32px">{{ form.responsibleName || '-' }}</span>
            </el-form-item>
          </el-col>
          <el-col :span="6" v-if="form.outTargetType === '2'">
            <el-form-item label="目标企业">
              <template v-if="!isView">
                <el-select v-model="form.enterpriseId" placeholder="搜索企业(可选)" filterable remote :remote-method="searchEnterpriseList" :loading="enterpriseLoading" style="width: 100%" @change="onEnterpriseSelect">
                  <el-option v-for="item in enterpriseOptions" :key="item.enterpriseId" :label="item.enterpriseName" :value="item.enterpriseId" />
                </el-select>
              </template>
              <span v-else style="line-height: 32px">{{ form.enterpriseName || '-' }}</span>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="6">
            <el-form-item label="出库日期" prop="stockOutDate">
              <el-date-picker v-model="form.stockOutDate" type="date" value-format="YYYY-MM-DD" placeholder="选择日期" :disabled="isView" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="18">
            <el-form-item label="备注" prop="remark">
              <el-input v-model="form.remark" placeholder="请输入备注" :disabled="isView" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-divider content-position="left">出库明细</el-divider>
        <el-table :data="form.items" border style="width: 100%" v-if="!isView || form.items?.length">
          <el-table-column label="货品" min-width="150" align="center" header-align="center">
            <template #default="scope">
              <el-select v-if="!isView" v-model="scope.row.productId" placeholder="搜索货品" filterable remote :remote-method="searchProductList" :loading="productLoading" @change="onProductSelect(scope.$index)" style="width: 100%">
                <el-option v-for="item in productOptions" :key="item.productId" :label="item.productName + '(' + item.productCode + ')'" :value="item.productId" />
              </el-select>
              <span v-else>{{ scope.row.productName }}</span>
            </template>
          </el-table-column>
          <el-table-column label="单位类型" min-width="90" align="center" header-align="center">
            <template #default="scope">
              <el-select v-if="!isView" v-model="scope.row.unitType" placeholder="选择" @change="onUnitTypeChange(scope.$index)" style="width: 100%">
                <el-option label="主单位(整)" value="1" />
                <el-option label="副单位(拆)" value="2" />
              </el-select>
              <span v-else>{{ scope.row.unitType === '1' ? '主单位(整)' : '副单位(拆)' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="换算/库存" min-width="100" align="center" header-align="center">
            <template #default="scope">
              <div v-if="scope.row.packQty && scope.row.packQty > 1" style="color: #909399; font-size: 12px;">
                <div>1{{ getUnitLabel(scope.row.unit) }}={{ scope.row.packQty }}{{ getSpecLabel(scope.row.spec) }}</div>
                <div v-if="scope.row.inventoryQty !== undefined" style="color: #67c23a;">库存: {{ scope.row.inventoryQty }}{{ getSpecLabel(scope.row.spec) }}</div>
              </div>
              <div v-else-if="scope.row.inventoryQty !== undefined" style="color: #67c23a; font-size: 12px;">库存: {{ scope.row.inventoryQty }}{{ getSpecLabel(scope.row.spec) }}</div>
              <span v-else>-</span>
            </template>
          </el-table-column>
          <el-table-column label="数量" min-width="100" align="center" header-align="center">
            <template #default="scope">
              <el-input-number v-if="!isView" v-model="scope.row.quantity" :min="1" @change="calcAmount(scope.$index)" style="width: 100%" />
              <span v-else>{{ scope.row.originalQuantity || scope.row.quantity || 0 }}</span>
            </template>
          </el-table-column>
          <el-table-column label="规格" min-width="60" align="center" header-align="center">
            <template #default="scope">
              <span>{{ scope.row.unitType === '1' ? getUnitLabel(scope.row.unit) : getSpecLabel(scope.row.spec) }}</span>
            </template>
          </el-table-column>
          <el-table-column label="出货单价" min-width="110" align="center" header-align="center">
            <template #default="scope">
              <el-input-number v-if="!isView" v-model="scope.row.salePrice" :precision="2" :min="0" @change="calcAmount(scope.$index)" style="width: 100%" />
              <span v-else>{{ formatItemPrice(scope.row) }}</span>
            </template>
          </el-table-column>
          <el-table-column label="金额" min-width="80" align="center" header-align="center">
            <template #default="scope"><span>{{ scope.row.amount }}</span></template>
          </el-table-column>
          <el-table-column label="操作" width="60" align="center" header-align="center" v-if="!isView">
            <template #default="scope"><el-button link type="danger" icon="Delete" @click="removeItem(scope.$index)" /></template>
          </el-table-column>
        </el-table>
        <el-button v-if="!isView" type="primary" link icon="Plus" @click="addItem" style="margin-top: 10px">添加明细</el-button>
      </el-form>
      <template #footer v-if="!isView">
        <el-button type="primary" @click="submitForm">确 定</el-button>
        <el-button @click="cancel">取 消</el-button>
      </template>
      <template #footer v-else>
        <el-button type="primary" icon="Printer" @click="handlePrint">打 印</el-button>
        <el-button @click="cancel">关 闭</el-button>
      </template>
    </el-dialog>

    <el-dialog title="打印预览" v-model="printPreview" width="900px" append-to-body>
      <div class="print-preview-content" id="print-content">
        <div class="print-container">
          <div class="print-header"><h1>出 库 单</h1></div>
          <div class="print-info">
            <div class="info-row">
              <div class="info-item"><span>出库单号：</span>{{ form.stockOutNo }}</div>
              <div class="info-item"><span>出库日期：</span>{{ form.stockOutDate }}</div>
            </div>
            <div class="info-row">
              <div class="info-item"><span>出库类型：</span>{{ getDictLabel(biz_stock_out_type, form.stockOutType) }}</div>
              <div class="info-item"><span>对象类型：</span>{{ form.outTargetType === '1' ? '企业出库' : '员工领用' }}</div>
            </div>
            <div class="info-row">
              <div class="info-item" v-if="form.outTargetType === '1'"><span>出库企业：</span>{{ form.enterpriseName || '-' }}</div>
              <div class="info-item" v-else><span>领用员工：</span>{{ form.responsibleName || '-' }}</div>
              <div class="info-item"><span>对接员工：</span>{{ form.contactEmployeeName || '-' }}</div>
            </div>
            <div class="info-row" v-if="form.remark">
              <div class="info-item full"><span>备注：</span>{{ form.remark }}</div>
            </div>
          </div>
          <table class="print-table">
            <thead><tr><th width="40">序号</th><th width="22%">货品名称</th><th width="16%">换算</th><th width="12%">数量</th><th width="10%">规格</th><th width="14%">单价</th><th width="14%">金额</th></tr></thead>
            <tbody>
              <tr v-for="(item, index) in form.items" :key="index">
                <td align="center">{{ index + 1 }}</td>
                <td>{{ item.productName }}</td>
                <td align="center">{{ item.packQty > 1 ? '1' + getUnitLabel(item.unit) + '=' + item.packQty + getSpecLabel(item.spec) : '-' }}</td>
                <td align="center">{{ item.originalQuantity || item.quantity || 0 }}</td>
                <td align="center">{{ item.unitType === '1' ? getUnitLabel(item.unit) : getSpecLabel(item.spec) }}</td>
                <td align="right">{{ formatItemPrice(item) }}</td>
                <td align="right">{{ item.amount }}</td>
              </tr>
            </tbody>
            <tfoot><tr>
              <td colspan="6" align="right"><strong>合 计：{{ formatTotalQuantity(form) }}</strong></td>
              <td align="right"><strong>{{ form.totalAmount ? Number(form.totalAmount).toFixed(2) : '0.00' }}</strong></td>
            </tr></tfoot>
          </table>
          <div class="print-footer">
            <div class="footer-row">
              <div class="footer-item"><span>制单人：</span>{{ form.createBy || '' }}</div>
              <div class="footer-item"><span>经手人：</span></div>
              <div class="footer-item"><span>日期：</span>{{ form.stockOutDate }}</div>
            </div>
            <div class="footer-row sign-row">
              <div class="footer-item"><span>收货签字：</span></div>
              <div class="footer-item"><span>领用人签字：</span></div>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <el-button type="primary" icon="Printer" @click="confirmPrint">确认打印</el-button>
        <el-button @click="printPreview = false">取消</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup name="WmsStockOut">
import { listStockOut, getStockOut, delStockOut, addStockOut, updateStockOut, confirmStockOut, confirmStockOutById, cancelConfirmStockOut } from "@/api/wms/stockOut"
import { ElMessageBox } from 'element-plus'
import { searchProduct } from "@/api/wms/product"
import { searchEnterprise } from "@/api/business/enterprise"
import { searchEmployee } from "@/api/business/employeeConfig"

const { proxy } = getCurrentInstance()
const { biz_stock_out_type, biz_doc_status, biz_product_unit, biz_product_spec } = useDict("biz_stock_out_type", "biz_doc_status", "biz_product_unit", "biz_product_spec")

const stockOutList = ref([])
const open = ref(false)
const loading = ref(true)
const showSearch = ref(true)
const ids = ref([])
const multiple = ref(true)
const total = ref(0)
const dialogTitle = ref("")
const isView = ref(false)
const printPreview = ref(false)
const dateRange = ref([])
const enterpriseOptions = ref([])
const enterpriseLoading = ref(false)
const employeeOptions = ref([])
const employeeLoading = ref(false)
const productOptions = ref([])
const productLoading = ref(false)

const data = reactive({
  form: { items: [] },
  queryParams: { pageNum: 1, pageSize: 10, stockOutNo: undefined, stockOutType: undefined, status: undefined },
  rules: {
    stockOutType: [{ required: true, message: "出库类型不能为空", trigger: "change" }],
    stockOutDate: [{ required: true, message: "出库日期不能为空", trigger: "change" }],
    outTargetType: [{ required: true, message: "对象类型不能为空", trigger: "change" }]
  }
})
const { queryParams, form, rules } = toRefs(data)

function getUnitLabel(value) { if (!value) return ''; const dict = biz_product_unit.value?.find(d => d.value === value); return dict ? dict.label : '' }
function getSpecLabel(value) { if (!value) return ''; const dict = biz_product_spec.value?.find(d => d.value === value); return dict ? dict.label : '' }

function formatQuantity(row) {
  const items = row.items || []

  if (items.length === 0) {
    return row.totalQuantity || 0
  }

  if (items.length === 1) {
    const item = items[0]
    const packQty = row.display_pack_qty || item.packQty || 1
    const unitLabel = getUnitLabel(row.display_unit || item.unit)
    const totalQty = row.totalQuantity || 0

    if (packQty > 1 && unitLabel) {
      const mainQty = totalQty / packQty
      const mainQtyStr = Number.isInteger(mainQty) ? mainQty : mainQty.toFixed(1).replace(/\.0$/, '')
      return mainQtyStr + unitLabel
    } else if (unitLabel) {
      return totalQty + unitLabel
    } else {
      return totalQty
    }
  }

  const firstUnit = items[0].unit
  const isSameUnit = items.every(item => item.unit === firstUnit)

  if (isSameUnit) {
    const packQty = items[0].packQty || 1
    const unitLabel = getUnitLabel(firstUnit)
    const totalQty = row.totalQuantity || 0

    if (packQty > 1 && unitLabel) {
      const mainQty = totalQty / packQty
      const mainQtyStr = Number.isInteger(mainQty) ? mainQty : mainQty.toFixed(1).replace(/\.0$/, '')
      return mainQtyStr + unitLabel
    } else {
      return totalQty + (unitLabel || '')
    }
  } else {
    return items.length + '种'
  }
}

function formatItemQuantity(item) {
  const packQty = item.packQty || 1
  const unitLabel = getUnitLabel(item.unit)
  const specLabel = getSpecLabel(item.spec)
  const qty = item.quantity || 0
  if (packQty > 1 && specLabel) {
    const mainQty = qty / packQty
    const mainQtyStr = Number.isInteger(mainQty) ? mainQty : mainQty.toFixed(1).replace(/\.0$/, '')
    return mainQtyStr + unitLabel + '（' + qty + specLabel + '）'
  } else if (unitLabel) {
    return qty + unitLabel
  } else {
    return qty
  }
}

function formatItemQuantityForView(item) {
  const unitLabel = getUnitLabel(item.unit)
  const specLabel = getSpecLabel(item.spec)
  if (item.unitType === '1') {
    return (item.originalQuantity || item.quantity || 0) + unitLabel
  } else {
    return (item.originalQuantity || item.quantity || 0) + specLabel
  }
}

function formatItemPrice(item) {
  const unitLabel = getUnitLabel(item.unit)
  const specLabel = getSpecLabel(item.spec)
  const packQty = item.packQty || 1
  if (item.unitType === '1') {
    let price = item.salePrice || 0
    if (packQty > 1) {
      price = parseFloat((price * packQty).toFixed(2))
    }
    return price + '/' + (unitLabel || '')
  } else {
    return item.salePrice + '/' + (specLabel || '')
  }
}

function formatTotalQuantity(formData) {
  const firstItem = formData.items?.[0]
  if (!firstItem) return formData.totalQuantity || 0
  const packQty = firstItem.packQty || 1
  const unitLabel = getUnitLabel(firstItem.unit)
  const specLabel = getSpecLabel(firstItem.spec)
  const totalQty = formData.totalQuantity || 0
  if (packQty > 1 && specLabel) {
    const mainQty = totalQty / packQty
    const mainQtyStr = Number.isInteger(mainQty) ? mainQty : mainQty.toFixed(1).replace(/\.0$/, '')
    return mainQtyStr + unitLabel + '（' + totalQty + specLabel + '）'
  } else if (unitLabel) {
    return totalQty + unitLabel
  } else {
    return totalQty
  }
}

function getList() {
  loading.value = true
  const params = { ...queryParams.value }
  if (dateRange.value && dateRange.value.length === 2) { params.stockOutDateStart = dateRange.value[0]; params.stockOutDateEnd = dateRange.value[1] }
  listStockOut(params).then(async response => {
    const rows = response.rows || []
    if (rows.length > 0) {
      await Promise.all(rows.map(row => {
        return getStockOut(row.stockOutId).then(res => {
          const data = res.data
          if (data && data.items && data.items.length > 0) {
            const firstItem = data.items[0]
            row.display_pack_qty = firstItem.packQty || 1
            row.display_unit = firstItem.unit
            row.display_spec = firstItem.spec
          }
        }).catch(() => {})
      }))
    }
    stockOutList.value = rows
    total.value = response.total
    loading.value = false
  })
}
function handleQuery() { queryParams.value.pageNum = 1; getList() }
function resetQuery() { dateRange.value = []; proxy.resetForm("queryRef"); handleQuery() }
function handleSelectionChange(selection) { ids.value = selection.map(item => item.stockOutId); multiple.value = !selection.length }

function reset() {
  form.value = { stockOutId: undefined, stockOutType: "1", outTargetType: "1", enterpriseId: undefined, enterpriseName: undefined, contactEmployeeId: undefined, contactEmployeeName: undefined, responsibleId: undefined, responsibleName: undefined, stockOutDate: new Date().toISOString().slice(0, 10), remark: undefined, items: [] }
  enterpriseOptions.value = []; employeeOptions.value = []; productOptions.value = []
  proxy.resetForm("stockOutRef")
}

function handleAdd() { reset(); isView.value = false; dialogTitle.value = "新增出库单"; open.value = true }
function handleUpdate(row) { reset(); getStockOut(row.stockOutId).then(response => { const data = response.data || response; form.value = data; if (!form.value.items) form.value.items = []; isView.value = false; dialogTitle.value = "修改出库单"; open.value = true }) }
function handleView(row) { reset(); getStockOut(row.stockOutId).then(response => { const data = response.data || response; form.value = data; if (!form.value.items) form.value.items = []; isView.value = true; dialogTitle.value = "查看出库单"; open.value = true }) }
function handleConfirm(row) { proxy.$modal.confirm('确认出库后将减少库存数量，是否继续？').then(() => confirmStockOut(row.stockOutId)).then(() => { getList(); proxy.$modal.msgSuccess("出库确认成功") }).catch(() => {}) }

function handleStatusChange(row, val) {
  const action = val === '1' ? '确认出库' : '取消确认'
  ElMessageBox.confirm(`确定要${action}该出库单吗？${val === '1' ? '\n确认后将扣减库存！' : '\n取消后将恢复库存！'}`, '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    if (val === '1') {
      confirmStockOutById(row.stockOutId).then(() => {
        proxy.$modal.msgSuccess('出库确认成功')
        getList()
      }).catch(() => {
        row.status = '0'
        proxy.$modal.msgError('操作失败')
      })
    } else {
      cancelConfirmStockOut(row.stockOutId).then(() => {
        proxy.$modal.msgSuccess('已取消确认')
        getList()
      }).catch(() => {
        row.status = '1'
        proxy.$modal.msgError('操作失败')
      })
    }
  }).catch(() => {
    row.status = val === '1' ? '0' : '1'
  })
}

function addItem() { form.value.items.push({ productId: undefined, productName: undefined, spec: undefined, unit: undefined, packQty: 1, unitType: '1', quantity: 1, salePrice: 0, salePriceSpec: 0, inventoryQty: undefined, amount: 0, remark: undefined }) }
function removeItem(index) { form.value.items.splice(index, 1) }

function onProductSelect(index) {
  const product = productOptions.value.find(p => p.productId === form.value.items[index].productId)
  if (product) {
    form.value.items[index].productName = product.productName
    form.value.items[index].spec = product.spec
    form.value.items[index].unit = product.unit
    form.value.items[index].packQty = product.packQty || 1
    form.value.items[index].salePrice = product.salePrice || 0
    form.value.items[index]._mainPrice = product.salePrice || 0
    form.value.items[index].salePriceSpec = product.salePriceSpec || product.salePrice || 0
    form.value.items[index].inventoryQty = product.inventoryQty
    form.value.items[index].unitType = '1'
    calcAmount(index)
  }
}

function onUnitTypeChange(index) {
  const item = form.value.items[index]
  const packQty = item.packQty || 1
  if (item.unitType === '1') { item.salePrice = item._mainPrice || item.salePrice || 0 }
  else {
    if (!item._mainPrice && item.salePrice) { item._mainPrice = item.salePrice }
    if (item._mainPrice && packQty > 0) { item.salePrice = Math.round((item._mainPrice / packQty) * 100) / 100 }
  }
  calcAmount(index)
}

function calcAmount(index) { const item = form.value.items[index]; item.amount = (item.quantity * item.salePrice).toFixed(2) }
function onEnterpriseSelect(val) { const ent = enterpriseOptions.value.find(e => e.enterpriseId === val); if (ent) form.value.enterpriseName = ent.enterpriseName }
function onEmployeeSelect(val) { const emp = employeeOptions.value.find(e => e.userId === val); if (emp) form.value.responsibleName = emp.userName }
function onContactEmployeeSelect(val) { const emp = employeeOptions.value.find(e => e.userId === val); if (emp) form.value.contactEmployeeName = emp.userName }
function onTargetTypeChange(val) { form.value.enterpriseId = undefined; form.value.enterpriseName = undefined; form.value.responsibleId = undefined; form.value.responsibleName = undefined; form.value.contactEmployeeId = undefined; form.value.contactEmployeeName = undefined }
function searchEnterpriseList(keyword) { enterpriseLoading.value = true; searchEnterprise(keyword).then(res => { enterpriseOptions.value = res.data || []; enterpriseLoading.value = false }) }
function searchEmployeeList(keyword) { employeeLoading.value = true; searchEmployee(keyword).then(res => { employeeOptions.value = res.data || []; employeeLoading.value = false }) }
function searchProductList(keyword) { productLoading.value = true; searchProduct(keyword).then(res => { productOptions.value = res.data || []; productLoading.value = false }) }

function submitForm() {
  proxy.$refs["stockOutRef"].validate(valid => {
    if (valid) {
      if (!form.value.items || form.value.items.length === 0) { proxy.$modal.msgWarning("请至少添加一条出库明细"); return }
      
      const submitData = { ...form.value }
      submitData.outTargetType = form.value.outTargetType || '1'
      submitData.enterpriseId = form.value.enterpriseId || null
      submitData.enterpriseName = form.value.enterpriseName || null
      submitData.contactEmployeeId = form.value.contactEmployeeId || null
      submitData.contactEmployeeName = form.value.contactEmployeeName || null
      
      if (form.value.stockInId != undefined) { updateStockOut(submitData).then(() => { proxy.$modal.msgSuccess("修改成功"); open.value = false; getList() }) }
      else { addStockOut(submitData).then(() => { proxy.$modal.msgSuccess("新增成功"); open.value = false; getList() }) }
    }
  })
}
function handleDelete(row) { const stockOutIds = row.stockOutId || ids.value; proxy.$modal.confirm('是否确认删除？').then(() => delStockOut(stockOutIds)).then(() => { getList(); proxy.$modal.msgSuccess("删除成功") }).catch(() => {}) }
function handlePrint() { printPreview.value = true }
function confirmPrint() {
  const printContent = document.getElementById('print-content')
  if (!printContent) return
  const printWindow = window.open('', '_blank')
  printWindow.document.write(`<!DOCTYPE html><html><head><meta charset="UTF-8"><title>出库单 - ${form.value.stockOutNo}</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:SimSun,serif;font-size:11pt;color:#000;background:#fff;padding:8mm}.print-container{width:210mm;margin:0 auto}.print-header{text-align:center;margin-bottom:20px}.print-header h1{font-size:18pt;font-weight:bold;letter-spacing:10px}.print-info{margin-bottom:15px}.info-row{display:flex;margin-bottom:8px}.info-item{flex:1}.info-item.full{flex:2}.print-table{width:100%;border-collapse:collapse;margin-bottom:15px}.print-table th,.print-table td{border:1px solid #000;padding:4px 6px}.print-table th{background:#f5f5f5;font-weight:bold}.print-table tfoot td{background:#f9f9f9}.print-footer{margin-top:30px}.footer-row{display:flex;margin-bottom:15px}.footer-item{flex:1}.sign-row{margin-top:40px}@media print{body{padding:0}.print-container{width:100%}}</style></head><body>${printContent.innerHTML}</body></html>`)
  printWindow.document.close()
  printWindow.focus()
  setTimeout(() => { printWindow.print(); printWindow.close() }, 100)
}
function getDictLabel(dict, value) { if (!dict) return value; const item = dict.find(d => d.value === value); return item ? item.label : value }
function cancel() { open.value = false; reset() }
getList()
</script>

<style scoped>
.print-preview-content { max-height: 60vh; overflow-y: auto; background: #f5f5f5; padding: 20px; }
.print-container { width: 210mm; margin: 0 auto; padding: 8mm; background: #fff; font-family: SimSun, serif; font-size: 11pt; color: #000; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
.print-header { text-align: center; margin-bottom: 20px; }
.print-header h1 { font-size: 18pt; font-weight: bold; margin: 0; letter-spacing: 10px; }
.print-info { margin-bottom: 15px; }
.info-row { display: flex; margin-bottom: 8px; }
.info-item { flex: 1; }
.info-item.full { flex: 2; }
.print-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
.print-table th, .print-table td { border: 1px solid #000; padding: 4px 6px; }
.print-table th { background: #f5f5f5; font-weight: bold; }
.print-table tfoot td { background: #f9f9f9; }
.print-footer { margin-top: 30px; }
.footer-row { display: flex; margin-bottom: 15px; }
.footer-item { flex: 1; }
.sign-row { margin-top: 40px; }
</style>
