import request from '@/utils/request'

export function listStore(params) {
  return request({ url: '/business/store/list', method: 'get', params })
}

export function getStore(id) {
  return request({ url: '/business/store/' + id, method: 'get' })
}

export function addStore(data) {
  return request({ url: '/business/store', method: 'post', data })
}

export function updateStore(data) {
  return request({ url: '/business/store', method: 'put', data })
}

export function delStore(ids) {
  return request({ url: '/business/store/' + ids, method: 'delete' })
}

export function searchStore(keyword, enterpriseId) {
  return request({ url: '/business/store/search', method: 'get', params: { keyword, enterpriseId } })
}
