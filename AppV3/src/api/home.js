import request from '@/utils/request'

export function getHomeData() {
  return request({
    url: '/home/data',
    method: 'get'
  })
}

export function getRecentOrders(params) {
  return request({
    url: '/home/orders',
    method: 'get',
    params
  })
}

export function getTodayStats() {
  return request({
    url: '/home/stats',
    method: 'get'
  })
}
