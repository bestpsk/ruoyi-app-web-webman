<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch" label-width="80px">
      <el-form-item label="出货单号" prop="shipmentNo">
        <el-input v-model="queryParams.shipmentNo" placeholder="请输入出货单号" clearable style="width: 160px" @keyup.enter="handleQuery" />
      </el-form-item>
      <el-form-item label="企业名称" prop="enterpriseName">
        <el-input v-model="queryParams.enterpriseName" placeholder="请输入企业名称" clearable style="width: 160px" @keyup.enter="handleQuery" />
      </el-form-item>
      <el-form-item label="出货状态" prop="shipmentStatus">
        <el-select v-model="queryParams.shipmentStatus" placeholder="请选择" clearable style="width: 120px">
          <el-option label="待审核" value="0" />
          <el-option label="已审核" value="1" />
          <el-option label="已发货" value="2" />
          <el-option label="已收货" value="3" />
          <el-option label="已驳回" value="4" />
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
        <el-button icon="Refresh" @click="resetQuery">重置</el-button>
      </el-form-item>
    </el-form>

    <el-table v-loading="loading" :data="shipmentList">
      <el-table-column label="出货单号" prop="shipmentNo" width="130" />
      <el-table-column label="企业名称" prop="enterpriseName" min-width="150" />
      <el-table-column label="方案名称" prop="plan.planName" min-width="150" />
      <el-table-column label="总数量" prop="totalQuantity" width="80" align="center" />
      <el-table-column label="总金额" prop="totalAmount" width="100" align="right" />
      <el-table-column label="物流单号" prop="logisticsNo" width="120" />
      <el-table-column label="出货状态" prop="shipmentStatus" width="90" align="center">
        <template #default="scope">
          <el-tag :type="statusType(scope.row.shipmentStatus)">{{ statusLabel(scope.row.shipmentStatus) }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" prop="createTime" width="160" />
      <el-table-column label="操作" width="260" align="center">
        <template #default="scope">
          <el-button link type="primary" icon="View" @click="handleDetail(scope.row)">详情</el-button>
          <template v-if="scope.row.shipmentStatus === '0'">
            <el-button link type="primary" icon="Select" @click="handleAudit(scope.row, true)">通过</el-button>
            <el-button link type="danger" icon="CloseBold" @click="handleAudit(scope.row, false)">驳回</el-button>
          </template>
          <template v-if="scope.row.shipmentStatus === '1'">
            <el-button link type="primary" icon="Van" @click="handleShip(scope.row)">发货</el-button>
          </template>
          <template v-if="scope.row.shipmentStatus === '2'">
            <el-button link type="primary" icon="Check" @click="handleConfirmReceipt(scope.row)">确认收货</el-button>
          </template>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" v-model:page="queryParams.pageNum" v-model:limit="queryParams.pageSize" @pagination="getList" />

    <el-dialog title="出货单详情" v-model="detailOpen" width="900px" append-to-body>
      <el-descriptions :column="3" border>
        <el-descriptions-item label="出货单号">{{ currentShipment.shipmentNo }}</el-descriptions-item>
        <el-descriptions-item label="企业名称">{{ currentShipment.enterpriseName }}</el-descriptions-item>
        <el-descriptions-item label="方案名称">{{ currentShipment.plan?.planName }}</el-descriptions-item>
        <el-descriptions-item label="收货人">{{ currentShipment.contactPerson }}</el-descriptions-item>
        <el-descriptions-item label="收货电话">{{ currentShipment.contactPhone }}</el-descriptions-item>
        <el-descriptions-item label="收货地址">{{ currentShipment.shippingAddress }}</el-descriptions-item>
        <el-descriptions-item label="总数量">{{ currentShipment.totalQuantity }}</el-descriptions-item>
        <el-descriptions-item label="总金额">{{ currentShipment.totalAmount }}</el-descriptions-item>
        <el-descriptions-item label="出货状态">
          <el-tag :type="statusType(currentShipment.shipmentStatus)">{{ statusLabel(currentShipment.shipmentStatus) }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="物流公司">{{ currentShipment.logisticsCompany || '-' }}</el-descriptions-item>
        <el-descriptions-item label="物流单号">{{ currentShipment.logisticsNo || '-' }}</el-descriptions-item>
        <el-descriptions-item label="发货日期">{{ currentShipment.shipmentDate || '-' }}</el-descriptions-item>
      </el-descriptions>
      <el-divider content-position="left">出货明细</el-divider>
      <el-table :data="currentShipment.items || []" border size="small">
        <el-table-column label="货品名称" prop="productName" />
        <el-table-column label="供货商" prop="supplierName" />
        <el-table-column label="单位类型" width="80" align="center">
          <template #default="scope">{{ scope.row.unitType === '1' ? '主单位整' : '副单位拆' }}</template>
        </el-table-column>
        <el-table-column label="数量" prop="quantity" width="80" align="center" />
        <el-table-column label="规格" prop="spec" width="60" align="center" />
        <el-table-column label="单价" prop="salePrice" width="90" align="right" />
        <el-table-column label="折扣单价" prop="discountPrice" width="90" align="right" />
        <el-table-column label="总金额" prop="amount" width="100" align="right" />
      </el-table>
    </el-dialog>

    <el-dialog title="发货" v-model="shipOpen" width="500px" append-to-body>
      <el-form ref="shipRef" :model="shipForm" label-width="100px">
        <el-form-item label="物流公司" prop="logisticsCompany">
          <el-input v-model="shipForm.logisticsCompany" placeholder="请输入物流公司" />
        </el-form-item>
        <el-form-item label="物流单号" prop="logisticsNo">
          <el-input v-model="shipForm.logisticsNo" placeholder="请输入物流单号" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button type="primary" @click="submitShip">确认发货</el-button>
        <el-button @click="shipOpen = false">取 消</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup name="EnterpriseShipment">
import { listShipment, getShipment, auditShipment, shipShipment, confirmReceipt } from "@/api/business/shipment"

const { proxy } = getCurrentInstance()

const shipmentList = ref([])
const loading = ref(true)
const showSearch = ref(true)
const total = ref(0)
const detailOpen = ref(false)
const currentShipment = ref({})
const shipOpen = ref(false)
const shipForm = ref({})

const data = reactive({
  queryParams: { pageNum: 1, pageSize: 10, shipmentNo: undefined, enterpriseName: undefined, shipmentStatus: undefined }
})

const { queryParams } = toRefs(data)

function statusType(status) {
  const map = { '0': 'warning', '1': '', '2': 'info', '3': 'success', '4': 'danger' }
  return map[status] || 'info'
}

function statusLabel(status) {
  const map = { '0': '待审核', '1': '已审核', '2': '已发货', '3': '已收货', '4': '已驳回' }
  return map[status] || '未知'
}

function getList() {
  loading.value = true
  listShipment(queryParams.value).then(res => {
    shipmentList.value = res.rows
    total.value = res.total
    loading.value = false
  })
}

function handleQuery() { queryParams.value.pageNum = 1; getList() }
function resetQuery() { proxy.resetForm("queryRef"); handleQuery() }

function handleDetail(row) {
  getShipment(row.shipmentId).then(res => {
    currentShipment.value = res.data
    detailOpen.value = true
  })
}

function handleAudit(row, passed) {
  const text = passed ? '通过' : '驳回'
  proxy.$modal.confirm('确认' + text + '？').then(() => {
    return auditShipment({ shipmentId: row.shipmentId, passed })
  }).then(() => { proxy.$modal.msgSuccess(text + "成功"); getList() }).catch(() => {})
}

function handleShip(row) {
  shipForm.value = { shipmentId: row.shipmentId, logisticsCompany: '', logisticsNo: '' }
  shipOpen.value = true
}

function submitShip() {
  shipShipment(shipForm.value).then(() => {
    proxy.$modal.msgSuccess("发货成功")
    shipOpen.value = false
    getList()
  })
}

function handleConfirmReceipt(row) {
  proxy.$modal.confirm('确认企业已收到货？').then(() => {
    return confirmReceipt(row.shipmentId)
  }).then(() => { proxy.$modal.msgSuccess("确认收货成功"); getList() }).catch(() => {})
}

getList()
</script>
