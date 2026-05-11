<template>
  <view class="form-container">
    <view class="form-section">
      <view class="form-field" @click="showUserPicker = mode !== 'view'">
        <view class="field-input-box">
          <u-icon name="account-fill" size="18" color="#86909C"></u-icon>
          <input class="field-input" :value="form.userName" placeholder="* 员工姓名" placeholder-class="field-placeholder" disabled :disabledColor="'#fff'" />
          <u-icon v-if="mode !== 'view'" name="arrow-right" size="14" color="#C9CDD4"></u-icon>
        </view>
      </view>

      <view class="form-field" @click="showEnterprisePicker = mode !== 'view'">
        <view class="field-input-box">
          <u-icon name="home-fill" size="18" color="#86909C"></u-icon>
          <input class="field-input" :value="form.enterpriseName" placeholder="* 企业" placeholder-class="field-placeholder" disabled :disabledColor="'#fff'" />
          <u-icon v-if="mode !== 'view'" name="arrow-right" size="14" color="#C9CDD4"></u-icon>
        </view>
      </view>

      <view class="form-field" @click="openCalendar">
        <view class="field-input-box">
          <u-icon name="calendar" size="18" color="#86909C"></u-icon>
          <input class="field-input" :value="dateRangeText" placeholder="* 选择日期范围" placeholder-class="field-placeholder" disabled :disabledColor="'#fff'" />
          <u-icon v-if="mode !== 'view'" name="arrow-right" size="14" color="#C9CDD4"></u-icon>
        </view>
      </view>

      <view class="form-item">
        <view class="form-label">下店目的</view>
        <view class="option-cards">
          <view v-for="item in purposeColumns" :key="item.value" class="option-card" :class="{ active: form.purpose === item.value, disabled: mode === 'view' }" @click="mode !== 'view' && (form.purpose = item.value, form.purposeName = item.label)">
            {{ item.label }}
          </view>
        </view>
      </view>

      <view class="form-item">
        <view class="form-label">状态</view>
        <view class="option-cards">
          <view v-for="item in statusColumns" :key="item.value" class="option-card" :class="{ active: form.status === item.value, disabled: mode === 'view' }" @click="mode !== 'view' && (form.status = item.value, form.statusName = item.label)">
            {{ item.label }}
          </view>
        </view>
      </view>

      <view class="form-field">
        <view class="field-textarea-box">
          <view class="textarea-prefix"><u-icon name="edit-pen" size="18" color="#86909C"></u-icon><text class="prefix-text">备注</text></view>
          <textarea class="field-textarea" v-model="form.remark" placeholder="请输入备注信息" placeholder-class="field-placeholder" :maxlength="500" auto-height></textarea>
        </view>
      </view>
    </view>

    <u-popup :show="showUserPicker" mode="bottom" round="16" @close="showUserPicker = false">
      <view class="picker-popup">
        <view class="picker-header">
          <text class="picker-title">选择员工</text>
          <view class="picker-close" @click="showUserPicker = false"><u-icon name="close" size="20" color="#86909C"></u-icon></view>
        </view>
        <view class="picker-search">
          <u-icon name="search" size="16" color="#86909C"></u-icon>
          <input class="picker-search-input" v-model="userSearchKeyword" placeholder="搜索员工姓名" placeholder-class="field-placeholder" @input="filterUserList" />
        </view>
        <scroll-view scroll-y class="picker-list">
          <view v-for="item in filteredUserList" :key="item.userId" class="picker-item" @click="onUserConfirm(item)">
            <text class="picker-item-text">{{ item.nickName || item.userName }}</text>
          </view>
          <u-empty v-if="filteredUserList.length === 0" mode="data" text="暂无员工数据" :marginTop="40"></u-empty>
        </scroll-view>
      </view>
    </u-popup>

    <u-popup :show="showEnterprisePicker" mode="bottom" round="16" @close="showEnterprisePicker = false">
      <view class="picker-popup">
        <view class="picker-header">
          <text class="picker-title">选择企业</text>
          <view class="picker-close" @click="showEnterprisePicker = false"><u-icon name="close" size="20" color="#86909C"></u-icon></view>
        </view>
        <view class="picker-search">
          <u-icon name="search" size="16" color="#86909C"></u-icon>
          <input class="picker-search-input" v-model="enterpriseSearchKeyword" placeholder="搜索企业名称" placeholder-class="field-placeholder" @input="filterEnterpriseList" />
        </view>
        <scroll-view scroll-y class="picker-list">
          <view v-for="item in filteredEnterpriseList" :key="item.enterpriseId" class="picker-item" @click="onEnterpriseConfirm(item)">
            <text class="picker-item-text">{{ item.enterpriseName }}</text>
          </view>
          <u-empty v-if="filteredEnterpriseList.length === 0" mode="data" text="暂无企业数据" :marginTop="40"></u-empty>
        </scroll-view>
      </view>
    </u-popup>

    <u-calendar
      :show="showCalendarPicker"
      mode="multiple"
      :maxDate="maxDate"
      :minDate="minDate"
      :formatter="calendarFormatter"
      :color="'#3D6DF7'"
      @confirm="onMultiDateConfirm"
      @close="showCalendarPicker = false"
    ></u-calendar>

    <view class="form-actions" v-if="mode !== 'view'">
      <u-button type="info" plain text="取消" @click="goBack"></u-button>
      <u-button type="primary" text="保存" :loading="submitting" @click="submitForm"></u-button>
    </view>
    <view class="form-actions" v-else>
      <u-button type="primary" plain text="编辑" @click="goEdit"></u-button>
      <u-button type="error" plain text="删除" @click="handleDelete"></u-button>
    </view>
  </view>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { getSchedule, addSchedule, addScheduleBatch, updateSchedule, delSchedule, getScheduleDates } from '@/api/business/schedule'
