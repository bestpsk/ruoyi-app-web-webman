import request from '@/utils/request'

export function listEmployeeConfig(params) {
  return request({ url: '/business/employeeConfig/list', method: 'get', params })
}

export function updateSchedulable(userId, isSchedulable) {
  return request({ url: '/business/employeeConfig/updateSchedulable', method: 'put', params: { userId, isSchedulable } })
}

export function saveRestDates(userId, restDates) {
  return request({ url: '/business/employeeConfig/saveRestDates', method: 'post', data: { userId, restDates } })
}

export function getRestDates(userId) {
  return request({ url: '/business/employeeConfig/getRestDates', method: 'get', params: { userId } })
}
