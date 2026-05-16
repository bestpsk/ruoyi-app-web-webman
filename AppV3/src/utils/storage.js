/**
 * @description 本地存储管理 - 用户数据聚合存储
 * @description 将用户相关数据（头像、ID、姓名、角色、权限）聚合到一个storage_data键下统一管理，
 * 避免在uni-app本地存储中创建过多独立键值对。仅允许预定义的key进行存储操作，防止数据污染
 */
import constant from './constant'

// 聚合存储的键名，所有用户数据存储在此键下的对象中
let storageKey = 'storage_data'
// 允许存储的key白名单，不在白名单中的key将被忽略
let storageNodeKeys = [constant.avatar, constant.id, constant.name, constant.roles, constant.permissions]

const storage = {
  /**
   * 将用户数据写入聚合存储，仅接受白名单中的key
   * @param {string} key - 存储键名，必须为constant中定义的常量
   * @param {*} value - 存储值，可为任意类型
   */
  set(key, value) {
    if (storageNodeKeys.indexOf(key) !== -1) {
      let tmp = uni.getStorageSync(storageKey)
      tmp = tmp ? tmp : {}
      tmp[key] = value
      uni.setStorageSync(storageKey, tmp)
    }
  },
  /**
   * 从聚合存储中读取指定key的值
   * @param {string} key - 存储键名
   * @returns {*} 存储的值，未找到时返回空字符串
   */
  get(key) {
    let storageData = uni.getStorageSync(storageKey) || {}
    return storageData[key] || ''
  },
  /**
   * 从聚合存储中删除指定key的值
   * @param {string} key - 要删除的存储键名
   */
  remove(key) {
    let storageData = uni.getStorageSync(storageKey) || {}
    delete storageData[key]
    uni.setStorageSync(storageKey, storageData)
  },
  /**
   * 清空整个聚合存储，退出登录时调用
   */
  clean() {
    uni.removeStorageSync(storageKey)
  }
}

export default storage
