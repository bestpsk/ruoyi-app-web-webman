# 持卡明细列表优化计划

## 需求
1. **添加备注列**：持卡明细表格增加"备注"列显示品项备注信息
2. **默认隐藏用完项目**：剩余次数为0的项目默认不显示在列表中
3. **显示用完切换按钮**：体验操作按钮左侧添加"显示用完"按钮，点击后展示剩余次数为0的项目

## 当前代码结构

**持卡明细表格** (第136-145行)：
- 数据源：`allPackageItems` 计算属性（合并所有套餐品项）
- 列：选择框 | 套餐 | 品项 | 方案总价 | 单次价 | 总次数 | 已用 | 剩余
- 选择条件：`:selectable="row => row.remainingQuantity > 0"`（剩余>0才可勾选）
- 体验操作按钮位置：卡片 header 右侧 (第132行)

**数据来源**：
- `biz_package_item.remark` 字段存在（varchar 500）

## 实施步骤

### 步骤1：添加备注列
在"剩余"列后面添加备注列：
```vue
<el-table-column label="备注" prop="remark" min-width="100" show-overflow-tooltip />
```

### 步骤2：新增过滤状态变量
在 script 中新增响应式变量控制是否显示已用完项目：
```js
const showExhaustedItems = ref(false)
```

### 步骤3：修改 allPackageItems 计算属性
根据 `showExhaustedItems` 过滤数据：
```js
const allPackageItems = computed(() => {
  const items = []
  packageList.value.forEach(pkg => {
    ;(pkg.items || []).forEach(item => {
      if (!showExhaustedItems.value && item.remainingQuantity <= 0) return
      items.push({
        ...item,
        packageId: pkg.packageId,
        packageName: pkg.packageName,
        packageNo: pkg.packageNo
      })
    })
  })
  return items
})
```

### 步骤4：添加"显示用完"切换按钮
在体验操作按钮左侧添加切换按钮：
```vue
<el-button :type="showExhaustedItems ? 'warning' : 'info'" plain size="small"
  @click="showExhaustedItems = !showExhaustedItems">
  {{ showExhaustedItems ? '隐藏用完' : '显示用完' }}
</el-button>
<el-button type="primary" plain @click="showTrialOperation">+ 体验操作</el-button>
```
两个按钮放在 `panel-header` 中，使用 flex 布局。

## 涉及文件
- `front/src/views/business/sales/index.vue`
  - 模板第136-145行（添加备注列）
  - 模板第130-133行（header区域添加切换按钮）
  - Script 第464行附近（allPackageItems 计算属性 + 新增变量）
