/**
 * @description 字典数据API - 系统字典数据查询接口
 * @description 提供根据字典类型查询字典数据的接口，用于下拉框、标签等组件的数据源。
 * 字典数据由后端统一管理，前端通过dictType获取对应的数据列表
 */
import request from '@/utils/request'

/**
 * 根据字典类型获取字典数据列表，返回该类型下所有字典项（如性别、状态等枚举值）
 * @param {string} dictType - 字典类型编码，如'sys_user_sex'、'sys_normal_disable'
 * @returns {Promise<Array<{dictValue: string, dictLabel: string, dictSort: number}>>} 字典数据列表
 */
export function getDicts(dictType) {
  return request({
    url: '/system/dict/data/type/' + dictType,
    method: 'get'
  })
}
