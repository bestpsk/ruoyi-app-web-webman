/**
 * @description 员工配置API - 员工排班与休息日配置接口
 * @description 提供员工配置列表查询、排班开关更新、休息日保存和查询接口。
 * 排班开关控制员工是否可被排班系统分配班次
 */
import request from '@/utils/request'

/**
 * 分页查询员工配置列表，包含排班状态和休息日信息
 * @param {object} params - 查询参数 { pageNum, pageSize, enterpriseId, storeId }
 * @returns {Promise<object>} 员工配置分页列表
 */
export function listEmployeeConfig(params) {
  return request({ url: '/business/employeeConfig/list', method: 'get', params })
}

/**
 * 更新员工是否可排班状态，关闭后排班系统不会为该员工分配班次
 * @param {string|number} userId - 员工用户ID
 * @param {boolean} schedulable - 是否可排班
 * @returns {Promise<void>}
 */
export function updateSchedulable(userId, schedulable) {
  return request({ url: '/business/employeeConfig/schedulable', method: 'put', data: { userId, schedulable } })
}

/**
 * 批量保存员工休息日期，用于排班系统排除这些日期
 * @param {string|number} userId - 员工用户ID
 * @param {Array<string>} restDates - 休息日期数组，格式为'YYYY-MM-DD'
 * @returns {Promise<void>}
 */
export function saveRestDates(userId, restDates) {
  return request({ url: '/business/employeeConfig/restDates', method: 'post', data: { userId, restDates } })
}

/**
 * 获取员工休息日期列表
 * @param {string|number} userId - 员工用户ID
 * @returns {Promise<Array<string>>} 休息日期数组
 */
export function getRestDates(userId) {
  return request({ url: '/business/employeeConfig/restDates', method: 'get', params: { userId } })
}
