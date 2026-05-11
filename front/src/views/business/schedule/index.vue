<template>
  <div class="app-container">
    <el-tabs v-model="activeTab" @tab-change="handleTabChange">
      <el-tab-pane label="员工行程" name="employee">
        <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch" label-width="68px">
          <el-form-item label="年月" prop="yearMonth">
            <el-date-picker v-model="queryParams.yearMonth" type="month" placeholder="选择年月" value-format="YYYY-MM" style="width: 160px" @change="handleQuery" />
          </el-form-item>
          <el-form-item label="员工姓名" prop="userName">
            <el-input v-model="queryParams.userName" placeholder="请输入员工姓名" clearable style="width: 160px" @keyup.enter="handleQuery" />
          </el-form-item>
          <el-form-item label="企业名称" prop="enterpriseName">
            <el-input v-model="queryParams.enterpriseName" placeholder="请输入企业名称" clearable style="width: 160px" @keyup.enter="handleQuery" />
          </el-form-item>
          <el-form-item label="下店目的" prop="purpose">
            <el-select v-model="queryParams.purpose" placeholder="请选择下店目的" clearable style="width: 140px">
              <el-option v-for="dict in biz_schedule_purpose" :key="dict.value" :label="dict.label" :value="dict.value" />
            </el-select>
          </el-form-item>
          <el-form-item label="状态" prop="status">
            <el-select v-model="queryParams.status" placeholder="行程状态" clearable style="width: 120px">
              <el-option v-for="dict in biz_schedule_status" :key="dict.value" :label="dict.label" :value="dict.value" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
            <el-button icon="Refresh" @click="resetQuery">重置</el-button>
          </el-form-item>
        </el-form>

        <el-row :gutter="10" class="mb8">
          <el-col :span="1.5">
            <el-button type="primary" plain icon="Plus" @click="handleAdd" v-hasPermi="['business:schedule:add']">新增</el-button>
          </el-col>
          <right-toolbar v-model:showSearch="showSearch" @queryTable="getList"></right-toolbar>
        </el-row>

        <!-- 日历网格 - Div布局 -->
        <div class="calendar-grid" ref="calendarGridRef">
          <!-- 表头 -->
          <div class="header-row">
            <div class="name-header">员工姓名</div>
            <div class="days-header">
              <div v-for="day in daysInMonth" :key="day" class="day-header">{{ day }}日</div>
            </div>
          </div>

          <!-- 数据行 -->
          <div v-for="(row, rowIndex) in scheduleData" :key="rowIndex" class="data-row">
            <div class="name-cell">
              <div class="name-text">{{ row.userName }}</div>
              <el-tag v-if="row.postName" size="small" type="info" class="post-tag">{{ row.postName }}</el-tag>
            </div>
            <div
              class="days-container"
              @mousedown.prevent="handleRowMouseDown($event, row, rowIndex)"
              @mouseup.prevent="handleRowMouseUp"
              @mouseleave="handleMouseLeave"
            >
              <div
                v-for="day in daysInMonth"
                :key="'d-' + rowIndex + '-' + day"
                :class="['day-cell', { selected: isSelectedDay(rowIndex, day), 'rest-day': isRestDayForUser(row.userId, day) }]"
                :data-day="day"
                @mouseenter="handleCellEnter(rowIndex, day)"
                @click.stop="handleCellClick(row, day)"
              />
              <template v-for="(merged, mIdx) in getMergedSchedules(row)" :key="'m-' + rowIndex + '-' + merged.startDay">
                <div
                  class="schedule-block"
                  :class="'purpose-' + merged.purpose + ' status-' + merged.status"
                  :style="getScheduleBlockStyle(merged)"
                  @click.stop="handleScheduleClick(merged)"
                >
                  <el-tooltip :content="getScheduleTooltip(merged)" placement="top" effect="dark" :show-after="200">
                    <span class="block-text">{{ merged.enterpriseName }}</span>
                  </el-tooltip>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- 排班列表 -->
        <div class="schedule-list-section mt-4">
          <h4>排班列表</h4>
          <el-table :data="processedScheduleList" border size="small" max-height="280" table-layout="fixed">
            <el-table-column label="员工" prop="userName" min-width="100" />
            <el-table-column label="职位" prop="postName" min-width="100" />
            <el-table-column label="企业" prop="enterpriseName" min-width="130" show-overflow-tooltip />
            <el-table-column label="下店目的" width="90">
              <template #default="scope"><dict-tag :options="biz_schedule_purpose" :value="scope.row.purpose" /></template>
            </el-table-column>
            <el-table-column label="状态" width="85">
              <template #default="scope"><dict-tag :options="biz_schedule_status" :value="scope.row.status" /></template>
            </el-table-column>
            <el-table-column label="排班日期" min-width="180">
              <template #default="scope">
                <el-tag v-for="(date, idx) in scope.row.dates" :key="idx" size="small" class="date-tag mx-1">{{ date.slice(5) }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="140" fixed="right">
              <template #default="scope">
                <el-button link type="primary" size="small" @click="handleGroupEdit(scope.row)" v-hasPermi="['business:schedule:edit']">编辑</el-button>
                <el-button link type="danger" size="small" @click="handleGroupDelete(scope.row)" v-hasPermi="['business:schedule:remove']">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </el-tab-pane>

      <el-tab-pane label="企业排班" name="enterprise">
        <el-form :model="queryParams" ref="queryRef2" :inline="true" v-show="showSearch" label-width="68px">
          <el-form-item label="年月" prop="yearMonth">
            <el-date-picker v-model="queryParams.yearMonth" type="month" placeholder="选择年月" value-format="YYYY-MM" style="width: 160px" @change="handleQuery" />
          </el-form-item>
          <el-form-item label="企业名称" prop="enterpriseName">
            <el-input v-model="queryParams.enterpriseName" placeholder="请输入企业名称" clearable style="width: 160px" @keyup.enter="handleQuery" />
          </el-form-item>
          <el-form-item label="员工姓名" prop="userName">
            <el-input v-model="queryParams.userName" placeholder="请输入员工姓名" clearable style="width: 160px" @keyup.enter="handleQuery" />
          </el-form-item>
          <el-form-item label="下店目的" prop="purpose">
            <el-select v-model="queryParams.purpose" placeholder="请选择下店目的" clearable style="width: 140px">
              <el-option v-for="dict in biz_schedule_purpose" :key="dict.value" :label="dict.label" :value="dict.value" />
            </el-select>
          </el-form-item>
          <el-form-item label="状态" prop="status">
            <el-select v-model="queryParams.status" placeholder="行程状态" clearable style="width: 120px">
              <el-option v-for="dict in biz_schedule_status" :key="dict.value" :label="dict.label" :value="dict.value" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
            <el-button icon="Refresh" @click="resetQuery">重置</el-button>
          </el-form-item>
        </el-form>

        <el-row :gutter="10" class="mb8">
          <el-col :span="1.5">
            <el-button type="primary" plain icon="Plus" @click="handleAdd" v-hasPermi="['business:schedule:add']">新增</el-button>
          </el-col>
          <right-toolbar v-model:showSearch="showSearch" @queryTable="getList"></right-toolbar>
        </el-row>

        <!-- 日历网格 - 企业排班 -->
        <div class="calendar-grid" ref="calendarGridRef2">
          <div class="header-row">
            <div class="name-header">企业名称</div>
            <div class="days-header">
              <div v-for="day in daysInMonth" :key="day" class="day-header">{{ day }}日</div>
            </div>
          </div>
          <div v-for="(row, rowIndex) in scheduleData" :key="rowIndex" class="data-row">
            <div class="name-cell">
              <div class="name-text">{{ row.enterpriseName }}</div>
            </div>
            <div
              class="days-container"
              @mousedown.prevent="handleRowMouseDown($event, row, rowIndex)"
              @mouseup.prevent="handleRowMouseUp"
              @mouseleave="handleMouseLeave"
            >
              <div
                v-for="day in daysInMonth"
                :key="'d-' + rowIndex + '-' + day"
                :class="['day-cell', { selected: isSelectedDay(rowIndex, day) }]"
                :data-day="day"
                @mouseenter="handleCellEnter(rowIndex, day)"
                @click.stop="handleCellClick(row, day)"
              />
              <template v-for="(merged, mIdx) in getMergedSchedules(row)" :key="'m-' + rowIndex + '-' + merged.startDay">
                <div
                  class="schedule-block"
                  :class="'purpose-' + merged.purpose + ' status-' + merged.status"
                  :style="getScheduleBlockStyle(merged)"
                  @click.stop="handleScheduleClick(merged)"
                >
                  <el-tooltip :content="getScheduleTooltip(merged)" placement="top" effect="dark" :show-after="200">
                    <span class="block-text">{{ merged.userName }}</span>
                  </el-tooltip>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- 排班列表 -->
        <div class="schedule-list-section mt-4">
          <h4>排班列表</h4>
          <el-table :data="processedScheduleList" border size="small" max-height="280" table-layout="fixed">
            <el-table-column label="企业" prop="enterpriseName" min-width="130" show-overflow-tooltip />
            <el-table-column label="员工" prop="userName" min-width="100" />
            <el-table-column label="职位" prop="postName" min-width="100" />
            <el-table-column label="下店目的" width="90">
              <template #default="scope"><dict-tag :options="biz_schedule_purpose" :value="scope.row.purpose" /></template>
            </el-table-column>
            <el-table-column label="状态" width="85">
              <template #default="scope"><dict-tag :options="biz_schedule_status" :value="scope.row.status" /></template>
            </el-table-column>
            <el-table-column label="排班日期" min-width="180">
              <template #default="scope">
                <el-tag v-for="(date, idx) in scope.row.dates" :key="idx" size="small" class="date-tag mx-1">{{ date.slice(5) }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="140" fixed="right">
              <template #default="scope">
                <el-button link type="primary" size="small" @click="handleGroupEdit(scope.row)" v-hasPermi="['business:schedule:edit']">编辑</el-button>
                <el-button link type="danger" size="small" @click="handleGroupDelete(scope.row)" v-hasPermi="['business:schedule:remove']">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </el-tab-pane>

      <el-tab-pane label="员工配置" name="employeeConfig">
        <el-form :model="configQueryParams" ref="configQueryRef" :inline="true" v-show="showSearch" label-width="68px">
          <el-form-item label="员工姓名" prop="userName">
            <el-input v-model="configQueryParams.userName" placeholder="请输入员工姓名" clearable style="width: 160px" @keyup.enter="queryConfig" />
          </el-form-item>
          <el-form-item label="部门" prop="deptName">
            <el-input v-model="configQueryParams.deptName" placeholder="请输入部门名称" clearable style="width: 160px" @keyup.enter="queryConfig" />
          </el-form-item>
          <el-form-item label="是否可排班" prop="isSchedulable">
            <el-select v-model="configQueryParams.isSchedulable" placeholder="是否可排班" clearable style="width: 120px">
              <el-option label="是" value="1" />
              <el-option label="否" value="0" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" icon="Search" @click="queryConfig">搜索</el-button>
            <el-button icon="Refresh" @click="resetConfigQuery">重置</el-button>
          </el-form-item>
        </el-form>
        <el-table :data="employeeConfigList" border v-loading="configLoading">
          <el-table-column label="员工姓名" prop="userName" width="110" />
          <el-table-column label="职位" prop="postName" width="120" />
          <el-table-column label="部门" prop="deptName" min-width="150" show-overflow-tooltip />
          <el-table-column label="休息日" min-width="180">
            <template #default="scope">
              <el-tag v-for="(date, idx) in (scope.row.restDates || [])" :key="idx" size="small" type="danger" class="mx-1">{{ date.slice(5) }}</el-tag>
              <span v-if="!scope.row.restDates?.length" style="color: #c0c4cc">未配置</span>
            </template>
          </el-table-column>
          <el-table-column label="是否可排班" width="110" align="center">
            <template #default="scope">
              <el-switch v-model="scope.row.isSchedulable" active-value="1" inactive-value="0" @change="handleSchedulableChange(scope.row)" />
            </template>
          </el-table-column>
          <el-table-column label="操作" width="130" align="center" fixed="right">
            <template #default="scope">
              <el-button link type="primary" @click="handleRestDateConfig(scope.row)">配置休息日期</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
    </el-tabs>

    <!-- 添加/修改行程对话框 -->
    <el-dialog :title="title" v-model="open" width="680px" append-to-body destroy-on-close>
      <el-form ref="scheduleRef" :model="form" :rules="rules" label-width="80px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="员工" prop="userIds" v-if="activeTab === 'employee'">
              <el-select v-model="form.userIds" multiple filterable collapse-tags collapse-tags-tooltip placeholder="请选择员工" style="width: 100%">
                <el-option v-for="user in userList" :key="user.userId" :label="user.realName || user.userName" :value="user.userId" />
              </el-select>
            </el-form-item>
            <el-form-item label="企业" prop="enterpriseId" v-else>
              <el-select v-model="form.enterpriseId" filterable remote :remote-method="searchEnterprise" :loading="searchLoading" placeholder="请搜索企业" style="width: 100%" @change="handleEnterpriseChange">
                <el-option v-for="ent in filteredEnterpriseList" :key="ent.enterpriseId" :label="ent.enterpriseName" :value="ent.enterpriseId" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="企业" prop="enterpriseId" v-if="activeTab === 'employee'">
              <el-select v-model="form.enterpriseId" filterable remote :remote-method="searchEnterprise" :loading="searchLoading" placeholder="请搜索企业" style="width: 100%" @change="handleEnterpriseChange">
                <el-option v-for="ent in filteredEnterpriseList" :key="ent.enterpriseId" :label="ent.enterpriseName" :value="ent.enterpriseId" />
              </el-select>
            </el-form-item>
            <el-form-item label="员工" prop="userIds" v-else>
              <el-select v-model="form.userIds" multiple filterable collapse-tags collapse-tags-tooltip placeholder="请选择员工" style="width: 100%">
                <el-option v-for="user in userList" :key="user.userId" :label="user.realName || user.userName" :value="user.userId" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="开始日期" prop="startDate">
              <el-date-picker v-model="form.startDate" type="date" placeholder="开始日期" value-format="YYYY-MM-DD" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="结束日期" prop="endDate">
              <el-date-picker v-model="form.endDate" type="date" placeholder="结束日期" value-format="YYYY-MM-DD" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item label="下店目的" prop="purpose">
              <el-radio-group v-model="form.purpose">
                <el-radio-button v-for="dict in biz_schedule_purpose" :key="dict.value" :value="dict.value">{{ dict.label }}</el-radio-button>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item label="状态" prop="status">
              <el-radio-group v-model="form.status">
                <el-radio-button v-for="dict in biz_schedule_status" :key="dict.value" :value="dict.value">{{ dict.label }}</el-radio-button>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item label="备注" prop="remark">
              <el-input v-model="form.remark" type="textarea" :rows="2" placeholder="请输入备注" />
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

    <!-- 行程详情对话框 -->
    <el-dialog title="行程详情" v-model="detailOpen" width="500px" append-to-body>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="员工姓名">{{ currentSchedule.userName }}</el-descriptions-item>
        <el-descriptions-item label="企业名称">{{ currentSchedule.enterpriseName }}</el-descriptions-item>
        <el-descriptions-item label="行程日期">{{ currentSchedule.scheduleDate }}</el-descriptions-item>
        <el-descriptions-item label="下店目的"><dict-tag :options="biz_schedule_purpose" :value="currentSchedule.purpose" /></el-descriptions-item>
        <el-descriptions-item label="状态"><dict-tag :options="biz_schedule_status" :value="currentSchedule.status" /></el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ parseTime(currentSchedule.createTime) }}</el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ currentSchedule.remark || '无' }}</el-descriptions-item>
      </el-descriptions>
      <template #footer>
        <div class="dialog-footer">
          <el-button type="primary" @click="handleEdit" v-hasPermi="['business:schedule:edit']">编 辑</el-button>
          <el-button type="danger" @click="handleDelete" v-hasPermi="['business:schedule:remove']">删 除</el-button>
          <el-button @click="detailOpen = false">关 闭</el-button>
        </div>
      </template>
    </el-dialog>

    <!-- 配置休息日期对话框 -->
    <el-dialog title="配置休息日期" v-model="restDateOpen" width="550px" append-to-body>
      <el-alert title="点击日期可选择/取消休息日，支持多选。休息日在日历中将以标记显示" type="info" :closable="false" show-icon style="margin-bottom: 16px" />
      <el-calendar v-model="restDateValue">
        <template #date-cell="{ data }">
          <div class="rest-date-cell" @click="toggleRestDate(data.day)">
            <span class="date-num">{{ data.day.split('-')[2] }}</span>
            <span v-if="isRestDate(data.day)" class="rest-dot"></span>
          </div>
        </template>
      </el-calendar>
      <template #footer>
        <div class="dialog-footer">
          <el-button type="primary" @click="saveRestDatesAction">保存</el-button>
          <el-button @click="restDateOpen = false">取消</el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup name="Schedule">
