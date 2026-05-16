<template>
  <view class="store-container">
    <view class="search-section">
      <view class="search-box">
        <u-icon name="search" size="16" color="#86909C"></u-icon>
        <input
          class="search-input"
          type="text"
          v-model="queryParams.keyword"
          placeholder="搜索门店名称/负责人/电话"
          placeholder-class="search-placeholder"
          confirm-type="search"
          @input="onSearchInput"
          @confirm="handleSearch"
        />
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
          <view v-if="queryParams.enterpriseId" class="filter-tag active" @click="clearFilter('enterpriseId')">
            <text>{{ getEnterpriseName(queryParams.enterpriseId) }}</text>
            <u-icon name="close" size="12"></u-icon>
          </view>
          <view v-if="queryParams.status !== '' && queryParams.status !== undefined" class="filter-tag active" @click="clearFilter('status')">
            <text>{{ queryParams.status === '0' ? '正常' : '停用' }}</text>
            <u-icon name="close" size="12"></u-icon>
          </view>
        </view>
      </scroll-view>
    </view>

    <u-popup :show="showFilter" mode="top" round="16" @close="toggleFilter">
      <view class="popup-content">
        <view class="popup-title">筛选条件</view>
        <view class="form-item">
          <view class="form-label">所属企业</view>
          <view class="form-options">
            <view
              v-for="item in enterpriseOptions"
              :key="item.enterpriseId"
              class="option-tag"
              :class="{ active: queryParams.enterpriseId === item.enterpriseId }"
              @click="queryParams.enterpriseId = queryParams.enterpriseId === item.enterpriseId ? '' : item.enterpriseId"
            >{{ item.enterpriseName }}</view>
          </view>
        </view>
        <view class="form-item">
          <view class="form-label">状态</view>
          <view class="form-options">
            <view class="option-tag" :class="{ active: queryParams.status === '0' }" @click="queryParams.status = queryParams.status === '0' ? '' : '0'">正常</view>
            <view class="option-tag" :class="{ active: queryParams.status === '1' }" @click="queryParams.status = queryParams.status === '1' ? '' : '1'">停用</view>
          </view>
        </view>
        <view class="popup-actions">
          <u-button type="info" plain text="重置" @click="resetFilter"></u-button>
          <u-button type="primary" text="确定" @click="confirmFilter"></u-button>
        </view>
      </view>
    </u-popup>

    <scroll-view
      scroll-y
      class="list-scroll"
      :style="{ height: scrollHeight + 'px' }"
      @scrolltolower="loadMore"
      refresher-enabled
      :refresher-triggered="refreshing"
      @refresherrefresh="onPullDownRefresh"
    >
      <view v-if="storeList.length > 0" class="card-list">
        <view v-for="item in storeList" :key="item.storeId" class="store-card" @click="goDetail(item)">
          <view class="card-header">
            <view class="store-name">
              <u-icon name="shop" size="18" color="#FF6B35"></u-icon>
              <text class="name-text">{{ item.storeName }}</text>
            </view>
            <view class="status-tag" :class="item.status === '0' ? 'status-normal' : 'status-stop'">
              {{ item.status === '0' ? '正常' : '停用' }}
            </view>
          </view>
          <view class="card-body">
            <view class="info-row">
              <view class="info-item">
                <text class="label">企业</text>
                <text class="value">{{ item.enterpriseName || '-' }}</text>
              </view>
            </view>
            <view class="info-row">
              <view class="info-item">
                <text class="label">负责人</text>
                <text class="value">{{ item.managerName || '-' }}</text>
              </view>
              <view class="info-item">
                <text class="label">电话</text>
                <text class="value phone-text" @click.stop="callPhone(item.phone)">{{ item.phone || '-' }}</text>
              </view>
            </view>
            <view class="info-row">
              <view class="info-item">
                <text class="label">营业</text>
                <text class="value">{{ item.businessHours || '-' }}</text>
              </view>
              <view class="info-item">
                <text class="label">年业绩</text>
                <text class="value highlight">¥{{ item.annualPerformance || '0.00' }}</text>
              </view>
            </view>
            <view class="info-row" v-if="item.address">
              <view class="info-item full">
                <text class="label">地址</text>
                <text class="value">{{ item.address }}</text>
              </view>
            </view>
          </view>
          <view class="card-footer">
            <view class="time-text">{{ formatTime(item.createTime) }}</view>
            <view class="action-btns">
              <view class="action-btn edit" @click.stop="goEdit(item)">
                <u-icon name="edit-pen" size="14"></u-icon>
                <text>编辑</text>
              </view>
              <view class="action-btn delete" @click.stop="handleDelete(item)">
                <u-icon name="trash" size="14"></u-icon>
                <text>删除</text>
              </view>
            </view>
          </view>
        </view>
      </view>
      <u-empty v-else-if="!loading" mode="data" text="暂无门店数据" :marginTop="100"></u-empty>
      <u-loadmore :status="loadStatus" :loading-text="'加载中...'" :loadmore-text="'上拉加载更多'" :nomore-text="'没有更多了'" :marginTop="20" />
    </scroll-view>

    <view class="fab-btn" @click="goAdd">
      <u-icon name="plus" size="24" color="#fff"></u-icon>
    </view>
  </view>