import { listEnterprise } from '@/api/business/enterprise'
import { listUser } from '@/api/system/user'
import { getDicts } from '@/api/system/dict/data'

const submitting = ref(false)
const showUserPicker = ref(false)
const showEnterprisePicker = ref(false)
const showCalendarPicker = ref(false)
const mode = ref('add')
const scheduleId = ref(null)

const userList = ref([])
const filteredUserList = ref([])
const userSearchKeyword = ref('')

const enterpriseList = ref([])
const filteredEnterpriseList = ref([])
const enterpriseSearchKeyword = ref('')

const purposeColumns = ref([])
const statusColumns = ref([])

const bookedDates = ref([])

const minDate = ref(Number(new Date()))
const maxDate = ref(Number(new Date(new Date().setFullYear(new Date().getFullYear() + 1))))

const form = reactive({
  scheduleId: undefined,
  userId: '',
  userName: '',
  enterpriseId: '',
  enterpriseName: '',
  selectedDates: [],
  startDate: '',
  endDate: '',
  purpose: '',
  purposeName: '',
  status: '1',
  statusName: '',
  remark: ''
})

const dateRangeText = computed(() => {
  if (!form.selectedDates || form.selectedDates.length === 0) {
    return ''
  }

  const formatMonthDay = (dateStr) => {
    const [year, month, day] = dateStr.split('-')
    return `${parseInt(month)}月${parseInt(day)}日`
  }

  if (form.selectedDates.length === 1) {
    return formatMonthDay(form.selectedDates[0])
  }

  if (form.selectedDates.length <= 3) {
    return form.selectedDates.map(formatMonthDay).join('、')
  }

  return `${formatMonthDay(form.selectedDates[0])} 等 ${form.selectedDates.length} 天`
})

function onUserConfirm(item) {
  form.userId = item.userId
  form.userName = item.nickName || item.userName
  showUserPicker.value = false
  loadBookedDates()
}

function onEnterpriseConfirm(item) {
  form.enterpriseId = item.enterpriseId
  form.enterpriseName = item.enterpriseName
  showEnterprisePicker.value = false
}

async function openCalendar() {
  if (mode.value === 'view') return
  if (!form.userId) {
    uni.showToast({ title: '请先选择员工', icon: 'none' })
    return
  }
  await loadBookedDates()
  showCalendarPicker.value = true
}

function calendarFormatter(day) {
  const dateStr = `${day.year}-${String(day.month).padStart(2, '0')}-${String(day.day).padStart(2, '0')}`
  if (bookedDates.value.includes(dateStr)) {
    day.bottomInfo = '已安排'
    day.type = 'disabled'
  }
  return day
}

function onMultiDateConfirm(e) {
  console.log('[Calendar] 多选结果:', e)

  if (Array.isArray(e) && e.length > 0) {
    form.selectedDates = [...e].sort()

    if (form.selectedDates.length > 0) {
      form.startDate = form.selectedDates[0]
      form.endDate = form.selectedDates[form.selectedDates.length - 1]
    }

    console.log('[Calendar] 最终选中日期:', form.selectedDates)
  } else {
    console.warn('[Calendar] 未选择任何日期或格式异常:', e)
  }

  showCalendarPicker.value = false
}

async function loadDictData() {
  try {
    const [purposeRes, statusRes] = await Promise.all([
      getDicts('biz_schedule_purpose'),
      getDicts('biz_schedule_status')
    ])
    purposeColumns.value = (purposeRes.data || []).map(p => ({ label: p.dictLabel, value: p.dictValue }))
    statusColumns.value = (statusRes.data || []).map(p => ({ label: p.dictLabel, value: p.dictValue }))
    if (statusColumns.value.length > 0 && !form.status) {
      form.status = statusColumns.value[0].value
      form.statusName = statusColumns.value[0].label
    }
  } catch (e) { console.error('加载字典数据失败:', e) }
}

