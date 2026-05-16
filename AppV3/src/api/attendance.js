/**
 * @description 考勤打卡API - 考勤记录与打卡操作接口
 * @description 提供考勤记录查询、上下班打卡、月度统计、考勤照片上传和用户考勤规则查询等接口。
 * 打卡接口支持携带位置信息和照片，照片上传使用upload工具而非request
 */
import request from '@/utils/request'
import upload from '@/utils/upload'

/**
 * 获取今日考勤记录，包含上下班打卡时间和状态
 * @returns {Promise<object>} 今日考勤记录
 */
export function getTodayRecord() {
  return request({ url: '/business/attendance/todayRecord', method: 'get' })
}

/**
 * 通用打卡接口，提交打卡位置和照片信息
 * @param {object} data - 打卡数据 { latitude, longitude, address, photoUrl }
 * @returns {Promise<object>} 打卡结果
 */
export function clock(data) {
  return request({ url: '/business/attendance/clock', method: 'post', data })
}

/**
 * 获取今日打卡记录列表，包含所有打卡时间点
 * @returns {Promise<Array>} 今日打卡列表
 */
export function getTodayClockList() {
  return request({ url: '/business/attendance/todayClockList', method: 'get' })
}

/**
 * 根据考勤记录ID获取该记录下的所有打卡明细
 * @param {string|number} recordId - 考勤记录ID
 * @returns {Promise<Array>} 打卡明细列表
 */
export function getClockListByRecordId(recordId) {
  return request({ url: '/business/attendance/clockList', method: 'get', params: { record_id: recordId } })
}

/**
 * 上班打卡，记录上班时间和位置
 * @param {object} data - 打卡数据 { latitude, longitude, address }
 * @returns {Promise<object>} 打卡结果
 */
export function clockIn(data) {
  return request({ url: '/business/attendance/clockIn', method: 'post', data })
}

/**
 * 下班打卡，记录下班时间和位置
 * @param {object} data - 打卡数据 { latitude, longitude, address }
 * @returns {Promise<object>} 打卡结果
 */
export function clockOut(data) {
  return request({ url: '/business/attendance/clockOut', method: 'post', data })
}

/**
 * 获取月度考勤统计，包含出勤天数、迟到次数等汇总数据
 * @param {object} params - 查询参数 { yearMonth, enterpriseId, storeId }
 * @returns {Promise<object>} 月度统计数据
 */
export function getMonthStats(params) {
  return request({ url: '/business/attendance/monthStats', method: 'get', params })
}

/**
 * 获取考勤记录列表，支持按月份和企业筛选
 * @param {object} params - 查询参数 { yearMonth, enterpriseId, storeId, pageNum, pageSize }
 * @returns {Promise<object>} 考勤记录分页列表
 */
export function getRecordList(params) {
  return request({ url: '/business/attendance/record/list', method: 'get', params })
}

/**
 * 上传考勤照片到服务器，使用uni.uploadFile实现文件上传
 * @param {object} data - 上传参数 { filePath: 本地文件路径 }
 * @returns {Promise<{url: string, fileName: string}>} 上传成功返回文件URL和名称
 */
export function uploadAttendancePhoto(data) {
  return upload({ url: '/common/upload', name: 'file', filePath: data.filePath })
}

/**
 * 获取当前用户的考勤规则，包括上下班时间、迟到阈值等配置
 * @returns {Promise<object>} 用户考勤规则
 */
export function getUserAttendanceRule() {
  return request({ url: '/business/attendance/config/userRule', method: 'get' })
}