import { listSchedule, getSchedule, getEmployeeSchedule, getEnterpriseSchedule, addSchedule, addScheduleBatch, updateSchedule, delSchedule } from "@/api/business/schedule"
import { listUser } from "@/api/system/user"
import { listEnterprise, searchEnterprise as searchEnterpriseApi } from "@/api/business/enterprise"
import { listEmployeeConfig, updateSchedulable, saveRestDates as saveRestDatesApi, getRestDates } from "@/api/business/employeeConfig"

const { proxy } = getCurrentInstance()
const { sys_normal_disable, biz_schedule_purpose, biz_schedule_status } = useDict("sys_normal_disable", "biz_schedule_purpose", "biz_schedule_status")

const activeTab = ref("employee")
const scheduleData = ref([])
const processedScheduleList = ref([])
const open = ref(false)
const detailOpen = ref(false)
const restDateOpen = ref(false)
const configLoading = ref(false)
const searchLoading = ref(false)
const showSearch = ref(true)
const title = ref("")
const daysInMonth = ref(31)
const tableHeight = ref(450)
const userList = ref([])
const enterpriseList = ref([])
const filteredEnterpriseList = ref([])
const employeeConfigList = ref([])
const scheduleListData = ref([])
const restDateMap = ref({})
const currentSchedule = ref({})
const tempRestDates = ref([])

