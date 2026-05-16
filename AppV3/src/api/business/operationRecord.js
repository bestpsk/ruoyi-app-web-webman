/**
 * @description 操作记录API - 客户操作记录管理接口
 * @description 提供操作记录的列表查询、新增、删除和详情获取接口。
 * 操作记录用于记录对客户执行的各种业务操作（如持卡操作、消费记录等）
 */
import request from '@/utils/request'

/**
 * 分页查询操作记录列表
 * @param {object} params - 查询参数 { pageNum, pageSize, customerId, operationType }
 * @returns {Promise<object>} 操作记录分页列表
 */
export function listOperation(params) {
  return request({ url: '/business/operation/list', method: 'get', params })
}

/**
 * 新增操作记录，记录对客户执行的业务操作
 * @param {object} data - 操作记录数据 { customerId, operationType, amount, remark }
 * @returns {Promise<void>}
 */
export function addOperation(data) {
  return request({ url: '/business/operation', method: 'post', data })
}

/**
 * 删除操作记录
 * @param {string|number} id - 操作记录ID
 * @returns {Promise<void>}
 */
export function delOperation(id) {
  return request({ url: '/business/operation/' + id, method: 'delete' })
}

/**
 * 根据ID获取操作记录详细信息
 * @param {string|number} id - 操作记录ID
 * @returns {Promise<object>} 操作记录详情
 */
export function getOperationRecord(id) {
  return request({ url: '/business/operation/' + id, method: 'get' })
}