</template>

<script setup>
/**
 * @description 门店列表页 - 门店管理入口
 * @description 展示门店列表，支持关键词搜索（门店名/负责人/电话）、按所属企业/状态筛选、
 * 分页加载、下拉刷新、拨打电话、跳转新增/编辑/详情、删除门店
 */
import { ref, reactive, onMounted, computed } from 'vue'
import { listStore, delStore } from '@/api/business/store'
import { listEnterprise } from '@/api/business/enterprise'

const storeList = ref([])
const loading = ref(false)
const refreshing = ref(false)
const loadStatus = ref('loadmore')
const showFilter = ref(false)
const scrollHeight = ref(600)
const enterpriseOptions = ref([])

let searchTimer = null

const hasActiveFilters = computed(() => {
  return queryParams.enterpriseId || (queryParams.status !== '' && queryParams.status !== undefined)
})

const queryParams = reactive({
  pageNum: 1,
  pageSize: 10,
  keyword: '',
  storeName: '',
  managerName: '',
  phone: '',
  enterpriseId: '',
  status: ''
})

/** 根据企业ID查找企业名称 */
function getEnterpriseName(id) {
  const item = enterpriseOptions.value.find(e => e.enterpriseId === id)
  return item ? item.enterpriseName : ''
}

function formatTime(time) {
  if (!time) return ''
  return time.substring(0, 10)
}

async function loadEnterpriseOptions() {
  try {
    const response = await listEnterprise({ pageNum: 1, pageSize: 1000, status: '0' })
    const data = response.data || response
    enterpriseOptions.value = data.rows || []
  } catch (e) {
    console.error('加载企业列表失败:', e)
  }
}

/** 加载门店列表，支持分页和关键词搜索（搜索时同时匹配门店名/负责人/电话），isRefresh为true时重置到第一页 */
async function getList(isRefresh = false) {
  if (loading.value) return
  loading.value = true
  if (isRefresh) { queryParams.pageNum = 1; loadStatus.value = 'loadmore' }

  try {
    const params = { ...queryParams }
    if (params.keyword) { params.storeName = params.keyword; params.managerName = params.keyword; params.phone = params.keyword }
    delete params.keyword

    const response = await listStore(params)
    const data = response.data || response
    const list = data.rows || []
    const total = data.total || 0

    storeList.value = isRefresh ? list : [...storeList.value, ...list]
    loadStatus.value = storeList.value.length >= total ? 'nomore' : 'loadmore'
  } catch (e) {
    console.error('获取门店列表失败:', e)
    loadStatus.value = 'error'
  } finally {
    loading.value = false
    refreshing.value = false
  }
}

