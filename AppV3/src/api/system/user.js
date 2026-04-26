import upload from '@/utils/upload'
import request from '@/utils/request'

export function updateUserPwd(oldPassword, newPassword) {
  const data = { oldPassword, newPassword }
  return request({
    url: '/system/user/profile/updatePwd',
    method: 'put',
    data: data
  })
}

export function getUserProfile() {
  return request({
    url: '/system/user/profile',
    method: 'get'
  })
}

export function updateUserProfile(data) {
  return request({
    url: '/system/user/profile',
    method: 'put',
    data: data
  })
}

export function uploadAvatar(data) {
  return upload({
    url: '/system/user/profile/avatar',
    name: data.name,
    filePath: data.filePath
  })
}
