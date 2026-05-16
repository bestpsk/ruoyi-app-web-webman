<template>
  <view class="pwd-retrieve-container">
    <uni-forms ref="formRef" :model="user" labelWidth="80px">
      <uni-forms-item name="oldPassword" label="旧密码">
        <uni-easyinput type="password" v-model="user.oldPassword" placeholder="请输入旧密码" />
      </uni-forms-item>
      <uni-forms-item name="newPassword" label="新密码">
        <uni-easyinput type="password" v-model="user.newPassword" placeholder="请输入新密码" />
      </uni-forms-item>
      <uni-forms-item name="confirmPassword" label="确认密码">
        <uni-easyinput type="password" v-model="user.confirmPassword" placeholder="请确认新密码" />
      </uni-forms-item>
      <button type="primary" @click="submit">提交</button>
    </uni-forms>
  </view>
</template>

<script setup>
/**
 * @description 修改密码页 - 用户密码更新
 * @description 提供旧密码验证和新密码设置功能，包含密码长度和一致性校验
 */
import { ref } from 'vue'
import { updateUserPwd } from '@/api/system/user'

const formRef = ref(null)
const user = ref({
  oldPassword: undefined,
  newPassword: undefined,
  confirmPassword: undefined
})

/** 提交密码修改表单，校验旧密码非空、新密码长度6-20位、两次密码一致后调用接口更新 */
function submit() {
  if (!user.value.oldPassword) {
    uni.showToast({ title: '旧密码不能为空', icon: 'none' })
    return
  }
  if (!user.value.newPassword) {
    uni.showToast({ title: '新密码不能为空', icon: 'none' })
    return
  }
  if (user.value.newPassword.length < 6 || user.value.newPassword.length > 20) {
    uni.showToast({ title: '新密码长度在6到20个字符', icon: 'none' })
    return
  }
  if (user.value.newPassword !== user.value.confirmPassword) {
    uni.showToast({ title: '两次输入的密码不一致', icon: 'none' })
    return
  }
  updateUserPwd(user.value.oldPassword, user.value.newPassword).then(() => {
    uni.showToast({ title: '修改成功', icon: 'success' })
  })
}
</script>

<style lang="scss" scoped>
page {
  background-color: #ffffff;
}

.pwd-retrieve-container {
  padding: 15px;
}
</style>
