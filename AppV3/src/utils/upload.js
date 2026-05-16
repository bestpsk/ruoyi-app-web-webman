/**
 * @description 文件上传封装 - uni.uploadFile统一封装与拦截处理
 * @description 基于uni.uploadFile封装统一的文件上传方法，核心功能包括：
 * 1. 自动在请求头中注入Bearer Token（可通过headers.isToken=false跳过）
 * 2. 统一响应状态码处理：401弹确认框后跳转登录、500提示服务器错误、非200统一toast提示
 * 3. 上传字段名默认为'file'，可通过name参数自定义
 * 4. 默认超时时间10秒
 */
import config from '@/config'
import { getToken } from '@/utils/auth'
import errorCode from '@/utils/errorCode'
import { toast, showConfirm } from '@/utils/common'
import { useUserStore } from '@/store/modules/user'

let timeout = 10000
const baseUrl = config.baseUrl

/**
 * 统一文件上传方法，封装uni.uploadFile实现自动Token注入和错误处理
 * @param {object} options - 上传配置项
 * @param {string} options.url - 上传接口路径（相对路径，会自动拼接baseUrl）
 * @param {string} options.filePath - 本地文件路径，uni.chooseImage等API返回的路径
 * @param {string} [options.name='file'] - 上传文件对应的字段名，后端通过此字段接收文件
 * @param {object} [options.header] - 自定义请求头
 * @param {object} [options.headers] - 扩展配置，{ isToken: false }可跳过Token注入
 * @param {object} [options.formData] - 额外的表单数据，如分类、描述等
 * @param {number} [options.timeout=10000] - 超时时间（毫秒）
 * @returns {Promise<object>} 上传成功后返回的响应数据
 */
const upload = (options) => {
  // headers.isToken=false时跳过Token注入
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
        // uni.uploadFile返回的data是字符串，需要手动解析为JSON
        const data = JSON.parse(res.data)
        const code = data.code || 200
        const msg = errorCode[code] || data.msg || errorCode['default']
        if (code === 401) {
          // 401未授权：弹出确认框，确认后清除登录状态并跳转登录页
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
          // 500服务端错误：弹出错误提示
          toast(msg)
          reject('500')
        } else if (code !== 200) {
          // 其他非200状态码：弹出提示
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
