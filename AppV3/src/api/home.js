/**
 * @description 首页数据API - 首页汇总数据与统计接口
 * @description 提供首页汇总数据、最近订单列表和今日统计数据接口
 */
import request from '@/utils/request'

/**
 * 获取首页汇总数据，包括订单数量、客户数量等概览信息
 * @returns {Promise<object>} 首页汇总数据
 */
export function getHomeData() {
  return request({
    url: '/home/data',
    method: 'get'
  })
}

/**
 * 获取最近订单列表，用于首页订单快捷展示
 * @param {object} params - 查询参数（分页等）
 * @returns {Promise<object>} 订单列表数据
 */
export function getRecentOrders(params) {
  return request({
    url: '/home/orders',
    method: 'get',
    params
  })
}

/**
 * 获取今日统计数据，包括今日订单数、销售额等
 * @returns {Promise<object>} 今日统计数据
 */
export function getTodayStats() {
  return request({
    url: '/home/stats',
    method: 'get'
  })
}
