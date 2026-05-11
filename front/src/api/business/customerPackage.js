import request from '@/utils/request'

export function listPackage(query) {
  return request({ url: '/business/package/list', method: 'get', params: query })
}

export function getPackage(packageId) {
  return request({ url: '/business/package/' + packageId, method: 'get' })
}

export function getPackageByCustomer(customerId, status) {
  return request({ url: '/business/package/byCustomer', method: 'get', params: { customerId, status } })
}
