import request from '@/utils/request'

export function listSalesOrder(query) {
  return request({ url: '/business/sales/list', method: 'get', params: query })
}

export function getSalesOrder(orderId) {
  return request({ url: '/business/sales/' + orderId, method: 'get' })
}

export function addSalesOrder(data) {
  return request({ url: '/business/sales', method: 'post', data })
}

export function updateSalesOrder(data) {
  return request({ url: '/business/sales', method: 'put', data })
}

export function delSalesOrder(orderId) {
  return request({ url: '/business/sales/' + orderId, method: 'delete' })
}

export function enterpriseAudit(orderId) {
  return request({ url: '/business/sales/enterpriseAudit', method: 'post', data: { orderId } })
}

export function financeAudit(orderId) {
  return request({ url: '/business/sales/financeAudit', method: 'post', data: { orderId } })
}