const isDragging = ref(false)
const dragStartInfo = ref(null)
const dragEndInfo = ref(null)
const selectedDays = ref(new Set())
const restDateValue = ref(new Date())
const currentConfigRow = ref({})

const data = reactive({
  form: {},
  queryParams: {
    yearMonth: new Date().toISOString().slice(0, 7),
    userName: undefined,
    enterpriseName: undefined,
    purpose: undefined,
    status: undefined
  },
  configQueryParams: {
    pageNum: 1,
    pageSize: 50,
    userName: undefined,
    deptName: undefined,
    isSchedulable: undefined
  },
  rules: {
    userIds: [{ required: true, message: "请选择员工", trigger: "change", type: 'array' }],
    enterpriseId: [{ required: true, message: "请选择企业", trigger: "change" }],
    startDate: [{ required: true, message: "开始日期不能为空", trigger: "change" }],
    endDate: [{ required: true, message: "结束日期不能为空", trigger: "change" }],
    purpose: [{ required: true, message: "下店目的不能为空", trigger: "change" }]
  }
})

const { queryParams, form, rules, configQueryParams } = toRefs(data)

function getList() {
  const [year, month] = queryParams.value.yearMonth.split('-')
  const startDate = `${year}-${month}-01`
  const endDate = `${year}-${month}-${new Date(year, month, 0).getDate()}`
  daysInMonth.value = new Date(year, month, 0).getDate()

  const params = { startDate, endDate, userName: queryParams.value.userName, enterpriseName: queryParams.value.enterpriseName, purpose: queryParams.value.purpose, status: queryParams.value.status }

  if (activeTab.value === 'employee') {
    getEmployeeSchedule(params).then(response => {
      scheduleData.value = response.data || []
      getScheduleList(params)
    })
  } else if (activeTab.value === 'enterprise') {
    getEnterpriseSchedule(params).then(response => {
      scheduleData.value = response.data || []
      getScheduleList(params)
    })
  }
}

