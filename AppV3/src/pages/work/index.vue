<template>
  <view class="work-container">
    <view class="swiper-section">
      <swiper class="swiper" :indicator-dots="true" :autoplay="true" :interval="3000" :duration="500" circular>
        <swiper-item v-for="(item, index) in bannerList" :key="index">
          <image :src="item.image" mode="aspectFill" class="banner-img" @click="clickBannerItem(item)" />
        </swiper-item>
      </swiper>
    </view>

    <view class="search-card">
      <view class="search-box">
        <u-icon name="search" size="16" color="#86909C" />
        <input
          class="search-input"
          type="text"
          placeholder="搜索功能"
          placeholder-class="search-placeholder"
          v-model="searchKeyword"
          confirm-type="search"
        />
        <view v-if="searchKeyword" class="search-clear" @click="searchKeyword = ''">
          <u-icon name="close-circle-fill" size="14" color="#C9CDD4" />
        </view>
      </view>
    </view>

    <view class="quick-card">
      <view class="card-header">
        <text class="card-title">常用功能</text>
      </view>
      <view class="quick-grid">
        <view class="quick-item" @click="goToPage('/pages/mine/info/index')">
          <view class="quick-icon"><u-icon name="account-fill" size="18" color="#3D6DF7" /></view>
          <text class="quick-text">个人信息</text>
        </view>
        <view class="quick-item" @click="goToPage('/pages/mine/pwd/index')">
          <view class="quick-icon"><u-icon name="lock-fill" size="18" color="#3D6DF7" /></view>
          <text class="quick-text">修改密码</text>
        </view>
        <view class="quick-item" @click="goToPage('/pages/mine/setting/index')">
          <view class="quick-icon"><u-icon name="setting" size="18" color="#3D6DF7" /></view>
          <text class="quick-text">应用设置</text>
        </view>
        <view class="quick-item" @click="handleGridClick({ title: '日志查询', path: '' })">
          <view class="quick-icon"><u-icon name="file-text" size="18" color="#3D6DF7" /></view>
          <text class="quick-text">日志查询</text>
        </view>
      </view>
    </view>

    <view class="grid-card">
      <view class="card-header">
        <text class="card-title">业务管理</text>
      </view>
      <view class="divider"></view>
      <view v-if="filteredBusinessList.length > 0" class="grid-body">
        <view class="grid-row">
          <view v-for="(item, index) in filteredBusinessList" :key="'biz-' + index" class="grid-item" @click="handleGridClick(item)">
            <view class="icon-wrapper biz-icon">
              <u-icon :name="item.icon" size="22" color="#fff" />
            </view>
            <text class="grid-text">{{ item.title }}</text>
          </view>
        </view>
      </view>
      <view v-else class="empty-state">
        <u-icon name="search" size="40" color="#C9CDD4" />
        <text class="empty-text">未找到相关功能</text>
      </view>
    </view>

    <view class="grid-card">
      <view class="card-header">
        <text class="card-title">系统管理</text>
      </view>
      <view class="divider"></view>
      <view v-if="filteredGridList.length > 0" class="grid-body">
        <view class="grid-row">
          <view v-for="(item, index) in filteredGridList" :key="index" class="grid-item" @click="handleGridClick(item)">
            <view class="icon-wrapper">
              <u-icon :name="item.icon" size="22" color="#fff" />
            </view>
            <text class="grid-text">{{ item.title }}</text>
          </view>
        </view>
      </view>
      <view v-else class="empty-state">
        <u-icon name="search" size="40" color="#C9CDD4" />
        <text class="empty-text">未找到相关功能</text>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchKeyword = ref('')

const bannerList = ref([
  { image: '/static/images/banner/banner01.jpg' },
  { image: '/static/images/banner/banner02.jpg' },
  { image: '/static/images/banner/banner03.jpg' }
])

const businessList = ref([
  { icon: 'home-fill', title: '企业管理', path: '/pages/business/enterprise/index' },
  { icon: 'shop', title: '门店管理', path: '/pages/business/store/index' },
  { icon: 'calendar', title: '行程安排', path: '/pages/business/schedule/index' },
  { icon: 'edit-pen', title: '销售开单', path: '/pages/business/sales/index' },
  { icon: 'grid', title: '项目操作', path: '/pages/business/operation/index' },
  { icon: 'list', title: '订单管理', path: '/pages/business/order/index' }
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

const filteredBusinessList = computed(() => {
  if (!searchKeyword.value.trim()) return businessList.value
  const keyword = searchKeyword.value.trim().toLowerCase()
  return businessList.value.filter(item => item.title.toLowerCase().includes(keyword))
})

const filteredGridList = computed(() => {
  if (!searchKeyword.value.trim()) return gridList.value
  const keyword = searchKeyword.value.trim().toLowerCase()
  return gridList.value.filter(item => item.title.toLowerCase().includes(keyword))
})

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
  background-color: #F5F7FA;
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

.search-card {
  margin: 0 24rpx 20rpx;
  background: #fff;
  border-radius: 20rpx;
  padding: 20rpx 24rpx;
  box-shadow: 0 2rpx 12rpx rgba(61, 109, 247, 0.06);
}

.search-box {
  display: flex;
  align-items: center;
  background: #F5F7FA;
  border-radius: 36rpx;
  padding: 0 24rpx;
  height: 72rpx;
  gap: 16rpx;
}

.search-input {
  flex: 1;
  font-size: 26rpx;
  color: #1D2129;
  height: 72rpx;
}

.search-placeholder {
  color: #86909C;
  font-size: 26rpx;
}

.search-clear {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8rpx;
}

.quick-card {
  margin: 0 24rpx 20rpx;
  background: #fff;
  border-radius: 20rpx;
  padding: 24rpx;
  box-shadow: 0 2rpx 12rpx rgba(61, 109, 247, 0.06);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24rpx;
}

.card-title {
  font-size: 28rpx;
  font-weight: 600;
  color: #1D2129;
}

.quick-grid {
  display: flex;
  justify-content: space-between;
}

.quick-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12rpx;

  &:active {
    transform: scale(0.95);
    opacity: 0.8;
  }

  .quick-icon {
    width: 72rpx;
    height: 72rpx;
    border-radius: 50%;
    background: #E8F0FE;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
  }

  .quick-text {
    font-size: 22rpx;
    color: #1D2129;
    font-weight: 500;
  }
}

.grid-card {
  margin: 0 24rpx;
  background: #fff;
  border-radius: 20rpx;
  padding: 24rpx;
  box-shadow: 0 2rpx 12rpx rgba(61, 109, 247, 0.06);
}

.divider {
  height: 1rpx;
  background: #E5E6EB;
  margin-bottom: 20rpx;
}

.grid-body {
  padding: 10rpx 0;
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

  &:active {
    transform: scale(0.95);
    opacity: 0.8;
  }

  transition: all 0.2s ease;
}

.icon-wrapper {
  width: 90rpx;
  height: 90rpx;
  border-radius: 50%;
  background-color: #3D6DF7;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 14rpx;
  transition: all 0.2s ease;

  &.biz-icon {
    background-color: #FF6B35;
  }

  &:active {
    opacity: 0.8;
    transform: scale(0.95);
  }
}

.grid-text {
  font-size: 24rpx;
  color: #1D2129;
  text-align: center;
  font-weight: 500;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60rpx 0;
  gap: 20rpx;
}

.empty-text {
  font-size: 26rpx;
  color: #86909C;
}
</style>
