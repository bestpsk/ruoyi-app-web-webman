/**
 * @description 销售订单API - 订单CRUD与审核接口
 * @description 提供销售订单的列表查询、详情获取、新增、修改、删除接口，
 * 以及企业审核和财务审核两个审批流程接口
 */
import request from '@/utils/request'

/**
 * 分页查询销售订单列表
 * @param {object} params - 查询参数 { pageNum, pageSize, enterpriseId, storeId, orderStatus }
 * @returns {Promise<object>} 订单分页列表
 */
export function listSalesOrder(params) {
  return request({ url: '/business/sales/list', method: 'get', params })
}

/**
 * 根据ID获取销售订单详细信息，包含订单项和客户信息
 * @param {string|number} id - 订单ID
 * @returns {Promise<object>} 订单详情
 */
export function getSalesOrder(id) {
  return request({ url: '/business/sales/' + id, method: 'get' })
}

/**
 * 新增销售订单，提交订单基本信息和订单项列表
 * @param {object} data - 订单数据 { customerId, storeId, items, totalAmount, remark }
 * @returns {Promise<void>}
 */
export function addSalesOrder(data) {
  return request({ url: '/business/sales', method: 'post', data })
}

/**
 * 修改销售订单信息
 * @param {object} data - 订单数据（需包含orderId）
 * @returns {Promise<void>}
 */
export function updateSalesOrder(data) {
  return request({ url: '/business/sales', method: 'put', data })
}

/**
 * 删除销售订单
 * @param {string|number} id - 订单ID
 * @returns {Promise<void>}
 */
export function delSalesOrder(id) {
  return request({ url: '/business/sales/' + id, method: 'delete' })
}

/**
 * 企业审核销售订单，审核通过后订单进入财务审核阶段
 * @param {string|number} orderId - 订单ID
 * @returns {Promise<void>}
 */
export function enterpriseAudit(orderId) {
  return request({ url: '/business/sales/enterpriseAudit', method: 'post', data: { orderId } })
}

/**
 * 财务审核销售订单，审核通过后订单完成确认
 * @param {string|number} orderId - 订单ID
 * @returns {Promise<void>}
 */
export function financeAudit(orderId) {
  return request({ url: '/business/sales/financeAudit', method: 'post', data: { orderId } })
}