function getScheduleList(params) {
  listSchedule({ ...params, pageNum: 1, pageSize: 2000 }).then(response => {
    scheduleListData.value = response.rows || []
    processScheduleListGroup()
  })
}

function processScheduleListGroup() {
  if (!scheduleListData.value.length) { processedScheduleList.value = []; return }
  
  const uniqueList = []
  const dateMap = new Map()
  scheduleListData.value.forEach(item => {
    const dedupeKey = `${item.userId}_${item.enterpriseId}_${item.scheduleDate}_${item.purpose}`
    if (!dateMap.has(dedupeKey)) {
      dateMap.set(dedupeKey, item)
      uniqueList.push(item)
    }
  })
  
  const grouped = {}
  uniqueList.forEach(item => {
    const status = String(item.status || '1')
    const key = `${item.userId}_${item.enterpriseId}_${item.purpose}_${status}`
    
    if (!grouped[key]) {
      grouped[key] = {
        userId: item.userId, userName: item.userName, postName: item.postName || '',
        enterpriseId: item.enterpriseId, enterpriseName: item.enterpriseName,
        purpose: item.purpose, status: status, dates: []
      }
    }
    if (!grouped[key].dates.includes(item.scheduleDate)) {
      grouped[key].dates.push(item.scheduleDate)
    }
  })
  
  processedScheduleList.value = Object.values(grouped)
}

