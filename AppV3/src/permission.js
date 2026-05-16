/**
 * @description 路由守卫 - 全局导航权限控制
 * @description 通过uni.addInterceptor对四种路由跳转方式（navigateTo/redirectTo/reLaunch/switchTab）
 * 添加拦截器，实现登录验证和白名单放行。已登录用户访问登录页时重定向首页；未登录用户访问非白名单页面时重定向登录页
 */
import { getToken } from '@/utils/auth'

// 登录页路径，未登录时重定向到此页
const loginPage = '/pages/login'
// 免登录白名单，这些页面无需Token即可访问
const whiteList = ['/pages/login', '/pages/register', '/pages/common/webview/index']

/**
 * 检查目标URL是否在白名单中，忽略URL中的查询参数
 * @param {string} url - 待检测的完整URL（可能包含查询参数）
 * @returns {boolean} 是否在白名单中
 */
function checkWhite(url) {
  const path = url.split('?')[0]
  return whiteList.indexOf(path) !== -1
}

// 对uni-app的四种路由跳转方式统一添加拦截器
let list = ['navigateTo', 'redirectTo', 'reLaunch', 'switchTab']

list.forEach(item => {
  uni.addInterceptor(item, {
    /**
     * 路由跳转前的拦截处理
     * 已登录：访问登录页时重定向到首页，其余放行
     * 未登录：白名单页面放行，其余重定向到登录页
     */
    invoke(to) {
      if (getToken()) {
        // 已登录用户访问登录页，重定向到首页避免重复登录
        if (to.url === loginPage) {
          uni.reLaunch({ url: '/' })
        }
        return true
      } else {
        // 未登录：白名单页面直接放行，其余重定向到登录页
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
