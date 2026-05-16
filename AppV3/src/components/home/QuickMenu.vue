<template>
  <view class="quick-menu">
    <view class="menu-grid">
      <view
        v-for="(item, index) in menuList"
        :key="index"
        class="menu-item"
        @click="handleMenuClick(item)"
      >
        <view class="icon-wrapper">
          <u-icon :name="item.icon" size="20" color="#3D6DF7" />
        </view>
        <text class="menu-text">{{ item.title }}</text>
      </view>
      <!-- 更多按钮改为图标+文字样式 -->
      <view class="menu-item" @click="handleMoreMenu">
        <view class="icon-wrapper">
          <u-icon name="apps" size="20" color="#3D6DF7" />
        </view>
        <text class="menu-text">更多</text>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * @description 首页快捷菜单组件 - 常用功能入口
 * @description 展示打卡、开单、行程、订单四个快捷入口，
 * 已配置路径的跳转对应页面，未配置的显示开发中提示
 */
import { ref } from 'vue'

const menuList = ref([
  { icon: 'clock', title: '打卡', path: '/pages/attendance/index' },
  { icon: 'file-text', title: '开单', path: '' },
  { icon: 'calendar', title: '行程', path: '' },
  { icon: 'list', title: '订单', path: '/pages/work/index' }
])

/** 菜单项点击处理，已配置路径的跳转对应页面，未配置的显示开发中提示 */
function handleMenuClick(item) {
  if (item.path) {
    uni.navigateTo({ url: item.path })
  } else {
    uni.showToast({
      title: `${item.title}功能开发中`,
      icon: 'none'
    })
  }
}

/** "更多"按钮，切换到工作台Tab页 */
function handleMoreMenu() {
  uni.switchTab({ url: '/pages/work/index' })
}
</script>

<style lang="scss" scoped>
.quick-menu {
  background: #fff;
  border-radius: 20rpx;
  padding: 28rpx 20rpx 20rpx;
  box-shadow: 0 2rpx 12rpx rgba(61, 109, 247, 0.06);
  opacity: 0.9;
}

.menu-grid {
  display: flex;
  justify-content: space-between;
}

.menu-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12rpx;

  &:active {
    transform: scale(0.95);
    opacity: 0.8;
  }

  .icon-wrapper {
    width: 88rpx;
    height: 88rpx;
    border-radius: 50%;
    background: #E8F0FE;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;

    &:active {
      background: #d4e8ff;
    }
  }

  .menu-text {
    font-size: 24rpx;
    color: #1D2129;
    font-weight: 500;
  }
}
</style>
