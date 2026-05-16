/**
 * @description 门店管理API - 门店信息CRUD与搜索接口
 * @description 提供门店列表查询、详情获取、新增、修改、删除和搜索接口。
 * 搜索接口支持按关键词和企业ID筛选，用于销售开单时快速选择门店
 */
import request from '@/utils/request'

/**
 * 分页查询门店列表
 * @param {object} params - 查询参数 { pageNum, pageSize, enterpriseId, storeName }
 * @returns {Promise<object>} 门店分页列表
 */
export function listStore(params) {
  return request({ url: '/business/store/list', method: 'get', params })
}

/**
 * 根据ID获取门店详细信息
 * @param {string|number} id - 门店ID
 * @returns {Promise<object>} 门店详情
 */
export function getStore(id) {
  return request({ url: '/business/store/' + id, method: 'get' })
}

/**
 * 新增门店
 * @param {object} data - 门店数据 { storeName, enterpriseId, address, phone }
 * @returns {Promise<void>}
 */
export function addStore(data) {
  return request({ url: '/business/store', method: 'post', data })
}

/**
 * 修改门店信息
 * @param {object} data - 门店数据（需包含storeId）
 * @returns {Promise<void>}
 */
export function updateStore(data) {
  return request({ url: '/business/store', method: 'put', data })
}

/**
 * 删除门店，支持批量删除（ids以逗号分隔）
 * @param {string} ids - 门店ID，多个以逗号分隔
 * @returns {Promise<void>}
 */
export function delStore(ids) {
  return request({ url: '/business/store/' + ids, method: 'delete' })
}

/**
 * 搜索门店，支持关键词模糊匹配和企业筛选，用于销售开单时快速选择门店
 * @param {string} keyword - 搜索关键词（匹配门店名称等）
 * @param {string|number} enterpriseId - 企业ID，筛选该企业下的门店
 * @returns {Promise<Array>} 匹配的门店列表
 */
export function searchStore(keyword, enterpriseId) {
  return request({ url: '/business/store/search', method: 'get', params: { keyword, enterpriseId } })
}
