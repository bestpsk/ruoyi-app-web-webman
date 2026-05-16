<template>
  <view class="schedule-container">
    <view class="search-section">
      <view class="month-picker" @click="showMonthPicker = true">
        <u-icon name="calendar" size="16" color="#fff"></u-icon>
        <text class="month-text">{{ queryParams.yearMonth }}</text>
        <u-icon name="arrow-down" size="12" color="#fff"></u-icon>
      </view>
      <view class="search-box">
        <u-icon name="search" size="16" color="#86909C"></u-icon>
        <input class="search-input" type="text" v-model="queryParams.keyword" placeholder="搜索员工/企业" placeholder-class="search-placeholder" confirm-type="search" @input="onSearchInput" @confirm="handleSearch" />
        <view v-if="queryParams.keyword" class="clear-btn" @click="clearKeyword">
          <u-icon name="close-circle-fill" size="14" color="#C9CDD4"></u-icon>
        </view>
        <view class="filter-btn" @click="toggleFilter">
          <text>筛选</text>
          <u-icon name="arrow-down" size="12" :class="{ 'icon-rotate': showFilter }"></u-icon>
        </view>
      </view>
    </view>

    <view v-if="hasActiveFilters" class="active-filters">
      <scroll-view scroll-x class="filter-scroll">
        <view class="filter-tags">
          <view v-if="queryParams.purpose" class="filter-tag active" @click="clearFilter('purpose')">
            <text>{{ getPurposeName(queryParams.purpose) }}</text><u-icon name="close" size="12"></u-icon>
          </view>
          <view v-if="queryParams.status" class="filter-tag active" @click="clearFilter('status')">
            <text>{{ getStatusName(queryParams.status) }}</text><u-icon name="close" size="12"></u-icon>
          </view>
        </view>
      </scroll-view>
    </view>

    <u-popup :show="showFilter" mode="top" round="16" @close="toggleFilter">
      <view class="popup-content">
        <view class="popup-title">筛选条件</view>
        <view class="form-item">
          <view class="form-label">下店目的</view>
          <view class="form-options">
            <view v-for="item in purposeOptions" :key="item.value" class="option-tag" :class="{ active: queryParams.purpose === item.value }" @click="queryParams.purpose = queryParams.purpose === item.value ? '' : item.value">{{ item.label }}</view>
          </view>
        </view>
        <view class="form-item">
          <view class="form-label">状态</view>
          <view class="form-options">
            <view v-for="item in statusOptions" :key="item.value" class="option-tag" :class="{ active: queryParams.status === item.value }" @click="queryParams.status = queryParams.status === item.value ? '' : item.value">{{ item.label }}</view>
          </view>
        </view>
        <view class="popup-actions">
          <u-button type="info" plain text="重置" @click="resetFilter"></u-button>
          <u-button type="primary" text="确定" @click="confirmFilter"></u-button>
        </view>
      </view>
    </u-popup>

    <u-datetime-picker :show="showMonthPicker" mode="year-month" v-model="monthPickerValue" @confirm="onMonthConfirm" @cancel="showMonthPicker = false" @close="showMonthPicker = false"></u-datetime-picker>

    <scroll-view scroll-y class="list-scroll" :style="{ height: scrollHeight + 'px' }" @scrolltolower="loadMore" refresher-enabled :refresher-triggered="refreshing" @refresherrefresh="onPullDownRefresh">
      <view v-if="scheduleList.length > 0" class="card-list">
        <view v-for="item in scheduleList" :key="item.scheduleIds[0]" class="schedule-card" @click="goDetail(item)">
          <view class="card-header">
            <view class="user-info">
              <u-icon name="account-fill" size="16" color="#3D6DF7"></u-icon>
              <text class="user-name">{{ item.userName || '-' }}</text>
            </view>
            <view class="status-tag" :class="'status-' + item.status">{{ getStatusName(item.status) }}</view>
          </view>
          <view class="card-body">
            <view class="info-row">
              <view class="info-item">
                <text class="label">企业</text>
                <text class="value">{{ item.enterpriseName || '-' }}</text>
              </view>
              <view class="info-item">
                <text class="label">目的</text>
                <text class="value purpose-text">{{ getPurposeName(item.purpose) }}</text>
              </view>
            </view>
            <view class="date-tags-row">
              <view class="date-tag" v-for="(date, idx) in getDisplayDates(item.scheduleDates)" :key="idx">
                {{ formatDay(date) }}
              </view>
              <view class="date-tag more" v-if="item.scheduleDates.length > 6">
                +{{ item.scheduleDates.length - 6 }}
              </view>
            </view>
            <view class="info-row" v-if="item.remark">
              <view class="info-item full">
                <text class="label">备注</text>
                <text class="value remark-text">{{ item.remark }}</text>
              </view>
            </view>
          </view>
          <view class="card-footer">
            <view class="time-text">共{{ item.scheduleDates.length }}天</view>
            <view class="action-btns">
              <view class="action-btn edit" @click.stop="goEdit(item)">
                <u-icon name="edit-pen" size="14"></u-icon><text>编辑</text>
              </view>
              <view class="action-btn delete" @click.stop="handleDelete(item)">
                <u-icon name="trash" size="14"></u-icon><text>删除</text>
              </view>
            </view>
          </view>
        </view>
      </view>
      <u-empty v-else-if="!loading" mode="data" text="暂无行程数据" :marginTop="100"></u-empty>
      <u-loadmore :status="loadStatus" :loading-text="'加载中...'" :loadmore-text="'上拉加载更多'" :nomore-text="'没有更多了'" :marginTop="20" />
    </scroll-view>

    <view class="fab-btn" @click="goAdd">
      <u-icon name="plus" size="24" color="#fff"></u-icon>
    </view>
  </view>
