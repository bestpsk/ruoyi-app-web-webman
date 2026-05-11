<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch" label-width="68px">
      <el-form-item label="门店名称" prop="storeName">
        <el-input
          v-model="queryParams.storeName"
          placeholder="请输入门店名称"
          clearable
          style="width: 160px"
          @keyup.enter="handleQuery"
        />
      </el-form-item>
      <el-form-item label="所属企业" prop="enterpriseId">
        <el-select
          v-model="queryParams.enterpriseId"
          placeholder="请选择企业"
          clearable
          filterable
          @focus="handleEnterpriseFocus"
          @visible-change="handleEnterpriseVisibleChange"
          style="width: 200px"
        >
          <el-option
            v-for="item in enterpriseOptions"
            :key="item.enterpriseId"
            :label="item.enterpriseName"
            :value="item.enterpriseId"
          />
        </el-select>
      </el-form-item>
      <el-form-item label="负责人" prop="managerName">
        <el-input
          v-model="queryParams.managerName"
          placeholder="请输入负责人"
          clearable
          style="width: 160px"
          @keyup.enter="handleQuery"
        />
      </el-form-item>
      <el-form-item label="联系电话" prop="phone">
        <el-input
          v-model="queryParams.phone"
          placeholder="请输入联系电话"
          clearable
          style="width: 160px"
          @keyup.enter="handleQuery"
        />
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-select v-model="queryParams.status" placeholder="门店状态" clearable style="width: 160px">
          <el-option
            v-for="dict in sys_normal_disable"
            :key="dict.value"
            :label="dict.label"
            :value="dict.value"
          />
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
        <el-button icon="Refresh" @click="resetQuery">重置</el-button>
      </el-form-item>
    </el-form>

    <el-row :gutter="10" class="mb8">
      <el-col :span="1.5">
        <el-button type="primary" plain icon="Plus" @click="handleAdd" v-hasPermi="['business:store:add']">新增</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="success" plain icon="Edit" :disabled="single" @click="handleUpdate" v-hasPermi="['business:store:edit']">修改</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="danger" plain icon="Delete" :disabled="multiple" @click="handleDelete" v-hasPermi="['business:store:remove']">删除</el-button>
      </el-col>
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList"></right-toolbar>
    </el-row>

    <el-table v-loading="loading" :data="storeList" @selection-change="handleSelectionChange">
      <el-table-column type="selection" width="55" align="center" />
      <el-table-column label="门店名称" align="center" prop="storeName" :show-overflow-tooltip="true" />
      <el-table-column label="所属企业" align="center" prop="enterpriseName" :show-overflow-tooltip="true" />
      <el-table-column label="负责人" align="center" prop="managerName" width="100" />
      <el-table-column label="联系电话" align="center" prop="phone" width="120" />
      <el-table-column label="微信" align="center" prop="wechat" width="100" />
      <el-table-column label="地址" align="center" prop="address" :show-overflow-tooltip="true" />
      <el-table-column label="营业时间" align="center" prop="businessHours" width="120" />
      <el-table-column label="年业绩" align="center" prop="annualPerformance" width="100" />
      <el-table-column label="常来顾客数" align="center" prop="regularCustomers" width="100" />
      <el-table-column label="服务员工" align="center" prop="serverUserName" width="100" />
      <el-table-column label="创建人" align="center" prop="creatorName" width="100" />
      <el-table-column label="状态" align="center" prop="status" width="80">
        <template #default="scope">
          <dict-tag :options="sys_normal_disable" :value="scope.row.status" />
        </template>
      </el-table-column>
      <el-table-column label="创建时间" align="center" prop="createTime" width="160">
        <template #default="scope">
          <span>{{ parseTime(scope.row.createTime) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center" width="150" class-name="small-padding fixed-width">
        <template #default="scope">
          <el-button link type="primary" icon="Edit" @click="handleUpdate(scope.row)" v-hasPermi="['business:store:edit']">修改</el-button>
          <el-button link type="primary" icon="Delete" @click="handleDelete(scope.row)" v-hasPermi="['business:store:remove']">删除</el-button>
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

    <el-dialog :title="title" v-model="open" width="650px" append-to-body>
      <el-form ref="storeRef" :model="form" :rules="rules" label-width="100px">
        <el-row>
          <el-col :span="12">
            <el-form-item label="所属企业" prop="enterpriseId">
              <el-select
                v-model="form.enterpriseId"
                placeholder="请选择企业"
                filterable
                @focus="handleEnterpriseFocus"
                @change="handleEnterpriseChange"
                style="width: 100%"
              >
                <el-option
                  v-for="item in enterpriseOptions"
                  :key="item.enterpriseId"
                  :label="item.enterpriseName"
                  :value="item.enterpriseId"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="门店名称" prop="storeName">
              <el-input v-model="form.storeName" placeholder="请输入门店名称" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="负责人" prop="managerName">
              <el-input v-model="form.managerName" placeholder="请输入门店负责人" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系电话" prop="phone">
              <el-input v-model="form.phone" placeholder="请输入联系电话" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="微信" prop="wechat">
              <el-input v-model="form.wechat" placeholder="请输入微信" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="营业时间" prop="businessTimeRange">
              <el-time-picker
                v-model="form.businessTimeRange"
                is-range
                range-separator="-"
                start-placeholder="开始时间"
                end-placeholder="结束时间"
                format="HH:mm"
                value-format="HH:mm"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item label="地址" prop="address">
              <el-input v-model="form.address" placeholder="请输入地址" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="年业绩" prop="annualPerformance">
              <el-input-number v-model="form.annualPerformance" controls-position="right" :min="0" :precision="2" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="常来顾客数" prop="regularCustomers">
              <el-input-number v-model="form.regularCustomers" controls-position="right" :min="0" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="服务员工" prop="serverUserId">
              <el-select
                v-model="form.serverUserId"
                placeholder="请选择服务员工"
                filterable
                @focus="handleUserFocus"
                @change="handleUserChange"
                style="width: 100%"
              >
                <el-option
                  v-for="item in userOptions"
                  :key="item.userId"
                  :label="item.realName || item.nickName || item.userName"
                  :value="item.userId"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="创建人">
              <el-input v-model="form.creatorName" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="状态" prop="status">
              <el-radio-group v-model="form.status">
                <el-radio
                  v-for="dict in sys_normal_disable"
                  :key="dict.value"
                  :value="dict.value"
                >{{ dict.label }}</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item label="备注" prop="remark">
              <el-input v-model="form.remark" type="textarea" placeholder="请输入内容" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <div class="dialog-footer">
          <el-button type="primary" @click="submitForm">确 定</el-button>
          <el-button @click="cancel">取 消</el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup name="Store">
import { listStore, getStore, delStore, addStore, updateStore } from "@/api/business/store"
import { searchEnterprise as searchEnterpriseApi } from "@/api/business/enterprise"
import { listUser } from "@/api/system/user"
import useUserStore from '@/store/modules/user'

const userStore = useUserStore()
const { proxy } = getCurrentInstance()
const { sys_normal_disable } = useDict("sys_normal_disable")

const storeList = ref([])
const open = ref(false)
const loading = ref(true)
const showSearch = ref(true)
const ids = ref([])
const single = ref(true)
const multiple = ref(true)
const total = ref(0)
const title = ref("")
const enterpriseOptions = ref([])
const userOptions = ref([])

const data = reactive({
  form: {},
  queryParams: {
    pageNum: 1,
    pageSize: 10,
    storeName: undefined,
    enterpriseId: undefined,
    managerName: undefined,
    phone: undefined,
    status: undefined
  },
  rules: {
    enterpriseId: [{ required: true, message: "所属企业不能为空", trigger: "change" }],
    storeName: [{ required: true, message: "门店名称不能为空", trigger: "blur" }]
  }
})

const { queryParams, form, rules } = toRefs(data)

function getList() {
  loading.value = true
  listStore(queryParams.value).then(response => {
    storeList.value = response.rows
    total.value = response.total
    loading.value = false
  })
}

function cancel() {
  open.value = false
  reset()
}

function reset() {
  form.value = {
    storeId: undefined,
    enterpriseId: undefined,
    enterpriseName: undefined,
    storeName: undefined,
    managerName: undefined,
    phone: undefined,
    wechat: undefined,
    address: undefined,
    businessHours: undefined,
    businessTimeRange: undefined,
    annualPerformance: 0,
    regularCustomers: 0,
    creatorName: undefined,
    serverUserId: undefined,
    serverUserName: undefined,
    status: "0",
    remark: undefined
  }
  proxy.resetForm("storeRef")
}

function handleQuery() {
  queryParams.value.pageNum = 1
  getList()
}

function resetQuery() {
  proxy.resetForm("queryRef")
  handleQuery()
}

function handleSelectionChange(selection) {
  ids.value = selection.map(item => item.storeId)
  single.value = selection.length != 1
  multiple.value = !selection.length
}

function handleEnterpriseFocus() {
  if (enterpriseOptions.value.length === 0) {
    loadEnterpriseList()
  }
}

function handleEnterpriseVisibleChange(visible) {
  if (visible && enterpriseOptions.value.length === 0) {
    loadEnterpriseList()
  }
}

function loadEnterpriseList(keyword = '') {
  searchEnterpriseApi(keyword).then(response => {
    enterpriseOptions.value = response.data || []
  })
}

function handleUserFocus() {
  if (userOptions.value.length === 0) {
    loadUserList()
  }
}

function loadUserList(keyword = '') {
  listUser({ userName: keyword, pageSize: 50 }).then(response => {
    userOptions.value = response.rows || []
  })
}

function handleEnterpriseChange(val) {
  const enterprise = enterpriseOptions.value.find(item => item.enterpriseId === val)
  if (enterprise) {
    form.value.enterpriseName = enterprise.enterpriseName
  }
}

function handleUserChange(val) {
  const user = userOptions.value.find(item => item.userId === val)
  if (user) {
    form.value.serverUserName = user.realName || user.nickName || user.userName
  }
}

function handleAdd() {
  reset()
  loadEnterpriseList()
  loadUserList()
  form.value.creatorName = userStore.realName || userStore.name || ''
  open.value = true
  title.value = "添加门店"
}

function handleUpdate(row) {
  reset()
  loadEnterpriseList()
  loadUserList()
  const storeId = row.storeId || ids.value
  getStore(storeId).then(response => {
    form.value = response.data
    if (form.value.businessHours) {
      const times = form.value.businessHours.split(' - ')
      if (times.length === 2) {
        form.value.businessTimeRange = [times[0], times[1]]
      }
    }
    open.value = true
    title.value = "修改门店"
  })
}

function submitForm() {
  proxy.$refs["storeRef"].validate(valid => {
    if (valid) {
      if (form.value.businessTimeRange && form.value.businessTimeRange.length === 2) {
        form.value.businessHours = form.value.businessTimeRange[0] + ' - ' + form.value.businessTimeRange[1]
      } else {
        form.value.businessHours = undefined
      }
      const submitData = { ...form.value }
      delete submitData.businessTimeRange
      if (form.value.storeId != undefined) {
        updateStore(submitData).then(() => {
          proxy.$modal.msgSuccess("修改成功")
          open.value = false
          getList()
        })
      } else {
        addStore(submitData).then(() => {
          proxy.$modal.msgSuccess("新增成功")
          open.value = false
          getList()
        })
      }
    }
  })
}

function handleDelete(row) {
  const storeIds = row.storeId || ids.value
  proxy.$modal.confirm('是否确认删除门店编号为"' + storeIds + '"的数据项？').then(function() {
    return delStore(storeIds)
  }).then(() => {
    getList()
    proxy.$modal.msgSuccess("删除成功")
  }).catch(() => {})
}

getList()
</script>
