import request from '@/utils/request'

export function stockInSummary(query) {
  return request({ url: '/wms/report/stockInSummary', method: 'get', params: query })
}

export function stockOutSummary(query) {
  return request({ url: '/wms/report/stockOutSummary', method: 'get', params: query })
}

export function inventoryTurnover(query) {
  return request({ url: '/wms/report/inventoryTurnover', method: 'get', params: query })
}

export function productFlow(query) {
  return request({ url: '/wms/report/productFlow', method: 'get', params: query })
}
