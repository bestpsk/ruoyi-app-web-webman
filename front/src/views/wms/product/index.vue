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
      <el-form-item label="状态" prop="status">
        <el-select v-model="queryParams.status" placeholder="请选择状态" clearable style="width: 160px">
          <el-option v-for="dict in sys_normal_disable" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
        <el-button icon="Refresh" @click="resetQuery">重置</el-button>
      </el-form-item>
    </el-form>

    <el-row :gutter="10" class="mb8">
      <el-col :span="1.5">
        <el-button type="primary" plain icon="Plus" @click="handleAdd" v-hasPermi="['wms:product:add']">新增</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="success" plain icon="Edit" :disabled="single" @click="handleUpdate" v-hasPermi="['wms:product:edit']">修改</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="danger" plain icon="Delete" :disabled="multiple" @click="handleDelete" v-hasPermi="['wms:product:remove']">删除</el-button>
      </el-col>
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList" />
    </el-row>

    <el-table v-loading="loading" :data="productList" @selection-change="handleSelectionChange">
      <el-table-column type="selection" width="50" align="center" />
      <el-table-column label="货品编码" prop="productCode" width="120" />
      <el-table-column label="品名" prop="productName" width="140" show-overflow-tooltip />
      <el-table-column label="供货商" prop="supplierId" width="110" />
      <el-table-column label="类别" prop="category" width="95" align="center">
        <template #default="scope">
          <dict-tag :options="biz_product_category" :value="scope.row.category" />
        </template>
      </el-table-column>
      <el-table-column label="单位(整)" prop="unit" width="75" align="center">
        <template #default="scope">
          <dict-tag :options="biz_product_unit" :value="scope.row.unit" />
        </template>
      </el-table-column>
      <el-table-column label="规格(拆)" prop="spec" width="75" align="center">
        <template #default="scope">
          <dict-tag :options="biz_product_spec" :value="scope.row.spec" />
        </template>
      </el-table-column>
      <el-table-column label="包装数量" prop="packQty" width="85" align="center">
        <template #default="scope">
          <span>{{ scope.row.packQty || 1 }}{{ scope.row.spec ? getSpecLabel(scope.row.spec) : '' }}/{{ getUnitLabel(scope.row.unit) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="进货价" prop="purchasePrice" width="90" align="right" />
      <el-table-column label="出货价(整)" prop="salePrice" width="100" align="right" />
      <el-table-column label="出货价(拆)" prop="salePriceSpec" width="100" align="right" />
      <el-table-column label="预警数量" prop="warnQty" width="75" align="center" />
      <el-table-column label="状态" prop="status" width="70" align="center">
        <template #default="scope">
          <el-switch v-model="scope.row.status" active-value="0" inactive-value="1"
            @change="(val) => handleStatusChange(scope.row, val)" />
        </template>
      </el-table-column>
      <el-table-column label="操作" min-width="150" align="center">
        <template #default="scope">
          <el-button link type="primary" icon="Edit" @click="handleUpdate(scope.row)" v-hasPermi="['wms:product:edit']">修改</el-button>
          <el-button link type="primary" icon="Delete" @click="handleDelete(scope.row)" v-hasPermi="['wms:product:remove']">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" v-model:page="queryParams.pageNum" v-model:limit="queryParams.pageSize" @pagination="getList" />

    <el-dialog :title="title" v-model="open" width="700px" append-to-body>
      <el-form ref="productRef" :model="form" :rules="rules" label-width="100px">
        <el-row>
          <el-col :span="12">
            <el-form-item label="品名" prop="productName">
              <el-input v-model="form.productName" placeholder="请输入品名" @blur="onProductNameBlur" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="货品编码" prop="productCode">
              <el-input v-model="form.productCode" placeholder="根据品名自动生成，可修改" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="供货商" prop="supplierId">
              <el-select v-model="form.supplierId" placeholder="请选择供货商" filterable remote :remote-method="searchSupplierList" :loading="supplierLoading" style="width: 100%">
                <el-option v-for="item in supplierOptions" :key="item.supplierId" :label="item.supplierName" :value="item.supplierId" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="类别" prop="category">
              <el-select v-model="form.category" placeholder="请选择类别" style="width: 100%">
                <el-option v-for="dict in biz_product_category" :key="dict.value" :label="dict.label" :value="dict.value" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item prop="unit">
              <template #label>
                <span>单位(整)</span>
                <el-tooltip content="进货时的主包装单位，如盒、箱等" placement="top">
                  <el-icon class="field-tip"><QuestionFilled /></el-icon>
                </el-tooltip>
              </template>
              <el-select v-model="form.unit" placeholder="请选择" style="width: 100%">
                <el-option v-for="dict in biz_product_unit" :key="dict.value" :label="dict.label" :value="dict.value" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item prop="spec">
              <template #label>
                <span>规格(拆)</span>
                <el-tooltip content="拆分出货的最小单位，如支、瓶等" placement="top">
                  <el-icon class="field-tip"><QuestionFilled /></el-icon>
                </el-tooltip>
              </template>
              <el-select v-model="form.spec" placeholder="请选择" style="width: 100%">
                <el-option v-for="dict in biz_product_spec" :key="dict.value" :label="dict.label" :value="dict.value" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item prop="packQty">
              <template #label>
                <span>包装数量</span>
                <el-tooltip content="1个主单位包含多少副单位？例：1盒=10支则填10" placement="top">
                  <el-icon class="field-tip"><QuestionFilled /></el-icon>
                </el-tooltip>
              </template>
              <el-input-number v-model="form.packQty" :min="1" style="width: 100%" @change="calcSalePriceSpec" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item prop="purchasePrice">
              <template #label>
                <span>进货价</span>
                <el-tooltip content="按主单位的采购价格" placement="top">
                  <el-icon class="field-tip"><QuestionFilled /></el-icon>
                </el-tooltip>
              </template>
              <el-input-number v-model="form.purchasePrice" :precision="2" :min="0" style="width: 100%" @change="onPurchasePriceChange" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item prop="salePrice">
              <template #label>
                <span>出货价(整)</span>
                <el-tooltip content="整件出货的价格，默认等于进货价" placement="top">
                  <el-icon class="field-tip"><QuestionFilled /></el-icon>
                </el-tooltip>
              </template>
              <el-input-number v-model="form.salePrice" :precision="2" :min="0" style="width: 100%" @change="calcSalePriceSpec" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item prop="salePriceSpec">
              <template #label>
                <span>出货价(拆)</span>
                <el-tooltip content="拆分出货的单价 = 出货价(整) ÷ 包装数量，可修改" placement="top">
                  <el-icon class="field-tip"><QuestionFilled /></el-icon>
                </el-tooltip>
              </template>
              <el-input-number v-model="form.salePriceSpec" :precision="2" :min="0" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="预警数量" prop="warnQty">
              <el-input-number v-model="form.warnQty" :min="0" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="状态" prop="status">
              <el-radio-group v-model="form.status">
                <el-radio v-for="dict in sys_normal_disable" :key="dict.value" :value="dict.value">{{ dict.label }}</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="备注" prop="remark">
              <el-input v-model="form.remark" type="textarea" placeholder="请输入备注" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <el-button type="primary" @click="submitForm">确 定</el-button>
        <el-button @click="cancel">取 消</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup name="WmsProduct">
import { listProduct, getProduct, delProduct, addProduct, updateProduct } from "@/api/wms/product"
import { searchSupplier } from "@/api/wms/supplier"
import { generateProductCode } from "@/utils/pinyin"
import { QuestionFilled } from '@element-plus/icons-vue'

const { proxy } = getCurrentInstance()
const { sys_normal_disable, biz_product_category, biz_product_unit, biz_product_spec } = useDict("sys_normal_disable", "biz_product_category", "biz_product_unit", "biz_product_spec")

const productList = ref([])
const open = ref(false)
const loading = ref(true)
const showSearch = ref(true)
const ids = ref([])
const single = ref(true)
const multiple = ref(true)
const total = ref(0)
const title = ref("")
const supplierOptions = ref([])
const supplierLoading = ref(false)
const isAutoCode = ref(true)

const data = reactive({
  form: {},
  queryParams: { pageNum: 1, pageSize: 10, productName: undefined, productCode: undefined, category: undefined, status: undefined },
  rules: {
    productName: [{ required: true, message: "品名不能为空", trigger: "blur" }],
    productCode: [{ required: true, message: "货品编码不能为空", trigger: "blur" }],
    category: [{ required: true, message: "类别不能为空", trigger: "change" }],
    unit: [{ required: true, message: "单位不能为空", trigger: "change" }]
  }
})
const { queryParams, form, rules } = toRefs(data)

function getUnitLabel(value) {
  if (!value) return ''
  const dict = biz_product_unit.value?.find(d => d.value === value)
  return dict ? dict.label : ''
}

function getSpecLabel(value) {
  if (!value) return ''
  const dict = biz_product_spec.value?.find(d => d.value === value)
  return dict ? dict.label : ''
}

function onProductNameBlur() {
  if (!form.value.productCode && form.value.productName) {
    form.value.productCode = generateProductCode(form.value.productName)
    isAutoCode.value = true
  }
}

function onPurchasePriceChange() {
  if (isAutoCode.value || form.value.salePrice === 0) {
    form.value.salePrice = form.value.purchasePrice
    calcSalePriceSpec()
  }
}

function calcSalePriceSpec() {
  if (form.value.packQty && form.value.packQty > 0 && form.value.salePrice) {
    form.value.salePriceSpec = Math.round((form.value.salePrice / form.value.packQty) * 100) / 100
  }
}

function getList() {
  loading.value = true
  listProduct(queryParams.value).then(response => {
    productList.value = response.rows
    total.value = response.total
    loading.value = false
  })
}

function handleQuery() { queryParams.value.pageNum = 1; getList() }
function resetQuery() { proxy.resetForm("queryRef"); handleQuery() }
function handleSelectionChange(selection) { ids.value = selection.map(item => item.productId); single.value = selection.length !== 1; multiple.value = !selection.length }

function reset() {
  form.value = { productId: undefined, productName: undefined, productCode: undefined, supplierId: undefined, category: "1", unit: "5", spec: "1", packQty: 1, purchasePrice: 0, salePrice: 0, salePriceSpec: 0, warnQty: 0, status: "0", remark: undefined }
  isAutoCode.value = true
  supplierOptions.value = []
  proxy.resetForm("productRef")
}

function handleAdd() { reset(); open.value = true; title.value = "添加货品" }

function handleUpdate(row) {
  reset()
  const productId = row.productId || ids.value
  getProduct(productId).then(response => {
    form.value = response.data
    isAutoCode.value = false
    if (form.value.supplierId) {
      searchSupplier("").then(res => { supplierOptions.value = res.data || [] })
    }
    open.value = true; title.value = "修改货品"
  })
}

function submitForm() {
  proxy.$refs["productRef"].validate(valid => {
    if (valid) {
      if (form.value.productId != undefined) {
        updateProduct(form.value).then(() => { proxy.$modal.msgSuccess("修改成功"); open.value = false; getList() })
      } else {
        addProduct(form.value).then(() => { proxy.$modal.msgSuccess("新增成功"); open.value = false; getList() })
      }
    }
  })
}

function handleDelete(row) {
  const productIds = row.productId || ids.value
  proxy.$modal.confirm('是否确认删除？').then(() => delProduct(productIds)).then(() => { getList(); proxy.$modal.msgSuccess("删除成功") }).catch(() => {})
}

function handleStatusChange(row, val) {
  const text = val === '0' ? '启用' : '停用'
  proxy.$modal.confirm('确认要"' + text + '""' + row.productName + '"吗？').then(() => {
    return updateProduct({ productId: row.productId, status: val })
  }).then(() => { proxy.$modal.msgSuccess(text + "成功") }).catch(() => {
    row.status = val === '0' ? '1' : '0'
  })
}

function cancel() { open.value = false; reset() }

function searchSupplierList(keyword) {
  supplierLoading.value = true
  searchSupplier(keyword).then(res => { supplierOptions.value = res.data || []; supplierLoading.value = false })
}

getList()
</script>

<style scoped>
.field-tip {
  margin-left: 4px;
  color: #909399;
  cursor: help;
  vertical-align: middle;
  font-size: 14px;
}
</style>