async function loadBookedDates() {
  if (!form.userId) return
  try {
    const now = new Date()
    const yearMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
    const response = await getScheduleDates({ userId: form.userId, yearMonth })
    bookedDates.value = response.data || []
  } catch (e) { console.error('加载已安排日期失败:', e) }
}

async function loadUserList() {
  try {
    const response = await listUser({ pageNum: 1, pageSize: 1000, status: '0' })
    const data = response.data || response
    userList.value = data.rows || []
    filteredUserList.value = userList.value
  } catch (e) { console.error('加载员工列表失败:', e) }
}

async function loadEnterpriseList() {
  try {
    const response = await listEnterprise({ pageNum: 1, pageSize: 1000, status: '0' })
    const data = response.data || response
    enterpriseList.value = data.rows || []
    filteredEnterpriseList.value = enterpriseList.value
  } catch (e) { console.error('加载企业列表失败:', e) }
}

function filterUserList() {
  const keyword = userSearchKeyword.value.toLowerCase()
  filteredUserList.value = userList.value.filter(u => (u.nickName || u.userName || '').toLowerCase().includes(keyword))
}

function filterEnterpriseList() {
  const keyword = enterpriseSearchKeyword.value.toLowerCase()
  filteredEnterpriseList.value = enterpriseList.value.filter(e => (e.enterpriseName || '').toLowerCase().includes(keyword))
}

async function loadDetail() {
  if (!scheduleId.value) return
  try {
    uni.showLoading({ title: '加载中...' })
    const response = await getSchedule(scheduleId.value)
    const data = response.data || response
    const pItem = purposeColumns.value.find(p => p.value === String(data.purpose))
    const sItem = statusColumns.value.find(s => s.value === String(data.status))
    Object.assign(form, {
      scheduleId: data.scheduleId,
      userId: data.userId || '',
      userName: data.userName || '',
      enterpriseId: data.enterpriseId || '',
      enterpriseName: data.enterpriseName || '',
      startDate: data.scheduleDate || '',
      endDate: data.scheduleDate || '',
      purpose: String(data.purpose || ''),
      purposeName: pItem ? pItem.label : '',
      status: String(data.status ?? '1'),
      statusName: sItem ? sItem.label : '',
      remark: data.remark || ''
    })
  } catch (e) { console.error('加载详情失败:', e); uni.showToast({ title: '加载失败', icon: 'none' }) }
  finally { uni.hideLoading() }
}

async function submitForm() {
  if (!form.userName) { uni.showToast({ title: '请选择员工', icon: 'none' }); return }
  if (!form.enterpriseId) { uni.showToast({ title: '请选择企业', icon: 'none' }); return }
  if (!form.selectedDates || form.selectedDates.length === 0) { uni.showToast({ title: '请选择至少一个日期', icon: 'none' }); return }
  if (!form.purpose) { uni.showToast({ title: '请选择下店目的', icon: 'none' }); return }

  const conflictDates = form.selectedDates.filter(date => bookedDates.value.includes(date))
  if (conflictDates.length > 0) {
    uni.showModal({ title: '日期冲突', content: `以下日期已有安排：${conflictDates.join('、')}`, showCancel: false })
    return
  }

  submitting.value = true
  try {
    const scheduleList = form.selectedDates.map(scheduleDate => ({
      userId: form.userId,
      userName: form.userName,
      enterpriseId: form.enterpriseId,
      enterpriseName: form.enterpriseName,
      scheduleDate,
      purpose: form.purpose,
      status: form.status,
      remark: form.remark
    }))

    if (form.scheduleId) {
      await updateSchedule({ scheduleId: form.scheduleId, userId: form.userId, userName: form.userName, enterpriseId: form.enterpriseId, enterpriseName: form.enterpriseName, scheduleDate: form.startDate, purpose: form.purpose, status: form.status, remark: form.remark })
      uni.showToast({ title: '修改成功', icon: 'success' })
    } else {
      await addScheduleBatch(scheduleList)
      uni.showToast({ title: `新增成功（共${scheduleList.length}天）`, icon: 'success' })
    }
    setTimeout(() => goBack(), 1500)
  } catch (e) {
    console.error('提交失败:', e)
    const msg = e?.msg || e?.message || '操作失败，请重试'
    uni.showToast({ title: msg, icon: 'none', duration: 2000 })
  } finally { submitting.value = false }
}

function handleDelete() {
  if (!scheduleId.value) return
  uni.showModal({ title: '提示', content: '确认删除该行程?', success: async (res) => {
    if (res.confirm) { try { await delSchedule(scheduleId.value); uni.showToast({ title: '删除成功', icon: 'success' }); setTimeout(() => goBack(), 1500) } catch (e) { console.error('删除失败:', e) } }
  }})
}

