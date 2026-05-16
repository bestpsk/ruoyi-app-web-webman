/**
 * @description 档案管理API - 客户档案管理接口
 * @description 提供档案列表查询、新增和删除接口。
 * 档案用于存储客户的文件资料（如合同、照片等附件）
 */
import request from '@/utils/request'

/**
 * 分页查询档案列表
 * @param {object} params - 查询参数 { pageNum, pageSize, customerId, archiveType }
 * @returns {Promise<object>} 档案分页列表
 */
export function listArchive(params) {
  return request({ url: '/business/archive/list', method: 'get', params })
}

/**
 * 新增档案记录，关联客户和上传的文件
 * @param {object} data - 档案数据 { customerId, archiveName, archiveType, fileUrl }
 * @returns {Promise<void>}
 */
export function addArchive(data) {
  return request({ url: '/business/archive', method: 'post', data })
}

/**
 * 删除档案
 * @param {string|number} id - 档案ID
 * @returns {Promise<void>}
 */
export function deleteArchive(id) {
  return request({ url: '/business/archive/' + id, method: 'delete' })
}
