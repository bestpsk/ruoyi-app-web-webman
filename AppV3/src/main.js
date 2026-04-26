import { createSSRApp } from 'vue'
import { createPinia } from 'pinia'
import uviewPlus from 'uview-plus'
import App from './App.vue'
import './permission'

export function createApp() {
  const app = createSSRApp(App)
  const pinia = createPinia()
  app.use(pinia)
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
