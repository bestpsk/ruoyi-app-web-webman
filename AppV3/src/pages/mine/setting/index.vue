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
import { ref } from 'vue'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()
const windowHeight = ref(uni.getSystemInfoSync().windowHeight)

function handleToPwd() {
  uni.navigateTo({ url: '/pages/mine/pwd/index' })
}
function handleToUpgrade() {
  uni.showToast({ title: '模块建设中~', icon: 'none' })
}
function handleCleanTmp() {
  uni.showToast({ title: '模块建设中~', icon: 'none' })
}
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
