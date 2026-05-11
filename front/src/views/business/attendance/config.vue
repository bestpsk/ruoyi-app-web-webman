<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch" label-width="68px">
      <el-form-item label="配置名称" prop="configName">
        <el-input v-model="queryParams.configName" placeholder="请输入配置名称" clearable style="width: 160px" @keyup.enter="handleQuery" />
      </el-form-item>
      <el-form-item label="配置类型" prop="configType">
        <el-select v-model="queryParams.configType" placeholder="配置类型" clearable style="width: 160px">
          <el-option label="用户级" :value="1" />
          <el-option label="部门级" :value="2" />
        </el-select>
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-select v-model="queryParams.status" placeholder="状态" clearable style="width: 160px">
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
        <el-button type="primary" plain icon="Plus" @click="handleAdd">新增</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="success" plain icon="Edit" :disabled="single" @click="handleUpdate">修改</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="danger" plain icon="Delete" :disabled="multiple" @click="handleDelete">删除</el-button>
      </el-col>
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList"></right-toolbar>
    </el-row>

    <el-table v-loading="loading" :data="configList" @selection-change="handleSelectionChange">
      <el-table-column type="selection" width="55" align="center" />
      <el-table-column label="配置名称" align="center" prop="configName" min-width="120" />
      <el-table-column label="配置类型" align="center" prop="configType" min-width="100">
        <template #default="scope">
          <el-tag :type="scope.row.configType === 1 ? 'primary' : 'success'">
            {{ scope.row.configType === 1 ? '用户级' : '部门级' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="考勤规则" align="center" prop="rule.ruleName" min-width="120" />
      <el-table-column label="关联用户" align="center" min-width="150">
        <template #default="scope">
          <template v-if="scope.row.configType === 1">
            <span v-if="scope.row.userIds">{{ getUserNames(scope.row.userIds) }}</span>
            <span v-else>-</span>
          </template>
          <span v-else>-</span>
        </template>
      </el-table-column>
      <el-table-column label="关联部门" align="center" min-width="100">
        <template #default="scope">
          {{ scope.row.configType === 2 ? (scope.row.dept?.deptName || '-') : '-' }}
        </template>
      </el-table-column>
      <el-table-column label="状态" align="center" prop="status" min-width="80">
        <template #default="scope">
          <dict-tag :options="sys_normal_disable" :value="scope.row.status" />
        </template>
      </el-table-column>
      <el-table-column label="创建时间" align="center" prop="createTime" min-width="160" />
      <el-table-column label="操作" align="center" min-width="160">
        <template #default="scope">
          <el-button link type="primary" icon="Edit" @click="handleUpdate(scope.row)">修改</el-button>
          <el-button link type="primary" icon="Delete" @click="handleDelete(scope.row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" v-model:page="queryParams.pageNum" v-model:limit="queryParams.pageSize" @pagination="getList" />

    <el-dialog :title="title" v-model="open" width="500px" append-to-body>
      <el-form ref="configRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="配置名称" prop="configName">
          <el-input v-model="form.configName" placeholder="请输入配置名称" />
        </el-form-item>
        <el-form-item label="配置类型" prop="configType">
          <el-radio-group v-model="form.configType" @change="handleConfigTypeChange">
            <el-radio :value="1">用户级</el-radio>
            <el-radio :value="2">部门级</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="考勤规则" prop="ruleId">
          <el-select v-model="form.ruleId" placeholder="请选择考勤规则" style="width: 100%">
            <el-option v-for="rule in ruleOptions" :key="rule.ruleId" :label="rule.ruleName" :value="rule.ruleId" />
          </el-select>
        </el-form-item>
        <el-form-item v-if="form.configType === 1" label="关联用户" prop="userIds">
          <el-select v-model="form.userIds" placeholder="请选择用户" filterable multiple collapse-tags collapse-tags-tooltip style="width: 100%">
            <el-option v-for="user in userOptions" :key="user.userId" :label="user.nickName || user.userName" :value="user.userId" />
          </el-select>
        </el-form-item>
        <el-form-item v-if="form.configType === 2" label="关联部门" prop="deptId">
          <el-tree-select v-model="form.deptId" :data="deptOptions" :props="{ value: 'id', label: 'label', children: 'children' }" value-key="id" placeholder="请选择部门" check-strictly style="width: 100%" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="form.status">
            <el-radio v-for="dict in sys_normal_disable" :key="dict.value" :value="dict.value">{{ dict.label }}</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="备注" prop="remark">
          <el-input v-model="form.remark" type="textarea" placeholder="请输入备注" />
        </el-form-item>
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

<script setup name="AttendanceConfig">
import { listAttendanceConfig, getAttendanceConfig, addAttendanceConfig, updateAttendanceConfig, delAttendanceConfig, listAttendanceRule } from "@/api/business/attendance"
import { listUser } from "@/api/system/user"
import { treeselect } from "@/api/system/dept"

const { proxy } = getCurrentInstance()
const { sys_normal_disable } = useDict("sys_normal_disable")

const configList = ref([])
const ruleOptions = ref([])
const userOptions = ref([])
const deptOptions = ref([])
const open = ref(false)
const loading = ref(true)
const showSearch = ref(true)
const ids = ref([])
const single = ref(true)
const multiple = ref(true)
const total = ref(0)
const title = ref("")

const data = reactive({
  form: {},
  queryParams: { pageNum: 1, pageSize: 10, configName: undefined, configType: undefined, status: undefined },
  rules: {
    configName: [{ required: true, message: "配置名称不能为空", trigger: "blur" }],
    configType: [{ required: true, message: "请选择配置类型", trigger: "change" }],
    ruleId: [{ required: true, message: "请选择考勤规则", trigger: "change" }]
  }
})

const { queryParams, form, rules } = toRefs(data)

function getList() {
  loading.value = true
  listAttendanceConfig(queryParams.value).then(response => {
    configList.value = response.rows
    total.value = response.total
    loading.value = false
  })
}

function getRuleList() {
  listAttendanceRule({ pageSize: 100 }).then(response => { ruleOptions.value = response.rows || [] })
}

function getUserList() {
  listUser({ pageSize: 1000 }).then(response => { userOptions.value = response.rows || [] })
}

function getDeptTree() {
  treeselect().then(response => { deptOptions.value = response.data || [] })
}

function handleQuery() { queryParams.value.pageNum = 1; getList() }
function resetQuery() { proxy.resetForm("queryRef"); handleQuery() }

function handleSelectionChange(selection) {
  ids.value = selection.map(item => item.configId)
  single.value = selection.length !== 1
  multiple.value = !selection.length
}

function reset() {
  form.value = { configId: undefined, configName: undefined, ruleId: undefined, configType: 1, userIds: [], deptId: undefined, status: "0", remark: undefined }
  proxy.resetForm("configRef")
}

function handleConfigTypeChange() { form.value.userIds = []; form.value.deptId = undefined }

function getUserNames(userIds) {
  if (!userIds) return '-'
  const ids = userIds.split(',').map(id => parseInt(id))
  const names = ids.map(id => {
    const user = userOptions.value.find(u => u.userId === id)
    return user ? (user.nickName || user.userName) : id
  })
  return names.join(', ')
}

function handleAdd() { reset(); open.value = true; title.value = "新增考勤配置" }

function handleUpdate(row) {
  reset()
  const configId = row.configId || ids.value[0]
  getAttendanceConfig(configId).then(response => {
    form.value = response.data
    if (form.value.userIds && typeof form.value.userIds === 'string') {
      form.value.userIds = form.value.userIds.split(',').map(id => parseInt(id))
    }
    open.value = true
    title.value = "修改考勤配置"
  })
}

function submitForm() {
  proxy.$refs["configRef"].validate(valid => {
    if (valid) {
      if (form.value.configId) {
        updateAttendanceConfig(form.value).then(() => { proxy.$modal.msgSuccess("修改成功"); open.value = false; getList() })
      } else {
        addAttendanceConfig(form.value).then(() => { proxy.$modal.msgSuccess("新增成功"); open.value = false; getList() })
      }
    }
  })
}

function handleDelete(row) {
  const configIds = row.configId || ids.value
  proxy.$modal.confirm('是否确认删除所选配置？').then(() => delAttendanceConfig(configIds)).then(() => { getList(); proxy.$modal.msgSuccess("删除成功") }).catch(() => {})
}

function cancel() { open.value = false; reset() }

getList()
getRuleList()
getUserList()
getDeptTree()
</script>
