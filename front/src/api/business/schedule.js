import request from '@/utils/request'

export function listSchedule(query) {
  return request({
    url: '/business/schedule/list',
    method: 'get',
    params: query
  })
}

export function getSchedule(scheduleId) {
  return request({
    url: '/business/schedule/' + scheduleId,
    method: 'get'
  })
}

export function getScheduleCalendar(query) {
  return request({
    url: '/business/schedule/calendar',
    method: 'get',
    params: query
  })
}

export function getEmployeeSchedule(query) {
  return request({
    url: '/business/schedule/employee',
    method: 'get',
    params: query
  })
}

export function getEnterpriseSchedule(query) {
  return request({
    url: '/business/schedule/enterprise',
    method: 'get',
    params: query
  })
}

export function addSchedule(data) {
  return request({
    url: '/business/schedule',
    method: 'post',
    data: data
  })
}

export function addScheduleBatch(data) {
  return request({
    url: '/business/schedule/batch',
    method: 'post',
    data: data
  })
}

export function updateSchedule(data) {
  return request({
    url: '/business/schedule',
    method: 'put',
    data: data
  })
}

export function delSchedule(scheduleId) {
  return request({
    url: '/business/schedule/' + scheduleId,
    method: 'delete'
  })
}
