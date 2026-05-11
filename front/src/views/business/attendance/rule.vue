<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch" label-width="68px">
      <el-form-item label="规则名称" prop="ruleName">
        <el-input
          v-model="queryParams.ruleName"
          placeholder="请输入规则名称"
          clearable
          style="width: 160px"
          @keyup.enter="handleQuery"
        />
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-select v-model="queryParams.status" placeholder="规则状态" clearable style="width: 160px">
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
        <el-button type="primary" plain icon="Plus" @click="handleAdd" v-hasPermi="['business:attendance:rule:add']">新增</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="success" plain icon="Edit" :disabled="single" @click="handleUpdate" v-hasPermi="['business:attendance:rule:edit']">修改</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="danger" plain icon="Delete" :disabled="multiple" @click="handleDelete" v-hasPermi="['business:attendance:rule:remove']">删除</el-button>
      </el-col>
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList"></right-toolbar>
    </el-row>

    <el-table v-loading="loading" :data="ruleList" @selection-change="handleSelectionChange">
      <el-table-column type="selection" width="55" align="center" />
      <el-table-column label="规则名称" align="center" prop="ruleName" />
      <el-table-column label="上班时间" align="center" prop="workStartTime" width="100" />
      <el-table-column label="下班时间" align="center" prop="workEndTime" width="100" />
      <el-table-column label="迟到容忍(分)" align="center" prop="lateThreshold" width="120" />
      <el-table-column label="早退容忍(分)" align="center" prop="earlyLeaveThreshold" width="120" />
      <el-table-column label="允许距离(米)" align="center" prop="allowedDistance" width="120" />
      <el-table-column label="状态" align="center" prop="status" width="80">
        <template #default="scope">
          <dict-tag :options="sys_normal_disable" :value="scope.row.status" />
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center" class-name="small-padding fixed-width" width="160">
        <template #default="scope">
          <el-button link type="primary" icon="Edit" @click="handleUpdate(scope.row)" v-hasPermi="['business:attendance:rule:edit']">修改</el-button>
          <el-button link type="primary" icon="Delete" @click="handleDelete(scope.row)" v-hasPermi="['business:attendance:rule:remove']">删除</el-button>
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

    <el-dialog :title="title" v-model="open" width="500px" append-to-body>
      <el-form ref="ruleRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="规则名称" prop="ruleName">
          <el-input v-model="form.ruleName" placeholder="请输入规则名称" />
        </el-form-item>
        <el-form-item label="上班时间" prop="workStartTime">
          <el-time-picker v-model="form.workStartTime" format="HH:mm" value-format="HH:mm:ss" placeholder="选择上班时间" style="width: 100%" />
        </el-form-item>
        <el-form-item label="下班时间" prop="workEndTime">
          <el-time-picker v-model="form.workEndTime" format="HH:mm" value-format="HH:mm:ss" placeholder="选择下班时间" style="width: 100%" />
        </el-form-item>
        <el-form-item label="迟到容忍" prop="lateThreshold">
          <el-input-number v-model="form.lateThreshold" :min="0" :max="60" placeholder="分钟" style="width: 100%" />
        </el-form-item>
        <el-form-item label="早退容忍" prop="earlyLeaveThreshold">
          <el-input-number v-model="form.earlyLeaveThreshold" :min="0" :max="60" placeholder="分钟" style="width: 100%" />
        </el-form-item>
        <el-form-item label="考勤地点" prop="workLatitude">
          <div style="display: flex; gap: 8px; width: 100%;">
            <el-input v-model="form.workLatitude" placeholder="纬度" style="flex: 1" readonly />
            <el-input v-model="form.workLongitude" placeholder="经度" style="flex: 1" readonly />
            <el-button type="primary" icon="Location" @click="openMapPicker">选点</el-button>
          </div>
          <div v-if="form.workAddress" class="location-address">{{ form.workAddress }}</div>
        </el-form-item>
        <el-form-item label="允许距离(米)" prop="allowedDistance">
          <el-input-number v-model="form.allowedDistance" :min="0" :max="5000" :step="100" placeholder="允许打卡距离" style="width: 100%" />
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

    <MapPicker ref="mapPickerRef" @confirm="handleMapConfirm" />
  </div>
</template>

<script setup name="AttendanceRule">
import { listAttendanceRule, getAttendanceRule, addAttendanceRule, updateAttendanceRule, delAttendanceRule } from "@/api/business/attendance"
import MapPicker from "@/components/MapPicker/index.vue"

const { proxy } = getCurrentInstance()
const { sys_normal_disable } = useDict("sys_normal_disable")

const ruleList = ref([])
const open = ref(false)
const loading = ref(true)
const showSearch = ref(true)
const ids = ref([])
const single = ref(true)
const multiple = ref(true)
const total = ref(0)
const title = ref("")
const mapPickerRef = ref(null)

const data = reactive({
  form: {},
  queryParams: {
    pageNum: 1,
    pageSize: 10,
    ruleName: undefined,
    status: undefined
  },
  rules: {
    ruleName: [{ required: true, message: "规则名称不能为空", trigger: "blur" }],
    workStartTime: [{ required: true, message: "上班时间不能为空", trigger: "change" }],
    workEndTime: [{ required: true, message: "下班时间不能为空", trigger: "change" }]
  }
})

const { queryParams, form, rules } = toRefs(data)

function getList() {
  loading.value = true
  listAttendanceRule(queryParams.value).then(response => {
    ruleList.value = response.rows
    total.value = response.total
    loading.value = false
  })
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
  ids.value = selection.map(item => item.ruleId)
  single.value = selection.length !== 1
  multiple.value = !selection.length
}

function reset() {
  form.value = {
    ruleId: undefined,
    ruleName: undefined,
    workStartTime: undefined,
    workEndTime: undefined,
    lateThreshold: 0,
    earlyLeaveThreshold: 0,
    workLatitude: undefined,
    workLongitude: undefined,
    workAddress: undefined,
    allowedDistance: 500,
    status: "0",
    remark: undefined
  }
  proxy.resetForm("ruleRef")
}

function openMapPicker() {
  mapPickerRef.value?.open()
}

function handleMapConfirm(data) {
  form.value.workLatitude = data.latitude
  form.value.workLongitude = data.longitude
  form.value.workAddress = data.address
}

function handleAdd() {
  reset()
  open.value = true
  title.value = "新增考勤规则"
}

function handleUpdate(row) {
  reset()
  const ruleId = row.ruleId || ids.value[0]
  getAttendanceRule(ruleId).then(response => {
    form.value = response.data
    open.value = true
    title.value = "修改考勤规则"
  })
}

function submitForm() {
  proxy.$refs["ruleRef"].validate(valid => {
    if (valid) {
      if (form.value.ruleId) {
        updateAttendanceRule(form.value).then(response => {
          proxy.$modal.msgSuccess("修改成功")
          open.value = false
          getList()
        })
      } else {
        addAttendanceRule(form.value).then(response => {
          proxy.$modal.msgSuccess("新增成功")
          open.value = false
          getList()
        })
      }
    }
  })
}

function handleDelete(row) {
  const ruleIds = row.ruleId || ids.value
  proxy.$modal.confirm('是否确认删除考勤规则编号为"' + ruleIds + '"的数据项？').then(() => {
    return delAttendanceRule(ruleIds)
  }).then(() => {
    getList()
    proxy.$modal.msgSuccess("删除成功")
  }).catch(() => {})
}

function cancel() {
  open.value = false
  reset()
}

getList()
</script>

<style scoped>
.location-address {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
  line-height: 1.4;
}
</style>