</template>

<script setup>
/**
 * @description 行程列表页 - 行程安排管理
 * @description 展示行程列表，支持月份选择、按目的/状态筛选、关键词搜索，
 * 将同一员工同一企业同一目的的行程按日期分组展示，支持删除整组行程
 */
import { ref, reactive, onMounted, computed } from 'vue'
import { listSchedule, delSchedule } from '@/api/business/schedule'

const scheduleList = ref([])
const loading = ref(false)
const refreshing = ref(false)
const loadStatus = ref('loadmore')
const showFilter = ref(false)
const showMonthPicker = ref(false)
const scrollHeight = ref(600)
const monthPickerValue = ref(Number(new Date()))

let searchTimer = null

const hasActiveFilters = computed(() => queryParams.purpose || queryParams.status)

const queryParams = reactive({
  pageNum: 1,
  pageSize: 20,
  keyword: '',
  yearMonth: new Date().toISOString().slice(0, 7),
  userName: '',
  enterpriseName: '',
  purpose: '',
  status: ''
})

const purposeOptions = ref([
  { label: '爆卡', value: '1' },
  { label: '启动销售', value: '2' },
  { label: '售后服务', value: '3' },
  { label: '洽谈业务', value: '4' }
])

const statusOptions = ref([
  { label: '已预约', value: '1' },
  { label: '服务中', value: '2' },
  { label: '已完成', value: '3' },
  { label: '已取消', value: '4' }
])

/** 行程目的编码映射为中文名称（1-爆卡/2-启动销售/3-售后服务/4-洽谈业务） */
function getPurposeName(value) {
  const item = purposeOptions.value.find(p => p.value === String(value))
  return item ? item.label : '-'
}

/** 行程状态编码映射为中文名称（1-已预约/2-服务中/3-已完成/4-已取消） */
function getStatusName(value) {
  const item = statusOptions.value.find(s => s.value === String(value))
  return item ? item.label : '-'
}

/** 格式化日期为MM-DD简短格式 */
function formatDay(dateStr) {
  if (!dateStr) return ''
  return dateStr.substring(5)
}

/**
 * 将行程列表按员工+企业+目的+状态+备注进行分组，
 * 同组内的日期合并为scheduleDates数组并按时间排序，
 * 用于在列表中以组为单位展示多天行程
 */
function groupScheduleList(list) {
  const groupMap = new Map()

  console.log('[Schedule] 原始数据条数:', list.length)
  console.log('[Schedule] 原始数据:', list)

  list.forEach(item => {
    const key = `${item.userId}_${item.enterpriseId}_${item.purpose}_${item.status}_${item.remark || ''}`

    if (!groupMap.has(key)) {
      groupMap.set(key, {
        ...item,
        scheduleIds: [item.scheduleId],
        scheduleDates: [item.scheduleDate]
      })
    } else {
      const group = groupMap.get(key)
      if (!group.scheduleIds.includes(item.scheduleId)) {
        group.scheduleIds.push(item.scheduleId)
      }
      if (!group.scheduleDates.includes(item.scheduleDate)) {
        group.scheduleDates.push(item.scheduleDate)
      }
    }
  })

  const result = Array.from(groupMap.values())
    .map(group => ({
      ...group,
      scheduleDates: [...new Set(group.scheduleDates)].sort()
    }))
    .sort((a, b) => new Date(a.scheduleDates[0]) - new Date(b.scheduleDates[0]))

  console.log('[Schedule] 分组后数据条数:', result.length)
  console.log('[Schedule] 分组后数据:', result)

  return result
}

/** 取日期数组前6个用于展示，超出部分省略 */
function getDisplayDates(dates) {
  return dates.slice(0, 6)
}