function getMergedSchedules(row) {
  if (!row.schedules) return []
  const schedules = row.schedules
  const merged = []
  let currentMerge = null

  for (let day = 1; day <= daysInMonth.value; day++) {
    const schedule = schedules[day]
    if (!schedule) {
      if (currentMerge) { merged.push(currentMerge); currentMerge = null }
      continue
    }

    if (!currentMerge) {
      currentMerge = { startDay: day, endDay: day, span: 1, ...schedule }
    } else if (
      schedule.purpose === currentMerge.purpose &&
      schedule.enterpriseId === currentMerge.enterpriseId &&
      schedule.status !== '4' &&
      day === currentMerge.endDay + 1
    ) {
      currentMerge.endDay = day
      currentMerge.span++
    } else {
      merged.push(currentMerge)
      currentMerge = { startDay: day, endDay: day, span: 1, ...schedule }
    }
  }
  if (currentMerge) merged.push(currentMerge)
  return merged
}

function getScheduleBlockStyle(merged) {
  const totalDays = daysInMonth.value
  const leftPercent = ((merged.startDay - 1) / totalDays) * 100
  const widthPercent = (merged.span / totalDays) * 100
  return { left: `${leftPercent}%`, width: `${widthPercent}%` }
}

function getUserList() {
  listUser({ pageNum: 1, pageSize: 1000, status: '0' }).then(response => { userList.value = response.rows || [] })
}

function getEnterpriseList() {
  listEnterprise({ pageNum: 1, pageSize: 1000, status: '0' }).then(response => {
    enterpriseList.value = response.rows || []
    filteredEnterpriseList.value = response.rows || []
  })
}

function searchEnterprise(query) {
  if (!query) { filteredEnterpriseList.value = enterpriseList.value; return }
  searchLoading.value = true
  searchEnterpriseApi(query).then(response => { filteredEnterpriseList.value = response.rows || []; searchLoading.value = false }).catch(() => { searchLoading.value = false })
}

function handleTabChange() {
  getList()
  if (activeTab.value === 'employeeConfig') queryConfig()
}

function handleQuery() { getList() }

function resetQuery() {
  proxy.resetForm("queryRef")
  queryParams.value.yearMonth = new Date().toISOString().slice(0, 7)
  handleQuery()
}

function cancel() { open.value = false; reset() }

function reset() {
  form.value = { scheduleId: undefined, userIds: [], userId: undefined, userName: undefined, enterpriseId: undefined, enterpriseName: undefined, startDate: undefined, endDate: undefined, purpose: undefined, status: "1", remark: undefined }
  proxy.resetForm("scheduleRef")
}

function handleAdd() { reset(); open.value = true; title.value = "添加行程" }

