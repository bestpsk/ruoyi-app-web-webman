/**
 * @description 系统用户API - 用户管理与个人信息接口
 * @description 提供用户列表查询、密码修改、个人资料获取/更新和头像上传接口。
 * 头像上传使用upload工具而非request，因为需要文件上传能力
 */
import request from '@/utils/request'
import upload from '@/utils/upload'

/**
 * 分页查询系统用户列表
 * @param {object} params - 查询参数 { pageNum, pageSize, userName, phonenumber, status }
 * @returns {Promise<object>} 用户分页列表
 */
export function listUser(params) {
  return request({ url: '/system/user/list', method: 'get', params })
}

/**
 * 修改用户密码，需验证旧密码
 * @param {string} oldPassword - 原密码
 * @param {string} newPassword - 新密码
 * @returns {Promise<void>}
 */
export function updateUserPwd(oldPassword, newPassword) {
  const data = { oldPassword, newPassword }
  return request({ url: '/system/user/profile/updatePwd', method: 'put', params: data })
}

/**
 * 获取当前登录用户的个人资料
 * @returns {Promise<object>} 用户资料 { userName, nickName, phonenumber, email, sex, avatar }
 */
export function getUserProfile() {
  return request({ url: '/system/user/profile', method: 'get' })
}

/**
 * 更新当前登录用户的个人资料
 * @param {object} data - 用户资料 { nickName, phonenumber, email, sex }
 * @returns {Promise<void>}
 */
export function updateUserProfile(data) {
  return request({ url: '/system/user/profile', method: 'put', data })
}

/**
 * 上传用户头像到服务器，使用uni.uploadFile实现文件上传
 * @param {object} data - 上传参数 { filePath: 本地图片路径 }
 * @returns {Promise<{url: string, fileName: string}>} 上传成功返回头像URL和文件名
 */
export function uploadAvatar(data) {
  return upload({ url: '/system/user/profile/avatar', name: 'avatarfile', filePath: data.filePath })
}
