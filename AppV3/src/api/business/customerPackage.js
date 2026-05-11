import request from '@/utils/request'

export function listPackage(params) {
  return request({ url: '/business/package/list', method: 'get', params })
}

export function getPackageByCustomer(customerId, status) {
  return request({ url: '/business/package/byCustomer', method: 'get', params: { customerId, status } })
}