function canSelectCell(schedule) { return !schedule || schedule.status === '4' }

function handleRowMouseDown(event, row, rowIndex) {
  const target = event.target
  const cell = target.closest('.day-cell')
  if (!cell) return

  const day = parseInt(cell.getAttribute('data-day'))
  const schedule = row.schedules?.[day]
  if (!canSelectCell(schedule)) return

  isDragging.value = true
  dragStartInfo.value = { row, rowIndex, day }
  dragEndInfo.value = { row, rowIndex, day }
  selectedDays.value.clear()
  selectedDays.value.add(`${rowIndex}-${day}`)
}

function handleCellEnter(rowIndex, day) {
  if (!isDragging.value || !dragStartInfo.value) return
  if (dragStartInfo.value.rowIndex !== rowIndex) return

  const startDay = Math.min(dragStartInfo.value.day, day)
  const endDay = Math.max(dragStartInfo.value.day, day)

  selectedDays.value.clear()
  for (let d = startDay; d <= endDay; d++) {
    const sched = dragStartInfo.value.row.schedules?.[d]
    if (!canSelectCell(sched)) {
      selectedDays.value.clear()
      return
    }
    selectedDays.value.add(`${rowIndex}-${d}`)
  }

  dragEndInfo.value = { row: dragStartInfo.value.row, rowIndex, day }
}

function handleRowMouseUp() {
  if (!isDragging.value || !dragStartInfo.value || selectedDays.value.size <= 1) {
    isDragging.value = false
    dragStartInfo.value = null
    dragEndInfo.value = null
    selectedDays.value.clear()
    return
  }

  const [year, month] = queryParams.value.yearMonth.split('-')
  const startDay = Math.min(dragStartInfo.value.day, dragEndInfo.value.day)
  const endDay = Math.max(dragStartInfo.value.day, dragEndInfo.value.day)

  reset()
  form.value.startDate = `${year}-${month}-${String(startDay).padStart(2, '0')}`
  form.value.endDate = `${year}-${month}-${String(endDay).padStart(2, '0')}`

  if (activeTab.value === 'employee') {
    form.value.userIds = [dragStartInfo.value.row.userId]
    form.value.userName = dragStartInfo.value.row.userName
  } else {
    form.value.enterpriseId = dragStartInfo.value.row.enterpriseId
    form.value.enterpriseName = dragStartInfo.value.row.enterpriseName
  }

  open.value = true
  title.value = "添加行程"
  isDragging.value = false
  dragStartInfo.value = null
  dragEndInfo.value = null
  selectedDays.value.clear()
}

function handleMouseLeave() {}

function handleCellClick(row, day) {
  if (isDragging.value) return
  const schedule = row.schedules?.[day]
  if (schedule && schedule.status !== '4') handleScheduleClick(schedule)
}

function isSelectedDay(rowIndex, day) {
  return selectedDays.value.has(`${rowIndex}-${day}`)
}

function handleScheduleClick(schedule) { currentSchedule.value = schedule; detailOpen.value = true }

function handleEdit() {
  detailOpen.value = false
  reset()
  const schedule = currentSchedule.value

  const relatedSchedules = scheduleListData.value.filter(item =>
    item.userId === schedule.userId &&
    item.enterpriseId === schedule.enterpriseId &&
    item.purpose === schedule.purpose
  )

  const dates = relatedSchedules.map(item => item.scheduleDate).sort()

  form.value = {
    ...schedule,
    userIds: [schedule.userId],
    startDate: dates[0],
    endDate: dates[dates.length - 1],
    status: String(schedule.status),
    updateIds: relatedSchedules.map(item => item.scheduleId)
  }
  open.value = true
  title.value = "修改行程"
}

function handleDelete() {
  proxy.$modal.confirm('是否确认删除该行程？').then(() => delSchedule(currentSchedule.value.scheduleId)).then(() => {
    detailOpen.value = false
    getList()
    proxy.$modal.msgSuccess("删除成功")
  }).catch(() => {})
}

function handleGroupEdit(row) {
  reset()
  form.value = { userIds: [row.userId], userId: row.userId, userName: row.userName, enterpriseId: row.enterpriseId, enterpriseName: row.enterpriseName, startDate: row.dates[0], endDate: row.dates[row.dates.length - 1], purpose: row.purpose, status: row.status }
  open.value = true
  title.value = "修改行程"
}

function handleGroupDelete(row) {
  proxy.$modal.confirm(`是否确认删除 ${row.userName} 在 ${row.enterpriseName} 的所有排班？`).then(() => {
    const ids = scheduleListData.value.filter(item => item.userId === row.userId && item.enterpriseId === row.enterpriseId && item.purpose === row.purpose).map(item => item.scheduleId)
    if (ids.length) return delSchedule(ids.join(','))
  }).then(() => { getList(); proxy.$modal.msgSuccess("删除成功") }).catch(() => {})
}

