<template>
  <view class="normal-login-container">
    <view class="logo-content">
      <image style="width: 100rpx; height: 100rpx" src="/static/logo.png" mode="widthFix" />
      <text class="title">馥辰国际注册</text>
    </view>
    <view class="login-form-content">
      <view class="input-item">
        <u-icon name="account" size="20" color="#999" style="margin-left: 10px" />
        <input v-model="registerForm.username" class="input" type="text" placeholder="请输入账�? maxlength="30" />
      </view>
      <view class="input-item">
        <u-icon name="lock" size="20" color="#999" style="margin-left: 10px" />
        <input v-model="registerForm.password" type="password" class="input" placeholder="请输入密�? maxlength="20" />
      </view>
      <view class="input-item">
        <u-icon name="lock" size="20" color="#999" style="margin-left: 10px" />
        <input v-model="registerForm.confirmPassword" type="password" class="input" placeholder="请输入重复密�? maxlength="20" />
      </view>
      <view class="input-item captcha-row" v-if="captchaEnabled">
        <u-icon name="photo" size="20" color="#999" style="margin-left: 10px" />
        <input v-model="registerForm.code" type="number" class="input captcha-input" placeholder="请输入验证码" maxlength="4" />
        <view class="login-code" @click="getCode">
          <image v-if="codeUrl" :src="codeUrl" class="login-code-img" mode="heightFix" />
          <view v-else class="login-code-placeholder">获取验证�?/view>
        </view>
      </view>
      <view class="action-btn">
        <button @click="handleRegister" class="register-btn">注册</button>
      </view>
    </view>
    <view class="xieyi">
      <text @click="handleUserLogin" class="uni-text-blue">使用已有账号登录</text>
    </view>
  </view>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getCodeImg, register } from '@/api/login'

const codeUrl = ref('')
const captchaEnabled = ref(true)
const registerForm = ref({
  username: '',
  password: '',
  confirmPassword: '',
  code: '',
  uuid: ''
})

onMounted(() => {
  getCode()
})

function handleUserLogin() {
  uni.navigateTo({ url: '/pages/login' })
}

function getCode() {
  getCodeImg().then(res => {
    captchaEnabled.value = res.captchaEnabled === undefined ? true : res.captchaEnabled
    if (captchaEnabled.value) {
      codeUrl.value = 'data:image/gif;base64,' + res.img
      registerForm.value.uuid = res.uuid
    }
  })
}

async function handleRegister() {
  if (registerForm.value.username === '') {
    uni.showToast({ title: '请输入您的账�?, icon: 'none' })
  } else if (registerForm.value.password === '') {
    uni.showToast({ title: '请输入您的密�?, icon: 'none' })
  } else if (registerForm.value.confirmPassword === '') {
    uni.showToast({ title: '请再次输入您的密�?, icon: 'none' })
  } else if (registerForm.value.password !== registerForm.value.confirmPassword) {
    uni.showToast({ title: '两次输入的密码不一�?, icon: 'none' })
  } else if (registerForm.value.code === '' && captchaEnabled.value) {
    uni.showToast({ title: '请输入验证码', icon: 'none' })
  } else {
    uni.showLoading({ title: '注册中，请耐心等待...' })
    doRegister()
  }
}

async function doRegister() {
  try {
    await register(registerForm.value)
    uni.hideLoading()
    uni.showModal({
      title: '系统提示',
      content: '恭喜你，您的账号 ' + registerForm.value.username + ' 注册成功�?,
      success: function (res) {
        if (res.confirm) {
          uni.redirectTo({ url: '/pages/login' })
        }
      }
    })
  } catch {
    uni.hideLoading()
    if (captchaEnabled.value) {
      getCode()
    }
  }
}
</script>

<style lang="scss" scoped>
page {
  background-color: #ffffff;
}

.normal-login-container {
  width: 100%;

  .logo-content {
    width: 100%;
    font-size: 21px;
    text-align: center;
    padding-top: 15%;
    display: flex;
    align-items: center;
    justify-content: center;

    image {
      border-radius: 4px;
    }

    .title {
      margin-left: 10px;
    }
  }

  .login-form-content {
    text-align: center;
    margin: 20px auto;
    margin-top: 15%;
    width: 80%;

    .input-item {
      margin: 20px auto;
      background-color: #f5f6f7;
      height: 45px;
      border-radius: 20px;
      display: flex;
      align-items: center;

      .input {
        flex: 1;
        font-size: 14px;
        line-height: 20px;
        text-align: left;
        padding-left: 15px;
      }

      .captcha-input {
        flex: 1;
      }
    }

    .captcha-row {
      padding-right: 0;

      .login-code {
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        flex-shrink: 0;

        .login-code-img {
          height: 38px;
          width: 200rpx;
        }

        .login-code-placeholder {
          font-size: 12px;
          color: #999;
          white-space: nowrap;
        }
      }
    }

    .register-btn {
      margin-top: 40px;
      height: 45px;
      line-height: 45px;
      background-color: #3c96f3;
      color: #ffffff;
      border-radius: 20px;
      font-size: 16px;
    }

    .xieyi {
      color: #333;
      margin-top: 20px;
      text-align: center;
    }
  }
}
</style>
