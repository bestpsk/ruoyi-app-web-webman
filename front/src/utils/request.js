/**
 * @description HTTP请求封装 - Axios实例配置与拦截器
 * @description 创建Axios实例并配置请求/响应拦截器，实现以下核心功能：
 * 1. 请求拦截：自动注入JWT Token、GET参数序列化、POST/PUT防重复提交（1秒内相同请求自动拦截）
 * 2. 响应拦截：统一错误码处理（401过期弹窗重登录、500服务端错误提示、601警告提示）
 * 3. 通用下载：封装文件下载方法，支持Blob流式下载和错误响应解析
 */
import axios from 'axios'
import { ElNotification , ElMessageBox, ElMessage, ElLoading } from 'element-plus'
import { getToken } from '@/utils/auth'
import errorCode from '@/utils/errorCode'
import { tansParams, blobValidate } from '@/utils/ruoyi'
import cache from '@/plugins/cache'
import { saveAs } from 'file-saver'
import useUserStore from '@/store/modules/user'

let downloadLoadingInstance

// 控制是否显示"重新登录"确认框，避免多个401响应同时弹出多个弹窗
export let isRelogin = { show: false }

axios.defaults.headers['Content-Type'] = 'application/json;charset=utf-8'

// 创建Axios实例，统一配置基础URL和超时时间
const service = axios.create({
  baseURL: import.meta.env.VITE_APP_BASE_API,
  timeout: 10000
})

// 请求拦截器：Token注入、参数序列化、防重复提交
service.interceptors.request.use(config => {
  // 请求头中isToken=false时跳过Token注入（如验证码接口不需要Token）
  const isToken = (config.headers || {}).isToken === false
  // 请求头中repeatSubmit=false时跳过防重复提交检查（如轮询接口）
  const isRepeatSubmit = (config.headers || {}).repeatSubmit === false
  // 防重复提交的时间间隔，默认1秒内相同请求视为重复
  const interval = (config.headers || {}).interval || 1000
  if (getToken() && !isToken) {
    // 在Authorization头中注入Bearer Token，后端通过此字段验证用户身份
    config.headers['Authorization'] = 'Bearer ' + getToken()
    console.log('[request]', config.url, 'Token:', getToken()?.substring(0, 30) + '...')
  }
  // GET请求：将params对象序列化为URL查询字符串，避免参数丢失
  if (config.method === 'get' && config.params) {
    let url = config.url + '?' + tansParams(config.params)
    url = url.slice(0, -1)
    config.params = {}
    config.url = url
  }
  // POST/PUT请求防重复提交：将请求数据和时间戳存入sessionStorage，
  // 与上次请求对比URL和数据，间隔小于阈值则拦截
  if (!isRepeatSubmit && (config.method === 'post' || config.method === 'put')) {
    const requestObj = {
      url: config.url,
      data: typeof config.data === 'object' ? JSON.stringify(config.data) : config.data,
      time: new Date().getTime()
    }
    const requestSize = Object.keys(JSON.stringify(requestObj)).length
    const limitSize = 5 * 1024 * 1024
    // 请求数据超过5M时跳过防重复检查，避免sessionStorage溢出
    if (requestSize >= limitSize) {
      console.warn(`[${config.url}]: ` + '请求数据大小超出允许的5M限制，无法进行防重复提交验证。')
      return config
    }
    const sessionObj = cache.session.getJSON('sessionObj')
    if (sessionObj === undefined || sessionObj === null || sessionObj === '') {
      cache.session.setJSON('sessionObj', requestObj)
    } else {
      const s_url = sessionObj.url
      const s_data = sessionObj.data
      const s_time = sessionObj.time
      // 相同URL + 相同数据 + 间隔小于阈值 → 判定为重复提交并拦截
      if (s_data === requestObj.data && requestObj.time - s_time < interval && s_url === requestObj.url) {
        const message = '数据正在处理，请勿重复提交'
        console.warn(`[${s_url}]: ` + message)
        return Promise.reject(new Error(message))
      } else {
        cache.session.setJSON('sessionObj', requestObj)
      }
    }
  }
  return config
}, error => {
    console.log(error)
    Promise.reject(error)
})

