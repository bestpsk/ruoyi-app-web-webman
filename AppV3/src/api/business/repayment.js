/**
 * @description 还款管理API - 客户还款记录与审核接口
 * @description 提供还款列表查询、详情获取、欠款套餐查询、新增还款、审核还款和取消还款接口。
 * 还款用于记录客户对欠款套餐的分期付款，审核流程确保还款记录的准确性
 */
import request from '@/utils/request'

/**
 * 分页查询还款记录列表
 * @param {object} params - 查询参数 { pageNum, pageSize, customerId, status }
 * @returns {Promise<object>} 还款分页列表
 */
export function listRepayment(params) {
  return request({ url: '/business/repayment/list', method: 'get', params })
}

/**
 * 根据ID获取还款详细信息
 * @param {string|number} id - 还款ID
 * @returns {Promise<object>} 还款详情
 */
export function getRepayment(id) {
  return request({ url: '/business/repayment/' + id, method: 'get' })
}

/**
 * 获取客户欠款的套餐列表，用于新增还款时选择要还款的套餐
 * @param {string|number} customerId - 客户ID
 * @returns {Promise<Array>} 欠款套餐列表
 */
export function getOwedPackages(customerId) {
  return request({ url: '/business/repayment/owedPackages', method: 'get', params: { customerId } })
}

/**
 * 新增还款记录，记录客户对某套餐的还款操作
 * @param {object} data - 还款数据 { customerId, packageId, amount, paymentMethod, remark }
 * @returns {Promise<void>}
 */
export function addRepayment(data) {
  return request({ url: '/business/repayment', method: 'post', data })
}

/**
 * 审核还款记录，确认还款金额和信息的准确性
 * @param {string|number} id - 还款ID
 * @returns {Promise<void>}
 */
export function auditRepayment(id) {
  return request({ url: '/business/repayment/audit', method: 'put', data: { id } })
}

/**
 * 取消还款记录，用于撤销错误的还款操作
 * @param {string|number} id - 还款ID
 * @returns {Promise<void>}
 */
export function cancelRepayment(id) {
  return request({ url: '/business/repayment/cancel', method: 'put', data: { id } })
}
