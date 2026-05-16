/**
 * @description Token管理 - JWT认证令牌的存取与清除
 * @description 基于js-cookie封装Token的读取、写入和删除操作。
 * Token键名为'Admin-Token'，存储在Cookie中，随浏览器关闭自动失效（会话级Cookie）。
 * 安全注意：当前未设置Cookie过期时间和secure/httpOnly属性，生产环境建议加强安全配置
 */
import Cookies from 'js-cookie'

const TokenKey = 'Admin-Token'

/**
 * 从Cookie中获取当前用户的JWT认证令牌
 * @returns {string|undefined} Token字符串，未登录时返回undefined
 */
export function getToken() {
  return Cookies.get(TokenKey)
}

/**
 * 将JWT认证令牌存储到Cookie中，登录成功后调用
 * @param {string} token - 后端返回的JWT令牌
 * @returns {string|undefined} Cookie.set的返回值
 */
export function setToken(token) {
  return Cookies.set(TokenKey, token)
}

/**
 * 从Cookie中清除认证令牌，退出登录时调用
 * @returns {void} Cookie.remove无返回值
 */
export function removeToken() {
  return Cookies.remove(TokenKey)
}
