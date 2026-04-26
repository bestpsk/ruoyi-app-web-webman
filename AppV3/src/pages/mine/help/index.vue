<template>
  <view class="help-container">
    <view v-for="(item, findex) in list" :key="findex" class="list-title">
      <view class="text-title">{{ item.title }}</view>
      <view class="childList">
        <view v-for="(child, zindex) in item.childList" :key="zindex" class="question" @click="handleText(child)">
          <view class="text-item">{{ child.title }}</view>
          <view class="line" v-if="zindex !== item.childList.length - 1" />
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'

const list = ref([
  {
    title: '馥辰国际问题',
    childList: [
      { title: '馥辰国际开源吗？', content: '开源' },
      { title: '馥辰国际可以商用吗？', content: '可以' },
      { title: '馥辰国际官网地址多少？', content: 'https://fuchenpro.com' }
    ]
  },
  {
    title: '其他问题',
    childList: [
      { title: '如何退出登录？', content: '请点击[我的] - [应用设置] - [退出登录]即可退出登录' },
      { title: '如何修改用户头像？', content: '请点击[我的] - [选择头像] - [点击提交]即可更换用户头像' },
      { title: '如何修改登录密码？', content: '请点击[我的] - [应用设置] - [修改密码]即可修改登录密码' }
    ]
  }
])

function handleText(item) {
  uni.navigateTo({ url: `/pages/common/textview/index?title=${item.title}&content=${item.content}` })
}
</script>

<style lang="scss" scoped>
page {
  background-color: #f8f8f8;
}

.help-container {
  margin-bottom: 100rpx;
  padding: 30rpx;
}

.list-title {
  margin-bottom: 30rpx;
}

.childList {
  background: #ffffff;
  box-shadow: 0px 0px 10rpx rgba(193, 193, 193, 0.2);
  border-radius: 16rpx;
  margin-top: 10rpx;
}

.line {
  width: 100%;
  height: 1rpx;
  background-color: #f5f5f5;
}

.text-title {
  color: #303133;
  font-size: 32rpx;
  font-weight: bold;
  margin-left: 10rpx;
}

.text-item {
  font-size: 28rpx;
  padding: 24rpx;
}

.question {
  color: #606266;
  font-size: 28rpx;
}
</style>
