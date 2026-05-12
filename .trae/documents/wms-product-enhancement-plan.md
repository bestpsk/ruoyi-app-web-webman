# 货品管理功能增强与问题修复计划

## 问题分析

### 问题1：数据库表结构缺失列
**错误信息**：
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'pack_qty'
```

**原因**：升级脚本中的 `ALTER TABLE biz_product ADD COLUMN pack_qty` 可能未成功执行。

### 问题2：新功能需求
1. 货品编码：根据品名首字母自动生成（可修改）
2. 单位字段名改为"单位（整）"
3. 规格字段名改为"规格（拆）"
4. 出货价默认等于进货价
5. 出货价（拆）= 出货价 ÷ 包装数量，自动算出（可修改）

---

## 解决方案

### 第1步：修复数据库表结构

检查并添加缺失的列到 `biz_product` 表：

```sql
-- 检查列是否存在
SHOW COLUMNS FROM biz_product LIKE 'pack_qty';
SHOW COLUMNS FROM biz_product LIKE 'sale_price_spec';

-- 如果不存在则添加
ALTER TABLE `biz_product` ADD COLUMN `pack_qty` int DEFAULT 1 COMMENT '换算比例' AFTER `spec`;
ALTER TABLE `biz_product` ADD COLUMN `sale_price_spec` decimal(10,2) DEFAULT 0.00 COMMENT '出货价(按副单位)' AFTER `sale_price`;
ALTER TABLE `biz_product` ADD COLUMN `product_code` varchar(50) NOT NULL COMMENT '货品编码' AFTER `product_name`;

-- 确保 unit 和 spec 字段类型正确
ALTER TABLE `biz_product` MODIFY COLUMN `unit` char(1) DEFAULT NULL COMMENT '主单位(字典biz_product_unit)';
ALTER TABLE `biz_product` MODIFY COLUMN `spec` char(1) DEFAULT NULL COMMENT '副单位/规格(字典biz_product_spec)';
```

### 第2步：修改前端货品管理页面

#### 2.1 货品编码自动生成
```javascript
// 品名转首字母缩写
function generateProductCode(productName) {
  if (!productName) return ''
  const pinyin = getPinyinShort(productName)  // 需要引入拼音库
  return pinyin.toUpperCase() + '-' + Date.now().toString(36).slice(-3)
}

// 或者简化为取首字母
function generateProductCode(productName) {
  if (!productName) return ''
  return productName.charAt(0).toUpperCase() + '-' + Math.random().toString(36).slice(-4)
}
```

#### 2.2 字段名称调整
- "单位" → "单位（整）"
- "规格" → "规格（拆）"

#### 2.3 价格联动逻辑
```javascript
// 品名填写时自动生成编码
watch(() => form.productName, (newVal) => {
  if (!form.productCode) {  // 只在编码为空时自动生成
    form.productCode = generateProductCode(newVal)
  }
})

// 进货价变化时，出货价自动同步
watch(() => form.purchasePrice, (newVal) => {
  if (form.salePrice === 0 || form.salePrice === form._originalSalePrice) {
    form.salePrice = newVal
    form._originalSalePrice = newVal
  }
})

// 出货价或包装数量变化时，自动计算出货价（拆）
watch([() => form.salePrice, () => form.packQty], ([salePrice, packQty]) => {
  if (packQty && packQty > 0) {
    form.salePriceSpec = Math.round((salePrice / packQty) * 100) / 100
  }
})
```

---

## 实施步骤清单

- [ ] **步骤1**: 创建数据库修复SQL脚本
  - 检查并添加缺失的列
  - 确保字段类型正确

- [ ] **步骤2**: 执行数据库修复脚本
  - 验证表结构正确

- [ ] **步骤3**: 修改前端货品管理页面
  - 货品编码自动生成
  - 单位/规格标签添加"（整）"/"（拆）"
  - 出货价与进货价联动
  - 出货价（拆）自动计算

- [ ] **步骤4**: 测试验证
  - 添加货品功能测试
  - 价格联动测试

---

## 文件修改清单

| 文件 | 修改内容 |
|------|----------|
| `front/src/views/wms/product/index.vue` | 功能增强 |
| `webman/sql/fix_product_columns.sql` | 新增SQL脚本 |

---

## 预期结果

1. ✅ 添加货品不再报 SQL 错误
2. ✅ 货品编码可自动生成
3. ✅ 单位显示为"单位（整）"，规格显示为"规格（拆）"
4. ✅ 出货价默认等于进货价
5. ✅ 出货价（拆）自动计算，可手动修改