function loadMore() {
  if (loading.value || loadStatus.value === 'nomore') return
  loadStatus.value = 'loading'
  queryParams.pageNum++
  getList()
}

function onPullDownRefresh() { refreshing.value = true; getList(true) }

function handleSearch() { getList(true) }

function onSearchInput() {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => handleSearch(), 500)
}

function clearKeyword() { queryParams.keyword = ''; handleSearch() }

function toggleFilter() { showFilter.value = !showFilter.value }

function resetFilter() { queryParams.enterpriseId = ''; queryParams.status = '' }

function confirmFilter() { showFilter.value = false; getList(true) }

function clearFilter(field) { queryParams[field] = ''; getList(true) }

/** 跳转门店详情页（查看模式） */
function goDetail(item) {
  uni.navigateTo({ url: `/pages/business/store/form?id=${item.storeId}&mode=view` })
}

/** 跳转门店编辑页 */
function goEdit(item) {
  uni.navigateTo({ url: `/pages/business/store/form?id=${item.storeId}&mode=edit` })
}

/** 跳转新增门店页 */
function goAdd() {
  uni.navigateTo({ url: '/pages/business/store/form?mode=add' })
}

function callPhone(phone) {
  if (!phone) return
  uni.makePhoneCall({ phoneNumber: phone })
}

/** 删除门店，弹出确认框后调用删除接口，成功后刷新列表 */
function handleDelete(item) {
  uni.showModal({
    title: '提示',
    content: `是否确认删除"${item.storeName}"?`,
    success: async (res) => {
      if (res.confirm) {
        try {
          await delStore(item.storeId)
          uni.showToast({ title: '删除成功', icon: 'success' })
          getList(true)
        } catch (e) { console.error('删除失败:', e) }
      }
    }
  })
}

function calcScrollHeight() {
  const systemInfo = uni.getSystemInfoSync()
  scrollHeight.value = systemInfo.windowHeight - 180
}

onMounted(() => {
  calcScrollHeight()
  loadEnterpriseOptions()
  getList(true)
})
</script>

<style lang="scss" scoped>
page { background-color: #F5F7FA; }

.store-container { min-height: 100vh; padding: 0 24rpx; padding-bottom: 120rpx; }

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
.popup-actions { display: flex; gap: 20rpx; margin-top: 40rpx; padding-top: 30rpx; border-top: 1rpx solid #E5E6EB;
  .u-button { flex: 1; }
}

.list-scroll { padding: 20rpx 0; }
.card-list { display: flex; flex-direction: column; gap: 20rpx; }

.store-card { background: #fff; border-radius: 16rpx; padding: 28rpx; box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.04);
  &:active { transform: scale(0.98); opacity: 0.9; }
}

.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20rpx; }
.store-name { display: flex; align-items: center; gap: 12rpx;
  .name-text { font-size: 30rpx; font-weight: 600; color: #1D2129; }
}

.status-tag { padding: 6rpx 16rpx; border-radius: 6rpx; font-size: 22rpx; font-weight: 500;
  &.status-normal { background: #E8FFEA; color: #00B42A; }
  &.status-stop { background: #FFF1F0; color: #F53F3F; }
}

.card-body { padding: 20rpx 0; border-top: 1rpx solid #F2F3F5; border-bottom: 1rpx solid #F2F3F5; }
.info-row { display: flex; margin-bottom: 16rpx; &:last-child { margin-bottom: 0; } }
.info-item { flex: 1; display: flex; align-items: center; gap: 12rpx;
  &.full { flex: none; width: 100%; }
  .label { font-size: 24rpx; color: #86909C; min-width: 60rpx; }
  .value { font-size: 26rpx; color: #1D2129;
    &.phone-text { color: #3D6DF7; }
    &.highlight { color: #FF6B35; font-weight: 500; }
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
