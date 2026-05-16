<template>
  <view class="container">
    <view class="title">{{ title }}</view>
    <view class="content">{{ content }}</view>
  </view>
</template>

<script setup>
/**
 * @description 文本详情页 - 纯文本内容展示
 * @description 接收路由参数中的标题和内容，用于展示常见问题的解答等纯文本信息
 */
import { ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'

/** 文本标题 */
const title = ref('')
/** 文本内容 */
const content = ref('')

/** 页面加载时从路由参数获取标题和内容并解码 */
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
