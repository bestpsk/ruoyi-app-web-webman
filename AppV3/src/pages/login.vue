<template>
  <view class="normal-login-container">
    <view class="logo-content">
      <image style="width: 100rpx; height: 100rpx" src="/static/logo.png" mode="widthFix" />
      <text class="title">馥辰国际</text>
    </view>
    <view class="login-form-content">
      <view class="input-item">
        <u-icon name="account" size="20" color="#999" style="margin-left: 10px" />
        <input v-model="loginForm.username" class="input" type="text" placeholder="请输入账号" maxlength="30" />
      </view>
      <view class="input-item">
        <u-icon name="lock" size="20" color="#999" style="margin-left: 10px" />
        <input v-model="loginForm.password" type="password" class="input" placeholder="请输入密码" maxlength="20" />
      </view>
      <view class="input-item captcha-row" v-if="captchaEnabled">
        <u-icon name="photo" size="20" color="#999" style="margin-left: 10px" />
        <input v-model="loginForm.code" type="number" class="input captcha-input" placeholder="请输入验证码" maxlength="4" />
        <view class="login-code" @click="getCode">
          <image v-if="codeUrl" :src="codeUrl" class="login-code-img" mode="heightFix" />
          <view v-else class="login-code-placeholder">获取验证码</view>
        </view>
      </view>
      <view class="action-btn">
        <button @click="handleLogin" class="login-btn">登录</button>
      </view>
      <view class="reg" v-if="register">
        <text class="uni-text-gray">没有账号？</text>
        <text @click="handleUserRegister" class="uni-text-blue">立即注册</text>
      </view>
      <view class="xieyi">
        <text class="uni-text-gray">登录即代表同意</text>
        <text @click="handleUserAgrement" class="uni-text-blue">《用户协议》</text>
        <text @click="handlePrivacy" class="uni-text-blue">《隐私协议》</text>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getCodeImg } from '@/api/login'
import { getToken } from '@/utils/auth'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()
const globalConfig = ref(getApp().globalData.config)
const codeUrl = ref('')
const captchaEnabled = ref(true)
const register = ref(true)
const loginForm = ref({
  username: 'admin',
  password: 'admin123',
  code: '',
  uuid: ''
})

onMounted(() => {
  getCode()
})

// #ifdef H5
if (getToken()) {
  uni.reLaunch({ url: '/pages/index' })
}
// #endif

function handleUserRegister() {
  uni.redirectTo({ url: '/pages/register' })
}

function handlePrivacy() {
  let site = globalConfig.value.appInfo.agreements[0]
  uni.navigateTo({ url: `/pages/common/webview/index?title=${site.title}&url=${site.url}` })
}

function handleUserAgrement() {
  let site = globalConfig.value.appInfo.agreements[1]
  uni.navigateTo({ url: `/pages/common/webview/index?title=${site.title}&url=${site.url}` })
}

function getCode() {
  getCodeImg().then(res => {
    captchaEnabled.value = res.captchaEnabled === undefined ? true : res.captchaEnabled
    if (captchaEnabled.value) {
      codeUrl.value = 'data:image/gif;base64,' + res.img
      loginForm.value.uuid = res.uuid
    }
  }).catch(err => {
    console.error('获取验证码失败', err)
  })
}

async function handleLogin() {
  if (loginForm.value.username === '') {
    uni.showToast({ title: '请输入账号', icon: 'none' })
  } else if (loginForm.value.password === '') {
    uni.showToast({ title: '请输入密码', icon: 'none' })
  } else if (loginForm.value.code === '' && captchaEnabled.value) {
    uni.showToast({ title: '请输入验证码', icon: 'none' })
  } else {
    uni.showLoading({ title: '登录中，请耐心等待...' })
    pwdLogin()
  }
}

async function pwdLogin() {
  try {
    await userStore.loginAction(loginForm.value)
    uni.hideLoading()
    await userStore.getInfoAction()
    uni.reLaunch({ url: '/pages/index' })
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

    .login-btn {
      margin-top: 40px;
      height: 45px;
      line-height: 45px;
      background-color: #3c96f3;
      color: #ffffff;
      border-radius: 20px;
      font-size: 16px;
    }

    .reg {
      margin-top: 15px;
      text-align: center;
    }

    .xieyi {
      color: #333;
      margin-top: 20px;
      text-align: center;
    }
  }
}
</style>
