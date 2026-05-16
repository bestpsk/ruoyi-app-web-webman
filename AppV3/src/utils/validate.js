/**
 * @description 数据验证工具 - 常用类型校验函数集合
 * @description 提供空值判断、URL协议判断、外链判断和邮箱格式校验等验证方法
 */

/**
 * 判断值是否为空（null、空字符串或undefined）
 * @param {*} value - 待检测的值
 * @returns {boolean} 为空返回true
 */
export function isEmpty(value) {
  return value === null || value === '' || value === undefined
}

/**
 * 判断URL是否为HTTP或HTTPS协议，用于区分外链和相对路径
 * @param {string} url - 待检测的URL字符串
 * @returns {boolean} 是HTTP/HTTPS协议返回true
 */
export function isHttp(url) {
  return url && (url.indexOf('http://') !== -1 || url.indexOf('https://') !== -1)
}

/**
 * 判断路径是否为外部链接（https/mailto/tel开头），用于路由跳转时区分内部页面和外部链接
 * @param {string} path - 待检测的路径字符串
 * @returns {boolean} 是外链返回true
 */
export function isExternal(path) {
  return /^(https?:|mailto:|tel:)/.test(path)
}

/**
 * 校验邮箱格式是否合法
 * @param {string} email - 待校验的邮箱地址
 * @returns {boolean} 格式合法返回true
 */
export function validEmail(email) {
  const reg = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
  return reg.test(email)
}
