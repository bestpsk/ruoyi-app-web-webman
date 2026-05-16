/**
 * @description 通用工具方法 - 若依框架核心工具函数集
 * @description 提供日期格式化、表单重置、日期范围参数处理、字典标签回显、
 * 树形数据构造、URL参数序列化、路径规范化、Blob验证等通用方法
 * Copyright (c) 2019 ruoyi
 */

/**
 * 日期格式化，支持多种输入格式（时间戳、Date对象、日期字符串）和自定义输出模板
 * @param {string|number|Date} time - 待格式化的时间值，支持10位秒级/13位毫秒级时间戳、Date对象、ISO日期字符串
 * @param {string} [pattern='{y}-{m}-{d} {h}:{i}:{s}'] - 输出格式模板，占位符：{y}年 {m}月 {d}日 {h}时 {i}分 {s}秒 {a}星期
 * @returns {string|null} 格式化后的日期字符串，输入为空时返回null
 */
export function parseTime(time, pattern) {
  if (arguments.length === 0 || !time) {
    return null
  }
  const format = pattern || '{y}-{m}-{d} {h}:{i}:{s}'
  let date
  if (typeof time === 'object') {
    date = time
  } else {
    if ((typeof time === 'string') && (/^[0-9]+$/.test(time))) {
      time = parseInt(time)
    } else if (typeof time === 'string') {
      // 兼容Safari：将'-'替换为'/'，去除T和毫秒
      time = time.replace(new RegExp(/-/gm), '/').replace('T', ' ').replace(new RegExp(/\.[\d]{3}/gm), '')
    }
    if ((typeof time === 'number') && (time.toString().length === 10)) {
      // 10位时间戳为秒级，需乘以1000转为毫秒
      time = time * 1000
    }
    date = new Date(time)
  }
  const formatObj = {
    y: date.getFullYear(),
    m: date.getMonth() + 1,
    d: date.getDate(),
    h: date.getHours(),
    i: date.getMinutes(),
    s: date.getSeconds(),
    a: date.getDay()
  }
  const time_str = format.replace(/{(y|m|d|h|i|s|a)+}/g, (result, key) => {
    let value = formatObj[key]
    // 星期几：getDay()返回0-6，0表示周日
    if (key === 'a') { return ['日', '一', '二', '三', '四', '五', '六'][value] }
    if (result.length > 0 && value < 10) {
      value = '0' + value
    }
    return value || 0
  })
  return time_str
}

/**
 * 重置表单字段值和验证状态，需在Vue组件中通过this调用（依赖this.$refs）
 * @param {string} refName - 表单组件的ref引用名称
 */
export function resetForm(refName) {
  if (this.$refs[refName]) {
    this.$refs[refName].resetFields()
  }
}

/**
 * 将日期范围选择器的值追加到查询参数中，用于后端时间范围筛选
 * @param {object} params - 原始查询参数对象
 * @param {Array<string>} dateRange - 日期范围数组，[开始时间, 结束时间]
 * @param {string} [propName] - 自定义参数名前缀，默认生成beginTime/endTime；指定后生成begin+propName/end+propName
 * @returns {object} 追加了时间范围参数的查询对象
 */
export function addDateRange(params, dateRange, propName) {
  let search = params
  search.params = typeof (search.params) === 'object' && search.params !== null && !Array.isArray(search.params) ? search.params : {}
  dateRange = Array.isArray(dateRange) ? dateRange : []
  if (typeof (propName) === 'undefined') {
    search.params['beginTime'] = dateRange[0]
    search.params['endTime'] = dateRange[1]
  } else {
    search.params['begin' + propName] = dateRange[0]
    search.params['end' + propName] = dateRange[1]
  }
  return search
}

/**
 * 根据字典值查找对应的字典标签（单值匹配），用于表格列中显示字典标签而非原始值
 * @param {Array<{value: string, label: string}>} datas - 字典数据数组
 * @param {string|number} value - 待匹配的字典值
 * @returns {string} 匹配到的字典标签，未匹配则返回原始值
 */
export function selectDictLabel(datas, value) {
  if (value === undefined) {
    return ""
  }
  const actions = []
  Object.keys(datas).some((key) => {
    if (datas[key].value == ('' + value)) {
      actions.push(datas[key].label)
      return true
    }
  })
  if (actions.length === 0) {
    actions.push(value)
  }
  return actions.join('')
}

/**
 * 根据字典值查找对应的字典标签（多值匹配），支持逗号分隔的字符串和数组输入
 * @param {Array<{value: string, label: string}>} datas - 字典数据数组
 * @param {string|Array<string>} value - 待匹配的字典值，支持逗号分隔字符串或数组
 * @param {string} [separator=','] - 多值分隔符，默认逗号
 * @returns {string} 匹配到的字典标签字符串，多个标签以分隔符连接
 */
