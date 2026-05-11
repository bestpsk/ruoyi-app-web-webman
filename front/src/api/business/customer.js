import request from '@/utils/request'

export function listCustomer(query) {
  return request({ url: '/business/customer/list', method: 'get', params: query })
}

export function getCustomer(customerId) {
  return request({ url: '/business/customer/' + customerId, method: 'get' })
}

export function searchCustomer(keyword, enterpriseId, storeId, hasDeal, satisfaction) {
  return request({ url: '/business/customer/search', method: 'get', params: { keyword, enterpriseId, storeId, hasDeal, satisfaction } })
}

export function addCustomer(data) {
  return request({ url: '/business/customer', method: 'post', data })
}

export function updateCustomer(data) {
  return request({ url: '/business/customer', method: 'put', data })
}

export function delCustomer(customerId) {
  return request({ url: '/business/customer/' + customerId, method: 'delete' })
}
