import request from '@/utils/request'

export function listEmployeeConfig(query) {
  return request({
    url: '/business/employeeConfig/list',
    method: 'get',
    params: query
  })
}

export function getEmployeeConfig(configId) {
  return request({
    url: '/business/employeeConfig/' + configId,
    method: 'get'
  })
}

export function addEmployeeConfig(data) {
  return request({
    url: '/business/employeeConfig',
    method: 'post',
    data: data
  })
}

export function updateEmployeeConfig(data) {
  return request({
    url: '/business/employeeConfig',
    method: 'put',
    data: data
  })
}

export function updateSchedulable(userId, isSchedulable) {
  return request({
    url: '/business/employeeConfig/updateSchedulable',
    method: 'put',
    params: { userId, isSchedulable }
  })
}

export function saveRestDates(userId, restDates) {
  return request({
    url: '/business/employeeConfig/saveRestDates',
    method: 'post',
    data: { userId, restDates }
  })
}

export function getRestDates(userId) {
  return request({
    url: '/business/employeeConfig/getRestDates',
    method: 'get',
    params: { userId }
  })
}

export function delEmployeeConfig(configId) {
  return request({
    url: '/business/employeeConfig/' + configId,
    method: 'delete'
  })
}

export function searchEmployee(keyword) {
  return request({
    url: '/business/employeeConfig/search',
    method: 'get',
    params: { keyword }
  })
}
