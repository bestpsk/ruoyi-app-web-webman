/**
 * @description 路由守卫 - 全局导航权限控制
 * @description 处理登录验证、白名单放行、锁屏拦截、动态路由加载及权限校验。
 * 核心流程：已登录→检查锁屏→加载用户信息和动态路由；未登录→白名单放行或重定向登录页
 */
import router from './router'
import { ElMessage } from 'element-plus'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'
import { getToken } from '@/utils/auth'
import { isHttp, isPathMatch } from '@/utils/validate'
import { isRelogin } from '@/utils/request'
import useUserStore from '@/store/modules/user'
import useLockStore from '@/store/modules/lock'
import useSettingsStore from '@/store/modules/settings'
import usePermissionStore from '@/store/modules/permission'

NProgress.configure({ showSpinner: false })

// 免登录白名单路径，匹配这些路径时无需Token验证
const whiteList = ['/login', '/register']

/**
 * 判断目标路径是否在白名单中
 * @param {string} path - 待检测的路径
 * @returns {boolean} 是否在白名单中
 */
const isWhiteList = (path) => {
  return whiteList.some(pattern => isPathMatch(pattern, path))
}

router.beforeEach(async (to, from) => {
  NProgress.start()
  if (getToken()) {
    // 已登录：同步浏览器标题为当前路由的meta.title
    to.meta.title && useSettingsStore().setTitle(to.meta.title)
    const isLock = useLockStore().isLock

    // 已登录时访问登录页，重定向到首页避免重复登录
    if (to.path === '/login') {
      NProgress.done()
      return { path: '/' }
    }
    // 白名单路径直接放行（如注册页）
    if (isWhiteList(to.path)) {
      return true
    }
    // 锁屏状态下，除锁屏页外的所有路径重定向到锁屏页
    if (isLock && to.path !== '/lock') {
      NProgress.done()
      return { path: '/lock' }
    }
    // 未锁屏时禁止直接访问锁屏页，重定向到首页
    if (!isLock && to.path === '/lock') {
      NProgress.done()
      return { path: '/' }
    }
    // 刷新页面后store中的roles会丢失，需要重新拉取用户信息和动态路由
    if (useUserStore().roles.length === 0) {
      isRelogin.show = true
      try {
        // 从后端拉取用户信息（角色、权限等）
        await useUserStore().getInfo()
        isRelogin.show = false
        // 根据用户角色生成可访问的动态路由并注册到router
        const accessRoutes = await usePermissionStore().generateRoutes()
        accessRoutes.forEach(route => {
          // 外链路由不需要注册到vue-router
          if (!isHttp(route.path)) {
            router.addRoute(route)
          }
        })
        // 使用replace重新导航，确保刚注册的动态路由能被正确匹配
        return { ...to, replace: true }
      } catch (err) {
        console.error('[permission.js] 路由守卫错误:', err)
        // 获取用户信息失败时清除登录状态并跳转首页
        await useUserStore().logOut()
        ElMessage.error(err || '获取用户信息失败')
        return { path: '/' }
      }
    }
    return true
  } else {
    // 未登录：白名单路径直接放行，其余重定向到登录页并携带原目标路径用于登录后回跳
    if (isWhiteList(to.path)) {
      return true
    }
    NProgress.done()
    return `/login?redirect=${to.fullPath}`
  }
})

router.afterEach(() => {
  NProgress.done()
})
