<template>
  <view class="about-container">
    <view class="header-section uni-text-center">
      <image style="width: 150rpx; height: 150rpx" src="/static/logo.png" mode="widthFix" />
      <view class="app-name">馥辰国际</view>
    </view>

    <view class="content-section">
      <view class="menu-list">
        <view class="list-cell">
          <view class="menu-item-box">
            <view>版本信息</view>
            <view class="text-right">v{{ version }}</view>
          </view>
        </view>
        <view class="list-cell">
          <view class="menu-item-box">
            <view>官方邮箱</view>
            <view class="text-right">contact@fuchenpro.com</view>
          </view>
        </view>
        <view class="list-cell">
          <view class="menu-item-box">
            <view>公司网站</view>
            <view class="text-right uni-text-blue">{{ url }}</view>
          </view>
        </view>
      </view>
    </view>

    <view class="copyright">
      <view>Copyright &copy; 2025 fuchenpro.com All Rights Reserved.</view>
    </view>
  </view>
</template>

<script setup>
/**
 * @description 关于我们页 - 应用信息展示
 * @description 展示应用Logo、名称、版本号、官方邮箱和公司网站
 */
import { ref } from 'vue'

/** 公司网站地址，从全局配置获取 */
const url = ref(getApp().globalData.config.appInfo.site_url)
/** 应用版本号，从全局配置获取 */
const version = ref(getApp().globalData.config.appInfo.version)
</script>

<style lang="scss" scoped>
page {
  background-color: #f8f8f8;
}

.copyright {
  margin-top: 50rpx;
  text-align: center;
  line-height: 60rpx;
  color: #999;
}

.header-section {
  display: flex;
  padding: 30rpx 0 0;
  flex-direction: column;
  align-items: center;
}

.app-name {
  font-size: 20px;
  font-weight: bold;
  margin-top: 10px;
}
</style>