function handleEnterpriseChange(enterpriseId) {
  const enterprise = [...enterpriseList.value, ...filteredEnterpriseList.value].find(e => e.enterpriseId === enterpriseId)
  if (enterprise) form.value.enterpriseName = enterprise.enterpriseName
}

function submitForm() {
  proxy.$refs["scheduleRef"].validate(valid => {
    if (valid) {
      const startDate = new Date(form.value.startDate)
      const endDate = new Date(form.value.endDate)
      const scheduleList = []
      const userIds = form.value.userIds?.length ? form.value.userIds : [form.value.userId].filter(Boolean)

      for (let d = startDate; d <= endDate; d.setDate(d.getDate() + 1)) {
        userIds.forEach(userId => {
          const user = userList.value.find(u => u.userId === userId)
          scheduleList.push({ userId: userId, userName: user?.realName || user?.userName || form.value.userName, enterpriseId: form.value.enterpriseId, enterpriseName: form.value.enterpriseName, scheduleDate: d.toISOString().slice(0, 10), purpose: form.value.purpose, status: form.value.status, remark: form.value.remark })
        })
      }

      if (form.value.updateIds?.length > 1) {
        delSchedule(form.value.updateIds.join(',')).then(() => {
          return addScheduleBatch(scheduleList)
        }).then(() => { proxy.$modal.msgSuccess("修改成功"); open.value = false; getList() })
      } else if (form.value.scheduleId != undefined) {
        const updateData = {
          scheduleId: form.value.scheduleId,
          userId: form.value.userId,
          userName: form.value.userName,
          enterpriseId: form.value.enterpriseId,
          enterpriseName: form.value.enterpriseName,
          scheduleDate: form.value.startDate,
          purpose: form.value.purpose,
          status: form.value.status,
          remark: form.value.remark
        }
        updateSchedule(updateData).then(() => { proxy.$modal.msgSuccess("修改成功"); open.value = false; getList() })
      } else {
        addScheduleBatch(scheduleList).then(() => { proxy.$modal.msgSuccess("新增成功"); open.value = false; getList() })
      }
    }
  })
}

function getScheduleTooltip(schedule) {
  const pMap = { '1': '爆卡', '2': '启动销售', '3': '售后服务', '4': '洽谈业务' }
  const sMap = { '1': '已预约', '2': '服务中', '3': '已完成', '4': '已取消' }
  return `企业：${schedule.enterpriseName}\n员工：${schedule.userName}\n目的：${pMap[schedule.purpose]}\n状态：${sMap[schedule.status]}\n备注：${schedule.remark || '无'}`
}

function queryConfig() {
  configLoading.value = true
  listEmployeeConfig(configQueryParams.value).then(response => { employeeConfigList.value = response.rows || []; configLoading.value = false }).catch(() => { configLoading.value = false })
}

function resetConfigQuery() {
  proxy.resetForm("configQueryRef")
  configQueryParams.value = { pageNum: 1, pageSize: 50, userName: undefined, deptName: undefined, isSchedulable: undefined }
  queryConfig()
}

async function handleSchedulableChange(row) {
  try {
    await updateSchedulable(row.userId, row.isSchedulable)
    proxy.$modal.msgSuccess("更新成功")
  } catch (error) { row.isSchedulable = row.isSchedulable === '1' ? '0' : '1'; proxy.$modal.msgError("更新失败") }
}

function handleRestDateConfig(row) {
  currentConfigRow.value = row
  tempRestDates.value = []
  restDateValue.value = new Date()
  restDateOpen.value = true
  getRestDates(row.userId).then(response => { tempRestDates.value = response.data || [] })
}

function isRestDate(dateStr) {
  return tempRestDates.value.includes(dateStr)
}

function toggleRestDate(dateStr) {
  const idx = tempRestDates.value.indexOf(dateStr)
  idx > -1 ? tempRestDates.value.splice(idx, 1) : tempRestDates.value.push(dateStr)
}

async function saveRestDatesAction() {
  try {
    await saveRestDatesApi(currentConfigRow.value.userId, tempRestDates.value)
    proxy.$modal.msgSuccess("保存成功")
    restDateOpen.value = false
  } catch (error) { proxy.$modal.msgError("保存失败") }
}

function isRestDayForUser(userId, day) {
  const dates = restDateMap.value[userId]
  if (!dates) return false
  const [y, m] = queryParams.value.yearMonth.split('-')
  return dates.includes(`${y}-${m}-${String(day).padStart(2, '0')}`)
}

onMounted(() => {
  getList()
  getUserList()
  getEnterpriseList()
  tableHeight.value = window.innerHeight - 320

  document.addEventListener('mouseup', () => {
    if (isDragging.value) {
      isDragging.value = false
      dragStartInfo.value = null
      dragEndInfo.value = null
      selectedDays.value.clear()
    }
  })
})
</script>

<style scoped>
.app-container { padding: 20px; }

