import request from '@/utils/request'

export function listRepayment(params) {
  return request({ url: '/business/repayment/list', method: 'get', params })
}

export function getRepayment(repaymentId) {
  return request({ url: '/business/repayment/' + repaymentId, method: 'get' })
}

export function getOwedPackages(customerId) {
  return request({ url: '/business/repayment/owedPackages', method: 'get', params: { customerId } })
}

export function addRepayment(data) {
  return request({ url: '/business/repayment/add', method: 'post', data })
}

export function auditRepayment(repaymentId) {
  return request({ url: '/business/repayment/audit', method: 'post', data: { repaymentId } })
}

export function cancelRepayment(repaymentId) {
  return request({ url: '/business/repayment/cancel', method: 'post', data: { repaymentId } })
}
