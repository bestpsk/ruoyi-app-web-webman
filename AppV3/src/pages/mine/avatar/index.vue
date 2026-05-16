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
/**
 * @description 修改头像页 - 用户头像更换
 * @description 支持从相册或相机选择图片，上传至服务端并更新store中的头像地址
 */
import { ref } from 'vue'
import { uploadAvatar } from '@/api/system/user'
import { useUserStore } from '@/store/modules/user'
import config from '@/config'

const userStore = useUserStore()
const baseUrl = config.baseUrl
/** 用户选中的本地图片路径 */
const selectedImage = ref('')
/** 当前展示的头像地址，初始为store中的头像 */
const avatarSrc = ref(userStore.avatar)

/** 从相册或相机选择一张图片作为新头像 */
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

/** 上传选中的头像图片到服务端，成功后更新store头像地址并返回上一页 */
async function uploadImage() {
  if (!selectedImage.value) {
    uni.showToast({ title: '请先选择头像', icon: 'none' })
    return
  }
  uni.showLoading({ title: '上传中...' })
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
