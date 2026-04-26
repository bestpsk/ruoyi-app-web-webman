<template>
  <view class="container">
    <view class="title">{{ title }}</view>
    <view class="content">{{ content }}</view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'

const title = ref('')
const content = ref('')

onLoad((options) => {
  if (options.title) {
    title.value = decodeURIComponent(options.title)
  }
  if (options.content) {
    content.value = decodeURIComponent(options.content)
  }
})
</script>

<style lang="scss" scoped>
.container {
  padding: 15px;
}

.title {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 15px;
}

.content {
  font-size: 14px;
  line-height: 24px;
  color: #666;
}
</style>
