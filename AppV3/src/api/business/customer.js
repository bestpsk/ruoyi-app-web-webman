import request from '@/utils/request'

export function listCustomer(params) {
  return request({ url: '/business/customer/list', method: 'get', params })
}

export function getCustomer(id) {
  return request({ url: '/business/customer/' + id, method: 'get' })
}

export function searchCustomer(keyword, enterpriseId, storeId) {
  return request({ url: '/business/customer/search', method: 'get', params: { keyword, enterpriseId, storeId } })
}

export function addCustomer(data) {
  return request({ url: '/business/customer', method: 'post', data })
}

export function updateCustomer(data) {
  return request({ url: '/business/customer', method: 'put', data })
}

export function delCustomer(id) {
  return request({ url: '/business/customer/' + id, method: 'delete' })
}