/* 日历网格样式 */
.calendar-grid {
  border: 1px solid #e4e7ed;
  border-radius: 6px;
  overflow-x: auto;
  background: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.header-row {
  display: flex;
  background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
  border-bottom: 2px solid #e4e7ed;
  position: sticky;
  top: 0;
  z-index: 10;
}

.name-header {
  width: 120px;
  min-width: 120px;
  padding: 12px 8px;
  text-align: center;
  font-weight: 600;
  font-size: 13px;
  color: #1f2329;
  border-right: 2px solid #e4e7ed;
  background: linear-gradient(135deg, #fafafa 0%, #f0f2f5 100%);
  position: sticky;
  left: 0;
  z-index: 10;
}

.days-header {
  display: flex;
  flex: 1;
}

.day-header {
  flex: 1;
  min-width: 0;
  padding: 8px 2px;
  text-align: center;
  font-size: 12px;
  color: #606266;
  border-right: 1px solid #ebeef5;
}

.data-row {
  display: flex;
  border-bottom: 1px solid #ebeef5;
  min-height: 56px;
  transition: background-color 0.2s ease;
}

.data-row:hover {
  background-color: #fafbfc;
}

.data-row:last-child { border-bottom: none; }

.name-cell {
  width: 120px;
  min-width: 120px;
  padding: 10px 6px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-right: 2px solid #e4e7ed;
  background: linear-gradient(180deg, #fafafa 0%, #f5f5f5 100%);
  position: sticky;
  left: 0;
  z-index: 5;
}

.name-text {
  font-size: 13px;
  font-weight: 500;
  color: #303133;
  margin-bottom: 4px;
}

.post-tag {
  font-size: 10px;
  transform: scale(0.9);
}

.days-container {
  display: flex;
  flex: 1;
  position: relative;
  min-width: 0;
}

.day-cell {
  flex: 1;
  min-width: 0;
  height: 52px;
  border-right: 1px solid #ebeef5;
  cursor: crosshair;
  transition: all 0.15s ease;
}

.day-cell:hover:not(.selected):not(.rest-day) {
  background-color: #e6f7ff;
  box-shadow: inset 0 0 0 1px #91d5ff;
}

.day-cell.selected {
  background-color: #91d5ff !important;
  border: 2px solid #1890ff !important;
  box-sizing: border-box;
  z-index: 3;
  animation: pulse-selected 1.5s ease-in-out infinite;
}

@keyframes pulse-selected {
  0%, 100% { background-color: #91d5ff; }
  50% { background-color: #69c0ff; }
}

.day-cell.rest-day {
  background: repeating-linear-gradient(
    45deg,
    #f5f5f5,
    #f5f5f5 4px,
    #fafafa 4px,
    #fafafa 8px
  );
  cursor: not-allowed;
  opacity: 0.7;
}

.schedule-block {
  position: absolute;
  top: 2px;
  bottom: 2px;
  border-radius: 3px;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  padding: 0 6px;
  overflow: hidden;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}

.schedule-block:hover {
  opacity: 0.92;
  box-shadow: 0 3px 10px rgba(0,0,0,0.25);
  z-index: 4;
  transform: translateY(-1px);
}

.block-text {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 100%;
  display: block;
}

.purpose-1 { background-color: #F56C6C; }
.purpose-2 { background-color: #67C23A; }
.purpose-3 { background-color: #E6A23C; }
.purpose-4 { background-color: #409EFF; }

.status-4 {
  opacity: 0.6;
  border: 2px dashed #c0c4cc;
  background-image: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 4px,
    rgba(255,255,255,.3) 4px,
    rgba(255,255,255,.3) 8px
  );
}

/* 排班列表 */
.schedule-list-section {
  margin-top: 20px;
  padding: 20px;
  background: linear-gradient(135deg, #fafbfc 0%, #f5f7fa 100%);
  border-radius: 8px;
  border: 1px solid #e4e7ed;
  box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.schedule-list-section h4 {
  margin: 0 0 16px 0;
  font-size: 15px;
  font-weight: 600;
  color: #1f2329;
  padding-bottom: 12px;
  border-bottom: 2px solid #e4e7ed;
}
.date-tag {
  margin: 3px 4px 3px 0;
  font-weight: 500;
}

/* 休息日期 - 精致圆点标记风格 */
.rest-date-cell {
  cursor: pointer;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: all 0.2s ease;
  border-radius: 6px;
}
.rest-date-cell:hover {
  background-color: #f0f5ff;
}
.rest-date-cell .date-num {
  font-size: 13px;
  color: #303133;
  line-height: 24px;
  transition: all 0.2s ease;
}
.rest-date-cell:hover .date-num {
  color: #1890ff;
  font-weight: 500;
}

/* 选中状态 - 底部精致圆点 */
.rest-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ff6b6b 0%, #ff4d4f 100%);
  box-shadow: 0 1px 3px rgba(255, 77, 79, 0.35);
  margin-top: 2px;
  animation: dot-pop 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes dot-pop {
  0% { transform: scale(0); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}
</style>
