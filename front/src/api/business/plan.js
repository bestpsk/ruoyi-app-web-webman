import request from '@/utils/request'

export function listEnterprise(query) {
  return request({ url: '/business/plan/enterpriseList', method: 'get', params: query })
}

export function listPlan(query) {
  return request({ url: '/business/plan/list', method: 'get', params: query })
}

export function getPlan(planId) {
  return request({ url: '/business/plan/' + planId, method: 'get' })
}

export function addPlan(data) {
  return request({ url: '/business/plan', method: 'post', data })
}

export function updatePlan(data) {
  return request({ url: '/business/plan', method: 'put', data })
}

export function delPlan(planIds) {
  return request({ url: '/business/plan/' + planIds, method: 'delete' })
}

export function submitAuditPlan(planId) {
  return request({ url: '/business/plan/submitAudit/' + planId, method: 'put' })
}

export function auditPlan(data) {
  return request({ url: '/business/plan/audit', method: 'put', data })
}

export function changePlanStatus(planId, status) {
  return request({ url: '/business/plan/changeStatus', method: 'put', data: { planId, status } })
}
