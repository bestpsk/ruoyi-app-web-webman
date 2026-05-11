import request from '@/utils/request'

export function listArchive(params) {
  return request({ url: '/business/archive/list', method: 'get', params })
}
