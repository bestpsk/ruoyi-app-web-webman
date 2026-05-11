import request from '@/utils/request'

export function listStockOut(query) {
  return request({ url: '/wms/stockOut/list', method: 'get', params: query })
}

export function getStockOut(stockOutId) {
  return request({ url: '/wms/stockOut/' + stockOutId, method: 'get' })
}

export function addStockOut(data) {
  return request({ url: '/wms/stockOut', method: 'post', data: data })
}

export function updateStockOut(data) {
  return request({ url: '/wms/stockOut', method: 'put', data: data })
}

export function delStockOut(stockOutId) {
  return request({ url: '/wms/stockOut/' + stockOutId, method: 'delete' })
}

export function confirmStockOut(id) {
  return request({ url: '/wms/stockOut/confirm/' + id, method: 'put' })
}

export function confirmStockOutById(stockOutId) {
  return request({ url: '/wms/stockOut/confirm/' + stockOutId, method: 'put' })
}

export function cancelConfirmStockOut(stockOutId) {
  return request({ url: '/wms/stockOut/cancelConfirm/' + stockOutId, method: 'put' })
}
