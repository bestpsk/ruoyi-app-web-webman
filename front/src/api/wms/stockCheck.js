import request from '@/utils/request'

export function listStockCheck(query) {
  return request({ url: '/wms/stockCheck/list', method: 'get', params: query })
}

export function getStockCheck(stockCheckId) {
  return request({ url: '/wms/stockCheck/' + stockCheckId, method: 'get' })
}

export function addStockCheck(data) {
  return request({ url: '/wms/stockCheck', method: 'post', data: data })
}

export function updateStockCheck(data) {
  return request({ url: '/wms/stockCheck', method: 'put', data: data })
}

export function delStockCheck(stockCheckId) {
  return request({ url: '/wms/stockCheck/' + stockCheckId, method: 'delete' })
}

export function confirmStockCheck(id) {
  return request({ url: '/wms/stockCheck/confirm/' + id, method: 'put' })
}

export function loadInventoryData() {
  return request({ url: '/wms/stockCheck/loadInventory', method: 'get' })
}
