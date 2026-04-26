<template>
  <view class="container">
    <view class="example">
      <uni-forms ref="formRef" :model="user" labelWidth="80px">
        <uni-forms-item label="用户昵称" name="nickName">
          <uni-easyinput v-model="user.nickName" placeholder="请输入昵称" />
        </uni-forms-item>
        <uni-forms-item label="手机号码" name="phonenumber">
          <uni-easyinput v-model="user.phonenumber" placeholder="请输入手机号码" />
        </uni-forms-item>
        <uni-forms-item label="邮箱" name="email">
          <uni-easyinput v-model="user.email" placeholder="请输入邮箱" />
        </uni-forms-item>
        <uni-forms-item label="性别" name="sex" required>
          <uni-data-checkbox v-model="user.sex" :localdata="sexs" />
        </uni-forms-item>
      </uni-forms>
      <button type="primary" @click="submit">提交</button>
    </view>
  </view>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getUserProfile, updateUserProfile } from '@/api/system/user'

const formRef = ref(null)
const user = ref({ nickName: '', phonenumber: '', email: '', sex: '' })
const sexs = ref([
  { text: '男', value: '0' },
  { text: '女', value: '1' }
])

onMounted(() => {
  getUser()
})

function getUser() {
  getUserProfile().then(response => {
    user.value = response.data
  })
}

function submit() {
  updateUserProfile(user.value).then(() => {
    uni.showToast({ title: '修改成功', icon: 'success' })
  })
}
</script>

<style lang="scss" scoped>
page {
  background-color: #ffffff;
}

.example {
  padding: 15px;
  background-color: #fff;
}
</style>
