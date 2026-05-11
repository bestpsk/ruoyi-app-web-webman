import request from '@/utils/request'

// 查询企业列表
export function listEnterprise(query) {
  return request({
    url: '/business/enterprise/list',
    method: 'get',
    params: query
  })
}

// 查询企业详细
export function getEnterprise(enterpriseId) {
  return request({
    url: '/business/enterprise/' + enterpriseId,
    method: 'get'
  })
}

// 新增企业
export function addEnterprise(data) {
  return request({
    url: '/business/enterprise',
    method: 'post',
    data: data
  })
}

// 修改企业
export function updateEnterprise(data) {
  return request({
    url: '/business/enterprise',
    method: 'put',
    data: data
  })
}

// 删除企业
export function delEnterprise(enterpriseId) {
  return request({
    url: '/business/enterprise/' + enterpriseId,
    method: 'delete'
  })
}

// 搜索企业（支持拼音首字母）
export function searchEnterprise(keyword) {
  return request({
    url: '/business/enterprise/search',
    method: 'get',
    params: { keyword }
  })
}

// 修改企业状态
export function changeEnterpriseStatus(enterpriseId, status) {
  return request({
    url: '/business/enterprise/status',
    method: 'put',
    data: { enterpriseId, status }
  })
}
