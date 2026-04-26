import config from '@/config'
import { getToken } from '@/utils/auth'
import errorCode from '@/utils/errorCode'
import { toast, showConfirm } from '@/utils/common'
import { useUserStore } from '@/store/modules/user'

let timeout = 10000
const baseUrl = config.baseUrl

const upload = (options) => {
  const isToken = (options.headers || {}).isToken === false
  if (getToken() && !isToken) {
    options.header = options.header || {}
    options.header['Authorization'] = 'Bearer ' + getToken()
  }
  return new Promise((resolve, reject) => {
    uni.uploadFile({
      url: baseUrl + options.url,
      filePath: options.filePath,
      name: options.name || 'file',
      header: options.header,
      formData: options.formData || {},
      timeout: options.timeout || timeout,
      success: (res) => {
        const data = JSON.parse(res.data)
        const code = data.code || 200
        const msg = errorCode[code] || data.msg || errorCode['default']
        if (code === 401) {
          showConfirm('登录状态已过期，您可以继续留在该页面，或者重新登录?').then(res => {
            if (res.confirm) {
              const userStore = useUserStore()
              userStore.logOut().then(() => {
                uni.reLaunch({ url: '/pages/login' })
              })
            }
          })
          reject('无效的会话，或者会话已过期，请重新登录。')
        } else if (code === 500) {
          toast(msg)
          reject('500')
        } else if (code !== 200) {
          toast(msg)
          reject(code)
        }
        resolve(data)
      },
      fail: (error) => {
        toast('后端接口连接异常')
        reject(error)
      }
    })
  })
}

export default upload
