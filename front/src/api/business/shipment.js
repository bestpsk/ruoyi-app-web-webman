import request from '@/utils/request'

export function listShipment(query) {
  return request({ url: '/business/shipment/list', method: 'get', params: query })
}

export function getShipment(shipmentId) {
  return request({ url: '/business/shipment/' + shipmentId, method: 'get' })
}

export function addShipment(data) {
  return request({ url: '/business/shipment', method: 'post', data })
}

export function updateShipment(data) {
  return request({ url: '/business/shipment', method: 'put', data })
}

export function delShipment(shipmentIds) {
  return request({ url: '/business/shipment/' + shipmentIds, method: 'delete' })
}

export function auditShipment(data) {
  return request({ url: '/business/shipment/audit', method: 'put', data })
}

export function shipShipment(data) {
  return request({ url: '/business/shipment/ship', method: 'put', data })
}

export function confirmReceipt(shipmentId) {
  return request({ url: '/business/shipment/confirmReceipt/' + shipmentId, method: 'put' })
}
