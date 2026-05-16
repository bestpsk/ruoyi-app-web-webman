/**
 * @description 登录认证API - 用户登录注册与身份验证接口
 * @description 提供登录、注册、获取用户信息、退出登录和验证码获取接口。
 * 登录和注册接口不携带Token（headers.isToken=false），验证码接口超时时间为20秒
 */
import request from '@/utils/request'

/**
 * 用户登录，验证账号密码和验证码后返回JWT令牌
 * @param {string} username - 用户名
 * @param {string} password - 密码
 * @param {string} code - 验证码答案
 * @param {string} uuid - 验证码唯一标识，用于后端校验验证码
 * @returns {Promise<{token: string}>} 登录成功返回JWT令牌
 */
export function login(username, password, code, uuid) {
  const data = { username, password, code, uuid }
  return request({
    url: '/login',
    headers: { isToken: false },
    method: 'post',
    data: data
  })
}

/**
 * 用户注册，创建新账号
 * @param {object} data - 注册信息 { username, password, confirmPassword, code, uuid }
 * @returns {Promise<void>}
 */
export function register(data) {
  return request({
    url: '/register',
    headers: { isToken: false },
    method: 'post',
    data: data
  })
}

/**
 * 获取当前登录用户的详细信息，包括角色、权限和密码状态
 * @returns {Promise<{user: object, roles: string[], permissions: string[], pwdChrtype: number}>}
 */
export function getInfo() {
  return request({
    url: '/getInfo',
    method: 'get'
  })
}

/**
 * 退出登录，通知后端清除服务端会话
 * @returns {Promise<void>}
 */
export function logout() {
  return request({
    url: '/logout',
    method: 'post'
  })
}

/**
 * 获取验证码图片，返回Base64编码的图片和验证码UUID
 * @returns {Promise<{img: string, uuid: string, captchaEnabled: boolean}>}
 */
export function getCodeImg() {
  return request({
    url: '/captchaImage',
    headers: { isToken: false },
    method: 'get',
    timeout: 20000
  })
}
