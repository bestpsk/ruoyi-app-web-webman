import request from '@/utils/request'

export function listOperation(query) {
  return request({ url: '/business/operation/list', method: 'get', params: query })
}

export function addOperation(data) {
  return request({ url: '/business/operation', method: 'post', data })
}

export function delOperation(recordId) {
  return request({ url: '/business/operation/' + recordId, method: 'delete' })
}
