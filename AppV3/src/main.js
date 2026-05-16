/**
 * @description 应用入口 - uni-app Vue3应用初始化
 * @description 创建SSR兼容的Vue应用实例，注册Pinia状态管理和uview-plus UI框架，
 * 导入全局路由守卫进行登录权限控制。导出createApp函数供uni-app框架调用
 */
import { createSSRApp } from 'vue'
import { createPinia } from 'pinia'
import uviewPlus from 'uview-plus'
import App from './App.vue'
import './permission'

export function createApp() {
  // 使用createSSRApp创建SSR兼容实例，支持小程序端服务端渲染
  const app = createSSRApp(App)
  const pinia = createPinia()
  // 注册Pinia状态管理，用于全局状态共享（用户信息、权限等）
  app.use(pinia)
  // 注册uview-plus UI框架，loadFontOnce配置避免字体重复加载
  app.use(uviewPlus, () => {
    return {
      options: {
        config: {
          loadFontOnce: true
        }
      }
    }
  })
  return { app }
}
