<template>
  <view class="mine-container">
    <view class="header-section">
      <view class="header-content">
        <view class="user-info-area">
          <view v-if="!avatar" class="avatar-placeholder">
            <text class="avatar-text">U</text>
          </view>
          <image v-if="avatar" @click="handleToAvatar" :src="avatar" class="avatar-img" mode="aspectFill" />
          <view v-if="!name" @click="handleToLogin" class="login-tip">点击登录</view>
          <view v-if="name" @click="handleToInfo" class="username">用户名：{{ name }}</view>
        </view>
        <view @click="handleToInfo" class="profile-link">
          <text>个人信息 ></text>
        </view>
      </view>
    </view>

    <view class="content-section">
      <view class="quick-actions">
        <view class="action-item" @click="handleBuilding">
          <view class="action-icon"><u-icon name="chat" size="20" color="#666" ></u-icon></view>
          <text class="action-label">在线客服</text>
        </view>
        <view class="action-item" @click="handleBuilding">
          <view class="action-icon"><u-icon name="edit-pen" size="20" color="#666" ></u-icon></view>
          <text class="action-label">反馈社区</text>
        </view>
        <view class="action-item" @click="handleBuilding">
          <view class="action-icon"><u-icon name="thumb-up" size="20" color="#666" ></u-icon></view>
          <text class="action-label">点赞我们</text>
        </view>
        <view class="action-item" @click="handleAbout">
          <view class="action-icon"><u-icon name="info-circle" size="20" color="#666" ></u-icon></view>
          <text class="action-label">关于我们</text>
        </view>
      </view>

      <view class="menu-list">
        <view class="menu-item" @click="handleToEditInfo">
          <view class="menu-icon"><u-icon name="edit-pen" size="16" color="#3c96f3" ></u-icon></view>
          <text class="menu-text">编辑资料</text>
          <text class="menu-arrow">></text>
        </view>
        <view class="menu-item" @click="handleHelp">
          <view class="menu-icon"><u-icon name="question-circle" size="16" color="#3c96f3" ></u-icon></view>
          <text class="menu-text">常见问题</text>
          <text class="menu-arrow">></text>
        </view>
        <view class="menu-item" @click="handleAbout">
          <view class="menu-icon"><u-icon name="info-circle" size="16" color="#3c96f3" ></u-icon></view>
          <text class="menu-text">关于我们</text>
          <text class="menu-arrow">></text>
        </view>
        <view class="menu-item" @click="handleToSetting">
          <view class="menu-icon"><u-icon name="setting" size="16" color="#3c96f3" ></u-icon></view>
          <text class="menu-text">应用设置</text>
          <text class="menu-arrow">></text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { computed } from 'vue'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()
const name = computed(() => userStore.name)
const avatar = computed(() => userStore.avatar)

function handleToInfo() {
  uni.navigateTo({ url: '/pages/mine/info/index' })
}
function handleToEditInfo() {
  uni.navigateTo({ url: '/pages/mine/info/edit' })
}
function handleToSetting() {
  uni.navigateTo({ url: '/pages/mine/setting/index' })
}
function handleToLogin() {
  uni.reLaunch({ url: '/pages/login' })
}
function handleToAvatar() {
  uni.navigateTo({ url: '/pages/mine/avatar/index' })
}
function handleHelp() {
  uni.navigateTo({ url: '/pages/mine/help/index' })
}
function handleAbout() {
  uni.navigateTo({ url: '/pages/mine/about/index' })
}
function handleBuilding() {
  uni.showToast({ title: '模块建设中~', icon: 'none' })
}
</script>

<style lang="scss" scoped>
page {
  background-color: #f5f7fa;
}

.mine-container {
  min-height: 100vh;
}

.header-section {
  background-color: #3c96f3;
  padding: 60rpx 30rpx 80rpx;
  color: white;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.user-info-area {
  display: flex;
  align-items: center;
}

.avatar-placeholder, .avatar-img {
  width: 120rpx;
  height: 120rpx;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.avatar-text {
  font-size: 44rpx;
  font-weight: 600;
  color: #fff;
}

.login-tip {
  font-size: 34rpx;
  margin-left: 24rpx;
}

.username {
  font-size: 32rpx;
  margin-left: 24rpx;
  font-weight: 500;
}

.profile-link {
  font-size: 26rpx;
  opacity: 0.9;
}

.content-section {
  position: relative;
  margin-top: -40px;
  padding: 0 24rpx;
}

.quick-actions {
  background-color: #fff;
  border-radius: 12rpx;
  padding: 36rpx 20rpx;
  display: flex;
  justify-content: space-around;
  box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.04);
  margin-bottom: 24rpx;
}

.action-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14rpx;
}

.action-icon {
  width: 80rpx;
  height: 80rpx;
  border-radius: 50%;
  background-color: #f5f5f5;
  display: flex;
  align-items: center;
  justify-content: center;

  text {
    font-size: 32rpx;
    font-weight: 600;
    color: #666;
  }
}

.action-label {
  font-size: 24rpx;
  color: #333;
}

.menu-list {
  background-color: #fff;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 32rpx 28rpx;
  border-bottom: 1rpx solid #f5f5f5;

  &:last-child {
    border-bottom: none;
  }

  &:active {
    background-color: #f9f9f9;
  }
}

.menu-icon {
  width: 56rpx;
  height: 56rpx;
  border-radius: 50%;
  background-color: #e8f2ff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20rpx;

  text {
    font-size: 24rpx;
    font-weight: 600;
    color: #3c96f3;
  }
}

.menu-text {
  flex: 1;
  font-size: 30rpx;
  color: #333;
}

.menu-arrow {
  font-size: 28rpx;
  color: #ccc;
}
</style>
