<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch" label-width="68px">
      <el-form-item label="员工姓名" prop="userName">
        <el-input
          v-model="queryParams.userName"
          placeholder="请输入员工姓名"
          clearable
          style="width: 160px"
          @keyup.enter="handleQuery"
        />
      </el-form-item>
      <el-form-item label="考勤日期" prop="dateRange">
        <el-date-picker
          v-model="dateRange"
          type="daterange"
          range-separator="-"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          value-format="YYYY-MM-DD"
          style="width: 240px"
        />
      </el-form-item>
      <el-form-item label="考勤状态" prop="attendanceStatus">
        <el-select v-model="queryParams.attendanceStatus" placeholder="考勤状态" clearable style="width: 160px">
          <el-option
            v-for="dict in biz_attendance_status"
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
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList"></right-toolbar>
    </el-row>

    <el-table v-loading="loading" :data="recordList" style="width: 100%">
      <el-table-column label="员工姓名" align="center" prop="userName" min-width="100" show-overflow-tooltip />
      <el-table-column label="考勤日期" align="center" prop="attendanceDate" min-width="120" />
      <el-table-column label="上班时间" align="center" prop="clockInTime" min-width="160">
        <template #default="scope">
          {{ scope.row.clockInTime || '--' }}
        </template>
      </el-table-column>
      <el-table-column label="下班时间" align="center" prop="clockOutTime" min-width="160">
        <template #default="scope">
          {{ scope.row.clockOutTime || '--' }}
        </template>
      </el-table-column>
      <el-table-column label="考勤状态" align="center" prop="attendanceStatus" min-width="100">
        <template #default="scope">
          <dict-tag :options="biz_attendance_status" :value="scope.row.attendanceStatus" />
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center" class-name="small-padding fixed-width" min-width="80">
        <template #default="scope">
          <el-button link type="primary" icon="View" @click="handleDetail(scope.row)" v-hasPermi="['business:attendance:record:detail']">详情</el-button>
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

    <el-dialog :title="detailTitle" v-model="detailOpen" width="700px" append-to-body>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="员工姓名">{{ detailData.userName }}</el-descriptions-item>
        <el-descriptions-item label="考勤日期">{{ detailData.attendanceDate }}</el-descriptions-item>
        <el-descriptions-item label="考勤状态">
          <dict-tag :options="biz_attendance_status" :value="detailData.attendanceStatus" />
        </el-descriptions-item>
        <el-descriptions-item label="备注">{{ detailData.remark || '--' }}</el-descriptions-item>
        <el-descriptions-item label="外勤事由" :span="2" v-if="detailData.clockType === '1'">{{ detailData.outsideReason || '--' }}</el-descriptions-item>
      </el-descriptions>

      <div style="margin-top: 20px;">
        <el-divider content-position="left">打卡明细</el-divider>

        <el-timeline v-if="detailClockList.length > 0">
          <el-timeline-item
            v-for="(clock, index) in detailClockList"
            :key="index"
            :timestamp="clock.clockTime"
            placement="top"
            :type="getTimelineType(index)"
            :hollow="false"
          >
            <el-card shadow="hover" class="clock-card">
              <div class="clock-header">
                <dict-tag :options="biz_clock_type" :value="clock.clockType" size="large" />
                <span class="clock-index">第 {{ index + 1 }} 次打卡</span>
              </div>

              <div class="clock-info">
                <p><el-icon><Location /></el-icon> {{ clock.address || '未知位置' }}</p>
                <p><el-icon><Position /></el-icon>
                  {{ clock.latitude && clock.longitude ? `${clock.latitude}, ${clock.longitude}` : '无坐标信息' }}
                </p>
              </div>

              <div class="clock-photo" v-if="clock.photo">
                <image-preview :src="clock.photo" :width="100" :height="100" />
              </div>
            </el-card>
          </el-timeline-item>
        </el-timeline>

        <el-empty description="暂无打卡明细" v-else :image-size="60" />
      </div>
    </el-dialog>
  </div>
</template>

<script setup name="AttendanceRecord">
import { listAttendanceRecord, getAttendanceRecord, getClockListByRecordId } from "@/api/business/attendance"

const { proxy } = getCurrentInstance()
const { biz_attendance_status, biz_clock_type } = useDict("biz_attendance_status", "biz_clock_type")

const recordList = ref([])
const loading = ref(true)
const showSearch = ref(true)
const total = ref(0)
const dateRange = ref([])
const detailOpen = ref(false)
const detailTitle = ref('')
const detailData = ref({})
const detailClockList = ref([])

const data = reactive({
  queryParams: {
    pageNum: 1,
    pageSize: 10,
    userName: undefined,
    attendanceStatus: undefined,
    startDate: undefined,
    endDate: undefined
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
  listAttendanceRecord(params).then(response => {
    recordList.value = response.rows
    total.value = response.total
    loading.value = false
  })
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

function handleDetail(row) {
  detailData.value = row
  detailTitle.value = '考勤记录详情'
  detailOpen.value = true

  if (row.recordId) {
    getClockListByRecordId(row.recordId).then(response => {
      detailClockList.value = response.data || []
    }).catch(() => {
      detailClockList.value = []
    })
  } else {
    detailClockList.value = []
  }
}

function getTimelineType(index) {
  if (index === 0) return 'primary'
  if (index === 1) return 'success'
  return 'warning'
}

getList()
</script>

<style scoped>
.photo-label {
  font-size: 14px;
  color: #606266;
  margin-bottom: 8px;
}

.clock-card {
  margin-bottom: 10px;
}

.clock-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.clock-index {
  font-size: 13px;
  color: #909399;
}

.clock-info {
  font-size: 14px;
  line-height: 24px;
  color: #606266;
}

.clock-info p {
  margin: 4px 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.clock-photo {
  margin-top: 12px;
  text-align: center;
}
</style>
