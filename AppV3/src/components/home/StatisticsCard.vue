<template>
  <view class="statistics-card">
    <view class="card-header">
      <text class="card-title">数据概览</text>
      <view class="refresh-btn" @click="handleRefresh">
        <u-icon name="reload" size="16" color="#86909C" />
      </view>
    </view>

    <view class="divider"></view>

    <view class="stats-grid">
      <view
        v-for="(item, index) in statsList"
        :key="index"
        class="stat-item"
      >
        <text class="stat-label">{{ item.label }}</text>
        <text class="stat-value-today">{{ item.todayValue }}</text>
        <text class="stat-value-month">本月 {{ item.monthValue }}</text>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * @description 首页统计卡片组件 - 业务数据概览
 * @description 展示咨询客数、成交客数、成交金额、成交项次的今日/月度数据，
 * 支持通过props传入自定义数据，未传入时使用默认统计
 */
import { ref } from 'vue'

const props = defineProps({
  /** 统计数据数组，每项包含label/todayValue/monthValue */
  data: {
    type: Array,
    default: () => []
  }
})

const defaultStats = [
  { label: '咨询客数', todayValue: '128', monthValue: '1,256' },
  { label: '成交客数', todayValue: '36', monthValue: '386' },
  { label: '成交金额', todayValue: '¥12.8k', monthValue: '¥128.5k' },
  { label: '成交项次', todayValue: '89', monthValue: '892' }
])

const statsList = ref(props.data.length > 0 ? props.data : defaultStats)

/** 刷新统计数据（预留） */
function handleRefresh() {
  uni.showToast({ title: '数据已刷新', icon: 'none' })
}
</script>

<style lang="scss" scoped>
.statistics-card {
  margin: 16rpx 24rpx 0;
  background: #fff;
  border-radius: 20rpx;
  padding: 24rpx;
  box-shadow: 0 2rpx 12rpx rgba(61, 109, 247, 0.06);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;

  .card-title {
    font-size: 30rpx;
    font-weight: 600;
    color: #1D2129;
  }

  .refresh-btn {
    width: 52rpx;
    height: 52rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;

    &:active {
      background: #F5F7FA;
    }
  }
}

.divider {
  height: 1rpx;
  background: #E5E6EB;
  margin: 18rpx 0;
}

.stats-grid {
  display: flex;
  justify-content: space-between;
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8rpx;

  &:not(:last-child) {
    border-right: 1rpx solid #E5E6EB;
  }

  .stat-label {
    font-size: 23rpx;
    color: #86909C;
  }

  .stat-value-today {
    font-size: 40rpx;
    font-weight: 700;
    color: #3D6DF7;
    line-height: 1;
  }

  .stat-value-month {
    font-size: 23rpx;
    color: #86909C;
    font-weight: 400;
    line-height: 1.4;
  }
}
</style>