function goEdit() { mode.value = 'edit'; uni.setNavigationBarTitle({ title: '编辑行程' }) }
function goBack() { const pages = getCurrentPages(); if (pages.length > 1) uni.navigateBack(); else uni.redirectTo({ url: '/pages/business/schedule/index' }) }

onMounted(() => {
  const pages = getCurrentPages()
  const options = pages[pages.length - 1].options || {}
  mode.value = options.mode || 'add'
  scheduleId.value = options.id ? parseInt(options.id) : null
  loadDictData()
  loadUserList()
  loadEnterpriseList()
  if (mode.value === 'view') { uni.setNavigationBarTitle({ title: '行程详情' }); loadDetail() }
  else if (mode.value === 'edit') { uni.setNavigationBarTitle({ title: '编辑行程' }); loadDetail() }
  else { uni.setNavigationBarTitle({ title: '新增行程' }) }
})
</script>

<style lang="scss" scoped>
page { background-color: #F5F7FA; }

.form-container { min-height: 100vh; padding-bottom: 140rpx; }

.form-section {
  margin: 24rpx;
  background: #fff;
  border-radius: 20rpx;
  padding: 32rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
}

.form-field { margin-bottom: 20rpx; &:last-child { margin-bottom: 0; } }

.field-input-box {
  display: flex;
  align-items: center;
  background: #F7F8FA;
  border-radius: 12rpx;
  padding: 0 20rpx;
  height: 88rpx;
  gap: 16rpx;
  border: 2rpx solid transparent;
  transition: all 0.2s;

  &:active { background: #EFF0F1; }
}

.field-input {
  flex: 1;
  font-size: 30rpx;
  color: #1D2129;
  height: 88rpx;
  line-height: 88rpx;
}

.field-placeholder { color: #C9CDD4; font-size: 30rpx; }

.field-textarea-box {
  display: flex;
  flex-direction: column;
  background: #F7F8FA;
  border-radius: 12rpx;
  padding: 16rpx 20rpx;
  gap: 8rpx;
  border: 2rpx solid transparent;
}

.textarea-prefix {
  display: flex;
  align-items: center;
  gap: 10rpx;
}

.prefix-text {
  font-size: 26rpx;
  color: #86909C;
  font-weight: 500;
}

.field-textarea {
  width: 100%;
  min-height: 120rpx;
  font-size: 28rpx;
  color: #1D2129;
  line-height: 1.6;
}

.form-item { margin-bottom: 24rpx; }

.form-label {
  font-size: 26rpx;
  color: #86909C;
  font-weight: 500;
  margin-bottom: 16rpx;
}

.option-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 16rpx;
}

.option-card {
  padding: 16rpx 28rpx;
  background: #F7F8FA;
  border-radius: 8rpx;
  font-size: 26rpx;
  color: #4E5969;
  border: 2rpx solid transparent;
  transition: all 0.2s;

  &.active {
    background: #E8F0FE;
    color: #3D6DF7;
    border-color: #3D6DF7;
  }

  &.disabled {
    opacity: 0.5;
    pointer-events: none;
  }
}

.picker-popup {
  background: #fff;
  border-radius: 24rpx 24rpx 0 0;
  max-height: 70vh;
  display: flex;
  flex-direction: column;
}

.picker-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 28rpx 32rpx;
  border-bottom: 1rpx solid #F2F3F5;
}

.picker-title {
  font-size: 32rpx;
  font-weight: 600;
  color: #1D2129;
}

.picker-close { padding: 8rpx; }

.picker-search {
  display: flex;
  align-items: center;
  gap: 12rpx;
  margin: 20rpx 32rpx;
  padding: 0 20rpx;
  background: #F7F8FA;
  border-radius: 36rpx;
  height: 72rpx;
}

.picker-search-input {
  flex: 1;
  font-size: 28rpx;
  color: #1D2129;
  height: 72rpx;
}

.picker-list {
  flex: 1;
  padding: 0 32rpx 32rpx;
  max-height: 50vh;
}

.picker-item {
  padding: 24rpx 0;
  border-bottom: 1rpx solid #F2F3F5;

  &:last-child { border-bottom: none; }
  &:active { background: #F7F8FA; }
}

.picker-item-text {
  font-size: 28rpx;
  color: #1D2129;
}

.form-actions {
  position: fixed;
  left: 24rpx;
  right: 24rpx;
  bottom: 40rpx;
  display: flex;
  gap: 20rpx;
  z-index: 100;

  .u-button { flex: 1; height: 88rpx; border-radius: 44rpx; font-size: 30rpx; font-weight: 600; }
}
</style>
