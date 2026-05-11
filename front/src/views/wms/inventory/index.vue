<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch">
      <el-form-item label="品名" prop="productName">
        <el-input v-model="queryParams.productName" placeholder="请输入品名" clearable style="width: 160px" @keyup.enter="handleQuery" />
      </el-form-item>
      <el-form-item label="货品编码" prop="productCode">
        <el-input v-model="queryParams.productCode" placeholder="请输入货品编码" clearable style="width: 160px" @keyup.enter="handleQuery" />
      </el-form-item>
      <el-form-item label="类别" prop="category">
        <el-select v-model="queryParams.category" placeholder="请选择类别" clearable style="width: 160px">
          <el-option v-for="dict in biz_product_category" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="仅看预警" prop="isWarn">
        <el-switch v-model="warnOnly" active-value="1" inactive-value="" @change="handleQuery" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
        <el-button icon="Refresh" @click="resetQuery">重置</el-button>
      </el-form-item>
    </el-form>

    <el-row :gutter="10" class="mb8">
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList" />
    </el-row>

    <el-table v-loading="loading" :data="inventoryList" style="width: 100%">
      <el-table-column label="货品编码" min-width="120">
        <template #default="scope">
          <span>{{ scope.row.product?.productCode }}</span>
        </template>
      </el-table-column>
      <el-table-column label="品名" min-width="140">
        <template #default="scope">
          <span>{{ scope.row.product?.productName }}</span>
        </template>
      </el-table-column>
      <el-table-column label="类别" min-width="90" align="center">
        <template #default="scope">
          <dict-tag :options="biz_product_category" :value="scope.row.product?.category" />
        </template>
      </el-table-column>
      <el-table-column label="换算" min-width="100" align="center">
        <template #default="scope">
          <span v-if="scope.row.product?.packQty > 1">1{{ getUnitLabel(scope.row.product?.unit) }}={{ scope.row.product?.packQty }}{{ getSpecLabel(scope.row.product?.spec) }}</span>
          <span v-else>-</span>
        </template>
      </el-table-column>
      <el-table-column label="当前库存" min-width="140" align="center">
        <template #default="scope">
          <span :style="{ color: scope.row.quantity <= scope.row.warnQty ? '#F56C6C' : '' }">{{ formatInventoryQty(scope.row) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="预警数量" min-width="100" align="center">
        <template #default="scope">
          <span>{{ scope.row.warnQty }}{{ getSpecLabel(scope.row.product?.spec) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="库存状态" min-width="80" align="center">
        <template #default="scope">
          <el-tag v-if="scope.row.quantity <= scope.row.warnQty" type="danger">预警</el-tag>
          <el-tag v-else type="success">正常</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="进货价" min-width="80" align="right">
        <template #default="scope">
          <span>{{ scope.row.product?.purchasePrice }}</span>
        </template>
      </el-table-column>
      <el-table-column label="出货价" min-width="80" align="right">
        <template #default="scope">
          <span>{{ scope.row.product?.salePrice }}</span>
        </template>
      </el-table-column>
      <el-table-column label="最后入库" prop="lastStockInTime" min-width="130" align="center" />
      <el-table-column label="最后出库" prop="lastStockOutTime" min-width="130" align="center" />
    </el-table>

    <pagination v-show="total > 0" :total="total" v-model:page="queryParams.pageNum" v-model:limit="queryParams.pageSize" @pagination="getList" />
  </div>
</template>

<script setup name="WmsInventory">
import { listInventory } from "@/api/wms/inventory"

const { proxy } = getCurrentInstance()
const { biz_product_category, biz_product_unit, biz_product_spec } = useDict("biz_product_category", "biz_product_unit", "biz_product_spec")

const inventoryList = ref([])
const loading = ref(true)
const showSearch = ref(true)
const total = ref(0)
const warnOnly = ref("")

const data = reactive({
  queryParams: { pageNum: 1, pageSize: 10, productName: undefined, productCode: undefined, category: undefined, isWarn: undefined }
})
const { queryParams } = toRefs(data)

function getUnitLabel(value) { if (!value) return ''; const dict = biz_product_unit.value?.find(d => d.value === value); return dict ? dict.label : '' }
function getSpecLabel(value) { if (!value) return ''; const dict = biz_product_spec.value?.find(d => d.value === value); return dict ? dict.label : '' }

function formatInventoryQty(row) {
  const packQty = row.product?.packQty || 1
  const unitLabel = getUnitLabel(row.product?.unit)
  const specLabel = getSpecLabel(row.product?.spec)
  const qty = row.quantity || 0
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

function getList() {
  loading.value = true
  const params = { ...queryParams.value }
  if (warnOnly.value === "1") params.isWarn = "1"
  listInventory(params).then(response => {
    inventoryList.value = response.rows
    total.value = response.total
    loading.value = false
  })
}

function handleQuery() { queryParams.value.pageNum = 1; getList() }
function resetQuery() { warnOnly.value = ""; proxy.resetForm("queryRef"); handleQuery() }

getList()
</script>
