import request from '@/utils/request'

export function listStockIn(query) {
  return request({ url: '/wms/stockIn/list', method: 'get', params: query })
}

export function getStockIn(stockInId) {
  return request({ url: '/wms/stockIn/' + stockInId, method: 'get' })
}

export function addStockIn(data) {
  return request({ url: '/wms/stockIn', method: 'post', data: data })
}

export function updateStockIn(data) {
  return request({ url: '/wms/stockIn', method: 'put', data: data })
}

export function delStockIn(stockInId) {
  return request({ url: '/wms/stockIn/' + stockInId, method: 'delete' })
}

export function confirmStockIn(id) {
  return request({ url: '/wms/stockIn/confirm/' + id, method: 'put' })
}

export function confirmStockInById(stockInId) {
  return request({ url: '/wms/stockIn/confirm/' + stockInId, method: 'put' })
}

export function cancelConfirmStockIn(stockInId) {
  return request({ url: '/wms/stockIn/cancelConfirm/' + stockInId, method: 'put' })
}
