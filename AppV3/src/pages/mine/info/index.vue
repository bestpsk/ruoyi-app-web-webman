<template>
  <view class="container">
    <uni-list>
      <uni-list-item showExtraIcon :extraIcon="{ type: 'person-filled' }" title="昵称" :rightText="user.nickName" />
      <uni-list-item showExtraIcon :extraIcon="{ type: 'phone-filled' }" title="手机号码" :rightText="user.phonenumber" />
      <uni-list-item showExtraIcon :extraIcon="{ type: 'email-filled' }" title="邮箱" :rightText="user.email" />
      <uni-list-item showExtraIcon :extraIcon="{ type: 'auth-filled' }" title="岗位" :rightText="postGroup" />
      <uni-list-item showExtraIcon :extraIcon="{ type: 'staff-filled' }" title="角色" :rightText="roleGroup" />
      <uni-list-item showExtraIcon :extraIcon="{ type: 'calendar-filled' }" title="创建日期" :rightText="user.createTime" />
    </uni-list>
  </view>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getUserProfile } from '@/api/system/user'

const user = ref({})
const roleGroup = ref('')
const postGroup = ref('')

onMounted(() => {
  getUser()
})

function getUser() {
  getUserProfile().then(response => {
    user.value = response.data
    roleGroup.value = response.roleGroup
    postGroup.value = response.postGroup
  })
}
</script>

<style lang="scss">
page {
  background-color: #ffffff;
}
</style>
