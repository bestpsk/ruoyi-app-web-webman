/**
 * @description Token管理 - JWT认证令牌的存取与清除
 * @description 基于uni-app本地存储封装Token的读取、写入和删除操作。
 * Token键名为'App-Token'，使用uni.getStorageSync/setStorageSync同步API存储，
 * 数据持久化在设备本地，应用关闭后不会丢失
 */
const TokenKey = 'App-Token'

/**
 * 从uni-app本地存储中获取当前用户的JWT认证令牌
 * @returns {string} Token字符串，未登录时返回空字符串
 */
export function getToken() {
  return uni.getStorageSync(TokenKey)
}

/**
 * 将JWT认证令牌存储到uni-app本地存储中，登录成功后调用
 * @param {string} token - 后端返回的JWT令牌
 */
export function setToken(token) {
  return uni.setStorageSync(TokenKey, token)
}

/**
 * 从uni-app本地存储中清除认证令牌，退出登录时调用
 */
export function removeToken() {
  return uni.removeStorageSync(TokenKey)
}
