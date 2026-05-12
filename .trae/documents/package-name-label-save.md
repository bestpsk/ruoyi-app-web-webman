# 套餐名称标题与保存计划

## 需求
1. **添加标题**：给"套餐名称"输入框前面加上标签文字"套餐名称："
2. **保存到订单**：提交订单时将 `orderPackageName` 的值同步保存到订单记录中

## 当前状态
- 第72行：只有 `el-input` + placeholder，无标题标签
- 第575-584行：提交 data 中没有 `packageName` 字段

## 实施步骤

### 步骤1：添加标题标签 (第71-73行)
```vue
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px">
  <div style="display: flex; align-items: center">
    <span class="stat-label">套餐名称</span>
    <el-input v-model="orderPackageName" placeholder="请输入套餐名称" clearable style="width: 240px; margin-left: 8px" />
  </div>
  <el-button v-if="canAddOrderItem" type="primary" plain icon="Plus" @click="addOrderItemRow">添加品项</el-button>
</div>
```

### 步骤2：提交订单时保存 packageName (第575-584行)
在 data 对象中添加 `packageName` 字段：
```js
const data = {
  // ...existing fields...
  packageName: orderPackageName.value,
}
```

## 涉及文件
- `front/src/views/business/sales/index.vue`
  - 第71-73行：模板 - 添加标题
  - 第580行附近：script - 提交数据中添加字段
