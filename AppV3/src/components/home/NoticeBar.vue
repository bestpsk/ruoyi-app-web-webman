<template>
  <view class="notice-card" v-if="noticeList.length > 0">
    <view class="notice-icon">
      <u-icon name="volume" size="18" color="#FFFFFF" />
    </view>
    <swiper class="notice-swiper" vertical autoplay circular interval="3000" duration="500">
      <swiper-item v-for="(item, index) in noticeList" :key="index">
        <view class="notice-item">
          <text class="notice-type">[{{ item.type }}]</text>
          <text class="notice-content">{{ item.content }}</text>
        </view>
      </swiper-item>
    </swiper>
    <view class="notice-more" @click="handleMoreNotice">
      <u-icon name="arrow-right" size="14" color="#86909C" />
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'

const noticeList = ref([
  { type: '通知', content: '您有一条新的订单待处理，请及时查看' },
  { type: '公告', content: '系统将于今晚22:00进行维护升级，请提前保存数据' },
  { type: '提醒', content: '今日有3位顾客预约，请提前做好准备' }
])

function handleMoreNotice() {
  uni.showToast({ title: '查看更多消息', icon: 'none' })
}
</script>

<style lang="scss" scoped>
.notice-card {
  margin: 8rpx 24rpx 0;
  background: #fff;
  border-radius: 20rpx;
  padding: 18rpx 24rpx;
  box-shadow: 0 2rpx 12rpx rgba(61, 109, 247, 0.06);
  display: flex;
  align-items: center;
  gap: 14rpx;
}

.notice-icon {
  width: 44rpx;
  height: 44rpx;
  border-radius: 50%;
  background: #3D6DF7;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.notice-swiper {
  flex: 1;
  height: 44rpx;
}

.notice-item {
  display: flex;
  align-items: center;
  gap: 8rpx;
  height: 44rpx;
  line-height: 44rpx;
}

.notice-type {
  font-size: 23rpx;
  color: #3D6DF7;
  font-weight: 500;
  flex-shrink: 0;
}

.notice-content {
  font-size: 23rpx;
  color: #4E5969;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.notice-more {
  width: 44rpx;
  height: 44rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;

  &:active {
    opacity: 0.6;
  }
}
</style>
