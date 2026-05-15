<template>
  <view class="container">
    <view class="avatar-section">
      <image :src="avatarSrc" class="current-avatar" mode="aspectFill" />
    </view>
    <view class="btn-section">
      <button type="primary" @click="chooseImage">选择头像</button>
      <button type="warn" @click="uploadImage" :disabled="!selectedImage">提交</button>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue'
import { uploadAvatar } from '@/api/system/user'
import { useUserStore } from '@/store/modules/user'
import config from '@/config'

const userStore = useUserStore()
const baseUrl = config.baseUrl
const selectedImage = ref('')
const avatarSrc = ref(userStore.avatar)

function chooseImage() {
  uni.chooseImage({
    count: 1,
    sizeType: ['compressed'],
    sourceType: ['album', 'camera'],
    success: (res) => {
      selectedImage.value = res.tempFilePaths[0]
      avatarSrc.value = selectedImage.value
    }
  })
}

async function uploadImage() {
  if (!selectedImage.value) {
    uni.showToast({ title: '请先选择头像', icon: 'none' })
    return
  }
  uni.showLoading({ title: '上传�?..' })
  try {
    const data = { name: 'avatarfile', filePath: selectedImage.value }
    const response = await uploadAvatar(data)
    const imgUrl = response.imgUrl
    userStore.avatar = imgUrl.startsWith('http') ? imgUrl : baseUrl + imgUrl
    uni.showToast({ title: '修改成功', icon: 'success' })
    setTimeout(() => {
      uni.navigateBack()
    }, 1500)
  } catch {
    uni.showToast({ title: '上传失败', icon: 'none' })
  } finally {
    uni.hideLoading()
  }
}
</script>

<style lang="scss" scoped>
page {
  background-color: #f5f6f7;
}

.container {
  padding: 30rpx;
}

.avatar-section {
  display: flex;
  justify-content: center;
  padding: 40rpx 0;
}

.current-avatar {
  width: 300rpx;
  height: 300rpx;
  border-radius: 50%;
  border: 2px solid #e5e5e5;
}

.btn-section {
  padding: 0 40rpx;

  button {
    margin-top: 30rpx;
  }
}
</style>
