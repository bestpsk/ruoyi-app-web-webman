import request from '@/utils/request'

export function listSalesOrder(params) {
  return request({ url: '/business/sales/list', method: 'get', params })
}

export function getSalesOrder(id) {
  return request({ url: '/business/sales/' + id, method: 'get' })
}

export function addSalesOrder(data) {
  return request({ url: '/business/sales', method: 'post', data })
}

export function updateSalesOrder(data) {
  return request({ url: '/business/sales', method: 'put', data })
}

export function delSalesOrder(id) {
  return request({ url: '/business/sales/' + id, method: 'delete' })
}

export function enterpriseAudit(orderId) {
  return request({ url: '/business/sales/enterpriseAudit', method: 'post', data: { orderId } })
}

export function financeAudit(orderId) {
  return request({ url: '/business/sales/financeAudit', method: 'post', data: { orderId } })
}
