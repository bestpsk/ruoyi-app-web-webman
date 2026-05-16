<template>
  <view class="order-list">
    <view class="card-header">
      <text class="card-title">最近订单</text>
      <view class="more-btn" @click="handleMore">
        <text class="more-text">查看全部</text>
        <u-icon name="arrow-right" size="14" color="#86909C" />
      </view>
    </view>

    <scroll-view scroll-y class="order-scroll" :style="{ height: scrollHeight }">
      <view v-if="orderList.length > 0" class="list-content">
        <view
          v-for="(item, index) in orderList"
          :key="index"
          class="order-item"
          @click="handleOrderClick(item)"
        >
          <view class="order-left">
            <u-avatar :src="item.avatar" size="64" />
            <view class="customer-info">
              <view class="customer-name">
                <text class="name-text">{{ item.name }}</text>
                <u-tag
                :text="item.status"
                size="small"
                :type="getStatusType(item.status)"
                style="margin-left: 8rpx; vertical-align: middle; font-size:x-small;  padding: 0 10rpx;"
              />
              </view>
              <view v-if="item.store" class="customer-store-row">
                <u-icon name="home" size="12" color="#86909C" style="margin-right: 4rpx;" />
                <text class="customer-store">{{ item.store }}</text>
              </view>
            </view>
          </view>

          <view class="order-right">
            <view class="amount-info">
              <text class="amount-label">消费金额(元)</text>
              <text class="amount-value">{{ item.amount }}</text>
            </view>
            <view class="action-info">
              <text class="action-time">{{ formatTime(item.createTime) }}</text>
              <u-icon name="arrow-right" size="14" color="#C9CDD4" />
            </view>
          </view>
        </view>
      </view>

      <view v-else class="empty-state">
        <u-icon name="file-text" size="80" color="#e5e5e5" />
        <text class="empty-text">暂无订单数据</text>
      </view>
    </scroll-view>
  </view>
</template>

<script setup>
/**
 * @description 首页订单列表组件 - 最近订单展示
 * @description 展示最近订单列表，每项显示客户名、门店、金额、来源类型和创建时间，
 * 点击订单项根据来源类型跳转到订单/操作/还款详情页
 */
import { ref, computed } from 'vue'

const props = defineProps({
  /** 订单数据数组，每项包含id/name/store/amount/status/createTime/sourceType/sourceId */
  list: {
    type: Array,
    default: () => []
  }
})

/** 动态计算滚动区域高度，基于列表项数量，最大600rpx */
const scrollHeight = computed(() => {
  return `${Math.min(props.list.length * 160 + 40, 600)}rpx`
})

/** 订单列表，优先使用props传入数据 */
const orderList = computed(() => {
  if (props.list && props.list.length > 0) return props.list
  return []
})

/** 订单来源类型映射为标签颜色类型（开单-蓝/操作-绿/还款-橙/手动-灰） */
function getStatusType(status) {
  const statusMap = { '开单': 'primary', '操作': 'success', '还款': 'warning', '手动': 'info' }
  return statusMap[status] || 'info'
}

/** 格式化时间为MM-DD HH:mm简短格式 */
function formatTime(time) {
  if (!time) return ''
  const d = typeof time === 'string' ? new Date(time.replace(/-/g, '/')) : new Date(time)
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  const hour = String(d.getHours()).padStart(2, '0')
  const min = String(d.getMinutes()).padStart(2, '0')
  return `${month}-${day} ${hour}:${min}`
}

/** 点击订单项，根据来源类型（0-订单/1-操作/2-还款）跳转到对应详情页 */
function handleOrderClick(item) {
  if (!item.sourceId) return

  const sourceType = item.sourceType || item.source_type
  const typeMap = { '0': 'order', '1': 'operation', '2': 'repayment' }
  const type = typeMap[sourceType] || 'order'

  uni.navigateTo({
    url: `/pages/business/order/detail?id=${item.sourceId}&type=${type}`
  })
}

/** "查看全部"按钮，切换到工作台Tab页 */
function handleMore() {
  uni.switchTab({ url: '/pages/work/index' })
}
</script>

<style lang="scss" scoped>
.order-list {
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
  margin-bottom: 20rpx;

  .card-title {
    font-size: 30rpx;
    font-weight: 600;
    color: #1D2129;
  }

  .more-btn {
    display: flex;
    align-items: center;
    gap: 6rpx;

    &:active { opacity: 0.6; }

    .more-text { font-size: 24rpx; color: #86909C; }
  }
}

.order-scroll { border-radius: 12rpx; }

.list-content {
  display: flex;
  flex-direction: column;
  gap: 16rpx;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 22rpx;
  background: #F5F7FA;
  border-radius: 16rpx;

  &:active { background: #E8F0FE; }

  .order-left {
    display: flex;
    align-items: center;
    gap: 16rpx;
    flex: 1;

    .customer-info {
      display: flex;
      flex-direction: column;
      gap: 6rpx;

      .customer-name {
        font-size: 26rpx;
        font-weight: 500;
        color: #1D2129;
        display: flex;
        align-items: center;

        .name-text { line-height: 1; }
      }

      .customer-store-row {
        display: flex;
        align-items: center;
        margin-top: 4rpx;
      }

      .customer-store { font-size: 23rpx; color: #86909C; }
    }
  }

  .order-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4rpx;

    .amount-info {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 4rpx;

      .amount-label { font-size: 21rpx; color: #86909C; }
      .amount-value { font-size: 28rpx; font-weight: 600; color: #1D2129; }
    }

    .action-info {
      display: flex;
      align-items: center;
      gap: 4rpx;

      .action-time { font-size: 21rpx; color: #4E5969; }
    }
  }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80rpx 0;
  gap: 20rpx;

  .empty-text { font-size: 26rpx; color: #86909C; }
}
</style>
