import request from '@/utils/request'

export function listOperation(params) {
  return request({ url: '/business/operation/list', method: 'get', params })
}

export function addOperation(data) {
  return request({ url: '/business/operation', method: 'post', data })
}

export function delOperation(id) {
  return request({ url: '/business/operation/' + id, method: 'delete' })
}

export function getOperationRecord(id) {
  return request({ url: '/business/operation/' + id, method: 'get' })
}
