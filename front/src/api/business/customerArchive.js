import request from '@/utils/request'

export function listArchive(params) {
  return request({ url: '/business/archive/list', method: 'get', params })
}

export function addArchive(data) {
  return request({ url: '/business/archive/add', method: 'post', data })
}

export function deleteArchive(archiveIds) {
  return request({ url: '/business/archive/' + archiveIds, method: 'delete' })
}
