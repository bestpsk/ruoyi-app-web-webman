import request from '@/utils/request'

export function listSchedule(params) {
  return request({ url: '/business/schedule/list', method: 'get', params })
}

export function getScheduleDates(params) {
  return request({
    url: '/business/schedule/dates',
    method: 'get',
    params
  })
}

export function getSchedule(id) {
  return request({ url: '/business/schedule/' + id, method: 'get' })
}

export function getEmployeeSchedule(params) {
  return request({ url: '/business/schedule/employee', method: 'get', params })
}

export function getEnterpriseSchedule(params) {
  return request({ url: '/business/schedule/enterprise', method: 'get', params })
}

export function addSchedule(data) {
  return request({ url: '/business/schedule', method: 'post', data })
}

export function addScheduleBatch(data) {
  return request({ url: '/business/schedule/batch', method: 'post', data })
}

export function updateSchedule(data) {
  return request({ url: '/business/schedule', method: 'put', data })
}

export function delSchedule(ids) {
  return request({ url: '/business/schedule/' + ids, method: 'delete' })
}
