<template>
  <view class="record-container">
    <view class="month-selector">
      <view class="month-arrow" @click="changeMonth(-1)">
        <u-icon name="arrow-left" size="16" color="#3D6DF7" />
      </view>
      <text class="month-text">{{ currentMonth }}</text>
      <view class="month-arrow" @click="changeMonth(1)">
        <u-icon name="arrow-right" size="16" color="#3D6DF7" />
      </view>
    </view>

    <view class="stats-card">
      <view class="stat-item">
        <text class="stat-value normal">{{ stats.normal }}</text>
        <text class="stat-label">正常</text>
      </view>
      <view class="stat-item">
        <text class="stat-value late">{{ stats.late }}</text>
        <text class="stat-label">迟到</text>
      </view>
      <view class="stat-item">
        <text class="stat-value early">{{ stats.early }}</text>
        <text class="stat-label">早退</text>
      </view>
      <view class="stat-item">
        <text class="stat-value absent">{{ stats.absent }}</text>
        <text class="stat-label">缺勤</text>
      </view>
    </view>

    <view class="calendar-card">
      <view class="week-header">
        <text v-for="day in weekDays" :key="day" class="week-day">{{ day }}</text>
      </view>
      <view class="calendar-grid">
        <view
          v-for="(item, index) in calendarDays"
          :key="index"
          class="calendar-day"
          :class="{ 'day-empty': !item.day, 'day-today': item.isToday, 'day-selected': selectedDate === item.date }"
          @click="item.day && selectDay(item)"
        >
          <text v-if="item.day" class="day-number">{{ item.day }}</text>
          <view v-if="item.day && item.status !== undefined" class="day-dot" :class="'dot-' + item.statusColor"></view>
        </view>
      </view>
    </view>

    <view class="record-list">
      <view class="list-title">
        <text>打卡详情</text>
        <view v-if="selectedDate" class="show-all-btn" @click="selectedDate = null">
          <text>显示全部</text>
          <u-icon name="close" size="12" color="#86909C" />
        </view>
      </view>
      <view v-if="filteredRecordList.length === 0" class="empty-state">
        <text class="empty-text">{{ selectedDate ? '该日期暂无打卡记录' : '暂无打卡记录' }}</text>
      </view>
      <view v-for="(item, index) in filteredRecordList" :key="index" class="record-item">
        <view class="record-date">
          <text class="date-main">{{ item.attendanceDate }}</text>
          <view class="record-status-tag" :class="'tag-' + getStatusColor(item.attendanceStatus)">
            <text>{{ getStatusText(item.attendanceStatus) }}</text>
          </view>
        </view>
        
        <view v-if="clockListMap[item.attendanceDate]?.length > 0" class="clock-list">
          <view
            v-for="(clock, idx) in clockListMap[item.attendanceDate]"
            :key="idx"
            class="clock-detail-item"
          >
            <text class="clock-time">{{ formatTime(clock.clockTime) }}</text>
            <text class="clock-type-tag" :class="getClockTagClass(idx)">
              {{ getClockTagText(idx) }}
            </text>
            <text v-if="formatAddress(clock.address)" class="clock-address">
              {{ formatAddress(clock.address) }}
            </text>
          </view>
        </view>
        
        <view v-else class="record-times">
          <view class="time-item">
            <text class="time-label">上班</text>
            <text class="time-value">{{ formatTime(item.clockInTime) }}</text>
          </view>
          <view class="time-item">
            <text class="time-label">下班</text>
            <text class="time-value">{{ formatTime(item.clockOutTime) }}</text>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getRecordList, getMonthStats, getClockListByRecordId } from '@/api/attendance'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()
const weekDays = ['日', '一', '二', '三', '四', '五', '六']
const currentYear = ref(new Date().getFullYear())
const currentMonthNum = ref(new Date().getMonth() + 1)
const recordList = ref([])
const stats = ref({ normal: 0, late: 0, early: 0, late_and_early: 0, absent: 0 })
const recordMap = ref({})
const clockListMap = ref({})
const selectedDate = ref(null)

const currentMonth = computed(() => `${currentYear.value}年${currentMonthNum.value}月`)

const filteredRecordList = computed(() => {
  if (!selectedDate.value) {
    return recordList.value
  }
  return recordList.value.filter(item => item.attendanceDate === selectedDate.value)
})

