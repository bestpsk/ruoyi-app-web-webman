<template>
  <view class="home-page">
    <HeaderNav />

    <scroll-view
      scroll-y
      class="main-content"
      :style="{ height: scrollHeight }"
      @refresherrefresh="onPullDownRefresh"
      :refresher-enabled="true"
      :refresher-triggered="isRefreshing"
    >
      <NoticeBar />

      <StatisticsCard :data="combinedStats" />

      <OrderList :list="orderList" />

      <view class="bottom-spacer"></view>
    </scroll-view>
  </view>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import HeaderNav from '@/components/home/HeaderNav.vue'
import NoticeBar from '@/components/home/NoticeBar.vue'
import StatisticsCard from '@/components/home/StatisticsCard.vue'
import OrderList from '@/components/home/OrderList.vue'
import { listSalesOrder } from '@/api/business/salesOrder'
import { listArchive } from '@/api/business/archive'
import { useUserStore } from '@/store/modules/user'

const isRefreshing = ref(false)
const combinedStats = ref([])
const orderList = ref([])

const userStore = useUserStore()

const scrollHeight = computed(() => {
  const systemInfo = uni.getSystemInfoSync()
  return `${systemInfo.windowHeight}px`
})

onMounted(() => {
  loadHomeData()
})

async function loadHomeData() {
  try {
    combinedStats.value = [
      { label: '咨询客数', todayValue: '128', monthValue: '1,256' },
      { label: '成交客数', todayValue: '36', monthValue: '386' },
      { label: '成交金额', todayValue: '¥12.8k', monthValue: '¥128.5k' },
      { label: '成交项次', todayValue: '89', monthValue: '892' }
    ]

    const archiveRes = await listArchive({
      operatorUserId: userStore.getId,
      pageNum: 1,
      pageSize: 5,
      orderByColumn: 'archive_date',
      isAsc: 'desc'
    })
    orderList.value = (archiveRes.rows || []).map(item => ({
      id: item.archiveId || item.archive_id,
      name: item.customerName || item.customer_name || '',
      store: [item.enterpriseName || item.enterprise_name, item.storeName || item.store_name].filter(Boolean).join('·'),
      avatar: '/static/images/profile.jpg',
      amount: Number(item.amount || 0).toFixed(2),
      sourceType: item.sourceType || item.source_type,
      sourceId: item.sourceId || item.source_id,
      status: getSourceTypeLabel(item.sourceType || item.source_type),
      createTime: item.archiveDate || item.archive_date || item.createTime
    }))
  } catch (error) {
    console.error('加载首页数据失败:', error)
    uni.showToast({ title: '数据加载失败', icon: 'none' })
  }
}

function onPullDownRefresh() {
  isRefreshing.value = true

  setTimeout(() => {
    loadHomeData()
    isRefreshing.value = false
    uni.stopPullDownRefresh()
  }, 800)
}

function getSourceTypeLabel(type) {
  const map = { '0': '开单', '1': '操作', '2': '还款', '3': '手动' }
  return map[type] || (type || '未知')
}
</script>

<style lang="scss" scoped>
.home-page {
  min-height: 100vh;
  background: #F5F7FA;
}

.main-content {
  padding-top: 20rpx;
}

.bottom-spacer {
  height: 40rpx;
}
</style>
