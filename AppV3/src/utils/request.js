/**
 * @description HTTP请求封装 - uni.request统一封装与拦截处理
 * @description 基于uni.request封装统一的请求方法，核心功能包括：
 * 1. 自动在请求头中注入Bearer Token（可通过headers.isToken=false跳过）
 * 2. 自动将params对象序列化为URL查询参数
 * 3. 统一响应状态码处理：401弹确认框后跳转登录、500提示服务器错误、非200统一toast提示
 * 4. 请求失败时区分超时和连接异常
 * 5. 默认超时时间10秒
 */
import { useUserStore } from '@/store/modules/user'
import config from '@/config'
import { getToken } from '@/utils/auth'
import errorCode from '@/utils/errorCode'
import { toast, showConfirm, tansParams } from '@/utils/common'

let timeout = 10000
const baseUrl = config.baseUrl

/**
 * 统一请求方法，封装uni.request实现自动Token注入、参数序列化和错误处理
 * @param {object} options - 请求配置项
 * @param {string} options.url - 接口路径（相对路径，会自动拼接baseUrl）
 * @param {string} [options.method='get'] - 请求方法（get/post/put/delete）
 * @param {object} [options.data] - 请求体数据
 * @param {object} [options.params] - URL查询参数，会自动序列化拼接到URL
 * @param {object} [options.header] - 自定义请求头
 * @param {object} [options.headers] - 扩展配置，{ isToken: false }可跳过Token注入
 * @param {number} [options.timeout=10000] - 超时时间（毫秒）
 * @returns {Promise<object>} 响应数据的Promise对象
 */
const request = (options) => {
  // headers.isToken=false时跳过Token注入（如验证码、注册等无需认证的接口）
  const isToken = (options.headers || {}).isToken === false
  options.header = options.header || {}
  if (getToken() && !isToken) {
    // 在Authorization头中注入Bearer Token，后端通过此字段验证用户身份
    options.header['Authorization'] = 'Bearer ' + getToken()
  }
  // GET请求：将params对象序列化为URL查询字符串，避免参数丢失
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
          // 401未授权：弹出确认框，确认后清除登录状态并跳转登录页
          showConfirm('登录状态已过期，您可以继续留在该页面，或者重新登录?').then(() => {
            const userStore = useUserStore()
            userStore.logOut().then(() => {
              uni.reLaunch({ url: '/pages/login' })
            })
          })
          reject('无效的会话，或者会话已过期，请重新登录。')
        } else if (code === 500) {
          // 500服务端错误：弹出错误提示
          toast(msg)
          reject('500')
        } else if (code !== 200) {
          // 其他非200状态码：弹出提示
          toast(msg)
          reject(code)
        } else {
          resolve(res.data)
        }
      },
      fail: (error) => {
        // 网络异常错误信息汉化处理
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
