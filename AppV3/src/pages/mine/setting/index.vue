<template>
  <view class="setting-container" :style="{ height: `${windowHeight}px` }">
    <view class="menu-list">
      <view class="list-cell list-cell-arrow" @click="handleToPwd">
        <view class="menu-item-box">
          <u-icon name="lock" size="18" color="#3c96f3" style="margin-right: 10px" />
          <view>修改密码</view>
        </view>
      </view>
      <view class="list-cell list-cell-arrow" @click="handleToUpgrade">
        <view class="menu-item-box">
          <u-icon name="reload" size="18" color="#3c96f3" style="margin-right: 10px" />
          <view>检查更新</view>
        </view>
      </view>
      <view class="list-cell list-cell-arrow" @click="handleCleanTmp">
        <view class="menu-item-box">
          <u-icon name="trash" size="18" color="#3c96f3" style="margin-right: 10px" />
          <view>清理缓存</view>
        </view>
      </view>
    </view>
    <view class="item-box" @click="handleLogout">
      <text>退出登录</text>
    </view>
  </view>
</template>

<script setup>
/**
 * @description 应用设置页 - 用户偏好设置
 * @description 提供修改密码、检查更新、清理缓存和退出登录功能入口
 */
import { ref } from 'vue'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()
/** 页面高度，适配不同设备屏幕 */
const windowHeight = ref(uni.getSystemInfoSync().windowHeight)

/** 跳转修改密码页 */
function handleToPwd() {
  uni.navigateTo({ url: '/pages/mine/pwd/index' })
}
/** 检查更新（建设中） */
function handleToUpgrade() {
  uni.showToast({ title: '模块建设中~', icon: 'none' })
}
/** 清理缓存（建设中） */
function handleCleanTmp() {
  uni.showToast({ title: '模块建设中~', icon: 'none' })
}
/** 退出登录，弹出确认框后清除用户状态并跳转登录页 */
function handleLogout() {
  uni.showModal({
    title: '系统提示',
    content: '确定注销并退出系统吗？',
    success: function (res) {
      if (res.confirm) {
        userStore.logOut().finally(() => {
          uni.reLaunch({ url: '/pages/login' })
        })
      }
    }
  })
}
</script>

<style lang="scss" scoped>
page {
  background-color: #f8f8f8;
}

.item-box {
  background-color: #ffffff;
  margin: 30rpx;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 10rpx;
  border-radius: 8rpx;
  color: #303133;
  font-size: 32rpx;
}
</style>
