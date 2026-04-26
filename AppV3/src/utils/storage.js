import constant from './constant'

let storageKey = 'storage_data'
let storageNodeKeys = [constant.avatar, constant.id, constant.name, constant.roles, constant.permissions]

const storage = {
  set(key, value) {
    if (storageNodeKeys.indexOf(key) !== -1) {
      let tmp = uni.getStorageSync(storageKey)
      tmp = tmp ? tmp : {}
      tmp[key] = value
      uni.setStorageSync(storageKey, tmp)
    }
  },
  get(key) {
    let storageData = uni.getStorageSync(storageKey) || {}
    return storageData[key] || ''
  },
  remove(key) {
    let storageData = uni.getStorageSync(storageKey) || {}
    delete storageData[key]
    uni.setStorageSync(storageKey, storageData)
  },
  clean() {
    uni.removeStorageSync(storageKey)
  }
}

export default storage
