/**
 * @description 排班管理API - 员工排班CRUD与查询接口
 * @description 提供排班列表查询、日期查询、详情获取、员工/企业维度排班查询、
 * 单条和批量新增、修改和删除接口
 */
import request from '@/utils/request'

/**
 * 分页查询排班列表
 * @param {object} params - 查询参数 { pageNum, pageSize, enterpriseId, storeId, scheduleDate }
 * @returns {Promise<object>} 排班分页列表
 */
export function listSchedule(params) {
  return request({ url: '/business/schedule/list', method: 'get', params })
}

/**
 * 获取有排班的日期列表，用于日历视图中标记有排班的日期
 * @param {object} params - 查询参数 { yearMonth, enterpriseId, storeId }
 * @returns {Promise<Array<string>>} 有排班的日期数组
 */
export function getScheduleDates(params) {
  return request({
    url: '/business/schedule/dates',
    method: 'get',
    params
  })
}

/**
 * 根据ID获取排班详细信息
 * @param {string|number} id - 排班ID
 * @returns {Promise<object>} 排班详情
 */
export function getSchedule(id) {
  return request({ url: '/business/schedule/' + id, method: 'get' })
}

/**
 * 查询指定员工的排班信息
 * @param {object} params - 查询参数 { userId, yearMonth }
 * @returns {Promise<Array>} 员工排班列表
 */
export function getEmployeeSchedule(params) {
  return request({ url: '/business/schedule/employee', method: 'get', params })
}

/**
 * 查询指定企业的排班信息，用于管理端查看全员排班
 * @param {object} params - 查询参数 { enterpriseId, storeId, yearMonth }
 * @returns {Promise<Array>} 企业排班列表
 */
export function getEnterpriseSchedule(params) {
  return request({ url: '/business/schedule/enterprise', method: 'get', params })
}

/**
 * 新增单条排班记录
 * @param {object} data - 排班数据 { userId, scheduleDate, shiftType, startTime, endTime }
 * @returns {Promise<void>}
 */
export function addSchedule(data) {
  return request({ url: '/business/schedule', method: 'post', data })
}

/**
 * 批量新增排班记录，一次提交多个员工或多天的排班数据
 * @param {object} data - 批量排班数据 { schedules: Array }
 * @returns {Promise<void>}
 */
export function addScheduleBatch(data) {
  return request({ url: '/business/schedule/batch', method: 'post', data })
}

/**
 * 修改排班信息
 * @param {object} data - 排班数据（需包含scheduleId）
 * @returns {Promise<void>}
 */
export function updateSchedule(data) {
  return request({ url: '/business/schedule', method: 'put', data })
}

/**
 * 删除排班，支持批量删除（ids以逗号分隔）
 * @param {string} ids - 排班ID，多个以逗号分隔
 * @returns {Promise<void>}
 */
export function delSchedule(ids) {
  return request({ url: '/business/schedule/' + ids, method: 'delete' })
}
