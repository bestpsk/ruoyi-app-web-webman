<template>
  <view class="container">
    <web-view :src="url" />
  </view>
</template>

<script setup>
import { ref, onLoad } from '@dcloudio/uni-app'

const url = ref('')

onLoad((options) => {
  if (options.url) {
    url.value = decodeURIComponent(options.url)
  }
})
</script>