export function selectDictLabels(datas, value, separator) {
  if (value === undefined || value.length ===0) {
    return ""
  }
  if (Array.isArray(value)) {
    value = value.join(",")
  }
  const actions = []
  const currentSeparator = undefined === separator ? "," : separator
  const temp = value.split(currentSeparator)
  Object.keys(value.split(currentSeparator)).some((val) => {
    let match = false
    Object.keys(datas).some((key) => {
      if (datas[key].value == ('' + temp[val])) {
        actions.push(datas[key].label + currentSeparator)
        match = true
      }
    })
    if (!match) {
      actions.push(temp[val] + currentSeparator)
    }
  })
  return actions.join('').substring(0, actions.join('').length - 1)
}

/**
 * 字符串格式化，将模板中的%s依次替换为传入的参数
 * @param {string} str - 包含%s占位符的模板字符串
 * @returns {string} 格式化后的字符串
 */
export function sprintf(str) {
  let flag = true, i = 1
  str = str.replace(/%s/g, function () {
    const arg = args[i++]
    if (typeof arg === 'undefined') {
      flag = false
      return ''
    }
    return arg
  })
  return flag ? str : ''
}

/**
 * 将undefined/null等空值转换为空字符串，避免页面显示"undefined"或"null"
 * @param {string} str - 待处理的字符串
 * @returns {string} 处理后的字符串，空值返回""
 */
export function parseStrEmpty(str) {
  if (!str || str == "undefined" || str == "null") {
    return ""
  }
  return str
}

/**
 * 递归合并两个对象，target中的属性会覆盖source中的同名属性
 * @param {object} source - 源对象，作为合并基础
 * @param {object} target - 目标对象，其属性会覆盖source中的同名属性
 * @returns {object} 合并后的对象
 */
export function mergeRecursive(source, target) {
  for (const p in target) {
    try {
      if (target[p].constructor == Object) {
        source[p] = mergeRecursive(source[p], target[p])
      } else {
        source[p] = target[p]
      }
    } catch (e) {
      source[p] = target[p]
    }
  }
  return source
}

/**
 * 将扁平数据数组转换为树形结构，常用于部门树、菜单树等层级数据的构建
 * @param {Array<object>} data - 扁平数据源数组
 * @param {string} [id='id'] - 节点ID字段名
 * @param {string} [parentId='parentId'] - 父节点ID字段名
 * @param {string} [children='children'] - 子节点列表字段名
 * @returns {Array<object>} 树形结构数组，顶层节点为没有父节点的元素
 */
export function handleTree(data, id, parentId, children) {
  const config = {
    id: id || 'id',
    parentId: parentId || 'parentId',
    childrenList: children || 'children'
  }

  const childrenListMap = {}
  const tree = []
  // 第一遍遍历：建立ID到节点的映射，并初始化children数组
  for (const d of data) {
    const id = d[config.id]
    childrenListMap[id] = d
    if (!d[config.childrenList]) {
      d[config.childrenList] = []
    }
  }

  // 第二遍遍历：根据parentId将节点挂载到父节点的children中，无父节点的作为顶层节点
  for (const d of data) {
    const parentId = d[config.parentId]
    const parentObj = childrenListMap[parentId]
    if (!parentObj) {
      tree.push(d)
    } else {
      parentObj[config.childrenList].push(d)
    }
  }
  return tree
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
    const part = encodeURIComponent(propName) + "="
    if (value !== null && value !== "" && typeof (value) !== "undefined") {
      if (typeof value === 'object') {
        // 嵌套对象展开为 propName[key]=value 格式
        for (const key of Object.keys(value)) {
          if (value[key] !== null && value[key] !== "" && typeof (value[key]) !== 'undefined') {
            const params = propName + '[' + key + ']'
            const subPart = encodeURIComponent(params) + "="
            result += subPart + encodeURIComponent(value[key]) + "&"
          }
        }
      } else {
        result += part + encodeURIComponent(value) + "&"
      }
    }
  }
  return result
}

/**
 * 规范化路径字符串，去除双斜杠和末尾斜杠
 * @param {string} p - 原始路径字符串
 * @returns {string} 规范化后的路径
 */
export function getNormalPath(p) {
  if (p.length === 0 || !p || p == 'undefined') {
    return p
  }
  let res = p.replace('//', '/')
  if (res[res.length - 1] === '/') {
    return res.slice(0, res.length - 1)
  }
  return res
}

/**
 * 验证响应数据是否为Blob文件类型，用于区分文件下载和JSON错误响应
 * @param {Blob} data - 响应数据对象
 * @returns {boolean} true表示是有效的Blob文件，false表示是JSON错误响应
 */
export function blobValidate(data) {
  return data.type !== 'application/json'
}
