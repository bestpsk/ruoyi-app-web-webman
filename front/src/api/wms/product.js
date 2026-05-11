import request from '@/utils/request'

export function listProduct(query) {
  return request({ url: '/wms/product/list', method: 'get', params: query })
}

export function getProduct(productId) {
  return request({ url: '/wms/product/' + productId, method: 'get' })
}

export function addProduct(data) {
  return request({ url: '/wms/product', method: 'post', data: data })
}

export function updateProduct(data) {
  return request({ url: '/wms/product', method: 'put', data: data })
}

export function delProduct(productId) {
  return request({ url: '/wms/product/' + productId, method: 'delete' })
}

export function searchProduct(keyword) {
  return request({ url: '/wms/product/search', method: 'get', params: { keyword } })
}
