<template>
  <view class="operation-container">
    <view class="search-section">
      <view class="search-box">
        <u-icon name="search" size="16" color="#86909C"></u-icon>
        <input class="search-input" type="text" v-model="queryParams.keyword" placeholder="搜索客户/操作类型" placeholder-class="search-placeholder" confirm-type="search" @input="onSearchInput" @confirm="handleSearch" />
        <view v-if="queryParams.keyword" class="clear-btn" @click="clearKeyword">
          <u-icon name="close-circle-fill" size="14" color="#C9CDD4"></u-icon>
        </view>
      </view>
    </view>

    <scroll-view scroll-y class="list-scroll" :style="{ height: scrollHeight + 'px' }" @scrolltolower="loadMore" refresher-enabled :refresher-triggered="refreshing" @refresherrefresh="onPullDownRefresh">
      <view v-if="operationList.length > 0" class="card-list">
        <view v-for="item in operationList" :key="item.recordId" class="operation-card">
          <view class="card-header">
            <view class="type-badge">{{ item.operationType || '操作' }}</view>
            <text class="time-text">{{ formatTime(item.createTime) }}</text>
          </view>
          <view class="card-body">
            <view class="info-row">
              <view class="info-item full">
                <text class="label">客户</text>
                <text class="value">{{ item.customerName || '-' }}</text>
              </view>
            </view>
            <view class="info-row">
              <view class="info-item full">
                <text class="label">内容</text>
                <text class="value content-text">{{ item.content || item.remark || '-' }}</text>
              </view>
            </view>
            <view class="info-row" v-if="item.operatorName">
              <view class="info-item">
                <text class="label">操作人</text>
                <text class="value">{{ item.operatorName }}</text>
              </view>
            </view>
          </view>
        </view>
      </view>
      <u-empty v-else-if="!loading" mode="data" text="暂无操作记录" :marginTop="100"></u-empty>
      <u-loadmore :status="loadStatus" :loading-text="'加载中...'" :loadmore-text="'上拉加载更多'" :nomore-text="'没有更多了'" :marginTop="20" />
    </scroll-view>
  </view>
</template>

<script setup>
/**
 * @description 操作记录列表页 - 项目操作记录查看
 * @description 展示操作记录列表，支持关键词搜索（客户名/操作类型）、分页加载、下拉刷新
 */
import { ref, reactive, onMounted } from 'vue'
import { listOperation } from '@/api/business/operationRecord'

const operationList = ref([])
const loading = ref(false)
const refreshing = ref(false)
const loadStatus = ref('loadmore')
const scrollHeight = ref(600)

let searchTimer = null

const queryParams = reactive({ pageNum: 1, pageSize: 20, keyword: '' })

function formatTime(time) { if (!time) return ''; return time.substring(0, 16) }

/** 加载操作记录列表，支持分页和关键词搜索（搜索时同时匹配客户名和操作类型） */
async function getList(isRefresh = false) {
  if (loading.value) return
  loading.value = true
  if (isRefresh) { queryParams.pageNum = 1; loadStatus.value = 'loadmore' }
  try {
    const params = { ...queryParams }
    if (params.keyword) { params.customerName = params.keyword; params.operationType = params.keyword }
    delete params.keyword
    const response = await listOperation(params)
    const data = response.data || response
    const list = data.rows || []
    const total = data.total || 0
    operationList.value = isRefresh ? list : [...operationList.value, ...list]
    loadStatus.value = operationList.value.length >= total ? 'nomore' : 'loadmore'
  } catch (e) { console.error('获取操作记录失败:', e); loadStatus.value = 'error' }
  finally { loading.value = false; refreshing.value = false }
}

function loadMore() { if (loading.value || loadStatus.value === 'nomore') return; loadStatus.value = 'loading'; queryParams.pageNum++; getList() }
function onPullDownRefresh() { refreshing.value = true; getList(true) }
function handleSearch() { getList(true) }
function onSearchInput() { if (searchTimer) clearTimeout(searchTimer); searchTimer = setTimeout(() => handleSearch(), 500) }
function clearKeyword() { queryParams.keyword = ''; handleSearch() }

function calcScrollHeight() { const systemInfo = uni.getSystemInfoSync(); scrollHeight.value = systemInfo.windowHeight - 180 }
onMounted(() => { calcScrollHeight(); getList(true) })
</script>

<style lang="scss" scoped>
page { background-color: #F5F7FA; }
.operation-container { min-height: 100vh; padding: 0 24rpx; padding-bottom: 40rpx; }

.search-section { padding: 20rpx 24rpx; margin-left: -24rpx; margin-right: -24rpx; background: linear-gradient(180deg, #3D6DF7 0%, #4A7AEF 100%); }
.search-box { display: flex; align-items: center; background: #fff; border-radius: 36rpx; padding: 0 24rpx; height: 72rpx; gap: 12rpx; }
.search-input { flex: 1; font-size: 28rpx; color: #1D2129; height: 72rpx; }
.search-placeholder { color: #86909C; font-size: 28rpx; }
.clear-btn { flex-shrink: 0; padding: 8rpx; display: flex; align-items: center; }

.list-scroll { padding: 20rpx 0; }
.card-list { display: flex; flex-direction: column; gap: 20rpx; }

.operation-card { background: #fff; border-radius: 16rpx; padding: 28rpx; box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.04); }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20rpx; }
.type-badge { background: #E8F0FE; color: #3D6DF7; padding: 8rpx 20rpx; border-radius: 8rpx; font-size: 26rpx; font-weight: 500; }
.time-text { font-size: 22rpx; color: #C9CDD4; }

.card-body { padding: 20rpx 0; border-top: 1rpx solid #F2F3F5; }
.info-row { display: flex; margin-bottom: 12rpx; &:last-child { margin-bottom: 0; } }
.info-item { flex: 1; display: flex; align-items: center; gap: 12rpx;
  &.full { flex: none; width: 100%; }
  .label { font-size: 24rpx; color: #86909C; min-width: 80rpx; }
  .value { font-size: 26rpx; color: #1D2129;
    &.content-text { color: #4E5969; }
  }
}
</style>