// 响应拦截器：统一处理业务错误码和网络异常
service.interceptors.response.use(res => {
    const code = res.data.code || 200
    const msg = errorCode[code] || res.data.msg || errorCode['default']
    console.log('[response]', res.config.url, 'code:', code, 'data:', JSON.stringify(res.data).substring(0, 200))
    // 文件下载响应（Blob/ArrayBuffer）直接返回原始数据，不做业务码判断
    if (res.request.responseType ===  'blob' || res.request.responseType ===  'arraybuffer') {
      return res.data
    }
    if (code === 401) {
      // 401未授权：弹出确认框提示重新登录，使用isRelogin标志防止多个401同时弹窗
      if (!isRelogin.show) {
        isRelogin.show = true
        ElMessageBox.confirm('登录状态已过期，您可以继续留在该页面，或者重新登录', '系统提示', { confirmButtonText: '重新登录', cancelButtonText: '取消', type: 'warning' }).then(() => {
          isRelogin.show = false
          useUserStore().logOut().then(() => {
            location.href = '/index'
          })
      }).catch(() => {
        isRelogin.show = false
      })
    }
      return Promise.reject('无效的会话，或者会话已过期，请重新登录。')
    } else if (code === 500) {
      // 500服务端错误：弹出错误提示
      ElMessage({ message: msg, type: 'error' })
      return Promise.reject(new Error(msg))
    } else if (code === 601) {
      // 601业务警告：弹出警告提示（如数据未修改时的提示）
      ElMessage({ message: msg, type: 'warning' })
      return Promise.reject(new Error(msg))
    } else if (code !== 200) {
      // 其他非200状态码：弹出通知
      ElNotification.error({ title: msg })
      return Promise.reject('error')
    } else {
      return  Promise.resolve(res.data)
    }
  },
  error => {
    console.log('err' + error)
    // 网络异常错误信息汉化处理
    let { message } = error
    if (message == "Network Error") {
      message = "后端接口连接异常"
    } else if (message.includes("timeout")) {
      message = "系统接口请求超时"
    } else if (message.includes("Request failed with status code")) {
      message = "系统接口" + message.slice(-3) + "异常"
    }
    ElMessage({ message: message, type: 'error', duration: 5 * 1000 })
    return Promise.reject(error)
  }
)

/**
 * 通用文件下载方法，以表单方式POST请求获取Blob并保存为文件
 * @param {string} url - 下载接口地址
 * @param {object} params - 请求参数，将以x-www-form-urlencoded格式发送
 * @param {string} filename - 下载保存的文件名（含扩展名）
 * @param {object} [config] - 额外的Axios配置项（如自定义headers）
 */
export function download(url, params, filename, config) {
  downloadLoadingInstance = ElLoading.service({ text: "正在下载数据，请稍候", background: "rgba(0, 0, 0, 0.7)", })
  return service.post(url, params, {
    transformRequest: [(params) => { return tansParams(params) }],
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    responseType: 'blob',
    ...config
  }).then(async (data) => {
    // 校验响应是否为有效的Blob文件，若后端返回JSON错误信息则解析并提示
    const isBlob = blobValidate(data)
    if (isBlob) {
      const blob = new Blob([data])
      saveAs(blob, filename)
    } else {
      const resText = await data.text()
      const rspObj = JSON.parse(resText)
      const errMsg = errorCode[rspObj.code] || rspObj.msg || errorCode['default']
      ElMessage.error(errMsg)
    }
    downloadLoadingInstance.close()
  }).catch((r) => {
    console.error(r)
    ElMessage.error('下载文件出现错误，请联系管理员！')
    downloadLoadingInstance.close()
  })
}

export default service
