/**
 * @description 客户套餐API - 客户购买的套餐管理接口
 * @description 提供套餐列表查询和按客户ID查询套餐接口。
 * 套餐是客户购买的服务项目组合，包含多次操作次数和有效期
 */
import request from '@/utils/request'

/**
 * 分页查询套餐列表
 * @param {object} params - 查询参数 { pageNum, pageSize, packageName, enterpriseId }
 * @returns {Promise<object>} 套餐分页列表
 */
export function listPackage(params) {
  return request({ url: '/business/package/list', method: 'get', params })
}

/**
 * 根据客户ID和状态查询该客户购买的套餐，用于客户档案中展示持卡信息
 * @param {string|number} customerId - 客户ID
 * @param {string} [status] - 套餐状态筛选（如有效/过期）
 * @returns {Promise<Array>} 客户套餐列表
 */
export function getPackageByCustomer(customerId, status) {
  return request({ url: '/business/package/byCustomer', method: 'get', params: { customerId, status } })
}
