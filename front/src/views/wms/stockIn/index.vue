<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch">
      <el-form-item label="入库单号" prop="stockInNo">
        <el-input v-model="queryParams.stockInNo" placeholder="请输入入库单号" clearable style="width: 180px" @keyup.enter="handleQuery" />
      </el-form-item>
      <el-form-item label="入库类型" prop="stockInType">
        <el-select v-model="queryParams.stockInType" placeholder="请选择" clearable style="width: 140px">
          <el-option v-for="dict in biz_stock_in_type" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-select v-model="queryParams.status" placeholder="请选择" clearable style="width: 120px">
          <el-option v-for="dict in biz_doc_status" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="入库日期">
        <el-date-picker v-model="dateRange" type="daterange" range-separator="-" start-placeholder="开始" end-placeholder="结束" value-format="YYYY-MM-DD" style="width: 240px" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
        <el-button icon="Refresh" @click="resetQuery">重置</el-button>
      </el-form-item>
    </el-form>

    <el-row :gutter="10" class="mb8">
      <el-col :span="1.5">
        <el-button type="primary" plain icon="Plus" @click="handleAdd" v-hasPermi="['wms:stockIn:add']">新增</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="danger" plain icon="Delete" :disabled="multiple" @click="handleDelete" v-hasPermi="['wms:stockIn:remove']">删除</el-button>
      </el-col>
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList" />
    </el-row>

    <el-table v-loading="loading" :data="stockInList" @selection-change="handleSelectionChange" style="width: 100%">
      <el-table-column type="selection" width="50" align="center" />
      <el-table-column label="入库单号" prop="stockInNo" min-width="140" />
      <el-table-column label="入库类型" prop="stockInType" min-width="90" align="center">
        <template #default="scope">
          <dict-tag :options="biz_stock_in_type" :value="scope.row.stockInType" />
        </template>
      </el-table-column>
      <el-table-column label="总数量" min-width="120" align="center">
        <template #default="scope">
          {{ formatQuantity(scope.row) }}
        </template>
      </el-table-column>
      <el-table-column label="总金额" prop="totalAmount" min-width="95" align="right" />
      <el-table-column label="入库日期" prop="stockInDate" min-width="95" align="center" />
      <el-table-column label="操作人" prop="operatorName" min-width="70" align="center" />
      <el-table-column label="状态" prop="status" min-width="65" align="center">
        <template #default="scope">
          <el-switch v-model="scope.row.status" active-value="1" inactive-value="0" @change="(val) => handleStatusChange(scope.row, val)" />
        </template>
      </el-table-column>
      <el-table-column label="创建时间" prop="createTime" min-width="130" align="center" />
      <el-table-column label="有效期至" min-width="100" align="center">
        <template #default="scope">
          <span v-if="scope.row.earliestExpiry" :style="{ color: isExpiringSoon(scope.row.earliestExpiry) ? '#e6a23c' : (isExpired(scope.row.earliestExpiry) ? '#f56c6c' : '') }">{{ scope.row.earliestExpiry }}</span>
          <span v-else>-</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" min-width="150" align="center">
        <template #default="scope">
          <el-button link type="primary" icon="View" @click="handleView(scope.row)">查看</el-button>
          <el-button link type="primary" icon="Edit" @click="handleUpdate(scope.row)" v-if="scope.row.status === '0'" v-hasPermi="['wms:stockIn:edit']">修改</el-button>
          <el-button link type="primary" icon="Delete" @click="handleDelete(scope.row)" v-if="scope.row.status === '0'" v-hasPermi="['wms:stockIn:remove']">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" v-model:page="queryParams.pageNum" v-model:limit="queryParams.pageSize" @pagination="getList" />

    <el-dialog :title="dialogTitle" v-model="open" width="85%" append-to-body>
      <el-form ref="stockInRef" :model="form" :rules="rules" label-width="100px">
        <el-row>
          <el-col :span="6">
            <el-form-item label="入库类型" prop="stockInType">
              <el-select v-model="form.stockInType" placeholder="请选择" :disabled="isView" style="width: 100%">
                <el-option v-for="dict in biz_stock_in_type" :key="dict.value" :label="dict.label" :value="dict.value" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="入库日期" prop="stockInDate">
              <el-date-picker v-model="form.stockInDate" type="date" value-format="YYYY-MM-DD" placeholder="选择日期" :disabled="isView" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="备注" prop="remark">
              <el-input v-model="form.remark" placeholder="请输入备注" :disabled="isView" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-divider content-position="left">入库明细</el-divider>
        <el-table :data="form.items" border style="width: 100%" v-if="!isView || form.items?.length">
          <el-table-column label="货品" min-width="160" align="center" header-align="center">
            <template #default="scope">
              <el-select v-if="!isView" v-model="scope.row.productId" placeholder="搜索货品" filterable remote :remote-method="searchProductList" :loading="productLoading" @change="onProductSelect(scope.$index)" style="width: 100%">
                <el-option v-for="item in productOptions" :key="item.productId" :label="item.productName + '(' + item.productCode + ')'" :value="item.productId" />
              </el-select>
              <span v-else>{{ scope.row.productName }}</span>
            </template>
          </el-table-column>
          <el-table-column label="供货商" min-width="100" align="center" header-align="center">
            <template #default="scope">
              <span>{{ scope.row.supplierName || '-' }}</span>
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
          <el-table-column label="换算" min-width="80" align="center" header-align="center">
            <template #default="scope">
              <span v-if="scope.row.packQty && scope.row.packQty > 1" style="color: #909399; font-size: 12px;">
                1{{ getUnitLabel(scope.row.unit) }}={{ scope.row.packQty }}{{ getSpecLabel(scope.row.spec) }}
              </span>
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
          <el-table-column label="进货单价" min-width="110" align="center" header-align="center">
            <template #default="scope">
              <el-input-number v-if="!isView" v-model="scope.row.purchasePrice" :precision="2" :min="0" @change="calcAmount(scope.$index)" style="width: 100%" />
              <span v-else>{{ formatItemPrice(scope.row) }}</span>
            </template>
          </el-table-column>
          <el-table-column label="金额" min-width="80" align="center" header-align="center">
            <template #default="scope">
              <span>{{ scope.row.amount }}</span>
            </template>
          </el-table-column>
          <el-table-column label="生产日期" min-width="110" align="center" header-align="center">
            <template #default="scope">
              <el-date-picker v-if="!isView" v-model="scope.row.productionDate" type="date" value-format="YYYY-MM-DD" placeholder="选择日期" style="width: 100%" />
              <span v-else>{{ scope.row.productionDate || '-' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="有效期至" min-width="110" align="center" header-align="center">
            <template #default="scope">
              <el-date-picker v-if="!isView" v-model="scope.row.expiryDate" type="date" value-format="YYYY-MM-DD" placeholder="选择日期" style="width: 100%" />
              <span v-else :style="{ color: isExpiringSoon(scope.row.expiryDate) ? '#e6a23c' : (isExpired(scope.row.expiryDate) ? '#f56c6c' : '') }">{{ scope.row.expiryDate || '-' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="60" align="center" header-align="center" v-if="!isView">
            <template #default="scope">
              <el-button link type="danger" icon="Delete" @click="removeItem(scope.$index)" />
            </template>
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

    <el-dialog title="打印预览" v-model="printPreview" width="1100px" append-to-body>
      <div class="print-preview-content" id="print-content">
        <div class="print-container">
          <div class="print-header"><h1>入 库 单</h1></div>
          <div class="print-info">
            <div class="info-row">
              <div class="info-item"><span>入库单号：</span>{{ form.stockInNo }}</div>
              <div class="info-item"><span>入库日期：</span>{{ form.stockInDate }}</div>
            </div>
            <div class="info-row">
              <div class="info-item"><span>入库类型：</span>{{ getDictLabel(biz_stock_in_type, form.stockInType) }}</div>
              <div class="info-item"><span>状态：</span>{{ form.status === '1' ? '已确认' : '待确认' }}</div>
            </div>
            <div class="info-row">
              <div class="info-item"><span>操作人：</span>{{ form.operatorName || '-' }}</div>
            </div>
            <div class="info-row" v-if="form.remark">
              <div class="info-item full"><span>备注：</span>{{ form.remark }}</div>
            </div>
          </div>
          <table class="print-table">
            <thead>
              <tr>
                <th width="40">序号</th>
                <th width="18%">货品名称</th>
                <th width="14%">供货商</th>
                <th width="12%">换算</th>
                <th width="10%">数量</th>
                <th width="8%">规格</th>
                <th width="14%">单价</th>
                <th width="14%">金额</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in form.items" :key="index">
                <td align="center">{{ index + 1 }}</td>
                <td>{{ item.productName }}</td>
                <td>{{ item.supplierName || '-' }}</td>
                <td align="center">{{ item.packQty > 1 ? '1' + getUnitLabel(item.unit) + '=' + item.packQty + getSpecLabel(item.spec) : '-' }}</td>
                <td align="center">{{ formatItemQuantity(item) }}</td>
                <td align="center">{{ item.unitType === '1' ? getUnitLabel(item.unit) : getSpecLabel(item.spec) }}</td>
                <td align="right">{{ formatItemPrice(item) }}</td>
                <td align="right">{{ item.amount }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="7" align="right"><strong>合 计：{{ formatTotalQuantity(form) }}</strong></td>
                <td align="right"><strong>{{ form.totalAmount ? Number(form.totalAmount).toFixed(2) : '0.00' }}</strong></td>
              </tr>
            </tfoot>
          </table>
          <div class="print-footer">
            <div class="footer-row">
              <div class="footer-item"><span>制单人：</span>{{ form.operatorName || '' }}</div>
              <div class="footer-item"><span>经手人：</span></div>
              <div class="footer-item"><span>日期：</span>{{ form.stockInDate }}</div>
            </div>
            <div class="footer-row sign-row">
              <div class="footer-item"><span>送货签字：</span></div>
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

<script setup name="WmsStockIn">
import { listStockIn, getStockIn, delStockIn, addStockIn, updateStockIn, confirmStockIn, confirmStockInById, cancelConfirmStockIn } from "@/api/wms/stockIn"
import { searchSupplier } from "@/api/wms/supplier"
import { searchProduct, getProduct } from "@/api/wms/product"
import { ElMessageBox } from 'element-plus'

const { proxy } = getCurrentInstance()
const { biz_stock_in_type, biz_doc_status, biz_product_unit, biz_product_spec } = useDict("biz_stock_in_type", "biz_doc_status", "biz_product_unit", "biz_product_spec")

const stockInList = ref([])
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
const supplierOptions = ref([])
const supplierLoading = ref(false)
const productOptions = ref([])
const productLoading = ref([])

const data = reactive({
  form: { items: [] },
  queryParams: { pageNum: 1, pageSize: 10, stockInNo: undefined, stockInType: undefined, status: undefined },
  rules: {
    stockInType: [{ required: true, message: "入库类型不能为空", trigger: "change" }],
    stockInDate: [{ required: true, message: "入库日期不能为空", trigger: "change" }]
  }
})
const { queryParams, form, rules } = toRefs(data)

function handleStatusChange(row, val) {
  const action = val === '1' ? '确认' : '取消确认'
  ElMessageBox.confirm(`确定要${action}该入库单吗？`, '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    if (val === '1') {
      confirmStockInById(row.stockInId).then(() => {
        proxy.$modal.msgSuccess('入库确认成功')
        getList()
      }).catch(() => {
        row.status = '0'
        proxy.$modal.msgError('操作失败')
      })
    } else {
      cancelConfirmStockIn(row.stockInId).then(() => {
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

function getUnitLabel(value) {
  if (!value && value !== 0) return ''
  const strValue = String(value)
  const dict = biz_product_unit.value?.find(d => String(d.value) === strValue)
  if (dict) return dict.label
  console.warn('未找到单位字典值:', value, '可用字典:', biz_product_unit.value)
  return strValue
}

function getSpecLabel(value) {
  if (!value && value !== 0) return ''
  const strValue = String(value)
  const dict = biz_product_spec.value?.find(d => String(d.value) === strValue)
  if (dict) return dict.label
  console.warn('未找到规格字典值:', value, '可用字典:', biz_product_spec.value)
  return strValue
}

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
  return item.originalQuantity || item.quantity || 0
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

function formatItemPrice(item) {
  const unitLabel = getUnitLabel(item.unit)
  const specLabel = getSpecLabel(item.spec)
  const packQty = item.packQty || 1
  if (item.unitType === '1') {
    let price = item.purchasePrice || 0
    if (packQty > 1) {
      price = parseFloat((price * packQty).toFixed(2))
    }
    return price + '/' + (unitLabel || '')
  } else {
    return item.purchasePrice + '/' + (specLabel || '')
  }
}

function getList() {
  loading.value = true
  const params = { ...queryParams.value }
  if (dateRange.value && dateRange.value.length === 2) {
    params.stockInDateStart = dateRange.value[0]
    params.stockInDateEnd = dateRange.value[1]
  }
  listStockIn(params).then(async response => {
    const rows = response.rows || []
    if (rows.length > 0) {
      await Promise.all(rows.map(row => {
        return getStockIn(row.stockInId).then(res => {
          const data = res.data
          if (data && data.items && data.items.length > 0) {
            const firstItem = data.items[0]
            row.display_pack_qty = firstItem.packQty || 1
            row.display_unit = firstItem.unit
            row.display_spec = firstItem.spec
            row.items = data.items
          }
        }).catch(() => {})
      }))
    }
    stockInList.value = rows
    total.value = response.total
    loading.value = false
  })
}

function handleQuery() { queryParams.value.pageNum = 1; getList() }
function resetQuery() { dateRange.value = []; proxy.resetForm("queryRef"); handleQuery() }
function handleSelectionChange(selection) { ids.value = selection.map(item => item.stockInId); multiple.value = !selection.length }

function reset() {
  form.value = { stockInId: undefined, stockInType: "1", stockInDate: new Date().toISOString().slice(0, 10), remark: undefined, items: [] }
  productOptions.value = []
  proxy.resetForm("stockInRef")
}

function handleAdd() { reset(); isView.value = false; dialogTitle.value = "新增入库单"; open.value = true }

async function loadProductOptionsForItems(items) {
  if (!items || items.length === 0) return
  const productIds = [...new Set(items.map(item => item.productId).filter(id => id))]
  if (productIds.length === 0) return
  
  try {
    const results = await Promise.all(productIds.map(id => getProduct(id)))
    const options = results.filter(r => r.data).map(r => r.data)
    productOptions.value = options
  } catch (e) {
    console.error('加载货品信息失败', e)
  }
}

function handleUpdate(row) {
  reset()
  const stockInId = row.stockInId || ids.value[0]
  getStockIn(stockInId).then(async response => {
    form.value = response.data
    if (!form.value.items) form.value.items = []
    isView.value = false
    dialogTitle.value = "修改入库单"
    open.value = true
    await loadProductOptionsForItems(form.value.items)
  })
}

function handleView(row) {
  reset()
  getStockIn(row.stockInId).then(async response => {
    form.value = response.data
    if (!form.value.items) form.value.items = []
    isView.value = true
    dialogTitle.value = "查看入库单"
    open.value = true
    await loadProductOptionsForItems(form.value.items)
  })
}

function addItem() {
  form.value.items.push({
    productId: undefined,
    productName: undefined,
    supplierId: undefined,
    supplierName: undefined,
    spec: undefined,
    unit: undefined,
    packQty: 1,
    unitType: '1',
    quantity: 1,
    purchasePrice: 0,
    _mainPrice: null,
    amount: 0,
    productionDate: undefined,
    expiryDate: undefined,
    remark: undefined
  })
}

function removeItem(index) { form.value.items.splice(index, 1) }

function onProductSelect(index) {
  const product = productOptions.value.find(p => p.productId === form.value.items[index].productId)
  if (product) {
    form.value.items[index].productName = product.productName
    form.value.items[index].supplierId = product.supplierId
    form.value.items[index].supplierName = product.supplierName || '未知供货商'
    form.value.items[index].spec = product.spec
    form.value.items[index].unit = product.unit
    form.value.items[index].packQty = product.packQty || 1
    form.value.items[index].purchasePrice = product.purchasePrice || 0
    form.value.items[index]._mainPrice = product.purchasePrice || 0
    form.value.items[index].unitType = '1'
    calcAmount(index)
  }
}

function isExpiringSoon(expiryDate) {
  if (!expiryDate) return false
  const now = new Date()
  const expiry = new Date(expiryDate)
  const daysLeft = Math.ceil((expiry - now) / (1000 * 60 * 60 * 24))
  return daysLeft > 0 && daysLeft <= 30
}

function isExpired(expiryDate) {
  if (!expiryDate) return false
  return new Date(expiryDate) < new Date(new Date().toISOString().slice(0, 10))
}

function onUnitTypeChange(index) {
  const item = form.value.items[index]
  const packQty = item.packQty || 1

  if (item.unitType === '1') {
    item.purchasePrice = item._mainPrice || item.purchasePrice || 0
  } else {
    if (!item._mainPrice && item.purchasePrice) {
      item._mainPrice = item.purchasePrice
    }
    if (item._mainPrice && packQty > 0) {
      item.purchasePrice = Math.round((item._mainPrice / packQty) * 100) / 100
    }
  }

  calcAmount(index)
}

function calcAmount(index) {
  const item = form.value.items[index]
  item.amount = (item.quantity * item.purchasePrice).toFixed(2)
}

function searchSupplierList(keyword) {
  supplierLoading.value = true
  searchSupplier(keyword).then(res => { supplierOptions.value = res.data || []; supplierLoading.value = false })
}

function searchProductList(keyword) {
  productLoading.value = true
  searchProduct(keyword).then(res => { productOptions.value = res.data || []; productLoading.value = false })
}

function submitForm() {
  proxy.$refs["stockInRef"].validate(valid => {
    if (valid) {
      if (!form.value.items || form.value.items.length === 0) {
        proxy.$modal.msgWarning("请至少添加一条入库明细")
        return
      }
      if (form.value.stockInId != undefined) {
        updateStockIn(form.value).then(() => { proxy.$modal.msgSuccess("修改成功"); open.value = false; getList() })
      } else {
        addStockIn(form.value).then(() => { proxy.$modal.msgSuccess("新增成功"); open.value = false; getList() })
      }
    }
  })
}

function handleDelete(row) {
  const stockInIds = row.stockInId || ids.value
  proxy.$modal.confirm('是否确认删除？').then(() => delStockIn(stockInIds)).then(() => { getList(); proxy.$modal.msgSuccess("删除成功") }).catch(() => {})
}

function handlePrint() {
  printPreview.value = true
}

function confirmPrint() {
  const printContent = document.getElementById('print-content')
  if (!printContent) return

  const printWindow = window.open('', '_blank')
  printWindow.document.write(`
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>入库单 - ${form.value.stockInNo}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: SimSun, serif; font-size: 10.5pt; color: #000; background: #fff; padding: 8mm; }
    .print-container { width: 210mm; margin: 0 auto; }
    .print-header { text-align: center; margin-bottom: 20px; }
    .print-header h1 { font-size: 18pt; font-weight: bold; letter-spacing: 10px; }
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
    @media print { body { padding: 0; } .print-container { width: 100%; } }
  </style>
</head>
<body>${printContent.innerHTML}</body>
</html>`)
  printWindow.document.close()
  printWindow.focus()
  setTimeout(() => {
    printWindow.print()
    printWindow.close()
  }, 100)
}

function getDictLabel(dict, value) {
  if (!dict) return value
  const item = dict.find(d => d.value === value)
  return item ? item.label : value
}

function cancel() { open.value = false; reset() }

getList()
</script>

<style scoped>
.print-preview-content {
  max-height: 60vh;
  overflow-y: auto;
  background: #f5f5f5;
  padding: 20px;
}

.print-container { width: 210mm; margin: 0 auto; padding: 8mm; background: #fff; font-family: SimSun, serif; font-size: 10.5pt; color: #000; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1); }

.print-header {
  text-align: center;
  margin-bottom: 20px;
}

.print-header h1 {
  font-size: 18pt;
  font-weight: bold;
  margin: 0;
  letter-spacing: 10px;
}

.print-info {
  margin-bottom: 15px;
}

.info-row {
  display: flex;
  margin-bottom: 8px;
}

.info-item {
  flex: 1;
}

.info-item.full {
  flex: 2;
}

.print-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 15px;
}

.print-table th,
.print-table td {
  border: 1px solid #000;
  padding: 4px 6px;
}

.print-table th {
  background: #f5f5f5;
  font-weight: bold;
}

.print-table tfoot td {
  background: #f9f9f9;
}

.print-footer {
  margin-top: 30px;
}

.footer-row {
  display: flex;
  margin-bottom: 15px;
}

.footer-item {
  flex: 1;
}

.sign-row {
  margin-top: 40px;
}
</style>
