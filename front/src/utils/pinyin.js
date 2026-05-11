import { pinyin } from 'pinyin-pro'

/**
 * 获取中文词组的拼音首字母
 * @param {string} chinese - 中文词组
 * @returns {string} 拼音首字母大写
 */
export function getPinyinInitial(chinese) {
  if (!chinese) return ''
  
  const result = pinyin(chinese, {
    pattern: 'first',
    toneType: 'none'
  })
  
  return result.replace(/\s+/g, '').toUpperCase()
}

/**
 * 生成货品编码
 * 格式：拼音首字母-YYYYMMDD
 * @param {string} productName - 货品名称
 * @returns {string} 货品编码
 */
export function generateProductCode(productName) {
  if (!productName) return ''
  
  const initials = getPinyinInitial(productName)
  const date = new Date().toISOString().slice(0, 10).replace(/-/g, '')
  
  return `${initials}-${date}`
}
