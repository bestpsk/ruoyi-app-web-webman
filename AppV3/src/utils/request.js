import { useUserStore } from '@/store/modules/user'
import config from '@/config'
import { getToken } from '@/utils/auth'
import errorCode from '@/utils/errorCode'
import { toast, showConfirm, tansParams } from '@/utils/common'

let timeout = 10000
const baseUrl = config.baseUrl

const request = (options) => {
  const isToken = (options.headers || {}).isToken === false
  options.header = options.header || {}
  if (getToken() && !isToken) {
    options.header['Authorization'] = 'Bearer ' + getToken()
  }
  if (options.params) {
    let url = options.url + '?' + tansParams(options.params)
    url = url.slice(0, -1)
    options.url = url
  }
  return new Promise((resolve, reject) => {
    uni.request({
      method: options.method || 'get',
      timeout: options.timeout || timeout,
      url: baseUrl + options.url,
      data: options.data,
      header: options.header,
      dataType: 'json',
      success: (res) => {
        const code = res.statusCode === 200 ? (res.data.code || 200) : res.statusCode
        const msg = errorCode[code] || res.data.msg || errorCode['default']
        if (code === 401) {
          showConfirm('登录状态已过期，您可以继续留在该页面，或者重新登录?').then(() => {
            const userStore = useUserStore()
            userStore.logOut().then(() => {
              uni.reLaunch({ url: '/pages/login' })
            })
          })
          reject('无效的会话，或者会话已过期，请重新登录。')
        } else if (code === 500) {
          toast(msg)
          reject('500')
        } else if (code !== 200) {
          toast(msg)
          reject(code)
        } else {
          resolve(res.data)
        }
      },
      fail: (error) => {
        let message = '后端接口连接异常'
        if (error.errMsg && error.errMsg.includes('timeout')) {
          message = '系统接口请求超时'
        }
        toast(message)
        reject(error)
      }
    })
  })
}

export default request