/** 月份选择确认，格式化为YYYY-MM并重新加载列表 */
function onMonthConfirm(e) {
  const date = new Date(e.value)
  queryParams.yearMonth = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
  showMonthPicker.value = false
  getList(true)
}

/** 加载行程列表，根据年月计算起止日期，搜索时同时匹配员工名和企业名，加载后自动分组 */
async function getList(isRefresh = false) {
  if (loading.value) return
  loading.value = true
  if (isRefresh) { queryParams.pageNum = 1; loadStatus.value = 'loadmore' }

  try {
    const [year, month] = queryParams.yearMonth.split('-')
    const startDate = `${year}-${month}-01`
    const endDate = `${year}-${month}-${new Date(year, month, 0).getDate()}`
    const params = { ...queryParams, startDate, endDate }
    if (params.keyword) { params.userName = params.keyword; params.enterpriseName = params.keyword }
    delete params.keyword; delete params.yearMonth

    const response = await listSchedule(params)
    const data = response.data || response
    const list = data.rows || []
    const total = data.total || 0

    console.log('[Schedule] API返回条数:', list.length, '总数:', total)

    const grouped = groupScheduleList(list)

    if (isRefresh) {
      scheduleList.value = grouped
    } else {
      const existingIds = new Set(scheduleList.value.flatMap(item => item.scheduleIds))
      const newItems = grouped.filter(item =>
        !item.scheduleIds.some(id => existingIds.has(id))
      )
      scheduleList.value = [...scheduleList.value, ...newItems]
    }

    loadStatus.value = scheduleList.value.length >= total ? 'nomore' : 'loadmore'
  } catch (e) {
    console.error('获取行程列表失败:', e)
    loadStatus.value = 'error'
  } finally { loading.value = false; refreshing.value = false }
}

function loadMore() {
  if (loading.value || loadStatus.value === 'nomore') return
  loadStatus.value = 'loading'
  queryParams.pageNum++
  getList()
}

function onPullDownRefresh() { refreshing.value = true; getList(true) }
function handleSearch() { getList(true) }
function onSearchInput() { if (searchTimer) clearTimeout(searchTimer); searchTimer = setTimeout(() => handleSearch(), 500) }
function clearKeyword() { queryParams.keyword = ''; handleSearch() }
function toggleFilter() { showFilter.value = !showFilter.value }
function resetFilter() { queryParams.purpose = ''; queryParams.status = '' }
function confirmFilter() { showFilter.value = false; getList(true) }
function clearFilter(field) { queryParams[field] = ''; getList(true) }

function goDetail(item) {
  uni.navigateTo({ url: `/pages/business/schedule/form?id=${item.scheduleIds[0]}&mode=view` })
}
function goEdit(item) {
  uni.navigateTo({ url: `/pages/business/schedule/form?id=${item.scheduleIds[0]}&mode=edit` })
}
function goAdd() {
  uni.navigateTo({ url: '/pages/business/schedule/form?mode=add' })
}

/** 删除行程组，弹出确认框后批量删除该组所有行程ID，成功后刷新列表 */
function handleDelete(item) {
  uni.showModal({
    title: '提示', content: `是否确认删除该行程（共${item.scheduleIds.length}天）?`,
    success: async (res) => {
      if (res.confirm) {
        try { await delSchedule(item.scheduleIds.join(',')); uni.showToast({ title: '删除成功', icon: 'success' }); getList(true) }
        catch (e) { console.error('删除失败:', e) }
      }
    }
  })
}

function calcScrollHeight() {
  const systemInfo = uni.getSystemInfoSync()
  scrollHeight.value = systemInfo.windowHeight - 100
}

onMounted(() => { calcScrollHeight(); getList(true) })
</script>

