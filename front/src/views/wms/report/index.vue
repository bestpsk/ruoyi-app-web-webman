<template>
  <div class="app-container">
    <el-tabs v-model="activeTab" @tab-click="handleTabClick">
      <el-tab-pane label="入库汇总" name="stockIn">
        <el-form :inline="true" :model="stockInQuery">
          <el-form-item label="入库日期">
            <el-date-picker v-model="stockInDateRange" type="daterange" range-separator="-" start-placeholder="开始" end-placeholder="结束" value-format="YYYY-MM-DD" style="width: 240px" />
          </el-form-item>
          <el-form-item label="类别">
            <el-select v-model="stockInQuery.category" placeholder="请选择" clearable style="width: 140px">
              <el-option v-for="dict in biz_product_category" :key="dict.value" :label="dict.label" :value="dict.value" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" icon="Search" @click="getStockInSummary">查询</el-button>
          </el-form-item>
        </el-form>
        <el-table :data="stockInData" border v-loading="stockInLoading" style="width: 100%">
          <el-table-column label="品名" prop="productName" min-width="140" />
          <el-table-column label="类别" min-width="90" align="center">
            <template #default="scope"><dict-tag :options="biz_product_category" :value="scope.row.category" /></template>
          </el-table-column>
          <el-table-column label="换算" min-width="100" align="center">
            <template #default="scope">
              <span v-if="scope.row.packQty > 1">1{{ getUnitLabel(scope.row.unit) }}={{ scope.row.packQty }}{{ getSpecLabel(scope.row.spec) }}</span>
              <span v-else>-</span>
            </template>
          </el-table-column>
          <el-table-column label="入库数量" min-width="140" align="center">
            <template #default="scope">{{ formatQty(scope.row.totalQuantity, scope.row) }}</template>
          </el-table-column>
          <el-table-column label="入库金额" prop="totalAmount" min-width="110" align="right" />
        </el-table>
      </el-tab-pane>

      <el-tab-pane label="出库汇总" name="stockOut">
        <el-form :inline="true" :model="stockOutQuery">
          <el-form-item label="出库日期">
            <el-date-picker v-model="stockOutDateRange" type="daterange" range-separator="-" start-placeholder="开始" end-placeholder="结束" value-format="YYYY-MM-DD" style="width: 240px" />
          </el-form-item>
          <el-form-item label="类别">
            <el-select v-model="stockOutQuery.category" placeholder="请选择" clearable style="width: 140px">
              <el-option v-for="dict in biz_product_category" :key="dict.value" :label="dict.label" :value="dict.value" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" icon="Search" @click="getStockOutSummary">查询</el-button>
          </el-form-item>
        </el-form>
        <el-table :data="stockOutData" border v-loading="stockOutLoading" style="width: 100%">
          <el-table-column label="品名" prop="productName" min-width="140" />
          <el-table-column label="类别" min-width="90" align="center">
            <template #default="scope"><dict-tag :options="biz_product_category" :value="scope.row.category" /></template>
          </el-table-column>
          <el-table-column label="换算" min-width="100" align="center">
            <template #default="scope">
              <span v-if="scope.row.packQty > 1">1{{ getUnitLabel(scope.row.unit) }}={{ scope.row.packQty }}{{ getSpecLabel(scope.row.spec) }}</span>
              <span v-else>-</span>
            </template>
          </el-table-column>
          <el-table-column label="出库数量" min-width="140" align="center">
            <template #default="scope">{{ formatQty(scope.row.totalQuantity, scope.row) }}</template>
          </el-table-column>
          <el-table-column label="出库金额" prop="totalAmount" min-width="110" align="right" />
        </el-table>
      </el-tab-pane>

      <el-tab-pane label="库存周转" name="turnover">
        <el-form :inline="true" :model="turnoverQuery">
          <el-form-item label="统计日期">
            <el-date-picker v-model="turnoverDateRange" type="daterange" range-separator="-" start-placeholder="开始" end-placeholder="结束" value-format="YYYY-MM-DD" style="width: 240px" />
          </el-form-item>
          <el-form-item label="类别">
            <el-select v-model="turnoverQuery.category" placeholder="请选择" clearable style="width: 140px">
              <el-option v-for="dict in biz_product_category" :key="dict.value" :label="dict.label" :value="dict.value" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" icon="Search" @click="getTurnover">查询</el-button>
          </el-form-item>
        </el-form>
        <el-table :data="turnoverData" border v-loading="turnoverLoading" style="width: 100%">
          <el-table-column label="货品编码" prop="productCode" min-width="110" />
          <el-table-column label="品名" prop="productName" min-width="140" />
          <el-table-column label="类别" min-width="80" align="center">
            <template #default="scope"><dict-tag :options="biz_product_category" :value="scope.row.category" /></template>
          </el-table-column>
          <el-table-column label="换算" min-width="100" align="center">
            <template #default="scope">
              <span v-if="scope.row.packQty > 1">1{{ getUnitLabel(scope.row.unit) }}={{ scope.row.packQty }}{{ getSpecLabel(scope.row.spec) }}</span>
              <span v-else>-</span>
            </template>
          </el-table-column>
          <el-table-column label="期初库存" min-width="130" align="center">
            <template #default="scope">{{ formatQty(scope.row.beginQuantity, scope.row) }}</template>
          </el-table-column>
          <el-table-column label="期间入库" min-width="130" align="center">
            <template #default="scope">{{ formatQty(scope.row.periodInQuantity, scope.row) }}</template>
          </el-table-column>
          <el-table-column label="期间出库" min-width="130" align="center">
            <template #default="scope">{{ formatQty(scope.row.periodOutQuantity, scope.row) }}</template>
          </el-table-column>
          <el-table-column label="期末库存" min-width="130" align="center">
            <template #default="scope">{{ formatQty(scope.row.endQuantity, scope.row) }}</template>
          </el-table-column>
        </el-table>
      </el-tab-pane>

      <el-tab-pane label="货品收发存" name="flow">
        <el-form :inline="true" :model="flowQuery">
          <el-form-item label="货品">
            <el-select v-model="flowQuery.productId" placeholder="搜索货品" filterable remote :remote-method="searchProductList" :loading="productLoading" style="width: 240px">
              <el-option v-for="item in productOptions" :key="item.productId" :label="item.productName + '(' + item.productCode + ')'" :value="item.productId" />
            </el-select>
          </el-form-item>
          <el-form-item label="日期范围">
            <el-date-picker v-model="flowDateRange" type="daterange" range-separator="-" start-placeholder="开始" end-placeholder="结束" value-format="YYYY-MM-DD" style="width: 240px" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" icon="Search" @click="getFlow">查询</el-button>
          </el-form-item>
        </el-form>
        <el-table :data="flowData" border v-loading="flowLoading" style="width: 100%">
          <el-table-column label="单号" prop="docNo" min-width="140" />
          <el-table-column label="日期" prop="flowDate" min-width="100" align="center" />
          <el-table-column label="类型" min-width="80" align="center">
            <template #default="scope">
              <el-tag :type="scope.row.flowType === '入库' ? 'success' : 'warning'">{{ scope.row.flowType }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="数量" min-width="140" align="center">
            <template #default="scope">{{ formatQty(scope.row.quantity, scope.row) }}</template>
          </el-table-column>
          <el-table-column label="金额" prop="amount" min-width="110" align="right" />
          <el-table-column label="结存" min-width="140" align="center">
            <template #default="scope">{{ formatQty(scope.row.balance, scope.row) }}</template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup name="WmsReport">
import { stockInSummary, stockOutSummary, inventoryTurnover, productFlow } from "@/api/wms/report"
import { searchProduct } from "@/api/wms/product"

const { biz_product_category, biz_product_unit, biz_product_spec } = useDict("biz_product_category", "biz_product_unit", "biz_product_spec")

const activeTab = ref("stockIn")
const productOptions = ref([])
const productLoading = ref(false)

const stockInQuery = ref({})
const stockInDateRange = ref([])
const stockInData = ref([])
const stockInLoading = ref(false)

const stockOutQuery = ref({})
const stockOutDateRange = ref([])
const stockOutData = ref([])
const stockOutLoading = ref(false)

const turnoverQuery = ref({})
const turnoverDateRange = ref([])
const turnoverData = ref([])
const turnoverLoading = ref(false)

const flowQuery = ref({})
const flowDateRange = ref([])
const flowData = ref([])
const flowLoading = ref(false)

function getUnitLabel(value) { if (!value) return ''; const dict = biz_product_unit.value?.find(d => d.value === value); return dict ? dict.label : '' }
function getSpecLabel(value) { if (!value) return ''; const dict = biz_product_spec.value?.find(d => d.value === value); return dict ? dict.label : '' }

function formatQty(qty, row) {
  const packQty = row.packQty || 1
  const unitLabel = getUnitLabel(row.unit)
  const specLabel = getSpecLabel(row.spec)
  qty = qty || 0
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

function getStockInSummary() {
  stockInLoading.value = true
  const params = { ...stockInQuery.value }
  if (stockInDateRange.value?.length === 2) { params.stockInDateStart = stockInDateRange.value[0]; params.stockInDateEnd = stockInDateRange.value[1] }
  stockInSummary(params).then(res => { stockInData.value = res.data || []; stockInLoading.value = false })
}

function getStockOutSummary() {
  stockOutLoading.value = true
  const params = { ...stockOutQuery.value }
  if (stockOutDateRange.value?.length === 2) { params.stockOutDateStart = stockOutDateRange.value[0]; params.stockOutDateEnd = stockOutDateRange.value[1] }
  stockOutSummary(params).then(res => { stockOutData.value = res.data || []; stockOutLoading.value = false })
}

function getTurnover() {
  turnoverLoading.value = true
  const params = { ...turnoverQuery.value }
  if (turnoverDateRange.value?.length === 2) { params.startDate = turnoverDateRange.value[0]; params.endDate = turnoverDateRange.value[1] }
  inventoryTurnover(params).then(res => { turnoverData.value = res.data || []; turnoverLoading.value = false })
}

function getFlow() {
  if (!flowQuery.value.productId) return
  flowLoading.value = true
  const params = { productId: flowQuery.value.productId }
  if (flowDateRange.value?.length === 2) { params.flowDateStart = flowDateRange.value[0]; params.flowDateEnd = flowDateRange.value[1] }
  productFlow(params).then(res => { flowData.value = res.data || []; flowLoading.value = false })
}

function searchProductList(keyword) {
  productLoading.value = true
  searchProduct(keyword).then(res => { productOptions.value = res.data || []; productLoading.value = false })
}

function handleTabClick(tab) {
  if (tab.paneName === "stockIn" && stockInData.value.length === 0) getStockInSummary()
  else if (tab.paneName === "stockOut" && stockOutData.value.length === 0) getStockOutSummary()
  else if (tab.paneName === "turnover" && turnoverData.value.length === 0) getTurnover()
}

getStockInSummary()
</script>
