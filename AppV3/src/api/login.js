import request from '@/utils/request'

export function login(username, password, code, uuid) {
  const data = { username, password, code, uuid }
  return request({
    url: '/login',
    headers: { isToken: false },
    method: 'post',
    data: data
  })
}

export function register(data) {
  return request({
    url: '/register',
    headers: { isToken: false },
    method: 'post',
    data: data
  })
}

export function getInfo() {
  return request({
    url: '/getInfo',
    method: 'get'
  })
}

export function logout() {
  return request({
    url: '/logout',
    method: 'post'
  })
}

export function getCodeImg() {
  return request({
    url: '/captchaImage',
    headers: { isToken: false },
    method: 'get',
    timeout: 20000
  })
}
