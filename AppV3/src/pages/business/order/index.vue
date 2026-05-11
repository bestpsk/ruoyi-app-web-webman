<template>
  <view class="order-container">
    <view class="search-section">
      <view class="search-box">
        <u-icon name="search" size="16" color="#86909C"></u-icon>
        <input class="search-input" type="text" v-model="queryParams.keyword" placeholder="搜索订单编号/客户" placeholder-class="search-placeholder" confirm-type="search" @input="onSearchInput" @confirm="handleSearch" />
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
          <view v-if="queryParams.status !== '' && queryParams.status !== undefined" class="filter-tag active" @click="clearFilter('status')">
            <text>{{ getOrderStatusName(queryParams.status) }}</text><u-icon name="close" size="12"></u-icon>
          </view>
        </view>
      </scroll-view>
    </view>

    <u-popup :show="showFilter" mode="top" round="16" @close="toggleFilter">
      <view class="popup-content">
        <view class="popup-title">筛选条件</view>
        <view class="form-item">
          <view class="form-label">订单状态</view>
          <view class="form-options">
            <view v-for="item in orderStatusOptions" :key="item.value" class="option-tag" :class="{ active: queryParams.status === item.value }" @click="queryParams.status = queryParams.status === item.value ? '' : item.value">{{ item.label }}</view>
          </view>
        </view>
        <view class="popup-actions">
          <u-button type="info" plain text="重置" @click="resetFilter"></u-button>
          <u-button type="primary" text="确定" @click="confirmFilter"></u-button>
        </view>
      </view>
    </u-popup>

    <scroll-view scroll-y class="list-scroll" :style="{ height: scrollHeight + 'px' }" @scrolltolower="loadMore" refresher-enabled :refresher-triggered="refreshing" @refresherrefresh="onPullDownRefresh">
      <view v-if="orderList.length > 0" class="card-list">
        <view v-for="item in orderList" :key="item.orderId" class="order-card" @click="goDetail(item)">
          <view class="card-header">
            <text class="order-no">{{ item.orderNo || ('ORD' + item.orderId) }}</text>
            <view class="status-tag" :class="'status-' + item.status">{{ getOrderStatusName(item.status) }}</view>
          </view>
          <view class="card-body">
            <view class="info-row">
              <view class="info-item"><text class="label">客户</text><text class="value">{{ item.customerName || '-' }}</text></view>
              <view class="info-item"><text class="label">门店</text><text class="value">{{ item.storeName || '-' }}</text></view>
            </view>
            <view class="info-row">
              <view class="info-item"><text class="label">金额</text><text class="value amount">¥{{ item.totalAmount || '0.00' }}</text></view>
              <view class="info-item"><text class="label">时间</text><text class="value">{{ formatTime(item.createTime) }}</text></view>
            </view>
          </view>
        </view>
      </view>
      <u-empty v-else-if="!loading" mode="data" text="暂无订单数据" :marginTop="100"></u-empty>
      <u-loadmore :status="loadStatus" :loading-text="'加载中...'" :loadmore-text="'上拉加载更多'" :nomore-text="'没有更多了'" :marginTop="20" />
    </scroll-view>
  </view>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { listSalesOrder } from '@/api/business/salesOrder'

const orderList = ref([])
const loading = ref(false)
const refreshing = ref(false)
const loadStatus = ref('loadmore')
const showFilter = ref(false)
const scrollHeight = ref(600)

let searchTimer = null

const hasActiveFilters = computed(() => queryParams.status !== '' && queryParams.status !== undefined)

const queryParams = reactive({ pageNum: 1, pageSize: 10, keyword: '', status: '' })

const orderStatusOptions = ref([
  { label: '待审核', value: '0' },
  { label: '企业已审', value: '1' },
  { label: '财务已审', value: '2' },
  { label: '已完成', value: '3' },
  { label: '已取消', value: '4' }
])

function getOrderStatusName(status) {
  const item = orderStatusOptions.value.find(s => s.value === String(status))
  return item ? item.label : '未知'
}

function formatTime(time) { if (!time) return ''; return time.substring(0, 10) }

async function getList(isRefresh = false) {
  if (loading.value) return
  loading.value = true
  if (isRefresh) { queryParams.pageNum = 1; loadStatus.value = 'loadmore' }
  try {
    const params = { ...queryParams }
    if (params.keyword) { params.orderNo = params.keyword; params.customerName = params.keyword }
    delete params.keyword
    const response = await listSalesOrder(params)
    const data = response.data || response
    const list = data.rows || []
    const total = data.total || 0
    orderList.value = isRefresh ? list : [...orderList.value, ...list]
    loadStatus.value = orderList.value.length >= total ? 'nomore' : 'loadmore'
  } catch (e) { console.error('获取订单列表失败:', e); loadStatus.value = 'error' }
  finally { loading.value = false; refreshing.value = false }
}

function loadMore() { if (loading.value || loadStatus.value === 'nomore') return; loadStatus.value = 'loading'; queryParams.pageNum++; getList() }
function onPullDownRefresh() { refreshing.value = true; getList(true) }
function handleSearch() { getList(true) }
function onSearchInput() { if (searchTimer) clearTimeout(searchTimer); searchTimer = setTimeout(() => handleSearch(), 500) }
function clearKeyword() { queryParams.keyword = ''; handleSearch() }
function toggleFilter() { showFilter.value = !showFilter.value }
function resetFilter() { queryParams.status = '' }
function confirmFilter() { showFilter.value = false; getList(true) }
function clearFilter(field) { queryParams[field] = ''; getList(true) }

function goDetail(item) {
  uni.navigateTo({ url: `/pages/business/order/detail?id=${item.orderId}` })
}

function calcScrollHeight() { const systemInfo = uni.getSystemInfoSync(); scrollHeight.value = systemInfo.windowHeight - 180 }
onMounted(() => { calcScrollHeight(); getList(true) })
</script>

<style lang="scss" scoped>
page { background-color: #F5F7FA; }
.order-container { min-height: 100vh; padding: 0 24rpx; padding-bottom: 40rpx; }

.search-section { padding: 20rpx 24rpx; margin-left: -24rpx; margin-right: -24rpx; background: linear-gradient(180deg, #3D6DF7 0%, #4A7AEF 100%); }
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

.order-card { background: #fff; border-radius: 16rpx; padding: 28rpx; box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.04);
  &:active { transform: scale(0.98); opacity: 0.9; }
}
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20rpx; }
.order-no { font-size: 28rpx; font-weight: 600; color: #1D2129; }
.status-tag { padding: 6rpx 16rpx; border-radius: 6rpx; font-size: 22rpx; font-weight: 500;
  &.status-0 { background: #FFF7E8; color: #FF7D00; }
  &.status-1 { background: #E8F0FE; color: #3D6DF7; }
  &.status-2 { background: #E8F0FE; color: #3D6DF7; }
  &.status-3 { background: #E8FFEA; color: #00B42A; }
  &.status-4 { background: #F2F3F5; color: #86909C; }
}

.card-body { padding: 20rpx 0; border-top: 1rpx solid #F2F3F5; }
.info-row { display: flex; margin-bottom: 16rpx; &:last-child { margin-bottom: 0; } }
.info-item { flex: 1; display: flex; align-items: center; gap: 12rpx;
  .label { font-size: 24rpx; color: #86909C; min-width: 60rpx; }
  .value { font-size: 26rpx; color: #1D2129;
    &.amount { color: #FF6B35; font-weight: 600; font-size: 30rpx; }
  }
}
</style>
