import request from '@/utils/request'

export function listStore(query) {
  return request({
    url: '/business/store/list',
    method: 'get',
    params: query
  })
}

export function getStore(storeId) {
  return request({
    url: '/business/store/' + storeId,
    method: 'get'
  })
}

export function addStore(data) {
  return request({
    url: '/business/store',
    method: 'post',
    data: data
  })
}

export function updateStore(data) {
  return request({
    url: '/business/store',
    method: 'put',
    data: data
  })
}

export function delStore(storeId) {
  return request({
    url: '/business/store/' + storeId,
    method: 'delete'
  })
}

export function searchStore(keyword, enterpriseId) {
  return request({
    url: '/business/store/search',
    method: 'get',
    params: { keyword, enterpriseId }
  })
}