const calendarDays = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonthNum.value - 1, 1).getDay()
  const daysInMonth = new Date(currentYear.value, currentMonthNum.value, 0).getDate()
  const today = new Date()
  const days = []

  for (let i = 0; i < firstDay; i++) {
    days.push({ day: null })
  }

  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${currentYear.value}-${String(currentMonthNum.value).padStart(2, '0')}-${String(d).padStart(2, '0')}`
    const record = recordMap.value[dateStr]
    const isToday = today.getFullYear() === currentYear.value && today.getMonth() + 1 === currentMonthNum.value && today.getDate() === d

    days.push({
      day: d,
      date: dateStr,
      isToday,
      status: record ? record.attendanceStatus : undefined,
      statusColor: record ? getStatusColor(record.attendanceStatus) : undefined
    })
  }

  return days
})

function getStatusText(status) {
  const map = { '0': '正常', '1': '迟到', '2': '早退', '3': '迟到+早退', '4': '缺勤' }
  return map[status] || '--'
}

function getStatusColor(status) {
  const map = { '0': 'normal', '1': 'late', '2': 'late', '3': 'absent', '4': 'absent' }
  return map[status] || ''
}

function formatTime(time) {
  if (!time) return '--:--'
  return time.substring(11, 16)
}

function getClockTagText(index) {
  if (index === 0) return '上班'
  if (index === 1) return '下班'
  return '补卡'
}

function getClockTagClass(index) {
  if (index === 0) return 'type-in'
  if (index === 1) return 'type-out'
  return 'type-supplement'
}

function isCoordinateOnly(str) {
  if (!str) return false
  return /^\s*[-+]?\d+\.\d+,\s*[-+]?\d+\.\d+\s*$/.test(str)
}

function isNumericOnly(str) {
  if (!str) return false
  return /^\d+$/.test(str.trim())
}

function formatAddress(address) {
  if (!address) return ''
  if (isCoordinateOnly(address)) return ''
  if (isNumericOnly(address)) return ''

  const addr = address.trim()

  if (addr.includes('(')) {
    const match = addr.match(/\(([^)]+)\)/)
    if (match && match[1] && match[1].length >= 2) {
      return match[1]
    }
  }

  if (addr.length <= 20) {
    return addr
  }

  const meaningfulPatterns = [
    /(?:省|市|自治区)[^区县镇路]{0,10}(?:区|县|镇|乡)/,
    /(?:区|县|镇|路|街|道|巷|弄|号)[^区县镇路]{2,15}/,
    /[^\s区县镇路街道巷弄号()（）]{2,18}(?:区|县|镇|路|街)?$/
  ]

  for (const pattern of meaningfulPatterns) {
    const match = addr.match(pattern)
    if (match && match[0] && match[0].length >= 2) {
      let result = match[0].trim()
      if (result.length > 20) {
        result = result.substring(0, 18) + '...'
      }
      return result
    }
  }

  const separators = ['区', '县', '镇', '路', '街', '道', '巷']
  for (let i = addr.length - 1; i >= Math.max(0, addr.length - 25); i--) {
    if (separators.includes(addr[i])) {
      const candidate = addr.substring(i + 1).trim()
      if (candidate.length >= 2) {
        return candidate.length > 20 ? candidate.substring(0, 18) + '...' : candidate
      }
    }
  }

  if (addr.length > 20) {
    return addr.substring(0, 18) + '...'
  }

  return addr.length >= 2 ? addr : (addr + '...')
}

function changeMonth(delta) {
  currentMonthNum.value += delta
  if (currentMonthNum.value > 12) {
    currentMonthNum.value = 1
    currentYear.value++
  } else if (currentMonthNum.value < 1) {
    currentMonthNum.value = 12
    currentYear.value--
  }
  loadData()
}

function selectDay(item) {
  if (!item.day) return
  if (selectedDate.value === item.date) {
    selectedDate.value = null
  } else {
    selectedDate.value = item.date
  }
}

async function loadData() {
  const month = `${currentYear.value}-${String(currentMonthNum.value).padStart(2, '0')}`
  const startDate = `${month}-01`
  const endDate = `${month}-${new Date(currentYear.value, currentMonthNum.value, 0).getDate()}`

  try {
    const [statsRes, listRes] = await Promise.all([
      getMonthStats({ month }),
      getRecordList({ startDate, endDate, pageSize: 50 })
    ])

    console.log('[Record] Stats API返回:', statsRes)
    console.log('[Record] statsRes.data:', statsRes.data)

    if (statsRes.data) {
      const rawData = statsRes.data
      stats.value = {
        normal: rawData.normal ?? rawData.normalCount ?? 0,
        late: rawData.late ?? rawData.lateCount ?? 0,
        early: rawData.early ?? rawData.earlyCount ?? 0,
        absent: rawData.absent ?? rawData.absentCount ?? 0
      }
      console.log('[Record] 统计数据赋值:', stats.value)
    }

    const rows = listRes.rows || []
    recordList.value = rows

    const map = {}
    const clockMap = {}
    
    for (const item of rows) {
      if (item.attendanceDate) {
        map[item.attendanceDate] = item
        
        if (item.recordId) {
          try {
            const clockRes = await getClockListByRecordId(item.recordId)
            clockMap[item.attendanceDate] = clockRes.data || []
          } catch (e) {
            console.warn('加载打卡明细失败', e)
            clockMap[item.attendanceDate] = []
          }
        }
      }
    }
    
    recordMap.value = map
    clockListMap.value = clockMap
  } catch (e) {
    console.error('加载记录失败', e)
  }
}

onMounted(() => {
  loadData()
})
</script>

<style lang="scss" scoped>
page {
  background-color: #F5F7FA;
}

.record-container {
  min-height: 100vh;
  padding: 30rpx 24rpx;
  padding-bottom: 60rpx;
}

.month-selector {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 40rpx;
  margin-bottom: 30rpx;
}

.month-arrow {
  width: 56rpx;
  height: 56rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.06);

  &:active {
    opacity: 0.7;
  }
}

.month-text {
  font-size: 32rpx;
  font-weight: 600;
  color: #1D2129;
}

.stats-card {
  display: flex;
  background: #fff;
  border-radius: 20rpx;
  padding: 28rpx 0;
  margin-bottom: 24rpx;
  box-shadow: 0 2rpx 12rpx rgba(61, 109, 247, 0.06);
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8rpx;
}

.stat-value {
  font-size: 36rpx;
  font-weight: 700;

  &.normal { color: #52c41a; }
  &.late { color: #fa8c16; }
  &.early { color: #fa8c16; }
  &.absent { color: #f5222d; }
}

.stat-label {
  font-size: 22rpx;
  color: #86909C;
}

.calendar-card {
  background: #fff;
  border-radius: 20rpx;
  padding: 24rpx;
  margin-bottom: 24rpx;
  box-shadow: 0 2rpx 12rpx rgba(61, 109, 247, 0.06);
}

.week-header {
  display: flex;
  margin-bottom: 16rpx;
}

.week-day {
  flex: 1;
  text-align: center;
  font-size: 24rpx;
  color: #86909C;
  font-weight: 500;
}

.calendar-grid {
  display: flex;
  flex-wrap: wrap;
}

.calendar-day {
  width: calc(100% / 7);
  height: 80rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4rpx;
}

.day-empty {
  width: calc(100% / 7);
}

.day-number {
  font-size: 26rpx;
  color: #1D2129;
}

.day-today .day-number {
  color: #3D6DF7;
  font-weight: 700;
}

.day-selected {
  background: rgba(61, 109, 247, 0.08);
  border-radius: 12rpx;

  .day-number {
    color: #3D6DF7;
    font-weight: 700;
  }
}

.day-dot {
  width: 10rpx;
  height: 10rpx;
  border-radius: 50%;

  &.dot-normal { background: #52c41a; }
  &.dot-late { background: #fa8c16; }
  &.dot-absent { background: #f5222d; }
}

.record-list {
  background: #fff;
  border-radius: 20rpx;
  padding: 24rpx;
  box-shadow: 0 2rpx 12rpx rgba(61, 109, 247, 0.06);
}

.list-title {
  font-size: 28rpx;
  font-weight: 600;
  color: #1D2129;
  margin-bottom: 20rpx;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.show-all-btn {
  display: inline-flex;
  align-items: center;
  gap: 6rpx;
  padding: 6rpx 16rpx;
  background: #F7F8FA;
  border-radius: 20rpx;
  font-size: 22rpx;
  color: #86909C;

  text {
    font-size: 22rpx;
    color: #86909C;
  }

  &:active {
    background: #E5E6EB;
  }
}

.empty-state {
  padding: 60rpx 0;
  text-align: center;
}

.empty-text {
  font-size: 26rpx;
  color: #86909C;
}

.record-item {
  padding: 20rpx 0;
  border-bottom: 1rpx solid #F5F7FA;

  &:last-child {
    border-bottom: none;
  }
}

.record-date {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12rpx;
}

.date-main {
  font-size: 26rpx;
  color: #1D2129;
  font-weight: 500;
}

.record-status-tag {
  padding: 4rpx 12rpx;
  border-radius: 6rpx;
  font-size: 22rpx;

  &.tag-normal { background: #f6ffed; color: #52c41a; }
  &.tag-late { background: #fff7e6; color: #fa8c16; }
  &.tag-absent { background: #fff1f0; color: #f5222d; }
}

.record-times {
  display: flex;
  gap: 40rpx;
}

.time-item {
  display: flex;
  align-items: center;
  gap: 10rpx;
}

.time-label {
  font-size: 24rpx;
  color: #86909C;
}

.time-value {
  font-size: 26rpx;
  color: #4E5969;
  font-weight: 500;
}

.clock-list {
  display: flex;
  flex-direction: column;
  gap: 12rpx;
  margin-top: 16rpx;
}

.clock-detail-item {
  display: flex;
  align-items: center;
  gap: 16rpx;
  padding: 16rpx;
  background: #F7F8FA;
  border-radius: 10rpx;
}

.clock-time {
  font-size: 28rpx;
  font-weight: 600;
  color: #1D2129;
  width: 140rpx;
}

.clock-type-tag {
  padding: 4rpx 12rpx;
  border-radius: 6rpx;
  font-size: 22rpx;
  font-weight: 500;

  &.type-in { background: #e6f7ff; color: #1890ff; }
  &.type-out { background: #f6ffed; color: #52c41a; }
  &.type-supplement { background: #fff7e6; color: #fa8c16; }
}

.clock-address {
  font-size: 24rpx;
  color: #86909C;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
