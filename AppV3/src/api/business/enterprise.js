/**
 * @description 企业管理API - 企业信息CRUD接口
 * @description 提供企业列表查询、详情获取、新增、修改和删除接口
 */
import request from '@/utils/request'

/**
 * 分页查询企业列表
 * @param {object} params - 查询参数 { pageNum, pageSize, enterpriseName }
 * @returns {Promise<object>} 企业分页列表
 */
export function listEnterprise(params) {
  return request({
    url: '/business/enterprise/list',
    method: 'get',
    params
  })
}

/**
 * 根据ID获取企业详细信息
 * @param {string|number} id - 企业ID
 * @returns {Promise<object>} 企业详情
 */
export function getEnterprise(id) {
  return request({
    url: '/business/enterprise/' + id,
    method: 'get'
  })
}

/**
 * 新增企业
 * @param {object} data - 企业数据 { enterpriseName, contactName, phone, address }
 * @returns {Promise<void>}
 */
export function addEnterprise(data) {
  return request({
    url: '/business/enterprise',
    method: 'post',
    data
  })
}

/**
 * 修改企业信息
 * @param {object} data - 企业数据（需包含enterpriseId）
 * @returns {Promise<void>}
 */
export function updateEnterprise(data) {
  return request({
    url: '/business/enterprise',
    method: 'put',
    data
  })
}

/**
 * 删除企业，支持批量删除（ids以逗号分隔）
 * @param {string} ids - 企业ID，多个以逗号分隔
 * @returns {Promise<void>}
 */
export function delEnterprise(ids) {
  return request({
    url: '/business/enterprise/' + ids,
    method: 'delete'
  })
}