<style lang="scss" scoped>
page { background-color: #F5F7FA; }
.schedule-container { min-height: 100vh; padding: 0 24rpx; padding-bottom: 120rpx; }

.search-section { padding: 20rpx 24rpx; margin-left: -24rpx; margin-right: -24rpx; background: linear-gradient(180deg, #3D6DF7 0%, #4A7AEF 100%); }
.month-picker { display: flex; align-items: center; justify-content: center; gap: 8rpx; margin-bottom: 16rpx; }
.month-text { font-size: 30rpx; font-weight: 600; color: #fff; }

.search-box { display: flex; align-items: center; background: #fff; border-radius: 36rpx; padding: 0 8rpx 0 28rpx; height: 72rpx; gap: 12rpx; box-sizing: border-box; }
.search-input { flex: 1; font-size: 28rpx; color: #1D2129; height: 72rpx; min-width: 0; }
.search-placeholder { color: #86909C; font-size: 28rpx; }
.clear-btn { flex-shrink: 0; padding: 8rpx; display: flex; align-items: center; }
.filter-btn { flex-shrink: 0; display: flex; align-items: center; justify-content: center; gap: 4rpx; height: 56rpx; padding: 0 22rpx; background: #E8F0FE; border-radius: 28rpx;
  text { font-size: 26rpx; color: #3D6DF7; font-weight: 500; white-space: nowrap; }
  .icon-rotate { transform: rotate(180deg); transition: transform 0.3s ease; }
}

.active-filters { padding: 12rpx 24rpx 16rpx; margin-left: -24rpx; margin-right: -24rpx; background: linear-gradient(180deg, #4A7AEF 0%, #F5F7FA 100%); }
.filter-scroll { white-space: nowrap; }
.filter-tags { display: inline-flex; gap: 16rpx; padding: 16rpx 0; }
.filter-tag { display: inline-flex; align-items: center; gap: 8rpx; padding: 10rpx 20rpx; background: rgba(255,255,255,0.2); border-radius: 28rpx; font-size: 24rpx; color: #fff;
  &.active { background: #fff; color: #3D6DF7; }
}

.popup-content { padding: 30rpx; background: #fff; }
.popup-title { font-size: 32rpx; font-weight: 600; color: #1D2129; margin-bottom: 30rpx; }
.form-item { margin-bottom: 30rpx; }
.form-label { font-size: 28rpx; color: #1D2129; font-weight: 500; margin-bottom: 16rpx; }
.form-options { display: flex; flex-wrap: wrap; gap: 16rpx; }
.option-tag { padding: 14rpx 28rpx; background: #F5F7FA; border-radius: 8rpx; font-size: 26rpx; color: #4E5969; border: 2rpx solid transparent;
  &.active { background: #E8F0FE; color: #3D6DF7; border-color: #3D6DF7; }
}
.popup-actions { display: flex; gap: 20rpx; margin-top: 40rpx; padding-top: 30rpx; border-top: 1rpx solid #E5E6EB; .u-button { flex: 1; } }

.list-scroll { padding: 20rpx 0; }
.card-list { display: flex; flex-direction: column; gap: 20rpx; }

.schedule-card { background: #fff; border-radius: 16rpx; padding: 28rpx; box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.04);
  &:active { transform: scale(0.98); opacity: 0.9; }
}
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20rpx; }
.user-info { display: flex; align-items: center; gap: 8rpx; }
.user-name { font-size: 28rpx; font-weight: 600; color: #1D2129; }
.status-tag { padding: 6rpx 16rpx; border-radius: 6rpx; font-size: 22rpx; font-weight: 500;
  &.status-1 { background: #E8F0FE; color: #3D6DF7; }
  &.status-2 { background: #FFF7E8; color: #FF7D00; }
  &.status-3 { background: #E8FFEA; color: #00B42A; }
  &.status-4 { background: #F2F3F5; color: #86909C; }
}

.card-body { padding: 20rpx 0; border-top: 1rpx solid #F2F3F5; border-bottom: 1rpx solid #F2F3F5; }
.info-row { display: flex; margin-bottom: 16rpx; &:last-child { margin-bottom: 0; } }
.info-item { flex: 1; display: flex; align-items: center; gap: 12rpx;
  &.full { flex: none; width: 100%; }
  .label { font-size: 24rpx; color: #86909C; min-width: 60rpx; }
  .value { font-size: 26rpx; color: #1D2129;
    &.purpose-text { color: #3D6DF7; }
    &.remark-text { color: #86909C; }
  }
}

.date-tags-row {
  display: flex;
  flex-wrap: nowrap;
  gap: 12rpx;
  margin-top: 16rpx;
  overflow: hidden;
}

.date-tag {
  padding: 6rpx 16rpx;
  background: #F7F8FA;
  border-radius: 6rpx;
  font-size: 22rpx;
  color: #4E5969;
  flex-shrink: 0;

  &.more {
    background: #E8F0FE;
    color: #3D6DF7;
  }
}

.card-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 20rpx; padding-top: 16rpx; }
.time-text { font-size: 22rpx; color: #C9CDD4; }
.action-btns { display: flex; gap: 24rpx; }
.action-btn { display: flex; align-items: center; gap: 6rpx; font-size: 24rpx; padding: 8rpx 16rpx; border-radius: 8rpx;
  &.edit { color: #3D6DF7; background: #E8F0FE; }
  &.delete { color: #F53F3F; background: #FFF1F0; }
}

.fab-btn { position: fixed; right: 32rpx; bottom: 120rpx; width: 100rpx; height: 100rpx; border-radius: 50%; background: linear-gradient(135deg, #FF6B35, #FF8F5E); display: flex; align-items: center; justify-content: center; box-shadow: 0 8rpx 24rpx rgba(255,107,53,0.4);
  &:active { transform: scale(0.95); opacity: 0.9; }
}
</style>
