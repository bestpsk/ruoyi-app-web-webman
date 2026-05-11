import request from '@/utils/request'

export function listAttendanceRecord(query) {
  return request({ url: '/business/attendance/record/list', method: 'get', params: query })
}

export function getAttendanceRecord(recordId) {
  return request({ url: '/business/attendance/record/' + recordId, method: 'get' })
}

export function listAttendanceRule(query) {
  return request({ url: '/business/attendance/rule/list', method: 'get', params: query })
}

export function getAttendanceRule(ruleId) {
  return request({ url: '/business/attendance/rule/' + ruleId, method: 'get' })
}

export function addAttendanceRule(data) {
  return request({ url: '/business/attendance/rule', method: 'post', data })
}

export function updateAttendanceRule(data) {
  return request({ url: '/business/attendance/rule', method: 'put', data })
}

export function delAttendanceRule(ruleId) {
  return request({ url: '/business/attendance/rule/' + ruleId, method: 'delete' })
}

export function getAttendanceMonthStats(query) {
  return request({ url: '/business/attendance/monthStats', method: 'get', params: query })
}

export function getClockListByRecordId(recordId) {
  return request({ url: '/business/attendance/clockList', method: 'get', params: { record_id: recordId } })
}

export function listAttendanceConfig(query) {
  return request({ url: '/business/attendance/config/list', method: 'get', params: query })
}

export function getAttendanceConfig(configId) {
  return request({ url: '/business/attendance/config/' + configId, method: 'get' })
}

export function addAttendanceConfig(data) {
  return request({ url: '/business/attendance/config', method: 'post', data })
}

export function updateAttendanceConfig(data) {
  return request({ url: '/business/attendance/config', method: 'put', data })
}

export function delAttendanceConfig(configIds) {
  return request({ url: '/business/attendance/config', method: 'delete', data: { configIds } })
}

export function getUserAttendanceRule() {
  return request({ url: '/business/attendance/config/userRule', method: 'get' })
}
