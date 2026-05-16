<template>
  <view class="container">
    <web-view :src="url" />
  </view>
</template>

<script setup>
/**
 * @description 内嵌网页页 - 外部链接展示
 * @description 通过web-view组件加载外部URL，用于展示用户协议、隐私政策等网页内容
 */
import { ref, onLoad } from '@dcloudio/uni-app'

/** 要加载的网页URL */
const url = ref('')

/** 页面加载时从路由参数获取URL并解码 */
onLoad((options) => {
  if (options.url) {
    url.value = decodeURIComponent(options.url)
  }
})
</script>
