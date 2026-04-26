<template>
  <view class="work-container">
    <view class="swiper-section">
      <swiper class="swiper" :indicator-dots="true" :autoplay="true" :interval="3000" :duration="500" circular>
        <swiper-item v-for="(item, index) in bannerList" :key="index">
          <image :src="item.image" mode="aspectFill" class="banner-img" @click="clickBannerItem(item)" />
        </swiper-item>
      </swiper>
    </view>

    <view class="section-title">
      <text>系统管理</text>
    </view>

    <view class="grid-body">
      <view class="grid-row">
        <view v-for="(item, index) in gridList" :key="index" class="grid-item" @click="handleGridClick(item)">
          <view class="icon-wrapper">
            <u-icon :name="item.icon" size="22" color="#fff" />
          </view>
          <text class="grid-text">{{ item.title }}</text>
        </view>
      </view>
    </view>

    <view class="section-title">
      <text>常用功能</text>
    </view>

    <view class="quick-actions">
      <view class="action-card" @click="goToPage('/pages/mine/info/index')">
        <view class="quick-icon"><u-icon name="account-fill" size="20" color="#3c96f3" /></view>
        <text class="action-label">个人信息</text>
      </view>
      <view class="action-card" @click="goToPage('/pages/mine/pwd/index')">
        <view class="quick-icon"><u-icon name="lock-fill" size="20" color="#3c96f3" /></view>
        <text class="action-label">修改密码</text>
      </view>
      <view class="action-card" @click="goToPage('/pages/mine/setting/index')">
        <view class="quick-icon"><u-icon name="setting" size="20" color="#3c96f3" /></view>
        <text class="action-label">应用设置</text>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'

const bannerList = ref([
  { image: '/static/images/banner/banner01.jpg' },
  { image: '/static/images/banner/banner02.jpg' },
  { image: '/static/images/banner/banner03.jpg' }
])

const gridList = ref([
  { icon: 'account', title: '用户管理', path: '' },
  { icon: 'man-add', title: '角色管理', path: '' },
  { icon: 'list', title: '菜单管理', path: '' },
  { icon: 'home', title: '部门管理', path: '' },
  { icon: 'bookmark', title: '岗位管理', path: '' },
  { icon: 'file-text', title: '字典管理', path: '' },
  { icon: 'setting', title: '参数设置', path: '' },
  { icon: 'chat', title: '通知公告', path: '' }
])

function clickBannerItem(item) {
  console.info('Banner clicked:', item)
}

function handleGridClick(item) {
  if (item.path) {
    uni.navigateTo({ url: item.path })
  } else {
    uni.showToast({ title: `${item.title}模块建设中~`, icon: 'none' })
  }
}

function goToPage(url) {
  uni.navigateTo({ url })
}
</script>

<style lang="scss" scoped>
page {
  background-color: #f5f7fa;
}

.work-container {
  min-height: 100vh;
  padding-bottom: 30rpx;
}

.swiper-section {
  margin-bottom: 20rpx;
}

.swiper {
  height: 320rpx;
}

.banner-img {
  width: 100%;
  height: 100%;
}

.section-title {
  padding: 24rpx 30rpx 16rpx;

  text {
    font-size: 28rpx;
    font-weight: 600;
    color: #333;
  }
}

.grid-body {
  margin: 0 20rpx;
  background-color: #fff;
  border-radius: 12rpx;
  padding: 30rpx 10rpx;
  box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.04);
}

.grid-row {
  display: flex;
  flex-wrap: wrap;
}

.grid-item {
  width: 25%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20rpx 0;
}

.icon-wrapper {
  width: 90rpx;
  height: 90rpx;
  border-radius: 50%;
  background-color: #3c96f3;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 14rpx;

  &:active {
    opacity: 0.8;
    transform: scale(0.95);
  }
}

.grid-text {
  font-size: 24rpx;
  color: #333;
  text-align: center;
}

.quick-actions {
  margin: 20rpx;
  display: flex;
  gap: 20rpx;
}

.action-card {
  flex: 1;
  background-color: #fff;
  border-radius: 12rpx;
  padding: 32rpx 20rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14rpx;
  box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.04);

  &:active {
    opacity: 0.7;
  }
}

.quick-icon {
  width: 64rpx;
  height: 64rpx;
  border-radius: 50%;
  background-color: #e8f2ff;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-label {
  font-size: 24rpx;
  color: #333;
}
</style>
