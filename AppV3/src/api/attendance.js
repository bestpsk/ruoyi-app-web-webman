import request from '@/utils/request'
import upload from '@/utils/upload'

export function getTodayRecord() {
  return request({ url: '/business/attendance/todayRecord', method: 'get' })
}

export function clock(data) {
  return request({ url: '/business/attendance/clock', method: 'post', data })
}

export function getTodayClockList() {
  return request({ url: '/business/attendance/todayClockList', method: 'get' })
}

export function getClockListByRecordId(recordId) {
  return request({ url: '/business/attendance/clockList', method: 'get', params: { record_id: recordId } })
}

export function clockIn(data) {
  return request({ url: '/business/attendance/clockIn', method: 'post', data })
}

export function clockOut(data) {
  return request({ url: '/business/attendance/clockOut', method: 'post', data })
}

export function getMonthStats(params) {
  return request({ url: '/business/attendance/monthStats', method: 'get', params })
}

export function getRecordList(params) {
  return request({ url: '/business/attendance/record/list', method: 'get', params })
}

export function uploadAttendancePhoto(data) {
  return upload({ url: '/common/upload', name: 'file', filePath: data.filePath })
}

export function getUserAttendanceRule() {
  return request({ url: '/business/attendance/config/userRule', method: 'get' })
}
