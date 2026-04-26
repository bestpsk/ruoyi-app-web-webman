import { getToken } from '@/utils/auth'

const loginPage = '/pages/login'
const whiteList = ['/pages/login', '/pages/register', '/pages/common/webview/index']

function checkWhite(url) {
  const path = url.split('?')[0]
  return whiteList.indexOf(path) !== -1
}

let list = ['navigateTo', 'redirectTo', 'reLaunch', 'switchTab']

list.forEach(item => {
  uni.addInterceptor(item, {
    invoke(to) {
      if (getToken()) {
        if (to.url === loginPage) {
          uni.reLaunch({ url: '/' })
        }
        return true
      } else {
        if (checkWhite(to.url)) {
          return true
        }
        uni.reLaunch({ url: loginPage })
        return false
      }
    },
    fail(err) {
      console.log(err)
    }
  })
})
