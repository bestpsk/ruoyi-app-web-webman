import request from '@/utils/request'

export function listEnterprise(params) {
  return request({
    url: '/business/enterprise/list',
    method: 'get',
    params
  })
}

export function getEnterprise(id) {
  return request({
    url: '/business/enterprise/' + id,
    method: 'get'
  })
}

export function addEnterprise(data) {
  return request({
    url: '/business/enterprise',
    method: 'post',
    data
  })
}

export function updateEnterprise(data) {
  return request({
    url: '/business/enterprise',
    method: 'put',
    data
  })
}

export function delEnterprise(ids) {
  return request({
    url: '/business/enterprise/' + ids,
    method: 'delete'
  })
}
