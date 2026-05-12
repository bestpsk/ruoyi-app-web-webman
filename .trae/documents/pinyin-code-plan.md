# 货品编码拼音优化计划

## 问题分析

### 当前问题
- 输入"美容套盒"，货品编码变成了"美-0aly6D"
- 原因：使用简单的 `charAt(0)` 取首字符，不是真正的拼音首字母

### 用户需求
- 使用主流的拼音插件来生成货品编码
- 输入"美容套盒" → 生成类似 "MRTJ-20260429" 的编码

---

## 解决方案

### 使用主流拼音库：pinyin-pro

**pinyin-pro** 是目前 Vue/React 项目中最常用的拼音转换库：
- ⭐ GitHub 高星项目
- 📦 体积小（约 50KB）
- 🚀 支持多音字
- 💯 API 简单易用

### 编码格式
- **拼音首字母（大写）+ 年月日**
- 示例：`MRTJ-20260429`

---

## 实施步骤

### 步骤1：安装 pinyin-pro
```bash
cd d:\fuchenpro\front
npm install pinyin-pro
```

### 步骤2：创建拼音工具函数
文件：`front/src/utils/pinyin.js`

```javascript
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
```

### 步骤3：修改货品管理页面
文件：`front/src/views/wms/product/index.vue`

```javascript
import { generateProductCode } from '@/utils/pinyin'

function onProductNameBlur() {
  if (!form.value.productCode && form.value.productName) {
    form.value.productCode = generateProductCode(form.value.productName)
    isAutoCode.value = true
  }
}
```

---

## 预期结果

| 输入 | 生成的编码 |
|------|-----------|
| 美容套盒 | MRTJ-20260429 |
| 玻尿酸原液面膜 | BFSYYMR-20260429 |
| 精华液 | JHY-20260429 |
| 补水面膜 | BSMB-20260429 |

---

## 文件修改清单

| 文件 | 操作 |
|------|------|
| `front/package.json` | 添加 pinyin-pro 依赖 |
| `front/src/utils/pinyin.js` | 新建 |
| `front/src/views/wms/product/index.vue` | 修改引入 |
