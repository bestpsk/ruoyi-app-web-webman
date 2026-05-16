/**
 * @description 通用工具函数 - 提示弹窗与参数序列化
 * @description 封装uni-app的Toast和Modal弹窗为Promise风格，以及URL查询参数序列化方法。
 * tansParams支持嵌套对象的序列化（如prop[key]=value格式），与后端参数接收方式对应
 */

/**
 * 显示轻提示弹窗，无图标纯文字展示
 * @param {string} content - 提示文字内容
 */
export function toast(content) {
  uni.showToast({ title: content, icon: 'none' })
}

/**
 * 显示确认弹窗，返回Promise便于async/await调用
 * @param {string} content - 确认弹窗的内容文字
 * @returns {Promise<object>} resolve值为{ confirm: boolean, cancel: boolean }
 */
export function showConfirm(content) {
  return new Promise((resolve, reject) => {
    uni.showModal({
      title: '系统提示',
      content: content,
      cancelText: '取消',
      confirmText: '确定',
      success: function (res) {
        resolve(res)
      },
      fail: function () {
        reject(new Error('弹窗失败'))
      }
    })
  })
}

/**
 * 将对象参数序列化为URL查询字符串（key=value&key=value格式），
 * 支持嵌套对象（转为key[subKey]=value格式），自动过滤空值
 * @param {object} params - 待序列化的参数对象
 * @returns {string} 序列化后的URL查询字符串（末尾带&）
 */
export function tansParams(params) {
  let result = ''
  for (const propName of Object.keys(params)) {
    const value = params[propName]
    var part = encodeURIComponent(propName) + '='
    if (value !== null && value !== '' && typeof value !== 'undefined') {
      if (typeof value === 'object') {
        // 嵌套对象展开为 propName[key]=value 格式，后端可通过@ModelAttribute接收
        for (const key of Object.keys(value)) {
          if (value[key] !== null && value[key] !== '' && typeof value[key] !== 'undefined') {
            let params = propName + '[' + key + ']'
            var subPart = encodeURIComponent(params) + '='
            result += subPart + encodeURIComponent(value[key]) + '&'
          }
        }
      } else {
        result += part + encodeURIComponent(value) + '&'
      }
    }
  }
  return result
}
