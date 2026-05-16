<template>
  <view class="header-section">
    <view class="header-nav" :style="{ paddingTop: statusBarHeight + 'px' }">
      <view class="nav-content">
        <view class="nav-left" @click="handleUserInfo">
          <u-avatar :src="userInfo.avatar" size="80" />
          <view class="user-info">
            <view class="user-name">{{ userInfo.name || '用户' }}</view>
            <view class="user-role">{{ userInfo.role || '美容顾问' }}</view>
          </view>
        </view>

        <view class="nav-right">
          <view class="icon-btn" @click="handleMessage">
            <u-icon name="bell" size="24" color="#fff" />
            <u-badge v-if="messageCount > 0" :value="messageCount" :absolute="true" type="error"></u-badge>
          </view>
          <view class="icon-btn" @click="handleSetting">
            <u-icon name="setting" size="24" color="#fff" />
          </view>
        </view>
      </view>

      <view class="welcome-text">
        <text>{{ greeting }}，开启美好的一天！</text>
      </view>
    </view>

    <!-- QuickMenu 嵌入 HeaderNav 底部，消除缝隙 -->
    <view class="quick-menu-wrapper">
      <QuickMenu />
    </view>
  </view>
</template>

<script setup>
/**
 * @description 首页头部导航组件 - 用户信息与快捷操作
 * @description 展示用户头像、昵称、问候语，提供个人信息、消息中心、设置三个快捷入口
 */
import { ref, computed } from 'vue'
import { useUserStore } from '@/store/modules/user'
import QuickMenu from './QuickMenu.vue'

const userStore = useUserStore()

const statusBarHeight = ref(44)
const messageCount = ref(3)

/** 用户信息：头像（默认占位图）、昵称（默认"用户"）、角色 */
const userInfo = computed(() => ({
  avatar: userStore.getAvatar || '/static/images/profile.jpg',
  name: userStore.getNickName || userStore.getName || '用户',
  role: '美容顾问'
}))

/** 根据当前时间自动生成问候语（早上好/下午好/晚上好） */
const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return '早上好'
  if (hour < 18) return '下午好'
  return '晚上好'
})

uni.getSystemInfoSync({
  success: (res) => {
    statusBarHeight.value = res.statusBarHeight
  }
})

/** 跳转个人信息页 */
function handleUserInfo() {
  uni.navigateTo({ url: '/pages/mine/info/index' })
}

/** 消息中心（建设中） */
function handleMessage() {
  uni.showToast({ title: '消息中心开发中', icon: 'none' })
}

/** 跳转应用设置页 */
function handleSetting() {
  uni.navigateTo({ url: '/pages/mine/setting/index' })
}
</script>

<style lang="scss" scoped>
.header-section {
  background: linear-gradient(180deg, #5B8FF9 0%, #3D6DF7 100%);
  padding-bottom: 12rpx;
}

.header-nav {
  padding: 20rpx 30rpx 24rpx;
  color: #fff;
}

.nav-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 20rpx;

  .user-info {
    display: flex;
    flex-direction: column;
    gap: 6rpx;

    .user-name {
      font-size: 32rpx;
      font-weight: 600;
    }

    .user-role {
      font-size: 24rpx;
      opacity: 0.9;
    }
  }
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 28rpx;

  .icon-btn {
    position: relative;
    width: 64rpx;
    height: 64rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible;

    :deep(.u-badge) {
      top: -4rpx !important;
      right: -4rpx !important;
    }

    &:active {
      opacity: 0.7;
      transform: scale(0.95);
    }
  }
}

.welcome-text {
  margin-top: 24rpx;
  font-size: 26rpx;
  opacity: 0.95;
}

.quick-menu-wrapper {
  margin: 0 24rpx;
  position: relative;
}
</style>
