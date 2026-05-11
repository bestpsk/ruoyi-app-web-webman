<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch" label-width="68px">
      <el-form-item label="订单编号" prop="orderNo">
        <el-input
          v-model="queryParams.orderNo"
          placeholder="请输入订单编号"
          clearable
          style="width: 160px"
          @keyup.enter="handleQuery"
        />
      </el-form-item>
      <el-form-item label="客户姓名" prop="customerName">
        <el-input
          v-model="queryParams.customerName"
          placeholder="请输入客户姓名"
          clearable
          style="width: 120px"
          @keyup.enter="handleQuery"
        />
      </el-form-item>
      <el-form-item label="企业" prop="enterpriseId">
        <el-select v-model="queryParams.enterpriseId" placeholder="请选择企业" filterable clearable style="width: 160px" @change="handleEnterpriseChange">
          <el-option v-for="item in enterpriseOptions" :key="item.enterpriseId" :label="item.enterpriseName" :value="item.enterpriseId" />
        </el-select>
      </el-form-item>
      <el-form-item label="门店" prop="storeId">
        <el-select v-model="queryParams.storeId" placeholder="请选择门店" filterable clearable style="width: 140px">
          <el-option v-for="item in storeOptions" :key="item.storeId" :label="item.storeName" :value="item.storeId" />
        </el-select>
      </el-form-item>
      <el-form-item label="企业审核" prop="enterpriseAuditStatus">
        <el-select v-model="queryParams.enterpriseAuditStatus" placeholder="企业审核" clearable style="width: 100px">
          <el-option label="未审核" value="0" />
          <el-option label="已审核" value="1" />
        </el-select>
      </el-form-item>
      <el-form-item label="财务审核" prop="financeAuditStatus">
        <el-select v-model="queryParams.financeAuditStatus" placeholder="财务审核" clearable style="width: 100px">
          <el-option label="未审核" value="0" />
          <el-option label="已审核" value="1" />
        </el-select>
      </el-form-item>
      <el-form-item label="创建时间" prop="dateRange">
        <el-date-picker
          v-model="dateRange"
          type="daterange"
          range-separator="-"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          value-format="YYYY-MM-DD"
          style="width: 200px"
        />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
        <el-button icon="Refresh" @click="resetQuery">重置</el-button>
      </el-form-item>
    </el-form>

    <el-row :gutter="10" class="mb8">
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList"></right-toolbar>
    </el-row>

    <el-table v-loading="loading" :data="orderList">
      <el-table-column label="订单编号" align="center" prop="orderNo" width="150" />
      <el-table-column label="客户姓名" align="center" prop="customerName" width="100" />
      <el-table-column label="企业名称" align="center" prop="enterpriseName" :show-overflow-tooltip="true" min-width="120" />
      <el-table-column label="门店名称" align="center" prop="storeName" width="100" :show-overflow-tooltip="true" />
      <el-table-column label="套餐名称" align="center" prop="packageName" width="120" :show-overflow-tooltip="true" />
      <el-table-column label="方案金额" align="right" prop="totalAmount" width="100">
        <template #default="scope">{{ Number(scope.row.totalAmount || 0).toFixed(2) }}</template>
      </el-table-column>
      <el-table-column label="成交金额" align="right" prop="dealAmount" width="100">
        <template #default="scope">
          <span style="color: #409eff; font-weight: 500">{{ Number(scope.row.dealAmount || 0).toFixed(2) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="实付金额" align="right" prop="paidAmount" width="100">
        <template #default="scope">
          <span style="color: #67c23a; font-weight: 500">{{ Number(scope.row.paidAmount || 0).toFixed(2) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="欠款金额" align="right" prop="owedAmount" width="100">
        <template #default="scope">
          <span :style="{ color: scope.row.owedAmount > 0 ? '#f56c6c' : '#909399', fontWeight: '500' }">{{ Number(scope.row.owedAmount || 0).toFixed(2) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="企业审核" align="center" width="100">
        <template #default="scope">
          <el-switch
            v-model="scope.row.enterpriseAuditStatus"
            active-value="1"
            inactive-value="0"
            :disabled="scope.row.enterpriseAuditStatus === '1'"
            @change="handleEnterpriseAudit(scope.row)"
            v-hasPermi="['business:order:enterpriseAudit']"
          />
        </template>
      </el-table-column>
      <el-table-column label="财务审核" align="center" width="100">
        <template #default="scope">
          <el-switch
            v-model="scope.row.financeAuditStatus"
            active-value="1"
            inactive-value="0"
            :disabled="scope.row.financeAuditStatus === '1'"
            @change="handleFinanceAudit(scope.row)"
            v-hasPermi="['business:order:financeAudit']"
          />
        </template>
      </el-table-column>
      <el-table-column label="开单员工" align="center" prop="creatorUserName" width="90" />
      <el-table-column label="创建时间" align="center" prop="createTime" width="160">
        <template #default="scope">
          <span>{{ parseTime(scope.row.createTime) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center" width="100" class-name="small-padding fixed-width">
        <template #default="scope">
          <el-button link type="primary" icon="View" @click="handleView(scope.row)">详情</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total > 0"
      :total="total"
      v-model:page="queryParams.pageNum"
      v-model:limit="queryParams.pageSize"
      @pagination="getList"
    />

    <el-dialog title="订单详情" v-model="viewOpen" width="700px" append-to-body>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="订单编号">{{ viewForm.orderNo }}</el-descriptions-item>
        <el-descriptions-item label="客户姓名">{{ viewForm.customerName }}</el-descriptions-item>
        <el-descriptions-item label="企业名称">{{ viewForm.enterpriseName }}</el-descriptions-item>
        <el-descriptions-item label="门店名称">{{ viewForm.storeName }}</el-descriptions-item>
        <el-descriptions-item label="套餐名称" :span="2">{{ viewForm.packageName || '-' }}</el-descriptions-item>
        <el-descriptions-item label="方案金额">{{ Number(viewForm.totalAmount || 0).toFixed(2) }} 元</el-descriptions-item>
        <el-descriptions-item label="成交金额">{{ Number(viewForm.dealAmount || 0).toFixed(2) }} 元</el-descriptions-item>
        <el-descriptions-item label="实付金额">{{ Number(viewForm.paidAmount || 0).toFixed(2) }} 元</el-descriptions-item>
        <el-descriptions-item label="欠款金额">{{ Number(viewForm.owedAmount || 0).toFixed(2) }} 元</el-descriptions-item>
        <el-descriptions-item label="订单状态">
          <el-tag :type="viewForm.orderStatus === '1' ? 'success' : viewForm.orderStatus === '2' ? 'info' : viewForm.orderStatus === '3' ? 'warning' : viewForm.orderStatus === '4' ? 'info' : 'warning'" size="small">{{ getOrderStatusName(viewForm.orderStatus) }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="企业审核">
          <el-tag :type="viewForm.enterpriseAuditStatus === '1' ? 'success' : 'warning'" size="small">{{ viewForm.enterpriseAuditStatus === '1' ? '已审核' : '未审核' }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="财务审核">
          <el-tag :type="viewForm.financeAuditStatus === '1' ? 'success' : 'warning'" size="small">{{ viewForm.financeAuditStatus === '1' ? '已审核' : '未审核' }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="开单员工">{{ viewForm.creatorUserName }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ parseTime(viewForm.createTime) }}</el-descriptions-item>
        <el-descriptions-item label="顾客反馈" :span="2">{{ viewForm.customerFeedback || '-' }}</el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ viewForm.remark || '-' }}</el-descriptions-item>
      </el-descriptions>
      <div style="margin-top: 16px" v-if="viewForm.items && viewForm.items.length > 0">
        <h4 style="margin-bottom: 12px">订单明细</h4>
        <el-table :data="viewForm.items" border size="small">
          <el-table-column label="品项名称" prop="productName" />
          <el-table-column label="次数" prop="quantity" width="70" align="center" />
          <el-table-column label="方案价格" prop="planPrice" width="100" align="right">
            <template #default="scope">{{ Number(scope.row.planPrice || 0).toFixed(2) }}</template>
          </el-table-column>
          <el-table-column label="是否成交" width="80" align="center">
            <template #default="scope">
              <el-tag :type="scope.row.isDeal === 1 ? 'success' : 'info'" size="small">{{ scope.row.isDeal === 1 ? '已成交' : '未成交' }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="成交金额" prop="dealAmount" width="100" align="right">
            <template #default="scope">{{ Number(scope.row.dealAmount || 0).toFixed(2) }}</template>
          </el-table-column>
          <el-table-column label="备注" prop="remark" :show-overflow-tooltip="true" />
        </el-table>
      </div>
      <template #footer>
        <el-button @click="viewOpen = false">关 闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup name="Order">
import { listSalesOrder, getSalesOrder, enterpriseAudit, financeAudit } from "@/api/business/salesOrder"
import { searchEnterprise } from "@/api/business/enterprise"
import { searchStore } from "@/api/business/store"

const { proxy } = getCurrentInstance()

function getOrderStatusName(status) {
  const map = { '0': '未成交', '1': '已成交', '2': '已用完', '3': '还款', '4': '已取消' }
  return map[status] || '未知'
}

const orderList = ref([])
const loading = ref(true)
const showSearch = ref(true)
const total = ref(0)
const dateRange = ref([])
const enterpriseOptions = ref([])
const storeOptions = ref([])
const viewOpen = ref(false)
const viewForm = ref({})

const data = reactive({
  queryParams: {
    pageNum: 1,
    pageSize: 10,
    orderNo: undefined,
    customerName: undefined,
    enterpriseId: undefined,
    storeId: undefined,
    orderStatus: undefined,
    enterpriseAuditStatus: undefined,
    financeAuditStatus: undefined
  }
})

const { queryParams } = toRefs(data)

function getList() {
  loading.value = true
  const params = { ...queryParams.value }
  if (dateRange.value && dateRange.value.length === 2) {
    params.startDate = dateRange.value[0]
    params.endDate = dateRange.value[1]
  }
  listSalesOrder(params).then(response => {
    orderList.value = response.rows
    total.value = response.total
    loading.value = false
  })
}

function loadEnterpriseList() {
  searchEnterprise('').then(res => {
    enterpriseOptions.value = res.data || []
  })
}

function handleEnterpriseChange(val) {
  queryParams.value.storeId = undefined
  storeOptions.value = []
  if (val) {
    searchStore('', val).then(res => {
      storeOptions.value = res.data || []
    })
  }
}

function handleQuery() {
  queryParams.value.pageNum = 1
  getList()
}

function resetQuery() {
  dateRange.value = []
  proxy.resetForm("queryRef")
  handleQuery()
}

function handleView(row) {
  getSalesOrder(row.orderId).then(response => {
    viewForm.value = response.data
    viewOpen.value = true
  })
}

function handleEnterpriseAudit(row) {
  if (row.enterpriseAuditStatus === '1') {
    enterpriseAudit(row.orderId).then(() => {
      proxy.$modal.msgSuccess("企业审核成功")
      getList()
    }).catch(() => {
      row.enterpriseAuditStatus = '0'
    })
  }
}

function handleFinanceAudit(row) {
  if (row.financeAuditStatus === '1') {
    financeAudit(row.orderId).then(() => {
      proxy.$modal.msgSuccess("财务审核成功")
      getList()
    }).catch(() => {
      row.financeAuditStatus = '0'
    })
  }
}

loadEnterpriseList()
getList()
</script>
