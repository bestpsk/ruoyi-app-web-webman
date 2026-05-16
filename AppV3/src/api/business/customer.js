/**
 * @description 客户管理API - 客户信息CRUD与搜索接口
 * @description 提供客户列表查询、详情获取、关键词搜索、新增、修改和删除接口。
 * 搜索接口支持按关键词、企业ID和门店ID组合筛选
 */
import request from '@/utils/request'

/**
 * 分页查询客户列表
 * @param {object} params - 查询参数 { pageNum, pageSize, enterpriseId, storeId, customerName }
 * @returns {Promise<object>} 客户分页列表
 */
export function listCustomer(params) {
  return request({ url: '/business/customer/list', method: 'get', params })
}

/**
 * 根据ID获取客户详细信息
 * @param {string|number} id - 客户ID
 * @returns {Promise<object>} 客户详情
 */
export function getCustomer(id) {
  return request({ url: '/business/customer/' + id, method: 'get' })
}

/**
 * 搜索客户，支持关键词模糊匹配和企业门店筛选，用于销售开单时快速选择客户
 * @param {string} keyword - 搜索关键词（匹配客户姓名、手机号等）
 * @param {string|number} enterpriseId - 企业ID
 * @param {string|number} storeId - 门店ID
 * @returns {Promise<Array>} 匹配的客户列表
 */
export function searchCustomer(keyword, enterpriseId, storeId) {
  return request({ url: '/business/customer/search', method: 'get', params: { keyword, enterpriseId, storeId } })
}

/**
 * 新增客户，提交客户基本信息（姓名、性别、年龄、标签、备注等）
 * @param {object} data - 客户数据 { customerName, gender, age, tag, remark, enterpriseId, storeId }
 * @returns {Promise<void>}
 */
export function addCustomer(data) {
  return request({ url: '/business/customer', method: 'post', data })
}

/**
 * 修改客户信息
 * @param {object} data - 客户数据（需包含customerId）
 * @returns {Promise<void>}
 */
export function updateCustomer(data) {
  return request({ url: '/business/customer', method: 'put', data })
}

/**
 * 删除客户
 * @param {string|number} id - 客户ID
 * @returns {Promise<void>}
 */
export function delCustomer(id) {
  return request({ url: '/business/customer/' + id, method: 'delete' })
}
